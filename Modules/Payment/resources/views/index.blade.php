<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div><p class="text-xs font-extrabold uppercase tracking-widest text-indigo-700">Admin / Finance</p><h2 class="mt-2 text-2xl font-extrabold text-slate-950">Payment report{{ $student ? ': '.$student->name : '' }}</h2><p class="mt-2 text-sm text-slate-500">Review collections by payment date, student and method. Export or print the filtered report.</p></div>
        </div>
    </x-slot>
    <x-slot name="headerActions">
        <div class="flex flex-wrap gap-2">
            <a href="{{ request()->url().'?'.http_build_query(array_merge($filters, ['output' => 'csv'])) }}" class="rounded-xl bg-indigo-700 px-4 py-3 text-sm font-bold text-white hover:bg-indigo-800">Export CSV</a>
            <a href="{{ request()->url().'?'.http_build_query(array_merge($filters, ['output' => 'print'])) }}" target="_blank" rel="noopener" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700">Print report</a>
        </div>
    </x-slot>

    <div class="space-y-4">
        @include('payment::partials.report-summary')
        <p class="text-xs text-slate-500">Export and print include all transactions matching the applied filters, across all pages. CSV opens in Excel.</p>
        <form method="GET" class="flex flex-wrap items-end gap-3 rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <label class="min-w-0 flex-1 text-xs font-bold text-slate-600">Search<input name="search" placeholder="Student, reference, #INV-123 or #PAY-123" value="{{ request('search') }}" class="mt-1 block w-full min-w-[12rem] rounded-xl border-slate-200 text-sm"></label>
            <select name="status" aria-label="Payment status" class="rounded-md border-slate-300">
                <option value="">All statuses</option>
                @foreach (['successful', 'reversed'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach
            </select>
            <label class="text-xs font-bold text-slate-600">Method<select name="payment_method" class="mt-1 block rounded-xl border-slate-200 text-sm"><option value="">All methods</option>@foreach($methods as $method)<option value="{{ $method }}" @selected(request('payment_method') === $method)>{{ $method === 'legacy' ? 'Previous invoice' : ucfirst(str_replace('_', ' ', $method)) }}</option>@endforeach</select></label>
            <label class="text-xs font-bold text-slate-600">Paid from<input type="date" name="from" value="{{ request('from') }}" class="mt-1 block rounded-xl border-slate-200 text-sm"></label>
            <label class="text-xs font-bold text-slate-600">Paid through<input type="date" name="to" value="{{ request('to') }}" class="mt-1 block rounded-xl border-slate-200 text-sm"></label>
            <button class="rounded-xl bg-indigo-700 px-4 py-2 text-sm font-bold text-white">Apply filters</button>
            <a href="{{ $student ? route('users.payments.index', $student) : route('dashboard.admin.payments.index') }}" class="inline-flex min-h-11 items-center justify-center gap-2 whitespace-nowrap rounded-xl border border-slate-300 bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"><i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i>Clear filters</a>
        </form>
        <div class="overflow-x-auto rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="mb-1 flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
                <h3 class="text-lg font-extrabold text-slate-950">Payment transactions</h3>
                <div class="flex flex-wrap items-center gap-x-3">
                    @if ($student)
                        <a class="inline-flex min-h-9 items-center text-xs font-semibold text-slate-500 hover:text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600" href="{{ route('users.index') }}">Back to Users</a>
                    @endif
                    @include('payment::partials.finance-link', ['currentPage' => 'payments'])
                </div>
            </div>
            <p class="mb-5 text-sm text-slate-500">Date filters use payment time. Received excludes reversed payments. Previous invoice dates are estimates. Dates in {{ config('app.timezone') }}.</p>
            @include('payment::partials.report-table')
        </div>
        {{ $payments->links() }}
    </div>
</x-app-layout>
