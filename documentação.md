# Documentação Técnica - Sistema de Gestão IORG Paraná

## 1. Visão Geral do Projeto

### 1.1 Objetivo
Desenvolver um sistema completo de gestão para a Jurisdição do Paraná da Ordem Internacional do Arco-Íris para Meninas (IORG), permitindo gerenciamento de múltiplas assembleias, membros, protocolos, tickets e calendários.

### 1.2 Stack Tecnológica
- *Backend*: Laravel 12
- *Admin Panel*: Filament 3.x
- *Banco de Dados*: MySQL 8.0+ ou PostgreSQL 15+
- *PHP*: 8.2+
- *Autenticação*: Laravel Sanctum / Fortify
- *Permissões*: Spatie Laravel Permission
- *Cache*: Redis (recomendado)

### 1.3 Requisitos do Sistema
- PHP 8.2 ou superior
- Composer 2.x
- Node.js 18+ e NPM
- MySQL 8.0+ ou PostgreSQL 15+
- Redis (opcional, mas recomendado)

---

## 2. Estrutura de Usuários e Permissões

### 2.1 Hierarquia de Roles

#### Nível Jurisdição (Gestão Total)
*IMPORTANTE:* Todos os membros da jurisdição têm acesso completo a todas as funcionalidades e visualização de todas as assembleias. A diferença está apenas nos títulos/cargos.

- *Suprema Deputada* (membro_jurisdicao)
  - Acesso total ao sistema
  - Gestão de todas assembleias
  - Aprovação final de protocolos
  - Criação e gestão de cargos
  - Atribuição de cargos da Grande Assembleia
  - Relatórios gerais da jurisdição

- *Oficial Executiva* (membro_jurisdicao)
  - Mesmo acesso da Suprema Deputada
  - Gestão operacional
  - Processamento de protocolos

- *Grande Deputado* (membro_jurisdicao)
  - Mesmo acesso da Suprema Deputada
  - Suporte às assembleias

- *Presidente de Comissão* (presidente_comissao)
  - Atendimento de tickets da sua comissão
  - Visualização de dados relacionados
  - Relatórios da comissão

- *Membros com Cargos da Grande Assembleia* (cargo_grande_assembleia)
  - Grande Ilustre Preceptora
  - Grande Ilustre Preceptora Adjunta
  - Grande Fé
  - Grande Caridade
  - Grande Esperança
  - (Outros cargos definidos pela Jurisdição)
  - *Observação:* Estes são cargos honoríficos atribuídos a meninas ativas. A Jurisdição gerencia estes cargos e os atribui anualmente.

#### Nível Assembleia (Gestão da Assembleia)
*Cargos Administrativos* (admin_assembleia)
Estes cargos têm acesso completo de administração da sua própria assembleia:

- *Ilustre Preceptora* (cargo principal da assembleia)
  - Gestão completa da assembleia
  - Criação de eventos no calendário
  - Gestão de membros
  - Solicitação de protocolos
  - Gestão de cargos internos

- *Ilustre Preceptora Adjunta*
  - Apoio à Ilustre Preceptora
  - Mesmos acessos administrativos

- *Presidente do Conselho*
  - Gestão do conselho consultivo
  - Acessos administrativos completos

- *Preceptora Mãe*
  - Orientação das meninas
  - Acessos administrativos completos

- *Preceptora Mãe Adjunta*
  - Apoio à Preceptora Mãe
  - Acessos administrativos completos

- *Arquivista*
  - Gestão de documentos
  - Acessos administrativos completos

*Cargos das Meninas* (cargos_meninas)
Estes são os cargos ocupados pelas meninas ativas da assembleia (não administrativos):
- Caridade
- Esperança
- Fé
- Natureza
- Imortalidade
- Fidelidade
- Patriotismo
- Serviço
- Tesoureira
- (Outros cargos criados pela Jurisdição)

#### Nível Membro
- *Menina Ativa* (menina_ativa)
  - Visualização do próprio perfil
  - Calendário da assembleia
  - Informações básicas
  - Pode ter cargos (Digna Conselheira, Fé, etc.)
  - Pode ter cargos da Grande Assembleia

- *Menina Maioridade* (menina_maioridade)
  - Acesso limitado após completar 20 anos
  - Histórico pessoal
  - Informações de contato
  - Visualização dos cargos que já ocupou

### 2.2 Matriz de Permissões

| Funcionalidade | Jurisdição | Pres. Comissão | Admin Assembleia* | Menina Ativa | Maioridade |
|----------------|------------|----------------|-------------------|--------------|------------|
| Ver todas assembleias | ✓ | - | - | - | - |
| Gerenciar todas assembleias | ✓ | - | - | - | - |
| Gerenciar assembleia própria | ✓ | - | ✓ | - | - |
| Criar/Gerenciar cargos (tipos) | ✓ | - | - | - | - |
| Atribuir cargos assembleia | ✓ | - | ✓ | - | - |
| Atribuir cargos Grande Assembleia | ✓ | - | - | - | - |
| Ver histórico de cargos | ✓ | - | ✓ | ✓ (próprio) | ✓ (próprio) |
| Criar protocolos | ✓ | - | ✓ | - | - |
| Aprovar protocolos | ✓ | - | - | - | - |
| Ver todos tickets | ✓ | ✓ (comissão) | ✓ (assembleia) | - | - |
| Responder tickets | ✓ | ✓ (comissão) | - | - | - |
| Criar tickets | ✓ | - | ✓ | - | - |
| Gerenciar membros | ✓ | - | ✓ | - | - |
| Ver calendário geral | ✓ | ✓ | - | - | - |
| Gerenciar calendário assembleia | ✓ | - | ✓ | - | - |
| Ver calendário assembleia | ✓ | - | ✓ | ✓ | - |
| Relatórios gerais | ✓ | - | - | - | - |
| Relatórios assembleia | ✓ | - | ✓ | - | - |

*Admin Assembleia inclui:* Ilustre Preceptora, Ilustre Preceptora Adjunta, Presidente do Conselho, Preceptora Mãe, Preceptora Mãe Adjunta, Arquivista

---

## 3. Estrutura do Banco de Dados

### 3.1 Diagrama de Relacionamentos


jurisdicao
    ↓
assembleias
    ↓
membros ← usuarios
    ↓
cargos (assembleia + grande_assembleia)
protocolos
tickets
eventos_calendario


### 3.2 Tabelas Principais

#### users
php
- id: bigint (PK)
- name: string
- email: string (unique)
- email_verified_at: timestamp (nullable)
- password: string
- telefone: string (nullable)
- data_nascimento: date (nullable)
- cpf: string (nullable, unique)
- remember_token: string (nullable)
- timestamps


#### jurisdicoes
php
- id: bigint (PK)
- nome: string (ex: "Jurisdição do Paraná")
- sigla: string (ex: "IORG-PR")
- email: string
- telefone: string
- endereco_completo: text
- ativa: boolean (default: true)
- timestamps
- soft_deletes


#### assembleias
php
- id: bigint (PK)
- jurisdicao_id: bigint (FK)
- numero: integer (ex: Assembleia nº 5)
- nome: string (ex: "Luz da Esperança")
- cidade: string
- estado: string (default: "PR")
- endereco_completo: text
- data_fundacao: date
- email: string (nullable)
- telefone: string (nullable)
- ativa: boolean (default: true)
- loja_patrocinadora: string (nullable)
- timestamps
- soft_deletes

Indexes:
- jurisdicao_id
- cidade
- ativa


#### membros
php
- id: bigint (PK)
- user_id: bigint (FK, nullable) - Relaciona com conta de usuário
- assembleia_id: bigint (FK)
- nome_completo: string
- data_nascimento: date
- cpf: string (nullable, unique)
- telefone: string
- email: string (nullable)
- endereco_completo: text
- nome_mae: string
- telefone_mae: string
- nome_pai: string (nullable)
- telefone_pai: string (nullable)
- responsavel_legal: string (nullable)
- contato_responsável: string
- data_iniciacao: date (nullable)
- madrinha: string
- data_maioridade: date (nullable)
- status: enum ('candidata', 'ativa', 'afastada', 'maioridade', 'desligada')
- motivo_afastamento: text (nullable)
- membro_cruz boolean (true, false)
- coracao_cores boolean (true, false)
- homenageados_ano date(nullable)
- foto: string (nullable)
- timestamps
- soft_deletes

Indexes:
- assembleia_id
- user_id
- status
- data_nascimento


#### tipos_cargos_assembleia
php
- id: bigint (PK)
- nome: string (ex: "Ilustre Preceptora", "Ilustre Preceptora Adjunta")
- categoria: enum ('administrativo', 'menina', 'grande_assembleia')
  # administrativo: Ilustre Preceptora, Presidente Conselho, etc
  # menina: Fé, Caridade, etc
  # grande_assembleia: Grande Ilustre Preceptora, Grande Fé, etc
- is_admin: boolean (default: false) - Define se tem acesso administrativo
- ordem: integer (para ordenação na exibição)
- ativo: boolean (default: true)
- criado_por: enum ('sistema', 'jurisdicao') - Cargos do sistema não podem ser deletados
- descricao: text (nullable)
- acessos 
  # Exemplo: Se is_admin = false -> Visualizar e/ou editar calendario, Visualizar e/ou criar/gerir protocolos
- timestamps
- soft_deletes

Indexes:
- categoria
- ativo
- ordem


#### cargos_assembleia
php
- id: bigint (PK)
- assembleia_id: bigint (FK)
- membro_id: bigint (FK, nullable) - Pode ser cargo vago
- tipo_cargo_id: bigint (FK) - Referência ao tipo de cargo
- data_inicio: date
- data_fim: date (nullable)
- ativo: boolean (default: true)
- observacoes: text (nullable)
- timestamps

Indexes:
- assembleia_id
- membro_id
- tipo_cargo_id
- ativo


#### cargos_grande_assembleia
php
- id: bigint (PK)
- membro_id: bigint (FK)
- tipo_cargo_id: bigint (FK) - Referência ao tipo de cargo (categoria = grande_assembleia)
- data_inicio: date
- data_fim: date (nullable)
- ativo: boolean (default: true)
- atribuido_por: bigint (FK) - Membro da jurisdição que atribuiu
- observacoes: text (nullable)
- timestamps

Indexes:
- membro_id
- tipo_cargo_id
- ativo
- data_inicio


#### historico_cargos
php
- id: bigint (PK)
- membro_id: bigint (FK)
- tipo_cargo_id: bigint (FK)
- cargo_assembleia_id: bigint (FK, nullable) - Se for cargo de assembleia
- cargo_grande_assembleia_id: bigint (FK, nullable) - Se for cargo da Grande Assembleia
- assembleia_id: bigint (FK, nullable)
- semestre: # devemos aqui garantir que sera um semestre do ano tipo 2025.1 para primeiro semestre ou 2025.2 para segundo semestre(date)
- tipo_historico: enum ('assembleia', 'grande_assembleia')
- created_at: timestamp

Indexes:
- membro_id
- tipo_cargo_id
- assembleia_id
- tipo_historico


#### protocolos (Lembrar que devem gerar logs visiveis na visualização do protocolo)
php
- id: bigint (PK)
- numero_protocolo: string (unique) - Gerado automaticamente (ex: "PR-2025-001")
- assembleia_id: bigint (FK)
- tipo: enum ('iniciacao', 'transferencia', 'afastamento', 'retorno', 'maioridade', 'desligamento', 'premios/honrarias' ) # irão existir mais tipos de protocolos, veja a melhor forma de fazer isso
- titulo: string
- descricao: text
- membro_id: bigint (FK, nullable) - Membro relacionado ao protocolo
- solicitante_id: bigint (FK) - Quem criou o protocolo
- status: enum ('rascunho', 'pendente', 'em_analise', 'aprovado', 'rejeitado', 'concluido', 'cancelado')
- prioridade: enum ('baixa', 'normal', 'alta', 'urgente')
- data_solicitacao: datetime
- data_conclusao: datetime (nullable)
- observacoes: text (nullable)
- dados_json: json (nullable) - Campos específicos por tipo de protocolo
- timestamps
- soft_deletes

Indexes:
- numero_protocolo (unique)
- assembleia_id
- tipo
- status
- solicitante_id
- responsavel_id
- membro_id


#### protocolo_historico
php
- id: bigint (PK)
- protocolo_id: bigint (FK)
- user_id: bigint (FK)
- status_anterior: string
- status_novo: string
- comentario: text (nullable)
- created_at: timestamp

Indexes:
- protocolo_id
- user_id


#### protocolo_anexos
php
- id: bigint (PK)
- protocolo_id: bigint (FK)
- nome_arquivo: string
- caminho_arquivo: string
- tipo_arquivo: string
- tamanho: integer (bytes)
- uploaded_by: bigint (FK)
- timestamps

Indexes:
- protocolo_id


#### protocolo_taxas
php
- id: bigint (PK)
- protocolo_id: bigint (FK)
- descricao: string (ex: "Taxa de Iniciação")
- valor: decimal(10,2)
- pago: boolean (default: false)
- data_pagamento: date (nullable)
- forma_pagamento: enum ('dinheiro', 'pix', 'transferencia', 'cartao') (nullable)
- comprovante: string (nullable)
- timestamps

Indexes:
- protocolo_id


#### comissoes
php
- id: bigint (PK)
- jurisdicao_id: bigint (FK)
- nome: string (ex: "Comissão de Rituaalística")
- descricao: text (nullable)
- ativa: boolean (default: true)
- timestamps

Indexes:
- jurisdicao_id


#### comissao_membros
php
- id: bigint (PK)
- comissao_id: bigint (FK)
- user_id: bigint (FK)
- cargo: enum ('presidente', 'membro')
- data_inicio: date
- data_fim: date (nullable)
- ativo: boolean (default: true)
- timestamps

Indexes:
- comissao_id
- user_id
- ativo


#### tickets (Assim como protocolos, geram logs que devem poder ser visualizados)
php
- id: bigint (PK)
- numero_ticket: string (unique) - Gerado automaticamente (ex: "TKT-2025-001")
- assembleia_id: bigint (FK, nullable) - Pode ser ticket geral
- comissao_id: bigint (FK, nullable) - Comissão responsável
- solicitante_id: bigint (FK)
- responsavel_id: bigint (FK, nullable)
- categoria: enum ('duvida', 'suporte_tecnico', 'financeiro', 'ritual', 'evento', 'administrativo', 'outros')
- assunto: string
- descricao: text
- prioridade: enum ('baixa', 'normal', 'alta', 'urgente')
- status: enum ('aberto', 'em_atendimento', 'aguardando_resposta', 'resolvido', 'fechado', 'cancelado')
- data_abertura: datetime
- data_primeira_resposta: datetime (nullable)
- data_resolucao: datetime (nullable)
- data_fechamento: datetime (nullable)
- avaliacao: integer (1-5, nullable)
- comentario_avaliacao: text (nullable)
- timestamps
- soft_deletes

Indexes:
- numero_ticket (unique)
- assembleia_id
- comissao_id
- solicitante_id
- responsavel_id
- status
- categoria


#### ticket_respostas
php
- id: bigint (PK)
- ticket_id: bigint (FK)
- user_id: bigint (FK)
- mensagem: text
- interno: boolean (default: false) - Nota interna, não visível ao solicitante
- created_at: timestamp

Indexes:
- ticket_id
- user_id


#### ticket_anexos
php
- id: bigint (PK)
- ticket_id: bigint (FK)
- nome_arquivo: string
- caminho_arquivo: string
- tipo_arquivo: string
- tamanho: integer
- uploaded_by: bigint (FK)
- timestamps

Indexes:
- ticket_id


#### eventos_calendario
php
- id: bigint (PK)
- assembleia_id: bigint (FK, nullable) - Null = evento da jurisdição
- titulo: string
- descricao: text (nullable)
- tipo: enum ('reuniao', 'iniciacao', 'instalacao', 'cerimonia_publica', 'filantropia', 'outros')
- data_inicio: datetime
- data_fim: datetime (nullable)
- local: string (nullable)
- endereco: text (nullable)
- publico: boolean (default: false) - Visível para meninas ativas
- criado_por: bigint (FK)
- cor_evento: string (nullable) - Hex color para calendário
- timestamps
- soft_deletes

Indexes:
- assembleia_id
- tipo
- data_inicio
- publico


#### aniversarios_cache
php
- id: bigint (PK)
- membro_id: bigint (FK)
- assembleia_id: bigint (FK)
- tipo: enum ('membro', 'iniciacao', 'maioridade')
- mes: integer (1-12)
- dia: integer (1-31)
- updated_at: timestamp

Indexes:
- membro_id
- assembleia_id
- mes, dia (composite)


### 3.3 Relacionamentos Principais

php
// User
- hasOne(Membro)
- hasMany(Protocolos) através de solicitante_id
- hasMany(Tickets) através de solicitante_id
- belongsToMany(Comissoes) através de comissao_membros
- belongsTo(Assembleia) através de Membro (se aplicável)

// Jurisdicao
- hasMany(Assembleias)
- hasMany(Comissoes)

// Assembleia
- belongsTo(Jurisdicao)
- hasMany(Membros)
- hasMany(Protocolos)
- hasMany(Tickets)
- hasMany(EventosCalendario)
- hasMany(CargosAssembleia)

// Membro
- belongsTo(User)
- belongsTo(Assembleia)
- hasMany(Protocolos)
- hasMany(CargosAssembleia)
- hasMany(CargosGrandeAssembleia)
- hasMany(HistoricoCargos)

// TipoCargo
- hasMany(CargosAssembleia)
- hasMany(CargosGrandeAssembleia)

// CargoAssembleia
- belongsTo(Assembleia)
- belongsTo(Membro)
- belongsTo(TipoCargo)

// CargoGrandeAssembleia
- belongsTo(Membro)
- belongsTo(TipoCargo)
- belongsTo(User) através de atribuido_por

// HistoricoCargos
- belongsTo(Membro)
- belongsTo(TipoCargo)
- belongsTo(Assembleia) quando aplicável
- belongsTo(CargoAssembleia) quando aplicável
- belongsTo(CargoGrandeAssembleia) quando aplicável

// Protocolo
- belongsTo(Assembleia)
- belongsTo(Membro)
- belongsTo(User) através de solicitante_id
- belongsTo(User) através de responsavel_id
- hasMany(ProtocoloHistorico)
- hasMany(ProtocoloAnexos)
- hasMany(ProtocoloTaxas)

// Ticket
- belongsTo(Assembleia)
- belongsTo(Comissao)
- belongsTo(User) através de solicitante_id
- belongsTo(User) através de responsavel_id
- hasMany(TicketRespostas)
- hasMany(TicketAnexos)

// Comissao
- belongsTo(Jurisdicao)
- hasMany(Tickets)
- belongsToMany(Users) através de comissao_membros


---

## 4. Módulos e Funcionalidades

### 4.1 Dashboard

#### Dashboard Jurisdição
*Widgets:*
- Total de Assembleias (ativas/inativas)
- Total de Membros (por status)
- Protocolos Pendentes (últimos 30 dias)
- Tickets Abertos (por comissão)
- Gráfico de Iniciações (últimos 12 meses)
- Próximos Eventos das Assembleias (próximos 7 dias, a maioria dos eventos acontecem sabados garantir visualização em lista)

#### Dashboard Assembleia
*Widgets:*
- Total de Membros (por status)
- Protocolos da Assembleia (pendentes/em andamento)
- Próximos Eventos da Assembleia
- Aniversariantes do Mês
- Cargos Vagos da Assembleia
- Calendário Resumido

#### Dashboard Membro
*Widgets:*
- Próximos Eventos
- Aniversariantes do Mês
- Informações Pessoais
- Meus Cargos Atuais (Grande Assembleia e Assembleia para meninas ativas, se maioridade não exibir ao menos que tenha algum cargo registrado que ainda não acabou, sem data de termino)

### 4.2 Gestão de Assembleias

*Filament Resource: AssembleiaResource*

*Campos do Formulário:*
- Seção "Informações Básicas"
  - Número da Assembleia
  - Nome da Assembleia
  - Data de Fundação
  - Loja Patrocinadora
  - Status (Ativa/Inativa)

- Seção "Contato"
  - Email
  - Telefone
  - Endereço Completo

- Seção "Localização"
  - Cidade
  - Estado (readonly: PR)

*Tabela (Listagem):*
- Colunas: Número, Nome, Cidade, Membros Ativas, Status, Ações
- Filtros: Cidade, Status, Data de Fundação
- Ações em Massa: Ativar/Desativar
- Busca: Número, Nome, Cidade

*Tabs no Detalhe:*
1. *Visão Geral* - Informações básicas
2. *Membros* - Lista de membros com filtros por status
3. *Cargos* - Gestão de cargos da assembleia
4. *Protocolos* - Protocolos relacionados
5. *Calendário* - Eventos da assembleia
6. *Estatísticas* - Gráficos e métricas

*Actions Personalizadas:*
- Exportar Relatório PDF (Com base nos protocolos, número de iniciações por ano, transferencias, etc..)
- Enviar Email para Todos Membros

### 4.3 Gestão de Cargos (Jurisdição)

*Filament Resource: TipoCargoResource*

*IMPORTANTE:* Apenas a Jurisdição pode criar, editar e excluir tipos de cargos. Estes cargos serão usados por todas as assembleias e para atribuição de cargos da Grande Assembleia.

#### 4.3.1 Tipos de Cargos

*Categorias de Cargos:*

1. *Administrativos* (acesso de gestão da assembleia)
   - Ilustre Preceptora
   - Ilustre Preceptora Adjunta
   - Presidente do Conselho
   - Preceptora Mãe
   - Preceptora Mãe Adjunta
   - Arquivista

2. *Cargos das Meninas* (sem acesso administrativo)
   - Caridade
   - Esperança
   - Fé
   - Arquivista
   - Arquivista Adjunta
   - Tesoureira
   - Tesoureira Adjunta
   - Capelã 
   - Chefe do Cerimonial
   - Amor
   - Religião
   - Natureza
   - Imortalidade
   - Fidelidade
   - Patriotismo
   - Serviço
   - Observadora Confidencial
   - Observadora Externa
   - Música
   - Regente do Coro
   - Coro
   - Arquiteta
   - Arquiteta Adjunta
   - Jornalista
   - Jornalista Adjunta
   - Chefe de Ritualística
   - Chefe de Ritualística Adjunta
   - Chanceler
   - Chanceler Adjunta
   - Chefe de Banquete
   - Chefe de Banquete Adjunta

3. *Grande Assembleia* (cargos honoríficos anuais)
   - Grande Ilustre Preceptora
   - Grande Ilustre Preceptora Adjunta
   - Grande Fé
   - Grande Caridade
   - Grande Esperança
   - Grande Natureza
   - (Outros cargos customizados)

*Campos do Formulário:*
- Nome do Cargo
- Categoria (select: Administrativo, Menina, Grande Assembleia)
- Possui Acesso Administrativo (toggle - apenas para categoria Administrativo)
- Se acesso administrativo: Niveis de acesso (checkbox com o basico selecionado)
- Ordem de Exibição (number)
- Status (Ativo/Inativo)
- Descrição (textarea, opcional)

*Tabela (Listagem):*
- Colunas: Nome, Categoria, Acesso Admin, Ordem, Status, Ações
- Filtros: Categoria, Status, Acesso Admin
- Busca: Nome
- Ordenação padrão: Categoria, Ordem
- Badges coloridos por Categoria

*Validações:*
- Nome único
- Cargos criados pelo sistema não podem ser deletados (apenas desativados)
- Não permitir desativar cargo se houver membros atualmente com esse cargo ativo

*Actions:*
- Visualizar Membros com este Cargo
- Ativar/Desativar
- Reordenar (drag and drop)

#### 4.3.2 Atribuição de Cargos de Assembleia

*Filament Resource: CargoAssembleiaResource* (dentro de AssembleiaResource)

*Contexto:* Pode ser gerenciado tanto pela Jurisdição (todas assembleias) quanto pelos Admins da Assembleia (apenas sua assembleia)

*Campos do Formulário:*
- Assembleia (select - readonly se for admin de assembleia)
- Tipo de Cargo (select - filtrado por categoria: administrativo + menina)
- Membro (select - apenas membros ativas da assembleia)
- Data de Início
- Data de Fim (opcional)
- Status (Ativo/Inativo)
- Observações (textarea, opcional)

*Tabela (Listagem):*
- Colunas: Cargo, Membro, Data Início, Data Fim, Status, Ações
- Filtros: Tipo de Cargo, Status, Período
- Busca: Nome do Membro
- Badges: Status (Ativo/Encerrado), Categoria do Cargo

*Validações:*
- Não permitir dois cargos iguais ativos simultaneamente na mesma assembleia
- Data fim deve ser posterior à data início
- Ao marcar como inativo, solicitar data de fim
- Ao adicionar cargo administrativo, verificar se membro já tem conta de usuário

*Actions:*
- Encerrar Cargo (solicita data fim e motivo)
- Ver Histórico do Membro
- Criar Conta de Usuário (se cargo administrativo e membro não tiver conta)

*Regras de Negócio:*
- Ao atribuir cargo administrativo: automaticamente cria/atualiza role do usuário
- Ao encerrar cargo: registra no histórico automaticamente
- Ao encerrar: se era último cargo administrativo, remove role de admin_assembleia

#### 4.3.3 Atribuição de Cargos da Grande Assembleia

*Filament Resource: CargoGrandeAssembleiaResource* (apenas Jurisdição)

*IMPORTANTE:* Apenas membros da Jurisdição podem atribuir e gerenciar estes cargos.

*Campos do Formulário:*
- Tipo de Cargo (select - filtrado por categoria: grande_assembleia)
- Membro (select - busca em todas as meninas ativas de todas as assembleias)
  - Mostrar: Nome, Assembleia, Idade
- Assembleia da Membro (readonly, preenchido automaticamente)
- Data de Início
- Data de Fim (opcional - geralmente 1 ano)
- Observações (textarea, opcional)

*Tabela (Listagem):*
- Colunas: Cargo, Membro, Assembleia, Data Início, Data Fim, Status, Ações
- Filtros: Tipo de Cargo, Assembleia, Status, Ano
- Busca: Nome do Membro
- Ordenação padrão: Data Início DESC

*Validações:*
- Não permitir dois cargos da Grande Assembleia iguais ativos simultaneamente
- Apenas meninas com status "ativa" podem receber cargos
- Data fim deve ser posterior à data início
- Atribuído por: registra automaticamente o usuário da jurisdição

*Actions:*
- Encerrar Cargo (solicita data fim)
- Renovar por Mais 1 Ano
- Ver Histórico da Membro
- Enviar Certificado de Nomeação (PDF)

*Notificações:*
- Ao atribuir: notifica a membro, a assembleia dela, e registra no histórico
- 1 mês antes do fim: lembra jurisdição sobre renovação
- No fim do mandato: encerra automaticamente e notifica

#### 4.3.4 Visualização no Perfil do Membro

*Seção: Cargos* (dentro de MembroResource - View)

*Tabs:*

1. *Cargos Atuais*
   - Lista de cargos ativos na assembleia
   - Lista de cargos ativos na Grande Assembleia
   - Badges coloridos por categoria
   - Data de início de cada cargo

2. *Histórico de Cargos*
   - Timeline de todos os cargos já ocupados
   - Filtros: Tipo (Assembleia/Grande Assembleia), Ano
   - Informações: Cargo, Período (início - fim), Duração
   - Exportar histórico em PDF

*Widget no Perfil:*
- Card resumido mostrando cargo principal atual
- Se tiver cargo da Grande Assembleia: destaque especial

*Funcionalidade para Admin da Assembleia:*
- Select para adicionar novo cargo
- Puxa todos os tipos de cargos da assembleia (categorias: administrativo + menina)
- Campos: Data de início, Observações
- Botão: Salvar

*Funcionalidade para Jurisdição:*
- Além do select de cargos da assembleia
- Select para adicionar cargo da Grande Assembleia
- Ambos no mesmo formulário/modal

### 4.4 Gestão de Membros

*Filament Resource: MembroResource*

*Campos do Formulário:*
- Seção "Dados Pessoais"
  - Foto (upload)
  - Nome Completo
  - Data de Nascimento (com validação de idade: 11-20 anos)
  - CPF (com máscara)
  - RG
  - Email
  - Telefone (com máscara)

- Seção "Endereço"
  - CEP (com busca automática)
  - Logradouro
  - Número
  - Complemento
  - Bairro
  - Cidade
  - Estado

- Seção "Responsáveis"
  - Nome da Mãe
  - Telefone da Mãe
  - Nome do Pai
  - Telefone do Pai
  - Responsável Legal (se diferente)
  - Contato de Emergência

- Seção "Informações IORG"
  - Assembleia (select)
  - Data de Iniciação
  - Status (candidata, ativa, afastada, maioridade, desligada)
  - Motivo de Afastamento (condicional)
  - Data de Maioridade (calculado automaticamente)

- Seção "Cargos Atuais" (visualização)
  - Lista de cargos ativos na assembleia
  - Lista de cargos ativos na Grande Assembleia
  - Botão: Adicionar Novo Cargo (modal)

- Seção "Observações"
  - Observações Gerais (textarea)

*Tabela (Listagem):*
- Colunas: Foto, Nome, Assembleia, Idade, Status, Cargo Principal, Data Iniciação, Ações
- Filtros: Assembleia, Status, Idade, Mês de Aniversário, Tem Cargo, Tipo de Cargo
- Busca: Nome, CPF, Email
- Badges coloridos para Status e Cargo

*Validações Importantes:*
- Idade mínima: 11 anos
- Mudança automática para "Maioridade" ao completar 20 anos (via Job agendado)
- CPF único no sistema
- Email único (se preenchido)
- Ao mudar status para "desligada" ou "maioridade": encerrar automaticamente todos os cargos ativos

*Actions Personalizadas:*
- Criar Conta de Usuário (se ainda não tiver)
- Gerar Carteirinha Digital (com cargos atuais)
- Enviar Email de Boas-Vindas
- Registrar Afastamento/Retorno
- Transferir para Outra Assembleia (encerra cargos automaticamente)
- Adicionar Cargo (modal com select de tipos de cargo)
- Ver Histórico Completo de Cargos

*Modal: Adicionar Cargo*
- Select: Tipo de Cargo (filtrado conforme permissão)
  - Admin Assembleia: vê apenas cargos da sua assembleia
  - Jurisdição: vê todos os cargos (assembleia + Grande Assembleia)
- Data de Início (datepicker)
- Data de Fim (datepicker, opcional)
- Observações (textarea, opcional)
- Botão: Salvar

*Relatório de Cargos:*
- Exportar lista de membros com seus cargos atuais
- Exportar histórico de ocupação de cargos por período
- Relatório de cargos vagos por assembleia

### 4.5 Sistema de Protocolos

*Filament Resource: ProtocoloResource*

#### Tipos de Protocolos e Campos Específicos

*1. Iniciação*
- Dados das Candidatas ou candidata (referência a membro)
- Taxas de Iniciação (repeater)
- Data Proposta para Cerimônia
- Madrinhas


*2-8. Outros tipos de protocolos*
(Transferências, Afastamento, Retorno, Maioridade, Desligamento)

#### Workflow de Protocolos



Rascunho → Pendente → Em Análise (pela jurisdição) → Aprovado/Rejeitado (pela jurisdição) → Concluído
ou para protocolo de iniciação
Rascunho → Pendente → Em Análise (pela jurisdição) → Confirmação adicional assembleia (Confirmar se as meninas iniciaram) -> Aprovado/Rejeitado (pela jurisdição) → Concluído
ou para protocolo de transferência
Rascunho → Pendente → Aprovação assembleia destino -> Aprovado/Rejeitado (pela jurisdição) → Concluído



*Notificações Automáticas:*
- Criação: notifica Jurisdição
- Atribuição: notifica Responsável
- Mudança de Status: notifica Solicitante
- Prazo próximo: lembra Responsável (3 dias antes)
- Prazo vencido: escala para supervisor

### 4.6 Sistema de Tickets

*Fluxo de Atendimento:*

Aberto → Atribuído à Comissão → Em Atendimento → Resolvido → Fechado


*Sistema de SLA:*
- Urgente: Primeira resposta em 4h, Resolução em 24h
- Alta: Primeira resposta em 8h, Resolução em 3 dias
- Normal: Primeira resposta em 24h, Resolução em 7 dias
- Baixa: Primeira resposta em 48h, Resolução em 15 dias

### 4.7 Calendário

*Tipos de Eventos:*
- Reunião, Iniciação, Instalação, Cerimônia Pública, Filantropia, Outros

*Funcionalidades Especiais:*
- Eventos Recorrentes
- Lembretes Automáticos
- Exportar para Google Calendar/iCal
- Sincronização de Aniversários (opcional exibir os aniversarios do membros da assembleia no calendario)

### 4.8 Relatórios e Estatísticas

#### Relatórios da Jurisdição
1. Relatório Geral da Jurisdição
2. Relatório de Protocolos
3. Relatório de Tickets
4. Relatório Financeiro de Protocolos
5. Relatório de Eventos
6. *Relatório de Cargos* (NOVO)
7. *Relatório da Grande Assembleia* (NOVO)

#### Relatórios da Assembleia
1. Relatório da Assembleia
2. Relatório de Membros
3. Relatório de Atividades
4. *Relatório de Cargos da Assembleia* (NOVO)

---

## 8. Jobs e Tarefas Agendadas

### 8.1 Jobs Importantes

1. *VerificarMaioridadeJob* - Diário às 00:00
2. *LembrarAniversariosJob* - Diário às 08:00
3. *VerificarPrazosProtocolosJob* - A cada hora
4. *VerificarSLATicketsJob* - A cada 30 minutos
5. *AtualizarAniversariosCalendarioJob* - Mensal
6. *AutoFecharTicketsResolvidosJob* - Diário às 02:00
7. *LimparArquivosTemporariosJob* - Semanal
8. *BackupDatabaseJob* - Diário às 04:00
9. *VerificarVencimentoCargosGrandeAssembleiaJob* - Diário às 08:00 (NOVO)
10. *SincronizarPermissoesUsuariosJob* - A cada hora (NOVO)
11. *RegistrarHistoricoCargosCerradosJob* - Diário às 01:00 (NOVO)

---

## 12. Dados Iniciais do Sistema (Seeders)

### 12.1 TipoCargoSeeder

*Cargos Administrativos:*
- Ilustre Preceptora
- Ilustre Preceptora Adjunta
- Presidente do Conselho
- Preceptora Mãe
- Preceptora Mãe Adjunta
- Arquivista

*Cargos das Meninas:*
   - Caridade
   - Esperança
   - Fé
   - Arquivista
   - Arquivista Adjunta
   - Tesoureira
   - Tesoureira Adjunta
   - Capelã 
   - Chefe do Cerimonial
   - Amor
   - Religião
   - Natureza
   - Imortalidade
   - Fidelidade
   - Patriotismo
   - Serviço
   - Observadora Confidencial
   - Observadora Externa
   - Música
   - Regente do Coro
   - Coro
   - Arquiteta
   - Arquiteta Adjunta
   - Jornalista
   - Jornalista Adjunta
   - Chefe de Ritualística
   - Chefe de Ritualística Adjunta
   - Chanceler
   - Chanceler Adjunta
   - Chefe de Banquete
   - Chefe de Banquete Adjunta

*Cargos da Grande Assembleia:*
- Grande Ilustre Preceptora
- Grande Ilustre Preceptora Adjunta
- Grande Fé, Grande Caridade, Grande Esperança
- Grande Natureza, Grande Imortalidade, Grande Fidelidade
- Grande Patriotismo, Grande Serviço

### 12.2 RoleSeeder

*Roles do Sistema:*
- membro_jurisdicao (acesso total)
- presidente_comissao
- admin_assembleia
- cargo_grande_assembleia
- menina_ativa
- menina_maioridade

### 12.3 ComissaoSeeder

*Comissões Padrão:*
- Comissão de Ritualística
- Comissão de Legislação
- Comissão de Tradução
- Comissão de Comunicação

---

## 15. Comandos Artisan Customizados

bash
# Comandos disponíveis:
php artisan iorg:install
php artisan iorg:create-admin
php artisan iorg:sync-aniversarios
php artisan iorg:gerar-relatorio-mensal
php artisan iorg:verificar-integridade
php artisan iorg:limpar-cache-completo
php artisan iorg:sincronizar-cargos
php artisan iorg:encerrar-cargos-vencidos
php artisan iorg:relatorio-cargos-vagos


---

## 18. Glossário IORG

- *Assembleia*: Grupo local da IORG
- *Ilustre Preceptora*: Cargo máximo de uma menina na assembleia
- *Preceptora Mãe e Preceptora Mãe Adjunta*: Conselheiras adultas que orientam as meninas
- *Presidente do Conselho*: Responsável pelo conselho consultivo
- *Iniciação*: Cerimônia de entrada de nova membro
- *Maioridade*: Completar 20 anos e deixar de ser membro ativo
- *Jurisdição*: Região administrativa (no caso, Paraná)
- *Suprema Deputada*: Líder máxima da jurisdição
- *Grande Assembleia*: Estrutura da jurisdição com cargos honoríficos anuais
---

*Versão*: 2.0 Final  
*Data*: Outubro 2025  
*Status*: Pronto para Desenvolvimento