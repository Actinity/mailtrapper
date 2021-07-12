<?php
namespace Actinity\Mailtrapper\Http\Middleware;

use Closure;
use Error;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class InjectMailtrapper
{
	public function handle(Request $request, Closure $next)
	{
		if(config('mail.default') !== 'trapper' || !config('mail.inject_mailtrapper')) {
			return $next($request);
		}

		/**
		 * Most of this is adapted from barryvdh/laravel-debugbar
		 */

		try {
			/** @var \Illuminate\Http\Response $response */
			$response = $next($request);
		} catch (Exception $e) {
			$response = $this->handleException($request, $e);
		} catch (Error $error) {
			$e = new FatalThrowableError($error);
			$response = $this->handleException($request, $e);
		}

		if(($response->headers->has('Content-Type') &&
				strpos($response->headers->get('Content-Type'), 'html') === false) ||
			$request->getRequestFormat() !== 'html' ||
			$response->getContent() === false ||
			$this->isJsonRequest($request) ||
			$response->headers->has('X-Mailtrapper')
		) {
			return $next($request);
		}


		$content = $response->getContent();

		$pos = strripos($content, '</body>');
		if (false !== $pos) {
			$content = substr($content, 0, $pos) . '<div id="mailtrapper_widget"><mailtrapper></mailtrapper></div><script src="/mailtrapper-ui/mailtrapper.js"></script>' . substr($content, $pos);

			$response->setContent($content);
			$response->headers->remove('Content-Length');
		}


		return $response;
	}

	/**
	 * Handle the given exception.
	 *
	 * (Copy from Illuminate\Routing\Pipeline by Taylor Otwell)
	 *
	 * @param $passable
	 * @param  Exception $e
	 * @return mixed
	 * @throws Exception
	 */
	protected function handleException($passable, Exception $e)
	{
		if (! $this->container->bound(ExceptionHandler::class) || ! $passable instanceof Request) {
			throw $e;
		}

		$handler = $this->container->make(ExceptionHandler::class);

		$handler->report($e);

		return $handler->render($passable, $e);
	}

	/**
	 * @param  \Symfony\Component\HttpFoundation\Request $request
	 * @return bool
	 */
	protected function isJsonRequest(\Symfony\Component\HttpFoundation\Request $request)
	{
		// If XmlHttpRequest or Live, return true
		if ($request->isXmlHttpRequest() || $request->headers->get('X-Livewire')) {
			return true;
		}

		// Check if the request wants Json
		$acceptable = $request->getAcceptableContentTypes();
		return (isset($acceptable[0]) && $acceptable[0] == 'application/json');
	}
}