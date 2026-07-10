<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\FrontendPage;
use App\Models\FrontendSection;
use App\Models\FrontendSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Batch\Models\Batch;
use Modules\ContactMessages\Http\Requests\StoreContactMessageRequest;
use Modules\ContactMessages\Models\ContactMessage;
use Modules\Course\Models\Course;
use Modules\Mentors\Models\Mentor;
use Modules\NewsUpdates\Models\NewsUpdate;
use Modules\Reviews\Models\Review;

class PublicSiteController extends ApiController
{
    private const PAGE_SLUGS = [
        'home',
        'about',
        'courses',
        'mentors',
        'reviews',
        'news',
        'software-solutions',
        'it-solutions',
        'web-hosting-solutions',
        'privacy',
        'terms',
        'contact',
    ];

    public function bootstrap(): JsonResponse
    {
        return $this->success([
            'locale' => app()->getLocale(),
            'settings' => $this->settings(),
            'navigation' => $this->navigation(),
            'footer_navigation' => $this->footerNavigation(),
            'auth' => [
                'registration_enabled' => true,
                'email_verification_required' => true,
            ],
        ]);
    }

    public function home(): JsonResponse
    {
        $popularCourses = Schema::hasTable('courses')
            ? $this->activeCoursesQuery()->latest('id')->limit(8)->get()
            : new Collection();

        $upcomingBatches = Schema::hasTable('batches')
            ? Batch::query()
                ->with([
                    'course:id,title,slug,thumbnail,status,old_price,discount_price,online_old_price,online_discount_price,offline_old_price,offline_discount_price',
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
                ->with('user:id,name,email,profile_image')
                ->where('is_active', true)
                ->latest('id')
                ->limit(8)
                ->get()
            : new Collection();

        $reviews = Schema::hasTable('reviews')
            ? Review::query()
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->latest('id')
                ->limit(6)
                ->get()
            : new Collection();

        $latestNews = Schema::hasTable('news_updates')
            ? $this->publishedNewsQuery()->limit(3)->get()
            : new Collection();

        return $this->success([
            'page' => $this->pagePayload('home'),
            'stats' => $this->siteStats(),
            'popular_courses' => $popularCourses->map(fn (Course $course) => $this->coursePayload($course, true))->values(),
            'course_tracks' => $popularCourses
                ->groupBy(fn (Course $course): string => $this->courseTrack($course))
                ->map(fn ($courses, string $track) => [
                    'name' => $track,
                    'course_ids' => $courses->pluck('id')->values(),
                ])->values(),
            'upcoming_batches' => $upcomingBatches->map(fn (Batch $batch) => $this->batchPayload($batch))->values(),
            'mentors' => $mentors->map(fn (Mentor $mentor) => $this->mentorPayload($mentor))->values(),
            'reviews' => $reviews->map(fn (Review $review) => $this->reviewPayload($review))->values(),
            'latest_news' => $latestNews->map(fn (NewsUpdate $item) => $this->newsPayload($item))->values(),
        ]);
    }

    public function page(string $slug): JsonResponse
    {
        abort_unless(in_array($slug, self::PAGE_SLUGS, true), 404);

        $data = ['page' => $this->pagePayload($slug)];

        if ($slug === 'about') {
            $data['stats'] = $this->siteStats();
            $data['featured_courses'] = Schema::hasTable('courses')
                ? $this->activeCoursesQuery()->latest('id')->limit(4)->get()
                    ->map(fn (Course $course) => $this->coursePayload($course, true))->values()
                : [];
        }

        if (in_array($slug, ['courses', 'reviews'], true)) {
            $data['stats'] = $this->siteStats();
        }

        return $this->success($data);
    }

    public function courses(Request $request): JsonResponse
    {
        abort_unless(Schema::hasTable('courses'), 404);

        $allActive = Course::query()
            ->where('status', 'active')
            ->get(['id', 'title']);
        $tracks = $allActive
            ->map(fn (Course $course): string => $this->courseTrack($course))
            ->unique()
            ->values();

        $query = $this->activeCoursesQuery()->latest('id');
        $search = trim((string) $request->query('search', ''));
        $track = trim((string) $request->query('track', ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($track !== '') {
            $ids = $allActive
                ->filter(fn (Course $course): bool => $this->courseTrack($course) === $track)
                ->pluck('id');
            $query->whereIn('id', $ids->isEmpty() ? [0] : $ids->all());
        }

        $paginator = $query->paginate($this->perPage($request, 12));

        return $this->success([
            ...$this->paginated($paginator, fn (Course $course) => $this->coursePayload($course, true)),
            'filters' => [
                'search' => $search,
                'track' => $track,
                'tracks' => $tracks,
            ],
        ]);
    }

    public function course(Course $course): JsonResponse
    {
        abort_unless($course->status === 'active', 404);

        $course->load([
            'batches' => fn ($query) => $query
                ->whereIn('status', ['upcoming', 'running'])
                ->with('mentors:id,name,email,profile_image')
                ->orderBy('start_date')
                ->orderBy('id'),
        ]);

        $related = $this->activeCoursesQuery()
            ->where('id', '!=', $course->id)
            ->limit(3)
            ->get();

        return $this->success([
            'course' => $this->coursePayload($course, true, true),
            'related_courses' => $related->map(fn (Course $item) => $this->coursePayload($item, true))->values(),
        ]);
    }

    public function mentors(Request $request): JsonResponse
    {
        abort_unless(Schema::hasTable('mentors'), 404);

        $query = Mentor::query()
            ->with('user:id,name,email,profile_image')
            ->where('is_active', true)
            ->latest('id');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('topic', 'like', "%{$search}%")
                    ->orWhere('bio', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($this->perPage($request, 12));

        return $this->success([
            ...$this->paginated($paginator, fn (Mentor $mentor) => $this->mentorPayload($mentor)),
            'filters' => ['search' => $search],
        ]);
    }

    public function mentor(string $mentor): JsonResponse
    {
        abort_unless(Schema::hasTable('mentors'), 404);

        $query = Mentor::query()->where('slug', $mentor);
        if (ctype_digit($mentor)) {
            $query->orWhereKey((int) $mentor);
        }

        $model = $query->firstOrFail();
        abort_unless($model->is_active, 404);

        $model->load([
            'user' => fn ($query) => $query
                ->select(['id', 'name', 'email', 'profile_image'])
                ->with([
                    'profile',
                    'address',
                    'educations' => fn ($q) => $q->orderByDesc('end_year')->orderByDesc('start_year'),
                    'experiences' => fn ($q) => $q->orderByDesc('end_date')->orderByDesc('start_date'),
                    'skills' => fn ($q) => $q->orderBy('name'),
                ]),
        ]);

        $related = $this->relatedCoursesForMentor($model);

        return $this->success([
            'mentor' => $this->mentorPayload($model, true),
            'related_courses' => $related->map(fn (Course $course) => $this->coursePayload($course, true))->values(),
        ]);
    }

    public function publicProfile(string $publicUrl): JsonResponse
    {
        $user = User::query()
            ->whereHas('profile', fn ($query) => $query->where('public_url', $publicUrl))
            ->with([
                'profile',
                'address',
                'educations' => fn ($query) => $query
                    ->orderByDesc('end_year')
                    ->orderByDesc('start_year')
                    ->orderByDesc('id'),
                'experiences' => fn ($query) => $query
                    ->orderByDesc('end_date')
                    ->orderByDesc('start_date')
                    ->orderByDesc('id'),
                'skills' => fn ($query) => $query->orderBy('name'),
            ])
            ->firstOrFail();

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_image_url' => $user->profile_image_url,
            ],
            'details' => $user->profile,
            'address' => $user->address,
            'educations' => $user->educations,
            'experiences' => $user->experiences,
            'skills' => $user->skills->map(fn ($skill) => [
                'id' => $skill->id,
                'name' => $skill->name,
                'proficiency_level' => $skill->pivot?->proficiency_level,
            ])->values(),
        ]);
    }

    public function reviews(Request $request): JsonResponse
    {
        abort_unless(Schema::hasTable('reviews'), 404);

        $paginator = Review::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate($this->perPage($request, 12));

        return $this->success($this->paginated(
            $paginator,
            fn (Review $review) => $this->reviewPayload($review)
        ));
    }

    public function news(Request $request): JsonResponse
    {
        abort_unless(Schema::hasTable('news_updates'), 404);

        $query = $this->publishedNewsQuery();
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($this->perPage($request, 9));

        return $this->success([
            ...$this->paginated($paginator, fn (NewsUpdate $item) => $this->newsPayload($item)),
            'filters' => ['search' => $search],
        ]);
    }

    public function newsItem(NewsUpdate $newsUpdate): JsonResponse
    {
        abort_unless($newsUpdate->status === 'published', 404);

        $related = $this->publishedNewsQuery()
            ->where('id', '!=', $newsUpdate->id)
            ->limit(3)
            ->get();

        return $this->success([
            'news' => $this->newsPayload($newsUpdate, true),
            'related_news' => $related->map(fn (NewsUpdate $item) => $this->newsPayload($item))->values(),
        ]);
    }

    public function contact(StoreContactMessageRequest $request): JsonResponse
    {
        $data = $request->validated();

        $message = ContactMessage::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
        ]);

        return $this->success([
            'message_id' => $message->id,
        ], 'Thanks for contacting us. We have received your message.', 201);
    }

    private function activeCoursesQuery(): Builder
    {
        return Course::query()
            ->where('status', 'active')
            ->with([
                'batches' => fn ($query) => $query
                    ->whereIn('status', ['upcoming', 'running'])
                    ->orderBy('start_date')
                    ->orderBy('id'),
            ]);
    }

    private function publishedNewsQuery(): Builder
    {
        return NewsUpdate::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    private function pagePayload(string $slug): array
    {
        if (! Schema::hasTable('frontend_pages') || ! Schema::hasTable('frontend_sections')) {
            return ['slug' => $slug, 'sections' => []];
        }

        $page = FrontendPage::query()->where('slug', $slug)->first();
        if (! $page) {
            return ['slug' => $slug, 'sections' => []];
        }

        $sections = FrontendSection::query()
            ->where('frontend_page_id', $page->id)
            ->active()
            ->orderBy('id')
            ->get();

        return [
            'id' => $page->id,
            'slug' => $page->slug,
            'sections' => $sections->map(fn (FrontendSection $section) => [
                'id' => $section->id,
                'key' => $section->section_key,
                'title' => $section->title,
                'content' => $section->content,
                'image_url' => $this->publicAssetUrl($section->image_path),
                'icon' => $section->icon,
                'button_text' => $section->button_text,
                'button_link' => $section->button_link,
            ])->values(),
        ];
    }

    private function settings(): array
    {
        $defaults = FrontendSetting::defaultValues(app()->getLocale());
        if (! Schema::hasTable('frontend_settings')) {
            return $this->normalizeSettingAssets($defaults);
        }

        $keyed = FrontendSetting::getCachedKeyed();
        $values = [];
        foreach ($defaults as $key => $default) {
            $setting = $keyed->get($key);
            $values[$key] = $setting ? $setting->localizedValue() : $default;
        }

        return $this->normalizeSettingAssets($values);
    }

    private function normalizeSettingAssets(array $settings): array
    {
        $settings['site_logo_url'] = $this->publicAssetUrl($settings['site_logo_path'] ?? null);
        $settings['site_favicon_url'] = $this->publicAssetUrl($settings['site_favicon_path'] ?? null);

        unset($settings['site_logo_path'], $settings['site_favicon_path']);

        return $settings;
    }

    private function navigation(): array
    {
        $isBangla = app()->getLocale() === 'bn';

        return [
            ['key' => 'home', 'label' => __('frontend.home'), 'href' => '/'],
            ['key' => 'about', 'label' => __('frontend.about'), 'href' => '/about'],
            ['key' => 'courses', 'label' => __('frontend.courses'), 'href' => '/courses'],
            [
                'key' => 'solutions',
                'label' => $isBangla ? 'সমাধান' : 'Solutions',
                'href' => '/solutions/software-solutions',
                'children' => [
                    [
                        'key' => 'software-solutions',
                        'label' => $isBangla ? 'সফটওয়্যার সল্যুশন' : 'Software Solutions',
                        'href' => '/solutions/software-solutions',
                    ],
                    [
                        'key' => 'it-solutions',
                        'label' => $isBangla ? 'আইটি সল্যুশন' : 'IT Solutions',
                        'href' => '/solutions/it-solutions',
                    ],
                    [
                        'key' => 'web-hosting-solutions',
                        'label' => $isBangla ? 'ওয়েব হোস্টিং সল্যুশন' : 'Web Hosting Solutions',
                        'href' => '/solutions/web-hosting-solutions',
                    ],
                ],
            ],
            ['key' => 'mentors', 'label' => __('frontend.mentors'), 'href' => '/mentors'],
            ['key' => 'reviews', 'label' => __('frontend.reviews'), 'href' => '/reviews'],
            ['key' => 'news', 'label' => __('frontend.news'), 'href' => '/news'],
            ['key' => 'contact', 'label' => __('frontend.contact'), 'href' => '/contact'],
        ];
    }

    private function footerNavigation(): array
    {
        return [
            ['label' => 'Course', 'href' => '/courses'],
            ['label' => 'Workshop', 'href' => '/courses'],
            ['label' => 'Event', 'href' => '/news'],
            ['label' => 'Archive', 'href' => '/news'],
            ['label' => 'Team', 'href' => '/mentors'],
            ['label' => 'About Us', 'href' => '/about'],
            ['label' => 'Our Vision & Mission', 'href' => '/about'],
            ['label' => 'Trainer', 'href' => '/mentors'],
            ['label' => 'Student Review', 'href' => '/reviews'],
            ['label' => 'Career', 'href' => '/about'],
            ['label' => 'FAQ', 'href' => '/contact'],
            ['label' => 'Privacy & Policy', 'href' => '/privacy'],
            ['label' => 'Terms & Conditions', 'href' => '/terms'],
        ];
    }

    private function siteStats(): array
    {
        return [
            'courses' => Schema::hasTable('courses') ? Course::query()->where('status', 'active')->count() : 0,
            'mentors' => Schema::hasTable('mentors') ? Mentor::query()->where('is_active', true)->count() : 0,
            'batches' => Schema::hasTable('batches') ? Batch::query()->whereIn('status', ['upcoming', 'running'])->count() : 0,
            'students' => Schema::hasTable('batch_students')
                ? DB::table('batch_students')->where('status', 'approved')->distinct('student_id')->count('student_id')
                : 0,
            'classes' => Schema::hasTable('class_schedules') ? DB::table('class_schedules')->count() : 0,
            'updates' => Schema::hasTable('news_updates') ? NewsUpdate::query()->published()->count() : 0,
        ];
    }

    private function courseTrack(Course $course): string
    {
        $title = Str::lower((string) $course->title);

        return match (true) {
            Str::contains($title, ['graphic', 'design']) => 'Graphic & Multimedia',
            Str::contains($title, ['marketing', 'seo', 'digital']) => 'Digital Marketing',
            Str::contains($title, ['hardware', 'network']) => 'Hardware & Networking',
            Str::contains($title, ['web', '.net', 'dotnet', 'software', 'development']) => 'Web & Software',
            default => 'Professional Skill',
        };
    }

    private function coursePayload(Course $course, bool $includeBatches = false, bool $includeDescription = false): array
    {
        $payload = [
            'id' => $course->id,
            'slug' => $course->slug,
            'title' => $course->title,
            'track' => $this->courseTrack($course),
            'thumbnail_url' => $course->thumbnail_url,
            'status' => $course->status,
            'pricing' => [
                'old_price' => $this->decimal($course->old_price),
                'discount_price' => $this->decimal($course->discount_price),
                'online_old_price' => $this->decimal($course->online_old_price),
                'online_discount_price' => $this->decimal($course->online_discount_price),
                'offline_old_price' => $this->decimal($course->offline_old_price),
                'offline_discount_price' => $this->decimal($course->offline_discount_price),
                'currency' => 'BDT',
            ],
        ];

        if ($includeDescription) {
            $payload['description'] = $course->description;
        }

        if ($includeBatches) {
            $payload['batches'] = $course->relationLoaded('batches')
                ? $course->batches->map(fn (Batch $batch) => $this->batchPayload($batch, false))->values()
                : [];
        }

        return $payload;
    }

    private function batchPayload(Batch $batch, bool $includeCourse = true): array
    {
        $payload = [
            'id' => $batch->id,
            'name' => $batch->name,
            'status' => $batch->status,
            'start_date' => $batch->start_date?->toDateString(),
            'end_date' => $batch->end_date?->toDateString(),
            'class_days' => $batch->class_days ?: [],
            'class_time' => $batch->class_time,
            'mentors' => $batch->relationLoaded('mentors')
                ? $batch->mentors->map(fn ($mentor) => [
                    'id' => $mentor->id,
                    'name' => $mentor->name,
                    'email' => $mentor->email,
                    'profile_image_url' => $mentor->profile_image_url,
                ])->values()
                : [],
        ];

        if ($includeCourse && $batch->relationLoaded('course') && $batch->course) {
            $payload['course'] = $this->coursePayload($batch->course);
        }

        return $payload;
    }

    private function mentorPayload(Mentor $mentor, bool $detailed = false): array
    {
        $payload = [
            'id' => $mentor->id,
            'slug' => $mentor->public_route_key,
            'name' => $mentor->name,
            'topic' => $mentor->topic,
            'bio' => $mentor->bio,
            'profile_image_url' => $mentor->user?->profile_image_url,
        ];

        if ($detailed && $mentor->user) {
            $user = $mentor->user;
            $payload['email'] = $user->email;
            $payload['profile'] = $user->profile;
            $payload['address'] = $user->address;
            $payload['educations'] = $user->educations;
            $payload['experiences'] = $user->experiences;
            $payload['skills'] = $user->skills->map(fn ($skill) => [
                'id' => $skill->id,
                'name' => $skill->name,
                'proficiency_level' => $skill->pivot?->proficiency_level,
            ])->values();
        }

        return $payload;
    }

    private function reviewPayload(Review $review): array
    {
        return [
            'id' => $review->id,
            'name' => $review->name,
            'designation' => $review->designation,
            'quote' => $review->quote,
            'rating' => (int) $review->rating,
        ];
    }

    private function newsPayload(NewsUpdate $item, bool $includeBody = false): array
    {
        $payload = [
            'id' => $item->id,
            'slug' => $item->slug,
            'title' => $item->title,
            'excerpt' => $item->excerpt,
            'image_url' => $this->publicAssetUrl($item->image_path),
            'published_at' => $item->published_at?->toIso8601String(),
            'created_at' => $item->created_at?->toIso8601String(),
        ];

        if ($includeBody) {
            $payload['body'] = $item->body;
        }

        return $payload;
    }

    private function relatedCoursesForMentor(Mentor $mentor): Collection
    {
        if (! Schema::hasTable('courses')) {
            return new Collection();
        }

        $query = $this->activeCoursesQuery();
        $topic = Str::lower((string) $mentor->topic);

        if (Str::contains($topic, ['graphic', 'design'])) {
            $query->where(fn ($builder) => $builder->where('title', 'like', '%Graphic%')->orWhere('title', 'like', '%Design%'));
        } elseif (Str::contains($topic, ['marketing'])) {
            $query->where('title', 'like', '%Marketing%');
        } elseif (Str::contains($topic, ['hardware', 'network'])) {
            $query->where(fn ($builder) => $builder->where('title', 'like', '%Hardware%')->orWhere('title', 'like', '%Network%'));
        } elseif (Str::contains($topic, ['php', '.net', 'developer', 'software'])) {
            $query->where(fn ($builder) => $builder
                ->where('title', 'like', '%Development%')
                ->orWhere('title', 'like', '%.NET%')
                ->orWhere('title', 'like', '%Web%'));
        }

        return $query->limit(3)->get();
    }

    private function decimal(mixed $value): ?float
    {
        return is_null($value) ? null : (float) $value;
    }

    private function perPage(Request $request, int $default): int
    {
        return min(max((int) $request->integer('per_page', $default), 1), 50);
    }
}
