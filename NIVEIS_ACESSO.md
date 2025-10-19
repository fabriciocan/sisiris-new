# Níveis de Acesso do Sistema

## 📋 Visão Geral

O sistema utiliza a coluna `nivel_acesso` na tabela `users` para controlar permissões e acessos de forma hierárquica, funcionando em conjunto com o sistema de **roles** (Spatie Permission).

## 🎯 Diferença entre Roles e Níveis de Acesso

### **Roles** (Funções)

-   Define o **tipo de usuário** e suas **permissões específicas**
-   Exemplos: `menina_ativa`, `digna_matrona`, `gra_digna`, `conselheira_fiscal`
-   Controlado pelo pacote **Spatie Permission**

### **Níveis de Acesso** (Hierarquia)

-   Define o **nível hierárquico** do usuário no sistema
-   Usado para **controle de acesso administrativo**
-   3 níveis: `membro_jurisdicao`, `admin_assembleia`, `membro`

---

## 🔐 Os 3 Níveis de Acesso

### 1. `membro_jurisdicao` (Nível Jurisdição)

**Maior nível de acesso - Administradores do Sistema**

**Roles que recebem este nível:**

-   `membro_jurisdicao`
-   `gra_digna`
-   `vice_gra_digna`

**Permissões:**

-   Acesso completo a todas as assembleias
-   Gerenciar protocolos de toda a jurisdição
-   Aprovar honrarias e protocolos especiais
-   Visualizar relatórios globais
-   Gerenciar usuários de qualquer assembleia

**Exemplo:**

```php
$admin = User::create([
    'name' => 'Administrador IORG',
    'email' => 'admin@iorgpr.org.br',
    'nivel_acesso' => 'membro_jurisdicao',
]);
$admin->assignRole('membro_jurisdicao');
```

---

### 2. `admin_assembleia` (Nível Assembleia)

**Nível intermediário - Administradores de Assembleia**

**Roles que recebem este nível:**

-   `admin_assembleia`
-   `digna_matrona`
-   `vice_digna_matrona`

**Permissões:**

-   Gerenciar apenas sua assembleia
-   Aprovar protocolos locais
-   Criar e gerenciar eventos
-   Gerenciar membros da sua assembleia
-   Visualizar relatórios da assembleia

**Exemplo:**

```php
$dignaMatrona = User::create([
    'name' => 'Maria Silva',
    'email' => 'maria@assembleia.com',
    'nivel_acesso' => 'admin_assembleia',
]);
$dignaMatrona->assignRole('digna_matrona');
```

---

### 3. `membro` (Nível Membro)

**Nível básico - Membros regulares**

**Roles que recebem este nível:**

-   `menina_ativa`
-   `menina_maioridade`
-   `menina_afastada`
-   `tio_macom`
-   `tia_estrela`
-   `conselheira_fiscal`
-   E outras roles de membros

**Permissões:**

-   Visualizar informações da sua assembleia
-   Criar protocolos pessoais
-   Visualizar eventos
-   Editar seu próprio perfil

**Exemplo:**

```php
$menina = User::create([
    'name' => 'Ana Santos',
    'email' => 'ana@assembleia.com',
    'nivel_acesso' => 'membro',
]);
$menina->assignRole('menina_ativa');
```

---

## 🔄 Atualização Automática

O sistema atualiza automaticamente o `nivel_acesso` quando:

1. **Um cargo é atribuído:**

    - Digna Matrona → `admin_assembleia`
    - Vice Digna Matrona → `admin_assembleia`

2. **Um cargo é removido:**

    - Remove cargo administrativo → volta para `membro`

3. **Através do serviço:**

```php
use App\Services\PositionManagementService;

$service = app(PositionManagementService::class);
$service->sincronizarNivelAcesso($user);
```

---

## 🛠️ Comandos Artisan

### Corrigir níveis de acesso

```bash
php artisan users:corrigir-niveis-acesso
```

Este comando:

-   Verifica todos os usuários
-   Ajusta o `nivel_acesso` baseado nas roles atuais
-   Exibe estatísticas

---

## 📊 Como Verificar o Nível de Acesso

### No código:

```php
// Verificar nível específico
if ($user->hasNivelAcesso('admin_assembleia')) {
    // Usuário é admin de assembleia
}

// Obter descrição do nível
$descricao = $user->getNivelAcessoDescricao();
// Retorna: 'Administrador de Assembleia'

// Verificar se pode acessar outra assembleia
if ($user->podeAcessarAssembleia($assembleia)) {
    // Pode acessar
}
```

### No banco de dados:

```sql
SELECT
    name,
    email,
    nivel_acesso,
    (SELECT GROUP_CONCAT(name) FROM roles
     INNER JOIN model_has_roles ON roles.id = model_has_roles.role_id
     WHERE model_has_roles.model_id = users.id) as roles
FROM users;
```

---

## 🎭 Matriz de Permissões vs Níveis

| Funcionalidade               | membro | admin_assembleia | membro_jurisdicao |
| ---------------------------- | ------ | ---------------- | ----------------- |
| Ver próprios dados           | ✅     | ✅               | ✅                |
| Criar protocolo pessoal      | ✅     | ✅               | ✅                |
| Aprovar protocolos locais    | ❌     | ✅               | ✅                |
| Gerenciar membros assembleia | ❌     | ✅               | ✅                |
| Aprovar honrarias            | ❌     | ❌               | ✅                |
| Acessar outras assembleias   | ❌     | ❌               | ✅                |
| Relatórios globais           | ❌     | ❌               | ✅                |

---

## 🔍 Troubleshooting

### Problema: Usuário com nível errado

**Solução 1 - Corrigir manualmente:**

```php
$user->update(['nivel_acesso' => 'admin_assembleia']);
```

**Solução 2 - Corrigir todos:**

```bash
php artisan users:corrigir-niveis-acesso
```

### Problema: Seeders criando usuários com nível errado

**Sempre incluir `nivel_acesso` nos seeders:**

```php
User::create([
    'name' => 'Nome',
    'email' => 'email@exemplo.com',
    'nivel_acesso' => 'admin_assembleia', // ← IMPORTANTE
]);
```

---

## 📝 Boas Práticas

1. **Sempre definir `nivel_acesso` ao criar usuário**
2. **Não modificar `nivel_acesso` manualmente** (usar serviços)
3. **Verificar nível antes de operações sensíveis**
4. **Registrar mudanças de nível** (já feito automaticamente)
5. **Executar comando de correção após seeders**

---

## 🚀 Implementação nos Seeders

### ✅ Correto

```php
$user = User::create([
    'name' => 'Maria Silva',
    'email' => 'maria@exemplo.com',
    'nivel_acesso' => 'admin_assembleia', // ← Define o nível
]);
$user->assignRole('digna_matrona');
```

### ❌ Incorreto

```php
$user = User::create([
    'name' => 'Maria Silva',
    'email' => 'maria@exemplo.com',
    // Faltou nivel_acesso - ficará como 'membro' por padrão
]);
$user->assignRole('digna_matrona');
```

---

## 📅 Data de criação: 16/10/2025

## 👤 Documentado por: GitHub Copilot
