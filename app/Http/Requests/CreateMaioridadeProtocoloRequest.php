<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Membro;
use App\Models\TipoUsuario;

class CreateMaioridadeProtocoloRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        // Only admin_assembleia can create maioridade protocols
        return $user && $user->hasRole('admin_assembleia');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'numero_protocolo' => 'required|string|max:50|unique:protocolos,numero_protocolo',
            'assembleia_id' => 'required|exists:assembleias,id',
            'tipo_protocolo' => 'required|in:maioridade',
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string|max:1000',
            'membros_selecionados' => 'required|array|min:1',
            'membros_selecionados.*' => 'exists:membros,id',
            'prioridade' => 'required|in:baixa,normal,alta,urgente',
            'data_solicitacao' => 'required|date',
            'observacoes_membros' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'assembleia_id.required' => 'A assembleia é obrigatória.',
            'assembleia_id.exists' => 'A assembleia selecionada não existe.',
            'titulo.required' => 'O título é obrigatório.',
            'descricao.required' => 'A descrição é obrigatória.',
            'membros_selecionados.required' => 'É necessário selecionar pelo menos uma menina ativa.',
            'membros_selecionados.min' => 'É necessário selecionar pelo menos uma menina ativa.',
            'membros_selecionados.*.exists' => 'Um ou mais membros selecionados não existem.',
            'prioridade.required' => 'A prioridade é obrigatória.',
            'prioridade.in' => 'A prioridade deve ser: baixa, normal, alta ou urgente.',
            'data_solicitacao.required' => 'A data de solicitação é obrigatória.',
            'data_solicitacao.date' => 'A data de solicitação deve ser uma data válida.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateMemberEligibility($validator);
            $this->validateUserPermissions($validator);
        });
    }

    /**
     * Validate that selected members are eligible for maioridade ceremony.
     */
    protected function validateMemberEligibility($validator): void
    {
        $assembleiaId = $this->input('assembleia_id');
        $membrosIds = $this->input('membros_selecionados', []);

        if (empty($membrosIds) || !$assembleiaId) {
            return;
        }

        // Get all selected members
        $membros = Membro::whereIn('id', $membrosIds)->get();

        foreach ($membros as $membro) {
            // Check if member is from the correct assembleia
            if ($membro->assembleia_id != $assembleiaId) {
                $validator->errors()->add(
                    'membros_selecionados',
                    "A menina {$membro->nome_completo} não pertence à assembleia selecionada."
                );
                continue;
            }

            // Check if member is menina ativa
            if (!$membro->isMeninaAtiva()) {
                $validator->errors()->add(
                    'membros_selecionados',
                    "A menina {$membro->nome_completo} não é do tipo 'Menina Ativa'."
                );
                continue;
            }

            // Check if member is active
            if ($membro->status !== 'ativa') {
                $validator->errors()->add(
                    'membros_selecionados',
                    "A menina {$membro->nome_completo} não está com status ativo."
                );
                continue;
            }

            // Check if member already has maioridade status
            if ($membro->data_maioridade) {
                $validator->errors()->add(
                    'membros_selecionados',
                    "A menina {$membro->nome_completo} já possui data de maioridade registrada."
                );
                continue;
            }
        }

        // Verify all selected members exist and are valid
        $validMembersCount = Membro::meninasAtivas()
            ->ativas()
            ->where('assembleia_id', $assembleiaId)
            ->whereIn('id', $membrosIds)
            ->whereNull('data_maioridade')
            ->count();

        if ($validMembersCount !== count($membrosIds)) {
            $validator->errors()->add(
                'membros_selecionados',
                'Alguns membros selecionados não são elegíveis para a cerimônia de maioridade.'
            );
        }
    }

    /**
     * Validate user permissions for the selected assembleia.
     */
    protected function validateUserPermissions($validator): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            $validator->errors()->add('assembleia_id', 'Usuário não autenticado.');
            return;
        }

        $assembleiaId = $this->input('assembleia_id');

        // If user is admin_assembleia, they can only create protocols for their assembleia
        if ($user->hasRole('admin_assembleia')) {
            if (!$user->membro || $user->membro->assembleia_id != $assembleiaId) {
                $validator->errors()->add(
                    'assembleia_id',
                    'Você só pode criar protocolos para sua própria assembleia.'
                );
            }
        }
    }

    /**
     * Get validated data with additional processing.
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        
        // Add default values for workflow
        if (is_null($key)) {
            $data['status'] = 'pendente';
            $data['etapa_atual'] = 'aguardando_aprovacao';
            $data['solicitante_id'] = Auth::id();
        }
        
        return $data;
    }
}