<div>
    <!-- Desktop Greeting Bar -->
    <div class="hidden md:flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                Halo, {{ $user?->name ?? 'Krisna' }}! 👋
            </h1>
            <p class="text-sm text-[#9CA3AF]">
                Buku catatan pengeluaran harian dan anggaran liburan Anda.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <button
                type="button"
                wire:click="exportExcel"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#17181E] border border-[#262C37] text-xs font-semibold text-[#9CA3AF] hover:text-white hover:border-[#5865F2] transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4 text-[#00E676]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Export Laporan</span>
            </button>

            <button
                type="button"
                x-data
                @click="$dispatch('open-transaction-modal', { mode: 'daily' })"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#5865F2] hover:bg-[#4752C4] text-xs font-semibold text-white shadow-lg shadow-[#5865F2]/25 transition-all cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Catat Harian</span>
            </button>
        </div>
    </div>

    <!-- MAIN RESPONSIVE CONTAINER (12-column grid on desktop, single vertical stack on mobile) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- LEFT COLUMN (Hero Balance, Batas Harian & Pundi Liburan) -->
        <div class="lg:col-span-5 space-y-5">

            <!-- 1. ROYAL BLUE HERO BALANCE CARD -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#4F46E5] via-[#4338CA] to-[#312E81] p-6 text-white shadow-2xl shadow-[#4F46E5]/30 border border-[#6366F1]/30">
                <!-- Decorative background light pattern -->
                <div class="absolute -right-12 -top-12 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -left-12 -bottom-12 w-48 h-48 bg-[#00E676]/10 rounded-full blur-2xl pointer-events-none"></div>

                <!-- Top Row: Greeting & Avatar -->
                <div class="relative z-10 flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-bold text-white tracking-tight">
                            Halo, {{ Str::words($user?->name ?? 'Krisna', 1, '') }}!
                        </h2>
                        <p class="text-xs text-white/80 font-medium">Buku Kas Harian &amp; Liburan</p>
                    </div>

                    <a href="{{ route('profile') }}" wire:navigate class="relative block">
                        <img
                            src="{{ $user?->avatar_url ?? asset('avatars/avatar-1.svg') }}"
                            alt="{{ $user?->name ?? 'User' }}"
                            class="w-11 h-11 rounded-full object-cover ring-2 ring-white/40 shadow-md"
                        />
                        <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-[#00E676] border-2 border-[#4338CA] rounded-full"></span>
                    </a>
                </div>

                <!-- Middle Row: Total Kas & Tabungan -->
                <div class="relative z-10 mb-4">
                    <div class="flex items-center gap-2 mb-1 text-white/80 text-[11px] font-semibold uppercase tracking-wider">
                        <span>TOTAL KAS &amp; TABUNGAN</span>
                        <button
                            type="button"
                            wire:click="toggleBalance"
                            class="hover:text-white transition-colors cursor-pointer"
                            title="{{ $showBalance ? 'Sembunyikan Saldo' : 'Tampilkan Saldo' }}"
                        >
                            @if($showBalance)
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            @endif
                        </button>
                    </div>

                    <div class="text-3xl sm:text-4xl font-extrabold tracking-tight font-mono-numbers">
                        @if($showBalance)
                            {{ $formattedTotalBalance }}
                        @else
                            Rp &bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;
                        @endif
                    </div>

                    <div class="mt-2.5 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-white text-[11px] font-semibold">
                        <span class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#00E676]"></span>
                            Kas {{ $formattedOpShort }}
                        </span>
                        <span class="text-white/40">&bull;</span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3 h-3 text-[#38BDF8]" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                            Pundi {{ $formattedTripShort }}
                        </span>
                    </div>
                </div>

                <!-- Bottom Row: 4 Quick Action Buttons (Refactored from fake transfer to Travel Ledger) -->
                <div class="relative z-10 pt-4 border-t border-white/20 grid grid-cols-4 gap-2 text-center">
                    <!-- 1. Catat + (Harian) -->
                    <button
                        type="button"
                        x-data
                        @click="$dispatch('open-transaction-modal', { mode: 'daily' })"
                        class="flex flex-col items-center gap-1.5 group cursor-pointer"
                    >
                        <div class="w-12 h-12 rounded-2xl bg-white/20 group-hover:bg-white/30 backdrop-blur-md flex items-center justify-center transition-all duration-150 shadow-md">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-medium text-white/90">Catat +</span>
                    </button>

                    <!-- 2. Trip + (Catat Pengeluaran Liburan) -->
                    <button
                        type="button"
                        x-data
                        @click="$dispatch('open-transaction-modal', { mode: 'trip' })"
                        class="flex flex-col items-center gap-1.5 group cursor-pointer"
                    >
                        <div class="w-12 h-12 rounded-2xl bg-white/20 group-hover:bg-white/30 backdrop-blur-md flex items-center justify-center transition-all duration-150 shadow-md">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-medium text-white/90">Trip +</span>
                    </button>

                    <!-- 3. Rencana Trip (Buka Hub Trip) -->
                    <a
                        href="{{ route('trips.index') }}"
                        wire:navigate
                        class="flex flex-col items-center gap-1.5 group cursor-pointer"
                    >
                        <div class="w-12 h-12 rounded-2xl bg-white/20 group-hover:bg-white/30 backdrop-blur-md flex items-center justify-center transition-all duration-150 shadow-md">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.284a2.25 2.25 0 0 0-2.012 0L2.622 5.72A1.125 1.125 0 0 0 2 6.726v11.93c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-medium text-white/90">Trip Hub</span>
                    </a>

                    <!-- 4. Export (Download Excel) -->
                    <button
                        type="button"
                        wire:click="exportExcel"
                        class="flex flex-col items-center gap-1.5 group cursor-pointer"
                    >
                        <div class="w-12 h-12 rounded-2xl bg-white/20 group-hover:bg-white/30 backdrop-blur-md flex items-center justify-center transition-all duration-150 shadow-md">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-medium text-white/90">Export</span>
                    </button>
                </div>
            </div>

            <!-- 2. TWO-COLUMN SUMMARY CARDS (Batas Harian & Pundi Liburan Aktif) -->
            <div class="grid grid-cols-2 gap-3.5">
                <!-- Batas Harian -->
                <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-4 flex flex-col justify-between relative overflow-hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-[#9CA3AF]">Batas Harian</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-[#00E676] shadow-[0_0_8px_#00E676]"></span>
                    </div>

                    <div class="my-1">
                        <span class="text-lg font-bold text-white font-mono-numbers">
                            Rp {{ number_format($dailyLimit, 0, ',', '.') }}
                        </span>
                    </div>

                    <div>
                        <!-- Progress bar -->
                        <div class="w-full h-1.5 bg-[#262C37] rounded-full overflow-hidden mb-1.5">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-[#5865F2] rounded-full" style="width: {{ $dailyPercentRemaining }}%"></div>
                        </div>
                        <span class="text-[11px] text-[#9CA3AF] font-medium">Tersisa {{ $dailyPercentRemaining }}%</span>
                    </div>
                </div>

                <!-- Pundi Liburan (Terkoneksi ke Trip Aktif) -->
                <a href="{{ route('trips.index') }}" wire:navigate class="bg-[#17181E] border border-[#262C37] rounded-3xl p-4 flex flex-col justify-between hover:border-[#5865F2]/50 transition-colors group">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-[#9CA3AF] group-hover:text-white transition-colors truncate">
                            {{ $activeTrip ? $activeTrip->destination : 'Pundi Liburan' }}
                        </span>
                        <svg class="w-4 h-4 text-[#9CA3AF] group-hover:text-[#00E676] transition-colors flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                        </svg>
                    </div>

                    <div class="my-1">
                        <span class="text-lg font-bold text-white font-mono-numbers">
                            Rp {{ number_format($tripSpent, 0, ',', '.') }}
                        </span>
                    </div>

                    <div>
                        <!-- Progress bar (Neon green) -->
                        <div class="w-full h-1.5 bg-[#262C37] rounded-full overflow-hidden mb-1.5">
                            <div class="h-full bg-[#00E676] rounded-full shadow-[0_0_8px_#00E676]" style="width: {{ min($tripPercent, 100) }}%"></div>
                        </div>
                        <span class="text-[11px] text-[#00E676] font-semibold">
                            Target {{ $tripPercent }}% tercapai
                        </span>
                    </div>
                </a>
            </div>

            <!-- 3. SECURITY BANNER (Enkripsi Biometrik Aktif) -->
            <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#00E676]/10 border border-[#00E676]/30 flex items-center justify-center text-[#00E676]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-white">Enkripsi Biometrik Aktif</h4>
                        <p class="text-[11px] text-[#9CA3AF]">Proteksi saldo bank real-time</p>
                    </div>
                </div>

                <span class="px-2.5 py-1 rounded-full bg-[#00E676]/15 border border-[#00E676]/30 text-[#00E676] text-[11px] font-semibold">
                    Aman
                </span>
            </div>
        </div>

        <!-- RIGHT COLUMN (Transaksi Terbaru) -->
        <div class="lg:col-span-7">
            <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-5 md:p-6">
                <!-- Section Header -->
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-white">Transaksi Terbaru</h3>
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-[#262C37] text-[#9CA3AF]">Hari ini</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('mutasi') }}" wire:navigate class="text-xs font-semibold text-[#5865F2] hover:text-[#4752C4] transition-colors">
                            Lihat Semua
                        </a>
                        <button type="button" class="text-[#9CA3AF] hover:text-white" title="Menu Opsi">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Transaction List -->
                <div class="space-y-3">
                    @forelse($recentTransactions as $tx)
                        @php
                            $cat = $tx->category;
                            $iconColor = $cat?->color ?? '#5865F2';
                            $icon = $cat?->icon ?? 'tag';
                            $isExpense = $tx->type === 'expense';
                        @endphp
                        <div class="bg-[#0D0E12] border border-[#262C37]/80 hover:border-[#5865F2]/40 rounded-2xl p-3.5 flex items-center justify-between transition-colors">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <!-- Category Icon Badge -->
                                <div
                                    class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                    style="background-color: {{ $iconColor }}1A; color: {{ $iconColor }}; border: 1px solid {{ $iconColor }}33;"
                                >
                                    @if($icon === 'utensils')
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                        </svg>
                                    @elseif($icon === 'car')
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V3.75m0 3.75H7.5m9 0h2.25" />
                                        </svg>
                                    @elseif($icon === 'bed')
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 .414-.336.75-.75.75h-1.5a.75.75 0 0 1-.75-.75v-1.5H6.75v1.5a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1-.75-.75v-4.25m16.5 0a3.75 3.75 0 0 0-3.75-3.75H12m8.25 3.75H3.75m0 0a3.75 3.75 0 0 1 3.75-3.75h1.5m-5.25 3.75v-8.5c0-.414.336-.75.75-.75h1.5c.414 0 .75.336.75.75v4.25" />
                                        </svg>
                                    @elseif($icon === 'coffee')
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5a3.75 3.75 0 0 0 3.75 3.75h1.5a3.75 3.75 0 0 0 3.75-3.75v-1.5m-9 1.5a3.75 3.75 0 0 1-3.75 3.75h-1.5A3.75 3.75 0 0 1 3 8.25v-1.5m9 7.5v4.5m0 0H8.25m3.75 0h3.75" />
                                        </svg>
                                    @elseif($icon === 'ticket')
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    @endif
                                </div>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-semibold text-white truncate">{{ $tx->title }}</h4>
                                        @if($tx->trip)
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-[#5865F2]/20 text-[#5865F2] border border-[#5865F2]/30 font-semibold truncate inline-flex items-center gap-1">
                                                <svg class="w-3 h-3 text-[#5865F2]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                                </svg>
                                                {{ $tx->trip->destination }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-[#9CA3AF] truncate">
                                        {{ $cat?->name ?? 'Pengeluaran' }} &bull; {{ $tx->formatted_date }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-right flex-shrink-0 ml-3">
                                <span class="text-sm font-bold font-mono-numbers block {{ $isExpense ? 'text-white' : 'text-[#00E676]' }}">
                                    {{ $tx->formatted_amount }}
                                </span>
                                <span class="text-[11px] text-[#9CA3AF]">Berhasil</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-[#9CA3AF]">
                            <p class="text-sm">Belum ada transaksi tercatat.</p>
                            <button
                                type="button"
                                x-data
                                @click="$dispatch('open-transaction-modal', { mode: 'daily' })"
                                class="mt-3 px-4 py-2 rounded-xl bg-[#5865F2] text-white text-xs font-semibold hover:bg-[#4752C4]"
                            >
                                Catat Transaksi Sekarang
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
