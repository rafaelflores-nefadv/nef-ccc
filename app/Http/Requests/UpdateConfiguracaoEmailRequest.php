<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConfiguracaoEmailRequest extends FormRequest
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
            'driver' => ['required', 'string', Rule::in(['smtp', 'sendmail', 'log'])],
            'host' => ['required', 'string', 'max:255'],
            'porta' => ['required', 'integer', 'min:1', 'max:65535'],
            'usuario' => ['required', 'string', 'max:255'],
            'senha' => ['nullable', 'string', 'max:255'],
            'criptografia' => ['nullable', 'string', Rule::in(['tls', 'ssl'])],
            'email_remetente' => ['required', 'email', 'max:255'],
            'nome_remetente' => ['required', 'string', 'max:255'],
            'ativo' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'driver' => trim((string) $this->input('driver')),
            'host' => trim((string) $this->input('host')),
            'usuario' => trim((string) $this->input('usuario')),
            'senha' => $this->filled('senha') ? (string) $this->input('senha') : null,
            'criptografia' => $this->filled('criptografia') ? trim((string) $this->input('criptografia')) : null,
            'email_remetente' => mb_strtolower(trim((string) $this->input('email_remetente'))),
            'nome_remetente' => trim((string) $this->input('nome_remetente')),
            'ativo' => $this->has('ativo') ? $this->boolean('ativo') : false,
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'driver.required' => 'O driver de e-mail é obrigatório.',
            'driver.in' => 'Selecione um driver de e-mail válido.',
            'host.required' => 'O host de e-mail é obrigatório.',
            'porta.required' => 'A porta de e-mail é obrigatória.',
            'porta.integer' => 'A porta deve ser um número inteiro.',
            'usuario.required' => 'O usuário de e-mail é obrigatório.',
            'criptografia.in' => 'Selecione uma criptografia válida (TLS ou SSL).',
            'email_remetente.required' => 'O e-mail remetente é obrigatório.',
            'email_remetente.email' => 'O e-mail remetente deve ser válido.',
            'nome_remetente.required' => 'O nome remetente é obrigatório.',
            'ativo.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'driver' => 'driver de e-mail',
            'host' => 'host',
            'porta' => 'porta',
            'usuario' => 'usuário',
            'senha' => 'senha',
            'criptografia' => 'criptografia',
            'email_remetente' => 'e-mail remetente',
            'nome_remetente' => 'nome remetente',
            'ativo' => 'status',
        ];
    }
}
