<?php

namespace Modules\Mentors\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Mentors\Models\Mentor;
use Yajra\DataTables\Facades\DataTables;

/**
 * Manage mentors.
 */
class MentorController extends Controller implements HasMiddleware
{
    /**
     * Controller middleware.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:admin|readMentor', only: ['index', 'show']),
            new Middleware('role_or_permission:admin|addMentor', only: ['create', 'store']),
            new Middleware('role_or_permission:admin|editMentor', only: ['edit', 'update']),
            new Middleware('role_or_permission:admin|deleteMentor', only: ['destroy']),
        ];
    }

    /**
     * Display the mentors list.
     */
    public function index()
    {
        abort_unless(Gate::allows('viewAny', Mentor::class), 403);

        if (request()->ajax() && request()->has('draw')) {
            $query = Mentor::query()
                ->with(['user:id,name,email'])
                ->select([
                    'id',
                    'user_id',
                    'name',
                    'topic',
                    'bio',
                    'is_active',
                    'created_at',
                ])
                ->latest();

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn(
                    'linked_user',
                    fn (Mentor $mentor) => $this->renderLinkedUserCell($mentor)
                )
                ->addColumn(
                    'status_badge',
                    fn (Mentor $mentor) => $this->renderStatusBadge($mentor)
                )
                ->addColumn(
                    'actions',
                    fn (Mentor $mentor) => $this->renderActions($mentor)
                )
                ->filterColumn(
                    'name',
                    function ($query, $keyword) {
                        $query->where(
                            function ($q) use ($keyword) {
                                $q->where('name', 'like', "%{$keyword}%")
                                    ->orWhere('topic', 'like', "%{$keyword}%")
                                    ->orWhere('bio', 'like', "%{$keyword}%");
                            }
                        );
                    }
                )
                ->rawColumns(['linked_user', 'status_badge', 'actions'])
                ->toJson();
        }

        return view('mentors::mentors.index');
    }

    private function renderLinkedUserCell(Mentor $mentor): string
    {
        if (! $mentor->user) {
            return '<span class="text-slate-500">-</span>';
        }

        return '<div class="font-medium">'
            . e($mentor->user->name)
            . '</div>'
            . '<div class="text-xs text-slate-500">'
            . e($mentor->user->email)
            . '</div>';
    }

    private function renderStatusBadge(Mentor $mentor): string
    {
        if ($mentor->is_active) {
            return '<span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 '
                . 'text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">Active</span>';
        }

        return '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 '
            . 'text-xs font-semibold text-slate-700 ring-1 ring-slate-200">Hidden</span>';
    }

    private function renderActions(Mentor $mentor): string
    {
        $viewUrl = route('dashboard.mentors.show', $mentor);
        $editUrl = route('dashboard.mentors.edit', $mentor);
        $deleteUrl = route('dashboard.mentors.destroy', $mentor);

        $buttons = '<div class="inline-flex items-center gap-2">'
            . '<a href="' . e($viewUrl) . '" '
            . 'class="rounded-md border border-slate-200 bg-white px-3 py-1.5 '
            . 'text-xs font-semibold text-slate-700 hover:bg-slate-50">View</a>';

        if (Gate::allows('update', $mentor)) {
            $buttons .= '<a href="' . e($editUrl) . '" '
                . 'class="rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 '
                . 'text-xs font-semibold text-amber-800 hover:bg-amber-100">Edit</a>';
        }

        if (request()->user() && request()->user()->can('deleteMentor')) {
            $buttons .= '<form method="POST" action="' . e($deleteUrl) . '" '
                . 'onsubmit="return confirm(\'Delete this mentor?\');">'
                . '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">'
                . '<input type="hidden" name="_method" value="DELETE">'
                . '<button type="submit" '
                . 'class="rounded-md border border-rose-200 bg-rose-50 px-3 py-1.5 '
                . 'text-xs font-semibold text-rose-800 hover:bg-rose-100">Delete</button>'
                . '</form>';
        }

        $buttons .= '</div>';

        return $buttons;
    }

    /**
     * Show the create mentor form.
     */
    public function create()
    {
        abort_unless(Gate::allows('create', Mentor::class), 403);

        return view('mentors::mentors.create');
    }

    /**
     * Store a new mentor.
     */
    public function store(Request $request)
    {
        abort_unless(Gate::allows('create', Mentor::class), 403);

        $validated = $request->validate(
            [
                'email' => 'required|email|max:255|unique:users,email',
                'name' => 'required|string|max:255',
                'slug' => [
                    'nullable',
                    'string',
                    'max:120',
                    'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                    Rule::unique('mentors', 'slug'),
                ],
                'topic' => 'nullable|string|max:255',
                'bio' => [
                    'nullable',
                    'string',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        $wordCount = count(preg_split('/\s+/u', trim(strip_tags((string) $value)), -1, PREG_SPLIT_NO_EMPTY));
                        if ($wordCount > 10000) {
                            $fail('The bio field must not exceed 10,000 words.');
                        }
                    },
                ],
                'is_active' => 'sometimes|boolean',
            ]
        );

        $validated['is_active'] = (bool) ($request->boolean('is_active'));
        $validated['slug'] = $this->generateUniqueSlug(
            $validated['slug'] ?? $validated['name'] ?? '',
        );

        DB::transaction(
            function () use ($validated) {
                $user = User::query()->create(
                    [
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'password' => '12345678',
                        'must_change_password' => true,
                    ]
                );

                $user->assignRole('mentor');

                Mentor::query()->create(
                    [
                        'user_id' => $user->id,
                        'slug' => $validated['slug'],
                        'name' => $validated['name'],
                        'topic' => $validated['topic'] ?? null,
                        'bio' => $validated['bio'] ?? null,
                        'is_active' => $validated['is_active'],
                    ]
                );
            }
        );

        return redirect()
            ->route('dashboard.mentors.index')
            ->with('success', 'Mentor created successfully.');
    }

    /**
     * Show a mentor.
     */
    public function show(Mentor $mentor)
    {
        abort_unless(Gate::allows('view', $mentor), 403);

        $mentor->load(['user:id,name,email']);

        return view('mentors::mentors.show', compact('mentor'));
    }

    /**
     * Show the edit mentor form.
     */
    public function edit(Mentor $mentor)
    {
        abort_unless(Gate::allows('update', $mentor), 403);

        $mentor->load(['user:id,name,email,profile_image']);

        return view('mentors::mentors.edit', compact('mentor'));
    }

    /**
     * Update an existing mentor.
     */
    public function update(Request $request, Mentor $mentor)
    {
        abort_unless(Gate::allows('update', $mentor), 403);

        $validated = $request->validate(
            [
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($mentor->user_id),
                ],
                'name' => 'required|string|max:255',
                'slug' => [
                    'nullable',
                    'string',
                    'max:120',
                    'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                    Rule::unique('mentors', 'slug')->ignore($mentor->id),
                ],
                'topic' => 'nullable|string|max:255',
                'bio' => [
                    'nullable',
                    'string',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        $wordCount = count(preg_split('/\s+/u', trim(strip_tags((string) $value)), -1, PREG_SPLIT_NO_EMPTY));
                        if ($wordCount > 10000) {
                            $fail('The bio field must not exceed 10,000 words.');
                        }
                    },
                ],
                'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'remove_profile_image' => 'nullable|boolean',
                'is_active' => 'sometimes|boolean',
            ]
        );

        $validated['is_active'] = (bool) ($request->boolean('is_active'));
        $validated['slug'] = $this->generateUniqueSlug(
            $validated['slug'] ?? $validated['name'] ?? '',
            $mentor->id,
        );

        DB::transaction(
            function () use ($mentor, $validated, $request) {
                $mentor->loadMissing(['user']);

                if ($mentor->user) {
                    $user = $mentor->user;
                } else {
                    $user = User::query()->create(
                        [
                            'name' => $validated['name'],
                            'email' => $validated['email'],
                            'password' => '12345678',
                            'must_change_password' => true,
                        ]
                    );
                    $user->assignRole('mentor');
                    $mentor->user()->associate($user);
                }

                $user->fill(
                    [
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                    ]
                );

                if ($request->boolean('remove_profile_image')) {
                    $this->deleteProfileImage($user);
                    $user->profile_image = null;
                }

                if ($request->hasFile('profile_image')) {
                    $this->deleteProfileImage($user);
                    $user->profile_image = $request->file('profile_image')
                        ->store('profile-images', 'public');
                }

                $user->save();

                $mentor->update(
                    [
                        'slug' => $validated['slug'],
                        'name' => $validated['name'],
                        'topic' => $validated['topic'] ?? null,
                        'bio' => $validated['bio'] ?? null,
                        'is_active' => $validated['is_active'],
                    ]
                );
            }
        );

        return redirect()
            ->route('dashboard.mentors.index')
            ->with('success', 'Mentor updated successfully.');
    }

    /**
     * Delete the currently stored mentor profile image.
     */
    protected function deleteProfileImage(User $user): void
    {
        $path = $user->profile_image;

        if (is_string($path) && $path !== '') {
            Storage::disk('public')->delete($path);
        }
    }

    protected function generateUniqueSlug(string $value, ?int $ignoreMentorId = null): string
    {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            $baseSlug = 'mentor';
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (
            Mentor::query()
                ->when($ignoreMentorId, fn ($query) => $query->whereKeyNot($ignoreMentorId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * Delete a mentor.
     */
    public function destroy(Mentor $mentor)
    {
        abort_unless(Gate::allows('delete', $mentor), 403);

        $mentor->delete();

        return redirect()
            ->route('dashboard.mentors.index')
            ->with('success', 'Mentor deleted successfully.');
    }
}
