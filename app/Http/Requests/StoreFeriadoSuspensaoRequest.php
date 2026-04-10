<?php

namespace App\Http\Requests;

use App\Models\FeriadoSuspensao;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeriadoSuspensaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'data' => ['required', 'date'],
            'descricao' => ['required', 'string', 'max:255'],
            'tipo' => [
                'required',
                'string',
                Rule::in([
                    FeriadoSuspensao::TIPO_FERIADO,
                    FeriadoSuspensao::TIPO_SUSPENSAO,
                ]),
            ],
            'abrangencia' => [
                'required',
                'string',
                Rule::in([
                    FeriadoSuspensao::ABRANGENCIA_NACIONAL,
                    FeriadoSuspensao::ABRANGENCIA_LOCAL,
                ]),
            ],
            'uf' => [
                'nullable',
                'string',
                'size:2',
                Rule::requiredIf(fn () => $this->input('abrangencia') === FeriadoSuspensao::ABRANGENCIA_LOCAL),
            ],
            'comarca' => ['nullable', 'string', 'max:255'],
            'ativo' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $abrangencia = (string) $this->input('abrangencia');
        $uf = $this->filled('uf') ? strtoupper(trim((string) $this->input('uf'))) : null;
        $comarca = $this->filled('comarca') ? trim((string) $this->input('comarca')) : null;

        if ($abrangencia === FeriadoSuspensao::ABRANGENCIA_NACIONAL) {
            $uf = null;
            $comarca = null;
        }

        $this->merge([
            'uf' => $uf,
            'comarca' => $comarca,
            'ativo' => $this->boolean('ativo'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'data.required' => 'A data é obrigatória.',
            'data.date' => 'Informe uma data válida.',
            'descricao.required' => 'A descrição é obrigatória.',
            'tipo.required' => 'O tipo é obrigatório.',
            'tipo.in' => 'O tipo deve ser feriado ou suspensão.',
            'abrangencia.required' => 'A abrangencia é obrigatória.',
            'abrangencia.in' => 'A abrangência deve ser nacional ou local.',
            'uf.required' => 'A UF é obrigatória quando a abrangência for local.',
            'uf.size' => 'A UF deve conter 2 caracteres.',
            'ativo.required' => 'Informe se o registro está ativo.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'data' => 'data',
            'descricao' => 'descrição',
            'tipo' => 'tipo',
            'abrangencia' => 'abrangência',
            'uf' => 'UF',
            'comarca' => 'comarca',
        ];
    }
}
