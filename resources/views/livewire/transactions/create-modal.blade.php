<div>
    @if($isOpen)
        <!-- Backdrop -->
        <div
            x-data
            @keydown.escape.window="$wire.closeModal()"
            role="dialog"
            aria-modal="true"
            aria-label="Form Transaksi"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex justify-center items-end md:items-center p-0 md:p-4 transition-all duration-300"
        >
            <!-- Modal Card (Bottom sheet on mobile, centered modal on desktop) -->
            <div
                @click.outside="$wire.closeModal()"
                class="bg-[#17181E] border border-[#262C37] w-full md:max-w-lg rounded-t-3xl md:rounded-3xl shadow-2xl flex flex-col max-h-[92vh] md:max-h-[90vh] overflow-hidden animate-in fade-in zoom-in-95 duration-200"
            >
                <!-- Mobile drag handle -->
                <div class="pt-3 pb-1 flex justify-center md:hidden">
                    <span class="w-10 h-1 bg-[#262C37] rounded-full"></span>
                </div>

                <!-- Modal Header -->
                <div class="px-5 pt-3 pb-4 flex items-center justify-between border-b border-[#262C37]/60">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl {{ $mode === 'income' ? 'bg-[#00E676]/20 border border-[#00E676]/40 text-[#00E676]' : 'bg-[#5865F2]/20 border border-[#5865F2]/40 text-[#5865F2]' }} flex items-center justify-center">
                            @if($mode === 'income')
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white tracking-tight">
                                {{ $mode === 'income' ? 'Tambah Uang ke Kas (Pemasukan)' : ($mode === 'trip' ? 'Catat Pengeluaran Trip' : 'Catat Pengeluaran Harian') }}
                            </h3>
                            <p class="text-[11px] text-[#9CA3AF]">
                                {{ $mode === 'income' ? 'Saldo kas akan bertambah secara otomatis' : 'Saldo kas akan dipotong sesuai nominal' }}
                            </p>
                        </div>
                    </div>

                    <button
                        wire:click="closeModal"
                        type="button"
                        aria-label="Tutup modal transaksi"
                        class="w-8 h-8 rounded-full bg-[#1E2028] border border-[#262C37] flex items-center justify-center text-[#9CA3AF] hover:text-white hover:border-[#5865F2] transition-colors cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Scrollable Body -->
                <div class="px-5 py-4 overflow-y-auto space-y-4">
                    <!-- Mode Switcher: Harian vs Pemasukan Kas vs Trip -->
                    <div class="bg-[#0D0E12] p-1.5 rounded-2xl flex border border-[#262C37]/80 gap-1.5">
                        <button
                            type="button"
                            wire:click="setMode('daily')"
                            class="flex-1 py-2 px-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition-all duration-200 {{ $mode === 'daily' ? 'bg-[#5865F2] text-white shadow-lg shadow-[#5865F2]/30' : 'text-[#9CA3AF] hover:text-white' }}"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                            </svg>
                            <span>Harian</span>
                        </button>
                        <button
                            type="button"
                            wire:click="setMode('income')"
                            class="flex-1 py-2 px-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition-all duration-200 {{ $mode === 'income' ? 'bg-[#00E676] text-black font-bold shadow-lg shadow-[#00E676]/30' : 'text-[#9CA3AF] hover:text-white' }}"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>+ Pemasukan</span>
                        </button>
                        <button
                            type="button"
                            wire:click="setMode('trip')"
                            class="flex-1 py-2 px-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 transition-all duration-200 {{ $mode === 'trip' ? 'bg-[#5865F2] text-white shadow-lg shadow-[#5865F2]/30' : 'text-[#9CA3AF] hover:text-white' }}"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                            <span>Trip</span>
                        </button>
                    </div>

                    <!-- Nominal Transaksi Card -->
                    <div class="bg-[#0D0E12] border {{ $mode === 'income' ? 'border-[#00E676]/40' : 'border-[#262C37]' }} rounded-2xl p-4 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-semibold text-[#9CA3AF] uppercase tracking-wider">
                                {{ $mode === 'income' ? 'Nominal Uang Masuk ke Kas' : 'Nominal Transaksi' }}
                            </span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $mode === 'income' ? 'bg-[#00E676]/20 border border-[#00E676]/40 text-[#00E676]' : 'bg-[#17181E] border border-[#262C37] text-[#9CA3AF]' }}">
                                {{ $mode === 'income' ? '+ UANG MASUK' : 'IDR' }}
                            </span>
                        </div>

                        <!-- Amount Input with Rp formatting -->
                        <div class="flex items-baseline justify-center py-2">
                            <span class="text-2xl md:text-3xl font-extrabold mr-1 {{ $mode === 'income' ? 'text-[#00E676]' : 'text-white' }}">
                                {{ $mode === 'income' ? '+Rp' : 'Rp' }}
                            </span>
                            <input
                                type="number"
                                wire:model.live="amount"
                                step="any"
                                min="0"
                                class="w-44 md:w-56 text-2xl md:text-3xl font-extrabold bg-transparent border-0 focus:ring-0 p-0 font-mono-numbers focus:outline-none {{ $mode === 'income' ? 'text-[#00E676]' : 'text-white' }}"
                                placeholder="0"
                            />
                        </div>
                        @if($amount && is_numeric($amount) && $amount > 0)
                            @php
                                $val = (float) $amount;
                                $formatted = (floor($val) == $val) ? number_format($val, 0, ',', '.') : number_format($val, 2, ',', '.');
                            @endphp
                            <div class="text-center text-[11px] {{ $mode === 'income' ? 'text-[#00E676]' : 'text-[#818CF8]' }} font-mono-numbers pb-1">
                                Terbaca: Rp {{ $formatted }}
                            </div>
                        @endif
                        @error('amount')
                            <p class="text-xs text-red-400 text-center mt-1">{{ $message }}</p>
                        @enderror

                        <!-- Quick Increment Buttons -->
                        <div class="flex items-center justify-center gap-1.5 pt-2 border-t border-[#262C37]/50 mt-2 flex-wrap">
                            <button
                                type="button"
                                wire:click="addAmount(50000)"
                                class="px-2.5 py-1.5 rounded-lg bg-[#17181E] hover:bg-[#262C37] text-xs font-medium text-[#9CA3AF] hover:text-white transition-colors cursor-pointer"
                            >
                                +50rb
                            </button>
                            <button
                                type="button"
                                wire:click="addAmount(100000)"
                                class="px-2.5 py-1.5 rounded-lg bg-[#17181E] hover:bg-[#262C37] text-xs font-medium text-[#9CA3AF] hover:text-white transition-colors cursor-pointer"
                            >
                                +100rb
                            </button>
                            <button
                                type="button"
                                wire:click="addAmount(500000)"
                                class="px-2.5 py-1.5 rounded-lg bg-[#17181E] hover:bg-[#262C37] text-xs font-medium text-[#9CA3AF] hover:text-white transition-colors cursor-pointer"
                            >
                                +500rb
                            </button>
                            <button
                                type="button"
                                wire:click="addAmount(1000000)"
                                class="px-2.5 py-1.5 rounded-lg bg-[#17181E] hover:bg-[#262C37] text-xs font-medium text-[#9CA3AF] hover:text-white transition-colors cursor-pointer"
                            >
                                +1 Jt
                            </button>
                        </div>
                    </div>

                    <!-- Destination Trip Selector (Only in Trip Mode) -->
                    @if($mode === 'trip')
                        <div class="bg-[#0D0E12] border border-[#5865F2]/40 rounded-2xl p-3.5 space-y-1.5">
                            <label class="block text-xs font-semibold text-white flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#5865F2]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                                <span>Pilih Destinasi Liburan / Trip</span>
                            </label>

                            <div class="relative">
                                <select
                                    wire:model.live="selectedTripId"
                                    class="w-full bg-[#17181E] border border-[#262C37] rounded-xl p-2.5 text-xs text-white appearance-none focus:outline-none focus:border-[#5865F2] pr-10 cursor-pointer"
                                >
                                    @foreach($trips as $t)
                                        <option value="{{ $t->id }}">
                                            {{ $t->name }} ({{ $t->destination }} &bull; {{ $t->status_label }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#9CA3AF]">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                            </div>
                            @if($selectedTrip)
                                <div class="flex items-center justify-between text-[11px] text-[#9CA3AF] px-1 pt-1">
                                    <span>Anggaran: <b class="text-white">{{ $selectedTrip->formatted_budget }}</b></span>
                                    <span>Terpakai: <b class="text-[#00E676]">{{ $selectedTrip->formatted_total_spent }}</b></span>
                                </div>
                            @endif
                            @error('selectedTripId')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <!-- Category Grid -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-white">{{ $mode === 'income' ? 'Pilih Kategori Pemasukan' : 'Pilih Kategori' }}</span>
                            <span class="text-[11px] text-[#9CA3AF]">Wajib dipilih</span>
                        </div>

                        <div class="grid grid-cols-3 gap-2.5">
                            @foreach($categories as $cat)
                                @php
                                    $isSelected = $selectedCategoryId === $cat->id;
                                @endphp
                                <button
                                    type="button"
                                    wire:click="selectCategory({{ $cat->id }})"
                                    class="relative flex flex-col items-center justify-center p-3 rounded-2xl border transition-all duration-150 cursor-pointer {{ $isSelected ? ($mode === 'income' ? 'bg-[#00E676]/15 border-[#00E676] ring-1 ring-[#00E676]' : 'bg-[#5865F2]/15 border-[#5865F2] ring-1 ring-[#5865F2]') : 'bg-[#0D0E12] border-[#262C37] hover:border-[#5865F2]/40 hover:bg-[#1E2028]' }}"
                                >
                                    @if($isSelected)
                                        <span class="absolute top-2 right-2 w-2 h-2 rounded-full {{ $mode === 'income' ? 'bg-[#00E676]' : 'bg-[#5865F2]' }}"></span>
                                    @endif

                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-1.5" style="background-color: {{ $cat->color }}20; color: {{ $cat->color }};">
                                        @if($cat->icon === 'utensils')
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                            </svg>
                                        @elseif($cat->icon === 'car')
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V3.75m0 3.75H7.5m9 0h2.25" />
                                            </svg>
                                        @elseif($cat->icon === 'bed')
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 .414-.336.75-.75.75h-1.5a.75.75 0 0 1-.75-.75v-1.5H6.75v1.5a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1-.75-.75v-4.25m16.5 0a3.75 3.75 0 0 0-3.75-3.75H12m8.25 3.75H3.75m0 0a3.75 3.75 0 0 1 3.75-3.75h1.5m-5.25 3.75v-8.5c0-.414.336-.75.75-.75h1.5c.414 0 .75.336.75.75v4.25" />
                                            </svg>
                                        @elseif($cat->icon === 'ticket')
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                                            </svg>
                                        @elseif($cat->icon === 'coffee')
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5a3.75 3.75 0 0 0 3.75 3.75h1.5a3.75 3.75 0 0 0 3.75-3.75v-1.5m-9 1.5a3.75 3.75 0 0 1-3.75 3.75h-1.5A3.75 3.75 0 0 1 3 8.25v-1.5m9 7.5v4.5m0 0H8.25m3.75 0h3.75" />
                                            </svg>
                                        @elseif($cat->icon === 'wallet')
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" />
                                            </svg>
                                        @elseif($cat->icon === 'banknotes')
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v8.25m0 0a60.207 60.207 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V14.25m-17.25-6H3m0 0a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V8.25m-17.25 0V6m17.25 8.25V6m0 0a60.207 60.207 0 0 1 1.75-2.101" />
                                            </svg>
                                        @elseif($cat->icon === 'gift')
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H4.5a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                            </svg>
                                        @elseif($cat->icon === 'credit-card')
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <span class="text-xs font-medium text-white truncate max-w-full">{{ $cat->name }}</span>
                                </button>
                            @endforeach

                            <!-- Add New Category Card -->
                            <button
                                type="button"
                                wire:click="toggleCreateCategory"
                                class="flex flex-col items-center justify-center p-3 rounded-2xl border border-dashed border-[#262C37] hover:border-[#5865F2] hover:bg-[#1E2028] transition-all duration-150 cursor-pointer"
                            >
                                <div class="w-9 h-9 rounded-xl bg-[#17181E] border border-[#262C37] flex items-center justify-center mb-1.5 text-[#9CA3AF]">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-[#9CA3AF]">+ Kategori</span>
                            </button>
                        </div>

                        <!-- Inline category creation input -->
                        @if($isCreatingCategory)
                            <div class="mt-3 p-3 rounded-2xl bg-[#0D0E12] border border-[#5865F2]/40 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-white">Buat Kategori Baru</span>
                                    <button type="button" wire:click="toggleCreateCategory" class="text-xs text-[#9CA3AF] hover:text-white">&times;</button>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="text"
                                        wire:model="newCategoryName"
                                        placeholder="Nama kategori..."
                                        class="flex-1 bg-[#17181E] border border-[#262C37] rounded-xl px-3 py-1.5 text-xs text-white placeholder-[#9CA3AF] focus:outline-none focus:border-[#5865F2]"
                                    />
                                    <input
                                        type="color"
                                        wire:model="newCategoryColor"
                                        class="w-8 h-8 rounded-lg bg-transparent border-0 cursor-pointer"
                                        title="Pilih Warna"
                                    />
                                    <button
                                        type="button"
                                        wire:click="saveNewCategory"
                                        class="px-3 py-1.5 rounded-xl bg-[#5865F2] text-white text-xs font-semibold hover:bg-[#4752C4] transition-colors cursor-pointer"
                                    >
                                        Simpan
                                    </button>
                                </div>
                                @error('newCategoryName')
                                    <p class="text-[11px] text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        @error('selectedCategoryId')
                            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sumber Dana Transaksi -->
                    <div>
                        <label class="block text-xs font-semibold text-white mb-1.5">
                            {{ $mode === 'income' ? 'Rekening Kas Penerima' : 'Sumber Dana (Kas / Dompet)' }}
                        </label>
                        <div class="relative">
                            <select
                                wire:model.live="selectedWalletId"
                                class="w-full bg-[#0D0E12] border border-[#262C37] rounded-2xl p-3 text-xs text-white appearance-none focus:outline-none focus:border-[#5865F2] pr-10 cursor-pointer"
                            >
                                @foreach($wallets as $wallet)
                                    <option value="{{ $wallet->id }}">
                                        {{ $wallet->name }} (Saldo: {{ $wallet->formatted_balance }} &bull; {{ $wallet->account_label ?? ucfirst($wallet->type) }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#9CA3AF]">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan Transaksi -->
                    <div class="bg-[#0D0E12] border border-[#262C37] rounded-2xl p-3">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="w-4 h-4 text-[#9CA3AF]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                            </svg>
                            <span class="text-[11px] font-semibold text-[#9CA3AF] uppercase tracking-wider">Judul / Catatan Transaksi</span>
                        </div>
                        <input
                            type="text"
                            wire:model="title"
                            placeholder="{{ $mode === 'income' ? 'Contoh: Gaji Bulanan, Uang Saku, Saldo Awal Kas...' : ($mode === 'trip' ? 'Contoh: Tiket Kereta, Penginapan, Kuliner...' : 'Contoh: Sarapan Pagi, Bensin, Kopi...') }}"
                            class="w-full bg-transparent border-0 text-sm text-white focus:outline-none focus:ring-0 p-0"
                        />
                        @error('title')
                            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Waktu Pencatatan -->
                    <div class="bg-[#0D0E12] border border-[#262C37] rounded-2xl p-3 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-[#9CA3AF]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                            </svg>
                            <div>
                                <span class="text-[10px] font-semibold text-[#9CA3AF] uppercase tracking-wider block">Waktu Pencatatan</span>
                                <span class="text-xs text-white font-medium">
                                    {{ \Carbon\Carbon::parse($transactionDate)->translatedFormat('d M Y • H:i') }} WIB
                                </span>
                            </div>
                        </div>

                        <button
                            type="button"
                            wire:click="$toggle('showDatePicker')"
                            class="text-xs font-semibold text-[#5865F2] hover:text-[#4752C4] cursor-pointer"
                        >
                            {{ $showDatePicker ? 'Tutup' : 'Ubah' }}
                        </button>
                    </div>

                    @if($showDatePicker)
                        <div class="p-3 bg-[#0D0E12] border border-[#262C37] rounded-2xl">
                            <input
                                type="datetime-local"
                                wire:model.live="transactionDate"
                                class="w-full bg-[#17181E] border border-[#262C37] rounded-xl p-2 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                            />
                        </div>
                    @endif
                </div>

                <!-- Footer & Action Button -->
                <div class="p-5 border-t border-[#262C37]/60 bg-[#17181E] space-y-3">
                    <button
                        type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="w-full py-3.5 px-4 rounded-2xl {{ $mode === 'income' ? 'bg-gradient-to-r from-[#00E676] to-[#00C853] hover:from-[#00C853] hover:to-[#00B048] text-black font-extrabold shadow-xl shadow-[#00E676]/30 hover:shadow-[#00E676]/50' : 'bg-gradient-to-r from-[#5865F2] to-[#4F46E5] hover:from-[#4F46E5] hover:to-[#4338CA] text-white font-bold shadow-xl shadow-[#5865F2]/40 hover:shadow-[#5865F2]/60' }} text-sm tracking-wide flex items-center justify-center gap-2 active:scale-[0.98] transition-all duration-200 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="save">{{ $mode === 'income' ? '+ Tambah Uang ke Kas Sekarang' : 'Simpan Transaksi' }}</span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                        <svg wire:loading.remove wire:target="save" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </button>

                    <div class="flex items-center justify-center gap-1.5 text-[11px] text-[#9CA3AF]">
                        <svg class="w-3.5 h-3.5 text-[#00E676]" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span>Tersimpan aman di buku kas SIMAU Ledger</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
