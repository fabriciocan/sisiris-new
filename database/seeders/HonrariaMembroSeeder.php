<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HonrariaMembro;
use App\Models\Membro;
use App\Models\User;

class HonrariaMembroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Busca alguns membros para adicionar honrarias de exemplo
        $membros = Membro::limit(5)->get();
        $adminUser = User::where('email', 'admin@iorgpr.org.br')->first();

        if ($membros->count() > 0 && $adminUser) {
            // Adiciona Coração das Cores para alguns membros
            foreach ($membros->take(2) as $index => $membro) {
                HonrariaMembro::create([
                    'membro_id' => $membro->id,
                    'tipo_honraria' => 'coracao_cores',
                    'ano_recebimento' => 2023 + $index, // Anos diferentes
                    'observacoes' => 'Honraria concedida por dedicação ao IORG',
                    'atribuido_por' => $adminUser->id,
                ]);
            }

            // Adiciona Grande Cruz das Cores para o último membro
            if ($membros->count() >= 3) {
                HonrariaMembro::create([
                    'membro_id' => $membros->get(2)->id, // Terceiro membro
                    'tipo_honraria' => 'grande_cruz_cores',
                    'ano_recebimento' => 2024,
                    'observacoes' => 'Honraria por serviços excepcionais prestados ao IORG',
                    'atribuido_por' => $adminUser->id,
                ]);
            }

            // Homenageados do Ano pode ser recebido múltiplas vezes
            foreach ($membros as $index => $membro) {
                // Homenageado em 2023
                HonrariaMembro::create([
                    'membro_id' => $membro->id,
                    'tipo_honraria' => 'homenageados_ano',
                    'ano_recebimento' => 2023,
                    'observacoes' => 'Homenageada por dedicação excepcional em 2023',
                    'atribuido_por' => $adminUser->id,
                ]);

                // Alguns membros também homenageados em 2024
                if ($index < 2) {
                    HonrariaMembro::create([
                        'membro_id' => $membro->id,
                        'tipo_honraria' => 'homenageados_ano',
                        'ano_recebimento' => 2024,
                        'observacoes' => 'Homenageada novamente em 2024',
                        'atribuido_por' => $adminUser->id,
                    ]);
                }
            }
        }
    }
}
