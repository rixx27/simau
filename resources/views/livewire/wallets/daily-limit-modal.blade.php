<div>
    @if($isOpen)
        <!-- Backdrop -->
        <div
            x-data
            @keydown.escape.window="$wire.closeModal()"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex justify-center items-end md:items-center p-0 md:p-4 transition-all duration-300"
        >
            <!-- Modal Box -->
            <div
                @click.outside="$wire.closeModal()"
                class="bg-[#17181E] border border-[#262C37] w-full md:max-w-md rounded-t-3xl md:rounded-3xl shadow-2xl flex flex-col max-h-[92vh] md:max-h-[90vh] overflow-hidden animate-in fade-in zoom-in-95 duration-200"
            >
                <!-- Mobile Drag Indicator -->
                <div class="pt-3 pb-1 flex justify-center md:hidden">
                    <span class="w-10 h-1 bg-[#262C37] rounded-full"></span>
                </div>

                <!-- Modal Header -->
                <div class="px-5 pt-3 pb-4 flex items-center justify-between border-b border-[#262C37]/60">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-[#5865F2]/20 border border-[#5865F2]/40 flex items-center justify-center text-[#5865F2]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white tracking-tight">Atur Batas Pengeluaran</h3>
                            <p class="text-[11px] text-[#9CA3AF]">
                                {{ $wallet ? $wallet->name : 'Kas Utama' }} &bull; Kontrol Anggaran Harian
                            </p>
                        </div>
                    </div>

                    <button
                        wire:click="closeModal"
                        type="button"
                        class="w-8 h-8 rounded-full bg-[#1E2028] border border-[#262C37] flex items-center justify-center text-[#9CA3AF] hover:text-white hover:border-[#5865F2] transition-colors cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="px-5 py-4 overflow-y-auto space-y-4">
                    <!-- Toggle Batas Harian Aktif / Nonaktif -->
                    <div class="bg-[#0D0E12] border border-[#262C37] rounded-2xl p-3.5 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold text-white block">Status Batas Pengeluaran</span>
                            <span class="text-[11px] text-[#9CA3AF] block mt-0.5">
                                {{ $isEnabled ? 'Aktif memantau pengeluaran harian' : 'Tanpa batas pengeluaran harian' }}
                            </span>
                        </div>

                        <button
                            type="button"
                            wire:click="toggleEnabled"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $isEnabled ? 'bg-[#5865F2]' : 'bg-[#262C37]' }}"
                        >
                            <span
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $isEnabled ? 'translate-x-5' : 'translate-x-0' }}"
                            ></span>
                        </button>
                    </div>

                    @if($isEnabled)
                        <!-- Nominal Card Input -->
                        <div class="bg-[#0D0E12] border border-[#5865F2]/40 rounded-2xl p-4 transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] font-semibold text-[#9CA3AF] uppercase tracking-wider">
                                    Target Batas per Hari
                                </span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#5865F2]/20 border border-[#5865F2]/40 text-[#5865F2]">
                                    IDR / HARI
                                </span>
                            </div>

                            <div class="flex items-baseline justify-center py-2">
                                <span class="text-2xl md:text-3xl font-extrabold mr-1 text-white">Rp</span>
                                <input
                                    type="number"
                                    wire:model.live="dailyLimit"
                                    step="10000"
                                    min="10000"
                                    class="w-48 md:w-56 text-2xl md:text-3xl font-extrabold bg-transparent border-0 focus:ring-0 p-0 text-white font-mono-numbers focus:outline-none"
                                    placeholder="350000"
                                />
                            </div>
                            @error('dailyLimit')
                                <p class="text-xs text-red-400 text-center mt-1">{{ $message }}</p>
                            @enderror

                            <!-- Quick Preset Buttons -->
                            <div class="pt-3 border-t border-[#262C37]/50 mt-2 space-y-2">
                                <div class="flex items-center justify-between text-[11px] text-[#9CA3AF]">
                                    <span>Pilihan Cepat:</span>
                                    <span>Klik untuk memilih</span>
                                </div>
                                <div class="grid grid-cols-5 gap-1.5">
                                    @foreach([100000 => '100rb', 200000 => '200rb', 350000 => '350rb', 500000 => '500rb', 1000000 => '1 Jt'] as $val => $label)
                                        <button
                                            type="button"
                                            wire:click="setPreset({{ $val }})"
                                            class="py-1.5 px-1 rounded-xl text-xs font-semibold text-center transition-all cursor-pointer {{ $dailyLimit == $val ? 'bg-[#5865F2] text-white shadow-md shadow-[#5865F2]/30' : 'bg-[#17181E] border border-[#262C37] text-[#9CA3AF] hover:text-white hover:border-[#5865F2]/40' }}"
                                        >
                                            {{ $label }}
                                        </button>
                                    @endforeach
                                </div>

                                <div class="flex items-center justify-center gap-1.5 pt-1">
                                    <button
                                        type="button"
                                        wire:click="addAmount(50000)"
                                        class="px-3 py-1 rounded-lg bg-[#17181E] hover:bg-[#262C37] text-xs font-medium text-[#9CA3AF] hover:text-white transition-colors cursor-pointer"
                                    >
                                        +50rb
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="addAmount(100000)"
                                        class="px-3 py-1 rounded-lg bg-[#17181E] hover:bg-[#262C37] text-xs font-medium text-[#9CA3AF] hover:text-white transition-colors cursor-pointer"
                                    >
                                        +100rb
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Status Evaluasi Pengeluaran Hari Ini (NO EMOJIS, PURE VECTOR ICONS) -->
                        @php
                            $limitVal = (float) ($dailyLimit ?: 0);
                            $remaining = max(0, $limitVal - $todaySpent);
                            $percentUsed = $limitVal > 0 ? min(100, round(($todaySpent / $limitVal) * 100)) : 0;
                            $isOver = $todaySpent > $limitVal;
                            $isNear = !$isOver && $percentUsed >= 80;
                        @endphp
                        <div class="bg-[#0D0E12] border {{ $isOver ? 'border-red-500/40' : ($isNear ? 'border-amber-400/40' : 'border-[#00E676]/30') }} rounded-2xl p-3.5 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-white flex items-center gap-1.5">
                                    @if($isOver)
                                        <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                        </svg>
                                        <span class="text-red-400">Status: Melampaui Batas</span>
                                    @elseif($isNear)
                                        <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                        </svg>
                                        <span class="text-amber-400">Status: Mendekati Batas</span>
                                    @else
                                        <svg class="w-4 h-4 text-[#00E676] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                        </svg>
                                        <span class="text-[#00E676]">Status: Pengeluaran Terkontrol</span>
                                    @endif
                                </span>
                                <span class="text-[11px] font-mono-numbers text-[#9CA3AF]">
                                    Hari ini: Rp {{ number_format($todaySpent, 0, ',', '.') }}
                                </span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="w-full h-1.5 bg-[#262C37] rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-300 {{ $isOver ? 'bg-red-500 shadow-[0_0_8px_#EF4444]' : ($isNear ? 'bg-amber-400 shadow-[0_0_8px_#F59E0B]' : 'bg-[#00E676] shadow-[0_0_8px_#00E676]') }}"
                                    style="width: {{ $percentUsed }}%"
                                ></div>
                            </div>

                            <p class="text-[11px] text-[#9CA3AF]">
                                @if($isOver)
                                    Pengeluaran hari ini sudah melebihi pagu sebesar <b class="text-red-400">Rp {{ number_format($todaySpent - $limitVal, 0, ',', '.') }}</b>.
                                @else
                                    Sisa kuota pengeluaran untuk hari ini: <b class="text-white">Rp {{ number_format($remaining, 0, ',', '.') }}</b> ({{ 100 - $percentUsed }}% tersisa).
                                @endif
                            </p>
                        </div>
                    @else
                        <!-- Info Nonaktif -->
                        <div class="bg-[#0D0E12] border border-[#262C37] rounded-2xl p-4 text-center space-y-1.5">
                            <div class="w-10 h-10 rounded-full bg-[#1E2028] border border-[#262C37] flex items-center justify-center text-[#9CA3AF] mx-auto mb-1">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                            </div>
                            <h4 class="text-xs font-bold text-white">Batas Harian Dinonaktifkan</h4>
                            <p class="text-[11px] text-[#9CA3AF]">
                                Anda bebas mencatat pengeluaran harian tanpa batasan nominal atau indikator peringatan budget.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Footer & Action Buttons -->
                <div class="p-5 border-t border-[#262C37]/60 bg-[#17181E] flex items-center gap-2.5">
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="flex-1 py-3 px-3 rounded-2xl bg-[#0D0E12] hover:bg-[#1E2028] border border-[#262C37] text-xs font-semibold text-[#9CA3AF] hover:text-white transition-colors cursor-pointer text-center"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        wire:click="save"
                        class="flex-[2] py-3 px-4 rounded-2xl bg-gradient-to-r from-[#5865F2] to-[#4F46E5] hover:from-[#4F46E5] hover:to-[#4338CA] text-white font-bold text-xs tracking-wide flex items-center justify-center gap-1.5 shadow-xl shadow-[#5865F2]/40 hover:shadow-[#5865F2]/60 active:scale-[0.98] transition-all cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span>Simpan Pengaturan</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
