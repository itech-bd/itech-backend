<?php

namespace Modules\Batch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Modules\Batch\Http\Requests\UpdateBatchMentorsRequest;
use Modules\Batch\Models\Batch;

/**
 * Batch mentor assignment controller.
 *
 * @category Controller
 * @package  Modules\Batch\Http\Controllers
 * @author   Unknown <unknown@example.invalid>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://laravel.com
 */
class BatchMentorAssignmentController extends Controller implements HasMiddleware
{
    /**
     * Define controller middleware.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:admin|assignMentorsToBatch'),
        ];
    }

    /**
     * Display the batch mentors management page.
     *
     * @param Batch $batch The batch model.
     *
     * @return View
     */
    public function edit(Batch $batch): View
    {
        abort_unless(Gate::allows('assignMentors', $batch), 403);

        $assignedMentors = $batch->mentors()
            ->orderBy('users.name')
            ->get(['users.id', 'users.name', 'users.email']);

        $assignedMentorIds = $assignedMentors
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $availableMentors = User::query()
            ->role('mentor')
            ->when(
                count($assignedMentorIds) > 0,
                fn ($q) => $q->whereNotIn('id', $assignedMentorIds),
                fn ($q) => $q
            )
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'name', 'email']);

        return view(
            'batch::admin.batches.mentors',
            compact('batch', 'assignedMentors', 'availableMentors')
        );
    }

    /**
     * Add a mentor to the batch.
     *
     * @param Request $request The incoming request.
     * @param Batch   $batch   The batch model.
     *
     * @return RedirectResponse
     */
    public function add(Request $request, Batch $batch): RedirectResponse
    {
        abort_unless(Gate::allows('assignMentors', $batch), 403);

        $validated = $request->validate(
            [
                'mentor_id' => ['required', 'integer', 'exists:users,id'],
            ]
        );

        $mentorId = (int) $validated['mentor_id'];
        $mentor = User::query()->findOrFail($mentorId);
        abort_unless($mentor->hasRole('mentor'), 422);

        $alreadyAssigned = $batch->mentors()
            ->where('users.id', $mentorId)
            ->exists();

        if ($alreadyAssigned) {
            return redirect()
                ->route('dashboard.batches.mentors.edit', $batch)
                ->withErrors(
                    ['mentor_id' => 'Mentor is already assigned to this batch.']
                );
        }

        $batch->mentors()->syncWithoutDetaching([$mentorId]);

        return redirect()
            ->route('dashboard.batches.mentors.edit', $batch)
            ->with('success', 'Mentor added successfully.');
    }

    /**
     * Remove a mentor from the batch.
     *
     * @param Batch $batch  The batch model.
     * @param User  $mentor The mentor user.
     *
     * @return RedirectResponse
     */
    public function remove(Batch $batch, User $mentor): RedirectResponse
    {
        abort_unless(Gate::allows('assignMentors', $batch), 403);
        abort_unless($mentor->hasRole('mentor'), 422);

        $batch->mentors()->detach($mentor->id);

        return redirect()
            ->route('dashboard.batches.mentors.edit', $batch)
            ->with('success', 'Mentor removed successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateBatchMentorsRequest $request The validated request.
     * @param Batch                     $batch   The batch model.
     *
     * @return RedirectResponse
     */
    public function update(
        UpdateBatchMentorsRequest $request,
        Batch $batch
    ): RedirectResponse {
        abort_unless(Gate::allows('assignMentors', $batch), 403);

        $mentorIds = array_map('intval', $request->validated()['mentor_ids'] ?? []);
        $allowedMentorIds = User::query()
            ->role('mentor')
            ->whereIn('id', $mentorIds)
            ->pluck('id')
            ->all();

        $batch->mentors()->sync($allowedMentorIds);

        return redirect()
            ->route('dashboard.batches.mentors.edit', $batch)
            ->with('success', 'Batch mentors updated successfully.');
    }
}
