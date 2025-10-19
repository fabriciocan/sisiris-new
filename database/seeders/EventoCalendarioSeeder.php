<?php

namespace Database\Seeders;

use App\Models\EventoCalendario;
use App\Models\Assembleia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventoCalendarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assembleia = Assembleia::first();
        $user = User::first();

        if (!$assembleia || !$user) {
            $this->command->warn('É necessário ter pelo menos uma assembleia e um usuário para criar eventos.');
            return;
        }

        $eventos = [
            [
                'titulo' => 'Reunião Ordinária de Novembro',
                'descricao' => 'Reunião mensal ordinária para discussão dos assuntos da Loja.',
                'tipo' => 'reuniao_ordinaria',
                'data_inicio' => Carbon::now()->addDays(5)->setTime(19, 30),
                'data_fim' => Carbon::now()->addDays(5)->setTime(22, 0),
                'local' => 'Templo da Loja',
                'endereco' => 'Rua dos Maçons, 123',
                'publico' => false,
                'cor_evento' => '#3B82F6',
            ],
            [
                'titulo' => 'Assembleia Geral Ordinária',
                'descricao' => 'Assembleia Geral Ordinária para votação de assuntos importantes.',
                'tipo' => 'assembleia_geral',
                'data_inicio' => Carbon::now()->addDays(12)->setTime(14, 0),
                'data_fim' => Carbon::now()->addDays(12)->setTime(18, 0),
                'local' => 'Auditório Principal',
                'endereco' => 'Rua dos Maçons, 123',
                'publico' => false,
                'cor_evento' => '#8B5CF6',
            ],
            [
                'titulo' => 'Sessão Magna de São João',
                'descricao' => 'Sessão Magna comemorativa do Dia de São João.',
                'tipo' => 'sessao_magna',
                'data_inicio' => Carbon::now()->addDays(20)->setTime(19, 0),
                'data_fim' => Carbon::now()->addDays(20)->setTime(23, 0),
                'local' => 'Templo Principal',
                'endereco' => 'Rua dos Maçons, 123',
                'publico' => true,
                'cor_evento' => '#10B981',
            ],
            [
                'titulo' => 'Cerimônia de Iniciação',
                'descricao' => 'Cerimônia de iniciação de novos irmãos.',
                'tipo' => 'iniciacao',
                'data_inicio' => Carbon::now()->addDays(8)->setTime(20, 0),
                'data_fim' => Carbon::now()->addDays(8)->setTime(22, 30),
                'local' => 'Templo da Loja',
                'endereco' => 'Rua dos Maçons, 123',
                'publico' => false,
                'cor_evento' => '#6366F1',
            ],
            [
                'titulo' => 'Reunião Extraordinária',
                'descricao' => 'Reunião extraordinária para discussão de assunto urgente.',
                'tipo' => 'reuniao_extraordinaria',
                'data_inicio' => Carbon::now()->addDays(3)->setTime(19, 0),
                'data_fim' => Carbon::now()->addDays(3)->setTime(21, 0),
                'local' => 'Sala de Reuniões',
                'endereco' => 'Rua dos Maçons, 123',
                'publico' => false,
                'cor_evento' => '#EF4444',
            ],
        ];

        foreach ($eventos as $eventoData) {
            EventoCalendario::create([
                ...$eventoData,
                'assembleia_id' => $assembleia->id,
                'criado_por' => $user->id,
            ]);
        }

        $this->command->info('Eventos de exemplo criados com sucesso!');
    }
}
