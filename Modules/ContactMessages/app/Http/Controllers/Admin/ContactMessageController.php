<?php

namespace Modules\ContactMessages\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\ContactMessages\Models\ContactMessage;
use Yajra\DataTables\Facades\DataTables;

class ContactMessageController extends Controller
{
    public function index()
    {
        if (request()->ajax() && request()->has('draw')) {
            $query = ContactMessage::query()
                ->select(['id', 'name', 'email', 'phone', 'subject', 'message', 'read_at', 'created_at'])
                ->latest();

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->filter(function ($query) {
                    $status = (string) request('status', 'all');

                    if ($status === 'unread') {
                        $query->whereNull('read_at');
                    } elseif ($status === 'read') {
                        $query->whereNotNull('read_at');
                    }

                    $search = trim((string) data_get(request()->all(), 'search.value', ''));
                    if ($search !== '') {
                        $query->where(function ($subQuery) use ($search) {
                            $subQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('subject', 'like', "%{$search}%")
                                ->orWhere('message', 'like', "%{$search}%");
                        });
                    }
                }, true)
                ->addColumn('visitor', function (ContactMessage $message) {
                    $out = '<div class="text-sm font-semibold text-slate-900">' . e($message->name) . '</div>';
                    $out .= '<div class="mt-1 text-sm text-slate-600">' . e($message->email) . '</div>';

                    if ($message->phone) {
                        $out .= '<div class="mt-1 text-xs text-slate-500">' . e($message->phone) . '</div>';
                    }

                    return $out;
                })
                ->editColumn('subject', function (ContactMessage $message) {
                    return '<div class="text-sm font-medium text-slate-800">' . e($message->subject) . '</div>'
                        . '<div class="mt-1 line-clamp-2 text-sm text-slate-500">' . e($message->message) . '</div>';
                })
                ->addColumn('status_badge', function (ContactMessage $message) {
                    if ($message->read_at) {
                        return '<span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">Read</span>';
                    }

                    return '<span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800 ring-1 ring-amber-200">Unread</span>';
                })
                ->addColumn('received_at', function (ContactMessage $message) {
                    return e((string) $message->created_at?->format('d M Y, h:i A'));
                })
                ->addColumn('actions', function (ContactMessage $message) {
                    $showUrl = route('dashboard.contact-messages.show', $message);
                    $deleteUrl = route('dashboard.contact-messages.destroy', $message);

                    $buttons = '<div class="inline-flex items-center justify-end gap-2">';
                    $buttons .= '<a href="' . e($showUrl) . '" class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">View</a>';

                    if (Auth::user()?->hasRole('admin')) {
                        $buttons .= '<form method="POST" action="' . e($deleteUrl) . '" onsubmit="return confirm(\'Delete this contact message?\');">'
                            . '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">'
                            . '<input type="hidden" name="_method" value="DELETE">'
                            . '<button type="submit" class="rounded-md border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800 hover:bg-rose-100">Delete</button>'
                            . '</form>';
                    }

                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['visitor', 'subject', 'status_badge', 'actions'])
                ->toJson();
        }

        $unreadCount = ContactMessage::query()->whereNull('read_at')->count();

        return view('contactmessages::admin.contact-messages.index', compact('unreadCount'));
    }

    public function show(ContactMessage $contactMessage): View
    {
        if ($contactMessage->read_at === null) {
            $contactMessage->forceFill(['read_at' => now()])->save();
        }

        return view('contactmessages::admin.contact-messages.show', compact('contactMessage'));
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return redirect()
            ->route('dashboard.contact-messages.index')
            ->with('success', 'Contact message deleted successfully.');
    }

    public function destroyBulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:contact_messages,id'],
        ]);

        $count = ContactMessage::whereIn('id', $validated['ids'])->delete();

        return redirect()
            ->route('dashboard.contact-messages.index')
            ->with('success', $count . ' contact message' . ($count !== 1 ? 's' : '') . ' deleted successfully.');
    }
}
