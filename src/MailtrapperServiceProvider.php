<?php
namespace Actinity\Mailtrapper;

use Actinity\Mailtrapper\Http\Middleware\InjectMailtrapper;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Actinity\Mailtrapper\Http\Middleware\RequireAuth;

class MailtrapperServiceProvider
	extends ServiceProvider
{
	public const STANDARD_MIDDLEWARE = [
		'web',
		RequireAuth::class,
	];

	public function boot()
	{
		$this->loadRoutesFrom(__DIR__ . '/Http/routes.php');
		$this->loadMigrationsFrom(__DIR__.'/migrations');

		config(['mail.mailers.trapper' => ['transport' => 'trapper']]);

		$kernel = $this->app[Kernel::class];
		$kernel->pushMiddleware(InjectMailtrapper::class);

		$this->publishes([
			__DIR__.'/config.php' => config_path('mailtrapper.php'),
		],'mailtrapper');
	}

}