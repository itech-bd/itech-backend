@php
    $showPayments = $currentPage === 'invoices';
    $destination = $showPayments ? 'payments' : 'invoices';
    $financeUrl = isset($student)
        ? route('users.'.$destination.'.index', $student)
        : route('dashboard.admin.'.$destination.'.index');
@endphp
<a href="{{ $financeUrl }}" class="inline-flex min-h-9 items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-800 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-100 active:bg-indigo-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
    View {{ $destination }}
    <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
</a>
