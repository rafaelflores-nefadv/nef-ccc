<?php

namespace App\Http\Controllers;

use App\Http\Requests\TestarMensagemProvedorRequest;
use App\Http\Requests\UpdateConfiguracaoEmailRequest;
use App\Http\Requests\UpdateConfiguracaoGeralRequest;
use App\Http\Requests\UpdateConfiguracaoNotificacaoRequest;
use App\Http\Requests\UpdateConfiguracaoProvedorMensagemRequest;
use App\Jobs\ProcessarEnvioMensagemTesteProvedorJob;
use App\Jobs\ProcessarTesteConfiguracaoEmailJob;
use App\Jobs\ProcessarTesteConectividadeProvedorJob;
use App\Models\ConfiguracaoEmail;
use App\Models\ConfiguracaoGeral;
use App\Models\ConfiguracaoNotificacao;
use App\Models\User;
use App\Services\ConfiguracaoAsyncTaskService;
use App\Services\ProvedorWhatsappService;
use App\Support\ConfiguracaoSistema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ConfiguracaoController extends Controller
{
    public function __construct(
        protected ProvedorWhatsappService $provedorWhatsappService
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', ConfiguracaoGeral::class);

        $this->provedorWhatsappService->garantirProvedoresSuportados();

        $configuracaoGeral = $this->configuracaoGeral();
        $configuracaoEmail = $this->configuracaoEmail();
        $configuracaoNotificacao = $this->configuracaoNotificacao();
        $configuracaoProvedorWhatsapp = $this->provedorWhatsappService->obterConfiguracaoUnica(false);
        $destinatarios = $this->destinatariosNotificacaoAutomaticos();

        $aba = (string) $request->query('aba', 'geral');
        $abasValidas = ['geral', 'email', 'provedores', 'notificacoes'];
        $oldInput = $request->session()->getOldInput();

        return view('configuracoes.index', [
            'abaAtiva' => in_array($aba, $abasValidas, true) ? $aba : 'geral',
            'configuracaoGeral' => $configuracaoGeral,
            'configuracaoEmail' => $configuracaoEmail,
            'configuracaoNotificacao' => $configuracaoNotificacao,
            'timezonesBrasil' => ConfiguracaoSistema::timezonesBrasil(),
            'provedores' => $this->provedorWhatsappService->listaProvedoresSuportados(),
            'configuracaoProvedorWhatsapp' => $configuracaoProvedorWhatsapp,
            'provedorWhatsappForm' => $this->provedorWhatsappService->dadosFormulario($oldInput, $configuracaoProvedorWhatsapp),
            'totalDestinatariosNotificacao' => count($destinatarios['usuarios_destino_json']),
        ]);
    }

    public function updateGeral(UpdateConfiguracaoGeralRequest $request): RedirectResponse
    {
        Gate::authorize('viewAny', ConfiguracaoGeral::class);

        try {
            $this->configuracaoGeral()->update($request->validated());
        } catch (Throwable $exception) {
            Log::error('Falha ao salvar configurações gerais.', [
                'user_id' => $request->user()?->id,
                'dados' => $request->safe()->only([
                    'nome_sistema',
                    'timezone',
                    'email_suporte',
                    'login_badge_text',
                    'login_title',
                    'login_description',
                    'rodape_relatorio',
                ]),
                'exception' => $exception,
            ]);

            return back()
                ->withInput()
                ->with('erro', 'Não foi possível salvar as configurações gerais.');
        }

        return redirect()
            ->route('configuracoes.index', ['aba' => 'geral'])
            ->with('status', 'Configurações gerais salvas com sucesso.');
    }

    public function updateEmail(UpdateConfiguracaoEmailRequest $request): RedirectResponse
    {
        Gate::authorize('viewAny', ConfiguracaoGeral::class);

        try {
            $configuracao = $this->configuracaoEmail();
            $dados = $request->validated();

            if (! $request->filled('senha')) {
                unset($dados['senha']);
            }

            $configuracao->update($dados);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('erro', 'Não foi possível salvar as configurações de e-mail.');
        }

        return redirect()
            ->route('configuracoes.index', ['aba' => 'email'])
            ->with('status', 'Configurações de e-mail salvas com sucesso.');
    }

    public function testarEmail(
        UpdateConfiguracaoEmailRequest $request,
        ConfiguracaoAsyncTaskService $taskService
    ): JsonResponse {
        Gate::authorize('viewAny', ConfiguracaoGeral::class);

        $dados = $request->validated();
        $configuracaoAtual = $this->configuracaoEmail();

        if (! $request->filled('senha')) {
            $dados['senha'] = $configuracaoAtual->senha;
        }

        $destinatario = (string) ($request->user()?->email ?: $dados['email_remetente']);

        if ($destinatario === '') {
            return response()->json([
                'status' => ConfiguracaoAsyncTaskService::STATUS_FALHA,
                'mensagem' => 'Não foi possível identificar o destinatário do e-mail de teste.',
            ], 422);
        }

        $tarefa = $taskService->criar('teste_email', (int) $request->user()->id, 'Teste de e-mail agendado.');

        ProcessarTesteConfiguracaoEmailJob::dispatchSync(
            (string) $tarefa['token'],
            $dados,
            $destinatario
        );

        $tarefaAtualizada = $taskService->obter((string) $tarefa['token'], (int) $request->user()->id) ?? $tarefa;

        return response()->json([
            'token' => $tarefaAtualizada['token'],
            'status' => $tarefaAtualizada['status'],
            'mensagem' => $tarefaAtualizada['mensagem'],
            'resultado' => $tarefaAtualizada['resultado'] ?? [],
        ], 200);
    }

    public function updateNotificacoes(UpdateConfiguracaoNotificacaoRequest $request): RedirectResponse
    {
        Gate::authorize('viewAny', ConfiguracaoGeral::class);

        try {
            $dados = $request->validated();
            $destinatarios = $this->destinatariosNotificacaoAutomaticos();

            $dados['emails_destino_json'] = $destinatarios['emails_destino_json'];
            $dados['usuarios_destino_json'] = $destinatarios['usuarios_destino_json'];

            $this->configuracaoNotificacao()->update($dados);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('erro', 'Não foi possível salvar as configurações de notificações.');
        }

        return redirect()
            ->route('configuracoes.index', ['aba' => 'notificacoes'])
            ->with('status', 'Configurações de notificações salvas com sucesso.');
    }

    public function salvarProvedorMensagem(UpdateConfiguracaoProvedorMensagemRequest $request): RedirectResponse
    {
        Gate::authorize('viewAny', ConfiguracaoGeral::class);

        try {
            $this->provedorWhatsappService->salvarConfiguracaoUnica($request->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('erro', 'Não foi possível salvar a configuração do provedor de WhatsApp.');
        }

        return redirect()
            ->route('configuracoes.index', ['aba' => 'provedores'])
            ->with('status', 'Configuração do provedor de WhatsApp salva com sucesso.');
    }

    public function testarConectividadeProvedor(
        Request $request,
        ConfiguracaoAsyncTaskService $taskService
    ): JsonResponse {
        Gate::authorize('viewAny', ConfiguracaoGeral::class);

        $configuracao = $this->provedorWhatsappService->obterConfiguracaoUnica(false);

        if (! $configuracao) {
            return response()->json([
                'status' => ConfiguracaoAsyncTaskService::STATUS_FALHA,
                'mensagem' => 'Salve a configuração do provedor antes de testar a conectividade.',
            ], 422);
        }

        $tarefa = $taskService->criar(
            'teste_conectividade_provedor',
            (int) $request->user()->id,
            'Teste de conectividade agendado.'
        );

        ProcessarTesteConectividadeProvedorJob::dispatchAfterResponse(
            (string) $tarefa['token'],
            (int) $configuracao->id
        );

        return response()->json([
            'token' => $tarefa['token'],
            'status' => $tarefa['status'],
            'mensagem' => $tarefa['mensagem'],
        ], 202);
    }

    public function testarMensagemProvedor(
        TestarMensagemProvedorRequest $request,
        ConfiguracaoAsyncTaskService $taskService
    ): JsonResponse {
        Gate::authorize('viewAny', ConfiguracaoGeral::class);

        $configuracao = $this->provedorWhatsappService->obterConfiguracaoUnica(false);

        if (! $configuracao) {
            return response()->json([
                'status' => ConfiguracaoAsyncTaskService::STATUS_FALHA,
                'mensagem' => 'Salve a configuração do provedor antes de testar o envio.',
            ], 422);
        }

        $dados = $request->validated();

        $tarefa = $taskService->criar(
            'teste_mensagem_provedor',
            (int) $request->user()->id,
            'Teste de envio agendado.'
        );

        ProcessarEnvioMensagemTesteProvedorJob::dispatchAfterResponse(
            (string) $tarefa['token'],
            (int) $configuracao->id,
            (string) $dados['numero'],
            (string) $dados['mensagem']
        );

        return response()->json([
            'token' => $tarefa['token'],
            'status' => $tarefa['status'],
            'mensagem' => $tarefa['mensagem'],
        ], 202);
    }

    public function statusTarefaAssincrona(
        Request $request,
        string $token,
        ConfiguracaoAsyncTaskService $taskService
    ): JsonResponse {
        Gate::authorize('viewAny', ConfiguracaoGeral::class);

        $userId = (int) ($request->user()?->id ?? 0);
        $tarefa = $taskService->obter($token, $userId);

        if (! $tarefa) {
            return response()->json([
                'mensagem' => 'Tarefa não encontrada.',
            ], 404);
        }

        return response()->json([
            'token' => $tarefa['token'],
            'status' => $tarefa['status'],
            'mensagem' => $tarefa['mensagem'],
            'resultado' => $tarefa['resultado'],
            'finalizado_em' => $tarefa['finalizado_em'],
        ]);
    }

    protected function configuracaoGeral(): ConfiguracaoGeral
    {
        return ConfiguracaoGeral::query()->firstOrCreate(
            ['id' => 1],
            [
                'nome_sistema' => config('app.name', 'Sistema'),
                'timezone' => config('app.timezone', 'America/Cuiaba'),
                'email_suporte' => null,
                'logo_path' => null,
                'login_badge_text' => null,
                'login_title' => null,
                'login_description' => null,
                'rodape_relatorio' => null,
            ]
        );
    }

    protected function configuracaoEmail(): ConfiguracaoEmail
    {
        return ConfiguracaoEmail::query()->firstOrCreate(
            ['id' => 1],
            [
                'driver' => 'smtp',
                'host' => 'smtp.exemplo.com',
                'porta' => 587,
                'usuario' => 'usuario@exemplo.com',
                'senha' => null,
                'criptografia' => 'tls',
                'email_remetente' => 'naoresponda@exemplo.com',
                'nome_remetente' => config('app.name', 'Sistema'),
                'ativo' => false,
            ]
        );
    }

    protected function configuracaoNotificacao(): ConfiguracaoNotificacao
    {
        return ConfiguracaoNotificacao::query()->firstOrCreate(
            ['id' => 1],
            [
                'canal_email_ativo' => false,
                'canal_whatsapp_ativo' => false,
                'notificar_prazo_vencendo' => false,
                'dias_antes_prazo' => 1,
                'notificar_prazo_vencido' => false,
                'notificar_leilao' => false,
                'notificar_novo_andamento' => false,
                'emails_destino_json' => [],
                'usuarios_destino_json' => [],
            ]
        );
    }

    /**
     * @return array{emails_destino_json: list<string>, usuarios_destino_json: list<int>}
     */
    protected function destinatariosNotificacaoAutomaticos(): array
    {
        $usuariosAtivos = User::query()
            ->where('ativo', true)
            ->whereNotNull('email')
            ->orderBy('name')
            ->get(['id', 'email']);

        $emails = $usuariosAtivos
            ->pluck('email')
            ->filter(fn ($email): bool => is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->map(fn (string $email): string => mb_strtolower(trim($email)))
            ->unique()
            ->values()
            ->all();

        $ids = $usuariosAtivos
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return [
            'emails_destino_json' => $emails,
            'usuarios_destino_json' => $ids,
        ];
    }
}
