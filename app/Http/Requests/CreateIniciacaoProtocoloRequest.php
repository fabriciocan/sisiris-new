<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Membro;
use App\Models\TipoUsuario;
use Carbon\Carbon;

class CreateIniciacaoProtocoloRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        // Only admin_assembleia can create iniciacao protocols
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
            'tipo_protocolo' => 'required|in:iniciacao',
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string|max:1000',
            'prioridade' => 'required|in:baixa,normal,alta,urgente',
            'data_solicitacao' => 'required|date',
            'observacoes_gerais' => 'nullable|string|max:1000',
            
            // Validação das novas meninas
            'novas_meninas' => 'required|array|min:1|max:20',
            'novas_meninas.*.nome_completo' => 'required|string|max:255',
            'novas_meninas.*.data_nascimento' => 'required|date|before:today|after:' . now()->subYears(50)->toDateString(),
            'novas_meninas.*.cpf' => 'required|string|size:14|unique:membros,cpf',
            'novas_meninas.*.telefone' => 'required|string|max:20',
            'novas_meninas.*.email' => 'required|email|max:255|unique:membros,email',
            'novas_meninas.*.endereco_completo' => 'required|string|max:500',
            'novas_meninas.*.data_iniciacao' => 'required|date|before_or_equal:today',
            'novas_meninas.*.nome_mae' => 'required|string|max:255',
            'novas_meninas.*.telefone_mae' => 'required|string|max:20',
            'novas_meninas.*.nome_pai' => 'nullable|string|max:255',
            'novas_meninas.*.telefone_pai' => 'nullable|string|max:20',
            'novas_meninas.*.responsavel_legal' => 'nullable|string|max:255',
            'novas_meninas.*.contato_responsavel' => 'nullable|string|max:20',
            'novas_meninas.*.madrinha_id' => 'required|exists:membros,id',
            'novas_meninas.*.observacoes' => 'nullable|string|max:500',
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
            'novas_meninas.required' => 'É necessário cadastrar pelo menos uma nova menina.',
            'novas_meninas.min' => 'É necessário cadastrar pelo menos uma nova menina.',
            'novas_meninas.max' => 'Máximo de 20 meninas por protocolo.',
            
            // Mensagens para campos das meninas
            'novas_meninas.*.nome_completo.required' => 'O nome completo é obrigatório.',
            'novas_meninas.*.data_nascimento.required' => 'A data de nascimento é obrigatória.',
            'novas_meninas.*.data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
            'novas_meninas.*.data_nascimento.after' => 'A menina deve ter menos de 50 anos.',
            'novas_meninas.*.cpf.required' => 'O CPF é obrigatório.',
            'novas_meninas.*.cpf.size' => 'O CPF deve ter 11 dígitos.',
            'novas_meninas.*.cpf.unique' => 'Este CPF já está cadastrado no sistema.',
            'novas_meninas.*.telefone.required' => 'O telefone é obrigatório.',
            'novas_meninas.*.email.required' => 'O e-mail é obrigatório.',
            'novas_meninas.*.email.email' => 'O e-mail deve ter um formato válido.',
            'novas_meninas.*.email.unique' => 'Este e-mail já está cadastrado no sistema.',
            'novas_meninas.*.endereco_completo.required' => 'O endereço completo é obrigatório.',
            'novas_meninas.*.data_iniciacao.required' => 'A data de iniciação é obrigatória.',
            'novas_meninas.*.data_iniciacao.before_or_equal' => 'A data de iniciação não pode ser futura.',
            'novas_meninas.*.nome_mae.required' => 'O nome da mãe é obrigatório.',
            'novas_meninas.*.telefone_mae.required' => 'O telefone da mãe é obrigatório.',
            'novas_meninas.*.madrinha_id.required' => 'A madrinha é obrigatória.',
            'novas_meninas.*.madrinha_id.exists' => 'A madrinha selecionada não existe.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateMadrinhaEligibility($validator);
            $this->validateUserPermissions($validator);
            $this->validateUniqueDataWithinRequest($validator);
            $this->validateMinimumAge($validator);
        });
    }

    /**
     * Validate that selected madrinhas are eligible.
     */
    protected function validateMadrinhaEligibility($validator): void
    {
        $assembleiaId = $this->input('assembleia_id');
        $novasMeninas = $this->input('novas_meninas', []);

        if (empty($novasMeninas) || !$assembleiaId) {
            return;
        }

        foreach ($novasMeninas as $index => $menina) {
            $madrinhaId = $menina['madrinha_id'] ?? null;
            
            if (!$madrinhaId) {
                continue;
            }

            $madrinha = Membro::find($madrinhaId);
            
            if (!$madrinha) {
                continue;
            }

            // Check if madrinha is from the correct assembleia
            if ($madrinha->assembleia_id != $assembleiaId) {
                $validator->errors()->add(
                    "novas_meninas.{$index}.madrinha_id",
                    "A madrinha {$madrinha->nome_completo} não pertence à assembleia selecionada."
                );
                continue;
            }

            // Check if madrinha is active
            if (!in_array($madrinha->status, ['ativa', 'maioridade'])) {
                $validator->errors()->add(
                    "novas_meninas.{$index}.madrinha_id",
                    "A madrinha {$madrinha->nome_completo} não está com status ativo."
                );
                continue;
            }
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
     * Validate that CPF and email are unique within the request.
     */
    protected function validateUniqueDataWithinRequest($validator): void
    {
        $novasMeninas = $this->input('novas_meninas', []);
        
        $cpfs = [];
        $emails = [];
        
        foreach ($novasMeninas as $index => $menina) {
            $cpf = $menina['cpf'] ?? null;
            $email = $menina['email'] ?? null;
            
            // Check CPF uniqueness within request
            if ($cpf) {
                $cpfClean = preg_replace('/[^0-9]/', '', $cpf);
                if (in_array($cpfClean, $cpfs)) {
                    $validator->errors()->add(
                        "novas_meninas.{$index}.cpf",
                        'Este CPF já foi usado para outra menina neste protocolo.'
                    );
                } else {
                    $cpfs[] = $cpfClean;
                }
            }
            
            // Check email uniqueness within request
            if ($email) {
                if (in_array(strtolower($email), $emails)) {
                    $validator->errors()->add(
                        "novas_meninas.{$index}.email",
                        'Este e-mail já foi usado para outra menina neste protocolo.'
                    );
                } else {
                    $emails[] = strtolower($email);
                }
            }
        }
    }

    /**
     * Validate minimum age requirements.
     */
    protected function validateMinimumAge($validator): void
    {
        $novasMeninas = $this->input('novas_meninas', []);
        
        foreach ($novasMeninas as $index => $menina) {
            $dataNascimento = $menina['data_nascimento'] ?? null;
            
            if (!$dataNascimento) {
                continue;
            }
            
            try {
                $idade = Carbon::parse($dataNascimento)->age;
                
                if ($idade < 10) {
                    $validator->errors()->add(
                        "novas_meninas.{$index}.data_nascimento",
                        'A menina deve ter pelo menos 10 anos de idade.'
                    );
                }
                
                if ($idade > 50) {
                    $validator->errors()->add(
                        "novas_meninas.{$index}.data_nascimento",
                        'A idade máxima para iniciação é 50 anos.'
                    );
                }
            } catch (\Exception $e) {
                // Data inválida, será capturada pela validação básica
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
            
            // Clean CPF fields
            if (isset($data['novas_meninas'])) {
                foreach ($data['novas_meninas'] as &$menina) {
                    if (isset($menina['cpf'])) {
                        $menina['cpf'] = preg_replace('/[^0-9]/', '', $menina['cpf']);
                    }
                }
            }
        }
        
        return $data;
    }
}