<div
    id="global-confirm-modal"
    class="fixed inset-0 z-[100] hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="global-confirm-modal-title"
    aria-describedby="global-confirm-modal-message"
>
    <div class="absolute inset-0 bg-slate-900/60" data-confirm-modal-close></div>

    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
            <div class="mb-2 flex items-center gap-3">
                <span
                    id="global-confirm-modal-icon"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-rose-100 text-rose-700"
                    aria-hidden="true"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M4.93 19h14.14a2 2 0 001.73-3L13.73 4a2 2 0 00-3.46 0L3.2 16a2 2 0 001.73 3z" />
                    </svg>
                </span>

                <h3 id="global-confirm-modal-title" class="text-base font-semibold text-slate-900">
                    Confirmar ação
                </h3>
            </div>

            <p id="global-confirm-modal-message" class="text-sm text-slate-600">
                Tem certeza de que deseja continuar?
            </p>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                    data-confirm-modal-cancel
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="inline-flex items-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-500"
                    data-confirm-modal-confirm
                >
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>
