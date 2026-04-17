<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCasoRequest;
use App\Http\Requests\UpdateCasoRequest;
use App\Models\Caso;
use App\Models\Cooperativa;
use App\Models\TipoStatus;
use App\Models\TipoSubstatus;
use App\Models\User;
use App\Services\GeradorCodigoCasoService;
use App\Services\PrazoCasoService;
use App\Support\EscopoCooperativa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class CasoController extends Controller
{
    public function __construct(
        protected GeradorCodigoCasoService $geradorCodigoCasoService,
        protected PrazoCasoService $prazoCasoService
    ) {
    }

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Caso::class);
        $perPage = $this->resolvePerPage($request);

        /** @var User $usuario */
        $usuario = $request->user();
        $isAdmin = EscopoCooperativa::isAdmin($usuario);
        $cooperativasIdsUsuario = EscopoCooperativa::cooperativaIds($usuario);

        $query = Caso::query()
            ->with([
                'cooperativa:id,nome',
                'tipoStatus:id,nome',
                'tipoSubstatus:id,nome',
            ]);

        if (! $isAdmin) {
            $this->aplicarEscopoCooperativas($query, $cooperativasIdsUsuario);
        }

        $this->aplicarFiltros($request, $query, $isAdmin);

        $casos = $query
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('casos.index', [
            'casos' => $casos,
            'isAdmin' => $isAdmin,
            'perPage' => $perPage,
            'perPageOptions' => $this->perPageOptions(),
            'cooperativas' => $isAdmin
                ? Cooperativa::query()->orderBy('nome')->get(['id', 'nome'])
                : collect(),
            'tiposStatus' => TipoStatus::query()->orderBy('ordem')->orderBy('nome')->get(['id', 'nome']),
            'tiposSubstatus' => TipoSubstatus::query()->orderBy('ordem')->orderBy('nome')->get(['id', 'nome']),
            'statusPrazoOpcoes' => [
                PrazoCasoService::STATUS_DENTRO_DO_PRAZO => 'Dentro do prazo',
                PrazoCasoService::STATUS_IGUAL_AO_PRAZO => 'Igual ao prazo',
                PrazoCasoService::STATUS_PRAZO_VENCIDO => 'Passou do prazo',
                PrazoCasoService::STATUS_SEM_DISTRIBUICAO => 'Sem distribuicao',
            ],
            'diasPrazoConfigurado' => $this->prazoCasoService->obterDiasAntesPrazo(),
            'filtros' => $request->only([
                'busca_geral',
                'codigo_caso',
                'numero_protocolo',
                'numero_prenotacao',
                'contrato',
                'nome',
                'comarca',
                'cooperativa_id',
                'tipo_status_id',
                'tipo_substatus_id',
                'pendente_faturamento',
                'status_prazo_distribuicao',
            ]),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Caso::class);

        /** @var User $usuario */
        $usuario = $request->user();

        return view('casos.create', [
            'caso' => new Caso(),
            ...$this->dadosFormulario($usuario, null),
        ]);
    }

    public function store(StoreCasoRequest $request): RedirectResponse
    {
        Gate::authorize('create', Caso::class);

        try {
            $dados = $request->validated();
            $dados['codigo_caso'] = $this->geradorCodigoCasoService->gerar();

            $caso = Caso::query()->create($dados);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('erro', 'Nao foi possivel salvar o caso. Verifique os dados informados.');
        }

        return redirect()
            ->route('casos.show', $caso)
            ->with('status', 'Caso criado com sucesso.');
    }

    public function show(Caso $caso): View
    {
        Gate::authorize('view', $caso);

        $caso->load([
            'cooperativa:id,nome',
            'responsavel:id,name',
            'tipoStatus:id,nome',
            'tipoSubstatus:id,nome',
            'andamentos' => fn ($query) => $query
                ->with([
                    'tipoStatus:id,nome',
                    'tipoSubstatus:id,nome',
                ])
                ->orderByDesc('data_descricao')
                ->orderByDesc('id'),
        ]);

        return view('casos.show', [
            'caso' => $caso,
            'tiposStatus' => TipoStatus::query()->where('ativo', true)->orderBy('ordem')->orderBy('nome')->get(['id', 'nome']),
            'tiposSubstatus' => TipoSubstatus::query()->where('ativo', true)->orderBy('ordem')->orderBy('nome')->get(['id', 'nome']),
            'diasPrazoConfigurado' => $this->prazoCasoService->obterDiasAntesPrazo(),
        ]);
    }

    public function edit(Request $request, Caso $caso): View
    {
        Gate::authorize('update', $caso);

        /** @var User $usuario */
        $usuario = $request->user();

        return view('casos.edit', [
            'caso' => $caso,
            ...$this->dadosFormulario($usuario, $caso),
        ]);
    }

    public function update(UpdateCasoRequest $request, Caso $caso): RedirectResponse
    {
        Gate::authorize('update', $caso);

        try {
            $caso->update($request->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('erro', 'Nao foi possivel salvar as alteracoes do caso. Verifique os dados informados.');
        }

        return redirect()
            ->route('casos.show', $caso)
            ->with('status', 'Caso atualizado com sucesso.');
    }

    public function destroy(Caso $caso): RedirectResponse
    {
        Gate::authorize('delete', $caso);

        try {
            $caso->delete();
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('erro', 'Nao foi possivel excluir o caso.');
        }

        return redirect()
            ->route('casos.index')
            ->with('status', 'Caso excluido com sucesso.');
    }

    protected function aplicarFiltros(Request $request, Builder $query, bool $isAdmin): void
    {
        $query->when($request->filled('codigo_caso'), function (Builder $query) use ($request): void {
            $query->where('codigo_caso', 'ilike', '%'.$request->string('codigo_caso')->trim().'%');
        });

        $query->when($request->filled('busca_geral'), function (Builder $query) use ($request): void {
            $termo = $request->string('busca_geral')->trim();

            $query->where(function (Builder $subQuery) use ($termo): void {
                $subQuery
                    ->where('codigo_caso', 'ilike', '%'.$termo.'%')
                    ->orWhere('numero_protocolo', 'ilike', '%'.$termo.'%')
                    ->orWhere('numero_prenotacao', 'ilike', '%'.$termo.'%')
                    ->orWhere('contrato', 'ilike', '%'.$termo.'%')
                    ->orWhere('nome', 'ilike', '%'.$termo.'%')
                    ->orWhere('comarca', 'ilike', '%'.$termo.'%');
            });
        });

        $query->when($request->filled('numero_protocolo'), function (Builder $query) use ($request): void {
            $query->where('numero_protocolo', 'ilike', '%'.$request->string('numero_protocolo')->trim().'%');
        });

        $query->when($request->filled('numero_prenotacao'), function (Builder $query) use ($request): void {
            $query->where('numero_prenotacao', 'ilike', '%'.$request->string('numero_prenotacao')->trim().'%');
        });

        $query->when($request->filled('contrato'), function (Builder $query) use ($request): void {
            $query->where('contrato', 'ilike', '%'.$request->string('contrato')->trim().'%');
        });

        $query->when($request->filled('nome'), function (Builder $query) use ($request): void {
            $query->where('nome', 'ilike', '%'.$request->string('nome')->trim().'%');
        });

        $query->when($request->filled('comarca'), function (Builder $query) use ($request): void {
            $query->where('comarca', 'ilike', '%'.$request->string('comarca')->trim().'%');
        });

        if ($isAdmin && $request->filled('cooperativa_id')) {
            $query->where('cooperativa_id', (int) $request->input('cooperativa_id'));
        }

        if ($request->filled('tipo_status_id')) {
            $query->where('tipo_status_id', (int) $request->input('tipo_status_id'));
        }

        if ($request->filled('tipo_substatus_id')) {
            $query->where('tipo_substatus_id', (int) $request->input('tipo_substatus_id'));
        }

        if ($request->filled('pendente_faturamento')) {
            $query->where('pendente_faturamento', filter_var($request->input('pendente_faturamento'), FILTER_VALIDATE_BOOLEAN));
        }

        $this->aplicarFiltroStatusPrazoDistribuicao($request, $query);
    }

    protected function aplicarFiltroStatusPrazoDistribuicao(Request $request, Builder $query): void
    {
        if (! $request->filled('status_prazo_distribuicao')) {
            return;
        }

        $status = (string) $request->input('status_prazo_distribuicao');
        $dataBasePrazo = $this->prazoCasoService->dataBasePrazo()->toDateString();

        match ($status) {
            PrazoCasoService::STATUS_SEM_DISTRIBUICAO => $query->whereNull('distribuicao'),
            PrazoCasoService::STATUS_IGUAL_AO_PRAZO => $query
                ->whereNotNull('distribuicao')
                ->whereDate('distribuicao', $dataBasePrazo),
            PrazoCasoService::STATUS_PRAZO_VENCIDO => $query
                ->whereNotNull('distribuicao')
                ->whereDate('distribuicao', '<', $dataBasePrazo),
            PrazoCasoService::STATUS_DENTRO_DO_PRAZO => $query
                ->whereNotNull('distribuicao')
                ->whereDate('distribuicao', '>', $dataBasePrazo),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function dadosFormulario(User $usuario, ?Caso $caso): array
    {
        $isAdmin = EscopoCooperativa::isAdmin($usuario);
        $cooperativasPermitidas = $this->cooperativasPermitidas($usuario);
        $cooperativaAtualId = $caso?->cooperativa_id ?? ($cooperativasPermitidas->first()?->id ?? null);

        return [
            'isAdmin' => $isAdmin,
            'cooperativas' => $cooperativasPermitidas,
            'responsaveis' => $this->responsaveis($usuario, $cooperativaAtualId),
            'tiposStatus' => TipoStatus::query()->orderBy('ordem')->orderBy('nome')->get(['id', 'nome']),
            'tiposSubstatus' => TipoSubstatus::query()->orderBy('ordem')->orderBy('nome')->get(['id', 'nome']),
        ];
    }

    protected function responsaveis(User $usuario, ?int $cooperativaId = null): Collection
    {
        $query = User::query()
            ->where('ativo', true)
            ->with([
                'cooperativa:id,nome',
                'cooperativas:id,nome',
            ])
            ->orderBy('name');

        if (EscopoCooperativa::isAdmin($usuario)) {
            if ($cooperativaId) {
                $query->where(function (Builder $subQuery) use ($cooperativaId): void {
                    $subQuery
                        ->whereHas('cooperativas', fn (Builder $cooperativaQuery) => $cooperativaQuery->where('cooperativas.id', $cooperativaId))
                        ->orWhere('cooperativa_id', $cooperativaId);
                });
            } else {
                $query->where(function (Builder $subQuery): void {
                    $subQuery->whereHas('cooperativas')
                        ->orWhereNotNull('cooperativa_id');
                });
            }
        } else {
            $cooperativasIds = EscopoCooperativa::cooperativaIds($usuario);
            $this->aplicarEscopoResponsaveis($query, $cooperativasIds);
        }

        return $query->get(['id', 'name', 'cooperativa_id']);
    }

    /**
     * @return Collection<int, Cooperativa>
     */
    protected function cooperativasPermitidas(User $usuario): Collection
    {
        if (EscopoCooperativa::isAdmin($usuario)) {
            return Cooperativa::query()
                ->orderBy('nome')
                ->get(['id', 'nome']);
        }

        $cooperativasIds = EscopoCooperativa::cooperativaIds($usuario);

        if ($cooperativasIds === []) {
            return collect();
        }

        return Cooperativa::query()
            ->whereIn('id', $cooperativasIds)
            ->orderBy('nome')
            ->get(['id', 'nome']);
    }

    /**
     * @param array<int> $cooperativasIds
     */
    protected function aplicarEscopoCooperativas(Builder $query, array $cooperativasIds): void
    {
        if ($cooperativasIds === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('cooperativa_id', $cooperativasIds);
    }

    /**
     * @param array<int> $cooperativasIds
     */
    protected function aplicarEscopoResponsaveis(Builder $query, array $cooperativasIds): void
    {
        if ($cooperativasIds === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function (Builder $subQuery) use ($cooperativasIds): void {
            $subQuery
                ->whereHas('cooperativas', fn (Builder $cooperativaQuery) => $cooperativaQuery->whereIn('cooperativas.id', $cooperativasIds))
                ->orWhereIn('cooperativa_id', $cooperativasIds);
        });
    }
}
