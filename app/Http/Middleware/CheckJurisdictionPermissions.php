<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckJurisdictionPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super admin tem acesso a tudo
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        // Verificar se o usuário tem pelo menos uma das permissões necessárias
        if (!empty($permissions) && !$user->hasAnyPermission($permissions)) {
            abort(403, 'Acesso negado. Permissões insuficientes.');
        }

        // Verificar se é membro da jurisdição para operações específicas
        $jurisdictionOnlyActions = [
            'approve_protocolos',
            'manage_protocolo_taxes',
            'manage_all_assembleias',
            'assign_cargos_conselho'
        ];

        foreach ($jurisdictionOnlyActions as $action) {
            if (in_array($action, $permissions) && !$user->hasRole('membro_jurisdicao')) {
                // Exceção para presidente de honrarias em protocolos de honrarias
                if ($action === 'approve_protocolos' && $user->hasRole('presidente_honrarias')) {
                    $protocolType = $this->getProtocolTypeFromRequest($request);
                    if (in_array($protocolType, ['homenageados_ano', 'coracao_cores', 'grande_cruz_cores'])) {
                        continue;
                    }
                }
                
                abort(403, 'Acesso negado. Esta operação requer privilégios de jurisdição.');
            }
        }

        return $next($request);
    }

    /**
     * Extract protocol type from request.
     */
    private function getProtocolTypeFromRequest(Request $request): ?string
    {
        // Tentar obter tipo do protocolo dos parâmetros
        if ($request->has('tipo_protocolo')) {
            return $request->get('tipo_protocolo');
        }

        // Tentar obter do modelo protocolo na rota
        if ($request->route('protocolo')) {
            $protocolo = $request->route('protocolo');
            return $protocolo->tipo_protocolo ?? null;
        }

        return null;
    }
}