<?php

namespace Imanghafoori\Tags;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;
use Imanghafoori\Tags\Console\Commands\DeleteExpiredTempTags;
use Imanghafoori\Tags\Services\TagService;

class TempTagServiceProvider extends ServiceProvider
{
    public function register()
    {
        config()->set('cache.stores.temp_tag', ['driver' => 'file', 'path' => config('tag.cache_storage_path')]);
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
            $this->app->bind('command.tag:delete-expired', DeleteExpiredTempTags::class);

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
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    private function configure()
    {
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/temp_tag.php', 'tag');
        }
    }

    private function registerEloquentMacros()
    {
        Builder::macro('orHasActiveTags', TagService::whereHasClosure('activeTempTags', 'orWhereHas'));
        Builder::macro('hasActiveTags', TagService::whereHasClosure('activeTempTags', 'whereHas'));
        Builder::macro('hasActiveTagsAt', TagService::whereHasUntilClosure('whereHas'));
        Builder::macro('hasNotActiveTagsAt', TagService::whereHasUntilClosure('whereDoesntHave'));
        Builder::macro('orHasActiveTagsAt', TagService::whereHasUntilClosure('orWhereHas'));
        Builder::macro('orHasNotActiveTagsAt', TagService::whereHasUntilClosure('orWhereDoesntHave'));

        Builder::macro('orHasNotActiveTags', TagService::whereHasClosure('activeTempTags', 'orWhereDoesntHave'));
        Builder::macro('hasNotActiveTags', TagService::whereHasClosure('activeTempTags', 'whereDoesntHave'));

        Builder::macro('orHasExpiredTags', TagService::whereHasClosure('expiredTempTags', 'orWhereHas'));
        Builder::macro('hasExpiredTags', TagService::whereHasClosure('expiredTempTags', 'whereHas'));

        Builder::macro('orHasNotExpiredTags', TagService::whereHasClosure('expiredTempTags', 'orWhereDoesntHave'));
        Builder::macro('hasNotExpiredTags', TagService::whereHasClosure('expiredTempTags', 'whereDoesntHave'));

        Builder::macro('orHasTags', TagService::whereHasClosure('tempTags', 'orWhereHas'));
        Builder::macro('hasTags', TagService::whereHasClosure('tempTags', 'whereHas'));

        Builder::macro('orHasNotTags', TagService::whereHasClosure('tempTags', 'orWhereDoesntHave'));
        Builder::macro('hasNotTags', TagService::whereHasClosure('tempTags', 'whereDoesntHave'));
    }
}
