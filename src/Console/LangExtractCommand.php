<?php

namespace Redot\LaravelLangExtractor\Console;

use Illuminate\Console\Command;
use Redot\LaravelLangExtractor\LangExtractor;

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
     * Execute the console command.
     */
    public function handle(): void
    {
        $language = $this->argument('language');
        $path = lang_path($language . '.json');

        $extractor = new LangExtractor();
        $extractor->extract()->mergeWithFile($path)->save($path, true);

        $this->info('Translations saved to ' . str_replace(base_path(), '', $path));
    }
}
