<?php

namespace Webkul\Google\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Webkul\Google\Console\Commands\Install;
use Webkul\Google\Jobs\PeriodicSynchronizations;
use Webkul\Google\Jobs\RefreshWebhookSynchronizations;
use Webkul\User\Contracts\User;
use Webkul\Google\Models\User as GoogleUser;

class GoogleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        if (class_exists(\Diglactic\Breadcrumbs\Breadcrumbs::class)) {
            require __DIR__ . '/../Routes/breadcrumbs.php';
        }

        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'google');

        Blade::anonymousComponentPath(__DIR__ . '/../Resources/views/components', 'google');


        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'google');

        Event::listen('admin.layout.head.after', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('google::components.layouts.style');
        });

        Event::listen('admin.components.activities.actions.activity.form_controls.modal.content.controls.after', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('google::leads.view.activities.create');
        });

        Event::listen('admin.activities.edit.form_controls.after', function ($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('google::activities.google');
        });

        $this->overridesModels();

        $this->registerProviders();

        $this->publishAssets();

        $this->registerScheduledJobs();
    }

    /**
     * Register the package's scheduled jobs.
     *
     * @return void
     */
    protected function registerScheduledJobs()
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->job(new PeriodicSynchronizations())->everyFifteenMinutes();

            $schedule->job(new RefreshWebhookSynchronizations())->daily();
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();

        $this->registerCommands();
    }

    /**
     * Register the console commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Install::class,
            ]);
        }
    }

    /**
     * Overrides models
     *
     * @return void
     */
    public function overridesModels()
    {
        $this->app->concord->registerModel(
            User::class,
            GoogleUser::class
        );
    }

    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/menu.php',
            'menu.admin'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/acl.php',
            'acl'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/krayin-vite.php', 
            'krayin-vite.viters'
        );
    }

    /**
     * Register the providers.
     */
    protected function registerProviders(): void
    {
        $this->app->register(ModuleServiceProvider::class);

        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Publish the assets.
     */
    protected function publishAssets(): void
    {
        $this->publishes([
            __DIR__ . '/../../publishable/assets'                                            => public_path('google'),
            __DIR__ . '/../Resources/views/components/activities/actions/activity.blade.php' => resource_path('views/vendor/admin/components/activities/actions/activity.blade.php'),
            __DIR__ . '/../Resources/views/activities/edit.blade.php'                        => resource_path('views/vendor/admin/activities/edit.blade.php'),
        ], 'public');
    }
}
