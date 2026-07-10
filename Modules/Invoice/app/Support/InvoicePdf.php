<?php

namespace Modules\Invoice\Support;

use App\Models\FrontendSetting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Course\Models\CourseOrder;

class InvoicePdf
{
    public static function download(CourseOrder $order, User $user)
    {
        return Pdf::loadView(
            'invoice::pdf.document',
            [
                'order' => $order,
                'user' => $user,
                'watermarkLogoDataUri' => static::watermarkLogoDataUri(),
                'logoDataUri' => static::logoDataUri(),
            ]
        )
            ->setPaper('a4')
            ->download('invoice-' . $order->id . '.pdf');
    }

    private static function logoDataUri(): ?string
    {
        $relativePath = FrontendSetting::where('key', 'site_logo_path')->value('value_en');
        if (! $relativePath) {
            return null;
        }

        $path = storage_path('app/public/' . $relativePath);
        if (! is_file($path)) {
            return null;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    private static function watermarkLogoDataUri(): ?string
    {
        $path = public_path('brand/itechbd-logo.svg');
        if (! is_file($path)) {
            return null;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return null;
        }

        return 'data:image/svg+xml;base64,' . base64_encode($contents);
    }
}