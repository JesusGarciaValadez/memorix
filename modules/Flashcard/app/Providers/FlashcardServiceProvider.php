<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

final class FlashcardServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../Resources/views', 'flashcard');
        $this->commands([
            \Modules\Flashcard\app\Console\Commands\FlashcardInteractiveCommand::class,
            \Modules\Flashcard\app\Console\Commands\FlashcardRegisterCommand::class,
            /*\Modules\Flashcard\app\Console\Commands\FlashcardDeleteCommand::class,
            \Modules\Flashcard\app\Console\Commands\FlashcardEditCommand::class,
            \Modules\Flashcard\app\Console\Commands\FlashcardListCommand::class,
            \Modules\Flashcard\app\Console\Commands\FlashcardReviewCommand::class,
            \Modules\Flashcard\app\Console\Commands\FlashcardStudyCommand::class,*/
        ]);
        $this->loadTranslationsFrom(__DIR__.'/../../Resources/lang');
        $this->loadMigrationsFrom(__DIR__.'/../../Database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../Routes/web.php');
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->configureCommands();
        $this->configureModels();
        $this->configureDates();
        $this->configureUrls();
        $this->configureVite();
    }

    /**
     * Configure the application's commands.
     */
    private function configureCommands(): void
    {
        DB::prohibitDestructiveCommands(
            $this->app->isProduction(),
        );
    }

    /**
     * Configure the application's dates.
     */
    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    /**
     * Configure the application's models.
     */
    private function configureModels(): void
    {
        Model::unguard();

        Model::shouldBeStrict();
    }

    /**
     * Configure the application's URLs.
     */
    private function configureUrls(): void
    {
        URL::forceScheme('https');
    }

    /**
     * Configure the application's Vite instance.
     */
    private function configureVite(): void
    {
        Vite::useAggressivePrefetching();
    }
}
