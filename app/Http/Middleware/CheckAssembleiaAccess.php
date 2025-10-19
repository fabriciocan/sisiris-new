<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAssembleiaAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super admin tem acesso a tudo
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        // Membro da jurisdição tem acesso a todas as assembleias
        if ($user->hasRole('membro_jurisdicao')) {
            return $next($request);
        }

        // Verificar se o usuário tem membro associado
        if (!$user->membro) {
            abort(403, 'Usuário não possui perfil de membro associado.');
        }

        // Admin de assembleia só pode acessar recursos de sua assembleia
        if ($user->hasRole('admin_assembleia')) {
            $assembleiaId = $this->getAssembleiaIdFromRequest($request);
            
            if ($assembleiaId && $assembleiaId != $user->membro->assembleia_id) {
                abort(403, 'Acesso negado. Você só pode acessar recursos de sua assembleia.');
            }
        }

        // Membros comuns só podem acessar recursos de sua assembleia
        if ($user->hasRole('membro')) {
            $assembleiaId = $this->getAssembleiaIdFromRequest($request);
            
            if ($assembleiaId && $assembleiaId != $user->membro->assembleia_id) {
                abort(403, 'Acesso negado. Você só pode acessar recursos de sua assembleia.');
            }
        }

        return $next($request);
    }

    /**
     * Extract assembleia ID from request parameters or route.
     */
    private function getAssembleiaIdFromRequest(Request $request): ?int
    {
        // Tentar obter assembleia_id dos parâmetros da requisição
        if ($request->has('assembleia_id')) {
            return (int) $request->get('assembleia_id');
        }

        // Tentar obter da rota
        if ($request->route('assembleia')) {
            return (int) $request->route('assembleia');
        }

        // Tentar obter de um modelo relacionado (protocolo, membro, etc.)
        if ($request->route('protocolo')) {
            $protocolo = $request->route('protocolo');
            return $protocolo->assembleia_id ?? null;
        }

        if ($request->route('membro')) {
            $membro = $request->route('membro');
            return $membro->assembleia_id ?? null;
        }

        return null;
    }
}