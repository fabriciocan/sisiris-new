<?php

namespace App\Services;

use App\Models\CargoConselho;
use App\Models\Membro;
use App\Models\Assembleia;
use App\Models\Protocolo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class CargoConselhoService
{
    /**
     * Atribui cargos de conselho através de protocolo
     */
    public function atribuirCargosConselho(
        int $assembleiaId,
        array $cargos,
        ?string $protocoloId = null,
        ?string $userId = null
    ): array {
        return DB::transaction(function () use ($assembleiaId, $cargos, $protocoloId, $userId) {
            // Validações iniciais
            $this->validarAtribuicaoCargos($assembleiaId, $cargos);

            // Finaliza todos os cargos atuais
            $this->finalizarCargosAtuais($assembleiaId);

            // Cria os novos cargos
            $novosCargosCriados = $this->criarNovosCargos(
                $assembleiaId, 
                $cargos, 
                $protocoloId, 
                $userId
            );

            // Atualiza permissões dos usuários
            $this->atualizarPermissoesUsuarios($novosCargosCriados);

            // Log da operação
            $this->logOperacao('atribuir_cargos_conselho', [
                'assembleia_id' => $assembleiaId,
                'protocolo_id' => $protocoloId,
                'user_id' => $userId,
                'cargos_criados' => count($novosCargosCriados)
            ]);

            return $novosCargosCriados;
        });
    }

    /**
     * Valida a atribuição de cargos de conselho
     */
    private function validarAtribuicaoCargos(int $assembleiaId, array $cargos): void
    {
        // Verifica se a assembleia existe
        $assembleia = Assembleia::find($assembleiaId);
        if (!$assembleia) {
            throw new InvalidArgumentException('Assembleia não encontrada');
        }

        // Valida cada cargo
        foreach ($cargos as $tipoCargo => $membroId) {
            if (!$membroId) {
                continue; // Cargo pode ficar vago
            }

            $membro = Membro::find($membroId);
            if (!$membro) {
                throw new InvalidArgumentException("Membro ID {$membroId} não encontrado");
            }

            // Verifica se o membro pertence à assembleia
            if ($membro->assembleia_id != $assembleiaId) {
                throw new InvalidArgumentException(
                    "Membro {$membro->nome_completo} não pertence à assembleia"
                );
            }

            // Validações específicas por tipo de cargo
            $this->validarElegibilidadePorTipoCargo($membro, $tipoCargo);
        }

        // Verifica duplicatas na mesma atribuição
        $membrosAtribuidos = array_filter($cargos);
        if (count($membrosAtribuidos) !== count(array_unique($membrosAtribuidos))) {
            throw new InvalidArgumentException(
                'Um mesmo membro não pode ocupar múltiplos cargos de conselho simultaneamente'
            );
        }
    }

    /**
     * Valida elegibilidade por tipo de cargo
     */
    private function validarElegibilidadePorTipoCargo(Membro $membro, string $tipoCargo): void
    {
        // Validação geral para cargos de conselho
        if (!$membro->isElegivelConselho()) {
            throw new InvalidArgumentException(
                "Membro {$membro->nome_completo} não é elegível para cargos de conselho"
            );
        }

        // Validação específica para Presidente do Conselho
        if ($tipoCargo === CargoConselho::PRESIDENTE) {
            if (!$membro->isTioMacomMestre()) {
                throw new InvalidArgumentException(
                    "Presidente do Conselho deve ser Tio Maçom com grau Mestre. " .
                    "Membro {$membro->nome_completo} não atende este requisito."
                );
            }
        }

        // Verifica se está ativo
        if ($membro->status !== 'ativa') {
            throw new InvalidArgumentException(
                "Membro {$membro->nome_completo} deve estar com status ativo"
            );
        }
    }

    /**
     * Finaliza todos os cargos atuais da assembleia
     */
    private function finalizarCargosAtuais(int $assembleiaId): void
    {
        $cargosAtuais = CargoConselho::where('assembleia_id', $assembleiaId)
            ->where('ativo', true)
            ->get();

        foreach ($cargosAtuais as $cargo) {
            $cargo->finalizar('Finalizado por nova atribuição de cargos em ' . now()->format('d/m/Y H:i'));
        }
    }

    /**
     * Cria os novos cargos
     */
    private function criarNovosCargos(
        int $assembleiaId,
        array $cargos,
        ?string $protocoloId,
        ?string $userId
    ): array {
        $novosCargosCriados = [];

        foreach ($cargos as $tipoCargo => $membroId) {
            if (!$membroId) {
                continue; // Pula cargos vagos
            }

            $cargo = CargoConselho::create([
                'assembleia_id' => $assembleiaId,
                'membro_id' => $membroId,
                'tipo_cargo' => $tipoCargo,
                'data_inicio' => now(),
                'ativo' => true,
                'protocolo_id' => $protocoloId,
                'atribuido_por' => $userId,
                'observacoes' => 'Atribuído através de protocolo de novos cargos de conselho'
            ]);

            $novosCargosCriados[] = $cargo;
        }

        return $novosCargosCriados;
    }

    /**
     * Atualiza permissões dos usuários baseado nos novos cargos
     */
    private function atualizarPermissoesUsuarios(array $cargos): void
    {
        foreach ($cargos as $cargo) {
            $cargo->atualizarPermissoesUsuario();
        }
    }

    /**
     * Remove um membro de cargo específico
     */
    public function removerMembro(string $cargoId, ?string $observacao = null): bool
    {
        return DB::transaction(function () use ($cargoId, $observacao) {
            $cargo = CargoConselho::find($cargoId);
            
            if (!$cargo) {
                throw new InvalidArgumentException('Cargo não encontrado');
            }

            if (!$cargo->ativo) {
                throw new InvalidArgumentException('Cargo já está inativo');
            }

            $cargo->finalizar($observacao);

            $this->logOperacao('remover_cargo_conselho', [
                'cargo_id' => $cargoId,
                'membro_id' => $cargo->membro_id,
                'assembleia_id' => $cargo->assembleia_id,
                'tipo_cargo' => $cargo->tipo_cargo,
                'observacao' => $observacao
            ]);

            return true;
        });
    }

    /**
     * Atribui um cargo específico a um membro
     */
    public function atribuirCargoEspecifico(
        int $assembleiaId,
        string $tipoCargo,
        string $membroId,
        ?string $protocoloId = null,
        ?string $userId = null
    ): CargoConselho {
        return DB::transaction(function () use ($assembleiaId, $tipoCargo, $membroId, $protocoloId, $userId) {
            // Validações
            $this->validarAtribuicaoCargos($assembleiaId, [$tipoCargo => $membroId]);

            // Verifica se já existe cargo ativo do mesmo tipo
            $cargoExistente = CargoConselho::getCargoAtivo($assembleiaId, $tipoCargo);
            if ($cargoExistente) {
                $cargoExistente->finalizar('Substituído por nova atribuição');
            }

            // Cria o novo cargo
            $cargo = CargoConselho::create([
                'assembleia_id' => $assembleiaId,
                'membro_id' => $membroId,
                'tipo_cargo' => $tipoCargo,
                'data_inicio' => now(),
                'ativo' => true,
                'protocolo_id' => $protocoloId,
                'atribuido_por' => $userId,
            ]);

            // Atualiza permissões
            $cargo->atualizarPermissoesUsuario();

            $this->logOperacao('atribuir_cargo_especifico', [
                'cargo_id' => $cargo->id,
                'assembleia_id' => $assembleiaId,
                'tipo_cargo' => $tipoCargo,
                'membro_id' => $membroId,
                'protocolo_id' => $protocoloId
            ]);

            return $cargo;
        });
    }

    /**
     * Obtém cargos ativos de uma assembleia
     */
    public function getCargosAtivos(int $assembleiaId): \Illuminate\Database\Eloquent\Collection
    {
        return CargoConselho::where('assembleia_id', $assembleiaId)
            ->where('ativo', true)
            ->with(['membro', 'protocolo'])
            ->orderBy('tipo_cargo')
            ->get();
    }

    /**
     * Obtém histórico de cargos de uma assembleia
     */
    public function getHistoricoCargos(int $assembleiaId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return CargoConselho::where('assembleia_id', $assembleiaId)
            ->with(['membro', 'protocolo', 'atribuidoPor'])
            ->orderBy('data_inicio', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Verifica se um membro pode ocupar um cargo específico
     */
    public function podeOcuparCargo(string $membroId, string $tipoCargo): array
    {
        $membro = Membro::find($membroId);
        if (!$membro) {
            return ['pode' => false, 'motivo' => 'Membro não encontrado'];
        }

        try {
            $this->validarElegibilidadePorTipoCargo($membro, $tipoCargo);
        } catch (InvalidArgumentException $e) {
            return ['pode' => false, 'motivo' => $e->getMessage()];
        }

        // Verifica se já ocupa outro cargo ativo de conselho
        $cargoAtual = CargoConselho::where('membro_id', $membroId)
            ->where('ativo', true)
            ->first();

        if ($cargoAtual) {
            return ['pode' => false, 'motivo' => 'Membro já ocupa outro cargo de conselho ativo'];
        }

        return ['pode' => true, 'motivo' => null];
    }

    /**
     * Obtém membros elegíveis para cargos de conselho
     */
    public function getMembrosElegiveis(int $assembleiaId, ?string $tipoCargo = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Membro::where('assembleia_id', $assembleiaId)
            ->elegiveisConselho()
            ->where('status', 'ativa')
            ->whereDoesntHave('cargosConselho', function ($q) {
                $q->where('ativo', true);
            });

        // Filtro específico para Presidente do Conselho
        if ($tipoCargo === CargoConselho::PRESIDENTE) {
            $query->tiosMaconsMestres();
        }

        return $query->orderBy('nome_completo')->get();
    }

    /**
     * Obtém membros elegíveis para cada tipo de cargo
     */
    public function getMembrosElegiveisParaCadaCargo(int $assembleiaId): array
    {
        $membrosElegiveis = [];

        foreach (CargoConselho::getTiposCargo() as $tipoCargo => $nomeFormatado) {
            $membrosElegiveis[$tipoCargo] = $this->getMembrosElegiveis($assembleiaId, $tipoCargo);
        }

        return $membrosElegiveis;
    }

    /**
     * Valida se uma atribuição em lote é válida
     */
    public function validarAtribuicaoLote(int $assembleiaId, array $cargos): array
    {
        $erros = [];

        try {
            $this->validarAtribuicaoCargos($assembleiaId, $cargos);
        } catch (InvalidArgumentException $e) {
            $erros[] = $e->getMessage();
        }

        return $erros;
    }

    /**
     * Obtém estatísticas dos cargos de conselho
     */
    public function getEstatisticasCargos(int $assembleiaId): array
    {
        $cargosAtivos = $this->getCargosAtivos($assembleiaId);
        $totalCargos = count(CargoConselho::getTiposCargo());
        $cargosPreenchidos = $cargosAtivos->count();
        $cargosVagos = $totalCargos - $cargosPreenchidos;

        return [
            'total_cargos' => $totalCargos,
            'cargos_preenchidos' => $cargosPreenchidos,
            'cargos_vagos' => $cargosVagos,
            'percentual_preenchimento' => $totalCargos > 0 ? round(($cargosPreenchidos / $totalCargos) * 100, 2) : 0,
            'cargos_executivos' => $cargosAtivos->where('concede_admin_acesso', true)->count(),
        ];
    }

    /**
     * Verifica se há conflitos de permissão
     */
    public function verificarConflitosPermissao(int $assembleiaId): array
    {
        $conflitos = [];
        
        $cargosAdmin = CargoConselho::where('assembleia_id', $assembleiaId)
            ->where('ativo', true)
            ->where('concede_admin_acesso', true)
            ->with('membro.user')
            ->get();

        foreach ($cargosAdmin as $cargo) {
            if (!$cargo->membro->user) {
                $conflitos[] = [
                    'tipo' => 'usuario_inexistente',
                    'cargo' => $cargo->getNomeCargoFormatado(),
                    'membro' => $cargo->membro->nome_completo,
                    'descricao' => 'Membro com cargo executivo não possui usuário no sistema'
                ];
            } elseif ($cargo->membro->user->nivel_acesso !== 'admin_assembleia') {
                $conflitos[] = [
                    'tipo' => 'nivel_acesso_incorreto',
                    'cargo' => $cargo->getNomeCargoFormatado(),
                    'membro' => $cargo->membro->nome_completo,
                    'descricao' => 'Usuário não possui nível de acesso adequado ao cargo'
                ];
            }
        }

        return $conflitos;
    }

    /**
     * Log de operações
     */
    private function logOperacao(string $operacao, array $dados): void
    {
        Log::info("CargoConselhoService: {$operacao}", $dados);
    }
}