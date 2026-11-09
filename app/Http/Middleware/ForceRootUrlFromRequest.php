<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ForceRootUrlFromRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        // Perlindungan host-header poisoning: JANGAN pernah memaksa root URL
        // dari Host request secara membabi-buta, karena QR signed-route
        // akan ikut host penyerang. Host request hanya diikuti bila:
        //  - sama persis dengan host APP_URL (config app.url), atau
        //  - env APP_ENV=local/testing (pengembangan lokal), atau
        //  - host ngrok yang dikenal (*.ngrok-free.dev / *.ngrok.io), atau
        //  - tercantum di allowlist APP_TRUSTED_HOSTS (comma-separated).
        // Selain itu root URL selalu diambil dari config app.url.
        $requestHost = strtolower($request->getHost());
        $appUrl = rtrim((string) config('app.url', 'http://localhost'), '/');
        $appHost = strtolower((string) parse_url($appUrl, PHP_URL_HOST));

        $trustedHosts = collect(explode(',', (string) env('APP_TRUSTED_HOSTS', '')))
            ->map(fn ($host) => strtolower(trim($host)))
            ->filter()
            ->all();

        $isNgrok = str_ends_with($requestHost, '.ngrok-free.dev')
            || str_ends_with($requestHost, '.ngrok.io');

        $isAllowed = $requestHost !== ''
            && ($requestHost === $appHost
                || app()->environment('local', 'testing')
                || $isNgrok
                || in_array($requestHost, $trustedHosts, true));

        if ($isAllowed) {
            // Host asal: localhost/ip tetap mengikuti skema request (http), domain ngrok dipaksa https
            // karena proxy ngrok tidak selalu meneruskan X-Forwarded-Proto (mixed content).
            if ($isNgrok) {
                URL::forceScheme('https');
                URL::forceRootUrl('https://'.$requestHost);
                $rootUrl = 'https://'.$requestHost;
            } else {
                $scheme = $request->getSchemeAndHttpHost();
                URL::forceRootUrl($scheme);
                $rootUrl = $scheme;
            }
        } else {
            // Host tidak tepercaya: jangan ikuti Host request sama sekali.
            URL::forceRootUrl($appUrl);
            $rootUrl = $appUrl;
        }

        // Storage::url() memakai config filesystems (APP_URL) dan tidak
        // terpengaruh URL::forceRootUrl di atas. Paksa mengikuti rootUrl
        // yang sudah divalidasi di atas agar file upload (logo/avatar)
        // tampil benar tanpa mengikuti host penyerang.
        config(['filesystems.disks.public.url' => $rootUrl.'/storage']);

        return $next($request);
    }
}
