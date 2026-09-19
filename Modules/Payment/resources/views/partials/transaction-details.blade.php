                                @if ($isImported)
                                    <p class="font-bold">Added from a previous invoice</p>
                                    <p>Added to system: {{ $payment->created_at?->format('d M Y, H:i') ?? 'Unknown' }}</p>
                                    <p>This invoice was already marked as paid. The exact payment time was not recorded, so the time shown is an estimate.</p>
                                @else
                                    <p class="font-bold">{{ $payment->recordedBy ? 'Recorded by '.$payment->recordedBy->name : 'Recorder information unavailable' }}</p>
                                    <p>Recorded on: {{ $payment->created_at?->format('d M Y, H:i') ?? 'Unknown' }}</p>
                                @endif
                                @if ($payment->note && ! ($isImported && $payment->note === 'Imported from paid invoice; paid_at is an estimated historical timestamp.'))<p class="whitespace-pre-line">{{ $payment->note }}</p>@endif
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
