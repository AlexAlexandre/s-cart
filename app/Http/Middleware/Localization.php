<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Session;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
//Set language
        $configsGlobal = \Helper::configsGlobal();
        $languages     = Language::where('status', 1)->get()->keyBy('code');
        if (!Session::has('locale')) {
            $detectLocale = empty($configsGlobal['locale']) ? config('app.locale') : $configsGlobal['locale'];
        } else {
            $detectLocale = session('locale');
        }
        $currentLocale = array_key_exists($detectLocale, $languages->toArray()) ? $detectLocale : $languages->first()->code;
        session(['locale' => $currentLocale, 'locale_id' => $languages[$currentLocale]['id']]);
        app()->setLocale($currentLocale);
//End language
        return $next($request);
    }
}
