<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAndamentoCasoRequest;
use App\Models\AndamentoCaso;
use App\Models\Caso;
use App\Models\TipoStatus;
use App\Models\TipoSubstatus;
use App\Services\AtualizadorCasoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;
use Illuminate\View\View;

class AndamentoCasoController extends Controller
{
    public function __construct(
        protected AtualizadorCasoService $atualizadorCasoService
    ) {
    }

    public function index(Caso $caso): View
    {
        Gate::authorize('viewAny', [AndamentoCaso::class, $caso]);

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
        ]);
    }

    public function store(StoreAndamentoCasoRequest $request, Caso $caso): RedirectResponse
    {
        Gate::authorize('create', [AndamentoCaso::class, $caso]);

        $dadosAndamento = $request->validated();

        try {
            DB::transaction(function () use ($request, $caso, $dadosAndamento): void {
                AndamentoCaso::query()->create([
                    'caso_id' => $caso->id,
                    'usuario_id' => $request->user()->id,
                    'data_descricao' => $dadosAndamento['data_andamento'],
                    'tipo_status_id' => $dadosAndamento['tipo_status_id'],
                    'data_alteracao_status' => $dadosAndamento['data_andamento'],
                    'tipo_substatus_id' => $dadosAndamento['tipo_substatus_id'],
                    'data_alteracao_substatus' => $dadosAndamento['data_andamento'],
                    'descricao' => $dadosAndamento['descricao'],
                    'observacoes' => $dadosAndamento['observacoes'] ?? null,
                    'data_prazo' => $dadosAndamento['data_prazo'] ?? null,
                    'data_primeiro_leilao' => $dadosAndamento['data_primeiro_leilao'] ?? null,
                    'data_segundo_leilao' => $dadosAndamento['data_segundo_leilao'] ?? null,
                ]);

                $this->atualizadorCasoService->atualizarAposAndamento($caso, $dadosAndamento);
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('erro', 'Não foi possível registrar o andamento. Verifique os dados informados.');
        }

        return redirect()
            ->to(route('casos.show', $caso).'#andamentos')
            ->with('status', 'Andamento registrado com sucesso.');
    }
}
