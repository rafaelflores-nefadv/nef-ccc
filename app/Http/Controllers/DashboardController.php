<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Support\ControleAcesso;
use App\Support\EscopoCooperativa;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {
    }

    public function index(Request $request): View
    {
        $usuario = $request->user();

        abort_if(! $usuario, 403);
        abort_if(! ControleAcesso::usuarioTemPermissao($usuario, 'dashboard.visualizar'), 403);

        $usuario->loadMissing([
            'cooperativas:id,nome',
            'cooperativa:id,nome',
        ]);

        $cooperativasUsuario = $usuario->cooperativas->pluck('nome')->filter()->values();

        if ($cooperativasUsuario->isEmpty() && $usuario->cooperativa?->nome) {
            $cooperativasUsuario = collect([$usuario->cooperativa->nome]);
        }

        $cooperativa = EscopoCooperativa::isAdmin($usuario)
            ? 'Todas as cooperativas'
            : ($cooperativasUsuario->isEmpty() ? 'Nao vinculada' : $cooperativasUsuario->join(', '));

        return view('dashboard', [
            'usuario' => $usuario,
            'cooperativa' => $cooperativa,
            ...$this->dashboardService->dadosDashboard($usuario),
        ]);
    }
}
