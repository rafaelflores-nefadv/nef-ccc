<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
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
            'cooperativa_id' => [
                'nullable',
                'integer',
                Rule::requiredIf(fn (): bool => (string) $this->input('perfil') !== User::PERFIL_ADMIN),
                Rule::exists('cooperativas', 'id'),
            ],
            'papel_id' => [
                'nullable',
                'integer',
                Rule::requiredIf(fn (): bool => (string) $this->input('perfil') !== User::PERFIL_ADMIN),
                Rule::exists('papeis', 'id'),
            ],
            'password' => ['required', 'string', 'min:6'],
            'ativo' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $perfil = (string) $this->input('perfil');

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'ativo' => $this->has('ativo') ? $this->boolean('ativo') : true,
            'cooperativa_id' => $perfil === User::PERFIL_ADMIN
                ? null
                : ($this->filled('cooperativa_id') ? (int) $this->input('cooperativa_id') : null),
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
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Já existe um usuário com este e-mail.',
            'perfil.required' => 'O perfil é obrigatório.',
            'perfil.in' => 'O perfil selecionado é inválido.',
            'cooperativa_id.required' => 'A cooperativa é obrigatória para este perfil.',
            'cooperativa_id.exists' => 'A cooperativa selecionada é inválida.',
            'papel_id.required' => 'O papel é obrigatório para este perfil.',
            'papel_id.exists' => 'O papel selecionado é inválido.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
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
            'cooperativa_id' => 'cooperativa',
            'papel_id' => 'papel',
            'password' => 'senha',
            'ativo' => 'status',
        ];
    }
}
