<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Api\V1\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Mentors\Models\Mentor;

class StudentMentorController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $batchIds = DB::table('batch_students')
            ->where('student_id', $request->user()->id)
            ->where('status', 'approved')
            ->pluck('batch_id');

        $query = Mentor::query()
            ->where('is_active', true)
            ->whereHas('batches', fn ($builder) => $builder->whereIn('batches.id', $batchIds))
            ->orderBy('name');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('topic', 'like', "%{$search}%"));
        }

        $paginator = $query->paginate(min(max($request->integer('per_page', 12), 1), 50));

        return $this->success([
            ...$this->paginated($paginator, fn (Mentor $mentor) => [
                'id' => $mentor->id,
                'slug' => $mentor->public_route_key,
                'name' => $mentor->name,
                'topic' => $mentor->topic,
                'bio' => $mentor->bio,
                'email' => $mentor->email,
                'profile_image_url' => $mentor->profile_image_url,
            ]),
            'filters' => ['search' => $search],
        ]);
    }
}
