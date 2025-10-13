# Design Arquitetural - Sistema IORG Paraná

## Visão Geral
Sistema completo de gestão para a Jurisdição do Paraná da IORG (Ordem Internacional do Arco-Íris para Meninas).

## Stack Tecnológica
- **Backend**: Laravel 12
- **Admin Panel**: Filament 3.x
- **Database**: MySQL 8.0+ / PostgreSQL 15+
- **PHP**: 8.2+
- **Auth**: Laravel Sanctum / Fortify
- **Permissions**: Spatie Laravel Permission
- **Cache**: Redis

---

## Arquitetura do Sistema

```
┌─────────────────────────────────────────────────────────────┐
│                    FRONTEND (Filament 3.x)                  │
├─────────────────────────────────────────────────────────────┤
│  Dashboard │ Resources │ Relations │ Widgets │ Actions      │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                      MIDDLEWARE LAYER                        │
├─────────────────────────────────────────────────────────────┤
│  Auth (Sanctum) │ Permissions (Spatie) │ Policies           │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                    APPLICATION LAYER                         │
├─────────────────────────────────────────────────────────────┤
│  Controllers │ Services │ Jobs │ Events │ Observers          │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                       DOMAIN LAYER                           │
├─────────────────────────────────────────────────────────────┤
│  Models │ Relationships │ Scopes │ Casts │ Traits           │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                      DATABASE LAYER                          │
├─────────────────────────────────────────────────────────────┤
│              MySQL/PostgreSQL + Redis Cache                  │
└─────────────────────────────────────────────────────────────┘
```

---

## Estrutura de Diretórios

```
app/
├── Filament/
│   ├── Resources/
│   │   ├── AssembleiaResource/
│   │   │   ├── Pages/
│   │   │   └── RelationManagers/
│   │   ├── MembroResource/
│   │   │   ├── Pages/
│   │   │   └── RelationManagers/
│   │   ├── ProtocoloResource/
│   │   │   ├── Pages/
│   │   │   └── RelationManagers/
│   │   ├── TicketResource/
│   │   │   ├── Pages/
│   │   │   └── RelationManagers/
│   │   ├── TipoCargoResource/
│   │   ├── CargoAssembleiaResource/
│   │   └── CargoGrandeAssembleiaResource/
│   ├── Widgets/
│   │   ├── DashboardJurisdicao/
│   │   │   ├── TotalAssembleiasWidget.php
│   │   │   ├── TotalMembrosWidget.php
│   │   │   ├── ProtocolosPendentesWidget.php
│   │   │   ├── TicketsAbertosWidget.php
│   │   │   ├── GraficoIniciacoesWidget.php
│   │   │   └── ProximosEventosWidget.php
│   │   ├── DashboardAssembleia/
│   │   │   ├── MembrosStatusWidget.php
│   │   │   ├── ProtocolosAssembleiaWidget.php
│   │   │   ├── ProximosEventosWidget.php
│   │   │   ├── AniversariantesWidget.php
│   │   │   └── CargosVagosWidget.php
│   │   └── DashboardMembro/
│   │       ├── ProximosEventosWidget.php
│   │       ├── AniversariantesWidget.php
│   │       └── MeusCargosWidget.php
│   └── Pages/
│       ├── Dashboard.php
│       └── Relatorios/
│           ├── RelatorioJurisdicao.php
│           ├── RelatorioAssembleia.php
│           ├── RelatorioCargos.php
│           └── RelatorioGrandeAssembleia.php
├── Models/
│   ├── User.php
│   ├── Jurisdicao.php
│   ├── Assembleia.php
│   ├── Membro.php
│   ├── TipoCargo.php
│   ├── CargoAssembleia.php
│   ├── CargoGrandeAssembleia.php
│   ├── HistoricoCargo.php
│   ├── Protocolo.php
│   ├── ProtocoloHistorico.php
│   ├── ProtocoloAnexo.php
│   ├── ProtocoloTaxa.php
│   ├── Comissao.php
│   ├── ComissaoMembro.php
│   ├── Ticket.php
│   ├── TicketResposta.php
│   ├── TicketAnexo.php
│   ├── EventoCalendario.php
│   └── AniversarioCache.php
├── Policies/
│   ├── AssembleiaPolicy.php
│   ├── MembroPolicy.php
│   ├── TipoCargoPolicy.php
│   ├── CargoAssembleiaPolicy.php
│   ├── CargoGrandeAssembleiaPolicy.php
│   ├── ProtocoloPolicy.php
│   ├── TicketPolicy.php
│   └── EventoCalendarioPolicy.php
├── Observers/
│   ├── MembroObserver.php
│   ├── CargoAssembleiaObserver.php
│   ├── CargoGrandeAssembleiaObserver.php
│   ├── ProtocoloObserver.php
│   └── TicketObserver.php
├── Services/
│   ├── ProtocoloService.php
│   ├── CargoService.php
│   ├── TicketService.php
│   ├── RelatorioService.php
│   └── NotificacaoService.php
├── Jobs/
│   ├── VerificarMaioridadeJob.php
│   ├── LembrarAniversariosJob.php
│   ├── VerificarPrazosProtocolosJob.php
│   ├── VerificarSLATicketsJob.php
│   ├── AtualizarAniversariosCalendarioJob.php
│   ├── AutoFecharTicketsResolvidosJob.php
│   ├── LimparArquivosTemporariosJob.php
│   ├── BackupDatabaseJob.php
│   ├── VerificarVencimentoCargosGrandeAssembleiaJob.php
│   ├── SincronizarPermissoesUsuariosJob.php
│   └── RegistrarHistoricoCargosCerradosJob.php
├── Console/
│   └── Commands/
│       ├── InstallCommand.php
│       ├── CreateAdminCommand.php
│       ├── SyncAniversariosCommand.php
│       ├── GerarRelatorioMensalCommand.php
│       ├── VerificarIntegridadeCommand.php
│       ├── LimparCacheCompletoCommand.php
│       ├── SincronizarCargosCommand.php
│       ├── EncerrarCargosVencidosCommand.php
│       └── RelatorioCargosVagosCommand.php
└── Traits/
    ├── HasCargos.php
    ├── HasProtocolos.php
    └── HasAssembleia.php
```

---

## Módulos Principais

### 1. Módulo de Autenticação e Permissões
**Roles:**
- `membro_jurisdicao` - Acesso total ao sistema
- `presidente_comissao` - Acesso a tickets da comissão
- `admin_assembleia` - Gestão completa da assembleia
- `cargo_grande_assembleia` - Cargos honoríficos
- `menina_ativa` - Visualização limitada
- `menina_maioridade` - Acesso histórico

**Matriz de Permissões:**
```
- view_all_assembleias (jurisdicao)
- manage_all_assembleias (jurisdicao)
- manage_own_assembleia (admin_assembleia)
- create_tipos_cargos (jurisdicao)
- assign_cargos_assembleia (jurisdicao, admin_assembleia)
- assign_cargos_grande_assembleia (jurisdicao)
- create_protocolos (jurisdicao, admin_assembleia)
- approve_protocolos (jurisdicao)
- view_all_tickets (jurisdicao, presidente_comissao)
- respond_tickets (jurisdicao, presidente_comissao)
- manage_calendario_assembleia (jurisdicao, admin_assembleia)
```

### 2. Módulo de Gestão de Assembleias
**Features:**
- CRUD completo de assembleias
- Gestão de membros por assembleia
- Visualização de estatísticas
- Exportação de relatórios PDF
- Envio de emails em massa

### 3. Módulo de Gestão de Membros
**Features:**
- Cadastro completo com validações
- Upload de foto
- Gestão de responsáveis
- Controle de status (candidata, ativa, afastada, maioridade, desligada)
- Cálculo automático de maioridade (20 anos)
- Histórico completo de cargos
- Geração de carteirinha digital

### 4. Módulo de Sistema de Cargos
**Três tipos de cargos:**

#### 4.1 Tipos de Cargos (TipoCargo)
- Criação e gestão pela Jurisdição
- Categorias: administrativo, menina, grande_assembleia
- Controle de acesso administrativo
- Ordenação customizável

#### 4.2 Cargos de Assembleia (CargoAssembleia)
- Atribuição por Jurisdição ou Admin Assembleia
- Controle de período (início/fim)
- Validação de unicidade de cargos ativos
- Registro automático no histórico

#### 4.3 Cargos da Grande Assembleia (CargoGrandeAssembleia)
- Atribuição exclusiva pela Jurisdição
- Cargos honoríficos anuais
- Sistema de renovação
- Notificações de vencimento

### 5. Módulo de Protocolos
**Workflow:**
```
Rascunho → Pendente → Em Análise → Aprovado/Rejeitado → Concluído
```

**Tipos de Protocolos:**
- Iniciação
- Transferência
- Afastamento
- Retorno
- Maioridade
- Desligamento
- Prêmios/Honrarias

**Features:**
- Numeração automática (PR-2025-001)
- Campos dinâmicos por tipo
- Upload de anexos
- Gestão de taxas e pagamentos
- Histórico de mudanças (logs visíveis)
- Notificações automáticas

### 6. Módulo de Tickets
**Workflow:**
```
Aberto → Atribuído → Em Atendimento → Resolvido → Fechado
```

**Sistema de SLA:**
- Urgente: 4h / 24h
- Alta: 8h / 3 dias
- Normal: 24h / 7 dias
- Baixa: 48h / 15 dias

**Features:**
- Atribuição a comissões
- Sistema de respostas em thread
- Notas internas
- Avaliação de atendimento
- Alertas de SLA

### 7. Módulo de Calendário
**Features:**
- Integração com FullCalendar
- Eventos por assembleia/jurisdição
- Tipos: reunião, iniciação, instalação, cerimônia pública, filantropia
- Eventos recorrentes
- Sincronização de aniversários
- Exportação iCal/Google Calendar

### 8. Módulo de Dashboards
**3 Dashboards personalizados:**
- Dashboard Jurisdição (visão geral completa)
- Dashboard Assembleia (gestão local)
- Dashboard Membro (visualização pessoal)

### 9. Módulo de Relatórios
**Relatórios disponíveis:**
- Relatório Geral da Jurisdição
- Relatório por Assembleia
- Relatório de Protocolos
- Relatório de Tickets
- Relatório Financeiro
- Relatório de Cargos
- Relatório da Grande Assembleia
- Relatório de Eventos

---

## Fluxos de Dados Principais

### Fluxo de Atribuição de Cargo
```
1. Admin seleciona membro
2. Escolhe tipo de cargo
3. Define data de início
4. Sistema valida unicidade
5. Cria registro em cargos_assembleia
6. Observer registra em historico_cargos
7. Atualiza permissões do usuário (se cargo admin)
8. Envia notificação
```

### Fluxo de Protocolo
```
1. Admin cria protocolo (rascunho)
2. Preenche dados específicos do tipo
3. Anexa documentos
4. Submete para análise (pendente)
5. Jurisdição recebe notificação
6. Jurisdição analisa (em_analise)
7. Jurisdição aprova/rejeita
8. Sistema executa ações automáticas
9. Registra histórico completo
10. Notifica solicitante
```

### Fluxo de Ticket
```
1. Usuário cria ticket
2. Sistema atribui a comissão baseado na categoria
3. Presidente da comissão recebe
4. Atribui a membro responsável
5. Membro responde
6. Sistema monitora SLA
7. Ticket resolvido
8. Solicitante avalia
9. Ticket fechado automaticamente após 7 dias
```

---

## Integrações

### Sistema de Notificações
- Email via SMTP
- Notificações in-app (Filament)
- Alertas de SLA
- Lembretes de vencimento

### Sistema de Arquivos
- Upload de documentos (protocolos, tickets)
- Armazenamento em storage/app
- Validação de tipos e tamanhos
- Limpeza automática de temporários

### Sistema de Cache
- Redis para cache de queries
- Cache de aniversários
- Cache de estatísticas do dashboard

---

## Segurança

### Autenticação
- Laravel Sanctum para API tokens
- Laravel Fortify para web auth
- 2FA (opcional)

### Autorização
- Spatie Permission para roles/permissions
- Policies para cada resource
- Middleware de verificação
- Filament authorization hooks

### Validações
- Validação de CPF único
- Validação de idade (11-20 anos)
- Validação de email único
- Validação de unicidade de cargos ativos
- Sanitização de inputs

---

## Performance

### Otimizações
- Eager loading de relacionamentos
- Índices de banco de dados otimizados
- Cache de queries pesadas
- Jobs em fila para operações pesadas
- Paginação em todas as listagens

### Monitoramento
- Logs estruturados
- Backup automático diário
- Verificação de integridade
- Métricas de performance

---

## Escalabilidade

### Preparação para Crescimento
- Arquitetura modular
- Separação de concerns
- Services para lógica de negócio
- Jobs assíncronos
- Cache estratégico
- Database indexes otimizados

---

**Versão:** 1.0
**Data:** Outubro 2025
**Status:** Design Aprovado