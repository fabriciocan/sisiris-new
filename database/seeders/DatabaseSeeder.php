<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            JurisdicaoSeeder::class,
            TipoCargoAssembleiaSeeder::class,
            ComissaoSeeder::class,
            AdminUserSeeder::class,
            AssembleiaExemploSeeder::class,
            HonrariaMembroSeeder::class,
            MembroAdicionalSeeder::class,
        ]);
    }
}
