<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePapelRequest;
use App\Http\Requests\UpdatePapelRequest;
use App\Http\Requests\UpdateStatusPapelRequest;
use App\Models\Papel;
use App\Models\Permissao;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class PapelController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Papel::class);
        $perPage = $this->resolvePerPage($request);

        $query = Papel::query()->withCount('permissoes');

        $this->aplicarFiltros($request, $query);

        $papeis = $query
            ->orderBy('nome')
            ->paginate($perPage)
            ->withQueryString();

        return view('papeis.index', [
            'papeis' => $papeis,
            'perPage' => $perPage,
            'perPageOptions' => $this->perPageOptions(),
            'filtros' => $request->only(['nome', 'slug', 'ativo']),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Papel::class);

        return view('papeis.create', [
            'papel' => new Papel(),
            'permissoesPorModulo' => $this->permissoesPorModulo(),
        ]);
    }

    public function store(StorePapelRequest $request): RedirectResponse
    {
        Gate::authorize('create', Papel::class);

        try {
            DB::transaction(function () use ($request): void {
                $dados = $request->validated();
                $permissoes = $dados['permissoes'] ?? [];

                unset($dados['permissoes']);

                $papel = Papel::query()->create($dados);
                $papel->permissoes()->sync($permissoes);
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('erro', 'Não foi possível cadastrar o papel. Verifique os dados informados.');
        }

        return redirect()
            ->route('papeis.index')
            ->with('status', 'Papel cadastrado com sucesso.');
    }

    public function edit(Papel $papel): View
    {
        Gate::authorize('update', $papel);

        $papel->load('permissoes:id');

        return view('papeis.edit', [
            'papel' => $papel,
            'permissoesPorModulo' => $this->permissoesPorModulo(),
        ]);
    }

    public function update(UpdatePapelRequest $request, Papel $papel): RedirectResponse
    {
        Gate::authorize('update', $papel);

        try {
            DB::transaction(function () use ($request, $papel): void {
                $dados = $request->validated();
                $permissoes = $dados['permissoes'] ?? [];

                unset($dados['permissoes']);

                $papel->update($dados);
                $papel->permissoes()->sync($permissoes);
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('erro', 'Não foi possível salvar as alterações do papel. Verifique os dados informados.');
        }

        return redirect()
            ->route('papeis.index')
            ->with('status', 'Papel atualizado com sucesso.');
    }

    public function atualizarStatus(UpdateStatusPapelRequest $request, Papel $papel): RedirectResponse
    {
        Gate::authorize('update', $papel);

        try {
            $papel->update([
                'ativo' => $request->boolean('ativo'),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('erro', 'Não foi possível atualizar o status do papel.');
        }

        return back()->with('status', $papel->ativo
            ? 'Papel ativado com sucesso.'
            : 'Papel desativado com sucesso.');
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

    protected function permissoesPorModulo(): Collection
    {
        return Permissao::query()
            ->orderBy('modulo')
            ->orderBy('nome')
            ->get(['id', 'nome', 'slug', 'modulo', 'descricao'])
            ->groupBy(fn (Permissao $permissao): string => $permissao->modulo ?: 'geral');
    }
}
