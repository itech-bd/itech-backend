<?php

use App\Models\Student;
use App\Models\User;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseOrder;
use Modules\Payment\Models\Payment;
use Modules\Payment\Services\RecordPayment;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->assignRole(Role::findOrCreate('admin', 'web'));
    $this->student = Student::query()->create(['name' => 'Payment Student', 'email' => fake()->unique()->safeEmail(), 'password' => 'password', 'email_verified_at' => now()]);
    $course = Course::query()->create(['title' => 'Payment Course', 'description' => 'Test', 'status' => 'active', 'created_by' => $this->admin->id]);
    $this->order = CourseOrder::query()->create(['student_id' => $this->student->id, 'course_id' => $course->id, 'amount' => 500, 'currency' => 'BDT', 'status' => 'pending']);
});

it('records one full payment and keeps retries idempotent', function () {
    $url = route('dashboard.admin.payments.store', $this->order);
    $this->actingAs($this->admin)->get(route('dashboard.admin.payments.create', $this->order))->assertOk();
    for ($i = 0; $i < 2; $i++) {
        $this->post($url, ['payment_method' => 'manual', 'transaction_reference' => 'REF-1', 'amount' => 1])->assertRedirect()->assertSessionHasNoErrors();
    }
    expect($this->order->refresh()->status)->toBe('paid');
    $this->assertDatabaseCount('payments', 1);
    $this->assertDatabaseHas('payments', ['amount' => 500, 'recorded_by' => $this->admin->id, 'status' => 'successful']);
    $this->get('/dashboard/admin/payments?search='.$this->student->email)->assertOk()->assertSee('REF-1');
    $this->get('/users/'.$this->student->id.'/payments')->assertOk()->assertSee('REF-1');
});

it('scopes student payments even when searching for another student', function () {
    app(RecordPayment::class)->record($this->order, $this->admin->id, ['transaction_reference' => 'PRIVATE-REF']);
    $other = Student::query()->create(['name' => 'Other', 'email' => fake()->unique()->safeEmail(), 'password' => 'password']);
    $this->actingAs($this->admin)->get('/users/'.$other->id.'/payments?search=PRIVATE-REF')->assertOk()->assertViewHas('payments', fn ($payments) => $payments->isEmpty());
    $this->get('/users/'.$other->id.'/payments/1')->assertNotFound();
    $this->get('/users/999999/payments')->assertNotFound();
});

it('requires a reversal reason and preserves financial history on reversal and repayment', function () {
    $payment = app(RecordPayment::class)->record($this->order, $this->admin->id);
    $this->actingAs($this->admin)->patch(route('dashboard.admin.payments.reverse', $payment), [])->assertSessionHasErrors('reversal_reason');
    $this->patch('/dashboard/admin/invoices/'.$this->order->id, ['status' => 'pending'])->assertSessionHasErrors('status');
    $this->patch(route('dashboard.admin.payments.reverse', $payment), ['reversal_reason' => 'Entered in error'])->assertRedirect();
    expect($this->order->refresh()->status)->toBe('pending');
    expect($payment->refresh()->status)->toBe('reversed');
    expect($payment->reversed_by)->toBe($this->admin->id);
    app(RecordPayment::class)->record($this->order, $this->admin->id);
    // Replaying an old reversal must not reverse a newer payment.
    $this->patch(route('dashboard.admin.payments.reverse', $payment), ['reversal_reason' => 'Retry'])->assertRedirect();
    expect($this->order->refresh()->status)->toBe('paid');
    $this->assertDatabaseCount('payments', 2);
    expect(Payment::where('status', 'successful')->sum('amount'))->toEqual(500);
});

it('validates manual inputs and rejects cancelled invoices', function () {
    $this->actingAs($this->admin)->post(route('dashboard.admin.payments.store', $this->order), ['payment_method' => '<invalid>', 'paid_at' => now()->addDay()->toDateTimeString()])->assertSessionHasErrors(['payment_method', 'paid_at']);
    $this->order->update(['status' => 'cancelled']);
    $this->post(route('dashboard.admin.payments.store', $this->order), ['payment_method' => 'manual'])->assertSessionHasErrors('status');
    $this->assertDatabaseCount('payments', 0);
});

it('backfills legacy paid invoices once without changing amounts or timestamps', function () {
    $this->order->update(['status' => 'paid']);
    $timestamp = $this->order->updated_at;
    $migration = require base_path('Modules/Payment/database/migrations/2026_09_13_000002_backfill_legacy_payments.php');
    $migration->up();
    $migration->up();
    $this->assertDatabaseCount('payments', 1);
    $payment = Payment::first();
    expect($payment->amount)->toBe('500.00');
    expect($payment->currency)->toBe('BDT');
    expect($payment->paid_at->equalTo($timestamp))->toBeTrue();
    expect($payment->recorded_by)->toBeNull();
    app(RecordPayment::class)->record($this->order, $this->admin->id);
    $this->assertDatabaseCount('payments', 1);
});

it('denies non-admin users and students all payment write and read routes', function () {
    $payment = app(RecordPayment::class)->record($this->order, $this->admin->id);
    $this->actingAs(User::factory()->create());
    $this->get('/dashboard/admin/payments')->assertForbidden();
    $this->get('/users/'.$this->student->id.'/payments')->assertForbidden();
    $this->post(route('dashboard.admin.payments.store', $this->order), ['payment_method' => 'manual'])->assertForbidden();
    $this->patch(route('dashboard.admin.payments.reverse', $payment), ['reversal_reason' => 'Test'])->assertForbidden();
    auth('web')->logout();
    $this->actingAs($this->student, 'student')->get('/dashboard/admin/payments')->assertRedirect(route('login'));
});

it('preserves student API invoice payloads and downloads after payment', function () {
    app(RecordPayment::class)->record($this->order, $this->admin->id);
    \Laravel\Sanctum\Sanctum::actingAs($this->student);
    $this->getJson('/api/v1/student/invoices/'.$this->order->id)->assertOk()
        ->assertJsonPath('data.status', 'paid')->assertJsonPath('data.amount', 500)
        ->assertJsonPath('data.download_path', '/api/v1/student/invoices/'.$this->order->id.'/download');
    $this->get('/api/v1/student/invoices/'.$this->order->id.'/download')->assertOk()->assertHeader('content-type', 'application/pdf');
});

it('uses successful payments for admin revenue', function () {
    $payment = app(RecordPayment::class)->record($this->order, $this->admin->id);
    $this->actingAs($this->admin)->get('/dashboard')->assertOk()
        ->assertViewHas('stats', fn ($stats) => $stats['paid_revenue'] === 500.0);
    app(RecordPayment::class)->reverse($payment, $this->admin->id, 'Correction');
    $this->get('/dashboard')->assertOk()->assertViewHas('stats', fn ($stats) => $stats['paid_revenue'] === 0.0);
});

it('filters transaction IDs and dates while keeping summaries scoped and currencies separate', function () {
    $first = app(RecordPayment::class)->record($this->order, $this->admin->id, ['payment_method' => 'cash', 'paid_at' => now()->subDay()->startOfDay()]);
    $secondOrder = CourseOrder::query()->create(['student_id' => $this->student->id, 'course_id' => $this->order->course_id, 'amount' => 10, 'currency' => 'USD', 'status' => 'pending']);
    app(RecordPayment::class)->record($secondOrder, $this->admin->id, ['payment_method' => 'card']);
    $this->actingAs($this->admin)->get('/users/'.$this->student->id.'/payments')->assertOk()
        ->assertViewHas('summary', fn ($rows) => $rows->count() === 2 && (float) $rows->firstWhere('currency', 'USD')->total === 10.0);
    $this->get('/dashboard/admin/payments?search='.urlencode('#PAY-'.$first->id))->assertOk()
        ->assertViewHas('payments', fn ($rows) => $rows->total() === 1 && $rows->first()->id === $first->id);
    $day = now()->subDay()->toDateString();
    $this->get('/dashboard/admin/payments?payment_method=cash&from='.$day.'&to='.$day)->assertOk()
        ->assertViewHas('payments', fn ($rows) => $rows->total() === 1)
        ->assertViewHas('summary', fn ($rows) => $rows->count() === 1 && $rows->first()->currency === 'BDT');
    $this->get('/dashboard/admin/payments?from=2026-02-02&to=2026-02-01')->assertSessionHasErrors('to');
});

it('validates domain calls and avoids offering payment entry on paid invoices', function () {
    expect(fn () => app(RecordPayment::class)->record($this->order, $this->admin->id, ['payment_method' => '<invalid>']))->toThrow(\Illuminate\Validation\ValidationException::class);
    $payment = app(RecordPayment::class)->record($this->order, $this->admin->id);
    expect(fn () => app(RecordPayment::class)->reverse($payment, $this->admin->id, '   '))->toThrow(\Illuminate\Validation\ValidationException::class);
    expect($payment->fresh()->status)->toBe('successful');
    $this->actingAs($this->admin)->get(route('dashboard.admin.payments.create', $this->order))->assertRedirect(route('users.payments.index', $this->student));
});

it('enforces the successful payment slot at database level', function () {
    $payment = app(RecordPayment::class)->record($this->order, $this->admin->id);
    expect(fn () => $payment->replicate()->save())->toThrow(\Illuminate\Database\QueryException::class);
});
