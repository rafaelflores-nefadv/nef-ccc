<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\SubstatusPertenceAoStatus;
use App\Support\EscopoCooperativa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCasoRequest extends FormRequest
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
            'cooperativa_id' => ['required', 'integer', Rule::exists('cooperativas', 'id')],
            'id_processo' => ['nullable', 'string', 'max:255'],
            'status_processo' => ['nullable', 'string', 'max:255'],
            'numero_processo' => ['nullable', 'string', 'max:255'],
            'area_direito' => ['nullable', 'string', 'max:255'],
            'esfera' => ['nullable', 'string', 'max:255'],
            'foro_tribunal' => ['nullable', 'string', 'max:255'],
            'vara_local' => ['nullable', 'string', 'max:255'],
            'tipo_acao' => ['nullable', 'string', 'max:255'],
            'codigo_empresa' => ['nullable', 'string', 'max:255'],
            'empresa' => ['nullable', 'string', 'max:255'],
            'agencia_filial' => ['nullable', 'string', 'max:255'],
            'escritorio_externo' => ['nullable', 'string', 'max:255'],
            'distribuicao' => ['nullable', 'date'],
            'fase_fluxo' => ['nullable', 'string', 'max:255'],
            'na_fase_desde' => ['nullable', 'date'],
            'fase' => ['nullable', 'string', 'max:255'],
            'data_fase_processual' => ['nullable', 'date'],
            'data_encerramento' => ['nullable', 'date'],
            'motivo_encerramento' => ['nullable', 'string'],
            'nome' => ['nullable', 'string', 'max:255'],
            'parte_contraria_cpf_cnpj' => ['nullable', 'string', 'max:20'],
            'modelo_conducao' => ['nullable', 'string', 'max:255'],
            'conducao_estrategica' => ['nullable', 'string', 'max:255'],
            'status_citacao' => ['nullable', 'string', 'max:255'],
            'classificacao' => ['nullable', 'string', 'max:255'],
            'irrecuperavel' => ['nullable', 'boolean'],
            'objeto_demanda' => ['nullable', 'string'],
            'instancia' => ['nullable', 'string', 'max:255'],
            'fase_acordo' => ['nullable', 'string', 'max:255'],
            'na_fase_acordo_desde' => ['nullable', 'date'],
            'polo_acao' => ['nullable', 'string', 'max:255'],
            'observacoes_gerais' => ['nullable', 'string'],
            'observacao_encerramento' => ['nullable', 'string'],
            'existe_saldo_residual' => ['nullable', 'boolean'],
            'medida_atipica' => ['nullable', 'string', 'max:255'],
            'advogado_parte_contraria' => ['nullable', 'string', 'max:255'],
            'comarca' => ['nullable', 'string', 'max:255'],
            'uf' => ['nullable', 'string', 'size:2'],
            'valor_causa' => ['nullable', 'numeric'],
            'data_cadastro_caso' => ['nullable', 'date'],
            'id_externo' => ['nullable', 'string', 'max:255'],
            'codigo_importacao' => ['nullable', 'string', 'max:255'],

            'numero_protocolo' => ['nullable', 'string', 'max:255'],
            'numero_prenotacao' => ['nullable', 'string', 'max:255'],
            'contrato' => ['required', 'string', 'max:255'],
            'partes' => ['required', 'string'],
            'matricula' => ['nullable', 'string', 'max:255'],
            'valor_divida' => ['nullable', 'numeric'],
            'responsavel_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'tipo_status_id' => ['nullable', 'integer', Rule::exists('tipos_status', 'id')],
            'tipo_substatus_id' => [
                'nullable',
                'integer',
                Rule::exists('tipos_substatus', 'id'),
                new SubstatusPertenceAoStatus('tipo_status_id'),
            ],
            'data_alteracao_status' => ['nullable', 'date'],
            'data_alteracao_substatus' => ['nullable', 'date'],
            'data_prazo' => ['nullable', 'date'],
            'data_primeiro_leilao' => ['nullable', 'date'],
            'data_segundo_leilao' => ['nullable', 'date', 'after_or_equal:data_primeiro_leilao'],
            'parecer' => ['nullable', 'string'],
            'arquivado' => ['nullable', 'boolean'],
            'pendente_faturamento' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $usuario = $this->user();

        if ($usuario && ! EscopoCooperativa::isAdmin($usuario)) {
            $cooperativasIds = EscopoCooperativa::cooperativaIds($usuario);

            if (count($cooperativasIds) === 1 && ! $this->filled('cooperativa_id')) {
                $this->merge([
                    'cooperativa_id' => $cooperativasIds[0],
                ]);
            }
        }

        $this->merge([
            'uf' => $this->filled('uf') ? strtoupper((string) $this->input('uf')) : null,
            'arquivado' => $this->boolean('arquivado'),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $usuario = $this->user();

            if (! $usuario) {
                return;
            }

            $cooperativaId = (int) $this->input('cooperativa_id');

            if (! EscopoCooperativa::usuarioPertenceCooperativa($usuario, $cooperativaId)) {
                $validator->errors()->add('cooperativa_id', 'Cooperativa invalida para o usuario logado.');

                return;
            }

            $responsavelId = $this->input('responsavel_id');

            if (! $responsavelId) {
                return;
            }

            $responsavel = User::query()
                ->with('cooperativas:id')
                ->select(['id', 'cooperativa_id'])
                ->find($responsavelId);

            if (! $responsavel || ! $responsavel->pertenceCooperativa($cooperativaId)) {
                $validator->errors()->add('responsavel_id', 'O responsavel deve pertencer a cooperativa do caso.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cooperativa_id.required' => 'A cooperativa e obrigatoria.',
            'cooperativa_id.exists' => 'A cooperativa selecionada e invalida.',
            'contrato.required' => 'O contrato e obrigatorio.',
            'partes.required' => 'O campo partes e obrigatorio.',
            'uf.size' => 'A UF deve conter 2 caracteres.',
            'valor_causa.numeric' => 'O valor da causa deve ser numerico.',
            'valor_divida.numeric' => 'O valor da divida deve ser numerico.',
            'responsavel_id.exists' => 'O responsavel selecionado e invalido.',
            'tipo_status_id.exists' => 'O status selecionado e invalido.',
            'tipo_substatus_id.exists' => 'O substatus selecionado e invalido.',
            'data_segundo_leilao.after_or_equal' => 'A data do segundo leilao nao pode ser anterior ao primeiro leilao.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'cooperativa_id' => 'cooperativa',
            'id_processo' => 'ID processo',
            'status_processo' => 'status do processo',
            'numero_processo' => 'numero do processo',
            'tipo_acao' => 'tipo de acao',
            'codigo_empresa' => 'codigo da empresa',
            'empresa' => 'empresa',
            'agencia_filial' => 'agencia/filial',
            'distribuicao' => 'distribuicao',
            'parte_contraria_cpf_cnpj' => 'parte contraria CPF/CNPJ',
            'numero_protocolo' => 'numero de protocolo',
            'numero_prenotacao' => 'numero de prenotacao',
            'data_cadastro_caso' => 'data de cadastro',
            'tipo_status_id' => 'status',
            'tipo_substatus_id' => 'substatus',
            'data_primeiro_leilao' => 'data do primeiro leilao',
            'data_segundo_leilao' => 'data do segundo leilao',
        ];
    }
}
