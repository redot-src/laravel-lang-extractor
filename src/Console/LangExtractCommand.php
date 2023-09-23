<?php

namespace Redot\LaravelLangExtractor\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;

class LangExtractCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:extract {language=en}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract translation strings from blade files';

    /**
     * Pattern to match translation strings.
     */
    protected string $pattern;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->pattern = $this->getPatternFrom('__', 'trans', '@lang');
    }

    /**
     * Get the pattern to match translation strings.
     */
    protected function getPatternFrom(string ...$functions): string
    {
        $ignore = glob(lang_path(config('app.fallback_locale')).'/*.php');
        $ignore = array_map(fn ($file) => basename($file, '.php').'\.[^\s]', $ignore);

        return '/(?:'.implode('|', $functions).")\((['\"])(?<translation>(?!".implode('|', $ignore).")(?:[^']|\\\')+?)(?<!\\\\)\\1/s";
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $language = $this->argument('language');

        $translations = $this->getTranslations();

        $this->saveTranslations($language, $translations);
    }

    /**
     * Get all translations from blade files.
     */
    protected function getTranslations(): array
    {
        $translations = collect();

        $files = Finder::create()->files()->ignoreVCSIgnored(true);
        $files->in(resource_path())->in(app_path('Http'))->in(app_path('Livewire'))->name('*.php');

        foreach ($files as $file) {
            preg_match_all($this->pattern, $file->getContents(), $matches);

            $translations = $translations->merge($matches['translation']);
        }

        $replacements = ['\"' => '"', '\\\'' => '\''];
        $translations = $translations->map(fn ($translation) => trim(strtr($translation, $replacements)));

        return $translations->filter()->unique()->values()->toArray();
    }

    /**
     * Save translations to a language file.
     */
    protected function saveTranslations(string $language, array $translations): void
    {
        $path = lang_path($language.'.json');

        $translations = array_combine($translations, $translations);
        $old = File::exists($path) ? json_decode(File::get($path), true) : [];

        if (count($old) > 0) {
            $translations = array_merge($translations, $old);

            File::delete($path);
        }

        ksort($translations);

        $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        File::put($path, json_encode($translations, $flags));

        $this->info('Translations saved to '.str_replace(base_path(), '', $path));
    }
}
