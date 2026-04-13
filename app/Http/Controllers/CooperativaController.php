<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCooperativaRequest;
use App\Http\Requests\UpdateCooperativaRequest;
use App\Http\Requests\UpdateStatusCooperativaRequest;
use App\Models\Cooperativa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class CooperativaController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Cooperativa::class);

        $query = Cooperativa::query()->withCount(['users', 'casos']);

        $this->aplicarFiltros($request, $query);

        $cooperativas = $query
            ->orderBy('nome')
            ->paginate(15)
            ->withQueryString();

        return view('cooperativas.index', [
            'cooperativas' => $cooperativas,
            'filtros' => $request->only(['nome', 'slug', 'ativo']),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Cooperativa::class);

        return view('cooperativas.create', [
            'cooperativa' => new Cooperativa(),
        ]);
    }

    public function store(StoreCooperativaRequest $request): RedirectResponse
    {
        Gate::authorize('create', Cooperativa::class);

        try {
            DB::transaction(function () use ($request): void {
                Cooperativa::query()->create($request->validated());
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('erro', 'Não foi possível cadastrar a cooperativa. Verifique os dados informados.');
        }

        return redirect()
            ->route('cooperativas.index')
            ->with('status', 'Cooperativa cadastrada com sucesso.');
    }

    public function edit(Cooperativa $cooperativa): View
    {
        Gate::authorize('update', $cooperativa);

        return view('cooperativas.edit', [
            'cooperativa' => $cooperativa,
        ]);
    }

    public function update(UpdateCooperativaRequest $request, Cooperativa $cooperativa): RedirectResponse
    {
        Gate::authorize('update', $cooperativa);

        try {
            DB::transaction(function () use ($request, $cooperativa): void {
                $cooperativa->update($request->validated());
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('erro', 'Não foi possível salvar as alterações da cooperativa. Verifique os dados informados.');
        }

        return redirect()
            ->route('cooperativas.index')
            ->with('status', 'Cooperativa atualizada com sucesso.');
    }

    public function atualizarStatus(UpdateStatusCooperativaRequest $request, Cooperativa $cooperativa): RedirectResponse
    {
        Gate::authorize('update', $cooperativa);

        $ativo = $request->boolean('ativo');

        try {
            if (! $ativo && $cooperativa->users()->where('ativo', true)->exists()) {
                return back()->with('erro', 'Não é possível desativar a cooperativa porque existem usuários ativos vinculados a ela.');
            }

            $cooperativa->update([
                'ativo' => $ativo,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('erro', 'Não foi possível atualizar o status da cooperativa.');
        }

        return back()->with('status', $cooperativa->ativo
            ? 'Cooperativa ativada com sucesso.'
            : 'Cooperativa desativada com sucesso.');
    }

    protected function aplicarFiltros(Request $request, Builder $query): void
    {
        $query->when($request->filled('nome'), function (Builder $query) use ($request): void {
            $query->where('nome', 'ilike', '%'.$request->string('nome')->trim().'%');
        });

        $query->when($request->filled('slug'), function (Builder $query) use ($request): void {
            $query->where('slug', 'ilike', '%'.$request->string('slug')->trim().'%');
        });

        if ($request->has('ativo') && $request->input('ativo') !== '') {
            $query->where('ativo', (bool) $request->integer('ativo'));
        }
    }
}
