<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CorrigirNiveisAcessoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:corrigir-niveis-acesso';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige os níveis de acesso dos usuários baseado em suas roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando correção dos níveis de acesso...');
        
        $usuarios = User::all();
        $corrigidos = 0;
        
        foreach ($usuarios as $user) {
            $nivelAcessoAtual = $user->nivel_acesso;
            $novoNivelAcesso = null;
            
            // Determinar o nível de acesso correto baseado nas roles
            if ($user->hasRole('membro_jurisdicao') || $user->hasRole('vice_gra_digna') || $user->hasRole('gra_digna')) {
                $novoNivelAcesso = 'membro_jurisdicao';
            } elseif ($user->hasRole('admin_assembleia') || $user->hasRole('digna_matrona') || $user->hasRole('vice_digna_matrona')) {
                $novoNivelAcesso = 'admin_assembleia';
            } else {
                $novoNivelAcesso = 'membro';
            }
            
            // Atualizar se necessário
            if ($nivelAcessoAtual !== $novoNivelAcesso) {
                $user->update(['nivel_acesso' => $novoNivelAcesso]);
                $this->line("✓ {$user->name} ({$user->email}): {$nivelAcessoAtual} → {$novoNivelAcesso}");
                $corrigidos++;
            }
        }
        
        if ($corrigidos > 0) {
            $this->info("\n✓ {$corrigidos} usuário(s) corrigido(s) com sucesso!");
        } else {
            $this->info("\n✓ Todos os usuários já estão com os níveis de acesso corretos!");
        }
        
        // Mostrar estatísticas
        $this->newLine();
        $this->info('Estatísticas de níveis de acesso:');
        $this->table(
            ['Nível de Acesso', 'Quantidade'],
            [
                ['Membro Jurisdição', User::where('nivel_acesso', 'membro_jurisdicao')->count()],
                ['Admin Assembleia', User::where('nivel_acesso', 'admin_assembleia')->count()],
                ['Membro', User::where('nivel_acesso', 'membro')->count()],
            ]
        );
        
        return 0;
    }
}
