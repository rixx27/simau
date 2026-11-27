<div>
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                <span>Trip &amp; Perjalanan Liburan</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-[#5865F2]/20 border border-[#5865F2]/40 text-[#818CF8] font-semibold">
                    Travel Ledger
                </span>
            </h1>
            <p class="text-xs text-[#9CA3AF] mt-0.5">
                Rencanakan anggaran liburan, pantau pengeluaran perjalanan aktif, dan evaluasi riwayat perjalanan pasca-trip.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <button
                type="button"
                x-data
                @click="$dispatch('open-transaction-modal', { mode: 'trip' })"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#00E676]/15 hover:bg-[#00E676]/25 border border-[#00E676]/40 text-xs font-semibold text-[#00E676] transition-all cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Catat Pengeluaran Trip</span>
            </button>

            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-[#5865F2] to-[#4F46E5] hover:from-[#4F46E5] hover:to-[#4338CA] text-xs font-semibold text-white shadow-lg shadow-[#5865F2]/25 transition-all cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span> Rencana Trip Baru</span>
            </button>
        </div>
    </div>

    <!-- Summary Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-4">
            <span class="text-xs text-[#9CA3AF] font-medium block mb-1">Total Plafon Anggaran</span>
            <span class="text-xl font-bold text-white font-mono-numbers block">
                Rp {{ number_format($totalTripBudget, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-[#9CA3AF] mt-1 block">Dari seluruh trip yang terdaftar</span>
        </div>

        <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-4">
            <span class="text-xs text-[#9CA3AF] font-medium block mb-1">Trip Sedang Berjalan</span>
            <div class="flex items-center gap-2">
                <span class="text-xl font-bold text-[#00E676] font-mono-numbers">
                    {{ $activeTripsCount }} Trip Aktif
                </span>
                @if($activeTripsCount > 0)
                    <span class="w-2 h-2 rounded-full bg-[#00E676] animate-pulse"></span>
                @endif
            </div>
            <span class="text-[11px] text-[#9CA3AF] mt-1 block">Siap dicatat pengeluarannya</span>
        </div>

        <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-4">
            <span class="text-xs text-[#9CA3AF] font-medium block mb-1">Riwayat Trip Selesai</span>
            <span class="text-xl font-bold text-[#818CF8] font-mono-numbers block">
                {{ $completedTripsCount }} Trip
            </span>
            <span class="text-[11px] text-[#9CA3AF] mt-1 block">Tersedia rekap finansial & evaluasi</span>
        </div>
    </div>

    <!-- Filter Status Tabs -->
    <div class="flex items-center gap-2 mb-6 overflow-x-auto pb-1 scrollbar-none">
        <button
            type="button"
            wire:click="$set('statusFilter', 'all')"
            class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all cursor-pointer {{ $statusFilter === 'all' ? 'bg-[#5865F2] text-white shadow-md shadow-[#5865F2]/25' : 'bg-[#17181E] text-[#9CA3AF] hover:text-white border border-[#262C37]' }}"
        >
            Semua Perjalanan ({{ $trips->count() }})
        </button>

        <button
            type="button"
            wire:click="$set('statusFilter', 'active')"
            class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all cursor-pointer {{ $statusFilter === 'active' ? 'bg-[#00E676] text-black shadow-md shadow-[#00E676]/25' : 'bg-[#17181E] text-[#9CA3AF] hover:text-white border border-[#262C37]' }}"
        >
             Sedang Berlangsung ({{ $activeTripsCount }})
        </button>

        <button
            type="button"
            wire:click="$set('statusFilter', 'planned')"
            class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all cursor-pointer {{ $statusFilter === 'planned' ? 'bg-[#5865F2] text-white shadow-md shadow-[#5865F2]/25' : 'bg-[#17181E] text-[#9CA3AF] hover:text-white border border-[#262C37]' }}"
        >
             Terencana ({{ $plannedTripsCount }})
        </button>

        <button
            type="button"
            wire:click="$set('statusFilter', 'completed')"
            class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all cursor-pointer {{ $statusFilter === 'completed' ? 'bg-[#818CF8] text-white shadow-md shadow-[#818CF8]/25' : 'bg-[#17181E] text-[#9CA3AF] hover:text-white border border-[#262C37]' }}"
        >
             Selesai / Riwayat ({{ $completedTripsCount }})
        </button>
    </div>

    <!-- TRIPS GRID -->
    @if($trips->isEmpty())
        <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-[#5865F2]/10 border border-[#5865F2]/30 flex items-center justify-center text-[#5865F2] mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-white mb-1">Belum Ada Perjalanan</h3>
            <p class="text-xs text-[#9CA3AF] max-w-sm mx-auto mb-5">
                Tidak ada data trip untuk filter yang dipilih. Buat rencana perjalanan liburan baru untuk mengontrol anggaran Anda.
            </p>
            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#5865F2] hover:bg-[#4752C4] text-xs font-semibold text-white transition-all cursor-pointer"
            >
                + Buat Rencana Trip
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($trips as $trip)
                @php
                    $percentage = $trip->budget_percentage;
                    $isOverbudget = $trip->total_spent > $trip->budget;
                @endphp
                <div class="bg-[#17181E] border border-[#262C37] hover:border-[#5865F2]/50 rounded-3xl overflow-hidden flex flex-col justify-between transition-all duration-200 group">
                    <!-- Top Cover & Header -->
                    <div>
                        <div class="relative h-36 w-full overflow-hidden bg-[#0D0E12]">
                            <img
                                src="{{ $trip->cover_image ?? 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=600&q=80' }}"
                                alt="{{ $trip->name }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 opacity-80"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-[#17181E] via-transparent to-black/40"></div>

                            <!-- Destination Badge -->
                            <div class="absolute top-3 left-3 flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-black/60 backdrop-blur-md border border-white/10 text-white text-[11px] font-semibold">
                                <svg class="w-3.5 h-3.5 text-[#38BDF8]" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                                <span>{{ $trip->destination }}</span>
                            </div>

                            <!-- Status Badge -->
                            <div class="absolute top-3 right-3">
                                @if($trip->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#00E676]/20 backdrop-blur-md border border-[#00E676]/50 text-[#00E676] text-[10px] font-bold uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#00E676] animate-pulse"></span>
                                        Aktif
                                    </span>
                                @elseif($trip->status === 'planned')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#5865F2]/20 backdrop-blur-md border border-[#5865F2]/50 text-[#818CF8] text-[10px] font-bold uppercase tracking-wider">
                                        Rencana
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#374151]/80 backdrop-blur-md border border-gray-600 text-gray-300 text-[10px] font-bold uppercase tracking-wider">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                        <span>Selesai</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5">
                            <h3 class="text-base font-bold text-white group-hover:text-[#5865F2] transition-colors truncate">
                                {{ $trip->name }}
                            </h3>

                            <div class="flex items-center gap-3 text-xs text-[#9CA3AF] mt-1 mb-4">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                    {{ $trip->formatted_date_range }}
                                </span>
                                <span>&bull;</span>
                                <span>{{ $trip->days_duration }} Hari</span>
                            </div>

                            <!-- Financial Progress -->
                            <div class="space-y-2 bg-[#0D0E12] border border-[#262C37]/80 rounded-2xl p-3.5 mb-3">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-[#9CA3AF]">Terpakai:</span>
                                    <span class="font-bold font-mono-numbers {{ $isOverbudget ? 'text-red-400' : 'text-white' }}">
                                        {{ $trip->formatted_total_spent }}
                                    </span>
                                </div>

                                <div class="w-full h-2 bg-[#262C37] rounded-full overflow-hidden">
                                    <div
                                        class="h-full rounded-full transition-all duration-500 {{ $isOverbudget ? 'bg-red-500 shadow-[0_0_8px_#EF4444]' : 'bg-[#00E676] shadow-[0_0_8px_#00E676]' }}"
                                        style="width: {{ min($percentage, 100) }}%"
                                    ></div>
                                </div>

                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="{{ $isOverbudget ? 'text-red-400' : 'text-[#00E676]' }} font-semibold">
                                        {{ $percentage }}% 
                                    </span>
                                    <span class="text-[#9CA3AF]">
                                        Anggaran: <b class="text-white font-mono-numbers">{{ $trip->formatted_budget }}</b>
                                    </span>
                                </div>
                            </div>

                            @if($trip->notes)
                                <p class="text-[11px] text-[#9CA3AF] line-clamp-2 italic mb-1">
                                    "{{ $trip->notes }}"
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Bottom Action Buttons Based on Trip Status -->
                    <div class="p-5 pt-0">
                        @if($trip->status === 'active')
                            <!-- ACTIVE TRIP ACTIONS -->
                            <div class="space-y-2 pt-3 border-t border-[#262C37]/60">
                                <button
                                    type="button"
                                    x-data
                                    @click="$dispatch('open-transaction-modal', { mode: 'trip', tripId: {{ $trip->id }} })"
                                    class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-[#5865F2] to-[#4F46E5] hover:from-[#4F46E5] hover:to-[#4338CA] text-xs font-semibold text-white transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-md shadow-[#5865F2]/20"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    <span>+ Catat Pengeluaran Trip</span>
                                </button>

                                <div class="flex items-center gap-2">
                                    <a
                                        href="{{ route('trips.report', $trip) }}"
                                        wire:navigate
                                        class="flex-1 py-2 px-3 rounded-xl bg-[#0D0E12] hover:bg-[#1E2028] border border-[#262C37] text-xs font-medium text-[#9CA3AF] hover:text-white transition-all text-center"
                                    >
                                        Pantau Rekap
                                    </a>

                                    <button
                                        type="button"
                                        wire:click="completeTrip({{ $trip->id }})"
                                        wire:confirm="Apakah perjalanan liburan ini sudah selesai? Status akan diubah menjadi Selesai dan laporan pasca-trip siap dievaluasi."
                                        class="py-2 px-3 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 text-xs font-medium text-amber-400 transition-all cursor-pointer whitespace-nowrap inline-flex items-center gap-1.5"
                                        title="Selesaikan Perjalanan"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.73a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
                                        </svg>
                                        <span>Selesaikan Trip</span>
                                    </button>
                                </div>
                            </div>

                        @elseif($trip->status === 'completed')
                            <!-- COMPLETED TRIP ACTIONS (POST-TRIP REVIEW BUTTON) -->
                            <div class="space-y-2 pt-3 border-t border-[#262C37]/60">
                                <a
                                    href="{{ route('trips.report', $trip) }}"
                                    wire:navigate
                                    class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-[#00E676]/20 to-[#00E676]/10 hover:bg-[#00E676] border border-[#00E676]/50 hover:border-[#00E676] text-xs font-bold text-[#00E676] hover:text-black transition-all flex items-center justify-center gap-2 shadow-sm group-hover:shadow-md cursor-pointer"
                                >
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                    </svg>
                                    <span>Lihat Rekap Pasca-Trip</span>
                                </a>

                                <a
                                    href="{{ route('trips.export', $trip) }}"
                                    class="w-full py-1.5 px-3 rounded-xl bg-[#0D0E12] hover:bg-[#1E2028] border border-[#262C37] text-[11px] font-medium text-[#9CA3AF] hover:text-white transition-all flex items-center justify-center gap-1.5"
                                >
                                    <svg class="w-3.5 h-3.5 text-[#00E676]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    <span>Unduh Excel Rekap Perjalanan</span>
                                </a>
                            </div>

                        @else
                            <!-- PLANNED TRIP ACTIONS -->
                            <div class="flex items-center gap-2 pt-3 border-t border-[#262C37]/60">
                                <button
                                    type="button"
                                    wire:click="activateTrip({{ $trip->id }})"
                                    class="flex-1 py-2.5 px-3 rounded-xl bg-[#5865F2]/20 hover:bg-[#5865F2] border border-[#5865F2]/40 text-xs font-semibold text-[#818CF8] hover:text-white transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                                >
                                    <span>Mulai Trip</span>
                                </button>
                                <a
                                    href="{{ route('trips.report', $trip) }}"
                                    wire:navigate
                                    class="py-2.5 px-3 rounded-xl bg-[#0D0E12] hover:bg-[#1E2028] border border-[#262C37] text-xs font-medium text-[#9CA3AF] hover:text-white transition-all"
                                    title="Detail Rencana"
                                >
                                    Detail
                                </a>
                            </div>
                        @endif

                        <!-- Edit & Delete Trip Row -->
                        <div class="flex items-center justify-end gap-2 mt-2 pt-2 border-t border-[#262C37]/40">
                            <button
                                type="button"
                                wire:click="openEditModal({{ $trip->id }})"
                                class="px-2.5 py-1 rounded-lg bg-[#0D0E12] hover:bg-[#1E2028] border border-[#262C37] text-[11px] font-medium text-[#9CA3AF] hover:text-white transition-colors cursor-pointer"
                            >
                                Edit Trip
                            </button>
                            <button
                                type="button"
                                wire:click="deleteTrip({{ $trip->id }})"
                                wire:confirm="Hapus trip '{{ $trip->name }}'? Transaksi terkait tetap aman di riwayat mutasi."
                                class="px-2.5 py-1 rounded-lg bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-[11px] font-medium text-red-400 transition-colors cursor-pointer"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- MODAL BUAT RENCANA TRIP BARU -->
    @if($isCreateModalOpen)
        <div
            x-data
            @keydown.escape.window="$wire.closeCreateModal()"
            role="dialog"
            aria-modal="true"
            aria-label="{{ $editingTripId ? 'Edit Rencana Trip' : 'Rencana Liburan atau Trip Baru' }}"
            class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex justify-center items-end md:items-center p-0 md:p-4 transition-all duration-200"
        >
            <div
                @click.outside="$wire.closeCreateModal()"
                class="bg-[#17181E] border border-[#262C37] w-full md:max-w-lg rounded-t-3xl md:rounded-3xl shadow-2xl flex flex-col max-h-[92vh] overflow-hidden animate-in fade-in zoom-in-95"
            >
                <!-- Header -->
                <div class="px-5 pt-4 pb-3 flex items-center justify-between border-b border-[#262C37]/60">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-[#5865F2]/15 border border-[#5865F2]/40 flex items-center justify-center text-[#5865F2]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white tracking-tight">{{ $editingTripId ? 'Edit Rencana Trip' : 'Rencana Liburan / Trip Baru' }}</h3>
                            <p class="text-xs text-[#9CA3AF]">Tentukan destinasi & batas anggaran perjalanan Anda</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeCreateModal" aria-label="Tutup modal trip" class="w-8 h-8 rounded-full bg-[#0D0E12] border border-[#262C37] flex items-center justify-center text-[#9CA3AF] hover:text-white cursor-pointer">&times;</button>
                </div>

                <!-- Form Body -->
                <form wire:submit.prevent="saveTrip" class="p-5 space-y-4 overflow-y-auto">
                    <!-- Nama Perjalanan -->
                    <div>
                        <label class="block text-xs font-semibold text-white mb-1.5">Nama Perjalanan / Trip</label>
                        <input
                            type="text"
                            wire:model="name"
                            placeholder="Contoh: Liburan Eksplor Bali 2026"
                            class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl p-3 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-[#5865F2]"
                        />
                        @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Destinasi -->
                    <div>
                        <label class="block text-xs font-semibold text-white mb-1.5">Kota / Lokasi Destinasi</label>
                        <input
                            type="text"
                            wire:model="destination"
                            placeholder="Contoh: Denpasar & Ubud, Bali"
                            class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl p-3 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-[#5865F2]"
                        />
                        @error('destination') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Tanggal Mulai & Selesai -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-white mb-1.5">Tanggal Berangkat</label>
                            <input
                                type="date"
                                wire:model="startDate"
                                class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                            />
                            @error('startDate') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-white mb-1.5">Tanggal Pulang</label>
                            <input
                                type="date"
                                wire:model="endDate"
                                class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                            />
                            @error('endDate') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Plafon Anggaran -->
                    <div class="bg-[#0D0E12] border border-[#262C37] rounded-2xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-semibold text-[#9CA3AF] uppercase tracking-wider">Plafon Anggaran Liburan</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#17181E] border border-[#262C37] text-[#9CA3AF]">IDR</span>
                        </div>
                        <div class="flex items-baseline justify-center py-2">
                            <span class="text-2xl font-extrabold text-white mr-1">Rp</span>
                            <input
                                type="number"
                                wire:model="budget"
                                step="50000"
                                min="10000"
                                class="w-48 text-2xl font-extrabold text-white bg-transparent border-0 focus:ring-0 p-0 font-mono-numbers focus:outline-none text-center"
                                placeholder="0"
                            />
                        </div>
                        @error('budget') <p class="text-xs text-red-400 text-center mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Status Awal -->
                    <div>
                        <label class="block text-xs font-semibold text-white mb-1.5">Status Perjalanan</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                type="button"
                                wire:click="$set('status', 'planned')"
                                class="p-2.5 rounded-xl border text-center text-xs font-semibold cursor-pointer transition-all {{ $status === 'planned' ? 'bg-[#5865F2]/20 border-[#5865F2] text-white' : 'bg-[#0D0E12] border-[#262C37] text-[#9CA3AF]' }}"
                            >
                                 Masih Terencana
                            </button>
                            <button
                                type="button"
                                wire:click="$set('status', 'active')"
                                class="p-2.5 rounded-xl border text-center text-xs font-semibold cursor-pointer transition-all {{ $status === 'active' ? 'bg-[#00E676]/20 border-[#00E676] text-[#00E676]' : 'bg-[#0D0E12] border-[#262C37] text-[#9CA3AF]' }}"
                            >
                                 Sedang Berlangsung
                            </button>
                        </div>
                    </div>

                    <!-- Catatan / Itinerary -->
                    <div>
                        <label class="block text-xs font-semibold text-white mb-1.5">Catatan / Rencana Destinasi (Opsional)</label>
                        <textarea
                            wire:model="notes"
                            rows="2"
                            placeholder="Catatan hotel, spot wisata wajib, atau oleh-oleh..."
                            class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl p-3 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-[#5865F2]"
                        ></textarea>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="pt-3 border-t border-[#262C37]/60 flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            wire:click="closeCreateModal"
                            class="px-4 py-2.5 rounded-xl bg-[#0D0E12] hover:bg-[#1E2028] border border-[#262C37] text-xs font-semibold text-[#9CA3AF] hover:text-white transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#5865F2] to-[#4F46E5] hover:from-[#4F46E5] hover:to-[#4338CA] text-xs font-semibold text-white shadow-lg shadow-[#5865F2]/25 transition-all cursor-pointer"
                        >
                            {{ $editingTripId ? 'Simpan Perubahan' : 'Simpan Rencana Perjalanan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
