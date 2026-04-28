<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConfiguracaoNotificacaoRequest extends FormRequest
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
            'canal_email_ativo' => ['nullable', 'boolean'],
            'canal_whatsapp_ativo' => ['nullable', 'boolean'],
            'notificar_prazo_vencendo' => ['nullable', 'boolean'],
            'dias_antes_prazo' => ['required', 'integer', 'min:0'],
            'notificar_prazo_vencido' => ['nullable', 'boolean'],
            'notificar_leilao' => ['nullable', 'boolean'],
            'notificar_novo_andamento' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'dias_antes_prazo' => (int) $this->input('dias_antes_prazo', 0),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'dias_antes_prazo.required' => 'Informe a quantidade de dias antes do prazo.',
            'dias_antes_prazo.integer' => 'A quantidade de dias antes do prazo deve ser um número inteiro.',
            'dias_antes_prazo.min' => 'A quantidade de dias antes do prazo não pode ser negativa.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'canal_email_ativo' => 'canal de e-mail',
            'canal_whatsapp_ativo' => 'canal de WhatsApp',
            'notificar_prazo_vencendo' => 'notificação de prazo vencendo',
            'dias_antes_prazo' => 'dias antes do prazo',
            'notificar_prazo_vencido' => 'notificação de prazo vencido',
            'notificar_leilao' => 'notificação de leilão',
            'notificar_novo_andamento' => 'notificação de novo andamento',
        ];
    }
}
