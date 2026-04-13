<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeriadoSuspensaoRequest;
use App\Http\Requests\UpdateFeriadoSuspensaoRequest;
use App\Models\FeriadoSuspensao;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Throwable;
use Illuminate\View\View;

class FeriadoSuspensaoController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', FeriadoSuspensao::class);
        $perPage = $this->resolvePerPage($request);

        $query = FeriadoSuspensao::query();

        $this->aplicarFiltros($request, $query);

        $feriadosSuspensoes = $query
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('feriados_suspensoes.index', [
            'feriadosSuspensoes' => $feriadosSuspensoes,
            'perPage' => $perPage,
            'perPageOptions' => $this->perPageOptions(),
            'filtros' => $request->only([
                'data',
                'descricao',
                'tipo',
                'abrangencia',
                'uf',
                'comarca',
                'ativo',
            ]),
            ...$this->opcoesFormulario(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', FeriadoSuspensao::class);

        return view('feriados_suspensoes.create', [
            'feriadoSuspensao' => new FeriadoSuspensao(),
            ...$this->opcoesFormulario(),
        ]);
    }

    public function store(StoreFeriadoSuspensaoRequest $request): RedirectResponse
    {
        Gate::authorize('create', FeriadoSuspensao::class);

        try {
            FeriadoSuspensao::query()->create($request->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('erro', 'Não foi possível salvar o registro. Verifique os dados informados.');
        }

        return redirect()
            ->route('feriados_suspensoes.index')
            ->with('status', 'Registro criado com sucesso.');
    }

    public function edit(FeriadoSuspensao $feriadoSuspensao): View
    {
        Gate::authorize('update', $feriadoSuspensao);

        return view('feriados_suspensoes.edit', [
            'feriadoSuspensao' => $feriadoSuspensao,
            ...$this->opcoesFormulario(),
        ]);
    }

    public function update(UpdateFeriadoSuspensaoRequest $request, FeriadoSuspensao $feriadoSuspensao): RedirectResponse
    {
        Gate::authorize('update', $feriadoSuspensao);

        try {
            $feriadoSuspensao->update($request->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('erro', 'Não foi possível salvar as alterações do registro. Verifique os dados informados.');
        }

        return redirect()
            ->route('feriados_suspensoes.index')
            ->with('status', 'Registro atualizado com sucesso.');
    }

    public function destroy(FeriadoSuspensao $feriadoSuspensao): RedirectResponse
    {
        Gate::authorize('delete', $feriadoSuspensao);

        try {
            $feriadoSuspensao->delete();
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('erro', 'Não foi possível excluir o registro.');
        }

        return redirect()
            ->route('feriados_suspensoes.index')
            ->with('status', 'Registro excluído com sucesso.');
    }

    protected function aplicarFiltros(Request $request, Builder $query): void
    {
        if ($request->filled('data')) {
            $query->whereDate('data', $request->input('data'));
        }

        $query->when($request->filled('descricao'), function (Builder $query) use ($request): void {
            $query->where('descricao', 'ilike', '%'.$request->string('descricao')->trim().'%');
        });

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        if ($request->filled('abrangencia')) {
            $query->where('abrangencia', $request->input('abrangencia'));
        }

        $query->when($request->filled('uf'), function (Builder $query) use ($request): void {
            $query->where('uf', strtoupper((string) $request->input('uf')));
        });

        $query->when($request->filled('comarca'), function (Builder $query) use ($request): void {
            $query->where('comarca', 'ilike', '%'.$request->string('comarca')->trim().'%');
        });

        if ($request->has('ativo') && $request->input('ativo') !== '') {
            $query->where('ativo', (bool) $request->integer('ativo'));
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function opcoesFormulario(): array
    {
        return [
            'tipos' => [
                FeriadoSuspensao::TIPO_FERIADO => 'Feriado',
                FeriadoSuspensao::TIPO_SUSPENSAO => 'Suspensão',
            ],
            'abrangencias' => [
                FeriadoSuspensao::ABRANGENCIA_NACIONAL => 'Nacional',
                FeriadoSuspensao::ABRANGENCIA_LOCAL => 'Local',
            ],
        ];
    }
}
