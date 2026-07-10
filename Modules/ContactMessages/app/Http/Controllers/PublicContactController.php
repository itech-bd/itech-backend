<?php

namespace Modules\ContactMessages\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FrontendPage;
use App\Models\FrontendSection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\ContactMessages\Http\Requests\StoreContactMessageRequest;
use Modules\ContactMessages\Models\ContactMessage;

class PublicContactController extends Controller
{
    /**
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

            /**
             * @var Collection<string, FrontendSection> $cmsSectionsByKey
             */
            $cmsSectionsByKey = new Collection();

            return compact('cmsPage', 'cmsSections', 'cmsSectionsByKey');
        }

        $cmsPage = FrontendPage::query()->firstOrCreate(['slug' => $slug]);

        $cmsSections = FrontendSection::query()
            ->where('frontend_page_id', $cmsPage->id)
            ->active()
            ->orderBy('section_key')
            ->get();

        /**
         * @var Collection<string, FrontendSection> $cmsSectionsByKey
         */
        $cmsSectionsByKey = $cmsSections->keyBy('section_key');

        return compact('cmsPage', 'cmsSections', 'cmsSectionsByKey');
    }

    public function show(): View
    {
        return view('contactmessages::public.contact', $this->loadCms('contact'));
    }

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        $data = $request->validated();

        ContactMessage::query()->create([
            ...$data,
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
        ]);

        return redirect()
            ->route('contact')
            ->with('contact_success', 'Thanks for contacting us. We have received your message.')
            ->withFragment('contact-form');
    }
}
