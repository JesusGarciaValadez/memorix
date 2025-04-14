<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Modules\Flashcard\app\Helpers\ConsoleRenderer;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Models\Statistic;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Policies\FlashcardPolicy;
use Modules\Flashcard\app\Policies\LogPolicy;
use Modules\Flashcard\app\Policies\StatisticPolicy;
use Modules\Flashcard\app\Policies\StudySessionPolicy;
use Modules\Flashcard\app\Repositories\Eloquent\FlashcardRepository;
use Modules\Flashcard\app\Repositories\Eloquent\LogRepository;
use Modules\Flashcard\app\Repositories\Eloquent\PracticeResultRepository;
use Modules\Flashcard\app\Repositories\Eloquent\StatisticRepository;
use Modules\Flashcard\app\Repositories\Eloquent\StudySessionRepository;
use Modules\Flashcard\app\Repositories\FlashcardRepositoryInterface;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\app\Repositories\PracticeResultRepositoryInterface;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;
use Modules\Flashcard\app\Services\FlashcardCommandService;
use Modules\Flashcard\app\Services\FlashcardCommandServiceInterface;
use Modules\Flashcard\app\Services\FlashcardService;
use Modules\Flashcard\app\Services\LogService;
use Modules\Flashcard\app\Services\LogServiceInterface;
use Modules\Flashcard\app\Services\StatisticService;
use Modules\Flashcard\app\Services\StatisticServiceInterface;
use Modules\Flashcard\app\Services\StudySessionService;

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
            \Modules\Flashcard\app\Console\Commands\FlashcardImportCommand::class,
        ]);
        $this->loadTranslationsFrom(__DIR__.'/../../Resources/lang');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../Routes/web.php');
        $this->registerPolicies();
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->registerRepositories();
        $this->registerServices();
        $this->registerHelpers();
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

    /**
     * Register repositories.
     */
    private function registerRepositories(): void
    {
        $this->app->singleton(FlashcardRepositoryInterface::class, FlashcardRepository::class);
        $this->app->singleton(LogRepositoryInterface::class, LogRepository::class);
        $this->app->singleton(PracticeResultRepositoryInterface::class, PracticeResultRepository::class);
        $this->app->singleton(StatisticRepositoryInterface::class, StatisticRepository::class);
        $this->app->singleton(StudySessionRepositoryInterface::class, StudySessionRepository::class);
    }

    /**
     * Register the module's policies.
     */
    private function registerPolicies(): void
    {
        Gate::policy(Flashcard::class, FlashcardPolicy::class);
        Gate::policy(Log::class, LogPolicy::class);
        Gate::policy(Statistic::class, StatisticPolicy::class);
        Gate::policy(StudySession::class, StudySessionPolicy::class);
    }

    /**
     * Register services.
     */
    private function registerServices(): void
    {
        $this->app->singleton(FlashcardService::class);
        $this->app->singleton(StudySessionService::class);
        $this->app->singleton(LogService::class);
        $this->app->singleton(LogServiceInterface::class, LogService::class);
        $this->app->singleton(StatisticService::class);
        $this->app->singleton(StatisticServiceInterface::class, StatisticService::class);
        $this->app->singleton(FlashcardCommandService::class);
        $this->app->singleton(FlashcardCommandServiceInterface::class, FlashcardCommandService::class);
    }

    /**
     * Register helpers.
     */
    private function registerHelpers(): void
    {
        $this->app->singleton(ConsoleRendererInterface::class, ConsoleRenderer::class);
    }
}
