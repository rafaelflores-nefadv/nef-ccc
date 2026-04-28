<?php

use App\Http\Controllers\AndamentoCasoController;
use App\Http\Controllers\AtualizacaoController;
use App\Http\Controllers\CasoController;
use App\Http\Controllers\CooperativaController;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeriadoSuspensaoController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\PapelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'active.user'])
    ->name('dashboard');

Route::middleware(['auth', 'active.user'])->group(function () {
    Route::prefix('casos')->name('casos.')->group(function () {
        Route::get('/', [CasoController::class, 'index'])->name('index');
        Route::get('/criar', [CasoController::class, 'create'])->name('create');
        Route::post('/', [CasoController::class, 'store'])->name('store');
        Route::get('/{caso}/andamentos', [AndamentoCasoController::class, 'index'])->name('andamentos.index');
        Route::post('/{caso}/andamentos', [AndamentoCasoController::class, 'store'])->name('andamentos.store');
        Route::get('/{caso}', [CasoController::class, 'show'])->name('show');
        Route::get('/{caso}/editar', [CasoController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{caso}', [CasoController::class, 'update'])->name('update');
        Route::delete('/{caso}', [CasoController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('feriados-suspensoes')->name('feriados_suspensoes.')->group(function () {
        Route::get('/', [FeriadoSuspensaoController::class, 'index'])->name('index');
        Route::get('/criar', [FeriadoSuspensaoController::class, 'create'])->name('create');
        Route::post('/', [FeriadoSuspensaoController::class, 'store'])->name('store');
        Route::get('/{feriadoSuspensao}/editar', [FeriadoSuspensaoController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{feriadoSuspensao}', [FeriadoSuspensaoController::class, 'update'])->name('update');
        Route::delete('/{feriadoSuspensao}', [FeriadoSuspensaoController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('relatorios')->name('relatorios.')->group(function () {
        Route::get('/', [RelatorioController::class, 'index'])->name('index');
        Route::get('/exportar/excel', [RelatorioController::class, 'exportarExcel'])->name('exportar.excel');
    });

    Route::prefix('atualizacao')->name('atualizacao.')->group(function () {
        Route::get('/', [AtualizacaoController::class, 'index'])->name('index');
        Route::get('/status/{execucao}', [AtualizacaoController::class, 'status'])->name('status');
        Route::get('/logs/{execucao}', [AtualizacaoController::class, 'logs'])->name('logs');
    });

    Route::prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('index');
        Route::get('/criar', [UsuarioController::class, 'create'])->name('create');
        Route::post('/', [UsuarioController::class, 'store'])->name('store');
        Route::get('/{user}/editar', [UsuarioController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{user}', [UsuarioController::class, 'update'])->name('update');
        Route::patch('/{user}/status', [UsuarioController::class, 'atualizarStatus'])->name('status');
        Route::delete('/{user}', [UsuarioController::class, 'destroy'])->name('destroy');
        Route::get('/{user}/senha', [UsuarioController::class, 'editSenha'])->name('senha.edit');
        Route::patch('/{user}/senha', [UsuarioController::class, 'updateSenha'])->name('senha.update');
    });

    Route::prefix('cooperativas')->name('cooperativas.')->group(function () {
        Route::get('/', [CooperativaController::class, 'index'])->name('index');
        Route::get('/criar', [CooperativaController::class, 'create'])->name('create');
        Route::post('/', [CooperativaController::class, 'store'])->name('store');
        Route::get('/{cooperativa}/editar', [CooperativaController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{cooperativa}', [CooperativaController::class, 'update'])->name('update');
        Route::patch('/{cooperativa}/status', [CooperativaController::class, 'atualizarStatus'])->name('status');
    });

    Route::prefix('papeis')->name('papeis.')->group(function () {
        Route::get('/', [PapelController::class, 'index'])->name('index');
        Route::get('/criar', [PapelController::class, 'create'])->name('create');
        Route::post('/', [PapelController::class, 'store'])->name('store');
        Route::get('/{papel}/editar', [PapelController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{papel}', [PapelController::class, 'update'])->name('update');
        Route::patch('/{papel}/status', [PapelController::class, 'atualizarStatus'])->name('status');
    });

    Route::prefix('configuracoes')->name('configuracoes.')->group(function () {
        Route::get('/', [ConfiguracaoController::class, 'index'])->name('index');
        Route::patch('/geral', [ConfiguracaoController::class, 'updateGeral'])->name('geral.update');
        Route::patch('/email', [ConfiguracaoController::class, 'updateEmail'])->name('email.update');
        Route::post('/email/teste', [ConfiguracaoController::class, 'testarEmail'])->name('email.teste');
        Route::patch('/notificacoes', [ConfiguracaoController::class, 'updateNotificacoes'])->name('notificacoes.update');
        Route::patch('/provedores', [ConfiguracaoController::class, 'salvarProvedorMensagem'])->name('provedores.update');
        Route::post('/provedores/teste-conectividade', [ConfiguracaoController::class, 'testarConectividadeProvedor'])->name('provedores.teste.conectividade');
        Route::post('/provedores/teste-mensagem', [ConfiguracaoController::class, 'testarMensagemProvedor'])->name('provedores.teste.mensagem');
        Route::get('/tarefas/{token}', [ConfiguracaoController::class, 'statusTarefaAssincrona'])->name('tarefas.status');
    });

    Route::prefix('notificacoes')->name('notificacoes.')->group(function () {
        Route::get('/', [NotificacaoController::class, 'index'])->name('index');
        Route::patch('/marcar-todas-como-lidas', [NotificacaoController::class, 'markAllAsRead'])->name('markAllAsRead');
        Route::get('/{id}', [NotificacaoController::class, 'show'])->name('show');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
