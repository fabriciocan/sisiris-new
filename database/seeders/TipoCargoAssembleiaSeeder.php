<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoCargoAssembleia;

class TipoCargoAssembleiaSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            // CARGOS ADMINISTRATIVOS (is_admin = true)
            // Estes cargos têm acesso completo de administração da assembleia
            [
                'nome' => 'Ilustre Preceptora',
                'categoria' => 'administrativo',
                'is_admin' => true,
                'ordem' => 1,
                'criado_por' => 'sistema',
                'descricao' => 'Cargo principal da assembleia, gestão completa'
            ],
            [
                'nome' => 'Ilustre Preceptora Adjunta',
                'categoria' => 'administrativo',
                'is_admin' => true,
                'ordem' => 2,
                'criado_por' => 'sistema',
                'descricao' => 'Apoio à Ilustre Preceptora, mesmos acessos administrativos'
            ],
            [
                'nome' => 'Presidente do Conselho',
                'categoria' => 'administrativo',
                'is_admin' => true,
                'ordem' => 3,
                'criado_por' => 'sistema',
                'descricao' => 'Gestão do conselho consultivo, acessos administrativos completos'
            ],
            [
                'nome' => 'Preceptora Mãe',
                'categoria' => 'administrativo',
                'is_admin' => true,
                'ordem' => 4,
                'criado_por' => 'sistema',
                'descricao' => 'Orientação das meninas, acessos administrativos completos'
            ],
            [
                'nome' => 'Preceptora Mãe Adjunta',
                'categoria' => 'administrativo',
                'is_admin' => true,
                'ordem' => 5,
                'criado_por' => 'sistema',
                'descricao' => 'Apoio à Preceptora Mãe, acessos administrativos completos'
            ],
            [
                'nome' => 'Arquivista',
                'categoria' => 'administrativo',
                'is_admin' => true,
                'ordem' => 6,
                'criado_por' => 'sistema',
                'descricao' => 'Gestão de documentos, acessos administrativos completos'
            ],

            // CARGOS DAS MENINAS (is_admin = false)
            // Estes são os cargos ocupados pelas meninas ativas da assembleia
            [
                'nome' => 'Fé',
                'categoria' => 'assembleia',
                'is_admin' => false,
                'ordem' => 10,
                'criado_por' => 'sistema',
                'descricao' => 'Cargo ocupado por menina ativa'
            ],
            [
                'nome' => 'Caridade',
                'categoria' => 'assembleia',
                'is_admin' => false,
                'ordem' => 11,
                'criado_por' => 'sistema',
                'descricao' => 'Cargo ocupado por menina ativa'
            ],
            [
                'nome' => 'Esperança',
                'categoria' => 'assembleia',
                'is_admin' => false,
                'ordem' => 12,
                'criado_por' => 'sistema',
                'descricao' => 'Cargo ocupado por menina ativa'
            ],
            [
                'nome' => 'Natureza',
                'categoria' => 'assembleia',
                'is_admin' => false,
                'ordem' => 13,
                'criado_por' => 'sistema',
                'descricao' => 'Cargo ocupado por menina ativa'
            ],
            [
                'nome' => 'Imortalidade',
                'categoria' => 'assembleia',
                'is_admin' => false,
                'ordem' => 14,
                'criado_por' => 'sistema',
                'descricao' => 'Cargo ocupado por menina ativa'
            ],
            [
                'nome' => 'Fidelidade',
                'categoria' => 'assembleia',
                'is_admin' => false,
                'ordem' => 15,
                'criado_por' => 'sistema',
                'descricao' => 'Cargo ocupado por menina ativa'
            ],
            [
                'nome' => 'Patriotismo',
                'categoria' => 'assembleia',
                'is_admin' => false,
                'ordem' => 16,
                'criado_por' => 'sistema',
                'descricao' => 'Cargo ocupado por menina ativa'
            ],
            [
                'nome' => 'Serviço',
                'categoria' => 'assembleia',
                'is_admin' => false,
                'ordem' => 17,
                'criado_por' => 'sistema',
                'descricao' => 'Cargo ocupado por menina ativa'
            ],
            [
                'nome' => 'Tesoureira',
                'categoria' => 'assembleia',
                'is_admin' => false,
                'ordem' => 18,
                'criado_por' => 'sistema',
                'descricao' => 'Cargo de tesoureira ocupado por menina ativa'
            ],

            // CARGOS DA GRANDE ASSEMBLEIA (is_admin = false)
            // Cargos honoríficos atribuídos a meninas ativas pela Jurisdição
            [
                'nome' => 'Grande Ilustre Preceptora',
                'categoria' => 'grande_assembleia',
                'is_admin' => false,
                'ordem' => 20,
                'criado_por' => 'sistema',
                'descricao' => 'Cargo honorífico da Grande Assembleia'
            ],
            [
                'nome' => 'Grande Ilustre Preceptora Adjunta',
                'categoria' => 'grande_assembleia',
                'is_admin' => false,
                'ordem' => 21,
                'criado_por' => 'sistema',
                'descricao' => 'Cargo honorífico da Grande Assembleia'
            ],
            [
                'nome' => 'Grande Fé',
                'categoria' => 'grande_assembleia',
                'is_admin' => false,
                'ordem' => 22,
                'criado_por' => 'sistema',
                'descricao' => 'Cargo honorífico da Grande Assembleia'
            ],
            [
                'nome' => 'Grande Caridade',
                'categoria' => 'grande_assembleia',
                'is_admin' => false,
                'ordem' => 23,
                'criado_por' => 'sistema',
                'descricao' => 'Cargo honorífico da Grande Assembleia'
            ],
            [
                'nome' => 'Grande Esperança',
                'categoria' => 'grande_assembleia',
                'is_admin' => false,
                'ordem' => 24,
                'criado_por' => 'sistema',
                'descricao' => 'Cargo honorífico da Grande Assembleia'
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoCargoAssembleia::create($tipo);
        }

        $this->command->info('Tipos de Cargos de Assembleia criados com sucesso!');
    }
}
