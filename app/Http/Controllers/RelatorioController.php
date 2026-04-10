<?php

namespace App\Http\Controllers;

use App\Exports\RelatorioCasosExport;
use App\Models\Caso;
use App\Models\Cooperativa;
use App\Models\TipoStatus;
use App\Models\TipoSubstatus;
use App\Models\User;
use App\Services\RelatorioCasoService;
use App\Support\ControleAcesso;
use App\Support\EscopoCooperativa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Throwable;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RelatorioController extends Controller
{
    public function __construct(
        protected RelatorioCasoService $relatorioCasoService
    ) {
    }

    public function index(Request $request): View
    {
        /** @var User|null $usuario */
        $usuario = $request->user();
        abort_if(! $usuario, 403);
        Gate::authorize('viewAny', Caso::class);
        abort_if(! ControleAcesso::usuarioTemPermissao($usuario, 'relatorios.visualizar'), 403);

        $isAdmin = EscopoCooperativa::isAdmin($usuario);
        $filtros = $this->filtros($request);

        return view('relatorios.index', [
            'isAdmin' => $isAdmin,
            'filtros' => $filtros,
            'cooperativas' => $isAdmin
                ? Cooperativa::query()->orderBy('nome')->get(['id', 'nome'])
                : collect(),
            'tiposStatus' => TipoStatus::query()->orderBy('ordem')->orderBy('nome')->get(['id', 'nome']),
            'tiposSubstatus' => TipoSubstatus::query()->orderBy('ordem')->orderBy('nome')->get(['id', 'nome']),
            'responsaveis' => $this->responsaveis($usuario),
        ]);
    }

    public function exportarExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        /** @var User|null $usuario */
        $usuario = $request->user();
        abort_if(! $usuario, 403);
        Gate::authorize('viewAny', Caso::class);
        abort_if(! ControleAcesso::usuarioTemPermissao($usuario, 'relatorios.exportar'), 403);

        $filtros = $this->filtros($request);
        $nomeArquivo = 'relatorio_casos_'.now()->format('Ymd_His').'.xlsx';

        try {
            return Excel::download(
                new RelatorioCasosExport($this->relatorioCasoService, $filtros, $usuario),
                $nomeArquivo
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('relatorios.index', $filtros)
                ->with('erro', 'Não foi possível gerar o relatório em Excel. Tente novamente.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function filtros(Request $request): array
    {
        return [
            'cooperativa_id' => $request->input('cooperativa_id'),
            'codigo_caso' => $request->input('codigo_caso'),
            'numero_protocolo' => $request->input('numero_protocolo'),
            'numero_prenotacao' => $request->input('numero_prenotacao'),
            'contrato' => $request->input('contrato'),
            'nome' => $request->input('nome'),
            'comarca' => $request->input('comarca'),
            'uf' => $request->input('uf'),
            'tipo_status_id' => $request->input('tipo_status_id'),
            'tipo_substatus_id' => $request->input('tipo_substatus_id'),
            'responsavel_id' => $request->input('responsavel_id'),
            'arquivado' => $request->input('arquivado'),
            'data_prazo_inicial' => $request->input('data_prazo_inicial'),
            'data_prazo_final' => $request->input('data_prazo_final'),
            'data_cadastro_inicial' => $request->input('data_cadastro_inicial'),
            'data_cadastro_final' => $request->input('data_cadastro_final'),
        ];
    }

    protected function responsaveis(User $usuario)
    {
        $query = User::query()
            ->where('ativo', true)
            ->whereNotNull('cooperativa_id')
            ->orderBy('name');

        $cooperativaId = EscopoCooperativa::cooperativaId($usuario);

        if ($cooperativaId !== null) {
            $query->where('cooperativa_id', $cooperativaId);
        }

        return $query->get(['id', 'name']);
    }
}
