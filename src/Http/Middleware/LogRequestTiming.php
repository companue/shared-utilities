<?php

namespace Companue\SharedUtilities\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequestTiming
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!config('shared-utilities.log_request_timing.enabled', false)) {
            return $next($request);
        }

        $frameworkStart = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);
        $middlewareStart = microtime(true);

        $response = $next($request);

        $total     = round((microtime(true) - $frameworkStart) * 1000, 2);
        $handler   = round((microtime(true) - $middlewareStart) * 1000, 2);
        $boot      = round(($middlewareStart - $frameworkStart) * 1000, 2);

        $content    = $response->getContent();
        $sizeBytes  = $content !== false ? strlen($content) : 0;
        $sizeKb     = round($sizeBytes / 1024, 2);

        Log::info('[TIMING] request completed', [
            'method'       => $request->method(),
            'path'         => $request->path(),
            'boot_ms'      => $boot,
            'handler_ms'   => $handler,
            'total_ms'     => $total,
            'status'       => $response->getStatusCode(),
            'size_bytes'   => $sizeBytes,
            'size_kb'      => $sizeKb,
        ]);

        return $response;
    }
}
