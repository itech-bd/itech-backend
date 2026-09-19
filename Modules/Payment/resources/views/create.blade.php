<x-app-layout>
    <x-slot name="header"><p class="text-xs font-extrabold uppercase tracking-widest text-indigo-700">Finance / Record payment</p><h2 class="mt-2 text-2xl font-extrabold text-slate-950">Record money received</h2><p class="mt-2 text-sm text-slate-500">Confirm the transaction for invoice #INV-{{ $order->id }}.</p></x-slot>
    <div class="mx-auto grid max-w-5xl items-start gap-6 lg:grid-cols-3">
        <aside class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="bg-slate-900 p-6 text-white"><p class="text-sm text-slate-300">Invoice total</p><p class="mt-3 text-3xl font-extrabold"><span class="text-base">{{ $order->currency }}</span> {{ number_format((float) $order->amount, 2) }}</p><span class="mt-4 inline-flex rounded-lg bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Pending payment</span></div>
            <dl class="space-y-5 p-6 text-sm">
                <div><dt class="text-xs font-bold uppercase text-slate-400">Invoice</dt><dd class="mt-1 font-bold text-indigo-700"><a href="{{ route('users.invoices.show', [$order->student_id, $order]) }}">#INV-{{ $order->id }}</a></dd></div>
                <div><dt class="text-xs font-bold uppercase text-slate-400">Student</dt><dd class="mt-1 font-bold text-slate-900">{{ $order->student?->name }}</dd><dd class="mt-1 break-all text-xs text-slate-500">{{ $order->student?->email }}</dd></div>
                <div><dt class="text-xs font-bold uppercase text-slate-400">Course</dt><dd class="mt-1 text-slate-700">{{ $order->course?->title }}</dd></div>
            </dl>
        </aside>
        <form method="POST" action="{{ route('dashboard.admin.payments.store', $order) }}" class="space-y-5 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200 lg:col-span-2" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <p>{{ $order->student?->name }} · {{ $order->course?->title }}</p>
            <h3 class="text-lg font-extrabold text-slate-950">Transaction details</h3>
            <label class="block text-sm font-bold text-slate-700">Payment method<input name="payment_method" list="payment-methods" value="{{ old('payment_method', 'manual') }}" maxlength="50" required pattern="[a-zA-Z0-9_-]+" class="mt-2 block w-full rounded-xl border-slate-200"></label>
            <datalist id="payment-methods"><option value="manual"></option><option value="cash"></option><option value="bank_transfer"></option><option value="bkash"></option><option value="nagad"></option><option value="card"></option></datalist>
            <label class="block">Transaction reference<input name="transaction_reference" value="{{ old('transaction_reference') }}" maxlength="191" class="mt-1 block w-full rounded-md border-slate-300"></label>
            <label class="block">Paid at ({{ config('app.timezone') }}; blank uses current time)<input type="datetime-local" name="paid_at" value="{{ old('paid_at') }}" class="mt-1 block w-full rounded-md border-slate-300"></label>
            <label class="block">Note<textarea name="note" maxlength="2000" class="mt-1 block w-full rounded-md border-slate-300">{{ old('note') }}</textarea></label>
            <p class="rounded-2xl bg-indigo-50 p-4 text-sm leading-6 text-indigo-900">Confirm receipt of the full invoice amount before recording. This marks the invoice as paid; it does not charge the student.</p>
            <button :disabled="submitting" class="rounded-xl bg-indigo-700 px-5 py-3 text-sm font-bold text-white disabled:opacity-50" x-text="submitting ? 'Recording...' : 'Confirm payment'">Confirm payment</button>
            <a href="{{ route('dashboard.admin.invoices.show', $order) }}" class="ml-3 text-slate-600">Back to invoice</a>
        </form>
    </div>
</x-app-layout>
