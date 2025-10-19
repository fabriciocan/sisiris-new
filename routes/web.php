<?php

use App\Http\Controllers\CalendarioExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Rotas de exportação do calendário (protegidas por auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/calendario/export/ical', [CalendarioExportController::class, 'exportIcal'])->name('calendario.export.ical');
    Route::get('/calendario/google/{evento}', [CalendarioExportController::class, 'googleCalendarLink'])->name('calendario.google');
    
    // Rota de teste para o calendário
    Route::get('/calendario-teste', function () {
        return view('livewire.calendario-eventos', [
            'assembleias' => \App\Models\Assembleia::all(),
            'calendarDays' => collect()
        ]);
    })->name('calendario.teste');
});
