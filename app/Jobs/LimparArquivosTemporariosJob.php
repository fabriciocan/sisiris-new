<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LimparArquivosTemporariosJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $diasParaLimpar = 7; // Arquivos mais antigos que 7 dias
        $agora = Carbon::now();
        $dataLimite = $agora->subDays($diasParaLimpar);
        
        $arquivosRemovidos = 0;
        
        // Limpar diretório tmp
        $tmpPath = storage_path('app/tmp');
        if (File::exists($tmpPath)) {
            $arquivos = File::allFiles($tmpPath);
            
            foreach ($arquivos as $arquivo) {
                if (Carbon::createFromTimestamp(File::lastModified($arquivo)) < $dataLimite) {
                    try {
                        File::delete($arquivo);
                        $arquivosRemovidos++;
                    } catch (\Exception $e) {
                        Log::error("Erro ao remover arquivo {$arquivo}: " . $e->getMessage());
                    }
                }
            }
        }
        
        // Limpar uploads temporários
        $uploadsTemp = storage_path('app/uploads/temp');
        if (File::exists($uploadsTemp)) {
            $arquivos = File::allFiles($uploadsTemp);
            
            foreach ($arquivos as $arquivo) {
                if (Carbon::createFromTimestamp(File::lastModified($arquivo)) < $dataLimite) {
                    try {
                        File::delete($arquivo);
                        $arquivosRemovidos++;
                    } catch (\Exception $e) {
                        Log::error("Erro ao remover arquivo temporário {$arquivo}: " . $e->getMessage());
                    }
                }
            }
        }
        
        // Limpar logs antigos (mais de 30 dias)
        $logsPath = storage_path('logs');
        if (File::exists($logsPath)) {
            $logs = File::files($logsPath);
            $dataLimiteLogs = $agora->subDays(30);
            
            foreach ($logs as $log) {
                if (Carbon::createFromTimestamp(File::lastModified($log)) < $dataLimiteLogs) {
                    try {
                        File::delete($log);
                        $arquivosRemovidos++;
                    } catch (\Exception $e) {
                        Log::error("Erro ao remover log {$log}: " . $e->getMessage());
                    }
                }
            }
        }
        
        // Limpar cache do Laravel
        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
        } catch (\Exception $e) {
            Log::error("Erro ao limpar cache: " . $e->getMessage());
        }
        
        Log::info("Job LimparArquivosTemporariosJob executado. {$arquivosRemovidos} arquivos removidos.");
    }
}
