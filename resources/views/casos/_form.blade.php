@php
    $cooperativaSelecionada = old('cooperativa_id', $caso->cooperativa_id ?? auth()->user()?->cooperativa_id);
    $arquivadoValor = old('arquivado', $caso->arquivado ?? false);
    $inputClass = 'mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500';
    $distribuicaoValor = old('distribuicao');

    if ($distribuicaoValor === null) {
        $distribuicaoValor = optional($caso->distribuicao)->format('Y-m-d\TH:i');
    } elseif ($distribuicaoValor !== '' && ! str_contains((string) $distribuicaoValor, 'T')) {
        try {
            $distribuicaoValor = \Illuminate\Support\Carbon::parse((string) $distribuicaoValor)->format('Y-m-d\TH:i');
        } catch (\Throwable) {
            $distribuicaoValor = '';
        }
    }
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    @if ($isAdmin)
        <div>
            <label for="cooperativa_id" class="block text-sm font-medium text-gray-700">Cooperativa</label>
            <select name="cooperativa_id" id="cooperativa_id" class="{{ $inputClass }}" required>
                <option value="">Selecione</option>
                @foreach ($cooperativas as $cooperativa)
                    <option value="{{ $cooperativa->id }}" @selected((string) $cooperativaSelecionada === (string) $cooperativa->id)>
                        {{ $cooperativa->nome }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('cooperativa_id')" class="mt-2" />
        </div>
    @else
        <input type="hidden" name="cooperativa_id" value="{{ auth()->user()?->cooperativa_id }}">
    @endif

    <div>
        <label for="id_processo" class="block text-sm font-medium text-gray-700">ID Processo</label>
        <input type="text" name="id_processo" id="id_processo" value="{{ old('id_processo', $caso->id_processo) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('id_processo')" class="mt-2" />
    </div>

    <div>
        <label for="status_processo" class="block text-sm font-medium text-gray-700">Status do processo</label>
        <input type="text" name="status_processo" id="status_processo" value="{{ old('status_processo', $caso->status_processo) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('status_processo')" class="mt-2" />
    </div>

    <div>
        <label for="numero_processo" class="block text-sm font-medium text-gray-700">Número do processo</label>
        <input type="text" name="numero_processo" id="numero_processo" value="{{ old('numero_processo', $caso->numero_processo) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('numero_processo')" class="mt-2" />
    </div>

    <div>
        <label for="parte_contraria_cpf_cnpj" class="block text-sm font-medium text-gray-700">Parte contrária CPF/CNPJ</label>
        <input type="text" name="parte_contraria_cpf_cnpj" id="parte_contraria_cpf_cnpj" value="{{ old('parte_contraria_cpf_cnpj', $caso->parte_contraria_cpf_cnpj) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('parte_contraria_cpf_cnpj')" class="mt-2" />
    </div>

    <div>
        <label for="tipo_acao" class="block text-sm font-medium text-gray-700">Tipo de ação</label>
        <input type="text" name="tipo_acao" id="tipo_acao" value="{{ old('tipo_acao', $caso->tipo_acao) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('tipo_acao')" class="mt-2" />
    </div>

    <div>
        <label for="codigo_empresa" class="block text-sm font-medium text-gray-700">Código da empresa</label>
        <input type="text" name="codigo_empresa" id="codigo_empresa" value="{{ old('codigo_empresa', $caso->codigo_empresa) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('codigo_empresa')" class="mt-2" />
    </div>

    <div>
        <label for="empresa" class="block text-sm font-medium text-gray-700">Empresa</label>
        <input type="text" name="empresa" id="empresa" value="{{ old('empresa', $caso->empresa) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('empresa')" class="mt-2" />
    </div>

    <div>
        <label for="agencia_filial" class="block text-sm font-medium text-gray-700">Agência/Filial</label>
        <input type="text" name="agencia_filial" id="agencia_filial" value="{{ old('agencia_filial', $caso->agencia_filial) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('agencia_filial')" class="mt-2" />
    </div>

    <div>
        <label for="distribuicao" class="block text-sm font-medium text-gray-700">Distribuição</label>
        <input type="datetime-local" name="distribuicao" id="distribuicao" value="{{ $distribuicaoValor }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('distribuicao')" class="mt-2" />
    </div>

    <div>
        <label for="numero_protocolo" class="block text-sm font-medium text-gray-700">Número de protocolo</label>
        <input type="text" name="numero_protocolo" id="numero_protocolo" value="{{ old('numero_protocolo', $caso->numero_protocolo) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('numero_protocolo')" class="mt-2" />
    </div>

    <div>
        <label for="numero_prenotacao" class="block text-sm font-medium text-gray-700">Número de prenotação</label>
        <input type="text" name="numero_prenotacao" id="numero_prenotacao" value="{{ old('numero_prenotacao', $caso->numero_prenotacao) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('numero_prenotacao')" class="mt-2" />
    </div>

    <div>
        <label for="data_cadastro_caso" class="block text-sm font-medium text-gray-700">Data de cadastro</label>
        <input type="date" name="data_cadastro_caso" id="data_cadastro_caso" value="{{ old('data_cadastro_caso', optional($caso->data_cadastro_caso)->format('Y-m-d')) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('data_cadastro_caso')" class="mt-2" />
    </div>

    <div>
        <label for="nome" class="block text-sm font-medium text-gray-700">Nome</label>
        <input type="text" name="nome" id="nome" value="{{ old('nome', $caso->nome) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('nome')" class="mt-2" />
    </div>

    <div>
        <label for="contrato" class="block text-sm font-medium text-gray-700">Contrato</label>
        <input type="text" name="contrato" id="contrato" value="{{ old('contrato', $caso->contrato) }}" class="{{ $inputClass }}" required>
        <x-input-error :messages="$errors->get('contrato')" class="mt-2" />
    </div>

    <div>
        <label for="comarca" class="block text-sm font-medium text-gray-700">Comarca</label>
        <input type="text" name="comarca" id="comarca" value="{{ old('comarca', $caso->comarca) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('comarca')" class="mt-2" />
    </div>

    <div>
        <label for="uf" class="block text-sm font-medium text-gray-700">UF</label>
        <input type="text" name="uf" id="uf" maxlength="2" value="{{ old('uf', $caso->uf) }}" class="{{ $inputClass }} uppercase">
        <x-input-error :messages="$errors->get('uf')" class="mt-2" />
    </div>

    <div>
        <label for="matricula" class="block text-sm font-medium text-gray-700">Matrícula</label>
        <input type="text" name="matricula" id="matricula" value="{{ old('matricula', $caso->matricula) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('matricula')" class="mt-2" />
    </div>

    <div>
        <label for="valor_causa" class="block text-sm font-medium text-gray-700">Valor da causa</label>
        <input type="number" step="0.01" name="valor_causa" id="valor_causa" value="{{ old('valor_causa', $caso->valor_causa) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('valor_causa')" class="mt-2" />
    </div>

    <div>
        <label for="valor_divida" class="block text-sm font-medium text-gray-700">Valor da dívida</label>
        <input type="number" step="0.01" name="valor_divida" id="valor_divida" value="{{ old('valor_divida', $caso->valor_divida) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('valor_divida')" class="mt-2" />
    </div>

    <div>
        <label for="responsavel_id" class="block text-sm font-medium text-gray-700">Responsável</label>
        <select name="responsavel_id" id="responsavel_id" class="{{ $inputClass }}">
            <option value="">Selecione</option>
            @foreach ($responsaveis as $responsavel)
                <option value="{{ $responsavel->id }}" @selected((string) old('responsavel_id', $caso->responsavel_id) === (string) $responsavel->id)>
                    {{ $responsavel->name }}@if($isAdmin && $responsavel->cooperativa)
                        - {{ $responsavel->cooperativa->nome }}
                    @endif
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('responsavel_id')" class="mt-2" />
    </div>

    <div>
        <label for="tipo_status_id" class="block text-sm font-medium text-gray-700">Status</label>
        <select name="tipo_status_id" id="tipo_status_id" class="{{ $inputClass }}">
            <option value="">Selecione</option>
            @foreach ($tiposStatus as $status)
                <option value="{{ $status->id }}" @selected((string) old('tipo_status_id', $caso->tipo_status_id) === (string) $status->id)>
                    {{ $status->nome }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('tipo_status_id')" class="mt-2" />
    </div>

    <div>
        <label for="tipo_substatus_id" class="block text-sm font-medium text-gray-700">Substatus</label>
        <select name="tipo_substatus_id" id="tipo_substatus_id" class="{{ $inputClass }}">
            <option value="">Selecione</option>
            @foreach ($tiposSubstatus as $substatus)
                <option value="{{ $substatus->id }}" @selected((string) old('tipo_substatus_id', $caso->tipo_substatus_id) === (string) $substatus->id)>
                    {{ $substatus->nome }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('tipo_substatus_id')" class="mt-2" />
    </div>

    <div>
        <label for="data_alteracao_status" class="block text-sm font-medium text-gray-700">Data de alteração do status</label>
        <input type="date" name="data_alteracao_status" id="data_alteracao_status" value="{{ old('data_alteracao_status', optional($caso->data_alteracao_status)->format('Y-m-d')) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('data_alteracao_status')" class="mt-2" />
    </div>

    <div>
        <label for="data_alteracao_substatus" class="block text-sm font-medium text-gray-700">Data de alteração do substatus</label>
        <input type="date" name="data_alteracao_substatus" id="data_alteracao_substatus" value="{{ old('data_alteracao_substatus', optional($caso->data_alteracao_substatus)->format('Y-m-d')) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('data_alteracao_substatus')" class="mt-2" />
    </div>

    <div>
        <label for="data_prazo" class="block text-sm font-medium text-gray-700">Data de prazo</label>
        <input type="date" name="data_prazo" id="data_prazo" value="{{ old('data_prazo', optional($caso->data_prazo)->format('Y-m-d')) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('data_prazo')" class="mt-2" />
    </div>

    <div>
        <label for="data_primeiro_leilao" class="block text-sm font-medium text-gray-700">Data do primeiro leilão</label>
        <input type="date" name="data_primeiro_leilao" id="data_primeiro_leilao" value="{{ old('data_primeiro_leilao', optional($caso->data_primeiro_leilao)->format('Y-m-d')) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('data_primeiro_leilao')" class="mt-2" />
    </div>

    <div>
        <label for="data_segundo_leilao" class="block text-sm font-medium text-gray-700">Data do segundo leilão</label>
        <input type="date" name="data_segundo_leilao" id="data_segundo_leilao" value="{{ old('data_segundo_leilao', optional($caso->data_segundo_leilao)->format('Y-m-d')) }}" class="{{ $inputClass }}">
        <x-input-error :messages="$errors->get('data_segundo_leilao')" class="mt-2" />
    </div>
</div>

<div class="mt-4">
    <label for="partes" class="block text-sm font-medium text-gray-700">Partes</label>
    <textarea name="partes" id="partes" rows="4" class="{{ $inputClass }}" required>{{ old('partes', $caso->partes) }}</textarea>
    <x-input-error :messages="$errors->get('partes')" class="mt-2" />
</div>

<div class="mt-4">
    <label for="observacoes_gerais" class="block text-sm font-medium text-gray-700">Observações gerais</label>
    <textarea name="observacoes_gerais" id="observacoes_gerais" rows="3" class="{{ $inputClass }}">{{ old('observacoes_gerais', $caso->observacoes_gerais) }}</textarea>
    <x-input-error :messages="$errors->get('observacoes_gerais')" class="mt-2" />
</div>

<div class="mt-4">
    <label for="parecer" class="block text-sm font-medium text-gray-700">Parecer</label>
    <textarea name="parecer" id="parecer" rows="3" class="{{ $inputClass }}">{{ old('parecer', $caso->parecer) }}</textarea>
    <x-input-error :messages="$errors->get('parecer')" class="mt-2" />
</div>

<div class="mt-4">
    <label class="inline-flex items-center">
        <input type="checkbox" name="arquivado" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked((bool) $arquivadoValor)>
        <span class="ms-2 text-sm text-gray-600">Caso arquivado</span>
    </label>
    <x-input-error :messages="$errors->get('arquivado')" class="mt-2" />
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
        {{ $submitLabel }}
    </button>
    <a href="{{ route('casos.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
</div>
