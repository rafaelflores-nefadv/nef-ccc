<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'perfil' => ['required', 'string', Rule::in(User::PERFIS)],
            'cooperativas' => [
                'nullable',
                'array',
                Rule::requiredIf(fn (): bool => (string) $this->input('perfil') !== User::PERFIL_ADMIN),
            ],
            'cooperativas.*' => [
                'integer',
                Rule::exists('cooperativas', 'id'),
                'distinct',
            ],
            'papel_id' => [
                'nullable',
                'integer',
                Rule::requiredIf(fn (): bool => (string) $this->input('perfil') !== User::PERFIL_ADMIN),
                Rule::exists('papeis', 'id'),
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'password_confirmation' => ['required', 'string'],
            'ativo' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $perfil = (string) $this->input('perfil');
        $cooperativas = collect((array) $this->input('cooperativas', []));

        if ($cooperativas->isEmpty() && $this->filled('cooperativa_id')) {
            $cooperativas->push($this->input('cooperativa_id'));
        }

        $cooperativasIds = $cooperativas
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($perfil === User::PERFIL_ADMIN) {
            $cooperativasIds = [];
        }

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'ativo' => $this->has('ativo') ? $this->boolean('ativo') : true,
            'cooperativas' => $cooperativasIds,
            'papel_id' => $perfil === User::PERFIL_ADMIN
                ? null
                : ($this->filled('papel_id') ? (int) $this->input('papel_id') : null),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome e obrigatorio.',
            'email.required' => 'O e-mail e obrigatorio.',
            'email.email' => 'Informe um e-mail valido.',
            'email.unique' => 'Ja existe um usuario com este e-mail.',
            'perfil.required' => 'O perfil e obrigatorio.',
            'perfil.in' => 'O perfil selecionado e invalido.',
            'cooperativas.required' => 'Selecione ao menos uma cooperativa para este perfil.',
            'cooperativas.array' => 'As cooperativas devem ser informadas em lista.',
            'cooperativas.*.exists' => 'Uma das cooperativas selecionadas e invalida.',
            'cooperativas.*.distinct' => 'Nao repita cooperativas na selecao.',
            'papel_id.required' => 'O papel e obrigatorio para este perfil.',
            'papel_id.exists' => 'O papel selecionado e invalido.',
            'password.required' => 'A senha e obrigatoria.',
            'password.confirmed' => 'A confirmacao de senha nao confere.',
            'password.min' => 'A senha deve ter no minimo 8 caracteres.',
            'password.letters' => 'A senha deve conter ao menos uma letra.',
            'password.mixed' => 'A senha deve conter ao menos uma letra maiuscula e uma minuscula.',
            'password.numbers' => 'A senha deve conter ao menos um numero.',
            'password.symbols' => 'A senha deve conter ao menos um caractere especial.',
            'password_confirmation.required' => 'A confirmacao de senha e obrigatoria.',
            'ativo.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'perfil' => 'perfil',
            'cooperativas' => 'cooperativas',
            'papel_id' => 'papel',
            'password' => 'senha',
            'password_confirmation' => 'confirmacao de senha',
            'ativo' => 'status',
        ];
    }
}
