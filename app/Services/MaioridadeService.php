<?php

namespace App\Services;

use App\Models\Protocolo;
use App\Models\Membro;
use App\Models\TipoUsuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaioridadeService
{
    /**
     * Process maioridade ceremony for all members in the protocol
     */
    public function processMaioridadeCeremony(Protocolo $protocolo, string $dataCerimonia): void
    {
        if ($protocolo->tipo_protocolo !== 'maioridade') {
            throw new \InvalidArgumentException('Este serviço é apenas para protocolos de maioridade');
        }

        DB::transaction(function () use ($protocolo, $dataCerimonia) {
            $tipoMaioridade = TipoUsuario::where('codigo', TipoUsuario::MAIORIDADE)->first();
            
            if (!$tipoMaioridade) {
                throw new \Exception('Tipo de usuário "Maioridade" não encontrado no sistema');
            }

            $membrosAtualizados = 0;

            foreach ($protocolo->membros as $membro) {
                // Validate that member is eligible
                if (!$this->isMemberEligible($membro)) {
                    Log::warning('Member not eligible for maioridade ceremony', [
                        'membro_id' => $membro->id,
                        'protocolo_id' => $protocolo->id,
                    ]);
                    continue;
                }

                // Update member to maioridade status
                $membro->update([
                    'data_maioridade' => $dataCerimonia,
                    'status' => 'maioridade',
                    'tipo_usuario_id' => $tipoMaioridade->id,
                ]);

                // Update user type if member has a user account
                if ($membro->user) {
                    $membro->user->update([
                        'tipo_usuario_id' => $tipoMaioridade->id,
                    ]);
                }

                $membrosAtualizados++;
            }

            Log::info('Maioridade ceremony processed successfully', [
                'protocolo_id' => $protocolo->id,
                'data_cerimonia' => $dataCerimonia,
                'membros_atualizados' => $membrosAtualizados,
            ]);
        });
    }

    /**
     * Check if member is eligible for maioridade ceremony
     */
    public function isMemberEligible(Membro $membro): bool
    {
        // Must be menina ativa
        if (!$membro->isMeninaAtiva()) {
            return false;
        }

        // Must be active
        if ($membro->status !== 'ativa') {
            return false;
        }

        // Must not already have maioridade
        if ($membro->data_maioridade) {
            return false;
        }

        return true;
    }

    /**
     * Get eligible members for maioridade ceremony from an assembleia
     */
    public function getEligibleMembers(int $assembleiaId): \Illuminate\Database\Eloquent\Collection
    {
        return Membro::meninasAtivas()
            ->ativas()
            ->where('assembleia_id', $assembleiaId)
            ->whereNull('data_maioridade')
            ->get()
            ->filter(fn($membro) => $this->isMemberEligible($membro));
    }

    /**
     * Validate protocol data before processing
     */
    public function validateProtocolData(Protocolo $protocolo): array
    {
        $errors = [];

        if ($protocolo->tipo_protocolo !== 'maioridade') {
            $errors[] = 'Protocolo deve ser do tipo maioridade';
        }

        if (!$protocolo->data_cerimonia) {
            $errors[] = 'Data da cerimônia é obrigatória';
        }

        if ($protocolo->membros->isEmpty()) {
            $errors[] = 'Protocolo deve ter pelo menos uma menina selecionada';
        }

        // Validate each member
        foreach ($protocolo->membros as $membro) {
            if (!$this->isMemberEligible($membro)) {
                $errors[] = "Menina {$membro->nome_completo} não é elegível para maioridade";
            }
        }

        return $errors;
    }

    /**
     * Get ceremony statistics
     */
    public function getCeremonyStatistics(Protocolo $protocolo): array
    {
        $totalMembers = $protocolo->membros->count();
        $eligibleMembers = $protocolo->membros->filter(fn($membro) => $this->isMemberEligible($membro))->count();
        $ineligibleMembers = $totalMembers - $eligibleMembers;

        return [
            'total_members' => $totalMembers,
            'eligible_members' => $eligibleMembers,
            'ineligible_members' => $ineligibleMembers,
            'ceremony_date' => $protocolo->data_cerimonia,
            'assembleia' => $protocolo->assembleia->nome,
        ];
    }
}