<div>
    @if($isOpen)
        <div
            x-data
            x-init="$nextTick(() => $refs.globalSearchInput?.focus())"
            @keydown.escape.window="$wire.closeModal()"
            role="dialog"
            aria-modal="true"
            aria-label="Pencarian cepat SIMAU"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex justify-center items-start pt-12 md:pt-20 p-4"
        >
            <div
                @click.outside="$wire.closeModal()"
                class="bg-[#17181E] border border-[#262C37] w-full max-w-xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[82vh]"
            >
                <!-- Search Input Header -->
                <div class="p-4 border-b border-[#262C37] flex items-center gap-3">
                    <svg class="w-5 h-5 text-[#818CF8] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <label for="global-search-input" class="sr-only">Cari transaksi, dompet, atau trip</label>
                    <input
                        id="global-search-input"
                        x-ref="globalSearchInput"
                        type="search"
                        wire:model.live.debounce.250ms="query"
                        placeholder="Cari transaksi, dompet, atau trip..."
                        class="flex-1 bg-transparent border-0 text-sm text-white placeholder-[#9CA3AF] focus:outline-none focus:ring-0"
                    />
                    <span wire:loading wire:target="query" class="text-[11px] text-[#818CF8] font-medium">Mencari...</span>
                    <button
                        type="button"
                        wire:click="closeModal"
                        aria-label="Tutup pencarian cepat"
                        class="w-9 h-9 rounded-xl bg-[#0D0E12] border border-[#262C37] text-[#9CA3AF] hover:text-white flex items-center justify-center cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Results Body -->
                <div class="p-4 overflow-y-auto space-y-5">
                    @if($trimmed === '')
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-[#9CA3AF]">
                            Transaksi Terbaru
                        </p>
                    @endif

                    @if($transactions->isNotEmpty())
                        <div class="space-y-2">
                            @if($trimmed !== '')
                                <h4 class="text-[11px] font-semibold uppercase tracking-wider text-[#9CA3AF]">Transaksi</h4>
                            @endif
                            @foreach($transactions as $tx)
                                <div class="bg-[#0D0E12] border border-[#262C37] hover:border-[#818CF8]/50 rounded-2xl p-3 flex items-center justify-between gap-3 transition-colors">
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-white truncate">{{ $tx->title }}</p>
                                        <p class="text-[11px] text-[#9CA3AF] truncate">
                                            {{ $tx->formatted_date_time }} &bull; {{ $tx->wallet?->name ?? 'Kas' }}
                                            @if($tx->category)
                                                &bull; {{ $tx->category->name }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span class="text-xs font-bold font-mono-numbers {{ $tx->type === 'expense' ? 'text-white' : 'text-[#00E676]' }}">
                                            {{ $tx->formatted_amount }}
                                        </span>
                                        @if($tx->type !== 'transfer')
                                            <button
                                                type="button"
                                                @click="$wire.closeModal(); $dispatch('open-edit-transaction-modal', { transactionId: {{ $tx->id }} })"
                                                aria-label="Edit transaksi {{ $tx->title }}"
                                                class="px-2.5 py-1.5 rounded-lg bg-[#17181E] border border-[#262C37] text-[11px] font-medium text-[#818CF8] hover:text-white cursor-pointer"
                                            >
                                                Edit
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($wallets->isNotEmpty())
                        <div class="space-y-2">
                            <h4 class="text-[11px] font-semibold uppercase tracking-wider text-[#9CA3AF]">Dompet &amp; Tabungan</h4>
                            @foreach($wallets as $wallet)
                                <a
                                    href="{{ route('tabungan') }}"
                                    wire:navigate
                                    @click="$wire.closeModal()"
                                    class="bg-[#0D0E12] border border-[#262C37] hover:border-[#818CF8]/50 rounded-2xl p-3 flex items-center justify-between gap-3 transition-colors"
                                >
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-white truncate">{{ $wallet->name }}</p>
                                        <p class="text-[11px] text-[#9CA3AF] truncate">{{ $wallet->account_label ?? ucfirst($wallet->type) }}</p>
                                    </div>
                                    <span class="text-xs font-bold font-mono-numbers text-[#00E676]">{{ $wallet->formatted_balance }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if($trips->isNotEmpty())
                        <div class="space-y-2">
                            <h4 class="text-[11px] font-semibold uppercase tracking-wider text-[#9CA3AF]">Trip Liburan</h4>
                            @foreach($trips as $trip)
                                <a
                                    href="{{ route('trips.report', $trip) }}"
                                    wire:navigate
                                    @click="$wire.closeModal()"
                                    class="bg-[#0D0E12] border border-[#262C37] hover:border-[#818CF8]/50 rounded-2xl p-3 flex items-center justify-between gap-3 transition-colors"
                                >
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-white truncate">{{ $trip->name }}</p>
                                        <p class="text-[11px] text-[#9CA3AF] truncate">{{ $trip->destination }} &bull; {{ $trip->status_label }}</p>
                                    </div>
                                    <span class="text-xs font-semibold text-[#818CF8]">Lihat Rekap</span>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if($trimmed !== '' && $transactions->isEmpty() && $wallets->isEmpty() && $trips->isEmpty())
                        <div class="py-10 text-center space-y-2">
                            <p class="text-xs font-semibold text-white">Tidak ditemukan hasil untuk "{{ $trimmed }}"</p>
                            <p class="text-[11px] text-[#9CA3AF]">Coba kata kunci judul transaksi, nama dompet, atau destinasi trip.</p>
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="px-4 py-3 border-t border-[#262C37] bg-[#0D0E12]/60 flex items-center justify-between text-[11px] text-[#9CA3AF]">
                    <span>Tekan Esc untuk menutup</span>
                    <a
                        href="{{ route('mutasi') }}"
                        wire:navigate
                        @click="$wire.closeModal()"
                        class="font-semibold text-[#818CF8] hover:text-white"
                    >
                        Buka Halaman Mutasi Lengkap
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
