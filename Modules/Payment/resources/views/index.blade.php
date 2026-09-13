<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div><p class="text-xs font-extrabold uppercase tracking-widest text-indigo-700">Admin / Finance</p><h2 class="mt-2 text-2xl font-extrabold text-slate-950">Payments{{ $student ? ': '.$student->name : '' }}</h2><p class="mt-2 text-sm text-slate-500">Money received, transaction references and reversal history.</p></div>
        </div>
    </x-slot>
    <x-slot name="headerActions">
        <a href="{{ $student ? route('users.invoices.index', [$student, 'status' => 'pending']) : route('dashboard.admin.invoices.index', ['status' => 'pending']) }}" class="inline-flex min-h-11 shrink-0 items-center justify-center whitespace-nowrap rounded-xl bg-indigo-700 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-800">+ Record a payment</a>
    </x-slot>
    <div class="space-y-4">
        <dl aria-label="Payment summary for current filters" class="grid overflow-hidden rounded-2xl border border-slate-200 bg-white divide-y divide-slate-200 md:grid-cols-3 md:divide-x md:divide-y-0">
            <div class="flex min-w-0 flex-wrap items-baseline justify-between gap-x-3 gap-y-1 bg-indigo-50/60 px-4 py-3">
                <dt class="text-xs font-semibold text-indigo-800">Matching transactions</dt>
                <dd class="text-xl font-extrabold tabular-nums leading-6 text-indigo-800">{{ number_format($payments->total()) }}</dd>
                <dd class="w-full text-xs text-slate-500">Current filters · all pages</dd>
            </div>
            @foreach (['successful' => 'Received', 'reversed' => 'Reversed'] as $state => $label)
                <div class="flex min-w-0 flex-wrap items-baseline justify-between gap-x-3 gap-y-1 px-4 py-3">
                        <dt class="text-xs font-semibold {{ $state === 'successful' ? 'text-emerald-700' : 'text-amber-700' }}">{{ $label }}</dt>
                        <dd class="space-y-1 text-right">
                            @forelse ($summary->where('status', $state) as $total)
                                <span class="block text-xl font-extrabold tabular-nums leading-6 text-slate-900"><span class="text-xs font-semibold text-slate-500">{{ $total->currency }}</span> {{ number_format((float) $total->total, 2) }}</span>
                            @empty
                                <span class="block text-xl leading-6 text-slate-400"><span aria-hidden="true">&mdash;</span><span class="sr-only">No matching transactions</span></span>
                            @endforelse
                        </dd>
                    <dd class="w-full text-xs text-slate-500">{{ $state === 'successful' ? 'Successful payments only' : 'Excluded from received totals' }}</dd>
                </div>
            @endforeach
        </dl>
        <form method="GET" class="flex flex-wrap items-end gap-3 rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <label class="min-w-0 flex-1 text-xs font-bold text-slate-600">Search<input name="search" placeholder="Student, reference, #INV-123 or #PAY-123" value="{{ request('search') }}" class="mt-1 block w-full min-w-[12rem] rounded-xl border-slate-200 text-sm"></label>
            <select name="status" aria-label="Payment status" class="rounded-md border-slate-300">
                <option value="">All statuses</option>
                @foreach (['successful', 'reversed'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach
            </select>
            <label class="text-xs font-bold text-slate-600">Method<select name="payment_method" class="mt-1 block rounded-xl border-slate-200 text-sm"><option value="">All methods</option>@foreach($methods as $method)<option value="{{ $method }}" @selected(request('payment_method') === $method)>{{ ucfirst(str_replace('_', ' ', $method)) }}</option>@endforeach</select></label>
            <label class="text-xs font-bold text-slate-600">Paid from<input type="date" name="from" value="{{ request('from') }}" class="mt-1 block rounded-xl border-slate-200 text-sm"></label>
            <label class="text-xs font-bold text-slate-600">Paid through<input type="date" name="to" value="{{ request('to') }}" class="mt-1 block rounded-xl border-slate-200 text-sm"></label>
            <button class="rounded-xl bg-indigo-700 px-4 py-2 text-sm font-bold text-white">Apply filters</button>
            <a href="{{ $student ? route('users.payments.index', $student) : route('dashboard.admin.payments.index') }}" class="px-2 py-2 text-sm font-semibold text-slate-500">Clear</a>
        </form>
        <div class="overflow-x-auto rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="mb-1 flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
                <h3 class="text-lg font-extrabold text-slate-950">Transaction history</h3>
                <div class="flex flex-wrap items-center gap-x-3">
                    @if ($student)
                        <a class="inline-flex min-h-9 items-center text-xs font-semibold text-slate-500 hover:text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600" href="{{ route('users.index') }}">Back to Users</a>
                    @endif
                    @include('payment::partials.finance-link', ['currentPage' => 'payments'])
                </div>
            </div>
            <p class="mb-5 text-sm text-slate-500">Expand a transaction to review its audit history. Dates in {{ config('app.timezone') }}.</p>
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50"><tr>
                    @foreach (['Transaction', 'Student / Invoice', 'Amount', 'Method / Reference', 'Status', 'Audit details'] as $heading)<th scope="col" class="px-4 py-3 text-xs font-extrabold uppercase tracking-wider text-slate-500">{{ $heading }}</th>@endforeach
                </tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($payments as $payment)
                        <tr class="align-top hover:bg-slate-50">
                            <td class="px-4 py-5"><p class="font-extrabold text-slate-900">#PAY-{{ $payment->id }}</p><p class="mt-1 whitespace-nowrap text-xs text-slate-500">{{ $payment->paid_at?->format('d M Y, H:i') }}</p></td>
                            <td class="px-4 py-5"><p class="font-bold text-slate-900">{{ $payment->courseOrder->student?->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $payment->courseOrder->student?->email }}</p><a class="mt-2 inline-block text-xs font-bold text-indigo-700" href="{{ route('users.invoices.show', [$payment->courseOrder->student_id, $payment->course_order_id]) }}">#INV-{{ $payment->course_order_id }}</a><p class="mt-1 text-xs text-slate-500">{{ $payment->courseOrder->course?->title }}</p></td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $payment->currency }} {{ $payment->amount }}</td>
                            <td class="px-4 py-3">{{ $payment->payment_method }}<div>{{ $payment->transaction_reference }}</div></td>
                            <td class="px-4 py-3"><span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold {{ $payment->status === 'successful' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($payment->status) }}</span></td>
                            <td class="px-4 py-3">
                                <details @if(old('payment_id') == $payment->id) open @endif><summary class="cursor-pointer font-bold text-indigo-700">View transaction</summary>
                                <div class="mt-3 min-w-[14rem] space-y-3 border-l-2 border-slate-200 pl-3 text-xs text-slate-600">
                                <p class="font-bold">Recorded by {{ $payment->recordedBy?->name ?? 'Legacy / system' }}</p><p>{{ $payment->created_at?->format('d M Y, H:i') }}</p>
                                @if ($payment->note)<p class="whitespace-pre-line">{{ $payment->note }}</p>@endif
                                @if ($payment->status === 'successful')
                                    <details><summary class="cursor-pointer text-amber-700">Reverse payment</summary>
                                        <form method="POST" action="{{ route('dashboard.admin.payments.reverse', $payment) }}" class="mt-2 space-y-2">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                            <p class="my-3">Returns the invoice to pending and preserves this transaction. No money is refunded automatically.</p>
                                            <textarea name="reversal_reason" aria-label="Reversal reason" placeholder="Required reason" required maxlength="2000" class="my-2 block w-full rounded-xl border-slate-200">{{ old('payment_id') == $payment->id ? old('reversal_reason') : '' }}</textarea>
                                            <button class="rounded-md bg-amber-600 px-3 py-2 text-white">Confirm reversal</button>
                                        </form>
                                    </details>
                                @else
                                    <p>{{ $payment->reversal_reason }}</p>
                                    <p class="text-xs text-slate-500">{{ $payment->reversedBy?->name }} · {{ $payment->reversed_at?->format('d M Y H:i') }}</p>
                                @endif
                                </div></details>
                            </td>
                        </tr>
                    @empty<tr><td colspan="6" class="px-4 py-14 text-center"><p class="font-bold text-slate-900">No payments found.</p><p class="mt-2 text-sm text-slate-500">Adjust your filters, or open a pending invoice to record money received.</p></td></tr>@endforelse
                </tbody>
            </table>
        </div>
        {{ $payments->links() }}
    </div>
</x-app-layout>
