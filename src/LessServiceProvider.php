<?php namespace Langemike\Laravel5Less;

use Illuminate\Support\ServiceProvider;

class LessServiceProvider extends ServiceProvider {
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->app->singleton('less', function($app) {
			return new Less($app['config'], $app['cache.store']);
		});
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('less.php'),
        ]);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('less');
	}

}