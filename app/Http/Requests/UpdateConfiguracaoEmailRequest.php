<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'host' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn (): bool => strtolower((string) $this->input('driver')) === 'smtp'),
            ],
            'porta' => [
                'nullable',
                'integer',
                'min:1',
                'max:65535',
                Rule::requiredIf(fn (): bool => strtolower((string) $this->input('driver')) === 'smtp'),
            ],
            'usuario' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn (): bool => strtolower((string) $this->input('driver')) === 'smtp'),
            ],
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $driver = strtolower((string) $this->input('driver', 'smtp'));

            if ($driver !== 'smtp') {
                return;
            }

            $host = mb_strtolower(trim((string) $this->input('host')));

            if ($host !== 'smtp.titan.email') {
                return;
            }

            $usuario = mb_strtolower(trim((string) $this->input('usuario')));
            $emailRemetente = mb_strtolower(trim((string) $this->input('email_remetente')));
            $porta = (int) $this->input('porta');
            $criptografia = mb_strtolower(trim((string) $this->input('criptografia')));

            if ($usuario !== '' && $emailRemetente !== '' && $usuario !== $emailRemetente) {
                $validator->errors()->add(
                    'email_remetente',
                    'No Titan, o e-mail remetente deve ser igual ao usuario SMTP autenticado.'
                );
            }

            if ($porta === 587 && $criptografia !== 'tls') {
                $validator->errors()->add(
                    'criptografia',
                    'No Titan, a porta 587 exige criptografia TLS.'
                );
            }

            if ($porta === 465 && $criptografia !== 'ssl') {
                $validator->errors()->add(
                    'criptografia',
                    'No Titan, a porta 465 exige criptografia SSL.'
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'driver.required' => 'O driver de e-mail e obrigatorio.',
            'driver.in' => 'Selecione um driver de e-mail valido.',
            'host.required' => 'O host de e-mail e obrigatorio.',
            'host.required_if' => 'O host de e-mail e obrigatorio para SMTP.',
            'porta.required' => 'A porta de e-mail e obrigatoria.',
            'porta.required_if' => 'A porta de e-mail e obrigatoria para SMTP.',
            'porta.integer' => 'A porta deve ser um numero inteiro.',
            'usuario.required' => 'O usuario de e-mail e obrigatorio.',
            'usuario.required_if' => 'O usuario de e-mail e obrigatorio para SMTP.',
            'criptografia.in' => 'Selecione uma criptografia valida (TLS ou SSL).',
            'email_remetente.required' => 'O e-mail remetente e obrigatorio.',
            'email_remetente.email' => 'O e-mail remetente deve ser valido.',
            'nome_remetente.required' => 'O nome remetente e obrigatorio.',
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
            'usuario' => 'usuario',
            'senha' => 'senha',
            'criptografia' => 'criptografia',
            'email_remetente' => 'e-mail remetente',
            'nome_remetente' => 'nome remetente',
            'ativo' => 'status',
        ];
    }
}

