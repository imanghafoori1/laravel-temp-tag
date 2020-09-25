<?php

namespace Imanghafoori\Tags;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Imanghafoori\Tags\Console\Commands\DeleteExpiredBans;
use Imanghafoori\Tags\Console\Commands\TestTempTags;
use Imanghafoori\Tags\Models\TempTag;
use Imanghafoori\Tags\Observers\TempTagObserver;

class TempTagServiceProvider extends ServiceProvider
{
    public static $registeredRelation = [];

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConsoleCommands();

        $this->registerEloquentMacros();
    }

    public function boot()
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
    protected function registerConsoleCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->app->bind('command.tag:delete-expired', DeleteExpiredBans::class);

            $this->commands([
                'command.tag:delete-expired',
                TestTempTags::class,
            ]);
        }
    }

    protected function registerObservers()
    {
        $this->app->make(TempTag::class)->observe(new TempTagObserver());
    }

    /**
     * Setup the resource publishing groups for Ban.
     *
     * @return void
     */
    protected function registerPublishes()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/temp_tag.php' => config_path('tag.php'),
            ], 'tag-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'migrations');
        }

        $this->registerMigrations();
    }

    /**
     * Register the Temporary Tag migrations.
     *
     * @return void
     */
    private function registerMigrations()
    {
        if ($this->app->runningInConsole() && $this->shouldLoadDefaultMigrations()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Merge Temporary Tag configuration with the application configuration.
     *
     * @return void
     */
    private function configure()
    {
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/temp_tag.php', 'tag');
        }
    }

    private function shouldLoadDefaultMigrations()
    {
        return config('tag.load_default_migrations', true);
    }

    static function registerRelationship($q)
    {
        $table = $q->getModel()->getTable();
        if (! in_array($table, TempTagServiceProvider::$registeredRelation)) {
            TempTagServiceProvider::$registeredRelation[] = $table;
            Relation::morphMap([$table => get_class($q->getModel())]);
        }
    }

    static function getClosure($title): \Closure
    {
        return function ($q) use ($title) {
            $q->whereIn('title', (array) $title);
        };
    }

    private function registerEloquentMacros()
    {
        Builder::macro('hasActiveTempTags', function ($title) {
            TempTagServiceProvider::registerRelationship($this);

            return $this->whereHas('activeTempTags', TempTagServiceProvider::getClosure($title));
        });

        Builder::macro('hasNotActiveTempTags', function ($title) {
            TempTagServiceProvider::registerRelationship($this);

            return $this->whereDoesntHave('activeTempTags', TempTagServiceProvider::getClosure($title));
        });

        Builder::macro('hasExpiredTempTags', function ($title) {
            TempTagServiceProvider::registerRelationship($this);

            return $this->whereHas('expiredTempTags', TempTagServiceProvider::getClosure($title));
        });

        Builder::macro('hasNotExpiredTempTags', function ($title) {
            TempTagServiceProvider::registerRelationship($this);

            return $this->whereDoesntHave('expiredTempTags', TempTagServiceProvider::getClosure($title));
        });

        Builder::macro('hasTempTags', function ($title) {
            TempTagServiceProvider::registerRelationship($this);

            return $this->whereHas('tempTags', TempTagServiceProvider::getClosure($title));
        });

        Builder::macro('hasNotTempTags', function ($title) {
            TempTagServiceProvider::registerRelationship($this);

            return $this->whereDoesntHave('tempTags', TempTagServiceProvider::getClosure($title));
        });
    }
}
