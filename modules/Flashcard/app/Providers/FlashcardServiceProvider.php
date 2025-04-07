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
use Modules\Flashcard\app\Repositories\Eloquent\UserRepository;
use Modules\Flashcard\app\Repositories\FlashcardRepositoryInterface;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\app\Repositories\PracticeResultRepositoryInterface;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;
use Modules\Flashcard\app\Repositories\UserRepositoryInterface;
use Modules\Flashcard\app\Services\LogService;
use Modules\Flashcard\app\Services\LogServiceInterface;
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
        ]);
        $this->loadTranslationsFrom(__DIR__.'/../../Resources/lang');
        $this->loadMigrationsFrom(__DIR__.'/../../Database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../Routes/web.php');

        $this->registerPolicies();
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
        $this->registerRepositories();
        $this->registerServices();
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
        $this->app->bind(FlashcardRepositoryInterface::class, FlashcardRepository::class);
        $this->app->bind(LogRepositoryInterface::class, LogRepository::class);
        $this->app->bind(PracticeResultRepositoryInterface::class, PracticeResultRepository::class);
        $this->app->bind(StatisticRepositoryInterface::class, StatisticRepository::class);
        $this->app->bind(StudySessionRepositoryInterface::class, StudySessionRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
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
        $this->app->bind(LogServiceInterface::class, LogService::class);

        $this->app->bind(StudySessionService::class, function ($app) {
            return new StudySessionService(
                $app->make(StudySessionRepositoryInterface::class),
                $app->make(FlashcardRepositoryInterface::class),
                $app->make(LogRepositoryInterface::class),
                $app->make(StatisticRepositoryInterface::class)
            );
        });
    }
}
