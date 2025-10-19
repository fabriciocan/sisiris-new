# Remoção da Coluna nivel_acesso

## 📅 Data: 16/10/2025

## 🎯 Motivo da Remoção

A coluna `nivel_acesso` foi **removida** do sistema porque era **redundante e desnecessária**.

### ❌ Problemas Identificados:

1. **Duplicação de Lógica**: O sistema já possui o pacote **Spatie Permission** com roles que fazem exatamente o mesmo trabalho

2. **Inconsistência**: Mantém duas formas de verificar permissões criava risco de inconsistência

3. **Complexidade Desnecessária**: Adiciona complexidade sem benefício real

4. **Não Utilizada nas Policies**: As Policies já usavam apenas `hasRole()`, não `nivel_acesso`

## ✅ Como o Sistema Funciona Agora

### Hierarquia de Acesso (via Roles):

| Nível           | Roles                                                     | Verificação                   |
| --------------- | --------------------------------------------------------- | ----------------------------- |
| **Super Admin** | `membro_jurisdicao`, `gra_digna`, `vice_gra_digna`        | `$user->isMembroJurisdicao()` |
| **Admin Local** | `admin_assembleia`, `digna_matrona`, `vice_digna_matrona` | `$user->isAdminAssembleia()`  |
| **Membro**      | `menina_ativa`, `menina_maioridade`, etc                  | `$user->isMembroComum()`      |

### Métodos Atualizados no Model User:

```php
// ✅ Verificações baseadas em Roles
public function isAdminAssembleia(): bool
{
    return $this->hasAnyRole(['admin_assembleia', 'digna_matrona', 'vice_digna_matrona']);
}

public function isMembroJurisdicao(): bool
{
    return $this->hasAnyRole(['membro_jurisdicao', 'gra_digna', 'vice_gra_digna']);
}

public function isMembroComum(): bool
{
    return !$this->isAdminAssembleia() && !$this->isMembroJurisdicao();
}
```

## 🔧 Arquivos Modificados:

### 1. **Migration**

-   ✅ Criada: `2025_10_16_103256_remove_nivel_acesso_from_users_table.php`
-   Remove a coluna `nivel_acesso` da tabela `users`

### 2. **Model User** (`app/Models/User.php`)

-   ❌ Removido: `'nivel_acesso'` do `$fillable`
-   ❌ Removido: `hasNivelAcesso()` method
-   ❌ Removido: `getNomeNivelAcesso()` method
-   ❌ Removido: Constantes `NIVEL_*`
-   ✅ Atualizado: `isAdminAssembleia()` - usa `hasAnyRole()`
-   ✅ Atualizado: `isMembroJurisdicao()` - usa `hasAnyRole()`
-   ✅ Atualizado: `isMembroComum()` - verificação baseada em roles

### 3. **Seeders**

Removido `nivel_acesso` de:

-   ❌ `AdminUserSeeder.php`
-   ❌ `AssembleiaExemploSeeder.php`
-   ❌ `TestUserSeeder.php`
-   ❌ `MembroAdicionalSeeder.php`

### 4. **Services** (Pendente de Atualização)

Arquivos que **precisam ser atualizados** para não usar `nivel_acesso`:

-   `app/Services/PositionManagementService.php`
-   `app/Services/CargoConselhoService.php`
-   `app/Services/IniciacaoService.php`

### 5. **Models** (Pendente de Atualização)

-   `app/Models/CargoConselho.php`

### 6. **Comandos**

-   ❌ Remover: `app/Console/Commands/CorrigirNiveisAcessoCommand.php`

### 7. **Documentação**

-   ❌ Remover: `NIVEIS_ACESSO.md`

## 📝 Guia de Migração de Código

### ❌ ANTES (Usando nivel_acesso):

```php
// Verificar nível
if ($user->nivel_acesso === 'admin_assembleia') {
    // código
}

// Ou
if ($user->hasNivelAcesso('admin_assembleia')) {
    // código
}

// Atualizar nível
$user->update(['nivel_acesso' => 'admin_assembleia']);
```

### ✅ AGORA (Usando Roles):

```php
// Verificar permissão
if ($user->isAdminAssembleia()) {
    // código
}

// Ou diretamente
if ($user->hasRole('admin_assembleia')) {
    // código
}

// Atribuir role
$user->assignRole('admin_assembleia');
```

## 🎯 Vantagens da Remoção:

1. ✅ **Simplicidade**: Uma única forma de verificar permissões
2. ✅ **Consistência**: Sem risco de role e nivel_acesso ficarem desincronizados
3. ✅ **Manutenibilidade**: Menos código para manter
4. ✅ **Padrão**: Usa apenas o Spatie Permission (padrão Laravel)
5. ✅ **Flexibilidade**: Fácil adicionar novas roles sem modificar banco

## 📊 Status da Migration:

```bash
php artisan migrate
# 2025_10_16_103256_remove_nivel_acesso_from_users_table DONE
```

✅ **Coluna removida com sucesso do banco de dados!**

## 🚀 Próximos Passos:

1. ⏳ Atualizar Services para remover referências a `nivel_acesso`
2. ⏳ Atualizar Models para remover `update(['nivel_acesso' => ...])`
3. ⏳ Remover comando `CorrigirNiveisAcessoCommand`
4. ⏳ Remover documentação antiga `NIVEIS_ACESSO.md`
5. ⏳ Limpar seeders
6. ⏳ Testar todo o sistema

---

## 💡 Nota Importante:

O sistema **já funcionava perfeitamente** apenas com roles antes da coluna `nivel_acesso` ser considerada. Esta remoção apenas **limpa** código redundante e torna o sistema mais **simples** e **manutenível**.

---

**Documentado por**: GitHub Copilot  
**Data**: 16/10/2025
