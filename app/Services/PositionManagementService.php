<?php

namespace App\Services;

use App\Models\User;
use App\Models\Membro;
use App\Models\CargoAssembleia;
use App\Models\CargoConselho;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PositionManagementService
{
    protected CargoAssembleiaService $cargoAssembleiaService;
    protected CargoConselhoService $cargoConselhoService;

    public function __construct(
        CargoAssembleiaService $cargoAssembleiaService,
        CargoConselhoService $cargoConselhoService
    ) {
        $this->cargoAssembleiaService = $cargoAssembleiaService;
        $this->cargoConselhoService = $cargoConselhoService;
    }

    /**
     * Atualiza automaticamente as permissões de um usuário baseado em seus cargos
     */
    public function atualizarPermissoesUsuario(User $user): void
    {
        if (!$user->membro) {
            return;
        }

        DB::transaction(function () use ($user) {
            // Obtém todos os cargos ativos do usuário
            $cargosAssembleia = CargoAssembleia::where('membro_id', $user->membro->id)
                ->where('ativo', true)
                ->get();

            $cargosConselho = CargoConselho::where('membro_id', $user->membro->id)
                ->where('ativo', true)
                ->get();

            // Determina o nível de acesso baseado nos cargos
            $novoNivelAcesso = $this->determinarNivelAcesso($cargosAssembleia, $cargosConselho);
            
            // Atualiza o nível de acesso se necessário
            if ($user->nivel_acesso !== $novoNivelAcesso) {
                $user->update(['nivel_acesso' => $novoNivelAcesso]);
            }

            // Atualiza roles
            $this->atualizarRoles($user, $novoNivelAcesso);

            // Atualiza permissões específicas
            $this->atualizarPermissoesEspecificas($user, $cargosAssembleia, $cargosConselho);

            $this->logOperacao('atualizar_permissoes_usuario', [
                'user_id' => $user->id,
                'membro_id' => $user->membro->id,
                'nivel_acesso_anterior' => $user->getOriginal('nivel_acesso'),
                'nivel_acesso_novo' => $novoNivelAcesso,
                'cargos_assembleia' => $cargosAssembleia->count(),
                'cargos_conselho' => $cargosConselho->count()
            ]);
        });
    }

    /**
     * Determina o nível de acesso baseado nos cargos
     */
    private function determinarNivelAcesso($cargosAssembleia, $cargosConselho): string
    {
        // Verifica se tem cargos de conselho que concedem admin
        $temCargoConselhoAdmin = $cargosConselho->contains(function ($cargo) {
            return $cargo->concedeAcessoAdmin();
        });

        if ($temCargoConselhoAdmin) {
            return 'admin_assembleia';
        }

        // Se tem qualquer cargo ativo, pelo menos é membro
        if ($cargosAssembleia->count() > 0 || $cargosConselho->count() > 0) {
            return 'membro';
        }

        return 'membro';
    }

    /**
     * Atualiza as roles do usuário
     */
    private function atualizarRoles(User $user, string $nivelAcesso): void
    {
        // Remove todas as roles atuais relacionadas a cargos
        $user->syncRoles([]);

        // Atribui a role baseada no nível de acesso
        switch ($nivelAcesso) {
            case 'admin_assembleia':
                $user->assignRole('admin_assembleia');
                break;
            case 'membro_jurisdicao':
                $user->assignRole('membro_jurisdicao');
                break;
            case 'membro':
            default:
                $user->assignRole('membro');
                break;
        }
    }

    /**
     * Atualiza permissões específicas baseadas nos cargos
     */
    private function atualizarPermissoesEspecificas(User $user, $cargosAssembleia, $cargosConselho): void
    {
        // Remove todas as permissões diretas
        $user->syncPermissions([]);

        $permissoes = [];

        // Permissões baseadas em cargos de conselho
        foreach ($cargosConselho as $cargo) {
            $permissoes = array_merge($permissoes, $this->getPermissoesPorCargoConselho($cargo->tipo_cargo));
        }

        // Permissões baseadas em cargos de assembleia (se houver regras específicas)
        foreach ($cargosAssembleia as $cargo) {
            $permissoes = array_merge($permissoes, $this->getPermissoesPorCargoAssembleia($cargo));
        }

        // Remove duplicatas e atribui permissões
        $permissoes = array_unique($permissoes);
        if (!empty($permissoes)) {
            $user->givePermissionTo($permissoes);
        }
    }

    /**
     * Obtém permissões por cargo de conselho
     */
    private function getPermissoesPorCargoConselho(string $tipoCargo): array
    {
        return match($tipoCargo) {
            CargoConselho::PRESIDENTE => [
                'protocolos.approve',
                'cargos.assign',
                'membros.manage',
                'assembleia.manage'
            ],
            CargoConselho::PRECEPTORA_MAE, CargoConselho::PRECEPTORA_MAE_ADJUNTA => [
                'protocolos.create',
                'protocolos.approve',
                'membros.manage'
            ],
            CargoConselho::MEMBRO_CONSELHO => [
                'protocolos.view',
                'membros.view'
            ],
            default => []
        };
    }

    /**
     * Obtém permissões por cargo de assembleia
     */
    private function getPermissoesPorCargoAssembleia(CargoAssembleia $cargo): array
    {
        // Por enquanto, cargos de assembleia não concedem permissões especiais
        // Mas pode ser expandido no futuro
        return [];
    }

    /**
     * Remove todas as permissões relacionadas a cargos de um usuário
     */
    public function removerPermissoesCargos(User $user): void
    {
        if (!$user->membro) {
            return;
        }

        DB::transaction(function () use ($user) {
            // Atualiza nível de acesso para membro básico
            $user->update(['nivel_acesso' => 'membro']);

            // Remove roles relacionadas a cargos
            $user->removeRole(['admin_assembleia', 'presidente_honrarias']);

            // Atribui role básica
            $user->assignRole('membro');

            // Remove permissões específicas de cargos
            $permissoesParaRemover = [
                'protocolos.approve',
                'cargos.assign',
                'membros.manage',
                'assembleia.manage',
                'protocolos.create'
            ];

            $user->revokePermissionTo($permissoesParaRemover);

            $this->logOperacao('remover_permissoes_cargos', [
                'user_id' => $user->id,
                'membro_id' => $user->membro->id
            ]);
        });
    }

    /**
     * Sincroniza permissões de todos os usuários com cargos
     */
    public function sincronizarTodasPermissoes(): array
    {
        $resultados = [
            'usuarios_atualizados' => 0,
            'erros' => []
        ];

        // Obtém todos os usuários que têm membros com cargos ativos
        $usuariosComCargos = User::whereHas('membro', function ($q) {
            $q->whereHas('cargosAssembleia', function ($sq) {
                $sq->where('ativo', true);
            })->orWhereHas('cargosConselho', function ($sq) {
                $sq->where('ativo', true);
            });
        })->get();

        foreach ($usuariosComCargos as $user) {
            try {
                $this->atualizarPermissoesUsuario($user);
                $resultados['usuarios_atualizados']++;
            } catch (\Exception $e) {
                $resultados['erros'][] = [
                    'user_id' => $user->id,
                    'erro' => $e->getMessage()
                ];
            }
        }

        $this->logOperacao('sincronizar_todas_permissoes', $resultados);

        return $resultados;
    }

    /**
     * Verifica inconsistências entre cargos e permissões
     */
    public function verificarInconsistencias(): array
    {
        $inconsistencias = [];

        // Verifica usuários com cargos executivos sem permissões adequadas
        $cargosExecutivos = CargoConselho::where('ativo', true)
            ->where('concede_admin_acesso', true)
            ->with('membro.user')
            ->get();

        foreach ($cargosExecutivos as $cargo) {
            if (!$cargo->membro->user) {
                $inconsistencias[] = [
                    'tipo' => 'usuario_inexistente',
                    'cargo_id' => $cargo->id,
                    'membro' => $cargo->membro->nome_completo,
                    'cargo' => $cargo->getNomeCargoFormatado(),
                    'descricao' => 'Membro com cargo executivo não possui usuário'
                ];
                continue;
            }

            $user = $cargo->membro->user;

            if ($user->nivel_acesso !== 'admin_assembleia') {
                $inconsistencias[] = [
                    'tipo' => 'nivel_acesso_incorreto',
                    'user_id' => $user->id,
                    'cargo_id' => $cargo->id,
                    'nivel_atual' => $user->nivel_acesso,
                    'nivel_esperado' => 'admin_assembleia',
                    'descricao' => 'Nível de acesso não condiz com cargo executivo'
                ];
            }

            if (!$user->hasRole('admin_assembleia')) {
                $inconsistencias[] = [
                    'tipo' => 'role_incorreta',
                    'user_id' => $user->id,
                    'cargo_id' => $cargo->id,
                    'descricao' => 'Usuário não possui role de admin_assembleia'
                ];
            }
        }

        return $inconsistencias;
    }

    /**
     * Corrige inconsistências encontradas
     */
    public function corrigirInconsistencias(array $inconsistencias): array
    {
        $resultados = [
            'corrigidas' => 0,
            'erros' => []
        ];

        foreach ($inconsistencias as $inconsistencia) {
            try {
                switch ($inconsistencia['tipo']) {
                    case 'nivel_acesso_incorreto':
                    case 'role_incorreta':
                        $user = User::find($inconsistencia['user_id']);
                        if ($user) {
                            $this->atualizarPermissoesUsuario($user);
                            $resultados['corrigidas']++;
                        }
                        break;
                }
            } catch (\Exception $e) {
                $resultados['erros'][] = [
                    'inconsistencia' => $inconsistencia,
                    'erro' => $e->getMessage()
                ];
            }
        }

        return $resultados;
    }

    /**
     * Obtém estatísticas gerais de cargos e permissões
     */
    public function getEstatisticasGerais(): array
    {
        return [
            'cargos_assembleia_ativos' => CargoAssembleia::where('ativo', true)->count(),
            'cargos_conselho_ativos' => CargoConselho::where('ativo', true)->count(),
            'usuarios_admin_assembleia' => User::where('nivel_acesso', 'admin_assembleia')->count(),
            'usuarios_membro_jurisdicao' => User::where('nivel_acesso', 'membro_jurisdicao')->count(),
            'usuarios_membro' => User::where('nivel_acesso', 'membro')->count(),
            'inconsistencias' => count($this->verificarInconsistencias()),
        ];
    }

    /**
     * Log de operações
     */
    private function logOperacao(string $operacao, array $dados): void
    {
        Log::info("PositionManagementService: {$operacao}", $dados);
    }
}