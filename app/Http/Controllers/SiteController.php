<?php

namespace App\Http\Controllers;

use App\Models\FrontendPage;
use App\Models\FrontendSection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Batch\Models\Batch;
use Modules\Course\Models\Course;
use Modules\Mentors\Models\Mentor;
use Modules\NewsUpdates\Models\NewsUpdate;
use Modules\Reviews\Models\Review;
use Yajra\DataTables\Facades\DataTables;

class SiteController extends Controller
{
    /**
     * Load CMS page + active sections for a given public slug.
     *
     * @return array{
     *     cmsPage: FrontendPage,
     *     cmsSections: Collection<int, FrontendSection>,
     *     cmsSectionsByKey: Collection<string, FrontendSection>
     * }
     */
    protected function loadCms(string $slug): array
    {
        $hasPagesTable = Schema::hasTable('frontend_pages');
        $hasSectionsTable = Schema::hasTable('frontend_sections');

        if (! $hasPagesTable || ! $hasSectionsTable) {
            $cmsPage = new FrontendPage(['slug' => $slug]);
            $cmsSections = new Collection();

            /** @var Collection<string, FrontendSection> $cmsSectionsByKey */
            $cmsSectionsByKey = new Collection();

            return compact('cmsPage', 'cmsSections', 'cmsSectionsByKey');
        }

        $cmsPage = FrontendPage::query()->firstOrCreate(['slug' => $slug]);

        $cmsSections = FrontendSection::query()
            ->where('frontend_page_id', $cmsPage->id)
            ->active()
            ->orderBy('id')
            ->get();

        /** @var Collection<string, FrontendSection> $cmsSectionsByKey */
        $cmsSectionsByKey = $cmsSections->keyBy('section_key');

        return compact('cmsPage', 'cmsSections', 'cmsSectionsByKey');
    }

    protected function activeCoursesQuery()
    {
        return Course::query()
            ->where('status', 'active')
            ->with([
                'batches' => function ($query) {
                    $query
                        ->whereIn('status', ['upcoming', 'running'])
                        ->orderBy('start_date')
                        ->orderBy('id');
                },
            ]);
    }

    protected function publishedNewsQuery()
    {
        return NewsUpdate::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    protected function courseTrack(Course $course): string
    {
        $title = Str::lower((string) $course->title);

        if (Str::contains($title, ['graphic', 'design'])) {
            return 'Graphic & Multimedia';
        }

        if (Str::contains($title, ['marketing', 'seo', 'digital'])) {
            return 'Digital Marketing';
        }

        if (Str::contains($title, ['hardware', 'network'])) {
            return 'Hardware & Networking';
        }

        if (Str::contains($title, ['web', '.net', 'dotnet', 'software', 'development'])) {
            return 'Web & Software';
        }

        return 'Professional Skill';
    }

    protected function siteStats(): array
    {
        return [
            'courses' => Schema::hasTable('courses')
                ? Course::query()->where('status', 'active')->count()
                : 0,
            'mentors' => Schema::hasTable('mentors')
                ? Mentor::query()->where('is_active', true)->count()
                : 0,
            'batches' => Schema::hasTable('batches')
                ? Batch::query()->whereIn('status', ['upcoming', 'running'])->count()
                : 0,
            'students' => Schema::hasTable('batch_students')
                ? DB::table('batch_students')
                    ->where('status', 'approved')
                    ->distinct('student_id')
                    ->count('student_id')
                : 0,
            'classes' => Schema::hasTable('class_schedules')
                ? DB::table('class_schedules')->count()
                : 0,
            'updates' => Schema::hasTable('news_updates')
                ? NewsUpdate::query()->published()->count()
                : 0,
        ];
    }

    public function home(): View
    {
        $cms = $this->loadCms('home');

        $popularCourses = Schema::hasTable('courses')
            ? $this->activeCoursesQuery()
                ->orderByDesc('id')
                ->limit(8)
                ->get()
            : new Collection();

        $courseTracks = $popularCourses->groupBy(
            fn (Course $course): string => $this->courseTrack($course)
        );

        $upcomingBatches = Schema::hasTable('batches')
            ? Batch::query()
                ->with([
                    'course' => fn ($query) => $query->select([
                        'id',
                        'title',
                        'slug',
                        'thumbnail',
                        'status',
                        'online_discount_price',
                        'offline_discount_price',
                        'discount_price',
                    ]),
                    'mentors:id,name,email,profile_image',
                ])
                ->whereIn('status', ['upcoming', 'running'])
                ->orderBy('start_date')
                ->orderBy('id')
                ->limit(6)
                ->get()
            : new Collection();

        $mentors = Schema::hasTable('mentors')
            ? Mentor::query()
                ->with(['user:id,name,profile_image'])
                ->where('is_active', true)
                ->orderByDesc('id')
                ->limit(8)
                ->get(['id', 'user_id', 'name', 'slug', 'topic', 'bio', 'is_active'])
            : new Collection();

        $reviews = Schema::hasTable('reviews')
            ? Review::query()
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->limit(6)
                ->get(['id', 'name', 'designation', 'quote', 'rating'])
            : new Collection();

        $latestNews = Schema::hasTable('news_updates')
            ? $this->publishedNewsQuery()
                ->limit(3)
                ->get(['id', 'title', 'slug', 'excerpt', 'body', 'image_path', 'published_at', 'created_at'])
            : new Collection();

        $stats = $this->siteStats();

        return view(
            'welcome',
            array_merge(
                $cms,
                compact('popularCourses', 'courseTracks', 'upcomingBatches', 'mentors', 'reviews', 'latestNews', 'stats')
            )
        );
    }

    public function news(): View
    {
        $cms = $this->loadCms('news');

        $newsUpdates = Schema::hasTable('news_updates')
            ? $this->publishedNewsQuery()
                ->paginate(9)
                ->appends(request()->query())
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 9);

        return view('pages.news', array_merge($cms, compact('newsUpdates')));
    }

    public function newsData()
    {
        abort_unless(Schema::hasTable('news_updates'), 404);

        $query = NewsUpdate::query()
            ->published()
            ->select(['id', 'title', 'slug', 'excerpt', 'published_at', 'created_at'])
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        return DataTables::eloquent($query)
            ->addColumn('date', function (NewsUpdate $item) {
                $dt = $item->published_at ?: $item->created_at;

                return $dt ? $dt->format('d M Y') : '';
            })
            ->addColumn('actions', function (NewsUpdate $item) {
                return route('news.show', $item);
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function newsShow(NewsUpdate $newsUpdate): View
    {
        abort_unless($newsUpdate->status === 'published', 404);

        $relatedNews = Schema::hasTable('news_updates')
            ? $this->publishedNewsQuery()
                ->where('id', '!=', $newsUpdate->id)
                ->limit(3)
                ->get(['id', 'title', 'slug', 'excerpt', 'body', 'image_path', 'published_at', 'created_at'])
            : new Collection();

        return view('pages.news-show', compact('newsUpdate', 'relatedNews'));
    }

    public function mentors(): View
    {
        $cms = $this->loadCms('mentors');

        $mentors = Schema::hasTable('mentors')
            ? Mentor::query()
                ->with(['user:id,name,profile_image'])
                ->where('is_active', true)
                ->orderByDesc('id')
                ->paginate(12)
                ->appends(request()->query())
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);

        return view('pages.mentors', array_merge($cms, compact('mentors')));
    }

    public function mentorShow(string $mentor): View|RedirectResponse
    {
        if (ctype_digit($mentor)) {
            $legacyMentor = Mentor::query()->findOrFail((int) $mentor);

            if (is_string($legacyMentor->slug) && $legacyMentor->slug !== '') {
                return redirect()->route('mentors.show', ['mentor' => $legacyMentor->slug], 301);
            }

            $mentor = (string) $legacyMentor->id;
        }

        $mentorQuery = Mentor::query()->where('slug', $mentor);

        if (ctype_digit($mentor)) {
            $mentorQuery->orWhereKey((int) $mentor);
        }

        $mentor = $mentorQuery->firstOrFail();

        abort_unless($mentor->is_active, 404);

        $mentor->loadMissing([
            'user' => fn ($query) => $query
                ->select(['id', 'name', 'email', 'profile_image'])
                ->with([
                    'profile',
                    'address',
                    'educations' => fn ($q) => $q
                        ->orderByDesc('end_year')
                        ->orderByDesc('start_year')
                        ->orderByDesc('id'),
                    'experiences' => fn ($q) => $q
                        ->orderByDesc('end_date')
                        ->orderByDesc('start_date')
                        ->orderByDesc('id'),
                    'skills' => fn ($q) => $q->orderBy('name'),
                ]),
        ]);

        $relatedCourses = new Collection();
        if (Schema::hasTable('courses')) {
            $relatedQuery = $this->activeCoursesQuery();
            $topic = Str::lower((string) $mentor->topic);

            if (Str::contains($topic, ['graphic', 'design'])) {
                $relatedQuery->where(function ($query) {
                    $query->where('title', 'like', '%Graphic%')
                        ->orWhere('title', 'like', '%Design%');
                });
            } elseif (Str::contains($topic, ['marketing'])) {
                $relatedQuery->where('title', 'like', '%Marketing%');
            } elseif (Str::contains($topic, ['hardware', 'network'])) {
                $relatedQuery->where(function ($query) {
                    $query->where('title', 'like', '%Hardware%')
                        ->orWhere('title', 'like', '%Network%');
                });
            } elseif (Str::contains($topic, ['php', '.net', 'developer', 'software'])) {
                $relatedQuery->where(function ($query) {
                    $query->where('title', 'like', '%Development%')
                        ->orWhere('title', 'like', '%.NET%')
                        ->orWhere('title', 'like', '%Web%');
                });
            }

            $relatedCourses = $relatedQuery->limit(3)->get();
        }

        return view('pages.mentor-show', compact('mentor', 'relatedCourses'));
    }

    public function page(string $slug): View
    {
        $cms = $this->loadCms($slug);

        if ($slug === 'courses') {
            $allCourses = Schema::hasTable('courses')
                ? $this->activeCoursesQuery()->orderByDesc('id')->get()
                : new Collection();

            $tracks = $allCourses
                ->map(fn (Course $course): string => $this->courseTrack($course))
                ->unique()
                ->values();

            $query = Schema::hasTable('courses')
                ? $this->activeCoursesQuery()->orderByDesc('id')
                : null;

            $search = trim((string) request('search'));
            if ($query && $search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            $selectedTrack = trim((string) request('track'));
            if ($query && $selectedTrack !== '') {
                $matchingIds = $allCourses
                    ->filter(fn (Course $course): bool => $this->courseTrack($course) === $selectedTrack)
                    ->pluck('id')
                    ->all();

                $query->whereIn('id', $matchingIds ?: [0]);
            }

            $courses = $query
                ? $query->paginate(12)->appends(request()->query())
                : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);

            $stats = $this->siteStats();

            return view('pages.courses', array_merge($cms, compact('courses', 'tracks', 'selectedTrack', 'search', 'stats')));
        }

        if ($slug === 'reviews') {
            $reviews = Schema::hasTable('reviews')
                ? Review::query()
                    ->where('status', 'active')
                    ->orderBy('sort_order')
                    ->orderByDesc('id')
                    ->paginate(12)
                    ->appends(request()->query())
                : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);

            $stats = $this->siteStats();

            return view('pages.reviews', array_merge($cms, compact('reviews', 'stats')));
        }

        if ($slug === 'about') {
            $stats = $this->siteStats();
            $featuredCourses = Schema::hasTable('courses')
                ? $this->activeCoursesQuery()->orderByDesc('id')->limit(4)->get()
                : new Collection();

            return view('pages.about', array_merge($cms, compact('stats', 'featuredCourses')));
        }

        if ($slug === 'news') {
            return $this->news();
        }

        return view('pages.' . $slug, $cms);
    }

    public function course(Course $course): View
    {
        abort_unless($course->status === 'active', 404);

        $course->load([
            'batches' => function ($query) {
                $query
                    ->whereIn('status', ['upcoming', 'running'])
                    ->with(['mentors:id,name,email,profile_image'])
                    ->orderBy('start_date')
                    ->orderBy('id');
            },
        ]);

        $relatedCourses = Schema::hasTable('courses')
            ? $this->activeCoursesQuery()
                ->where('id', '!=', $course->id)
                ->limit(3)
                ->get()
            : new Collection();

        return view('pages.course-show', compact('course', 'relatedCourses'));
    }
}
