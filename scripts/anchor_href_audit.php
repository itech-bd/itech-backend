<?php

declare(strict_types=1);

/**
 * Anchor href audit for Blade templates.
 *
 * Usage:
 *   php scripts/anchor_href_audit.php
 *   php scripts/anchor_href_audit.php --write-safe
 *
 * Output:
 *   storage/app/anchor-href-report.json
 *   storage/app/anchor-href-report.csv
 */

$root = realpath(__DIR__ . '/..');
if ($root === false) {
    fwrite(STDERR, "Unable to resolve project root.\n");
    exit(1);
}

$writeSafe = in_array('--write-safe', $argv, true);

$routeListPath = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'route-list.json';
if (!is_file($routeListPath)) {
    fwrite(STDERR, "Missing storage/app/route-list.json. Generate it via: php artisan route:list --json > storage/app/route-list.json\n");
    exit(1);
}

$routeJson = file_get_contents($routeListPath);
if ($routeJson === false) {
    fwrite(STDERR, "Failed to read route list JSON.\n");
    exit(1);
}

// Windows PowerShell redirection (>) often writes UTF-16LE. json_decode expects UTF-8.
if (str_starts_with($routeJson, "\xFF\xFE")) {
    $routeJson = mb_convert_encoding($routeJson, 'UTF-8', 'UTF-16LE');
} elseif (str_starts_with($routeJson, "\xFE\xFF")) {
    $routeJson = mb_convert_encoding($routeJson, 'UTF-8', 'UTF-16BE');
} elseif (str_contains(substr($routeJson, 0, 200), "\x00")) {
    // Heuristic: lots of NUL bytes near the start implies UTF-16 without BOM.
    $routeJson = mb_convert_encoding($routeJson, 'UTF-8', 'UTF-16LE');
}

$routes = json_decode($routeJson, true);
if (!is_array($routes)) {
    fwrite(STDERR, "Invalid route list JSON.\n");
    exit(1);
}

$nameToUri = [];
foreach ($routes as $r) {
    if (!is_array($r)) {
        continue;
    }

    $name = $r['name'] ?? null;
    $uri = $r['uri'] ?? null;

    if (!is_string($name) || $name === '' || !is_string($uri)) {
        continue;
    }

    // prefer the first occurrence if duplicates exist
    if (!array_key_exists($name, $nameToUri)) {
        $nameToUri[$name] = $uri;
    }
}

$bladeRoots = [
    $root . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views',
    $root . DIRECTORY_SEPARATOR . 'Modules',
];

function iter_blade_files(array $roots): Generator
{
    foreach ($roots as $dir) {
        if (!is_dir($dir)) {
            continue;
        }

        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($it as $fileInfo) {
            /** @var SplFileInfo $fileInfo */
            if (!$fileInfo->isFile()) {
                continue;
            }

            $path = $fileInfo->getPathname();
            if (!str_ends_with($path, '.blade.php')) {
                continue;
            }

            // Only views under resources/views
            if (str_contains($path, DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR)) {
                yield $path;
            }
        }
    }
}

function classify_href(string $hrefRaw): string
{
    $h = trim($hrefRaw);
    if ($h === '' || $h === '""' || $h === "''") {
        return 'empty';
    }
    if (preg_match('~^\#~', $h)) {
        return 'hash';
    }
    if (preg_match('~^(https?:)?//~i', $h)) {
        return 'external';
    }
    if (preg_match('~^(mailto:|tel:)~i', $h)) {
        return 'contact';
    }
    if (preg_match('~^javascript:~i', $h)) {
        return 'javascript';
    }
    if (preg_match('~\broute\s*\(~', $h)) {
        return 'route';
    }
    if (preg_match('~\burl\s*\(~', $h)) {
        return 'url';
    }
    if (preg_match('~\basset\s*\(~', $h)) {
        return 'asset';
    }

    return 'static-or-other';
}

function to_public_path(string $uri): string
{
    $uri = trim($uri);
    if ($uri === '' || $uri === '/') {
        return '/';
    }
    return '/' . ltrim($uri, '/');
}

function offset_to_line(string $content, int $offset): int
{
    if ($offset <= 0) {
        return 1;
    }
    return substr_count(substr($content, 0, $offset), "\n") + 1;
}

$report = [];
$changedFiles = [];

// Capture <a ... href=...> with single/double quotes (incl Blade expressions inside quotes), or bare Blade expressions.
$anchorHrefRegex = '~<a\b[^>]*\bhref\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|({!!.*?!!}|{{.*?}}))~si';

foreach (iter_blade_files($bladeRoots) as $filePath) {
    $content = file_get_contents($filePath);
    if ($content === false) {
        continue;
    }

    if (!preg_match_all($anchorHrefRegex, $content, $m, PREG_OFFSET_CAPTURE)) {
        continue;
    }

    $fileRel = str_replace($root . DIRECTORY_SEPARATOR, '', $filePath);

    $replacements = [];

    foreach ($m[0] as $idx => $wholeMatch) {
        $offset = $wholeMatch[1];
        $line = offset_to_line($content, $offset);

        $hrefRaw = $m[1][$idx][0] !== '' ? $m[1][$idx][0]
            : ($m[2][$idx][0] !== '' ? $m[2][$idx][0]
                : $m[3][$idx][0]);

        $type = classify_href($hrefRaw);

        $routeName = null;
        $resolvedPath = null;
        $safeToReplace = false;

        if ($type === 'route') {
            // safe: route('name') with no 2nd arg
            if (preg_match('~\broute\(\s*[\'\"]([^\'\"]+)[\'\"]\s*\)~', $hrefRaw, $rm)) {
                $routeName = $rm[1];
                if (isset($nameToUri[$routeName])) {
                    $resolvedPath = to_public_path($nameToUri[$routeName]);
                    $safeToReplace = true;
                }
            } else {
                // still capture the name when possible, even if parameterized
                if (preg_match('~\broute\(\s*[\'\"]([^\'\"]+)[\'\"]~', $hrefRaw, $rm)) {
                    $routeName = $rm[1];
                    if (isset($nameToUri[$routeName])) {
                        $resolvedPath = to_public_path($nameToUri[$routeName]);
                    }
                }
            }
        }

        $report[] = [
            'file' => str_replace('\\', '/', $fileRel),
            'line' => $line,
            'href_raw' => $hrefRaw,
            'type' => $type,
            'route_name' => $routeName,
            'resolved_uri_template' => $resolvedPath,
            'safe_to_replace' => $safeToReplace,
        ];

        if ($writeSafe && $safeToReplace) {
            // Only replace the exact Blade pattern inside quotes: {{ route('name') }} or {!! route('name') !!}
            // We don't touch other expressions.
            $safeExprRegex = '~^(?:{{\s*route\(\s*[\'\"]' . preg_quote($routeName ?? '', '~') . '[\'\"]\s*\)\s*}}|{!!\s*route\(\s*[\'\"]' . preg_quote($routeName ?? '', '~') . '[\'\"]\s*\)\s*!!})$~s';
            if ($routeName !== null && preg_match($safeExprRegex, trim($hrefRaw))) {
                $replacements[] = [
                    'offset' => $offset,
                    'old' => $hrefRaw,
                    'new' => $resolvedPath,
                ];
            }
        }
    }

    if ($writeSafe && count($replacements) > 0) {
        // Apply replacements from back to front so offsets stay valid.
        usort($replacements, fn ($a, $b) => $b['offset'] <=> $a['offset']);

        $newContent = $content;
        foreach ($replacements as $r) {
            $old = $r['old'];
            $new = $r['new'];
            // Replace only the first occurrence after this offset by doing a global str_replace on exact text.
            // This is safe because we're only replacing the inner href content that was captured.
            $newContent = preg_replace('~' . preg_quote($old, '~') . '~', $new, $newContent, 1);
        }

        if ($newContent !== null && $newContent !== $content) {
            file_put_contents($filePath, $newContent);
            $changedFiles[] = str_replace('\\', '/', $fileRel);
        }
    }
}

$outDir = $root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app';
@mkdir($outDir, 0777, true);

$jsonOut = $outDir . DIRECTORY_SEPARATOR . 'anchor-href-report.json';
file_put_contents($jsonOut, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$csvOut = $outDir . DIRECTORY_SEPARATOR . 'anchor-href-report.csv';
$fp = fopen($csvOut, 'wb');
if ($fp !== false) {
    fputcsv($fp, ['file', 'line', 'type', 'href_raw', 'route_name', 'resolved_uri_template', 'safe_to_replace']);
    foreach ($report as $row) {
        fputcsv($fp, [
            $row['file'],
            (string) $row['line'],
            $row['type'],
            $row['href_raw'],
            (string) ($row['route_name'] ?? ''),
            (string) ($row['resolved_uri_template'] ?? ''),
            $row['safe_to_replace'] ? '1' : '0',
        ]);
    }
    fclose($fp);
}

fwrite(STDOUT, "Wrote: storage/app/anchor-href-report.json\n");
fwrite(STDOUT, "Wrote: storage/app/anchor-href-report.csv\n");

if ($writeSafe) {
    fwrite(STDOUT, "Updated files: " . count($changedFiles) . "\n");
}
