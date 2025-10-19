<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePassword extends Page implements HasForms
{
    use InteractsWithForms;
    
    protected static ?string $navigationLabel = 'Alterar Senha';
    protected static ?string $title = 'Alterar Senha';
    protected static bool $shouldRegisterNavigation = false;
    
    protected string $view = 'filament.pages.change-password';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('current_password')
                    ->label('Senha Atual')
                    ->password()
                    ->required()
                    ->revealable()
                    ->validationMessages([
                        'required' => 'A senha atual é obrigatória.',
                    ]),

                TextInput::make('password')
                    ->label('Nova Senha')
                    ->password()
                    ->required()
                    ->revealable()
                    ->rules([
                        'required',
                        Password::min(8)
                            ->letters()
                            ->numbers()
                            ->symbols()
                            ->uncompromised(),
                    ])
                    ->validationMessages([
                        'required' => 'A nova senha é obrigatória.',
                        'min' => 'A senha deve ter pelo menos 8 caracteres.',
                    ]),

                TextInput::make('password_confirmation')
                    ->label('Confirmar Nova Senha')
                    ->password()
                    ->required()
                    ->revealable()
                    ->same('password')
                    ->validationMessages([
                        'required' => 'A confirmação da senha é obrigatória.',
                        'same' => 'A confirmação da senha deve ser igual à nova senha.',
                    ]),
            ])
            ->statePath('data');
    }

    public function updatePassword(): void
    {
        $data = $this->form->getState();
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Verify current password
        if (!Hash::check($data['current_password'], $user->password)) {
            Notification::make()
                ->danger()
                ->title('Erro')
                ->body('A senha atual está incorreta.')
                ->send();
            return;
        }

        // Update password
        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        // Mark that password has been changed
        $user->markPasswordChanged();

        // Clear form data
        $this->form->fill([]);

        Notification::make()
            ->success()
            ->title('Senha Alterada')
            ->body('Sua senha foi alterada com sucesso!')
            ->send();

        // Force redirect to dashboard with a small delay to ensure session is updated
        $this->redirect('/admin');
    }

    public function getHeading(): string
    {
        return 'Alterar Senha';
    }

    public function getSubheading(): ?string
    {
        return 'Por segurança, altere sua senha temporária para uma senha pessoal.';
    }
}