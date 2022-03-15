<?php

namespace App\V1\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class ServerTime
{
    /**
     * Add date to HTTP headers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|array  $options
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \InvalidArgumentException
     */
    public function handle($request, Closure $next, $options = [])
    {
        $response = $next($request);
        if ($response instanceof JsonResponse) {
            $response->header('Server-Time', Carbon::now()->toRfc2822String());
        }
        return $response;
    }
}
