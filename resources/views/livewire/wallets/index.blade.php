<div>
    <!-- Top Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Tabungan & Pundi Liburan</h1>
            <p class="text-xs text-[#9CA3AF]">Kelola pos tabungan, target liburan, dan alokasi rekening keuangan Anda.</p>
        </div>

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

    <!-- PUNDI LIBURAN & TABUNGAN ENVELOPES GRID (1 col mobile, 2 col md, 3 col lg) -->
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
                    @endphp
                    <div class="bg-[#17181E] border border-[#262C37] hover:border-[#5865F2]/50 rounded-3xl p-5 flex flex-col justify-between transition-all duration-200 group">
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
                                    <span class="text-[#9CA3AF]">Sisa: Rp {{ number_format(max(0, $wallet->target_amount - $wallet->current_balance), 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="pt-3 border-t border-[#262C37]/60 mt-2">
                            <button
                                type="button"
                                x-data
                                @click="$dispatch('open-transaction-modal', { mode: 'saving', destinationId: {{ $wallet->id }} })"
                                class="w-full py-2.5 px-3 rounded-xl bg-[#0D0E12] hover:bg-[#5865F2] border border-[#262C37] hover:border-[#5865F2] text-xs font-semibold text-white transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-sm"
                            >
                                <svg class="w-4 h-4 text-[#00E676]" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>Alokasikan Tabungan</span>
                            </button>
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
                                @if($op->daily_limit)
                                    <span class="text-xs text-[#9CA3AF] mt-0.5 block">
                                        Batas Harian: <span class="text-white font-medium">{{ $op->formatted_daily_limit }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="pt-3 border-t border-[#262C37]/60 mt-3">
                            <button
                                type="button"
                                x-data
                                @click="$dispatch('open-transaction-modal', { mode: 'expense', walletId: {{ $op->id }} })"
                                class="w-full py-2.5 px-3 rounded-xl bg-[#0D0E12] hover:bg-[#5865F2] border border-[#262C37] hover:border-[#5865F2] text-xs font-semibold text-white transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>Catat Pengeluaran</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- MODAL CREATE WALLET -->
    @if($isCreateModalOpen)
        <div
            x-data
            @keydown.escape.window="$wire.closeCreateModal()"
            class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex justify-center items-center p-4"
        >
            <div
                @click.outside="$wire.closeCreateModal()"
                class="bg-[#17181E] border border-[#262C37] w-full max-w-md rounded-3xl shadow-2xl p-6 space-y-4"
            >
                <div class="flex items-center justify-between border-b border-[#262C37]/60 pb-3">
                    <h3 class="text-base font-bold text-white">Buat Target Dompet Baru</h3>
                    <button type="button" wire:click="closeCreateModal" class="text-[#9CA3AF] hover:text-white">&times;</button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-white mb-1">Nama Pos / Target</label>
                        <input
                            type="text"
                            wire:model="name"
                            placeholder="Contoh: Liburan Bali 2027, Dana Darurat..."
                            class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                        />
                        @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-white mb-1">Label Akun / Bank</label>
                        <input
                            type="text"
                            wire:model="accountLabel"
                            placeholder="Contoh: BCA Prioritas, Mandiri, Bibit..."
                            class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-white mb-1">Tipe Dompet</label>
                            <select
                                wire:model="type"
                                class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                            >
                                <option value="trip">Pundi Liburan (Trip)</option>
                                <option value="saving">Tabungan (Saving)</option>
                                <option value="operational">Operasional (Kas)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-white mb-1">Target Nominal (Rp)</label>
                            <input
                                type="number"
                                wire:model="targetAmount"
                                placeholder="5000000"
                                class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-white mb-1">Saldo Awal (Rp)</label>
                            <input
                                type="number"
                                wire:model="initialBalance"
                                placeholder="0"
                                class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                            />
                        </div>

                        @if($type === 'operational')
                            <div>
                                <label class="block text-xs font-semibold text-white mb-1">Batas Harian (Rp)</label>
                                <input
                                    type="number"
                                    wire:model="dailyLimit"
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
                        class="px-4 py-2 rounded-xl bg-[#0D0E12] border border-[#262C37] text-xs font-semibold text-[#9CA3AF] hover:text-white"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="saveWallet"
                        class="px-4 py-2 rounded-xl bg-[#5865F2] hover:bg-[#4752C4] text-xs font-semibold text-white shadow-md shadow-[#5865F2]/25"
                    >
                        Simpan Dompet
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
