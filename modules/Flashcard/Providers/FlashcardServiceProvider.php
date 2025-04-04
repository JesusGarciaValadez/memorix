<?php

declare(strict_types=1);

namespace Modules\Flashcard\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class FlashcardServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'flashcard');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }
}
