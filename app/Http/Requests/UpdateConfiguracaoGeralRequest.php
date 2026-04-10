<?php

namespace App\Http\Requests;

use App\Support\ConfiguracaoSistema;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConfiguracaoGeralRequest extends FormRequest
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
            'nome_sistema' => ['required', 'string', 'max:255'],
            'timezone' => ['required', 'string', Rule::in(ConfiguracaoSistema::valoresTimezoneBrasil())],
            'email_suporte' => ['nullable', 'email', 'max:255'],
            'login_badge_text' => ['nullable', 'string', 'max:80'],
            'login_title' => ['nullable', 'string', 'max:120'],
            'login_description' => ['nullable', 'string', 'max:600'],
            'rodape_relatorio' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nome_sistema' => trim((string) $this->input('nome_sistema')),
            'timezone' => trim((string) $this->input('timezone')),
            'email_suporte' => $this->filled('email_suporte')
                ? mb_strtolower(trim((string) $this->input('email_suporte')))
                : null,
            'login_badge_text' => $this->filled('login_badge_text')
                ? trim((string) $this->input('login_badge_text'))
                : null,
            'login_title' => $this->filled('login_title')
                ? trim((string) $this->input('login_title'))
                : null,
            'login_description' => $this->filled('login_description')
                ? trim((string) $this->input('login_description'))
                : null,
            'rodape_relatorio' => $this->filled('rodape_relatorio')
                ? trim((string) $this->input('rodape_relatorio'))
                : null,
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome_sistema.required' => 'O nome do sistema é obrigatório.',
            'timezone.required' => 'O fuso horário é obrigatório.',
            'timezone.in' => 'Selecione um fuso horário brasileiro válido.',
            'email_suporte.email' => 'O e-mail de suporte deve ser válido.',
            'login_badge_text.max' => 'O texto do selo do login deve ter no máximo 80 caracteres.',
            'login_title.max' => 'O título do login deve ter no máximo 120 caracteres.',
            'login_description.max' => 'A descrição do login deve ter no máximo 600 caracteres.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nome_sistema' => 'nome do sistema',
            'timezone' => 'fuso horário',
            'email_suporte' => 'e-mail de suporte',
            'login_badge_text' => 'texto do selo do login',
            'login_title' => 'título do login',
            'login_description' => 'descrição do login',
            'rodape_relatorio' => 'rodapé do relatório',
        ];
    }
}
