<?php

namespace App\Rules;

use App\Models\TipoSubstatus;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class SubstatusPertenceAoStatus implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    public function __construct(
        protected string $campoStatus = 'tipo_status_id'
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param Closure(string): void $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value) {
            return;
        }

        $tipoStatusId = $this->data[$this->campoStatus] ?? null;

        if (! $tipoStatusId) {
            return;
        }

        $substatus = TipoSubstatus::query()
            ->select(['id', 'tipo_status_id'])
            ->find($value);

        if (! $substatus) {
            return;
        }

        if ($substatus->tipo_status_id !== null && (int) $substatus->tipo_status_id !== (int) $tipoStatusId) {
            $fail('O substatus selecionado nao pertence ao status informado.');
        }
    }
}
