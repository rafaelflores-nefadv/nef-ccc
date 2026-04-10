<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestarMensagemProvedorRequest extends FormRequest
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
            'numero' => ['required', 'digits_between:10,15'],
            'mensagem' => ['required', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $numero = preg_replace('/\D+/', '', (string) $this->input('numero'));

        $this->merge([
            'numero' => $numero,
            'mensagem' => trim((string) $this->input('mensagem')),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'numero.required' => 'O número de teste é obrigatório.',
            'numero.digits_between' => 'Informe um número válido com DDD e código do país (10 a 15 dígitos).',
            'mensagem.required' => 'A mensagem de teste é obrigatória.',
            'mensagem.max' => 'A mensagem de teste pode ter no máximo 1000 caracteres.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'numero' => 'número',
            'mensagem' => 'mensagem',
        ];
    }
}
