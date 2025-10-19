<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Assembleia;
use App\Models\Membro;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar uma assembleia para o teste
        $assembleia = Assembleia::first();
        
        if (!$assembleia) {
            $this->command->error('Nenhuma assembleia encontrada. Execute o AssembleiaExemploSeeder primeiro.');
            return;
        }

        // Criar usuário com role admin_assembleia
        $adminAssembleia = User::create([
            'name' => 'Admin Assembleia',
            'email' => 'admin.assembleia@iorgpr.org.br',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
            'nivel_acesso' => 'admin_assembleia',
        ]);

        // Criar perfil de membro
        $membro = Membro::create([
            'user_id' => $adminAssembleia->id,
            'assembleia_id' => $assembleia->id,
            'numero_membro' => '901',
            'nome_completo' => 'Admin Assembleia Teste',
            'data_nascimento' => '1990-01-01',
            'cpf' => '123.456.789-00',
            'telefone' => '(41) 99999-9999',
            'email' => 'admin.assembleia@iorgpr.org.br',
            'endereco_completo' => 'Rua Teste, 123',
            'nome_mae' => 'Mãe Teste',
            'telefone_mae' => '(41) 88888-8888',
            'contato_responsavel' => '(41) 88888-8888',
            'madrinha' => 'Madrinha Teste',
            'status' => 'ativa',
        ]);

        // Atribuir role de admin_assembleia
        $adminAssembleia->assignRole('admin_assembleia');

        // Criar usuário sem roles especiais (menina ativa)
        $meninaAtiva = User::create([
            'name' => 'Menina Ativa',
            'email' => 'menina.ativa@iorgpr.org.br',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
            'nivel_acesso' => 'membro',
        ]);

        // Criar perfil de membro
        $membroAtiva = Membro::create([
            'user_id' => $meninaAtiva->id,
            'assembleia_id' => $assembleia->id,
            'numero_membro' => '902',
            'nome_completo' => 'Menina Ativa Teste',
            'data_nascimento' => '2005-01-01',
            'cpf' => '987.654.321-00',
            'telefone' => '(41) 77777-7777',
            'email' => 'menina.ativa@iorgpr.org.br',
            'endereco_completo' => 'Rua Teste 2, 456',
            'nome_mae' => 'Mãe Teste 2',
            'telefone_mae' => '(41) 66666-6666',
            'contato_responsavel' => '(41) 66666-6666',
            'madrinha' => 'Madrinha Teste 2',
            'status' => 'ativa',
        ]);

        // Atribuir role de menina_ativa
        $meninaAtiva->assignRole('menina_ativa');

        $this->command->info('Usuários de teste criados com sucesso!');
        $this->command->info('Admin Assembleia: admin.assembleia@iorgpr.org.br (senha: admin123)');
        $this->command->info('Menina Ativa: menina.ativa@iorgpr.org.br (senha: admin123)');
    }
}
