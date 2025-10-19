# Plano de Desenvolvimento - Sistema IORG Paraná

## Resumo Executivo

Desenvolvimento completo em 16 fases do Sistema de Gestão IORG Paraná utilizando Laravel 12 + Filament 3.x.

**Estimativa total:** 8-12 semanas
**Complexidade:** Alta
**Prioridade:** Seguir ordem sequencial das fases

---

## Fase 1: Setup Inicial (2-3 dias)

### 1.1 Instalação do Laravel 12

```bash
composer create-project laravel/laravel sisiris "12.*"
cd sisiris
```

### 1.2 Configuração do Ambiente

-   [ ] Configurar `.env` com dados do banco
-   [ ] Configurar cache (Redis)
-   [ ] Configurar queue (Redis/Database)
-   [ ] Configurar filesystem
-   [ ] Configurar mail (SMTP)

### 1.3 Instalação de Dependências

```bash
# Filament 3.x
composer require filament/filament:"^3.0"

# Spatie Permission
composer require spatie/laravel-permission

# Laravel Sanctum
composer require laravel/sanctum

# Outras dependências úteis
composer require barryvdh/laravel-dompdf
composer require maatwebsite/excel
```

### 1.4 Inicialização do Filament

```bash
php artisan filament:install --panels=admin
php artisan filament:user
```

### 1.5 Configuração Git

```bash
git init
git add .
git commit -m "Initial commit: Laravel 12 + Filament 3.x setup"
```

---

## Fase 2: Migrations (3-4 dias)

### 2.1 Migrations Base

Criar migrations na ordem correta:

```bash
# Ordem de criação
php artisan make:migration create_jurisdicoes_table
php artisan make:migration create_assembleias_table
php artisan make:migration create_membros_table
php artisan make:migration create_tipos_cargos_assembleia_table
php artisan make:migration create_cargos_assembleia_table
php artisan make:migration create_cargos_grande_assembleia_table
php artisan make:migration create_historico_cargos_table
php artisan make:migration create_comissoes_table
php artisan make:migration create_comissao_membros_table
php artisan make:migration create_protocolos_table
php artisan make:migration create_protocolo_historico_table
php artisan make:migration create_protocolo_anexos_table
php artisan make:migration create_protocolo_taxas_table
php artisan make:migration create_tickets_table
php artisan make:migration create_ticket_respostas_table
php artisan make:migration create_ticket_anexos_table
php artisan make:migration create_eventos_calendario_table
php artisan make:migration create_aniversarios_cache_table
php artisan make:migration add_fields_to_users_table
```

### 2.2 Checklist de Implementação

-   [ ] Implementar todas as migrations conforme documentação
-   [ ] Adicionar índices adequados
-   [ ] Configurar foreign keys com onDelete
-   [ ] Adicionar soft deletes onde necessário
-   [ ] Testar migrations (up/down)
-   [ ] Executar: `php artisan migrate`

---

## Fase 3: Filament & Auth (2 dias)

### 3.1 Configuração do Filament

-   [ ] Personalizar cores e branding
-   [ ] Configurar timezone (America/Sao_Paulo)
-   [ ] Configurar locale (pt_BR)
-   [ ] Configurar middleware customizado

### 3.2 Instalação Spatie Permission

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### 3.3 Configuração de Autenticação

-   [ ] Configurar Filament auth guard
-   [ ] Customizar login page
-   [ ] Configurar password reset
-   [ ] Adicionar campos extras ao User (telefone, data_nascimento, cpf)

---

## Fase 4: Permissões (2-3 dias)

### 4.1 Criar Roles

```php
Role::create(['name' => 'membro_jurisdicao']);
Role::create(['name' => 'presidente_comissao']);
Role::create(['name' => 'admin_assembleia']);
Role::create(['name' => 'cargo_grande_assembleia']);
Role::create(['name' => 'menina_ativa']);
Role::create(['name' => 'menina_maioridade']);
```

### 4.2 Criar Permissions

-   [ ] view_all_assembleias
-   [ ] manage_all_assembleias
-   [ ] manage_own_assembleia
-   [ ] create_tipos_cargos
-   [ ] assign_cargos_assembleia
-   [ ] assign_cargos_grande_assembleia
-   [ ] create_protocolos
-   [ ] approve_protocolos
-   [ ] view_all_tickets
-   [ ] respond_tickets
-   [ ] manage_calendario_assembleia
-   [ ] view_relatorios
-   [ ] manage_membros

### 4.3 Criar Policies

```bash
php artisan make:policy AssembleiaPolicy --model=Assembleia
php artisan make:policy MembroPolicy --model=Membro
php artisan make:policy TipoCargoPolicy --model=TipoCargo
php artisan make:policy CargoAssembleiaPolicy --model=CargoAssembleia
php artisan make:policy CargoGrandeAssembleiaPolicy --model=CargoGrandeAssembleia
php artisan make:policy ProtocoloPolicy --model=Protocolo
php artisan make:policy TicketPolicy --model=Ticket
php artisan make:policy EventoCalendarioPolicy --model=EventoCalendario
```

### 4.4 Implementar Matriz de Permissões

-   [ ] Implementar cada Policy conforme documentação
-   [ ] Testar permissões para cada role
-   [ ] Registrar policies no AuthServiceProvider

---

## Fase 5: Models e Relationships (3-4 dias)

### 5.1 Criar Models

```bash
php artisan make:model Jurisdicao
php artisan make:model Assembleia
php artisan make:model Membro
php artisan make:model TipoCargo
php artisan make:model CargoAssembleia
php artisan make:model CargoGrandeAssembleia
php artisan make:model HistoricoCargo
php artisan make:model Comissao
php artisan make:model ComissaoMembro
php artisan make:model Protocolo
php artisan make:model ProtocoloHistorico
php artisan make:model ProtocoloAnexo
php artisan make:model ProtocoloTaxa
php artisan make:model Ticket
php artisan make:model TicketResposta
php artisan make:model TicketAnexo
php artisan make:model EventoCalendario
php artisan make:model AniversarioCache
```

### 5.2 Implementar Relationships

-   [ ] User: hasOne(Membro), hasMany(Protocolos), belongsToMany(Comissoes)
-   [ ] Jurisdicao: hasMany(Assembleias), hasMany(Comissoes)
-   [ ] Assembleia: belongsTo(Jurisdicao), hasMany(Membros), hasMany(Protocolos)
-   [ ] Membro: belongsTo(User), belongsTo(Assembleia), hasMany(Cargos)
-   [ ] TipoCargo: hasMany(CargosAssembleia), hasMany(CargosGrandeAssembleia)
-   [ ] CargoAssembleia: belongsTo(Assembleia), belongsTo(Membro), belongsTo(TipoCargo)
-   [ ] CargoGrandeAssembleia: belongsTo(Membro), belongsTo(TipoCargo)
-   [ ] Protocolo: belongsTo(Assembleia), belongsTo(Membro), hasMany(Historico)
-   [ ] Ticket: belongsTo(Assembleia), belongsTo(Comissao), hasMany(Respostas)

### 5.3 Criar Observers

```bash
php artisan make:observer MembroObserver --model=Membro
php artisan make:observer CargoAssembleiaObserver --model=CargoAssembleia
php artisan make:observer CargoGrandeAssembleiaObserver --model=CargoGrandeAssembleia
php artisan make:observer ProtocoloObserver --model=Protocolo
php artisan make:observer TicketObserver --model=Ticket
```

### 5.4 Criar Traits

-   [ ] HasCargos trait
-   [ ] HasProtocolos trait
-   [ ] HasAssembleia trait

### 5.5 Implementar Casts e Scopes

-   [ ] Adicionar casts adequados (date, boolean, json)
-   [ ] Criar scopes úteis: scopeAtivas, scopeMaioridade, scopePendentes

---

## Fase 6: Seeders (2 dias)

### 6.1 Criar Seeders

```bash
php artisan make:seeder RoleSeeder
php artisan make:seeder TipoCargoSeeder
php artisan make:seeder ComissaoSeeder
php artisan make:seeder JurisdicaoSeeder
php artisan make:seeder DemoDataSeeder
```

### 6.2 Implementar TipoCargoSeeder

-   [ ] 6 Cargos Administrativos
-   [ ] 29 Cargos das Meninas
-   [ ] 8 Cargos da Grande Assembleia
        Total: 43 tipos de cargos predefinidos

### 6.3 Implementar RoleSeeder

-   [ ] Criar 6 roles
-   [ ] Atribuir permissions a cada role

### 6.4 Implementar ComissaoSeeder

-   [ ] Comissão de Ritualística
-   [ ] Comissão de Legislação
-   [ ] Comissão de Tradução
-   [ ] Comissão de Comunicação

### 6.5 Implementar JurisdicaoSeeder

-   [ ] Criar Jurisdição do Paraná (IORG-PR)

### 6.6 Executar Seeders

```bash
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=TipoCargoSeeder
php artisan db:seed --class=ComissaoSeeder
php artisan db:seed --class=JurisdicaoSeeder
```

---

## Fase 7: Resources - Jurisdição e Assembleias (3-4 dias)

### 7.1 Criar JurisdicaoResource

```bash
php artisan make:filament-resource Jurisdicao --view
```

-   [ ] Formulário readonly com informações da jurisdição
-   [ ] Estatísticas gerais

### 7.2 Criar AssembleiaResource

```bash
php artisan make:filament-resource Assembleia --generate
```

#### Implementações:

-   [ ] Formulário com seções:
    -   Informações Básicas
    -   Contato
    -   Localização
-   [ ] Tabela com colunas: Número, Nome, Cidade, Membros Ativas, Status
-   [ ] Filtros: Cidade, Status, Data de Fundação
-   [ ] Busca: Número, Nome, Cidade
-   [ ] Tabs no detalhe:
    -   Visão Geral
    -   Membros (RelationManager)
    -   Cargos (RelationManager)
    -   Protocolos (RelationManager)
    -   Calendário (RelationManager)
    -   Estatísticas (custom page)
-   [ ] Actions:
    -   Exportar Relatório PDF
    -   Enviar Email para Membros
    -   Ativar/Desativar

---

## Fase 8: Resources - Membros (4-5 dias)

### 8.1 Criar MembroResource

```bash
php artisan make:filament-resource Membro --generate
```

#### Implementações:

-   [ ] Formulário com 5 seções:
    -   Dados Pessoais (foto, nome, data_nascimento, cpf, email, telefone)
    -   Endereço (CEP com busca automática)
    -   Responsáveis (mãe, pai, responsável legal)
    -   Informações IORG (assembleia, data_iniciacao, status)
    -   Cargos Atuais (visualização + botão adicionar)
-   [ ] Validações:
    -   Idade entre 11-20 anos
    -   CPF único
    -   Email único
-   [ ] Tabela com colunas: Foto, Nome, Assembleia, Idade, Status, Cargo Principal
-   [ ] Filtros: Assembleia, Status, Idade, Mês Aniversário, Tem Cargo
-   [ ] Actions:
    -   Criar Conta de Usuário
    -   Gerar Carteirinha Digital
    -   Enviar Email de Boas-Vindas
    -   Registrar Afastamento/Retorno
    -   Transferir para Outra Assembleia
    -   Adicionar Cargo (modal)
    -   Ver Histórico de Cargos

### 8.2 Implementar Upload de Foto

-   [ ] Configurar disk de storage
-   [ ] Validação de tipo (jpg, png)
-   [ ] Resize automático

### 8.3 Implementar Busca de CEP

-   [ ] Integração com API ViaCEP
-   [ ] Auto-preenchimento de endereço

---

## Fase 9: Resources - Sistema de Cargos (5-6 dias)

### 9.1 Criar TipoCargoResource

```bash
php artisan make:filament-resource TipoCargo --generate
```

#### Implementações:

-   [ ] Formulário:
    -   Nome do Cargo
    -   Categoria (select: administrativo, assembleia, grande_assembleia)
    -   Possui Acesso Administrativo (toggle)
    -   Ordem de Exibição
    -   Status (Ativo/Inativo)
    -   Descrição
-   [ ] Tabela com filtros por Categoria e Status
-   [ ] Badges coloridos
-   [ ] Reordenação drag & drop
-   [ ] Proteção de cargos do sistema (não permitir delete)
-   [ ] Validação: não desativar se houver membros com cargo ativo

### 9.2 Criar CargoAssembleiaResource

```bash
php artisan make:filament-resource CargoAssembleia --generate
```

#### Implementações:

-   [ ] Formulário:
    -   Assembleia (readonly se admin_assembleia)
    -   Tipo de Cargo (select filtrado)
    -   Membro (select apenas ativas)
    -   Data Início/Fim
    -   Status
    -   Observações
-   [ ] Validações:
    -   Não permitir dois cargos iguais ativos na mesma assembleia
    -   Data fim > data início
-   [ ] Actions:
    -   Encerrar Cargo (modal com data_fim)
    -   Ver Histórico do Membro
    -   Criar Conta de Usuário (se cargo admin)
-   [ ] Observer: registrar automaticamente no histórico

### 9.3 Criar CargoGrandeAssembleiaResource

```bash
php artisan make:filament-resource CargoGrandeAssembleia --generate
```

#### Implementações:

-   [ ] Formulário:
    -   Tipo de Cargo (filtrado: grande_assembleia)
    -   Membro (busca todas meninas ativas)
    -   Data Início/Fim
    -   Observações
-   [ ] Tabela: Cargo, Membro, Assembleia, Data Início, Data Fim, Status
-   [ ] Filtros: Tipo de Cargo, Assembleia, Status, Ano
-   [ ] Validações:
    -   Não permitir duplicados ativos
    -   Apenas meninas ativas
-   [ ] Actions:
    -   Ver Histórico
-   [ ] Notificações:
    -   Ao atribuir
    -   1 mês antes do fim
    -   No vencimento

### 9.4 Implementar Visualização no Perfil do Membro

-   [ ] Seção Cargos com tabs:
    -   Cargos Atuais
    -   Histórico de Cargos (timeline)
-   [ ] Widget de cargo principal
-   [ ] Exportar histórico em PDF

---

## Fase 10: Sistema de Protocolos (5-6 dias)

### 10.1 Criar ProtocoloResource

```bash
php artisan make:filament-resource Protocolo --generate
```

#### Implementações:

-   [ ] Geração automática de número (PR-2025-001)
-   [ ] Formulário dinâmico por tipo:
    -   Iniciação (dados candidatas, taxas, data cerimônia)
    -   Transferência (assembleia destino)
    -   Afastamento (motivo, data)
    -   Retorno (data)
    -   Maioridade (data)
    -   Desligamento (motivo, data)
    -   Prêmios/Honrarias (descrição)
-   [ ] Campo JSON para dados específicos
-   [ ] Upload de anexos (múltiplos)
-   [ ] Gestão de taxas (repeater)
-   [ ] Workflow com estados
-   [ ] Histórico de mudanças (ProtocoloHistorico)

### 10.2 Implementar ProtocoloService

-   [ ] Método: criarProtocolo()
-   [ ] Método: mudarStatus()
-   [ ] Método: aprovar()
-   [ ] Método: rejeitar()
-   [ ] Método: concluir()
-   [ ] Validações de transição de estado

### 10.3 Implementar RelationManager para Anexos

-   [ ] Upload múltiplo
-   [ ] Visualização inline
-   [ ] Download
-   [ ] Delete

### 10.4 Implementar RelationManager para Taxas

-   [ ] CRUD de taxas
-   [ ] Marcar como pago
-   [ ] Upload de comprovante
-   [ ] Relatório financeiro

### 10.5 Implementar Notificações

-   [ ] Notificação ao criar
-   [ ] Notificação ao mudar status
-   [ ] Notificação de prazo próximo
-   [ ] Notificação de prazo vencido

---

## Fase 11: Sistema de Tickets (4-5 dias)

### 11.1 Criar TicketResource

```bash
php artisan make:filament-resource Ticket --generate
```

#### Implementações:

-   [ ] Geração automática de número (TKT-2025-001)
-   [ ] Formulário:
    -   Assembleia
    -   Comissão (baseado na categoria)
    -   Categoria (select)
    -   Assunto
    -   Descrição
    -   Prioridade
-   [ ] Atribuição automática a comissão
-   [ ] Sistema de respostas em thread
-   [ ] Notas internas (checkbox)
-   [ ] Upload de anexos
-   [ ] Workflow de estados

### 11.2 Implementar Sistema de SLA

-   [ ] Calcular prazos baseado na prioridade
-   [ ] Alertas visuais (badges coloridos)
-   [ ] Job para verificar SLA a cada 30min

### 11.3 Implementar RelationManager para Respostas

-   [ ] Thread de mensagens
-   [ ] Diferenciação visual (interno/público)
-   [ ] Menção de usuários
-   [ ] Timestamp

### 11.4 Implementar Avaliação de Atendimento

-   [ ] Modal de avaliação ao resolver
-   [ ] Rating 1-5 estrelas
-   [ ] Comentário opcional
-   [ ] Exibição de média no dashboard

---

## Fase 12: Calendário (3-4 dias)

### 12.1 Criar EventoCalendarioResource

```bash
php artisan make:filament-resource EventoCalendario --generate
```

#### Implementações:

-   [ ] Formulário:
    -   Título
    -   Descrição
    -   Tipo (select)
    -   Data Início/Fim
    -   Local
    -   Endereço
    -   Público (toggle)
    -   Cor do Evento (color picker)
-   [ ] Filtros: Assembleia, Tipo, Período

### 12.2 Integrar FullCalendar

```bash
composer require guava/filament-calendar
```

-   [ ] Criar página de calendário
-   [ ] Visualização mensal/semanal/diária
-   [ ] Criar eventos por drag & drop
-   [ ] Modal de detalhes do evento
-   [ ] Filtros dinâmicos

### 12.3 Implementar Sincronização de Aniversários

-   [ ] Job para atualizar aniversarios_cache
-   [ ] Exibição opcional no calendário
-   [ ] Configuração por assembleia

### 12.4 Implementar Exportação

-   [ ] Export iCal
-   [ ] Export Google Calendar
-   [ ] Compartilhamento de link público

---

## Fase 13: Dashboards (4-5 dias)

### 13.1 Dashboard Jurisdição

Criar widgets:

```bash
php artisan make:filament-widget TotalAssembleiasWidget --stats
php artisan make:filament-widget TotalMembrosWidget --chart
php artisan make:filament-widget ProtocolosPendentesWidget --table
php artisan make:filament-widget TicketsAbertosWidget --stats
php artisan make:filament-widget GraficoIniciacoesWidget --chart
php artisan make:filament-widget ProximosEventosWidget --table
```

### 13.2 Dashboard Assembleia

Criar widgets:

```bash
php artisan make:filament-widget MembrosStatusWidget --chart
php artisan make:filament-widget ProtocolosAssembleiaWidget --table
php artisan make:filament-widget ProximosEventosAssembleiaWidget --table
php artisan make:filament-widget AniversariantesWidget --stats
php artisan make:filament-widget CargosVagosWidget --table
```

### 13.3 Dashboard Membro

Criar widgets:

```bash
php artisan make:filament-widget ProximosEventosMembroWidget --table
php artisan make:filament-widget AniversariantesMembroWidget --stats
php artisan make:filament-widget MeusCargosWidget --info-list
```

### 13.4 Implementar Lógica Condicional

-   [ ] Mostrar dashboard baseado no role do usuário
-   [ ] Filtrar dados por permissões
-   [ ] Cache de widgets pesados

---

## Fase 14: Jobs e Notificações (3-4 dias)

### 14.1 Criar Jobs

```bash
php artisan make:job VerificarMaioridadeJob
php artisan make:job LembrarAniversariosJob
php artisan make:job VerificarPrazosProtocolosJob
php artisan make:job VerificarSLATicketsJob
php artisan make:job AtualizarAniversariosCalendarioJob
php artisan make:job AutoFecharTicketsResolvidosJob
php artisan make:job LimparArquivosTemporariosJob
php artisan make:job BackupDatabaseJob
php artisan make:job VerificarVencimentoCargosGrandeAssembleiaJob
php artisan make:job SincronizarPermissoesUsuariosJob
php artisan make:job RegistrarHistoricoCargosCerradosJob
```

### 14.2 Configurar Scheduler

No `app/Console/Kernel.php`:

```php
$schedule->job(new VerificarMaioridadeJob)->daily();
$schedule->job(new LembrarAniversariosJob)->dailyAt('08:00');
$schedule->job(new VerificarPrazosProtocolosJob)->hourly();
$schedule->job(new VerificarSLATicketsJob)->everyThirtyMinutes();
$schedule->job(new AtualizarAniversariosCalendarioJob)->monthly();
$schedule->job(new AutoFecharTicketsResolvidosJob)->dailyAt('02:00');
$schedule->job(new LimparArquivosTemporariosJob)->weekly();
$schedule->job(new BackupDatabaseJob)->dailyAt('04:00');
$schedule->job(new VerificarVencimentoCargosGrandeAssembleiaJob)->dailyAt('08:00');
$schedule->job(new SincronizarPermissoesUsuariosJob)->hourly();
$schedule->job(new RegistrarHistoricoCargosCerradosJob)->dailyAt('01:00');
```

### 14.3 Implementar Sistema de Notificações

```bash
php artisan make:notification ProtocoloCriadoNotification
php artisan make:notification ProtocoloAtualizadoNotification
php artisan make:notification TicketNovoNotification
php artisan make:notification TicketRespondidoNotification
php artisan make:notification CargoAtribuidoNotification
php artisan make:notification CargoVencendoNotification
php artisan make:notification MaioridadeProximaNotification
php artisan make:notification AniversarioNotification
```

### 14.4 Configurar Queues

```bash
php artisan queue:table
php artisan migrate
```

-   [ ] Configurar Redis ou Database queue
-   [ ] Testar processamento de jobs
-   [ ] Configurar Supervisor (produção)

---

## Fase 15: Relatórios (4-5 dias)

### 15.1 Criar RelatorioService

```bash
php artisan make:service RelatorioService
```

Métodos:

-   [ ] relatorioGeralJurisdicao()
-   [ ] relatorioAssembleia()
-   [ ] relatorioProtocolos()
-   [ ] relatorioTickets()
-   [ ] relatorioFinanceiro()
-   [ ] relatorioCargos()
-   [ ] relatorioGrandeAssembleia()
-   [ ] relatorioEventos()

### 15.2 Implementar Geração de PDFs

```bash
composer require barryvdh/laravel-dompdf
```

Criar views:

-   [ ] resources/views/pdfs/relatorio-jurisdicao.blade.php
-   [ ] resources/views/pdfs/relatorio-assembleia.blade.php
-   [ ] resources/views/pdfs/relatorio-protocolos.blade.php
-   [ ] resources/views/pdfs/relatorio-cargos.blade.php
-   [ ] resources/views/pdfs/carteirinha-membro.blade.php
-   [ ] resources/views/pdfs/certificado-cargo.blade.php

### 15.3 Implementar Exportação Excel

```bash
composer require maatwebsite/excel
```

Criar exports:

-   [ ] MembrosExport
-   [ ] ProtocolosExport
-   [ ] TicketsExport
-   [ ] CargosExport

### 15.4 Criar Páginas de Relatórios no Filament

```bash
php artisan make:filament-page RelatorioJurisdicao
php artisan make:filament-page RelatorioAssembleia
php artisan make:filament-page RelatorioCargos
php artisan make:filament-page RelatorioGrandeAssembleia
```

---

## Fase 16: Comandos Artisan (2-3 dias)

### 16.1 Criar Comandos

```bash
php artisan make:command InstallCommand --command=iorg:install
php artisan make:command CreateAdminCommand --command=iorg:create-admin
php artisan make:command SyncAniversariosCommand --command=iorg:sync-aniversarios
php artisan make:command GerarRelatorioMensalCommand --command=iorg:gerar-relatorio-mensal
php artisan make:command VerificarIntegridadeCommand --command=iorg:verificar-integridade
php artisan make:command LimparCacheCompletoCommand --command=iorg:limpar-cache-completo
php artisan make:command SincronizarCargosCommand --command=iorg:sincronizar-cargos
php artisan make:command EncerrarCargosVencidosCommand --command=iorg:encerrar-cargos-vencidos
php artisan make:command RelatorioCargosVagosCommand --command=iorg:relatorio-cargos-vagos
```

### 16.2 Implementar Lógica dos Comandos

**iorg:install**

-   [ ] Executar migrations
-   [ ] Executar seeders
-   [ ] Criar primeiro usuário admin
-   [ ] Configurar storage links
-   [ ] Mensagem de sucesso

**iorg:create-admin**

-   [ ] Solicitar nome, email, senha
-   [ ] Criar usuário
-   [ ] Atribuir role membro_jurisdicao
-   [ ] Mensagem de sucesso

**iorg:sync-aniversarios**

-   [ ] Limpar cache de aniversários
-   [ ] Reprocessar todos os membros
-   [ ] Atualizar aniversarios_cache
-   [ ] Exibir estatísticas

**iorg:gerar-relatorio-mensal**

-   [ ] Gerar relatório do mês anterior
-   [ ] Enviar por email para jurisdição
-   [ ] Salvar PDF em storage

**iorg:verificar-integridade**

-   [ ] Verificar membros sem assembleia
-   [ ] Verificar cargos órfãos
-   [ ] Verificar protocolos sem histórico
-   [ ] Verificar usuários sem roles
-   [ ] Relatório de inconsistências

**iorg:limpar-cache-completo**

-   [ ] Limpar cache de aplicação
-   [ ] Limpar cache de views
-   [ ] Limpar cache de rotas
-   [ ] Limpar cache de config
-   [ ] Mensagem de sucesso

**iorg:sincronizar-cargos**

-   [ ] Verificar cargos vencidos
-   [ ] Atualizar status
-   [ ] Registrar no histórico
-   [ ] Atualizar permissões de usuários

**iorg:encerrar-cargos-vencidos**

-   [ ] Buscar cargos com data_fim passada
-   [ ] Marcar como inativos
-   [ ] Registrar no histórico
-   [ ] Enviar notificações

**iorg:relatorio-cargos-vagos**

-   [ ] Listar cargos sem membro por assembleia
-   [ ] Exibir em tabela formatada
-   [ ] Opção para exportar CSV

---

## Fase Extra: Testes e Validações (3-4 dias)

### Testes Unitários

-   [ ] Testar Models e Relationships
-   [ ] Testar Scopes
-   [ ] Testar Observers
-   [ ] Testar Services

### Testes de Feature

-   [ ] Testar autenticação e autorização
-   [ ] Testar CRUD de Assembleias
-   [ ] Testar CRUD de Membros
-   [ ] Testar atribuição de cargos
-   [ ] Testar workflow de protocolos
-   [ ] Testar workflow de tickets
-   [ ] Testar Jobs

### Testes de Integração

-   [ ] Testar fluxo completo de protocolo
-   [ ] Testar fluxo completo de ticket
-   [ ] Testar permissões por role
-   [ ] Testar notificações

### Validações Manuais

-   [ ] Testar todos os formulários
-   [ ] Testar todas as validações
-   [ ] Testar uploads de arquivo
-   [ ] Testar geração de PDFs
-   [ ] Testar exportações
-   [ ] Testar dashboards
-   [ ] Testar calendário
-   [ ] Testar responsividade

---

## Checklist de Finalização

### Documentação

-   [ ] README.md completo
-   [ ] Instruções de instalação
-   [ ] Instruções de deploy
-   [ ] Documentação de comandos
-   [ ] Changelog

### Configuração de Produção

-   [ ] Configurar .env.production
-   [ ] Configurar cache (Redis)
-   [ ] Configurar queue worker (Supervisor)
-   [ ] Configurar backups automáticos
-   [ ] Configurar monitoramento de erros (Sentry)
-   [ ] Configurar SSL
-   [ ] Otimizar performance (php artisan optimize)

### Segurança

-   [ ] Validar todas as entradas
-   [ ] Sanitizar outputs
-   [ ] Configurar CSRF
-   [ ] Configurar rate limiting
-   [ ] Revisar permissões de arquivos
-   [ ] Configurar firewall

### Performance

-   [ ] Adicionar índices no banco
-   [ ] Implementar eager loading
-   [ ] Configurar cache de queries
-   [ ] Minificar assets
-   [ ] Configurar CDN (se necessário)

---

**Estimativa Total:** 8-12 semanas
**Última atualização:** Outubro 2025
**Status:** Pronto para iniciar Fase 1
