<?php

namespace App\Services;

use App\Models\Protocolo;
use App\Models\Membro;
use App\Models\User;
use App\Models\TipoUsuario;
use App\Notifications\FirstAccessCredentials;
use App\Jobs\SendFirstAccessCredentialsJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class IniciacaoService
{
    /**
     * Process the iniciacao protocol completion
     */
    public function processProtocolCompletion(Protocolo $protocolo): array
    {
        if ($protocolo->tipo_protocolo !== 'iniciacao') {
            throw new \InvalidArgumentException('Este serviço é apenas para protocolos de iniciação');
        }

        if ($protocolo->status !== 'concluido') {
            throw new \InvalidArgumentException('Protocolo deve estar concluído para processar');
        }

        $novasMeninas = $protocolo->dados_membros ?? [];
        $results = [];

        DB::transaction(function () use ($novasMeninas, $protocolo, &$results) {
            foreach ($novasMeninas as $index => $dadosMenina) {
                try {
                    $result = $this->createMemberAndUser($dadosMenina, $protocolo);
                    $results[] = $result;
                } catch (\Exception $e) {
                    $results[] = [
                        'success' => false,
                        'nome' => $dadosMenina['nome_completo'] ?? 'Desconhecido',
                        'error' => $e->getMessage(),
                        'index' => $index
                    ];
                }
            }
        });

        // Log the completion
        $protocolo->historico()->create([
            'user_id' => Auth::id(),
            'status_anterior' => 'concluido',
            'status_novo' => 'concluido',
            'comentario' => 'Processamento de iniciação concluído. Total: ' . count($novasMeninas) . 
                          ', Sucessos: ' . count(array_filter($results, fn($r) => $r['success'])) . 
                          ', Erros: ' . count(array_filter($results, fn($r) => !$r['success'])),
            'created_at' => now(),
        ]);

        return $results;
    }

    /**
     * Create member and user profile for a new menina
     */
    protected function createMemberAndUser(array $dadosMenina, Protocolo $protocolo): array
    {
        // Get the TipoUsuario for Menina Ativa
        $tipoUsuario = TipoUsuario::where('codigo', TipoUsuario::MENINA_ATIVA)->first();
        
        if (!$tipoUsuario) {
            throw new \Exception('Tipo de usuário "Menina Ativa" não encontrado');
        }

        // Generate unique member number
        $numeroMembro = $this->generateMemberNumber($protocolo->assembleia_id);

        // Clean CPF
        $cpf = preg_replace('/[^0-9]/', '', $dadosMenina['cpf']);

        // Create the member record
        $membro = Membro::create([
            'numero_membro' => $numeroMembro,
            'assembleia_id' => $protocolo->assembleia_id,
            'tipo_usuario_id' => $tipoUsuario->id,
            'nome_completo' => $dadosMenina['nome_completo'],
            'data_nascimento' => Carbon::parse($dadosMenina['data_nascimento']),
            'cpf' => $cpf,
            'telefone' => $dadosMenina['telefone'],
            'email' => $dadosMenina['email'],
            'endereco_completo' => $dadosMenina['endereco_completo'],
            'nome_mae' => $dadosMenina['nome_mae'],
            'telefone_mae' => $dadosMenina['telefone_mae'],
            'nome_pai' => $dadosMenina['nome_pai'] ?? null,
            'telefone_pai' => $dadosMenina['telefone_pai'] ?? null,
            'responsavel_legal' => $dadosMenina['responsavel_legal'] ?? null,
            'contato_responsavel' => $dadosMenina['contato_responsavel'] ?? null,
            'data_iniciacao' => Carbon::parse($dadosMenina['data_iniciacao']),
            'madrinha' => $this->getMadrinhaName($dadosMenina['madrinha_id']),
            'status' => 'ativa',
        ]);

        // Generate temporary password
        $temporaryPassword = $this->generateTemporaryPassword();

        // Create user account
        $user = User::create([
            'name' => $dadosMenina['nome_completo'],
            'email' => $dadosMenina['email'],
            'password' => Hash::make($temporaryPassword),
            'tipo_usuario_id' => $tipoUsuario->id,
            'nivel_acesso' => 'membro',
            'email_verified_at' => null, // Will be verified on first login
        ]);

        // Link member to user
        $membro->update(['user_id' => $user->id]);

        // Assign default role
        $user->assignRole('membro');

        // Send first access email
        $emailSent = $this->sendFirstAccessEmail($user, $temporaryPassword);

        return [
            'success' => true,
            'nome' => $dadosMenina['nome_completo'],
            'membro_id' => $membro->id,
            'user_id' => $user->id,
            'numero_membro' => $numeroMembro,
            'email_sent' => $emailSent,
            'temporary_password' => $temporaryPassword, // For debugging/admin purposes
        ];
    }

    /**
     * Generate unique member number
     */
    protected function generateMemberNumber(int $assembleiaId): string
    {
        $year = date('Y');
        $assembleiaCode = str_pad($assembleiaId, 2, '0', STR_PAD_LEFT);
        
        // Get the last member number for this assembleia this year
        $lastMember = Membro::where('assembleia_id', $assembleiaId)
            ->whereYear('created_at', $year)
            ->orderBy('numero_membro', 'desc')
            ->first();

        if ($lastMember && preg_match('/(\d{4})$/', $lastMember->numero_membro, $matches)) {
            $sequence = intval($matches[1]) + 1;
        } else {
            $sequence = 1;
        }

        $sequenceStr = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        
        return "{$year}{$assembleiaCode}{$sequenceStr}";
    }

    /**
     * Get madrinha name by ID
     */
    protected function getMadrinhaName(int $madrinhaId): string
    {
        $madrinha = Membro::find($madrinhaId);
        return $madrinha ? $madrinha->nome_completo : 'Não informado';
    }

    /**
     * Generate temporary password
     */
    protected function generateTemporaryPassword(): string
    {
        // Generate a readable temporary password
        $words = ['Sol', 'Lua', 'Mar', 'Rio', 'Flor', 'Casa', 'Vida', 'Amor'];
        $word = $words[array_rand($words)];
        $number = rand(100, 999);
        
        return $word . $number;
    }

    /**
     * Send first access email to new member
     */
    protected function sendFirstAccessEmail(User $user, string $temporaryPassword): bool
    {
        try {
            // Dispatch job to send email asynchronously
            SendFirstAccessCredentialsJob::dispatch($user, $temporaryPassword);
            return true;
        } catch (\Exception $e) {
            // Log the error but don't fail the entire process
            \Illuminate\Support\Facades\Log::error('Failed to dispatch first access email job', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Validate member data before creation
     */
    public function validateMemberData(array $dadosMenina): array
    {
        $errors = [];

        // Check if CPF already exists
        $cpf = preg_replace('/[^0-9]/', '', $dadosMenina['cpf'] ?? '');
        if (Membro::where('cpf', $cpf)->exists()) {
            $errors[] = "CPF {$dadosMenina['cpf']} já está cadastrado";
        }

        // Check if email already exists
        if (Membro::where('email', $dadosMenina['email'])->exists()) {
            $errors[] = "E-mail {$dadosMenina['email']} já está cadastrado";
        }

        if (User::where('email', $dadosMenina['email'])->exists()) {
            $errors[] = "E-mail {$dadosMenina['email']} já está em uso";
        }

        // Validate madrinha exists and is active
        $madrinha = Membro::find($dadosMenina['madrinha_id'] ?? 0);
        if (!$madrinha || !in_array($madrinha->status, ['ativa', 'maioridade'])) {
            $errors[] = "Madrinha selecionada não é válida ou não está ativa";
        }

        return $errors;
    }

    /**
     * Get summary of protocol processing
     */
    public function getProcessingSummary(array $results): array
    {
        $total = count($results);
        $successful = count(array_filter($results, fn($r) => $r['success']));
        $failed = $total - $successful;

        return [
            'total' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
            'results' => $results,
        ];
    }
}