<?php

namespace Modules\Mentors\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Mentors\Models\Mentor;

/**
 * Student mentors pages.
 *
 * @category Controller
 * @package  Modules\Mentors\Http\Controllers
 * @author   Edu App <support@example.test>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://laravel.com
 */
class MyMentorsController extends Controller
{
    /**
     * List mentors for the authenticated student (from their approved batches only).
     *
     * @param Request $request Incoming request.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        $batchIds = DB::table('batch_students')
            ->where('student_id', $user->id)
            ->where('status', 'approved')
            ->pluck('batch_id');

        $mentors = Mentor::query()
            ->where('is_active', true)
            ->whereHas(
                'user',
                function ($query) use ($batchIds) {
                    $query->whereHas(
                        'mentorBatches',
                        function ($batchQuery) use ($batchIds) {
                            $batchQuery->whereIn('batches.id', $batchIds);
                        }
                    );
                }
            )
            ->with(['user:id,name,email'])
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view(
            'mentors::student.mentors.index',
            [
                'mentors' => $mentors,
            ]
        );
    }
}
