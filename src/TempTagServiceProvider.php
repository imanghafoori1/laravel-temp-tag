<?php

namespace Imanghafoori\Tags;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Imanghafoori\Tags\Console\Commands\DeleteExpiredBans;

class TempTagServiceProvider extends ServiceProvider
{
    public static $registeredRelation = [];

    public function register()
    {
        config()->set('cache.stores.temp_tag', ['driver' => 'file', 'path' => storage_path('framework/temp_tag'),]);
        $this->registerEloquentMacros();
        $this->registerConsoleCommands();
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

            $this->commands(['command.tag:delete-expired']);
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

    private function whereHasClosure($relation, $method)
    {
        return function ($title, $payload = []) use ($relation, $method) {
            TempTagServiceProvider::registerRelationship($this);

            return $this->$method($relation, TempTagServiceProvider::getClosure($title, $payload));
        };
    }

    public static function getClosure($title, $payload)
    {
        return function ($q) use ($title, $payload) {
            if (is_string($title) && Str::contains($title, ['*'])) {
                $title = str_replace('*', '%', $title);
                $q->where('title', 'like', $title);
            } else {
                $q->whereIn('title', (array) $title);
            }

            foreach ($payload as $key => $value) {
                $q->where('payload->'.$key, $value);
            }
        };
    }

    private function registerEloquentMacros()
    {
        Builder::macro('orHasActiveTags', $this->whereHasClosure('activeTempTags', 'orWhereHas'));
        Builder::macro('hasActiveTags', $this->whereHasClosure('activeTempTags', 'whereHas'));

        Builder::macro('orHasNotActiveTags', $this->whereHasClosure('activeTempTags', 'orWhereDoesntHave'));
        Builder::macro('hasNotActiveTags', $this->whereHasClosure('activeTempTags', 'whereDoesntHave'));

        Builder::macro('orHasExpiredTags', $this->whereHasClosure('expiredTempTags', 'orWhereHas'));
        Builder::macro('hasExpiredTags', $this->whereHasClosure('expiredTempTags', 'whereHas'));

        Builder::macro('orHasNotExpiredTags', $this->whereHasClosure('expiredTempTags', 'orWhereDoesntHave'));
        Builder::macro('hasNotExpiredTags', $this->whereHasClosure('expiredTempTags', 'whereDoesntHave'));

        Builder::macro('orHasTags', $this->whereHasClosure('tempTags', 'orWhereHas'));
        Builder::macro('hasTags', $this->whereHasClosure('tempTags', 'whereHas'));

        Builder::macro('orHasNotTags', $this->whereHasClosure('tempTags', 'orWhereDoesntHave'));
        Builder::macro('hasNotTags', $this->whereHasClosure('tempTags', 'whereDoesntHave'));
    }
}
