<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Criar usuário admin padrão
        $admin = User::create([
            'name' => 'Administrador IORG',
            'email' => 'admin@iorgpr.org.br',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
            'nivel_acesso' => 'membro_jurisdicao',
        ]);

        // Atribuir role de membro_jurisdicao (super admin)
        $admin->assignRole('membro_jurisdicao');

        $this->command->info('Usuário administrador criado com sucesso!');
        $this->command->info('Email: admin@iorgpr.org.br');
        $this->command->info('Senha: admin123');
        $this->command->warn('IMPORTANTE: Altere a senha após o primeiro login!');
    }
}
