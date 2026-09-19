        <dl aria-label="Payment summary for current filters" class="grid overflow-hidden rounded-2xl border border-slate-200 bg-white divide-y divide-slate-200 md:grid-cols-3 md:divide-x md:divide-y-0">
            <div class="flex min-w-0 flex-wrap items-baseline justify-between gap-x-3 gap-y-1 bg-indigo-50/60 px-4 py-3">
                <dt class="text-xs font-semibold text-indigo-800">Matching transactions</dt>
                <dd class="text-xl font-extrabold tabular-nums leading-6 text-indigo-800">{{ number_format($transactionCount) }}</dd>
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