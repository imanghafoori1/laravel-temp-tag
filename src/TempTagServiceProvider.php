<?php

namespace Imanghafoori\Tags;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Imanghafoori\Tags\Console\Commands\DeleteExpiredBans;

class TempTagServiceProvider extends ServiceProvider
{
    public static $registeredRelation = [];

    public function register()
    {
        $this->registerConsoleCommands();

        $this->registerEloquentMacros();
    }

    public function boot()
    {
        $this->configure();
        $this->registerPublishes();
    }

    protected function registerConsoleCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->app->bind('command.tag:delete-expired', DeleteExpiredBans::class);

            $this->commands([
                'command.tag:delete-expired',
            ]);
        }
    }

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

    private function registerMigrations()
    {
        if ($this->app->runningInConsole() && $this->shouldLoadDefaultMigrations()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

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

    public static function registerRelationship($q)
    {
        $table = $q->getModel()->getTable();
        if (! in_array($table, TempTagServiceProvider::$registeredRelation)) {
            TempTagServiceProvider::$registeredRelation[] = $table;
            Relation::morphMap([$table => get_class($q->getModel())]);
        }
    }

    public static function getClosure($title, $payload)
    {
        return function ($q) use ($title, $payload) {
            $q->whereIn('title', (array) $title);
            if ($payload) {
                $q->where('payload->'.$payload[0], $payload[1]);
            }
        };
    }

    private function registerEloquentMacros()
    {
        Builder::macro('hasActiveTempTags', $this->whereHasClosure('activeTempTags'));

        Builder::macro('hasNotActiveTempTags', $this->whereHasNotClosure('activeTempTags'));

        Builder::macro('hasExpiredTempTags', $this->whereHasClosure('expiredTempTags'));

        Builder::macro('hasNotExpiredTempTags', $this->whereHasNotClosure('expiredTempTags'));

        Builder::macro('hasTempTags', $this->whereHasClosure('tempTags'));

        Builder::macro('hasNotTempTags', $this->whereHasNotClosure('tempTags'));
    }

    private function whereHasClosure($relation)
    {
        return function ($title, $payload) use ($relation) {
            TempTagServiceProvider::registerRelationship($this);

            return $this->whereHas($relation, TempTagServiceProvider::getClosure($title, $payload));
        };
    }

    private function whereHasNotClosure($relation)
    {
        return function ($title, $payload) use ($relation) {
            TempTagServiceProvider::registerRelationship($this);

            return $this->whereDoesntHave($relation, TempTagServiceProvider::getClosure($title, $payload));
        };
    }
}
