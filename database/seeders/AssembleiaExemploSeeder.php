<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jurisdicao;
use App\Models\Assembleia;
use App\Models\User;
use App\Models\Membro;

class AssembleiaExemploSeeder extends Seeder
{
    public function run(): void
    {
        $jurisdicao = Jurisdicao::first();

        if (!$jurisdicao) {
            $this->command->error('Nenhuma jurisdição encontrada. Execute o JurisdicaoSeeder primeiro.');
            return;
        }

        // Criar assembleia de exemplo
        $assembleia = Assembleia::create([
            'jurisdicao_id' => $jurisdicao->id,
            'numero' => 1,
            'nome' => 'Luz da Esperança',
            'cidade' => 'Curitiba',
            'estado' => 'PR',
            'endereco_completo' => 'Rua das Flores, 123 - Centro - Curitiba/PR - CEP: 80010-100',
            'data_fundacao' => '2020-03-15',
            'email' => 'assembleia1@iorgpr.org.br',
            'telefone' => '(41) 3333-1111',
            'ativa' => true,
            'loja_patrocinadora' => 'Loja Maçônica Exemplo Nº 100',
        ]);

        // Criar alguns usuários e membros de exemplo
        $users = [
            [
                'name' => 'Maria Silva',
                'email' => 'maria.silva@exemplo.com',
                'telefone' => '(41) 99999-1111',
                'data_nascimento' => '1995-05-15',
                'cpf' => '123.456.789-01',
                'numero_membro' => '1001',
                'role' => 'admin_assembleia'
            ],
            [
                'name' => 'Ana Santos',
                'email' => 'ana.santos@exemplo.com',
                'telefone' => '(41) 99999-2222',
                'data_nascimento' => '2005-08-20',
                'cpf' => '123.456.789-02',
                'numero_membro' => '1002',
                'role' => 'menina_ativa'
            ],
            [
                'name' => 'Julia Costa',
                'email' => 'julia.costa@exemplo.com',
                'telefone' => '(41) 99999-3333',
                'data_nascimento' => '2006-12-10',
                'cpf' => '123.456.789-03',
                'numero_membro' => '1003',
                'role' => 'menina_ativa'
            ]
        ];

        foreach ($users as $userData) {
            $nivelAcesso = $userData['role'] === 'admin_assembleia' ? 'admin_assembleia' : 'membro';
            
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => bcrypt('password'),
                'telefone' => $userData['telefone'],
                'data_nascimento' => $userData['data_nascimento'],
                'cpf' => $userData['cpf'],
                'nivel_acesso' => $nivelAcesso,
            ]);

            $user->assignRole($userData['role']);

            // Criar membro correspondente
            Membro::create([
                'user_id' => $user->id,
                'assembleia_id' => $assembleia->id,
                'numero_membro' => $userData['numero_membro'],
                'nome_completo' => $userData['name'],
                'data_nascimento' => $userData['data_nascimento'],
                'cpf' => $userData['cpf'],
                'telefone' => $userData['telefone'],
                'email' => $userData['email'],
                'endereco_completo' => 'Endereço de ' . $userData['name'],
                'nome_mae' => 'Mãe de ' . $userData['name'],
                'telefone_mae' => '(41) 98888-0000',
                'contato_responsavel' => '(41) 98888-0000',
                'data_iniciacao' => now()->subMonths(6),
                'madrinha' => 'Madrinha Exemplo',
                'status' => 'ativa',
            ]);
        }

        $this->command->info('Assembleia de exemplo e membros criados com sucesso!');
    }
}
