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
        // Host asal: localhost/ip tetap mengikuti skema request (http), domain ngrok dipaksa https
        // karena proxy ngrok tidak selalu meneruskan X-Forwarded-Proto (mixed content).
        $isNgrok = str_ends_with($request->getHost(), '.ngrok-free.dev')
            || $request->is('*.ngrok-free.dev');

        $scheme = $request->getSchemeAndHttpHost();

        if ($isNgrok) {
            URL::forceScheme('https');
            URL::forceRootUrl('https://'.$request->getHost());
            $rootUrl = 'https://'.$request->getHost();
        } else {
            URL::forceRootUrl($scheme);
            $rootUrl = $scheme;
        }

        // Storage::url() memakai config filesystems (APP_URL) dan tidak
        // terpengaruh URL::forceRootUrl di atas. Paksa mengikuti host
        // request agar file upload (logo/avatar) tampil di localhost
        // maupun domain ngrok tanpa halaman peringatan ngrok.
        config(['filesystems.disks.public.url' => $rootUrl.'/storage']);

        return $next($request);
    }
}
