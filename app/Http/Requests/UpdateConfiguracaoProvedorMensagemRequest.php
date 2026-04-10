<?php

namespace App\Http\Requests;

use App\Models\ProvedorMensagem;
use App\Support\ConfiguracaoSistema;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConfiguracaoProvedorMensagemRequest extends FormRequest
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
            'provedor_id' => [
                'required',
                'integer',
                Rule::exists('provedores_mensagem', 'id')
                    ->where(function ($query) {
                        $query->where('tipo', 'whatsapp')
                            ->whereIn('slug', ConfiguracaoSistema::slugsProvedoresWhatsappSuportados());
                    }),
            ],

            'meta_url_base' => [
                Rule::requiredIf(fn (): bool => $this->provedorSlugSelecionado() === 'meta'),
                'nullable',
                'url',
                'max:255',
            ],
            'meta_token' => [
                Rule::requiredIf(fn (): bool => $this->provedorSlugSelecionado() === 'meta'),
                'nullable',
                'string',
            ],
            'meta_phone_number_id' => [
                Rule::requiredIf(fn (): bool => $this->provedorSlugSelecionado() === 'meta'),
                'nullable',
                'string',
                'max:120',
            ],
            'meta_business_account_id' => ['nullable', 'string', 'max:120'],
            'meta_api_version' => ['nullable', 'string', 'max:20', 'regex:/^v\\d+\\.\\d+$/'],

            'waha_url_base' => [
                Rule::requiredIf(fn (): bool => $this->provedorSlugSelecionado() === 'waha'),
                'nullable',
                'url',
                'max:255',
            ],
            'waha_token' => ['nullable', 'string'],
            'waha_instancia' => [
                Rule::requiredIf(fn (): bool => $this->provedorSlugSelecionado() === 'waha'),
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'meta_url_base' => $this->filled('meta_url_base')
                ? trim((string) $this->input('meta_url_base'))
                : 'https://graph.facebook.com',
            'meta_token' => $this->filled('meta_token')
                ? trim((string) $this->input('meta_token'))
                : null,
            'meta_phone_number_id' => $this->filled('meta_phone_number_id')
                ? trim((string) $this->input('meta_phone_number_id'))
                : null,
            'meta_business_account_id' => $this->filled('meta_business_account_id')
                ? trim((string) $this->input('meta_business_account_id'))
                : null,
            'meta_api_version' => $this->filled('meta_api_version')
                ? trim((string) $this->input('meta_api_version'))
                : 'v20.0',

            'waha_url_base' => $this->filled('waha_url_base')
                ? trim((string) $this->input('waha_url_base'))
                : null,
            'waha_token' => $this->filled('waha_token')
                ? trim((string) $this->input('waha_token'))
                : null,
            'waha_instancia' => $this->filled('waha_instancia')
                ? trim((string) $this->input('waha_instancia'))
                : null,
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'provedor_id.required' => 'Selecione um provedor de WhatsApp.',
            'provedor_id.exists' => 'Selecione um provedor suportado (Meta ou WAHA).',

            'meta_url_base.required' => 'A URL base da Meta é obrigatória.',
            'meta_url_base.url' => 'A URL base da Meta deve ser válida.',
            'meta_token.required' => 'O token da Meta é obrigatório.',
            'meta_phone_number_id.required' => 'O Phone Number ID da Meta é obrigatório.',
            'meta_api_version.regex' => 'A versão da API da Meta deve seguir o formato v00.0.',

            'waha_url_base.required' => 'A URL base do WAHA é obrigatória.',
            'waha_url_base.url' => 'A URL base do WAHA deve ser válida.',
            'waha_instancia.required' => 'A instância/sessão do WAHA é obrigatória.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'provedor_id' => 'provedor',
            'meta_url_base' => 'URL base da Meta',
            'meta_token' => 'token da Meta',
            'meta_phone_number_id' => 'Phone Number ID da Meta',
            'meta_business_account_id' => 'Business Account ID da Meta',
            'meta_api_version' => 'versão da API da Meta',
            'waha_url_base' => 'URL base do WAHA',
            'waha_token' => 'token do WAHA',
            'waha_instancia' => 'instância do WAHA',
        ];
    }

    public function provedorSlugSelecionado(): ?string
    {
        $provedorId = (int) $this->input('provedor_id');

        if ($provedorId <= 0) {
            return null;
        }

        return ProvedorMensagem::query()
            ->whereKey($provedorId)
            ->where('tipo', 'whatsapp')
            ->whereIn('slug', ConfiguracaoSistema::slugsProvedoresWhatsappSuportados())
            ->value('slug');
    }
}
