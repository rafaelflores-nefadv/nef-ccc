<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePapelRequest extends FormRequest
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
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('papeis', 'slug')],
            'descricao' => ['nullable', 'string'],
            'ativo' => ['nullable', 'boolean'],
            'permissoes' => ['nullable', 'array'],
            'permissoes.*' => ['integer', Rule::exists('permissoes', 'id')],
        ];
    }

    protected function prepareForValidation(): void
    {
        $slugInformado = $this->filled('slug')
            ? (string) $this->input('slug')
            : (string) $this->input('nome');

        $permissoes = $this->input('permissoes', []);

        $this->merge([
            'nome' => trim((string) $this->input('nome')),
            'slug' => Str::slug($slugInformado),
            'descricao' => $this->filled('descricao') ? trim((string) $this->input('descricao')) : null,
            'ativo' => $this->has('ativo') ? $this->boolean('ativo') : true,
            'permissoes' => is_array($permissoes)
                ? array_values(array_unique(array_map('intval', $permissoes)))
                : [],
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do papel é obrigatório.',
            'slug.required' => 'O slug do papel é obrigatório.',
            'slug.unique' => 'Já existe um papel com este slug.',
            'ativo.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',
            'permissoes.array' => 'As permissões devem ser informadas em formato de lista.',
            'permissoes.*.exists' => 'Uma ou mais permissões selecionadas são inválidas.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'slug' => 'slug',
            'descricao' => 'descrição',
            'ativo' => 'status',
            'permissoes' => 'permissões',
        ];
    }
}

