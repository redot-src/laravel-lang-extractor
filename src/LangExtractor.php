<?php

namespace Redot\LaravelLangExtractor;

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;

class LangExtractor
{
    /**
     * Pattern to match translation strings.
     */
    protected string $pattern;

    /**
     * Directories to search for blade files.
     */
    protected array $directories = [];

    /**
     * File extensions to search for.
     */
    protected array $extensions = ['php'];

    /**
     * Translations extracted from blade files.
     */
    protected array $translations = [];

    /**
     * Create a new instance.
     */
    public function __construct($directories = [])
    {
        $this->directories = $directories;

        if (count($this->directories) === 0) {
            $this->searchIn(resource_path(), app_path('Http'), app_path('Livewire'));
        }

        $this->pattern = $this->generatePatternUsing('__', 'trans', '@lang');
    }

    /**
     * Set the directories to search for blade files.
     */
    public function searchIn(string ...$directories): static
    {
        foreach ($directories as $directory) {
            if (! in_array($directory, $this->directories)) {
                $this->directories[] = $directory;
            }
        }

        return $this;
    }

    /**
     * Set the file extensions to search for.
     */
    public function withExtensions(string ...$extensions): static
    {
        foreach ($extensions as $extension) {
            $extension = ltrim(strtolower($extension), '.');

            if (! in_array($extension, $this->extensions)) {
                $this->extensions[] = $extension;
            }
        }

        return $this;
    }

    /**
     * Get the pattern to match translation strings.
     */
    protected function generatePatternUsing(string ...$functions): string
    {
        $ignore = glob(lang_path(config('app.fallback_locale')) . '/*.php');
        $ignore = array_map(fn ($file) => basename($file, '.php') . '\.[^\s]', $ignore);

        return '/(?:' . implode('|', $functions) . ")\((['\"])(?<translation>(?!" . implode('|', $ignore) . ")(?:[^']|\\\')+?)(?<!\\\\)\\1/s";
    }

    /**
     * Get all translations from blade files.
     */
    public function extract(): static
    {
        $translations = collect();

        $files = Finder::create()->files()->ignoreVCSIgnored(true);
        $files->in($this->directories)->name('*.' . implode(', *.', $this->extensions));

        foreach ($files as $file) {
            preg_match_all($this->pattern, $file->getContents(), $matches);

            $translations = $translations->merge($matches['translation']);
        }

        $replacements = ['\"' => '"', '\\\'' => '\''];
        $translations = $translations->map(fn ($translation) => trim(strtr($translation, $replacements)));

        $this->translations = $translations->filter()->unique()->values()->toArray();
        $this->translations = array_combine($this->translations, $this->translations);

        return $this;
    }

    /**
     * Merge translations with existing translations.
     */
    public function mergeWithFile(string $path): static
    {
        $old = File::exists($path) ? json_decode(File::get($path), true) : [];

        if (count($old) > 0) {
            $this->translations = array_merge($this->translations, $old);
            ksort($this->translations);
        }

        return $this;
    }

    /**
     * Merge translations with existing translations.
     */
    public function mergeWithArray(array $translations): static
    {
        $this->translations = array_merge($this->translations, $translations);
        ksort($this->translations);

        return $this;
    }

    /**
     * Get all translations.
     */
    public function all(): array
    {
        return $this->translations;
    }

    /**
     * Save translations to a language file.
     */
    public function save(string $path, bool $force = false): int|bool
    {
        if (File::exists($path) && ! $force) {
            return false;
        }

        File::delete($path);

        $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        return File::put($path, json_encode($this->translations, $flags));
    }
}
