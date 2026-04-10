<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePadraoConfiguracaoProvedorMensagemRequest extends FormRequest
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
            'padrao' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('padrao')) {
            return;
        }

        $this->merge([
            'padrao' => $this->boolean('padrao'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'padrao.required' => 'Informe se a configuração será padrão.',
            'padrao.boolean' => 'O campo configuração padrão deve ser verdadeiro ou falso.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'padrao' => 'configuração padrão',
        ];
    }
}
