<?php

namespace Imanghafoori\Tags;

use Imanghafoori\Tags\Console\Commands\TestTempTags;
use Imanghafoori\Tags\Console\Commands\DeleteExpiredBans;
use Imanghafoori\Tags\Models\TempTag;
use Imanghafoori\Tags\Observers\TempTagObserver;
use Illuminate\Support\ServiceProvider;

class TempTagServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerConsoleCommands();
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(): void
    {
        $this->configure();
        $this->registerPublishes();
//        $this->registerObservers();
    }

    /**
     * Register console commands.
     *
     * @return void
     */
    protected function registerConsoleCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->app->bind('command.tag:delete-expired', DeleteExpiredBans::class);

            $this->commands([
                'command.tag:delete-expired',
                TestTempTags::class
            ]);
        }
    }

    /**
     * Register Ban's models observers.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function registerObservers(): void
    {
        $this->app->make(TempTag::class)->observe(new TempTagObserver);
    }

    /**
     * Setup the resource publishing groups for Ban.
     *
     * @return void
     */
    protected function registerPublishes(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/temp_tag.php' => config_path('tag.php'),
            ], 'tag-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'migrations');
        }

        $this->registerMigrations();
    }

    /**
     * Register the Temporary Tag migrations.
     *
     * @return void
     */
    private function registerMigrations(): void
    {
        if ($this->app->runningInConsole() && $this->shouldLoadDefaultMigrations()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    /**
     * Merge Temporary Tag configuration with the application configuration.
     *
     * @return void
     */
    private function configure(): void
    {
        if (!$this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/../config/temp_tag.php', 'tag');
        }
    }

    /**
     * Determine if we should register default migrations.
     *
     * @return bool
     */
    private function shouldLoadDefaultMigrations(): bool
    {
        return config('tag.load_default_migrations', true);
    }
}
