<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidRolesPermissionsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->hasPermission($request)) {
            return $next($request);
        }

        return abort(403, __('messages.un_authenticated'));
    }

    private function hasPermission(Request $request)
    {
        $route = explode('/', $request->path())[1];
        $method = $request->method();

        switch ($method) {
            case 'GET':
                $action = 'view';
                break;
            case 'POST':
                $action = 'create';
                break;
            case 'PUT':
                $action = 'update';
                break;
            case 'DELETE':
                $action = 'delete';
                break;
        }
        Log::info("PERMISSION Check {$action}_{$route} for user_id: " . auth('user')->id());

        //if no permission, skip the middleware
        try {
            if (auth('user')->user()?->hasDirectPermission("{$action}_{$route}")) {
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            Log::error("PERMISSION Error caused from {$action}_{$route}: {$th->getMessage()}\ntrace: {$th->getTraceAsString()}");
            return true;
        }
    }
}
