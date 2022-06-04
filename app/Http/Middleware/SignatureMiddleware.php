<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SignatureMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param string $headerName
     * @return void
     */
    public function handle(Request $request, Closure $next, $headerName = 'X-Name')
    {
        // We are modifying th response headers here, not the request
        // We are attaching the app signature to the response
        $response = $next($request);
        $response->headers->set($headerName, config('app.name'));
        return $response;
    }
}
