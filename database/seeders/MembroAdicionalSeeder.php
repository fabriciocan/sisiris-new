<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Assembleia;
use App\Models\Membro;
use Illuminate\Support\Facades\Hash;

class MembroAdicionalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar a primeira assembleia disponível
        $assembleia = Assembleia::first();
        
        if (!$assembleia) {
            $this->command->error('Nenhuma assembleia encontrada. Execute os seeders de assembleia primeiro.');
            return;
        }

        // Criar usuário para o novo membro
        $user = User::create([
            'name' => 'Maria Silva Santos',
            'email' => 'maria.santos@iorgpr.org.br',
            'password' => Hash::make('Maria@2025'),
            'email_verified_at' => now(),
            'nivel_acesso' => 'membro',
        ]);

        // Criar perfil de membro
        $membro = Membro::create([
            'user_id' => $user->id,
            'assembleia_id' => $assembleia->id,
            'numero_membro' => '1025',
            'nome_completo' => 'Maria Silva Santos',
            'data_nascimento' => '2006-03-15',
            'cpf' => '456.789.123-45',
            'telefone' => '(41) 98765-4321',
            'email' => 'maria.santos@iorgpr.org.br',
            'endereco_completo' => 'Rua das Flores, 789 - Centro, Curitiba - PR',
            'nome_mae' => 'Ana Paula Silva Santos',
            'telefone_mae' => '(41) 98888-7777',
            'contato_responsavel' => '(41) 98888-7777',
            'madrinha' => 'Juliana Costa',
            'data_iniciacao' => '2020-06-20',
            'status' => 'ativa',
        ]);

        // Atribuir role de menina ativa
        $user->assignRole('menina_ativa');

        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('✓ Novo membro criado com sucesso!');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('CREDENCIAIS DE ACESSO:');
        $this->command->info('────────────────────────────────────────────────────────');
        $this->command->info('Nome: Maria Silva Santos');
        $this->command->info('Email: maria.santos@iorgpr.org.br');
        $this->command->info('Senha: Maria@2025');
        $this->command->info('Número de Membro: 1025');
        $this->command->info('Assembleia: ' . $assembleia->nome);
        $this->command->info('Status: Ativa');
        $this->command->info('Role: Menina Ativa');
        $this->command->info('────────────────────────────────────────────────────────');
        $this->command->info('');
    }
}
