<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jurisdicao;

class JurisdicaoSeeder extends Seeder
{
    public function run(): void
    {
        $jurisdicao = [
            'nome' => 'Grande Jurisdição do Paraná',
            'sigla' => 'IORGPR',
            'email' => 'iorgpr@iorgpr.org.br',
            'telefone' => '(41) 3333-4444',
            'endereco_completo' => 'Av. Marechal Floriano Peixoto, 1234 - Centro - Curitiba/PR - CEP: 80010-000',
            'ativa' => true,
        ];

        Jurisdicao::create($jurisdicao);

        $this->command->info('Jurisdição criada com sucesso!');
    }
}
