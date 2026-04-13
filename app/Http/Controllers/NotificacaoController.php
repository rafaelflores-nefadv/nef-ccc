<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificacaoController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = $request->user();
        $perPage = $this->resolvePerPage($request);

        abort_if(! $usuario, 403);

        $notificacoes = $usuario->notifications()
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('notificacoes.index', [
            'notificacoes' => $notificacoes,
            'naoLidasCount' => $usuario->unreadNotifications()->count(),
            'perPage' => $perPage,
            'perPageOptions' => $this->perPageOptions(),
        ]);
    }

    public function show(Request $request, string $id): View
    {
        $usuario = $request->user();

        abort_if(! $usuario, 403);

        $notificacao = $this->obterNotificacaoDoUsuario($request, $id);

        if ($notificacao->read_at === null) {
            $notificacao->markAsRead();
        }

        return view('notificacoes.show', [
            'notificacao' => $notificacao,
        ]);
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        abort_if(! $usuario, 403);

        $usuario->unreadNotifications()->update([
            'read_at' => now(),
        ]);

        return back()->with('status', 'Todas as notificações foram marcadas como lidas.');
    }

    protected function obterNotificacaoDoUsuario(Request $request, string $id): DatabaseNotification
    {
        $usuario = $request->user();

        abort_if(! $usuario, 403);

        return $usuario->notifications()
            ->whereKey($id)
            ->firstOrFail();
    }
}
