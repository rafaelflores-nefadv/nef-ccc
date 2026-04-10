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

        $cooperativa = EscopoCooperativa::isAdmin($usuario)
            ? 'Todas as cooperativas'
            : ($usuario?->cooperativa?->nome ?? 'Não vinculada');

        return view('dashboard', [
            'usuario' => $usuario,
            'cooperativa' => $cooperativa,
            ...$this->dashboardService->dadosDashboard($usuario),
        ]);
    }
}
