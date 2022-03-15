<?php

namespace App\V1\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use App;

class SetLanguage
{
    /**
     * Configure language from HTTP headers.
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
        $locale = $request->headers->get('ACCEPT_LANGUAGE');
        if ($locale !== null) {
            if (strpos($locale, ';') !== false) {
                list($locale) = explode(';', $locale);
            }
            if (strpos($locale, ',') !== false) {
                list($locale) = explode(',', $locale);
            }
            if (strpos($locale, '-') !== false) {
                list($locale) = explode('-', $locale);
            }
            App::setLocale(strtolower(trim($locale)));
        }
        return $next($request);
    }
}
