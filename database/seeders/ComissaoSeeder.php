<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comissao;

class ComissaoSeeder extends Seeder
{
    public function run(): void
    {
        // Primeiro, precisamos pegar a jurisdição criada
        $jurisdicao = \App\Models\Jurisdicao::first();
        
        if (!$jurisdicao) {
            $this->command->error('Nenhuma jurisdição encontrada. Execute o JurisdicaoSeeder primeiro.');
            return;
        }

        // Comissões Padrão conforme documentação
        $comissoes = [
            [
                'jurisdicao_id' => $jurisdicao->id,
                'nome' => 'Comissão de Ritualística',
                'descricao' => 'Responsável por questões relacionadas ao ritual e cerimônias da IORG.',
                'ativa' => true,
            ],
            [
                'jurisdicao_id' => $jurisdicao->id,
                'nome' => 'Comissão de Legislação',
                'descricao' => 'Responsável por questões legais e regulamentares da Jurisdição.',
                'ativa' => true,
            ],
            [
                'jurisdicao_id' => $jurisdicao->id,
                'nome' => 'Comissão de Tradução',
                'descricao' => 'Responsável por traduções de documentos e materiais oficiais.',
                'ativa' => true,
            ],
            [
                'jurisdicao_id' => $jurisdicao->id,
                'nome' => 'Comissão de Comunicação',
                'descricao' => 'Responsável pela comunicação oficial da Jurisdição e divulgação de eventos.',
                'ativa' => true,
            ],
        ];

        foreach ($comissoes as $comissao) {
            Comissao::create($comissao);
        }

        $this->command->info('Comissões criadas com sucesso!');
    }
}
