<?php

namespace App\Http\Controllers;

use App\Models\RobotExecucao;
use App\Models\RobotExecucaoLog;
use App\Support\ControleAcesso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AtualizacaoController extends Controller
{
    public function index(Request $request): View
    {
        $this->autorizar($request);

        $execucao = RobotExecucao::query()
            ->orderByDesc('id')
            ->first();

        $logs = $execucao
            ? $execucao->logs()
                ->orderByDesc('id')
                ->limit(200)
                ->get()
                ->sortBy('id')
                ->values()
            : collect();

        return view('atualizacao.index', [
            'execucao' => $execucao,
            'logs' => $logs,
        ]);
    }

    public function status(Request $request, RobotExecucao $execucao): JsonResponse
    {
        $this->autorizar($request);

        $execucao->refresh();

        return response()->json([
            'execucao' => $this->serializeExecucao($execucao),
        ]);
    }

    public function logs(Request $request, RobotExecucao $execucao): JsonResponse
    {
        $this->autorizar($request);

        $afterId = max((int) $request->query('after_id', 0), 0);
        $limit = min(max((int) $request->query('limit', 200), 1), 500);

        $query = RobotExecucaoLog::query()->where('robot_execucao_id', $execucao->id);

        if ($afterId > 0) {
            $logs = $query
                ->where('id', '>', $afterId)
                ->orderBy('id')
                ->limit($limit)
                ->get();
        } else {
            $logs = $query
                ->orderByDesc('id')
                ->limit($limit)
                ->get()
                ->sortBy('id')
                ->values();
        }

        return response()->json([
            'logs' => $logs->map(fn (RobotExecucaoLog $log): array => $this->serializeLog($log))->values(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeExecucao(RobotExecucao $execucao): array
    {
        return [
            'id' => $execucao->id,
            'robot_nome' => $execucao->robot_nome,
            'status' => $execucao->status,
            'arquivo_origem' => $execucao->arquivo_origem,
            'relatorio_id' => $execucao->relatorio_id,
            'total_linhas' => (int) $execucao->total_linhas,
            'linhas_processadas' => (int) $execucao->linhas_processadas,
            'linhas_inseridas' => (int) $execucao->linhas_inseridas,
            'linhas_atualizadas' => (int) $execucao->linhas_atualizadas,
            'linhas_ignoradas' => (int) $execucao->linhas_ignoradas,
            'linhas_com_erro' => (int) $execucao->linhas_com_erro,
            'percentual' => (float) $execucao->percentual,
            'mensagem_status' => $execucao->mensagem_status,
            'iniciado_em' => $execucao->iniciado_em?->toIso8601String(),
            'iniciado_em_formatado' => $execucao->iniciado_em?->format('d/m/Y H:i:s'),
            'finalizado_em' => $execucao->finalizado_em?->toIso8601String(),
            'finalizado_em_formatado' => $execucao->finalizado_em?->format('d/m/Y H:i:s'),
            'updated_at' => $execucao->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeLog(RobotExecucaoLog $log): array
    {
        return [
            'id' => $log->id,
            'nivel' => $log->nivel,
            'mensagem' => $log->mensagem,
            'contexto_json' => $log->contexto_json,
            'created_at' => $log->created_at?->toIso8601String(),
            'created_at_formatado' => $log->created_at?->format('d/m/Y H:i:s'),
        ];
    }

    protected function autorizar(Request $request): void
    {
        $usuario = $request->user();

        abort_unless(
            $usuario &&
                (
                    $usuario->isAdmin() ||
                    ControleAcesso::usuarioTemAlgumaPermissao($usuario, ['feriados.visualizar', 'feriados.gerenciar'])
                ),
            403
        );
    }
}
