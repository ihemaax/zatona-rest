<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ExtractTranslations extends Command
{
    protected $signature = 'translations:extract';
    protected $description = 'Extract visible texts from Blade and PHP files into lang files';

    protected array $ignoredPatterns = [
        '/^\s*$/u',
        '/^[\d\W_]+$/u',
        '/^(true|false|null)$/i',
        '/^(GET|POST|PUT|PATCH|DELETE)$/i',
        '/^(rtl|ltr)$/i',
        '/^(ar|en)$/i',
        '/^#[0-9a-fA-F]{3,6}$/',
        '/^\{\{.*\}\}$/u',
        '/^\@\w+/u',
        '/^<.*>$/u',
    ];

    public function handle(): int
    {
        $viewPath = resource_path('views');
        $appPath  = app_path();

        $files = collect()
            ->merge(File::allFiles($viewPath))
            ->merge(File::allFiles($appPath))
            ->filter(function ($file) {
                return in_array($file->getExtension(), ['php', 'blade.php']);
            });

        $strings = [];

        foreach ($files as $file) {
            $content = File::get($file->getPathname());

            $cleaned = $this->stripCodeNoise($content);

            $matches = [];
            preg_match_all('/>([^<>@{}][^<>]{1,200})</u', $cleaned, $matches);

            foreach ($matches[1] as $text) {
                $text = $this->normalizeText($text);

                if ($this->shouldSkip($text)) {
                    continue;
                }

                $key = $this->makeKey($text);

                if (!isset($strings[$key])) {
                    $strings[$key] = $text;
                }
            }
        }

        ksort($strings);

        $this->writeLangFile(lang_path('en/generated.php'), $strings, false);
        $this->writeLangFile(lang_path('ar/generated.php'), $strings, true);

        $this->info('Translations extracted successfully.');
        $this->info('EN file: ' . lang_path('en/generated.php'));
        $this->info('AR file: ' . lang_path('ar/generated.php'));

        return self::SUCCESS;
    }

    protected function stripCodeNoise(string $content): string
    {
        $content = preg_replace('/<script\b[^>]*>.*?<\/script>/is', ' ', $content);
        $content = preg_replace('/<style\b[^>]*>.*?<\/style>/is', ' ', $content);
        $content = preg_replace('/\{\{.*?\}\}/s', ' ', $content);
        $content = preg_replace('/\{!!.*?!!\}/s', ' ', $content);
        $content = preg_replace('/@php.*?@endphp/s', ' ', $content);
        $content = preg_replace('/<\?php.*?\?>/s', ' ', $content);

        return $content;
    }

    protected function normalizeText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($text);
        $text = preg_replace('/\s+/u', ' ', $text);
        return trim($text);
    }

    protected function shouldSkip(string $text): bool
    {
        if (mb_strlen($text) < 2) {
            return true;
        }

        foreach ($this->ignoredPatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }

   protected function makeKey(string $text): string
{
    // لو النص فيه حروف إنجليزية، حاول نطلع منه key مقروء
    if (preg_match('/[A-Za-z]/', $text)) {
        $base = \Illuminate\Support\Str::of($text)
            ->lower()
            ->replaceMatches('/[^a-z0-9\s]/', '')
            ->replace(' ', '_')
            ->trim('_')
            ->limit(60, '')
            ->value();

        if (!empty($base)) {
            return $base;
        }
    }

    // لو النص عربي أو مختلط، استخدم key ثابت بدون فرنكو
    return 'text_' . substr(md5($text), 0, 10);
}

    protected function writeLangFile(string $path, array $strings, bool $arabic): void
    {
        $output = "<?php\n\nreturn [\n";

        foreach ($strings as $key => $value) {
$translatedValue = $arabic ? '' : (preg_match('/[\x{0600}-\x{06FF}]/u', $value) ? '' : $value);            $output .= "    '{$key}' => '" . addslashes($translatedValue) . "',\n";
        }

        $output .= "];\n";

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $output);
    }
}