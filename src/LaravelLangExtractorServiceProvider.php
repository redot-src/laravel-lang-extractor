<?php

namespace Redot\LaravelLangExtractor;

use Illuminate\Support\ServiceProvider;

class LaravelLangExtractorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->commands([
            Console\LangExtractCommand::class,
        ]);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // ...
    }
}
