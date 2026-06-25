<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $permissions = explode('|', $permission);
        $hasAccess = false;
        
        if ($request->user()) {
            foreach ($permissions as $p) {
                if ($request->user()->hasPermission($p)) {
                    $hasAccess = true;
                    break;
                }
            }
        }

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melakukan tindakan ini.'
            ], 403);
        }

        return $next($request);
    }
}
