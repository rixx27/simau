<div>
    <!-- Back Navigation & Actions -->
    <div class="flex items-center justify-between gap-4 mb-6">
        <a
            href="{{ route('trips.index') }}"
            wire:navigate
            class="inline-flex items-center gap-2 text-xs font-semibold text-[#9CA3AF] hover:text-white transition-colors"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Trip Hub</span>
        </a>

        <div class="flex items-center gap-2">
            @if($trip->status === 'active')
                <button
                    type="button"
                    wire:click="markAsCompleted"
                    wire:confirm="Selesaikan trip ini sekarang?"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 text-xs font-semibold text-amber-400 transition-all cursor-pointer"
                >
                    <span> Selesaikan Trip</span>
                </button>
            @endif

            <button
                type="button"
                wire:click="exportExcel"
                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-[#17181E] hover:bg-[#262C37] border border-[#262C37] text-xs font-semibold text-white transition-all cursor-pointer"
            >
                <svg class="w-4 h-4 text-[#00E676]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Unduh Excel Rekap</span>
            </button>
        </div>
    </div>

    <!-- HERO HEADER CARD -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#17181E] via-[#1E2028] to-[#121318] border border-[#262C37] p-6 sm:p-8 mb-6 shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#5865F2]/20 border border-[#5865F2]/40 text-[#818CF8] text-xs font-semibold">
                        <svg class="w-3.5 h-3.5 text-[#38BDF8]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        <span>{{ $trip->destination }}</span>
                    </span>

                    @if($trip->status === 'completed')
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-[#00E676]/15 border border-[#00E676]/40 text-[#00E676] text-xs font-bold uppercase tracking-wider">
                             Rekap Pasca-Trip Selesai
                        </span>
                    @elseif($trip->status === 'active')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-[#00E676] animate-pulse"></span>
                            Berlangsung
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-[#5865F2]/20 border border-[#5865F2]/40 text-[#818CF8] text-xs font-bold uppercase tracking-wider">
                            Perjalanan Terencana
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    {{ $trip->name }}
                </h1>
                <p class="text-xs sm:text-sm text-[#9CA3AF] mt-1 flex items-center gap-2">
                    <span>{{ $trip->formatted_date_range }}</span>
                    <span>&bull;</span>
                    <span>Durasi: {{ $trip->days_duration }} Hari</span>
                </p>
            </div>

            <!-- Total Spending Highlight -->
            <div class="bg-[#0D0E12]/80 border border-[#262C37] rounded-2xl p-4 md:text-right min-w-[220px]">
                <span class="text-[11px] font-semibold text-[#9CA3AF] uppercase tracking-wider block mb-0.5">Total Realisasi Biaya</span>
                <span class="text-2xl sm:text-3xl font-extrabold text-white font-mono-numbers block">
                    Rp {{ number_format($totalSpent, 0, ',', '.') }}
                </span>
                <span class="text-xs {{ $isSurplus ? 'text-[#00E676]' : 'text-red-400' }} font-medium mt-1 block">
                    {{ $isSurplus ? 'Di Bawah Target' : 'Melebihi Target' }}
                </span>
            </div>
        </div>
    </div>

    <!-- 4 METRICS SCORECARD -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- 1. Plafon Anggaran -->
        <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-4">
            <span class="text-[11px] font-medium text-[#9CA3AF] block mb-1">Plafon Anggaran</span>
            <span class="text-lg sm:text-xl font-bold text-white font-mono-numbers block">
                {{ $trip->formatted_budget }}
            </span>
            <span class="text-[10px] text-[#9CA3AF] mt-1 block">Target batas maksimal</span>
        </div>

        <!-- 2. Realisasi Pengeluaran -->
        <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-4">
            <span class="text-[11px] font-medium text-[#9CA3AF] block mb-1">Realisasi Terpakai</span>
            <span class="text-lg sm:text-xl font-bold {{ $isSurplus ? 'text-[#00E676]' : 'text-red-400' }} font-mono-numbers block">
                Rp {{ number_format($totalSpent, 0, ',', '.') }}
            </span>
            <span class="text-[10px] text-[#9CA3AF] mt-1 block">{{ $trip->budget_percentage }}% dari plafon</span>
        </div>

        <!-- 3. Evaluasi Surplus / Defisit -->
        <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-4">
            <span class="text-[11px] font-medium text-[#9CA3AF] block mb-1">Selisih Anggaran</span>
            <span class="text-lg sm:text-xl font-bold {{ $isSurplus ? 'text-[#00E676]' : 'text-red-400' }} font-mono-numbers block">
                {{ $isSurplus ? '+ Rp ' . number_format($surplusDeficit, 0, ',', '.') : '- Rp ' . number_format(abs($surplusDeficit), 0, ',', '.') }}
            </span>
            <span class="text-[10px] {{ $isSurplus ? 'text-[#00E676]' : 'text-red-400' }} mt-1 font-semibold block">
                {{ $isSurplus ? 'Hemat / Sisa Budget' : 'Defisit / Overbudget' }}
            </span>
        </div>

        <!-- 4. Rata-rata Pengeluaran Per Hari -->
        <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-4">
            <span class="text-[11px] font-medium text-[#9CA3AF] block mb-1">Rata-rata Harian</span>
            <span class="text-lg sm:text-xl font-bold text-white font-mono-numbers block">
                {{ $trip->formatted_average_daily_spent }}
            </span>
            <span class="text-[10px] text-[#9CA3AF] mt-1 block">Per hari liburan ({{ $trip->days_duration }} hari)</span>
        </div>
    </div>

    <!-- MAIN 2-COLUMN EVALUATION GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- LEFT COLUMN (Category Breakdown & Daily Trend) -->
        <div class="lg:col-span-6 space-y-6">
            <!-- Category Distribution Card -->
            <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#5865F2]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                            </svg>
                            Distribusi Kategori Liburan
                        </h2>
                        <p class="text-xs text-[#9CA3AF] mt-0.5">Proporsi pos biaya selama perjalanan ini</p>
                    </div>
                    <span class="text-xs text-[#9CA3AF] font-medium">{{ $categoryBreakdown->count() }} Kategori</span>
                </div>

                @if($categoryBreakdown->isEmpty())
                    <p class="text-xs text-[#9CA3AF] py-6 text-center">Belum ada pengeluaran tercatat.</p>
                @else
                    <div class="space-y-4">
                        @foreach($categoryBreakdown as $cat)
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $cat['color'] }}"></span>
                                        <span class="font-semibold text-white">{{ $cat['name'] }}</span>
                                        <span class="text-[11px] text-[#9CA3AF]">({{ $cat['count'] }}x)</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold text-white font-mono-numbers">
                                            Rp {{ number_format($cat['total'], 0, ',', '.') }}
                                        </span>
                                        <span class="text-[11px] text-[#9CA3AF] ml-1.5 font-medium">
                                            {{ $cat['percentage'] }}%
                                        </span>
                                    </div>
                                </div>

                                <div class="w-full h-2 bg-[#0D0E12] rounded-full overflow-hidden">
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        style="width: {{ $cat['percentage'] }}%; background-color: {{ $cat['color'] }};"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Daily Spending Pattern Card -->
            <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#00E676]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                            Pola Pengeluaran Per Hari Liburan
                        </h2>
                        <p class="text-xs text-[#9CA3AF] mt-0.5">Analisis hari pengeluaran terbesar</p>
                    </div>
                </div>

                @if($dailySpending->isEmpty())
                    <p class="text-xs text-[#9CA3AF] py-6 text-center">Belum ada data pengeluaran per hari.</p>
                @else
                    <div class="space-y-3">
                        @php
                            $maxDaily = $dailySpending->max('total') ?: 1;
                        @endphp
                        @foreach($dailySpending as $daily)
                            @php
                                $barWidth = round(($daily['total'] / $maxDaily) * 100);
                            @endphp
                            <div class="bg-[#0D0E12] border border-[#262C37]/80 rounded-2xl p-3 flex items-center justify-between gap-3">
                                <div class="w-16 flex-shrink-0">
                                    <span class="text-xs font-bold text-white block">{{ $daily['date'] }}</span>
                                    <span class="text-[10px] text-[#9CA3AF]">{{ $daily['count'] }} transaksi</span>
                                </div>

                                <div class="flex-1">
                                    <div class="w-full h-2 bg-[#17181E] rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-[#5865F2] to-[#00E676] rounded-full" style="width: {{ $barWidth }}%"></div>
                                    </div>
                                </div>

                                <div class="w-28 text-right flex-shrink-0">
                                    <span class="text-xs font-extrabold text-white font-mono-numbers">
                                        Rp {{ number_format($daily['total'], 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- RIGHT COLUMN (Chronological Transaction Log) -->
        <div class="lg:col-span-6">
            <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-5 sm:p-6 flex flex-col h-full">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#818CF8]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            Log Transaksi Perjalanan
                        </h2>
                        <p class="text-xs text-[#9CA3AF] mt-0.5">Catatan seluruh pengeluaran selama perjalanan ini</p>
                    </div>

                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-[#262C37] text-white">
                        {{ $transactions->count() }} Transaksi
                    </span>
                </div>

                @if($transactions->isEmpty())
                    <div class="flex-1 flex flex-col items-center justify-center py-12 text-center">
                        <p class="text-xs text-[#9CA3AF] mb-3">Belum ada transaksi pengeluaran yang di-tag ke trip ini.</p>
                        @if($trip->status === 'active')
                            <button
                                type="button"
                                x-data
                                @click="$dispatch('open-transaction-modal', { mode: 'trip', tripId: {{ $trip->id }} })"
                                class="px-4 py-2 rounded-xl bg-[#5865F2] hover:bg-[#4752C4] text-xs font-semibold text-white transition-all cursor-pointer"
                            >
                                + Catat Pengeluaran Sekarang
                            </button>
                        @endif
                    </div>
                @else
                    <div class="space-y-3 overflow-y-auto max-h-[600px] pr-1">
                        @foreach($transactions as $tx)
                            @php
                                $cat = $tx->category;
                                $iconColor = $cat?->color ?? '#5865F2';
                                $catIcon = $cat?->icon ?? 'tag';
                                $catName = strtolower($cat?->name ?? '');
                            @endphp
                            <div class="bg-[#0D0E12] border border-[#262C37]/80 hover:border-[#5865F2]/40 rounded-2xl p-3.5 flex items-center justify-between gap-3 transition-colors">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div
                                        class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                        style="background-color: {{ $iconColor }}1A; color: {{ $iconColor }}; border: 1px solid {{ $iconColor }}33;"
                                    >
                                        @if($catIcon === 'utensils' || str_contains($catName, 'makan') || str_contains($catName, 'kuliner'))
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                            </svg>
                                        @elseif($catIcon === 'car' || str_contains($catName, 'transport') || str_contains($catName, 'grab'))
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V3.75m0 3.75H7.5m9 0h2.25" />
                                            </svg>
                                        @elseif($catIcon === 'bed' || str_contains($catName, 'inap') || str_contains($catName, 'hotel'))
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 .414-.336.75-.75.75h-1.5a.75.75 0 0 1-.75-.75v-1.5H6.75v1.5a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1-.75-.75v-4.25m16.5 0a3.75 3.75 0 0 0-3.75-3.75H12m8.25 3.75H3.75m0 0a3.75 3.75 0 0 1 3.75-3.75h1.5m-5.25 3.75v-8.5c0-.414.336-.75.75-.75h1.5c.414 0 .75.336.75.75v4.25" />
                                            </svg>
                                        @elseif($catIcon === 'ticket' || str_contains($catName, 'tiket') || str_contains($catName, 'wisata'))
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                                            </svg>
                                        @elseif($catIcon === 'coffee' || str_contains($catName, 'kopi') || str_contains($catName, 'cafe'))
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5a3.75 3.75 0 0 0 3.75 3.75h1.5a3.75 3.75 0 0 0 3.75-3.75v-1.5m-9 1.5a3.75 3.75 0 0 1-3.75 3.75h-1.5A3.75 3.75 0 0 1 3 8.25v-1.5m9 7.5v4.5m0 0H8.25m3.75 0h3.75" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                            </svg>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <h4 class="text-xs font-bold text-white truncate">{{ $tx->title }}</h4>
                                        <p class="text-[11px] text-[#9CA3AF] truncate">
                                            <span>{{ $tx->category?->name ?? 'Lainnya' }}</span>
                                            <span>&bull;</span>
                                            <span>{{ $tx->transaction_date ? $tx->transaction_date->translatedFormat('d M, H:i') : '-' }}</span>
                                            @if($tx->wallet)
                                                <span>&bull;</span>
                                                <span class="text-[#818CF8]">{{ $tx->wallet->name }}</span>
                                            @endif
                                        </p>
                                        @if($tx->note)
                                            <p class="text-[10px] text-gray-400 italic truncate mt-0.5">"{{ $tx->note }}"</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-right flex-shrink-0">
                                    <span class="text-xs sm:text-sm font-extrabold text-white font-mono-numbers block">
                                        {{ $tx->formatted_amount }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
