<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 leading-tight">Contact Message</h2>
                <p class="mt-1 text-sm text-slate-500">View the full details of a visitor enquiry.</p>
            </div>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('dashboard.contact-messages.destroy', $contactMessage) }}" onsubmit="return confirm('Delete this contact message?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-800 hover:bg-rose-100">Delete</button>
                </form>
                <a href="{{ route('dashboard.contact-messages.index') }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Subject</div>
                <div class="mt-2 text-lg font-semibold text-slate-900">{{ $contactMessage->subject }}</div>
            </div>

            <div class="mt-6">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Message</div>
                <div class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $contactMessage->message }}</div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="text-sm font-semibold text-slate-900">Visitor Details</div>

                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Name</dt>
                        <dd class="mt-1 font-medium text-slate-800">{{ $contactMessage->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Email</dt>
                        <dd class="mt-1"><a href="mailto:{{ $contactMessage->email }}" class="font-medium text-sky-700 hover:text-sky-800">{{ $contactMessage->email }}</a></dd>
                    </div>
                    @if($contactMessage->phone)
                        <div>
                            <dt class="text-slate-500">Phone</dt>
                            <dd class="mt-1"><a href="tel:{{ preg_replace('/[^\d+]/', '', $contactMessage->phone) }}" class="font-medium text-sky-700 hover:text-sky-800">{{ $contactMessage->phone }}</a></dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-slate-500">Received</dt>
                        <dd class="mt-1 font-medium text-slate-800">{{ $contactMessage->created_at?->format('d M Y, h:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Status</dt>
                        <dd class="mt-1">
                            @if($contactMessage->read_at)
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">Read {{ $contactMessage->read_at->format('d M Y, h:i A') }}</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800 ring-1 ring-amber-200">Unread</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="text-sm font-semibold text-slate-900">Technical Details</div>

                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-slate-500">IP Address</dt>
                        <dd class="mt-1 break-all font-medium text-slate-800">{{ $contactMessage->ip_address ?: 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">User Agent</dt>
                        <dd class="mt-1 break-words text-slate-700">{{ $contactMessage->user_agent ?: 'N/A' }}</dd>
                    </div>
                    @if($contactMessage->user)
                        <div>
                            <dt class="text-slate-500">Linked User</dt>
                            <dd class="mt-1 font-medium text-slate-800">{{ $contactMessage->user->name }} ({{ $contactMessage->user->email }})</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
