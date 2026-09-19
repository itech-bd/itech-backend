            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50"><tr>
                    @foreach (['Transaction', 'Student / Invoice', 'Amount', 'Method / Reference', 'Status'] as $heading)<th scope="col" class="px-4 py-3 text-xs font-extrabold uppercase tracking-wider text-slate-500">{{ $heading }}</th>@endforeach
                </tr></thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($payments as $payment)
                        @php($isImported = $payment->payment_method === 'legacy')
                        <tr class="align-top hover:bg-slate-50">
                            <td class="px-4 py-5"><p class="font-extrabold text-slate-900">#PAY-{{ $payment->id }}</p><p class="mt-1 text-xs text-slate-500">{{ $isImported ? 'Estimated payment time' : 'Payment time' }}</p><p class="mt-1 whitespace-nowrap text-xs text-slate-500">{{ $payment->paid_at?->format('d M Y, H:i') ?? 'Unknown' }}</p></td>
                            <td class="px-4 py-5"><p class="font-bold text-slate-900">{{ $payment->courseOrder->student?->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $payment->courseOrder->student?->email }}</p><a class="mt-2 inline-block text-xs font-bold text-indigo-700" href="{{ route('dashboard.admin.invoices.show', $payment->course_order_id) }}">#INV-{{ $payment->course_order_id }}</a><p class="mt-1 text-xs text-slate-500">{{ $payment->courseOrder->course?->title }}</p></td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $payment->currency }} {{ $payment->amount }}</td>
                            <td class="px-4 py-3">{{ $isImported ? 'Previous invoice' : $payment->payment_method }}<div>{{ $payment->transaction_reference }}</div></td>
                            <td class="px-4 py-3"><span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold {{ $payment->status === 'successful' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($payment->status) }}</span></td>

                        </tr>
                    @empty<tr><td colspan="5" class="px-4 py-14 text-center"><p class="font-bold text-slate-900">No payments found.</p><p class="mt-2 text-sm text-slate-500">No transactions match the selected filters.</p></td></tr>@endforelse
                </tbody>
            </table>