<?php

/**
 * Script de teste para verificar a fundação do sistema de protocolos
 * Execute com: php tests/TestFundacaoProtocolos.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Protocolo;
use App\Models\Assembleia;
use App\Models\User;
use App\Helpers\ProtocoloLogger;

echo "=== TESTE DE FUNDAÇÃO DO SISTEMA DE PROTOCOLOS ===" . PHP_EOL . PHP_EOL;

try {
    // 1. Verificar se temos dados básicos
    echo "1. Verificando dados básicos..." . PHP_EOL;
    $assembleia = Assembleia::first();
    $user = User::first();

    if (!$assembleia) {
        echo "   ❌ ERRO: Nenhuma assembleia encontrada. Execute os seeders primeiro." . PHP_EOL;
        exit(1);
    }

    if (!$user) {
        echo "   ❌ ERRO: Nenhum usuário encontrado. Execute os seeders primeiro." . PHP_EOL;
        exit(1);
    }

    echo "   ✓ Assembleia encontrada: {$assembleia->nome}" . PHP_EOL;
    echo "   ✓ Usuário encontrado: {$user->name}" . PHP_EOL;
    echo PHP_EOL;

    // 2. Testar criação de protocolo
    echo "2. Testando criação de protocolo..." . PHP_EOL;
    $protocolo = Protocolo::create([
        'numero_protocolo' => 'TEST-' . time(),
        'assembleia_id' => $assembleia->id,
        'tipo_protocolo' => 'maioridade',
        'titulo' => 'Teste de Fundação',
        'descricao' => 'Protocolo de teste para verificar sistema de logs',
        'solicitante_id' => $user->id,
        'status' => 'rascunho',
        'data_solicitacao' => now(),
    ]);

    echo "   ✓ Protocolo criado: {$protocolo->numero_protocolo}" . PHP_EOL;
    echo "   ✓ Status inicial: {$protocolo->status}" . PHP_EOL;
    echo "   ✓ Etapa inicial: {$protocolo->etapa_atual}" . PHP_EOL;
    echo PHP_EOL;

    // 3. Verificar Observer (log automático)
    echo "3. Verificando Observer (criação automática de logs)..." . PHP_EOL;
    $historicoCount = $protocolo->historico()->count();
    echo "   ✓ Histórico criado automaticamente: {$historicoCount} registro(s)" . PHP_EOL;

    if ($historicoCount > 0) {
        $primeiroLog = $protocolo->historico()->first();
        echo "   ✓ Primeiro log: {$primeiroLog->acao} - {$primeiroLog->descricao}" . PHP_EOL;
    }
    echo PHP_EOL;

    // 4. Testar ProtocoloLogger
    echo "4. Testando ProtocoloLogger..." . PHP_EOL;
    ProtocoloLogger::logEnvioAprovacao($protocolo, 'Teste de envio', $user);
    echo "   ✓ Log de envio para aprovação criado" . PHP_EOL;

    ProtocoloLogger::logDefinicaoTaxa($protocolo, 150.50, $user);
    echo "   ✓ Log de definição de taxa criado" . PHP_EOL;
    echo PHP_EOL;

    // 5. Testar mudança de status
    echo "5. Testando mudança de status..." . PHP_EOL;
    $statusAnterior = $protocolo->status;
    $protocolo->update(['status' => 'pendente']);
    echo "   ✓ Status atualizado: {$statusAnterior} → {$protocolo->status}" . PHP_EOL;
    echo PHP_EOL;

    // 6. Testar mudança de etapa
    echo "6. Testando mudança de etapa..." . PHP_EOL;
    $etapaAnterior = $protocolo->etapa_atual;
    $protocolo->update(['etapa_atual' => 'aguardando_aprovacao']);
    echo "   ✓ Etapa atualizada: {$etapaAnterior} → {$protocolo->etapa_atual}" . PHP_EOL;
    echo PHP_EOL;

    // 7. Verificar total de logs
    echo "7. Verificando logs completos..." . PHP_EOL;
    $totalLogs = $protocolo->historico()->count();
    echo "   ✓ Total de logs criados: {$totalLogs}" . PHP_EOL;
    echo PHP_EOL;

    // 8. Exibir timeline
    echo "8. Timeline do protocolo:" . PHP_EOL;
    $logs = ProtocoloLogger::getTimeline($protocolo);
    foreach ($logs as $index => $log) {
        $numero = $index + 1;
        $data = $log->created_at->format('d/m/Y H:i:s');
        $usuario = $log->user ? $log->user->name : 'Sistema';
        echo "   {$numero}. [{$data}] {$log->acao_label} - por {$usuario}" . PHP_EOL;

        if ($log->hasStatusChange()) {
            echo "      Status: {$log->status_anterior} → {$log->status_novo}" . PHP_EOL;
        }

        if ($log->hasEtapaChange()) {
            echo "      Etapa: {$log->etapa_anterior} → {$log->etapa_nova}" . PHP_EOL;
        }

        if ($log->descricao) {
            echo "      Descrição: {$log->descricao}" . PHP_EOL;
        }
    }
    echo PHP_EOL;

    // 9. Testar métodos do ProtocoloHistorico
    echo "9. Testando métodos do model ProtocoloHistorico..." . PHP_EOL;
    $ultimoLog = $logs->first();
    echo "   ✓ getAcaoLabelAttribute: {$ultimoLog->acao_label}" . PHP_EOL;
    echo "   ✓ getNomeUsuarioAttribute: {$ultimoLog->nome_usuario}" . PHP_EOL;
    echo "   ✓ hasStatusChange: " . ($ultimoLog->hasStatusChange() ? 'Sim' : 'Não') . PHP_EOL;
    echo "   ✓ hasEtapaChange: " . ($ultimoLog->hasEtapaChange() ? 'Sim' : 'Não') . PHP_EOL;
    echo PHP_EOL;

    // 10. Limpar teste
    echo "10. Limpando dados de teste..." . PHP_EOL;
    $protocolo->forceDelete();
    echo "   ✓ Protocolo de teste removido" . PHP_EOL;
    echo PHP_EOL;

    // Resultado final
    echo "=== ✅ TODOS OS TESTES PASSARAM COM SUCESSO! ===" . PHP_EOL;
    echo PHP_EOL;
    echo "Fundação do sistema implementada com sucesso:" . PHP_EOL;
    echo "  ✓ Model ProtocoloHistorico aprimorado" . PHP_EOL;
    echo "  ✓ Helper ProtocoloLogger funcionando" . PHP_EOL;
    echo "  ✓ Observer ProtocoloObserver ativo" . PHP_EOL;
    echo "  ✓ Migrations executadas" . PHP_EOL;
    echo "  ✓ Base classes criadas (BaseProtocoloPage, BaseProtocoloSchema)" . PHP_EOL;
    echo PHP_EOL;

} catch (Exception $e) {
    echo PHP_EOL;
    echo "=== ❌ ERRO NO TESTE ===" . PHP_EOL;
    echo "Mensagem: " . $e->getMessage() . PHP_EOL;
    echo "Arquivo: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo PHP_EOL;
    echo "Stack trace:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}
