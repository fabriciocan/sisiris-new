<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\VerificarSLATickets;
use App\Jobs\NotificarEventosProximos;
use App\Jobs\VerificarMaioridadeJob;
use App\Jobs\LembrarAniversariosJob;
use App\Jobs\VerificarPrazosProtocolosJob;
use App\Jobs\AtualizarAniversariosCalendarioJob;
use App\Jobs\AutoFecharTicketsResolvidosJob;
use App\Jobs\LimparArquivosTemporariosJob;
use App\Jobs\BackupDatabaseJob;
use App\Jobs\VerificarVencimentoCargosGrandeAssembleiaJob;
use App\Jobs\SincronizarPermissoesUsuariosJob;
use App\Jobs\RegistrarHistoricoCargosCerradosJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ===========================================
// JOBS AGENDADOS - SISTEMA SISIRIS
// ===========================================

// Jobs a cada 30 minutos
Schedule::job(new VerificarSLATickets)->everyThirtyMinutes();
Schedule::job(new NotificarEventosProximos)->everyThirtyMinutes();

// Jobs por hora
Schedule::job(new VerificarPrazosProtocolosJob)->hourly();
Schedule::job(new SincronizarPermissoesUsuariosJob)->hourly();

// Jobs diários
Schedule::job(new VerificarMaioridadeJob)->daily();
Schedule::job(new LembrarAniversariosJob)->dailyAt('08:00');
Schedule::job(new VerificarVencimentoCargosGrandeAssembleiaJob)->dailyAt('08:00');
Schedule::job(new AutoFecharTicketsResolvidosJob)->dailyAt('02:00');
Schedule::job(new BackupDatabaseJob)->dailyAt('04:00');
Schedule::job(new RegistrarHistoricoCargosCerradosJob)->dailyAt('01:00');

// Jobs semanais
Schedule::job(new LimparArquivosTemporariosJob)->weekly();

// Jobs mensais
Schedule::job(new AtualizarAniversariosCalendarioJob)->monthly();
