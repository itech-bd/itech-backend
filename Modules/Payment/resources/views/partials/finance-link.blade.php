@php
    $showPayments = $currentPage === 'invoices';
    $destination = $showPayments ? 'payments' : 'invoices';
    $financeUrl = isset($student)
        ? route('users.'.$destination.'.index', $student)
        : route('dashboard.admin.'.$destination.'.index');
@endphp
<a href="{{ $financeUrl }}" class="inline-flex min-h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl px-4 py-2 text-xs font-bold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 {{ $showPayments ? 'border border-indigo-600 bg-indigo-600 text-white shadow-sm hover:border-indigo-700 hover:bg-indigo-700 active:bg-indigo-800' : 'text-slate-500 hover:bg-slate-50 hover:text-indigo-700' }}">
    @if ($showPayments)
        <i class="fa-solid fa-chart-column" aria-hidden="true"></i>
    @endif
    {{ $showPayments ? 'Payment report' : 'Back to invoices' }}
    <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
</a>
