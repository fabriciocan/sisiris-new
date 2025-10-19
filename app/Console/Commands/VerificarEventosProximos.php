<?php

namespace App\Console\Commands;

use App\Jobs\NotificarEventosProximos;
use Illuminate\Console\Command;

class VerificarEventosProximos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eventos:verificar-proximos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica eventos próximos e envia notificações';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando eventos próximos...');
        
        NotificarEventosProximos::dispatch();
        
        $this->info('Job de notificação agendado com sucesso!');
        
        return Command::SUCCESS;
    }
}
