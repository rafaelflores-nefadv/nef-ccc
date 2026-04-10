<?php

namespace App\Http\Requests;

use App\Rules\SubstatusPertenceAoStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAndamentoCasoRequest extends FormRequest
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
            'tipo_status_id' => ['required', 'integer', Rule::exists('tipos_status', 'id')],
            'tipo_substatus_id' => [
                'required',
                'integer',
                Rule::exists('tipos_substatus', 'id'),
                new SubstatusPertenceAoStatus('tipo_status_id'),
            ],
            'descricao' => ['required', 'string'],
            'data_andamento' => ['required', 'date'],
            'data_prazo' => ['nullable', 'date', 'after_or_equal:data_andamento'],
            'data_primeiro_leilao' => ['nullable', 'date', 'after_or_equal:data_andamento'],
            'data_segundo_leilao' => ['nullable', 'date', 'after_or_equal:data_primeiro_leilao', 'after_or_equal:data_andamento'],
            'observacoes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tipo_status_id.required' => 'O campo status é obrigatório.',
            'tipo_status_id.exists' => 'O status selecionado é inválido.',
            'tipo_substatus_id.required' => 'O campo substatus é obrigatório.',
            'tipo_substatus_id.exists' => 'O substatus selecionado é inválido.',
            'descricao.required' => 'A descrição do andamento é obrigatória.',
            'data_andamento.required' => 'A data do andamento é obrigatória.',
            'data_andamento.date' => 'Informe uma data válida para o andamento.',
            'data_prazo.after_or_equal' => 'A data de prazo não pode ser anterior a data do andamento.',
            'data_primeiro_leilao.after_or_equal' => 'A data do primeiro leilão não pode ser anterior a data do andamento.',
            'data_segundo_leilao.after_or_equal' => 'A data do segundo leilão não pode ser anterior a data do andamento e do primeiro leilão.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tipo_status_id' => 'status',
            'tipo_substatus_id' => 'substatus',
            'data_andamento' => 'data do andamento',
            'data_prazo' => 'data de prazo',
            'data_primeiro_leilao' => 'data do primeiro leilão',
            'data_segundo_leilao' => 'data do segundo leilão',
        ];
    }
}
