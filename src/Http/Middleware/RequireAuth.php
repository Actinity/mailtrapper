<?php
namespace Actinity\Mailtrapper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireAuth
{
	public function handle($request,Closure $next)
	{
		if(!auth()->user()) {
			abort(403);
		}
		return $next($request);
	}
}