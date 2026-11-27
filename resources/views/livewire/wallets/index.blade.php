<div>
    <!-- Top Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Tabungan & Pundi Liburan</h1>
            <p class="text-xs text-[#9CA3AF]">Kelola pos tabungan, target liburan, dan alokasi simpanan keuangan Anda.</p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <button
                type="button"
                wire:click="openTransferModal"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#17181E] hover:bg-[#1E2028] border border-[#262C37] hover:border-[#5865F2] text-xs font-semibold text-white transition-all cursor-pointer"
            >
                <svg class="w-4 h-4 text-[#818CF8]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
                <span>Transfer / Cairkan</span>
            </button>

            <button
                type="button"
                wire:click="openDepositModal"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#00E676]/15 hover:bg-[#00E676]/25 border border-[#00E676]/40 text-xs font-semibold text-[#00E676] transition-all cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Nabung Cepat</span>
            </button>

            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-[#5865F2] to-[#4F46E5] hover:from-[#4F46E5] hover:to-[#4338CA] text-xs font-semibold text-white shadow-lg shadow-[#5865F2]/25 transition-all cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Target Baru</span>
            </button>
        </div>
    </div>

    <!-- Summary Overview Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-4">
            <span class="text-xs text-[#9CA3AF] font-medium block mb-1">Total Tabungan & Liburan</span>
            <span class="text-xl font-bold text-[#00E676] font-mono-numbers block">
                Rp {{ number_format($totalSavings, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-[#9CA3AF] mt-1 block">Terkumpul dari semua pos</span>
        </div>

        <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-4">
            <span class="text-xs text-[#9CA3AF] font-medium block mb-1">Total Saldo Operasional</span>
            <span class="text-xl font-bold text-white font-mono-numbers block">
                Rp {{ number_format($wallets->where('type', 'operational')->sum('current_balance'), 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-[#9CA3AF] mt-1 block">Kas harian & kebutuhan</span>
        </div>

        <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-4">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs text-[#9CA3AF] font-medium">Progres Target Keseluruhan</span>
                <span class="text-xs font-bold text-[#00E676]">{{ $overallProgress }}%</span>
            </div>
            <div class="w-full h-2 bg-[#262C37] rounded-full overflow-hidden my-2">
                <div class="h-full bg-[#00E676] rounded-full shadow-[0_0_8px_#00E676]" style="width: {{ min($overallProgress, 100) }}%"></div>
            </div>
            <span class="text-[11px] text-[#9CA3AF] block">Dari target Rp {{ number_format($totalTargets, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- POS TABUNGAN & PUNDI LIBURAN GRID -->
    <div class="space-y-6">
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#00E676]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                    Pos Tabungan & Pundi Liburan
                </h2>
                <span class="text-xs text-[#9CA3AF]">{{ $tripWallets->count() + $savingWallets->count() }} Pos Aktif</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($wallets->whereIn('type', ['trip', 'saving']) as $wallet)
                    @php
                        $progress = $wallet->progress_percentage;
                        $isCompleted = $progress >= 100;
                    @endphp
                    <div class="bg-[#17181E] border border-[#262C37] hover:border-[#5865F2]/50 rounded-3xl p-5 flex flex-col justify-between transition-all duration-200 group relative overflow-hidden">
                        @if($isCompleted)
                            <div class="absolute top-0 right-0 bg-[#00E676] text-black text-[10px] font-extrabold uppercase px-3 py-0.5 rounded-bl-xl shadow-md flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                <span>Target Tercapai!</span>
                            </div>
                        @endif

                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div class="w-10 h-10 rounded-2xl bg-[#00E676]/10 border border-[#00E676]/30 flex items-center justify-center text-[#00E676]">
                                    @if($wallet->type === 'trip')
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    @endif
                                </div>

                                <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-[#262C37] text-[#9CA3AF]">
                                    {{ $wallet->account_label ?? ucfirst($wallet->type) }}
                                </span>
                            </div>

                            <h3 class="text-base font-bold text-white group-hover:text-[#5865F2] transition-colors truncate">
                                {{ $wallet->name }}
                            </h3>

                            <div class="my-3">
                                <span class="text-2xl font-extrabold text-white font-mono-numbers block">
                                    {{ $wallet->formatted_balance }}
                                </span>
                                <span class="text-xs text-[#9CA3AF] mt-0.5 block">
                                    Target: <span class="text-white font-medium">{{ $wallet->formatted_target }}</span>
                                </span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="space-y-1.5 my-3">
                                <div class="w-full h-2 bg-[#262C37] rounded-full overflow-hidden">
                                    <div
                                        class="h-full bg-[#00E676] rounded-full shadow-[0_0_8px_#00E676] transition-all duration-500"
                                        style="width: {{ $progress }}%"
                                    ></div>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-[#00E676] font-semibold">Tercapai {{ $progress }}%</span>
                                    <span class="text-[#9CA3AF]">
                                        @if($wallet->target_amount > $wallet->current_balance)
                                            Sisa: Rp {{ number_format($wallet->target_amount - $wallet->current_balance, 0, ',', '.') }}
                                        @else
                                            Lunas
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons: Deposit, Transfer/Cairkan, Edit, Delete -->
                        <div class="pt-3 border-t border-[#262C37]/60 mt-2 space-y-2">
                            <button
                                type="button"
                                wire:click="openDepositModal({{ $wallet->id }})"
                                class="w-full py-2.5 px-3 rounded-xl bg-[#00E676]/10 hover:bg-[#00E676] border border-[#00E676]/40 hover:border-[#00E676] text-xs font-semibold text-[#00E676] hover:text-black transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-sm group-hover:shadow-md"
                            >
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>Setor / Tambah Tabungan</span>
                            </button>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    wire:click="openTransferModal({{ $wallet->id }})"
                                    class="flex-1 py-1.5 px-2.5 rounded-xl bg-[#0D0E12] hover:bg-[#1E2028] border border-[#262C37] text-[11px] font-medium text-[#9CA3AF] hover:text-white transition-colors cursor-pointer"
                                >
                                    Cairkan / Pindah
                                </button>
                                <button
                                    type="button"
                                    wire:click="openEditModal({{ $wallet->id }})"
                                    aria-label="Edit dompet {{ $wallet->name }}"
                                    class="py-1.5 px-2.5 rounded-xl bg-[#0D0E12] hover:bg-[#1E2028] border border-[#262C37] text-[11px] font-medium text-[#9CA3AF] hover:text-white transition-colors cursor-pointer"
                                >
                                    Edit
                                </button>
                                @if($wallet->transactions_count === 0 && $wallet->incoming_transfers_count === 0)
                                    <button
                                        type="button"
                                        wire:click="deleteWallet({{ $wallet->id }})"
                                        wire:confirm="Hapus dompet '{{ $wallet->name }}'?"
                                        aria-label="Hapus dompet {{ $wallet->name }}"
                                        class="py-1.5 px-2.5 rounded-xl bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-[11px] font-medium text-red-400 transition-colors cursor-pointer"
                                    >
                                        Hapus
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- REKENING OPERASIONAL -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#5865F2]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                    Rekening Operasional & Arus Kas
                </h2>
                <span class="text-xs text-[#9CA3AF]">{{ $operationalWallets->count() }} Rekening</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($operationalWallets as $op)
                    <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div class="w-10 h-10 rounded-2xl bg-[#5865F2]/10 border border-[#5865F2]/30 flex items-center justify-center text-[#5865F2]">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                    </svg>
                                </div>
                                <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-[#262C37] text-[#9CA3AF]">
                                    {{ $op->account_label ?? 'Operasional' }}
                                </span>
                            </div>

                            <h3 class="text-base font-bold text-white truncate">{{ $op->name }}</h3>
                            <div class="my-2">
                                <span class="text-2xl font-extrabold text-white font-mono-numbers block">
                                    {{ $op->formatted_balance }}
                                </span>
                                <div class="flex items-center justify-between mt-2 pt-2 border-t border-[#262C37]/40">
                                    <span class="text-xs text-[#9CA3AF]">
                                        Batas Harian: 
                                        @if($op->daily_limit)
                                            <span class="text-white font-medium">{{ $op->formatted_daily_limit }}</span>
                                        @else
                                            <span class="text-[#9CA3AF] italic">Nonaktif</span>
                                        @endif
                                    </span>
                                    <button
                                        type="button"
                                        x-data
                                        @click="$dispatch('open-daily-limit-modal', { walletId: {{ $op->id }} })"
                                        class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#818CF8] hover:text-white transition-colors py-0.5 px-2 rounded-lg bg-[#5865F2]/10 hover:bg-[#5865F2]/20 border border-[#5865F2]/20 cursor-pointer"
                                        title="Atur Batas Pengeluaran Harian"
                                    >
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                        <span>Ubah</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-[#262C37]/60 mt-3 space-y-2">
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    x-data
                                    @click="$dispatch('open-transaction-modal', { mode: 'income', walletId: {{ $op->id }} })"
                                    class="py-2.5 px-2 rounded-xl bg-[#00E676]/15 hover:bg-[#00E676] border border-[#00E676]/40 text-xs font-semibold text-[#00E676] hover:text-black transition-all flex items-center justify-center gap-1 cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    <span>Tambah Kas</span>
                                </button>

                                <button
                                    type="button"
                                    x-data
                                    @click="$dispatch('open-transaction-modal', { mode: 'daily', walletId: {{ $op->id }} })"
                                    class="py-2.5 px-2 rounded-xl bg-[#0D0E12] hover:bg-[#5865F2] border border-[#262C37] hover:border-[#5865F2] text-xs font-semibold text-white transition-all flex items-center justify-center gap-1 cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                    </svg>
                                    <span>Pengeluaran</span>
                                </button>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    wire:click="openTransferModal({{ $op->id }})"
                                    class="flex-1 py-1.5 px-2.5 rounded-xl bg-[#0D0E12] hover:bg-[#1E2028] border border-[#262C37] text-[11px] font-medium text-[#9CA3AF] hover:text-white transition-colors cursor-pointer"
                                >
                                    Transfer
                                </button>
                                <button
                                    type="button"
                                    wire:click="openEditModal({{ $op->id }})"
                                    aria-label="Edit rekening {{ $op->name }}"
                                    class="py-1.5 px-2.5 rounded-xl bg-[#0D0E12] hover:bg-[#1E2028] border border-[#262C37] text-[11px] font-medium text-[#9CA3AF] hover:text-white transition-colors cursor-pointer"
                                >
                                    Edit
                                </button>
                                @if($op->transactions_count === 0 && $op->incoming_transfers_count === 0)
                                    <button
                                        type="button"
                                        wire:click="deleteWallet({{ $op->id }})"
                                        wire:confirm="Hapus rekening '{{ $op->name }}'?"
                                        aria-label="Hapus rekening {{ $op->name }}"
                                        class="py-1.5 px-2.5 rounded-xl bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-[11px] font-medium text-red-400 transition-colors cursor-pointer"
                                    >
                                        Hapus
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- MODAL SETOR / TAMBAH SALDO TABUNGAN (DEPOSIT MODAL) -->
    @if($isDepositModalOpen)
        <div
            x-data
            @keydown.escape.window="$wire.closeDepositModal()"
            role="dialog"
            aria-modal="true"
            aria-label="Setor atau Tambah Tabungan"
            class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex justify-center items-end md:items-center p-0 md:p-4 transition-all duration-200"
        >
            <div
                @click.outside="$wire.closeDepositModal()"
                class="bg-[#17181E] border border-[#262C37] w-full md:max-w-lg rounded-t-3xl md:rounded-3xl shadow-2xl flex flex-col max-h-[92vh] overflow-hidden animate-in fade-in zoom-in-95"
            >
                <!-- Header -->
                <div class="px-5 pt-4 pb-3 flex items-center justify-between border-b border-[#262C37]/60">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-[#00E676]/15 border border-[#00E676]/40 flex items-center justify-center text-[#00E676]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white tracking-tight">Setor / Tambah Tabungan</h3>
                            <p class="text-xs text-[#9CA3AF]">
                                Menabung ke: <span class="text-white font-semibold">{{ $depositTargetWallet?->name ?? 'Pundi Tabungan' }}</span>
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDepositModal" aria-label="Tutup modal setor tabungan" class="w-8 h-8 flex items-center justify-center text-[#9CA3AF] hover:text-white cursor-pointer">&times;</button>
                </div>

                <!-- Body -->
                <div class="px-5 py-4 overflow-y-auto space-y-4">
                    <!-- Progress Card Preview -->
                    @if($depositTargetWallet)
                        <div class="bg-[#0D0E12] border border-[#262C37] rounded-2xl p-3.5 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-[#9CA3AF]">Saldo Saat Ini: <b class="text-white font-mono-numbers">{{ $depositTargetWallet->formatted_balance }}</b></span>
                                <span class="text-[#00E676] font-bold">Target {{ $depositTargetWallet->progress_percentage }}%</span>
                            </div>
                            <div class="w-full h-1.5 bg-[#262C37] rounded-full overflow-hidden">
                                <div class="h-full bg-[#00E676] rounded-full" style="width: {{ $depositTargetWallet->progress_percentage }}%"></div>
                            </div>
                            <div class="text-[11px] text-[#9CA3AF] flex justify-between">
                                <span>Target: {{ $depositTargetWallet->formatted_target }}</span>
                                <span>Kurang: Rp {{ number_format(max(0, $depositTargetWallet->target_amount - $depositTargetWallet->current_balance), 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Pilihan Target Dompet (Jika ingin ganti) -->
                    <div>
                        <label class="block text-xs font-semibold text-white mb-1.5">Pos Tabungan Tujuan</label>
                        <select
                            wire:model.live="depositWalletId"
                            class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-[#00E676]"
                        >
                            @foreach($wallets->whereIn('type', ['trip', 'saving']) as $w)
                                <option value="{{ $w->id }}">
                                    {{ $w->name }} (Saldo: {{ $w->formatted_balance }} / Target: {{ $w->formatted_target }})
                                </option>
                            @endforeach
                        </select>
                        @error('depositWalletId') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Nominal Setoran -->
                    <div class="bg-[#0D0E12] border border-[#262C37] rounded-2xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-semibold text-[#9CA3AF] uppercase tracking-wider">Nominal Tabungan</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#17181E] border border-[#262C37] text-[#9CA3AF]">IDR</span>
                        </div>

                        <div class="flex items-baseline justify-center py-2">
                            <span class="text-2xl sm:text-3xl font-extrabold text-white mr-1">Rp</span>
                            <input
                                type="number"
                                wire:model.live="depositAmount"
                                step="any"
                                min="0"
                                class="w-44 sm:w-56 text-2xl sm:text-3xl font-extrabold text-white bg-transparent border-0 focus:ring-0 p-0 font-mono-numbers focus:outline-none text-center"
                                placeholder="0"
                            />
                        </div>
                        @if($depositAmount && is_numeric($depositAmount) && $depositAmount > 0)
                            @php
                                $val = (float) $depositAmount;
                                $formatted = (floor($val) == $val) ? number_format($val, 0, ',', '.') : number_format($val, 2, ',', '.');
                            @endphp
                            <div class="text-center text-[11px] text-[#00E676] font-mono-numbers pb-1">
                                Terbaca: Rp {{ $formatted }}
                            </div>
                        @endif
                        @error('depositAmount') <p class="text-xs text-red-400 text-center mt-1">{{ $message }}</p> @enderror

                        <!-- Quick Increment Buttons -->
                        <div class="flex items-center justify-center gap-2 pt-2 border-t border-[#262C37]/50 mt-2 flex-wrap">
                            <button
                                type="button"
                                wire:click="addDepositAmount(50000)"
                                class="px-2.5 py-1 rounded-lg bg-[#17181E] hover:bg-[#262C37] text-xs font-medium text-[#9CA3AF] hover:text-white transition-colors cursor-pointer"
                            >
                                +Rp 50.000
                            </button>
                            <button
                                type="button"
                                wire:click="addDepositAmount(100000)"
                                class="px-2.5 py-1 rounded-lg bg-[#17181E] hover:bg-[#262C37] text-xs font-medium text-[#9CA3AF] hover:text-white transition-colors cursor-pointer"
                            >
                                +Rp 100.000
                            </button>
                            <button
                                type="button"
                                wire:click="addDepositAmount(500000)"
                                class="px-2.5 py-1 rounded-lg bg-[#17181E] hover:bg-[#262C37] text-xs font-medium text-[#9CA3AF] hover:text-white transition-colors cursor-pointer"
                            >
                                +Rp 500.000
                            </button>
                            <button
                                type="button"
                                wire:click="addDepositAmount(1000000)"
                                class="px-2.5 py-1 rounded-lg bg-[#17181E] hover:bg-[#262C37] text-xs font-medium text-[#9CA3AF] hover:text-white transition-colors cursor-pointer"
                            >
                                +Rp 1.000.000
                            </button>
                        </div>
                    </div>

                    <!-- Sumber Dana: Sisihkan Kas vs Dana Luar -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-white">Sumber Alokasi Dana</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                type="button"
                                wire:click="$set('sourceMode', 'operational')"
                                class="p-3 rounded-2xl border text-left transition-all cursor-pointer {{ $sourceMode === 'operational' ? 'bg-[#5865F2]/15 border-[#5865F2] ring-1 ring-[#5865F2]' : 'bg-[#0D0E12] border-[#262C37] text-[#9CA3AF]' }}"
                            >
                                <span class="text-xs font-bold text-white block mb-0.5">Sisihkan dari Kas</span>
                                <span class="text-[11px] text-[#9CA3AF] block leading-tight">Kurangi kas harian, alihkan ke tabungan ini</span>
                            </button>

                            <button
                                type="button"
                                wire:click="$set('sourceMode', 'external')"
                                class="p-3 rounded-2xl border text-left transition-all cursor-pointer {{ $sourceMode === 'external' ? 'bg-[#00E676]/15 border-[#00E676] ring-1 ring-[#00E676]' : 'bg-[#0D0E12] border-[#262C37] text-[#9CA3AF]' }}"
                            >
                                <span class="text-xs font-bold text-white block mb-0.5">Dana Masuk Baru</span>
                                <span class="text-[11px] text-[#9CA3AF] block leading-tight">Pemasukan luar / bonus langsung ke tabungan</span>
                            </button>
                        </div>

                        @if($sourceMode === 'operational')
                            <div class="mt-2">
                                <label class="block text-[11px] text-[#9CA3AF] mb-1">Rekening Kas Pengurang</label>
                                <select
                                    wire:model="sourceWalletId"
                                    class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl p-2 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                                >
                                    @foreach($operationalWallets as $op)
                                        <option value="{{ $op->id }}">
                                            {{ $op->name }} (Saldo: {{ $op->formatted_balance }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>

                    <!-- Catatan Tambahan -->
                    <div>
                        <label class="block text-xs font-semibold text-white mb-1">Catatan (Opsional)</label>
                        <input
                            type="text"
                            wire:model="depositNote"
                            placeholder="Contoh: Sisihkan gaji bulanan, sisa uang saku..."
                            class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-[#00E676]"
                        />
                    </div>
                </div>

                <!-- Footer Action -->
                <div class="p-5 border-t border-[#262C37]/60 bg-[#17181E] flex items-center justify-end gap-2.5">
                    <button
                        type="button"
                        wire:click="closeDepositModal"
                        class="px-4 py-2.5 rounded-xl bg-[#0D0E12] border border-[#262C37] text-xs font-semibold text-[#9CA3AF] hover:text-white"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="saveDeposit"
                        wire:loading.attr="disabled"
                        wire:target="saveDeposit"
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#00E676] to-[#10B981] hover:from-[#10B981] hover:to-[#059669] text-xs font-bold text-black shadow-lg shadow-[#00E676]/30 active:scale-[0.98] transition-all cursor-pointer flex items-center gap-1.5 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="saveDeposit">Simpan Tabungan</span>
                        <span wire:loading wire:target="saveDeposit">Menyimpan...</span>
                        <svg wire:loading.remove wire:target="saveDeposit" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL CREATE / EDIT WALLET -->
    @if($isCreateModalOpen)
        <div
            x-data
            @keydown.escape.window="$wire.closeCreateModal()"
            role="dialog"
            aria-modal="true"
            aria-label="{{ $editingWalletId ? 'Edit Dompet' : 'Buat Target Dompet Baru' }}"
            class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex justify-center items-center p-4"
        >
            <div
                @click.outside="$wire.closeCreateModal()"
                class="bg-[#17181E] border border-[#262C37] w-full max-w-md rounded-3xl shadow-2xl p-6 space-y-4"
            >
                <div class="flex items-center justify-between border-b border-[#262C37]/60 pb-3">
                    <h3 class="text-base font-bold text-white">{{ $editingWalletId ? 'Edit Dompet / Target' : 'Buat Target Dompet Baru' }}</h3>
                    <button type="button" wire:click="closeCreateModal" aria-label="Tutup modal dompet" class="text-[#9CA3AF] hover:text-white cursor-pointer">&times;</button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-white mb-1">Nama Pos / Target</label>
                        <input
                            type="text"
                            wire:model="name"
                            placeholder="Contoh: Liburan Bali 2027, HP iPhone, Dana Darurat..."
                            class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                        />
                        @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-white mb-1">Label Akun / Bank</label>
                        <input
                            type="text"
                            wire:model="accountLabel"
                            placeholder="Contoh: BCA Prioritas, Mandiri, ShopeePay..."
                            class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-white mb-1">Tipe Dompet</label>
                            <select
                                wire:model.live="type"
                                @if($editingWalletId) disabled @endif
                                class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#5865F2] disabled:opacity-60"
                            >
                                <option value="trip">Pundi Liburan (Trip)</option>
                                <option value="saving">Tabungan (Saving)</option>
                                <option value="operational">Operasional (Kas)</option>
                            </select>
                        </div>

                        @if($type !== 'operational')
                            <div>
                                <label class="block text-xs font-semibold text-white mb-1">Target Nominal (Rp)</label>
                                <input
                                    type="number"
                                    wire:model="targetAmount"
                                    step="any"
                                    min="0"
                                    placeholder="5000000"
                                    class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                                />
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        @if(!$editingWalletId)
                            <div>
                                <label class="block text-xs font-semibold text-white mb-1">Saldo Awal (Rp)</label>
                                <input
                                    type="number"
                                    wire:model="initialBalance"
                                    step="any"
                                    min="0"
                                    placeholder="0"
                                    class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                                />
                            </div>
                        @endif

                        @if($type === 'operational')
                            <div>
                                <label class="block text-xs font-semibold text-white mb-1">Batas Harian (Rp)</label>
                                <input
                                    type="number"
                                    wire:model="dailyLimit"
                                    step="any"
                                    min="0"
                                    placeholder="350000"
                                    class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                                />
                            </div>
                        @endif
                    </div>
                </div>

                <div class="pt-3 border-t border-[#262C37]/60 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        wire:click="closeCreateModal"
                        class="px-4 py-2 rounded-xl bg-[#0D0E12] border border-[#262C37] text-xs font-semibold text-[#9CA3AF] hover:text-white cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="saveWallet"
                        class="px-4 py-2 rounded-xl bg-[#5865F2] hover:bg-[#4752C4] text-xs font-semibold text-white shadow-md shadow-[#5865F2]/25 cursor-pointer"
                    >
                        {{ $editingWalletId ? 'Simpan Perubahan' : 'Simpan Dompet' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL TRANSFER / CAIRKAN DANA ANTAR DOMPET -->
    @if($isTransferModalOpen)
        <div
            x-data
            @keydown.escape.window="$wire.closeTransferModal()"
            role="dialog"
            aria-modal="true"
            aria-label="Transfer atau Cairkan Dana"
            class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex justify-center items-center p-4"
        >
            <div
                @click.outside="$wire.closeTransferModal()"
                class="bg-[#17181E] border border-[#262C37] w-full max-w-md rounded-3xl shadow-2xl p-6 space-y-4"
            >
                <div class="flex items-center justify-between border-b border-[#262C37]/60 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-white">Transfer / Cairkan Dana</h3>
                        <p class="text-[11px] text-[#9CA3AF]">Pindahkan saldo antar dompet operasional maupun tabungan</p>
                    </div>
                    <button type="button" wire:click="closeTransferModal" aria-label="Tutup modal transfer" class="text-[#9CA3AF] hover:text-white cursor-pointer">&times;</button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-white mb-1">Dari Dompet</label>
                        <select
                            wire:model.live="fromWalletId"
                            class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                        >
                            @foreach($wallets as $w)
                                <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->formatted_balance }})</option>
                            @endforeach
                        </select>
                        @error('fromWalletId') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-white mb-1">Ke Dompet Tujuan</label>
                        <select
                            wire:model.live="toWalletId"
                            class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                        >
                            @foreach($wallets as $w)
                                <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->formatted_balance }})</option>
                            @endforeach
                        </select>
                        @error('toWalletId') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-white mb-1">Nominal (Rp)</label>
                        <input
                            type="number"
                            wire:model="transferAmount"
                            step="any"
                            min="1000"
                            class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl px-3 py-2 text-sm font-bold text-white font-mono-numbers focus:outline-none focus:border-[#5865F2]"
                        />
                        @error('transferAmount') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-white mb-1">Catatan (Opsional)</label>
                        <input
                            type="text"
                            wire:model="transferNote"
                            placeholder="Contoh: Cairkan tabungan untuk bayar tiket"
                            class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                        />
                    </div>
                </div>

                <div class="pt-3 border-t border-[#262C37]/60 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        wire:click="closeTransferModal"
                        class="px-4 py-2 rounded-xl bg-[#0D0E12] border border-[#262C37] text-xs font-semibold text-[#9CA3AF] hover:text-white cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="saveTransfer"
                        class="px-4 py-2 rounded-xl bg-[#5865F2] hover:bg-[#4752C4] text-xs font-semibold text-white shadow-md shadow-[#5865F2]/25 cursor-pointer"
                    >
                        Proses Transfer
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
