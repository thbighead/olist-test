<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnforceAcceptJson
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /*
         * Laravel shall return a response accordingly to what request accepts.
         * Adding this header will make Laravel understands that every response
         * should be made in JSON format.
         */
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
