<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectSensitiveOperations
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $operation): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super admin tem acesso a tudo
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        // Verificar operações sensíveis específicas
        switch ($operation) {
            case 'protocol_approval':
                return $this->checkProtocolApprovalAccess($request, $next, $user);
                
            case 'member_management':
                return $this->checkMemberManagementAccess($request, $next, $user);
                
            case 'position_assignment':
                return $this->checkPositionAssignmentAccess($request, $next, $user);
                
            case 'honor_management':
                return $this->checkHonorManagementAccess($request, $next, $user);
                
            case 'tax_management':
                return $this->checkTaxManagementAccess($request, $next, $user);
                
            default:
                abort(403, 'Operação não reconhecida.');
        }
    }

    /**
     * Check protocol approval access.
     */
    private function checkProtocolApprovalAccess(Request $request, Closure $next, $user): Response
    {
        // Membro da jurisdição pode aprovar todos os protocolos
        if ($user->hasRole('membro_jurisdicao')) {
            return $next($request);
        }

        // Presidente de honrarias pode aprovar apenas protocolos de honrarias
        if ($user->hasRole('presidente_honrarias')) {
            $protocolType = $this->getProtocolTypeFromRequest($request);
            if (in_array($protocolType, ['homenageados_ano', 'coracao_cores', 'grande_cruz_cores'])) {
                return $next($request);
            }
        }

        abort(403, 'Acesso negado. Você não tem permissão para aprovar este tipo de protocolo.');
    }

    /**
     * Check member management access.
     */
    private function checkMemberManagementAccess(Request $request, Closure $next, $user): Response
    {
        if (!$user->hasPermission('manage_membros')) {
            abort(403, 'Acesso negado. Você não tem permissão para gerenciar membros.');
        }

        // Verificar se está tentando gerenciar membro de outra assembleia
        $membroId = $request->route('membro')?->id ?? $request->get('membro_id');
        if ($membroId && !$user->hasRole('membro_jurisdicao')) {
            $membro = \App\Models\Membro::find($membroId);
            if ($membro && $user->membro && $membro->assembleia_id !== $user->membro->assembleia_id) {
                abort(403, 'Acesso negado. Você só pode gerenciar membros de sua assembleia.');
            }
        }

        return $next($request);
    }

    /**
     * Check position assignment access.
     */
    private function checkPositionAssignmentAccess(Request $request, Closure $next, $user): Response
    {
        $cargoType = $request->get('cargo_type') ?? $request->get('tipo_cargo');
        
        // Verificar permissões específicas por tipo de cargo
        if (str_contains($cargoType, 'assembleia')) {
            if (!$user->hasPermission('assign_cargos_assembleia')) {
                abort(403, 'Acesso negado. Você não tem permissão para atribuir cargos de assembleia.');
            }
        } elseif (str_contains($cargoType, 'conselho')) {
            if (!$user->hasPermission('assign_cargos_conselho')) {
                abort(403, 'Acesso negado. Você não tem permissão para atribuir cargos de conselho.');
            }
        }

        // Verificar se está tentando atribuir cargo em outra assembleia
        if (!$user->hasRole('membro_jurisdicao')) {
            $assembleiaId = $request->get('assembleia_id');
            if ($assembleiaId && $user->membro && $assembleiaId != $user->membro->assembleia_id) {
                abort(403, 'Acesso negado. Você só pode atribuir cargos em sua assembleia.');
            }
        }

        return $next($request);
    }

    /**
     * Check honor management access.
     */
    private function checkHonorManagementAccess(Request $request, Closure $next, $user): Response
    {
        // Membro da jurisdição pode gerenciar todas as honrarias
        if ($user->hasRole('membro_jurisdicao')) {
            return $next($request);
        }

        // Presidente de honrarias pode gerenciar honrarias
        if ($user->hasRole('presidente_honrarias') && $user->hasPermission('manage_honrarias')) {
            return $next($request);
        }

        // Admin de assembleia pode gerenciar honrarias de sua assembleia
        if ($user->hasRole('admin_assembleia') && $user->hasPermission('manage_honrarias')) {
            $assembleiaId = $request->get('assembleia_id');
            if (!$assembleiaId || ($user->membro && $assembleiaId == $user->membro->assembleia_id)) {
                return $next($request);
            }
        }

        abort(403, 'Acesso negado. Você não tem permissão para gerenciar honrarias.');
    }

    /**
     * Check tax management access.
     */
    private function checkTaxManagementAccess(Request $request, Closure $next, $user): Response
    {
        if (!$user->hasPermission('manage_protocolo_taxes')) {
            abort(403, 'Acesso negado. Você não tem permissão para gerenciar taxas de protocolos.');
        }

        // Apenas membro da jurisdição pode gerenciar taxas
        if (!$user->hasRole('membro_jurisdicao')) {
            abort(403, 'Acesso negado. Apenas membros da jurisdição podem gerenciar taxas.');
        }

        return $next($request);
    }

    /**
     * Extract protocol type from request.
     */
    private function getProtocolTypeFromRequest(Request $request): ?string
    {
        if ($request->has('tipo_protocolo')) {
            return $request->get('tipo_protocolo');
        }

        if ($request->route('protocolo')) {
            $protocolo = $request->route('protocolo');
            return $protocolo->tipo_protocolo ?? null;
        }

        return null;
    }
}