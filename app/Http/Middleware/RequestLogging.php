<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Models\SystemLog;

class RequestLogging
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            // Skip logging for irrelevant paths (assets, debug tools)
            $path = $request->path();
            if (preg_match('~^(assets/|storage/|_debugbar|telescope|horizon)~', $path)) {
                return $response;
            }

            $payload = $request->except([
                '_token','password','password_confirmation','current_password','_method'
            ]);

            // Ensure payload is JSON serializable
            $payload = json_decode(json_encode($payload), true);

            $routeName = Route::currentRouteName();

            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'request',
                'table_name' => $routeName ? ('route:'.$routeName) : '-',
                // record_id is required by schema; for generic request we generate a UUID placeholder
                'record_id' => (string) Str::uuid(),
                'old_values' => null,
                'new_values' => null,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'request_payload' => $payload ?: null,
                'ip_address' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            // Do not block request flow on logging failure
        }

        return $response;
    }
}
