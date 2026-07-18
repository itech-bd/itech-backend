<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PublicMediaController extends Controller
{
    public function show(string $path): Response
    {
        $normalized = trim(str_replace('\\', '/', $path), '/');

        if ($normalized === '' || Str::contains($normalized, ['../', '..\\'])) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if ($disk->exists($normalized)) {
            $absolutePath = $disk->path($normalized);
            $mime = mime_content_type($absolutePath) ?: 'application/octet-stream';

            return response()->file($absolutePath, [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }

        return response($this->placeholderSvg($normalized), 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    private function placeholderSvg(string $path): string
    {
        $isProfile = Str::startsWith($path, 'profile-images/');
        $label = $isProfile ? 'Mentor Image' : 'Course Image';
        $subLabel = $isProfile ? 'Image backup needed' : 'Thumbnail backup needed';
        $width = $isProfile ? 600 : 1200;
        $height = $isProfile ? 600 : 750;
        $radius = $isProfile ? 36 : 42;
        $titleSize = $isProfile ? 40 : 58;
        $subtitleSize = $isProfile ? 20 : 28;

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$width} {$height}" width="{$width}" height="{$height}">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#0F4AA3" />
      <stop offset="62%" stop-color="#1C63D5" />
      <stop offset="100%" stop-color="#FF7A1A" />
    </linearGradient>
    <linearGradient id="card" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.96" />
      <stop offset="100%" stop-color="#FFF5EA" stop-opacity="0.92" />
    </linearGradient>
  </defs>
  <rect width="100%" height="100%" rx="{$radius}" fill="url(#bg)" />
  <circle cx="88%" cy="18%" r="90" fill="#FFFFFF" fill-opacity="0.08" />
  <circle cx="14%" cy="78%" r="120" fill="#FFFFFF" fill-opacity="0.08" />
  <rect x="7%" y="10%" width="86%" height="80%" rx="{$radius}" fill="url(#card)" />
  <rect x="11%" y="16%" width="26%" height="10" rx="5" fill="#FF7A1A" fill-opacity="0.82" />
  <rect x="11%" y="20%" width="36%" height="10" rx="5" fill="#0F4AA3" fill-opacity="0.9" />
  <rect x="11%" y="26%" width="22%" height="10" rx="5" fill="#1C63D5" fill-opacity="0.82" />
  <text x="50%" y="52%" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="{$titleSize}" font-weight="800" fill="#0B1F43">{$label}</text>
  <text x="50%" y="61%" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="{$subtitleSize}" font-weight="600" fill="#FF7A1A">{$subLabel}</text>
  <text x="50%" y="71%" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="{$subtitleSize}" font-weight="500" fill="#4A5E82">Add original uploads into storage/app/public</text>
</svg>
SVG;
    }
}
