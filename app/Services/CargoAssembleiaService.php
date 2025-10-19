<?php

namespace App\Services;

use App\Models\CargoAssembleia;
use App\Models\Membro;
use App\Models\Assembleia;
use App\Models\Protocolo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class CargoAssembleiaService
{
    /**
     * Atribui cargos de assembleia através de protocolo
     */
    public function atribuirCargosAssembleia(
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

            // Log da operação
            $this->logOperacao('atribuir_cargos_assembleia', [
                'assembleia_id' => $assembleiaId,
                'protocolo_id' => $protocoloId,
                'user_id' => $userId,
                'cargos_criados' => count($novosCargosCriados)
            ]);

            return $novosCargosCriados;
        });
    }

    /**
     * Valida a atribuição de cargos
     */
    private function validarAtribuicaoCargos(int $assembleiaId, array $cargos): void
    {
        // Verifica se a assembleia existe
        $assembleia = Assembleia::find($assembleiaId);
        if (!$assembleia) {
            throw new InvalidArgumentException('Assembleia não encontrada');
        }

        // Valida cada cargo
        foreach ($cargos as $tipoCargoId => $membroId) {
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

            // Verifica se é menina ativa
            if (!$membro->isMeninaAtiva()) {
                throw new InvalidArgumentException(
                    "Membro {$membro->nome_completo} deve ser Menina Ativa para ocupar cargo de assembleia"
                );
            }

            // Verifica se está ativo
            if ($membro->status !== 'ativa') {
                throw new InvalidArgumentException(
                    "Membro {$membro->nome_completo} deve estar com status ativo"
                );
            }
        }

        // Verifica duplicatas na mesma atribuição
        $membrosAtribuidos = array_filter($cargos);
        if (count($membrosAtribuidos) !== count(array_unique($membrosAtribuidos))) {
            throw new InvalidArgumentException(
                'Um mesmo membro não pode ocupar múltiplos cargos simultaneamente'
            );
        }
    }

    /**
     * Finaliza todos os cargos atuais da assembleia
     */
    private function finalizarCargosAtuais(int $assembleiaId): void
    {
        CargoAssembleia::where('assembleia_id', $assembleiaId)
            ->where('ativo', true)
            ->update([
                'ativo' => false,
                'data_fim' => now(),
                'observacoes' => DB::raw("CONCAT(COALESCE(observacoes, ''), '\nFinalizado por nova atribuição de cargos em " . now()->format('d/m/Y H:i') . "')")
            ]);
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

        foreach ($cargos as $tipoCargoId => $membroId) {
            if (!$membroId) {
                continue; // Pula cargos vagos
            }

            $cargo = CargoAssembleia::create([
                'assembleia_id' => $assembleiaId,
                'membro_id' => $membroId,
                'tipo_cargo_id' => $tipoCargoId,
                'data_inicio' => now(),
                'ativo' => true,
                'protocolo_id' => $protocoloId,
                'atribuido_por' => $userId,
                'observacoes' => 'Atribuído através de protocolo de novos cargos'
            ]);

            $novosCargosCriados[] = $cargo;
        }

        return $novosCargosCriados;
    }

    /**
     * Remove um membro de cargo específico
     */
    public function removerMembro(string $cargoId, ?string $observacao = null): bool
    {
        return DB::transaction(function () use ($cargoId, $observacao) {
            $cargo = CargoAssembleia::find($cargoId);
            
            if (!$cargo) {
                throw new InvalidArgumentException('Cargo não encontrado');
            }

            if (!$cargo->ativo) {
                throw new InvalidArgumentException('Cargo já está inativo');
            }

            $cargo->finalizar($observacao);

            $this->logOperacao('remover_cargo_assembleia', [
                'cargo_id' => $cargoId,
                'membro_id' => $cargo->membro_id,
                'assembleia_id' => $cargo->assembleia_id,
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
        int $tipoCargoId,
        string $membroId,
        ?string $protocoloId = null,
        ?string $userId = null
    ): CargoAssembleia {
        return DB::transaction(function () use ($assembleiaId, $tipoCargoId, $membroId, $protocoloId, $userId) {
            // Validações
            $this->validarAtribuicaoCargos($assembleiaId, [$tipoCargoId => $membroId]);

            // Verifica se já existe cargo ativo do mesmo tipo
            $cargoExistente = CargoAssembleia::getCargoAtivo($assembleiaId, $tipoCargoId);
            if ($cargoExistente) {
                $cargoExistente->finalizar('Substituído por nova atribuição');
            }

            // Cria o novo cargo
            $cargo = CargoAssembleia::create([
                'assembleia_id' => $assembleiaId,
                'membro_id' => $membroId,
                'tipo_cargo_id' => $tipoCargoId,
                'data_inicio' => now(),
                'ativo' => true,
                'protocolo_id' => $protocoloId,
                'atribuido_por' => $userId,
            ]);

            $this->logOperacao('atribuir_cargo_especifico', [
                'cargo_id' => $cargo->id,
                'assembleia_id' => $assembleiaId,
                'tipo_cargo_id' => $tipoCargoId,
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
        return CargoAssembleia::where('assembleia_id', $assembleiaId)
            ->where('ativo', true)
            ->with(['membro', 'tipoCargo', 'protocolo'])
            ->orderBy('tipo_cargo_id')
            ->get();
    }

    /**
     * Obtém histórico de cargos de uma assembleia
     */
    public function getHistoricoCargos(int $assembleiaId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return CargoAssembleia::where('assembleia_id', $assembleiaId)
            ->with(['membro', 'tipoCargo', 'protocolo', 'atribuidoPor'])
            ->orderBy('data_inicio', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Verifica se um membro pode ocupar um cargo específico
     */
    public function podeOcuparCargo(string $membroId, int $tipoCargoId): array
    {
        $membro = Membro::find($membroId);
        if (!$membro) {
            return ['pode' => false, 'motivo' => 'Membro não encontrado'];
        }

        if (!$membro->isMeninaAtiva()) {
            return ['pode' => false, 'motivo' => 'Apenas Meninas Ativas podem ocupar cargos de assembleia'];
        }

        if ($membro->status !== 'ativa') {
            return ['pode' => false, 'motivo' => 'Membro deve estar com status ativo'];
        }

        // Verifica se já ocupa outro cargo ativo
        $cargoAtual = CargoAssembleia::where('membro_id', $membroId)
            ->where('ativo', true)
            ->first();

        if ($cargoAtual) {
            return ['pode' => false, 'motivo' => 'Membro já ocupa outro cargo ativo'];
        }

        return ['pode' => true, 'motivo' => null];
    }

    /**
     * Obtém membros elegíveis para cargos de assembleia
     */
    public function getMembrosElegiveis(int $assembleiaId): \Illuminate\Database\Eloquent\Collection
    {
        return Membro::where('assembleia_id', $assembleiaId)
            ->meninasAtivas()
            ->where('status', 'ativa')
            ->whereDoesntHave('cargosAssembleia', function ($q) {
                $q->where('ativo', true);
            })
            ->orderBy('nome_completo')
            ->get();
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
     * Log de operações
     */
    private function logOperacao(string $operacao, array $dados): void
    {
        Log::info("CargoAssembleiaService: {$operacao}", $dados);
    }
}