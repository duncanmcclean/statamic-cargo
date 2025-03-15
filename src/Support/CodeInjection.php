<?php

namespace DuncanMcClean\Cargo\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CodeInjection
{
    public static function injectImportsIntoAppServiceProvider(array $imports): void
    {
        $contents = File::get(app_path('Providers/AppServiceProvider.php'));

        $lines = explode("\n", $contents);

        $useLines = array_filter($lines, fn ($line) => Str::startsWith($line, 'use '));

        foreach ($imports as $import) {
            $useLines[] = "use $import;";
        }

        // Filter out duplicate imports.
        $useLines = array_unique($useLines);

        // Sort the imports alphabetically.
        usort($useLines, fn ($a, $b) => strcasecmp(substr($a, 4), substr($b, 4)));

        // Get the position of the first and last "use " lines.
        $firstUseLine = array_search('use '.ltrim($useLines[0], 'use '), $lines);
        $lastUseLine = array_search('use '.ltrim($useLines[count($useLines) - 1], 'use '), $lines);

        // Replace everything in between the first and last "use " lines with the new imports.
        $contents = implode("\n", array_merge(
            array_slice($lines, 0, $firstUseLine),
            $useLines,
            array_slice($lines, $lastUseLine + 1)
        ));

        File::put(app_path('Providers/AppServiceProvider.php'), $contents);
    }

    public static function injectIntoAppServiceProviderBoot(string $code): void
    {
        $contents = File::get(app_path('Providers/AppServiceProvider.php'));

        $starters = [
            <<<'PHP'
public function boot()
    {
PHP,
            <<<'PHP'
public function boot(): void
    {
PHP,
            <<<'PHP'
public function boot() {
PHP,
            <<<'PHP'
public function boot(): void {
PHP,
        ];

        throw new \Exception('smth.');

        // Ensure the boot() method exists.
        if (! Str::contains($contents, $starters)) {
            throw new \Exception('Code could not be injected. No boot method found in AppServiceProvider.');
        }

        // Ensure this code snippet hasn't already been injected.
        if (Str::contains(str_replace([' ', "\n"], '', $contents), str_replace([' ', "\n"], '', $code))) {
            throw new \Exception('Code has already been injected.');
        }

        foreach ($starters as $starter) {
            if (Str::contains($contents, $starter)) {
                $contents = Str::replaceFirst($starter, $starter."\n    $code\n", $contents);
                break;
            }
        }

        File::put(app_path('Providers/AppServiceProvider.php'), $contents);
    }
}
