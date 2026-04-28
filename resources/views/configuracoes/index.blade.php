@extends('layouts.app')

@section('title', 'Configurações')

@section('content')
    @php
        $abaInicial = $abaAtiva;

        if ($errors->any()) {
            $camposGeral = [
                'nome_sistema',
                'timezone',
                'email_suporte',
                'login_badge_text',
                'login_title',
                'login_description',
                'rodape_relatorio',
            ];
            $camposEmail = ['driver', 'host', 'porta', 'usuario', 'senha', 'criptografia', 'email_remetente', 'nome_remetente', 'ativo'];
            $camposProvedores = [
                'provedor_id',
                'meta_url_base',
                'meta_token',
                'meta_phone_number_id',
                'meta_business_account_id',
                'meta_api_version',
                'waha_url_base',
                'waha_token',
                'waha_instancia',
                'numero',
                'mensagem',
            ];
            $camposNotificacoes = [
                'canal_email_ativo',
                'canal_whatsapp_ativo',
                'notificar_prazo_vencendo',
                'dias_antes_prazo',
                'notificar_prazo_vencido',
                'notificar_leilao',
                'notificar_novo_andamento',
            ];

            if (collect($camposProvedores)->contains(fn (string $campo): bool => $errors->has($campo))) {
                $abaInicial = 'provedores';
            } elseif (collect($camposNotificacoes)->contains(fn (string $campo): bool => $errors->has($campo))) {
                $abaInicial = 'notificacoes';
            } elseif (collect($camposEmail)->contains(fn (string $campo): bool => $errors->has($campo))) {
                $abaInicial = 'email';
            } elseif (collect($camposGeral)->contains(fn (string $campo): bool => $errors->has($campo))) {
                $abaInicial = 'geral';
            }
        }
    @endphp

    <div class="py-2">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8" x-data="{ aba: @js($abaInicial) }">
            <div class="rounded-xl bg-white shadow-sm">
                <div class="border-b border-gray-200 p-4">
                    <h2 class="text-lg font-semibold text-slate-900">Configurações do sistema</h2>
                    <p class="mt-1 text-sm text-slate-600">Gerencie parâmetros gerais, e-mail, WhatsApp e notificações.</p>
                </div>

                <div class="overflow-x-auto border-b border-gray-200 px-4">
                    <div class="flex min-w-max items-center gap-2 py-3">
                        <button type="button" @click="aba = 'geral'" :class="aba === 'geral' ? 'bg-blue-100 text-blue-800' : 'text-slate-600 hover:bg-slate-100'" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                            Geral
                        </button>
                        <button type="button" @click="aba = 'email'" :class="aba === 'email' ? 'bg-blue-100 text-blue-800' : 'text-slate-600 hover:bg-slate-100'" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 6.75A2.25 2.25 0 015.25 4.5h13.5A2.25 2.25 0 0121 6.75v10.5A2.25 2.25 0 0118.75 19.5H5.25A2.25 2.25 0 013 17.25V6.75zm0 0L12 12.75l9-6" /></svg>
                            E-mail
                        </button>
                        <button type="button" @click="aba = 'provedores'" :class="aba === 'provedores' ? 'bg-blue-100 text-blue-800' : 'text-slate-600 hover:bg-slate-100'" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 7.5h15m-15 4.5h15m-15 4.5h9" /></svg>
                            WhatsApp / Provedores
                        </button>
                        <button type="button" @click="aba = 'notificacoes'" :class="aba === 'notificacoes' ? 'bg-blue-100 text-blue-800' : 'text-slate-600 hover:bg-slate-100'" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.25 18.75a2.25 2.25 0 01-4.5 0m6.189-8.737A6 6 0 106.06 10.5c0 2.18-.784 3.85-1.56 4.901A1.5 1.5 0 005.71 17.75h12.58a1.5 1.5 0 001.21-2.349c-.775-1.05-1.56-2.72-1.56-4.9z" /></svg>
                            Notificações
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div x-show="aba === 'geral'" x-cloak>
                        @include('configuracoes._aba_geral')
                    </div>

                    <div x-show="aba === 'email'" x-cloak>
                        @include('configuracoes._aba_email')
                    </div>

                    <div x-show="aba === 'provedores'" x-cloak>
                        @include('configuracoes._aba_provedores')
                    </div>

                    <div x-show="aba === 'notificacoes'" x-cloak>
                        @include('configuracoes._aba_notificacoes')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const CONFIG_ASYNC_FINALIZADOS = ['sucesso', 'falha'];

        function csrfHeaders(token) {
            return {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token,
            };
        }

        function parseErroPadrao(error) {
            if (error && typeof error.message === 'string' && error.message.trim() !== '') {
                return error.message;
            }

            return 'Não foi possível concluir a operação. Tente novamente.';
        }

        window.emailTester = function ({ testeUrl, statusUrlTemplate, csrfToken }) {
            return {
                loading: false,
                status: '',
                mensagem: '',
                pollTimer: null,
                async testarConfiguracao() {
                    this.loading = true;
                    this.status = 'pendente';
                    this.mensagem = 'Solicitação enviada. Aguardando processamento...';

                    try {
                        const body = new FormData(this.$refs.form);
                        body.delete('_method');

                        const response = await fetch(testeUrl, {
                            method: 'POST',
                            headers: csrfHeaders(csrfToken),
                            body,
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(this.mensagemErroValidacao(data));
                        }

                        this.status = data.status || 'pendente';
                        this.mensagem = data.mensagem || 'Teste agendado.';

                        if (!CONFIG_ASYNC_FINALIZADOS.includes(this.status)) {
                            this.iniciarPolling(data.token);
                        }
                    } catch (error) {
                        this.status = 'falha';
                        this.mensagem = parseErroPadrao(error);
                    } finally {
                        this.loading = false;
                    }
                },
                iniciarPolling(token) {
                    if (!token) {
                        return;
                    }

                    if (this.pollTimer) {
                        clearInterval(this.pollTimer);
                    }

                    const statusUrl = statusUrlTemplate.replace('__TOKEN__', encodeURIComponent(token));
                    let tentativas = 0;

                    this.pollTimer = setInterval(async () => {
                        tentativas += 1;

                        try {
                            const response = await fetch(statusUrl, {
                                method: 'GET',
                                headers: csrfHeaders(csrfToken),
                            });

                            if (!response.ok) {
                                throw new Error('Não foi possível consultar o status da tarefa.');
                            }

                            const data = await response.json();
                            this.status = data.status || 'processando';
                            this.mensagem = data.mensagem || 'Processando...';

                            if (CONFIG_ASYNC_FINALIZADOS.includes(this.status) || tentativas >= 40) {
                                clearInterval(this.pollTimer);
                                this.pollTimer = null;
                            }
                        } catch (error) {
                            clearInterval(this.pollTimer);
                            this.pollTimer = null;
                            this.status = 'falha';
                            this.mensagem = parseErroPadrao(error);
                        }
                    }, 1500);
                },
                mensagemErroValidacao(data) {
                    if (!data || typeof data !== 'object') {
                        return 'Não foi possível validar os dados para o teste.';
                    }

                    if (typeof data.mensagem === 'string') {
                        return data.mensagem;
                    }

                    if (typeof data.message === 'string') {
                        return data.message;
                    }

                    if (data.errors && typeof data.errors === 'object') {
                        const primeiraChave = Object.keys(data.errors)[0];
                        const primeiroErro = primeiraChave ? data.errors[primeiraChave]?.[0] : null;
                        if (primeiroErro) {
                            return primeiroErro;
                        }
                    }

                    return 'Não foi possível validar os dados para o teste.';
                },
            };
        };

        window.provedorTester = function ({
            csrfToken,
            testeConectividadeUrl,
            testeMensagemUrl,
            statusUrlTemplate,
            initialForm,
        }) {
            return {
                loadingConectividade: false,
                loadingEnvio: false,
                modalAberto: false,
                statusAtual: '',
                statusMensagem: '',
                pollTimer: null,
                provedorId: Number(initialForm.provedor_id || 0),
                provedorSlug: initialForm.provedor_slug || 'meta',
                numeroTeste: '',
                mensagemTeste: 'Mensagem de teste enviada pelo sistema.',
                form: {
                    meta_url_base: initialForm.meta_url_base || 'https://graph.facebook.com',
                    meta_token: initialForm.meta_token || '',
                    meta_phone_number_id: initialForm.meta_phone_number_id || '',
                    meta_business_account_id: initialForm.meta_business_account_id || '',
                    meta_api_version: initialForm.meta_api_version || 'v20.0',
                    waha_url_base: initialForm.waha_url_base || '',
                    waha_token: initialForm.waha_token || '',
                    waha_instancia: initialForm.waha_instancia || '',
                },
                sincronizarSlug() {
                    const select = document.getElementById('provedor_id');
                    const option = select?.selectedOptions?.[0];
                    const slug = option?.dataset?.slug || '';

                    this.provedorSlug = slug || 'meta';
                },
                classeStatus() {
                    if (this.statusAtual === 'sucesso') {
                        return 'border-emerald-300 bg-emerald-50 text-emerald-700';
                    }
                    if (this.statusAtual === 'falha') {
                        return 'border-rose-300 bg-rose-50 text-rose-700';
                    }
                    return 'border-slate-300 bg-slate-50 text-slate-700';
                },
                async testarConectividade() {
                    this.loadingConectividade = true;
                    this.statusAtual = 'pendente';
                    this.statusMensagem = 'Teste de conectividade agendado.';

                    try {
                        const response = await fetch(testeConectividadeUrl, {
                            method: 'POST',
                            headers: csrfHeaders(csrfToken),
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(this.mensagemErroValidacao(data));
                        }

                        this.statusAtual = data.status || 'pendente';
                        this.statusMensagem = data.mensagem || 'Teste de conectividade agendado.';
                        this.iniciarPolling(data.token);
                    } catch (error) {
                        this.statusAtual = 'falha';
                        this.statusMensagem = parseErroPadrao(error);
                    } finally {
                        this.loadingConectividade = false;
                    }
                },
                abrirModalTesteEnvio() {
                    this.modalAberto = true;
                },
                fecharModalTesteEnvio() {
                    this.modalAberto = false;
                },
                async testarEnvio() {
                    this.loadingEnvio = true;
                    this.statusAtual = 'pendente';
                    this.statusMensagem = 'Teste de envio agendado.';

                    try {
                        const body = new FormData();
                        body.append('numero', this.numeroTeste || '');
                        body.append('mensagem', this.mensagemTeste || '');

                        const response = await fetch(testeMensagemUrl, {
                            method: 'POST',
                            headers: csrfHeaders(csrfToken),
                            body,
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(this.mensagemErroValidacao(data));
                        }

                        this.statusAtual = data.status || 'pendente';
                        this.statusMensagem = data.mensagem || 'Teste de envio agendado.';
                        this.iniciarPolling(data.token);
                        this.modalAberto = false;
                    } catch (error) {
                        this.statusAtual = 'falha';
                        this.statusMensagem = parseErroPadrao(error);
                    } finally {
                        this.loadingEnvio = false;
                    }
                },
                iniciarPolling(token) {
                    if (!token) {
                        return;
                    }

                    if (this.pollTimer) {
                        clearInterval(this.pollTimer);
                    }

                    const statusUrl = statusUrlTemplate.replace('__TOKEN__', encodeURIComponent(token));
                    let tentativas = 0;

                    this.pollTimer = setInterval(async () => {
                        tentativas += 1;

                        try {
                            const response = await fetch(statusUrl, {
                                method: 'GET',
                                headers: csrfHeaders(csrfToken),
                            });

                            if (!response.ok) {
                                throw new Error('Não foi possível consultar o status da tarefa.');
                            }

                            const data = await response.json();
                            this.statusAtual = data.status || 'processando';
                            this.statusMensagem = data.mensagem || 'Processando...';

                            if (CONFIG_ASYNC_FINALIZADOS.includes(this.statusAtual) || tentativas >= 40) {
                                clearInterval(this.pollTimer);
                                this.pollTimer = null;
                            }
                        } catch (error) {
                            clearInterval(this.pollTimer);
                            this.pollTimer = null;
                            this.statusAtual = 'falha';
                            this.statusMensagem = parseErroPadrao(error);
                        }
                    }, 1500);
                },
                mensagemErroValidacao(data) {
                    if (!data || typeof data !== 'object') {
                        return 'Não foi possível validar os dados informados.';
                    }

                    if (typeof data.mensagem === 'string') {
                        return data.mensagem;
                    }

                    if (data.errors && typeof data.errors === 'object') {
                        const primeiraChave = Object.keys(data.errors)[0];
                        const primeiroErro = primeiraChave ? data.errors[primeiraChave]?.[0] : null;
                        if (primeiroErro) {
                            return primeiroErro;
                        }
                    }

                    return 'Não foi possível validar os dados informados.';
                },
            };
        };
    </script>
@endsection
