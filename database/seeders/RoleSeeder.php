<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Limpar cache de permissões
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Criar Permissions para o sistema de protocolos
        $permissions = [
            // Permissões gerais do sistema
            'view_all_assembleias',
            'manage_all_assembleias',
            'manage_own_assembleia',
            'view_assembleia_data',
            'view_own_data',
            
            // Permissões de membros
            'manage_membros',
            'view_membros',
            'create_membros',
            'edit_membros',
            'delete_membros',
            
            // Permissões de protocolos
            'create_protocolos',
            'view_protocolos',
            'edit_protocolos',
            'delete_protocolos',
            'approve_protocolos',
            'reject_protocolos',
            'manage_protocolo_taxes',
            'view_protocolo_logs',
            
            // Permissões específicas por tipo de protocolo
            'create_protocolo_maioridade',
            'create_protocolo_iniciacao',
            'create_protocolo_homenageados',
            'create_protocolo_coracao_cores',
            'create_protocolo_grande_cruz',
            'create_protocolo_afastamento',
            'create_protocolo_cargos_assembleia',
            'create_protocolo_cargos_conselho',
            
            // Permissões de aprovação específicas
            'approve_protocolo_maioridade',
            'approve_protocolo_iniciacao',
            'approve_protocolo_homenageados',
            'approve_protocolo_coracao_cores',
            'approve_protocolo_grande_cruz',
            'approve_protocolo_afastamento',
            'approve_protocolo_cargos_assembleia',
            'approve_protocolo_cargos_conselho',
            
            // Permissões de cargos
            'assign_cargos_assembleia',
            'assign_cargos_conselho',
            'view_cargos',
            'manage_cargos',
            
            // Permissões de honrarias
            'manage_honrarias',
            'approve_honrarias',
            'view_honrarias',
            
            // Permissões de sistema
            'view_all_tickets',
            'respond_tickets',
            'manage_calendario_assembleia',
            'view_relatorios',
            'manage_sistema',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Criar Roles e atribuir permissões

        // Role: super_admin - Acesso total ao sistema
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Role: membro_jurisdicao (Suprema Deputada, Oficial Executiva, Grande Deputado)
        // Pode aprovar protocolos de qualquer assembleia e selecionar assembleia ao criar protocolos
        $membroJurisdicao = Role::firstOrCreate(['name' => 'membro_jurisdicao']);
        $membroJurisdicao->syncPermissions([
            'view_all_assembleias',
            'manage_all_assembleias',
            'view_assembleia_data',
            'view_own_data',
            'manage_membros',
            'view_membros',
            'create_membros',
            'edit_membros',
            'create_protocolos',
            'view_protocolos',
            'edit_protocolos',
            'approve_protocolos',
            'reject_protocolos',
            'manage_protocolo_taxes',
            'view_protocolo_logs',
            // Pode criar todos os tipos de protocolo
            'create_protocolo_maioridade',
            'create_protocolo_iniciacao',
            'create_protocolo_homenageados',
            'create_protocolo_coracao_cores',
            'create_protocolo_grande_cruz',
            'create_protocolo_afastamento',
            'create_protocolo_cargos_assembleia',
            'create_protocolo_cargos_conselho',
            // Pode aprovar todos os tipos de protocolo
            'approve_protocolo_maioridade',
            'approve_protocolo_iniciacao',
            'approve_protocolo_homenageados',
            'approve_protocolo_coracao_cores',
            'approve_protocolo_grande_cruz',
            'approve_protocolo_afastamento',
            'approve_protocolo_cargos_assembleia',
            'approve_protocolo_cargos_conselho',
            'assign_cargos_assembleia',
            'assign_cargos_conselho',
            'view_cargos',
            'manage_cargos',
            'manage_honrarias',
            'approve_honrarias',
            'view_honrarias',
            'view_relatorios',
            'manage_sistema',
        ]);

        // Role: admin_assembleia (Ilustre Preceptora, Ilustre Preceptora Adjunta, 
        // Presidente do Conselho, Preceptora Mãe, Preceptora Mãe Adjunta)
        // Acesso completo aos protocolos da sua assembleia
        $adminAssembleia = Role::firstOrCreate(['name' => 'admin_assembleia']);
        $adminAssembleia->syncPermissions([
            'manage_own_assembleia',
            'view_assembleia_data',
            'view_own_data',
            'manage_membros',
            'view_membros',
            'create_membros',
            'edit_membros',
            'create_protocolos',
            'view_protocolos',
            'edit_protocolos',
            'view_protocolo_logs',
            // Pode criar todos os tipos de protocolo para sua assembleia
            'create_protocolo_maioridade',
            'create_protocolo_iniciacao',
            'create_protocolo_homenageados',
            'create_protocolo_coracao_cores',
            'create_protocolo_grande_cruz',
            'create_protocolo_afastamento',
            'create_protocolo_cargos_assembleia',
            'create_protocolo_cargos_conselho',
            'assign_cargos_assembleia',
            'assign_cargos_conselho',
            'view_cargos',
            'manage_cargos',
            'view_honrarias',
            'manage_calendario_assembleia',
        ]);

        // Role: presidente_honrarias (Presidente da Comissão de Honrarias)
        // Pode aprovar protocolos de honrarias
        $presidenteHonrarias = Role::firstOrCreate(['name' => 'presidente_honrarias']);
        $presidenteHonrarias->syncPermissions([
            'view_assembleia_data',
            'view_own_data',
            'view_protocolos',
            'view_protocolo_logs',
            'approve_protocolo_homenageados',
            'approve_protocolo_coracao_cores',
            'approve_protocolo_grande_cruz',
            'approve_honrarias',
            'view_honrarias',
            'view_all_tickets',
            'respond_tickets',
        ]);

        // Role: presidente_comissao (Presidente de Comissão)
        // Atendimento de tickets da sua comissão
        $presidenteComissao = Role::firstOrCreate(['name' => 'presidente_comissao']);
        $presidenteComissao->syncPermissions([
            'view_all_tickets',
            'respond_tickets',
            'view_own_data',
        ]);

        // Role: cargo_grande_assembleia (Grande Ilustre Preceptora, Grande Fé, etc)
        // Cargos honoríficos atribuídos a meninas ativas
        $cargoGrandeAssembleia = Role::firstOrCreate(['name' => 'cargo_grande_assembleia']);
        $cargoGrandeAssembleia->syncPermissions([
            'view_assembleia_data',
            'view_own_data',
        ]);

        // Role: menina_ativa (Meninas ativas da assembleia)
        // Podem ter cargos como Fé, Caridade, etc.
        $meninaAtiva = Role::firstOrCreate(['name' => 'menina_ativa']);
        $meninaAtiva->syncPermissions([
            'view_own_data',
        ]);

        // Role: menina_maioridade (Meninas que completaram 20 anos)
        // Acesso limitado ao histórico pessoal
        $meninaMaioridade = Role::firstOrCreate(['name' => 'menina_maioridade']);
        $meninaMaioridade->syncPermissions([
            'view_own_data',
        ]);

        // Role: membro (Membro comum)
        // Apenas visualização de informações da sua assembleia
        $membro = Role::firstOrCreate(['name' => 'membro']);
        $membro->syncPermissions([
            'view_assembleia_data',
            'view_own_data',
        ]);

        $this->command->info('Roles e Permissions criados com sucesso!');
        $this->command->info('Total de permissões: ' . Permission::count());
        $this->command->info('Total de roles: ' . Role::count());
    }
}
