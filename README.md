# SISIRIS - Sistema de Gestão IORG Paraná

Sistema completo de gestão para a Jurisdição do Paraná da Ordem Internacional do Arco-Íris para Meninas (IORG).

## Stack Tecnológica

- **Laravel**: 12.x
- **Filament**: 4.1.7
- **PHP**: 8.4+
- **Database**: MySQL 8.0+ / PostgreSQL 15+
- **Cache**: Redis (recomendado)
- **Autenticação**: Laravel Sanctum
- **Permissões**: Spatie Laravel Permission 6.x

## Requisitos do Sistema

- PHP 8.2 ou superior
- Composer 2.x
- Node.js 18+ e NPM
- MySQL 8.0+ ou PostgreSQL 15+
- Redis (opcional, mas recomendado)

## Instalação

### 1. Clonar o repositório
```bash
git clone <repository-url>
cd sisiris
```

### 2. Instalar dependências
```bash
composer install
npm install
```

### 3. Configurar ambiente
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurar banco de dados
Edite o arquivo `.env` com suas credenciais:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sisiris
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

### 5. Executar migrations e seeders
```bash
php artisan migrate
php artisan db:seed
```

### 6. Criar storage link
```bash
php artisan storage:link
```

### 7. Compilar assets
```bash
npm run build
```

### 8. Criar usuário admin
```bash
php artisan iorg:create-admin
```

### 9. Iniciar servidor
```bash
php artisan serve
```

Acesse: http://localhost:8000/admin

## Estrutura do Projeto

```
app/
├── Filament/
│   ├── Resources/      # Recursos do Filament (CRUD)
│   ├── Widgets/        # Widgets para dashboards
│   └── Pages/          # Páginas customizadas
├── Models/             # Models Eloquent
├── Policies/           # Authorization Policies
├── Observers/          # Model Observers
├── Services/           # Serviços de lógica de negócio
├── Jobs/               # Jobs assíncronos
└── Console/
    └── Commands/       # Comandos Artisan customizados
```

## Funcionalidades Principais

### 1. Gestão de Assembleias
- Cadastro completo de assembleias
- Gestão de membros por assembleia
- Visualização de estatísticas
- Exportação de relatórios

### 2. Gestão de Membros
- Cadastro de meninas ativas
- Controle de maioridade (20 anos)
- Histórico completo de cargos
- Upload de fotos
- Geração de carteirinha digital

### 3. Sistema de Cargos
- **Cargos Administrativos**: Gestão da assembleia
- **Cargos das Meninas**: Cargos internos
- **Cargos da Grande Assembleia**: Cargos honoríficos anuais
- Histórico completo de todos os cargos ocupados

### 4. Sistema de Protocolos
- Iniciação, Transferência, Afastamento, Retorno, Maioridade, Desligamento
- Workflow completo com aprovações
- Upload de anexos
- Gestão de taxas e pagamentos
- Histórico de mudanças

### 5. Sistema de Tickets
- Suporte por comissões
- Sistema de SLA
- Thread de respostas
- Notas internas
- Avaliação de atendimento

### 6. Calendário
- Eventos por assembleia ou jurisdição
- Tipos de evento personalizáveis
- Sincronização de aniversários
- Exportação iCal/Google Calendar

### 7. Dashboards Personalizados
- **Dashboard Jurisdição**: Visão geral completa
- **Dashboard Assembleia**: Gestão local
- **Dashboard Membro**: Visualização pessoal

### 8. Relatórios
- Relatório Geral da Jurisdição
- Relatório por Assembleia
- Relatório de Protocolos
- Relatório de Tickets
- Relatório Financeiro
- Relatório de Cargos
- Exportação em PDF e Excel

## Roles e Permissões

### Roles Disponíveis:
- `membro_jurisdicao`: Acesso total ao sistema
- `presidente_comissao`: Gestão de tickets da comissão
- `admin_assembleia`: Gestão completa da assembleia
- `cargo_grande_assembleia`: Cargos honoríficos
- `menina_ativa`: Visualização limitada
- `menina_maioridade`: Acesso ao histórico

## Comandos Artisan Customizados

```bash
# Instalar sistema completo
php artisan iorg:install

# Criar usuário administrador
php artisan iorg:create-admin

# Sincronizar aniversários
php artisan iorg:sync-aniversarios

# Gerar relatório mensal
php artisan iorg:gerar-relatorio-mensal

# Verificar integridade do sistema
php artisan iorg:verificar-integridade

# Limpar cache completo
php artisan iorg:limpar-cache-completo

# Sincronizar cargos
php artisan iorg:sincronizar-cargos

# Encerrar cargos vencidos
php artisan iorg:encerrar-cargos-vencidos

# Relatório de cargos vagos
php artisan iorg:relatorio-cargos-vagos
```

## Jobs Agendados

O sistema possui jobs que rodam automaticamente:

- **Verificação de Maioridade**: Diário às 00:00
- **Lembrete de Aniversários**: Diário às 08:00
- **Verificação de Prazos de Protocolos**: A cada hora
- **Verificação de SLA de Tickets**: A cada 30 minutos
- **Atualização de Aniversários no Calendário**: Mensal
- **Fechamento Automático de Tickets Resolvidos**: Diário às 02:00
- **Limpeza de Arquivos Temporários**: Semanal
- **Backup do Banco de Dados**: Diário às 04:00
- **Verificação de Vencimento de Cargos**: Diário às 08:00

Configure o Cron no servidor:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Desenvolvimento

### Executar em modo de desenvolvimento
```bash
php artisan serve
npm run dev
```

### Executar testes
```bash
php artisan test
```

### Executar queue worker
```bash
php artisan queue:work
```

## Documentação

Para mais informações, consulte:
- [Documentação Técnica Completa](documentação.md)
- [Design Arquitetural](DESIGN.md)
- [Plano de Tarefas](TASKS.md)

## Tecnologias Utilizadas

- **Laravel**: Framework PHP
- **Filament**: Admin Panel
- **Livewire**: Componentes reativos
- **Alpine.js**: Framework JavaScript leve
- **Tailwind CSS**: Framework CSS
- **Spatie Permission**: Sistema de permissões
- **Laravel Sanctum**: Autenticação
- **DomPDF**: Geração de PDFs
- **Laravel Excel**: Exportação de planilhas

## Segurança

- Todas as senhas são criptografadas com bcrypt
- CSRF protection habilitado
- Validação de inputs
- Sanitização de outputs
- Autorização baseada em Policies
- Rate limiting configurado

## Performance

- Cache de queries com Redis
- Eager loading de relacionamentos
- Índices otimizados no banco de dados
- Jobs em fila para operações pesadas
- Assets compilados e minificados

## Suporte

Para reportar bugs ou solicitar funcionalidades, abra uma issue no repositório.

## Licença

[Especifique a licença do projeto]

## Autores

Sistema desenvolvido para a Jurisdição do Paraná da IORG.

---

**Versão**: 1.0.0
**Última atualização**: Outubro 2025
**Status do Projeto**: Em Desenvolvimento - Fase 1 Concluída
