# Validação Dupla de Honrarias - Frontend e Backend

## Sistema de Validação Implementado

### 🎯 **Objetivo**

Impedir que um membro receba honrarias únicas (Coração das Cores e Grande Cruz das Cores) mais de uma vez, implementando validação tanto no frontend quanto no backend.

## 🔒 **Validação Frontend (Filament)**

### **Formulário Dinâmico**

O campo de seleção de honrarias agora:

1. **Remove opções já concedidas**:

    ```php
    ->options(function ($livewire) {
        $record = $livewire->getRecord();
        $allOptions = [
            'coracao_cores' => 'Coração das Cores',
            'grande_cruz_cores' => 'Grande Cruz das Cores',
            'homenageados_ano' => 'Homenageados do Ano',
        ];

        // Remove honrarias únicas já concedidas
        if ($record && $record->exists) {
            $honrariasExistentes = $record->honrarias()
                ->whereIn('tipo_honraria', ['coracao_cores', 'grande_cruz_cores'])
                ->pluck('tipo_honraria')->toArray();

            foreach ($honrariasExistentes as $honrariaExistente) {
                unset($allOptions[$honrariaExistente]);
            }
        }

        return $allOptions;
    })
    ```

2. **Mostra texto explicativo**:

    - Novo membro: "Coração das Cores e Grande Cruz das Cores só podem ser recebidas uma vez na vida."
    - Membro existente: "Honrarias únicas já recebidas: Coração das Cores"

3. **Validação de formulário**:
    ```php
    ->rules([
        function ($livewire) {
            return function (string $attribute, $value, \Closure $fail) use ($livewire) {
                // Valida se está tentando adicionar honraria já existente
                if (/* honraria única já existe */) {
                    $fail("O membro já possui a honraria '{$nomeHonraria}'. Esta honraria só pode ser recebida uma vez na vida.");
                }
            };
        }
    ])
    ```

## 🛡️ **Validação Backend (Modelo)**

### **Validação no Modelo HonrariaMembro**

```php
protected static function boot()
{
    parent::boot();

    static::saving(function ($honraria) {
        $honraria->validarRegrasHonrarias();
    });
}

private function validarRegrasHonrarias()
{
    // Para honrarias únicas
    if (in_array($this->tipo_honraria, ['coracao_cores', 'grande_cruz_cores'])) {
        $existente = static::where('membro_id', $this->membro_id)
            ->where('tipo_honraria', $this->tipo_honraria)
            ->where('id', '!=', $this->id ?? 0)
            ->first();

        if ($existente) {
            throw ValidationException::withMessages([
                'tipo_honraria' => "O membro já recebeu a honraria em {$existente->ano_recebimento}. Esta honraria só pode ser recebida uma vez na vida."
            ]);
        }
    }

    // Para Homenageados do Ano (uma vez por ano)
    if ($this->tipo_honraria === 'homenageados_ano') {
        // Validação específica para ano
    }
}
```

## 📋 **Comportamento do Sistema**

### **Cenário 1: Novo Membro**

-   ✅ Pode selecionar qualquer honraria
-   ✅ Todas as 3 opções disponíveis
-   ✅ Texto de ajuda explicativo

### **Cenário 2: Membro com Coração das Cores**

-   ❌ Opção "Coração das Cores" removida do select
-   ✅ Pode receber "Grande Cruz das Cores"
-   ✅ Pode receber "Homenageados do Ano" (múltiplas vezes)
-   ℹ️ Texto: "Honrarias únicas já recebidas: Coração das Cores"

### **Cenário 3: Membro com Ambas Honrarias Únicas**

-   ❌ "Coração das Cores" removido
-   ❌ "Grande Cruz das Cores" removido
-   ✅ Apenas "Homenageados do Ano" disponível
-   ℹ️ Texto: "Honrarias únicas já recebidas: Coração das Cores, Grande Cruz das Cores"

### **Cenário 4: Tentativa de Burlar Sistema**

-   🛡️ **Frontend**: Opção não aparece no select
-   🛡️ **Backend**: Validation Exception lançada
-   🛡️ **API/Direto**: Bloqueado no Observer do modelo

## ⚠️ **Mensagens de Erro**

### **Frontend**

-   "O membro já possui a honraria 'Coração das Cores'. Esta honraria só pode ser recebida uma vez na vida."

### **Backend**

-   "O membro já recebeu a honraria 'Coração das Cores' em 2023. Esta honraria só pode ser recebida uma vez na vida."

## 🔄 **Fluxo de Validação**

1. **Interface**: Usuário só vê opções permitidas
2. **Submit Frontend**: Validação de regras customizadas
3. **Salvamento**: Observer do modelo valida antes de persistir
4. **Banco de Dados**: Constraints de integridade (se aplicável)

## ✅ **Benefícios**

1. **UX Melhorada**: Usuário não vê opções inválidas
2. **Prevenção de Erros**: Feedback imediato e claro
3. **Segurança**: Validação dupla previne contornos
4. **Informação**: Textos explicativos educam o usuário
5. **Robustez**: Sistema à prova de tentativas de burla

## 🎯 **Status**

✅ **Implementado** - Validação dupla funcional com feedback claro ao usuário
