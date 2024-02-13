<?php

namespace App\Providers;

use App\Models\Link;
use App\Models\Taggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        config([
            'auth.guards.web-subscribers' => [
                'driver' => 'session',
                'provider' => 'subscribers',
            ],
            'auth.providers.subscribers' => [
                'driver' => 'eloquent',
                'model' => \App\Models\Subscriber::class,
            ],
            'logging.channels.deprecations' => array_merge(
                config('logging.channels.single'), ['path' => storage_path('logs/deprecations.log')],
            ),
        ]);

        if (version_compare(Application::VERSION, '9.35.0', '>=')) {
            Model::shouldBeStrict((bool) config('app.debug'));
        } else {
            Model::preventLazyLoading((bool) config('app.debug'));
        }

        $this->app->instance('uses_searchable', file_exists(base_path('.searchable')));
        $this->app->instance('uses_inline_create', file_exists(base_path('.inline-create')));
        $this->app->instance('uses_with_reordering', ! file_exists(base_path('.disable-reordering')));
        $this->app->instance('uses_breadcrumbs', ! file_exists(base_path('.disable-breadcrumbs')));
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            'link' => Link::class,
            'taggable' => Taggable::class,
        ]);
    }
}
