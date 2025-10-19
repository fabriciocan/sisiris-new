<?php

namespace Database\Factories;

use App\Models\TipoUsuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TipoUsuario>
 */
class TipoUsuarioFactory extends Factory
{
    protected $model = TipoUsuario::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => $this->faker->unique()->slug(2),
            'nome' => $this->faker->words(2, true),
            'descricao' => $this->faker->sentence(),
            'requer_assembleia' => $this->faker->boolean(),
            'requer_pais_responsaveis' => $this->faker->boolean(),
            'campos_especificos' => [],
        ];
    }

    /**
     * Estado para Menina Ativa
     */
    public function meninaAtiva(): static
    {
        return $this->state(fn (array $attributes) => [
            'codigo' => TipoUsuario::MENINA_ATIVA,
            'nome' => 'Menina Ativa',
            'descricao' => 'Membro ativo da assembleia, menor de idade',
            'requer_assembleia' => true,
            'requer_pais_responsaveis' => true,
            'campos_especificos' => [
                'madrinha' => ['obrigatorio' => false, 'tipo' => 'string'],
            ],
        ]);
    }

    /**
     * Estado para Maioridade
     */
    public function maioridade(): static
    {
        return $this->state(fn (array $attributes) => [
            'codigo' => TipoUsuario::MAIORIDADE,
            'nome' => 'Maioridade',
            'descricao' => 'Membro que atingiu a maioridade',
            'requer_assembleia' => true,
            'requer_pais_responsaveis' => false,
            'campos_especificos' => [
                'data_maioridade' => ['obrigatorio' => true, 'tipo' => 'date'],
            ],
        ]);
    }

    /**
     * Estado para Tio Maçom
     */
    public function tioMacom(): static
    {
        return $this->state(fn (array $attributes) => [
            'codigo' => TipoUsuario::TIO_MACOM,
            'nome' => 'Tio Maçom',
            'descricao' => 'Membro masculino da maçonaria',
            'requer_assembleia' => false,
            'requer_pais_responsaveis' => false,
            'campos_especificos' => [
                'loja_maconica' => ['obrigatorio' => true, 'tipo' => 'string'],
                'grau_maconico' => ['obrigatorio' => true, 'tipo' => 'enum', 'opcoes' => ['aprendiz', 'companheiro', 'mestre']],
                'data_companheiro' => ['obrigatorio' => false, 'tipo' => 'date'],
                'data_mestre' => ['obrigatorio' => false, 'tipo' => 'date'],
            ],
        ]);
    }

    /**
     * Estado para Tia Estrela do Oriente
     */
    public function tiaEstrela(): static
    {
        return $this->state(fn (array $attributes) => [
            'codigo' => TipoUsuario::TIA_ESTRELA,
            'nome' => 'Tia Estrela do Oriente',
            'descricao' => 'Membro feminino da Ordem da Estrela do Oriente',
            'requer_assembleia' => false,
            'requer_pais_responsaveis' => false,
            'campos_especificos' => [
                'capitulo_estrela' => ['obrigatorio' => true, 'tipo' => 'string'],
                'data_iniciacao_arco_iris' => ['obrigatorio' => false, 'tipo' => 'date'],
            ],
        ]);
    }

    /**
     * Estado para Tio
     */
    public function tio(): static
    {
        return $this->state(fn (array $attributes) => [
            'codigo' => TipoUsuario::TIO,
            'nome' => 'Tio',
            'descricao' => 'Membro masculino sem vínculo maçônico',
            'requer_assembleia' => false,
            'requer_pais_responsaveis' => false,
            'campos_especificos' => [],
        ]);
    }

    /**
     * Estado para Tia
     */
    public function tia(): static
    {
        return $this->state(fn (array $attributes) => [
            'codigo' => TipoUsuario::TIA,
            'nome' => 'Tia',
            'descricao' => 'Membro feminino sem vínculo específico',
            'requer_assembleia' => false,
            'requer_pais_responsaveis' => false,
            'campos_especificos' => [],
        ]);
    }
}