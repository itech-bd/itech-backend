<?php

namespace Modules\FrontendEditor\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFrontendSectionRequest;
use App\Http\Requests\Admin\UpdateFrontendSectionRequest;
use App\Models\FrontendPage;
use App\Models\FrontendSetting;
use App\Models\FrontendSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Frontend editor controller.
 *
 * @category Controller
 * @package  App\Http\Controllers\Admin
 * @author   Unknown <unknown@example.invalid>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://laravel.com
 */
class FrontendEditorController extends Controller implements HasMiddleware
{
    /**
     * Define controller middleware.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('role:admin'),
            new Middleware('backend.locale'),
        ];
    }

    /**
     * Display the frontend editor.
     *
     * @param Request $request The incoming request.
     *
     * @return View
     */
    public function index(Request $request): View
    {
        $allowedSlugs = ['home', 'about', 'courses', 'contact'];

        $tab = (string) $request->query('tab', 'pages');
        if (!in_array($tab, ['pages', 'header', 'footer'], true)) {
            $tab = 'pages';
        }

        foreach ($allowedSlugs as $slug) {
            FrontendPage::query()->firstOrCreate(['slug' => $slug]);
        }

        $pages = FrontendPage::query()
            ->whereIn('slug', $allowedSlugs)
            ->orderByRaw("FIELD(slug, 'home', 'about', 'courses', 'contact')")
            ->get();

        $selectedSlug = (string) $request->query('page', 'home');
        if (!in_array($selectedSlug, $allowedSlugs, true)) {
            $selectedSlug = 'home';
        }

        $selectedPage = $pages->firstWhere('slug', $selectedSlug)
            ?: FrontendPage::query()->where('slug', 'home')->firstOrFail();

        $sections = collect();
        if ($tab === 'pages') {
            $sections = FrontendSection::query()
                ->where('frontend_page_id', $selectedPage->id)
                ->orderBy('section_key')
                ->get();
        }

        $settings = collect();
        if (in_array($tab, ['header', 'footer'], true) && Schema::hasTable('frontend_settings')) {
            $settings = FrontendSetting::query()->get()->keyBy('key');
        }

        return view(
            'frontendeditor::admin.frontend-editor.index',
            [
                'pages' => $pages,
                'selectedPage' => $selectedPage,
                'sections' => $sections,
                'allowedSlugs' => $allowedSlugs,
                'tab' => $tab,
                'settings' => $settings,
            ]
        );
    }

    /**
     * Store a new section for a page.
     *
     * @param StoreFrontendSectionRequest $request The validated request.
     * @param FrontendPage                $page    The page model.
     *
     * @return RedirectResponse
     */
    public function store(
        StoreFrontendSectionRequest $request,
        FrontendPage $page
    ): RedirectResponse {
        $validated = $request->validated();

        $hasIconColumn = Schema::hasColumn('frontend_sections', 'icon');

        $data = [
            'frontend_page_id' => $page->id,
            'section_key' => $validated['section_key'],
            'title_bn' => $validated['title_bn'] ?? null,
            'title_en' => $validated['title_en'] ?? null,
            'content_bn' => $validated['content_bn'] ?? null,
            'content_en' => $validated['content_en'] ?? null,
            'button_text_bn' => $validated['button_text_bn'] ?? null,
            'button_text_en' => $validated['button_text_en'] ?? null,
            'button_link' => $validated['button_link'] ?? null,
            'icon' => $hasIconColumn ? ($validated['icon'] ?? null) : null,
            'status' => $validated['status'],
        ];

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $data['image_path'] = $image->store('frontend-sections', 'public');
        }

        FrontendSection::query()->create($data);

        return redirect()
            ->route('admin.frontend-editor.index', ['page' => $page->slug])
            ->with('success', 'Section created successfully.');
    }

    /**
     * Update an existing section.
     *
     * @param UpdateFrontendSectionRequest $request The validated request.
     * @param FrontendSection              $section The section model.
     *
     * @return RedirectResponse
     */
    public function update(
        UpdateFrontendSectionRequest $request,
        FrontendSection $section
    ): RedirectResponse {
        $validated = $request->validated();

        $hasIconColumn = Schema::hasColumn('frontend_sections', 'icon');

        $data = [
            'title_bn' => $validated['title_bn'] ?? null,
            'title_en' => $validated['title_en'] ?? null,
            'content_bn' => $validated['content_bn'] ?? null,
            'content_en' => $validated['content_en'] ?? null,
            'button_text_bn' => $validated['button_text_bn'] ?? null,
            'button_text_en' => $validated['button_text_en'] ?? null,
            'button_link' => $validated['button_link'] ?? null,
            'icon' => $hasIconColumn ? ($validated['icon'] ?? null) : null,
            'status' => $validated['status'],
        ];

        if ($request->hasFile('image')) {
            $oldPath = $section->image_path;
            $image = $request->file('image');
            $data['image_path'] = $image->store('frontend-sections', 'public');

            if (is_string($oldPath) && $oldPath !== '') {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $section->update($data);

        $pageSlug = $section->page ? $section->page->slug : 'home';

        return redirect()
            ->route('admin.frontend-editor.index', ['page' => $pageSlug])
            ->with('success', 'Section updated successfully.');
    }

    /**
     * Delete an existing section.
     *
     * Used by the Skill Tracks UI (AJAX) to remove a track completely.
     */
    public function destroy(Request $request, FrontendSection $section): JsonResponse|RedirectResponse
    {
        $oldImagePath = $section->image_path;
        $pageSlug = $section->page ? $section->page->slug : 'home';

        $section->delete();

        if (is_string($oldImagePath) && $oldImagePath !== '') {
            Storage::disk('public')->delete($oldImagePath);
        }

        if ($request->expectsJson()) {
            return response()
                ->json(['ok' => true])
                ->header('Cache-Control', 'no-store');
        }

        return redirect()
            ->route('admin.frontend-editor.index', ['page' => $pageSlug])
            ->with('success', 'Section deleted successfully.');
    }

    /**
     * Bulk update (upsert) multiple sections for a page.
     *
     * Currently used by the Home tab to edit hero-related fields in a single
     * form.
     *
     * @param Request      $request The incoming request.
     * @param FrontendPage $page    The page model.
     *
     * @return RedirectResponse
     */
    public function bulkUpdate(
        Request $request,
        FrontendPage $page
    ): RedirectResponse {
        $hasIconColumn = Schema::hasColumn('frontend_sections', 'icon');

        $iconRule = [
            'nullable',
            'string',
            'max:80',
            // Supports legacy keys and Font Awesome classes like: "fa-solid fa-code".
            'regex:/^(code|search|dotnet|design|sparkles|rocket|chart|shield|fa-(solid|regular|brands)\s+fa-[a-z0-9-]+)$/',
        ];

        $fixedKeys = [
            // Shared / generic
            'hero',

            'hero_cta_primary',
            'hero_emphasis',
            'hero_paragraph',
            'hero_primary',
            'hero_side_heading',

            'home_about_title',
            'home_about_subtitle',
            'home_about_card_1',
            'home_about_card_2',
            'home_about_card_3',

            'home_skill_tracks_title',
            'home_skill_tracks_subtitle',
            'home_skill_tracks_cta',

            // About page
            'about_intro',
            'about_mission',
            'about_vision',
            'about_value_1',
            'about_value_2',
            'about_value_3',
            'about_value_4',
            'about_value_5',
            'about_value_6',
            'about_stats_title',
            'about_stat_1',
            'about_stat_2',
            'about_stat_3',
            'about_stat_4',
            'about_cta',
        ];

        $validated = $request->validate(
            [
                'sections' => ['required', 'array'],
                'sections.*' => ['array'],

                'sections.*.title_bn' => ['nullable', 'string', 'max:255'],
                'sections.*.title_en' => ['nullable', 'string', 'max:255'],
                'sections.*.content_bn' => ['nullable', 'string'],
                'sections.*.content_en' => ['nullable', 'string'],
                'sections.*.icon' => $iconRule,
                'sections.*.button_text_bn' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'sections.*.button_text_en' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'sections.*.button_link' => ['nullable', 'string', 'max:255'],
                'sections.*.status' => [
                    'required',
                    Rule::in(['active', 'inactive']),
                ],
            ],
            [],
            [
                'sections.*.title_bn' => 'Bangla title',
                'sections.*.title_en' => 'English title',
                'sections.*.content_bn' => 'Bangla content',
                'sections.*.content_en' => 'English content',
                'sections.*.icon' => 'Icon',
                'sections.*.button_text_bn' => 'Bangla button text',
                'sections.*.button_text_en' => 'English button text',
                'sections.*.button_link' => 'Button link',
                'sections.*.status' => 'Status',
            ]
        );

        $sectionsPayload = $validated['sections'] ?? [];

        foreach ($sectionsPayload as $sectionKey => $payload) {
            $sectionKey = (string) $sectionKey;

            $isFixed = in_array($sectionKey, $fixedKeys, true);
            $isHeroReason = false;
            $isSkillTrack = false;

            if (preg_match('/^hero_different_reason_(\d+)$/', $sectionKey, $m)) {
                $index = (int) $m[1];
                $isHeroReason = $index >= 1 && $index <= 50;
            }

            if (preg_match('/^home_skill_track_(\d+)$/', $sectionKey, $m)) {
                $index = (int) $m[1];
                $isSkillTrack = $index >= 1 && $index <= 20;
            }

            if (! $isFixed && ! $isHeroReason && ! $isSkillTrack) {
                continue;
            }

            $payload = is_array($payload) ? $payload : [];

            $updateData = [
                'title_bn' => $payload['title_bn'] ?? null,
                'title_en' => $payload['title_en'] ?? null,
                'content_bn' => $payload['content_bn'] ?? null,
                'content_en' => $payload['content_en'] ?? null,
                'button_text_bn' => $payload['button_text_bn'] ?? null,
                'button_text_en' => $payload['button_text_en'] ?? null,
                'button_link' => $payload['button_link'] ?? null,
                'status' => $payload['status'] ?? FrontendSection::STATUS_ACTIVE,
            ];

            // Backward-compatible: only update icon if it's present in payload.
            if ($hasIconColumn && array_key_exists('icon', $payload)) {
                $updateData['icon'] = $payload['icon'] ?: null;
            }

            FrontendSection::query()->updateOrCreate(
                [
                    'frontend_page_id' => $page->id,
                    'section_key' => $sectionKey,
                ],
                $updateData
            );
        }

        $successMessage = 'Sections updated successfully.';

        if ($page->slug === 'about' && ! empty($sectionsPayload)) {
            $successMessage = 'About page sections updated successfully.';
        }

        if ($page->slug === 'home' && ! empty($sectionsPayload)) {
            $hasHero = false;
            $hasAbout = false;
            $hasSkillTracks = false;
            $hasOther = false;

            foreach (array_keys($sectionsPayload) as $key) {
                $key = (string) $key;

                if (str_starts_with($key, 'hero_') || str_starts_with($key, 'hero_different_reason_')) {
                    $hasHero = true;
                    continue;
                }

                if (str_starts_with($key, 'home_about_')) {
                    $hasAbout = true;
                    continue;
                }

                if (str_starts_with($key, 'home_skill_tracks_') || str_starts_with($key, 'home_skill_track_')) {
                    $hasSkillTracks = true;
                    continue;
                }

                $hasOther = true;
            }

            $groupCount = (int) $hasHero + (int) $hasAbout + (int) $hasSkillTracks;
            if (! $hasOther && $groupCount === 1) {
                if ($hasHero) {
                    $successMessage = 'Home hero sections updated successfully.';
                } elseif ($hasAbout) {
                    $successMessage = 'Home about sections updated successfully.';
                } elseif ($hasSkillTracks) {
                    $successMessage = 'Home skill tracks updated successfully.';
                }
            }
        }

        return redirect()
            ->route('admin.frontend-editor.index', ['page' => $page->slug])
            ->with('success', $successMessage);
    }

    /**
     * Return all free Font Awesome icons (from local metadata) for the icon picker.
     *
     * @return JsonResponse
     */
    public function fontAwesomeIcons(): JsonResponse
    {
        $candidatePaths = [
            public_path('vendor/fontawesome/metadata/icon-families.json'),
            base_path('node_modules/@fortawesome/fontawesome-free/metadata/icon-families.json'),
        ];

        $metadataPath = null;
        foreach ($candidatePaths as $path) {
            if (is_string($path) && $path !== '' && file_exists($path)) {
                $metadataPath = $path;
                break;
            }
        }

        if (!is_string($metadataPath)) {
            return response()
                ->json(['icons' => []], 404)
                ->header('Cache-Control', 'private, max-age=300');
        }

        $raw = file_get_contents($metadataPath);
        if (!is_string($raw) || $raw === '') {
            return response()
                ->json(['icons' => []], 500)
                ->header('Cache-Control', 'private, max-age=60');
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return response()
                ->json(['icons' => []], 500)
                ->header('Cache-Control', 'private, max-age=60');
        }

        $styleToPrefix = [
            'solid' => 'fa-solid',
            'regular' => 'fa-regular',
            'brands' => 'fa-brands',
        ];

        $icons = [];
        foreach ($data as $name => $meta) {
            if (!is_string($name) || $name === '') {
                continue;
            }
            if (!preg_match('/^[a-z0-9-]+$/', $name)) {
                continue;
            }

            $meta = is_array($meta) ? $meta : [];
            $label = isset($meta['label']) && is_string($meta['label']) ? $meta['label'] : str_replace('-', ' ', $name);
            $freeFamilies = $meta['familyStylesByLicense']['free'] ?? [];
            if (!is_array($freeFamilies) || count($freeFamilies) === 0) {
                continue;
            }

            $availableStyles = [];
            foreach ($freeFamilies as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $family = $row['family'] ?? null;
                $style = $row['style'] ?? null;

                // Our UI currently supports the classic FA CSS prefixes only.
                if ($family !== 'classic') {
                    continue;
                }

                if (!is_string($style) || !array_key_exists($style, $styleToPrefix)) {
                    continue;
                }

                $availableStyles[$style] = true;
            }

            foreach (array_keys($availableStyles) as $style) {
                $prefix = $styleToPrefix[$style];
                $class = $prefix . ' fa-' . $name;

                $styleSuffix = '';
                if ($style === 'brands') {
                    $styleSuffix = ' (Brand)';
                } elseif ($style === 'regular') {
                    $styleSuffix = ' (Regular)';
                }

                $icons[] = [
                    'class' => $class,
                    'label' => $label . $styleSuffix,
                ];
            }
        }

        usort($icons, static function ($a, $b) {
            $aLabel = is_array($a) && isset($a['label']) ? (string) $a['label'] : '';
            $bLabel = is_array($b) && isset($b['label']) ? (string) $b['label'] : '';
            return strcasecmp($aLabel, $bLabel);
        });

        return response()
            ->json(['icons' => $icons])
            ->header('Cache-Control', 'private, max-age=86400');
    }
}
