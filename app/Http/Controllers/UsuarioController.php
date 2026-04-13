<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateSenhaUsuarioRequest;
use App\Http\Requests\UpdateStatusUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\Cooperativa;
use App\Models\Papel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Throwable;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);
        $perPage = $this->resolvePerPage($request);

        $query = User::query()->with([
            'cooperativas:id,nome',
            'cooperativa:id,nome',
            'papeis:id,nome',
        ]);

        $this->aplicarFiltros($request, $query);

        $usuarios = $query
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('usuarios.index', [
            'usuarios' => $usuarios,
            'cooperativas' => $this->cooperativas(),
            'papeis' => $this->papeis(),
            'perfis' => $this->opcoesPerfil(),
            'perPage' => $perPage,
            'perPageOptions' => $this->perPageOptions(),
            'filtros' => $request->only([
                'nome',
                'email',
                'perfil',
                'cooperativa_id',
                'papel_id',
                'ativo',
            ]),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('usuarios.create', [
            'usuario' => new User(),
            'cooperativas' => $this->cooperativas(),
            'papeis' => $this->papeis(),
            'perfis' => $this->opcoesPerfil(),
        ]);
    }

    public function store(StoreUsuarioRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        try {
            DB::transaction(function () use ($request): void {
                $dados = $request->validated();
                $papelId = $dados['papel_id'] ?? null;
                $cooperativasIds = $this->normalizarCooperativasIds($dados['cooperativas'] ?? []);

                unset($dados['papel_id'], $dados['cooperativas'], $dados['password_confirmation']);

                $dados['cooperativa_id'] = $this->cooperativaPrincipalId($cooperativasIds);
                $dados['password'] = Hash::make((string) $dados['password']);

                $usuario = User::query()->create($dados);

                $this->sincronizarCooperativasUsuario($usuario, $cooperativasIds);
                $this->sincronizarPapelUsuario($usuario, is_int($papelId) ? $papelId : null);
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('erro', 'Nao foi possivel cadastrar o usuario. Verifique os dados informados.');
        }

        return redirect()
            ->route('usuarios.index')
            ->with('status', 'Usuario cadastrado com sucesso.');
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);
        $user->load([
            'papeis:id,nome',
            'cooperativas:id,nome',
        ]);

        return view('usuarios.edit', [
            'usuario' => $user,
            'cooperativas' => $this->cooperativas(),
            'papeis' => $this->papeis(),
            'perfis' => $this->opcoesPerfil(),
        ]);
    }

    public function update(UpdateUsuarioRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        try {
            DB::transaction(function () use ($request, $user): void {
                $dados = $request->validated();
                $papelId = $dados['papel_id'] ?? null;
                $cooperativasIds = $this->normalizarCooperativasIds($dados['cooperativas'] ?? []);
                $novaSenha = $dados['password'] ?? null;

                unset($dados['papel_id'], $dados['cooperativas'], $dados['password'], $dados['password_confirmation']);

                $dados['cooperativa_id'] = $this->cooperativaPrincipalId($cooperativasIds);

                if (is_string($novaSenha) && trim($novaSenha) !== '') {
                    $dados['password'] = Hash::make($novaSenha);
                }

                $user->update($dados);
                $this->sincronizarCooperativasUsuario($user, $cooperativasIds);
                $this->sincronizarPapelUsuario($user, is_int($papelId) ? $papelId : null);
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('erro', 'Nao foi possivel salvar as alteracoes do usuario. Verifique os dados informados.');
        }

        return redirect()
            ->route('usuarios.index')
            ->with('status', 'Usuario atualizado com sucesso.');
    }

    public function atualizarStatus(UpdateStatusUsuarioRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        try {
            $user->update([
                'ativo' => $request->boolean('ativo'),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('erro', 'Nao foi possivel atualizar o status do usuario.');
        }

        return back()->with('status', $user->ativo
            ? 'Usuario ativado com sucesso.'
            : 'Usuario desativado com sucesso.');
    }

    public function editSenha(User $user): View
    {
        Gate::authorize('update', $user);

        return view('usuarios.senha', [
            'usuario' => $user,
        ]);
    }

    public function updateSenha(UpdateSenhaUsuarioRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        try {
            $user->update([
                'password' => Hash::make((string) $request->validated('password')),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('erro', 'Nao foi possivel redefinir a senha do usuario.');
        }

        return redirect()
            ->route('usuarios.index')
            ->with('status', 'Senha redefinida com sucesso.');
    }

    protected function aplicarFiltros(Request $request, Builder $query): void
    {
        $query->when($request->filled('nome'), function (Builder $query) use ($request): void {
            $query->where('name', 'ilike', '%'.$request->string('nome')->trim().'%');
        });

        $query->when($request->filled('email'), function (Builder $query) use ($request): void {
            $query->where('email', 'ilike', '%'.$request->string('email')->trim().'%');
        });

        if ($request->filled('perfil')) {
            $query->where('perfil', (string) $request->input('perfil'));
        }

        if ($request->filled('cooperativa_id')) {
            $cooperativaId = (int) $request->input('cooperativa_id');

            $query->where(function (Builder $subQuery) use ($cooperativaId): void {
                $subQuery
                    ->whereHas('cooperativas', fn (Builder $cooperativaQuery) => $cooperativaQuery->where('cooperativas.id', $cooperativaId))
                    ->orWhere('cooperativa_id', $cooperativaId);
            });
        }

        if ($request->filled('papel_id')) {
            $papelId = (int) $request->input('papel_id');
            $query->whereHas('papeis', fn (Builder $papelQuery) => $papelQuery->where('papeis.id', $papelId));
        }

        if ($request->has('ativo') && $request->input('ativo') !== '') {
            $query->where('ativo', (bool) $request->integer('ativo'));
        }
    }

    protected function cooperativas(): Collection
    {
        return Cooperativa::query()->orderBy('nome')->get(['id', 'nome']);
    }

    protected function papeis(): Collection
    {
        return Papel::query()
            ->orderByDesc('ativo')
            ->orderBy('nome')
            ->get(['id', 'nome', 'ativo']);
    }

    /**
     * @return array<string, string>
     */
    protected function opcoesPerfil(): array
    {
        return [
            User::PERFIL_ADMIN => 'Administrador',
            User::PERFIL_GESTOR => 'Gestor',
            User::PERFIL_OPERACIONAL => 'Operacional',
        ];
    }

    /**
     * @param array<int> $cooperativasIds
     */
    protected function sincronizarCooperativasUsuario(User $user, array $cooperativasIds): void
    {
        $cooperativaPrincipalId = $this->cooperativaPrincipalId($cooperativasIds);

        $user->cooperativas()->sync($cooperativasIds);

        if ((int) ($user->cooperativa_id ?? 0) !== (int) ($cooperativaPrincipalId ?? 0)) {
            $user->forceFill([
                'cooperativa_id' => $cooperativaPrincipalId,
            ])->save();
        }
    }

    protected function sincronizarPapelUsuario(User $user, ?int $papelId): void
    {
        if (! $papelId) {
            $user->papeis()->sync([]);

            return;
        }

        $user->papeis()->sync([$papelId]);
    }

    /**
     * @param array<int|string> $cooperativas
     * @return array<int>
     */
    protected function normalizarCooperativasIds(array $cooperativas): array
    {
        return collect($cooperativas)
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param array<int> $cooperativasIds
     */
    protected function cooperativaPrincipalId(array $cooperativasIds): ?int
    {
        return $cooperativasIds[0] ?? null;
    }
}
