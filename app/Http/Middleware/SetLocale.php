<?php

namespace App\Http\Middleware;

use App\Models\Hotel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the {locale} route prefix (ar | en) and shares the alternate-language URL with views.
 */
class SetLocale
{
    public const LOCALES = ['ar', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');
        if (! in_array($locale, self::LOCALES, true)) {
            abort(404);
        }

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        $other = $locale === 'ar' ? 'en' : 'ar';
        $route = $request->route();
        $alternateUrl = route($route->getName(), [...$route->parameters(), 'locale' => $other]);
        if ($request->getQueryString()) {
            $alternateUrl .= '?'.$request->getQueryString();
        }

        View::share([
            'locale' => $locale,
            'isRtl' => $locale === 'ar',
            'alternateLocale' => $other,
            'alternateUrl' => $alternateUrl,
            'hotel' => Hotel::current(),
        ]);

        // The route closure/controller doesn't need {locale} as an argument.
        $route->forgetParameter('locale');

        return $next($request);
    }
}
