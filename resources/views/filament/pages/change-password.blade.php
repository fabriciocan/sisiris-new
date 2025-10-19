<x-filament-panels::page>
    <form wire:submit="updatePassword">
        {{ $this->form }}

        <div class="mt-6 flex gap-3">
            <x-filament::button type="submit">
                Alterar Senha
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
