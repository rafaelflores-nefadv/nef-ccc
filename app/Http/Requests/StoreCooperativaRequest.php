<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCooperativaRequest extends FormRequest
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
            'slug' => ['required', 'string', 'max:255', Rule::unique('cooperativas', 'slug')],
            'ativo' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $slugInformado = $this->filled('slug')
            ? (string) $this->input('slug')
            : (string) $this->input('nome');

        $this->merge([
            'nome' => trim((string) $this->input('nome')),
            'slug' => Str::slug(trim($slugInformado)),
            'ativo' => $this->has('ativo') ? $this->boolean('ativo') : true,
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O nome da cooperativa é obrigatório.',
            'slug.required' => 'O slug da cooperativa é obrigatório.',
            'slug.unique' => 'Já existe uma cooperativa com este slug.',
            'ativo.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',
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
            'ativo' => 'status',
        ];
    }
}
