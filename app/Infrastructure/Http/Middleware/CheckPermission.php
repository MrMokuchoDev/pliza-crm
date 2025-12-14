<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para verificar que el usuario tiene un permiso específico.
 *
 * Uso:
 *   - Un permiso: middleware('permission:leads.view_all')
 *   - Múltiples permisos (OR): middleware('permission:leads.view_all|leads.view_own')
 */
class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permissions  El/los permiso(s) requerido(s), separados por |
     */
    public function handle(Request $request, Closure $next, string $permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Soporta múltiples permisos separados por |
        $permissionList = explode('|', $permissions);

        if (! $user->hasAnyPermission($permissionList)) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        return $next($request);
    }
}
