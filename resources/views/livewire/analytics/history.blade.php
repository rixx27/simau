<div>
    <!-- Top Bar with Back & Title -->
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
            <a
                href="{{ route('dashboard') }}"
                wire:navigate
                class="w-10 h-10 rounded-2xl bg-[#17181E] border border-[#262C37] flex items-center justify-center text-[#9CA3AF] hover:text-white hover:border-[#5865F2] transition-colors"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-white tracking-tight">Statistik & Riwayat</h1>
                <p class="text-xs text-[#9CA3AF] hidden sm:block">Analisis tren pengeluaran mingguan dan mutasi transaksi</p>
            </div>
        </div>

        <button
            type="button"
            wire:click="toggleFilters"
            aria-label="Tampilkan atau sembunyikan filter lanjutan"
            aria-expanded="{{ $showFilters ? 'true' : 'false' }}"
            class="w-10 h-10 rounded-2xl bg-[#17181E] border {{ $showFilters || $typeFilter !== 'all' || $walletFilter || $categoryFilter ? 'border-[#5865F2] text-white' : 'border-[#262C37] text-[#9CA3AF]' }} flex items-center justify-center hover:text-white hover:border-[#5865F2] transition-colors cursor-pointer"
            title="Filter Lanjutan"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
            </svg>
        </button>
    </div>

    <!-- Period Filter Pills -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-6 scrollbar-none">
        @foreach($periodOptions as $pKey => $pLabel)
            <button
                type="button"
                wire:click="selectPeriod('{{ $pKey }}')"
                class="px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all duration-200 cursor-pointer {{ $period === $pKey ? 'bg-white text-[#0D0E12] shadow-lg shadow-white/20' : 'bg-[#17181E] text-[#9CA3AF] border border-[#262C37] hover:text-white hover:border-[#5865F2]' }}"
            >
                {{ $pLabel }}
            </button>
        @endforeach
    </div>

    <!-- RESPONSIVE GRID (2 columns on desktop >= 1024px, single column on mobile) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- LEFT COLUMN (Chart & Spending Summary & Categories) -->
        <div class="lg:col-span-6 space-y-6">

            <!-- Spending Summary Metric -->
            <div class="space-y-1">
                <div class="flex items-center justify-between text-xs text-[#9CA3AF]">
                    <span>
                        {{ $period === 'this_week' ? 'Pengeluaran Harian' : 'Pengeluaran Per Minggu' }} &bull; Rata-rata Rp {{ number_format($weeklyAverage, 0, ',', '.') }}
                    </span>
                    @if($totalMonthExpense > 0)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-500/15 text-red-400 font-semibold text-[11px]">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                            </svg>
                            Arus Kas Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#00E676]/15 text-[#00E676] font-semibold text-[11px]">
                            Belum Ada Pengeluaran
                        </span>
                    @endif
                </div>

                <div class="flex items-baseline gap-2">
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white font-mono-numbers">
                        Rp {{ number_format($totalMonthExpense, 0, ',', '.') }}
                    </h2>
                    <span class="text-xs text-[#9CA3AF] font-medium">Total {{ $periodLabel }}</span>
                </div>
            </div>

            <!-- TREN SIKLUS MINGGUAN WAVE CHART -->
            <div
                x-data="{
                    chart: null,
                    getRadii(w) {
                        return [
                            w === 1 ? 8 : 4,
                            w === 2 ? 8 : 4,
                            w === 3 ? 8 : 4,
                            w === 4 ? 8 : 4
                        ];
                    },
                    async initChart() {
                        const canvas = this.$refs.canvas;
                        if (!canvas) return;

                        const ChartCtor = window.ensureChartJs ? await window.ensureChartJs() : window.Chart;
                        if (!ChartCtor) return;

                        if (this.chart) {
                            this.chart.destroy();
                            this.chart = null;
                        }

                        const ctx = canvas.getContext('2d');
                        const data = @js($chartData);
                        const initialWeek = @js($selectedWeek);

                        // Vertical gradient fill
                        const gradient = ctx.createLinearGradient(0, 0, 0, 180);
                        gradient.addColorStop(0, 'rgba(88, 101, 242, 0.45)');
                        gradient.addColorStop(0.7, 'rgba(88, 101, 242, 0.1)');
                        gradient.addColorStop(1, 'rgba(88, 101, 242, 0.0)');

                        this.chart = new ChartCtor(ctx, {
                            type: 'line',
                            data: {
                                labels: data.labels,
                                datasets: [{
                                    label: 'Pengeluaran',
                                    data: data.values,
                                    borderColor: '#5865F2',
                                    borderWidth: 3,
                                    tension: 0.4,
                                    fill: true,
                                    backgroundColor: gradient,
                                    pointBackgroundColor: '#5865F2',
                                    pointBorderColor: '#FFFFFF',
                                    pointBorderWidth: 2,
                                    pointRadius: this.getRadii(initialWeek),
                                    pointHoverRadius: 8,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: { duration: 300 },
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: '#FFFFFF',
                                        titleColor: '#17181E',
                                        bodyColor: '#17181E',
                                        bodyFont: { family: 'JetBrains Mono', weight: 'bold', size: 13 },
                                        padding: 10,
                                        cornerRadius: 12,
                                        displayColors: false,
                                        callbacks: {
                                            label: function(context) {
                                                return 'Rp ' + Number(context.parsed.y).toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: { display: false, drawBorder: false },
                                        ticks: {
                                            color: '#9CA3AF',
                                            font: { family: 'Plus Jakarta Sans', size: 11, weight: '500' }
                                        }
                                    },
                                    y: {
                                        display: false,
                                        grid: { display: false },
                                        suggestedMin: 0,
                                        suggestedMax: 100000
                                    }
                                }
                            }
                        });

                        Livewire.on('chart-data-updated', (event) => {
                            if (this.chart && event.data) {
                                this.chart.data.labels = event.data.labels;
                                this.chart.data.datasets[0].data = event.data.values;
                                this.chart.update();
                            }
                        });

                        Livewire.on('week-selected', (event) => {
                            if (this.chart) {
                                this.chart.data.datasets[0].pointRadius = this.getRadii(event.week);
                                this.chart.update('none');
                            }
                        });
                    }
                }"
                x-init="initChart()"
                class="bg-[#17181E] border border-[#262C37] rounded-3xl p-5"
            >
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#5865F2] shadow-[0_0_8px_#5865F2]"></span>
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Tren Siklus Mingguan</h3>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#262C37] text-[#9CA3AF] uppercase">
                        {{ $periodLabel }}
                    </span>
                </div>

                <!-- Active Week Tooltip Dynamic Indicator -->
                @if($period !== 'this_week')
                    @php
                        $activeWeekIndex = ($selectedWeek ? $selectedWeek - 1 : 2);
                        $activeWeekValue = $chartData['values'][$activeWeekIndex] ?? 0;
                        $alignClass = match($selectedWeek) {
                            1 => 'justify-start pl-4 sm:pl-8',
                            2 => 'justify-start pl-24 sm:pl-32',
                            3 => 'justify-end pr-24 sm:pr-32',
                            4 => 'justify-end pr-4 sm:pr-8',
                            default => 'justify-center',
                        };
                    @endphp
                    <div class="flex {{ $alignClass }} mb-1 transition-all duration-300">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white text-[#0D0E12] shadow-xl text-xs font-extrabold font-mono-numbers">
                            <span class="text-[10px] text-[#5865F2] font-semibold">{{ $selectedWeek ? 'Min ' . $selectedWeek : 'Total' }}:</span>
                            <span>Rp {{ number_format($activeWeekValue, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endif

                <!-- Chart Canvas Container (wire:ignore prevents Livewire morphing from wiping the canvas) -->
                <div class="relative h-44 w-full" wire:ignore>
                    <canvas x-ref="canvas"></canvas>
                </div>

                <!-- Interactive Week Selectors -->
                @if($period !== 'this_week')
                    <div class="flex items-center justify-around pt-3 border-t border-[#262C37]/60 mt-2">
                        @foreach(['Min 1', 'Min 2', 'Min 3', 'Min 4'] as $index => $wLabel)
                            @php
                                $wNum = $index + 1;
                                $isActive = $selectedWeek === $wNum;
                            @endphp
                            <button
                                type="button"
                                wire:click="selectWeek({{ $wNum }})"
                                class="px-3 py-1 rounded-lg text-xs font-semibold transition-all duration-150 cursor-pointer {{ $isActive ? 'bg-[#5865F2] text-white shadow-md' : 'text-[#9CA3AF] hover:text-white' }}"
                            >
                                {{ $wLabel }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- KATEGORI PENGELUARAN HORIZONTAL CARDS -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <button
                        type="button"
                        wire:click="toggleAllCategories"
                        class="text-sm font-bold text-white flex items-center gap-1 cursor-pointer hover:text-[#818CF8] transition-colors"
                    >
                        <span>Kategori Pengeluaran</span>
                        <svg class="w-4 h-4 text-[#9CA3AF] transition-transform {{ $showAllCategories ? 'rotate-90' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                    <span class="text-xs text-[#9CA3AF]">{{ $categories->count() }} Kategori</span>
                </div>

                @php
                    $visibleCategories = $showAllCategories ? $categories : $categories->take(3);
                @endphp
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pb-1">
                    @foreach($visibleCategories as $cat)
                        @php
                            $spent = $cat->transactions_sum_amount ?? 0;
                            $catIcon = $cat->icon ?? 'tag';
                            $isCatFiltered = $categoryFilter === $cat->id;
                        @endphp
                        <button
                            type="button"
                            wire:click="selectCategoryFilter({{ $cat->id }})"
                            class="text-left bg-[#17181E] border {{ $isCatFiltered ? 'border-[#5865F2] ring-1 ring-[#5865F2]' : 'border-[#262C37]' }} rounded-2xl p-3.5 flex flex-col justify-between hover:border-[#5865F2]/50 transition-colors cursor-pointer"
                        >
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-2" style="background-color: {{ $cat->color }}1A; color: {{ $cat->color }};">
                                @if($catIcon === 'utensils')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                    </svg>
                                @elseif($catIcon === 'car')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V3.75m0 3.75H7.5m9 0h2.25" />
                                    </svg>
                                @elseif($catIcon === 'bed')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 .414-.336.75-.75.75h-1.5a.75.75 0 0 1-.75-.75v-1.5H6.75v1.5a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1-.75-.75v-4.25m16.5 0a3.75 3.75 0 0 0-3.75-3.75H12m8.25 3.75H3.75m0 0a3.75 3.75 0 0 1 3.75-3.75h1.5m-5.25 3.75v-8.5c0-.414.336-.75.75-.75h1.5c.414 0 .75.336.75.75v4.25" />
                                    </svg>
                                @elseif($catIcon === 'ticket')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                                    </svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                @endif
                            </div>

                            <span class="text-xs font-semibold text-[#9CA3AF] truncate block">
                                {{ $cat->name }}
                            </span>
                            <span class="text-sm font-bold text-white font-mono-numbers mt-0.5 block truncate">
                                Rp {{ number_format($spent, 0, ',', '.') }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN (Aktivitas Terkini Transaction Feed) -->
        <div class="lg:col-span-6">
            <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-5 md:p-6 h-full flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-white">Aktivitas Terkini</h3>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-[#262C37] text-[#9CA3AF]">
                        @if($period === 'this_week')
                            Minggu Ini
                        @elseif($selectedWeek)
                            Min {{ $selectedWeek }} ({{ ($selectedWeek - 1) * 7 + 1 }} - {{ $selectedWeek === 4 ? 'Akhir' : $selectedWeek * 7 }} {{ $periodLabel }})
                        @else
                            Semua Minggu {{ $periodLabel }}
                        @endif
                    </span>
                </div>

                <!-- Search Bar & Optional Filters -->
                <div class="space-y-2.5 mb-4">
                    <div class="relative">
                        <label for="mutasi-search" class="sr-only">Cari transaksi</label>
                        <input
                            id="mutasi-search"
                            type="search"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Cari judul atau catatan transaksi..."
                            class="w-full bg-[#0D0E12] border border-[#262C37] rounded-xl pl-9 pr-3 py-2 text-xs text-white placeholder-[#9CA3AF] focus:outline-none focus:border-[#5865F2]"
                        />
                        <svg class="w-4 h-4 text-[#9CA3AF] absolute left-3 top-2.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>

                    @if($showFilters || $typeFilter !== 'all' || $walletFilter || $categoryFilter)
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 p-2.5 rounded-2xl bg-[#0D0E12] border border-[#262C37]">
                            <div>
                                <label for="filter-type" class="sr-only">Tipe transaksi</label>
                                <select
                                    id="filter-type"
                                    wire:model.live="typeFilter"
                                    class="w-full bg-[#17181E] border border-[#262C37] rounded-xl px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                                >
                                    <option value="all">Semua Tipe</option>
                                    <option value="expense">Pengeluaran</option>
                                    <option value="income">Pemasukan</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                            </div>

                            <div>
                                <label for="filter-wallet" class="sr-only">Filter dompet</label>
                                <select
                                    id="filter-wallet"
                                    wire:model.live="walletFilter"
                                    class="w-full bg-[#17181E] border border-[#262C37] rounded-xl px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                                >
                                    <option value="">Semua Dompet</option>
                                    @foreach($wallets as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="filter-category" class="sr-only">Filter kategori</label>
                                <select
                                    id="filter-category"
                                    wire:model.live="categoryFilter"
                                    class="w-full bg-[#17181E] border border-[#262C37] rounded-xl px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-[#5865F2]"
                                >
                                    <option value="">Semua Kategori</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if($search !== '' || $typeFilter !== 'all' || $walletFilter || $categoryFilter)
                                <div class="sm:col-span-3 flex justify-end">
                                    <button
                                        type="button"
                                        wire:click="resetFilters"
                                        class="text-[11px] font-semibold text-[#818CF8] hover:text-white cursor-pointer"
                                    >
                                        Reset Filter
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="space-y-3 flex-1 overflow-y-auto">
                    @forelse($activities as $act)
                        @php
                            $cat = $act->category;
                            $catIcon = $cat?->icon ?? 'tag';
                            $iconColor = $cat?->color ?? '#5865F2';
                        @endphp
                        <div class="bg-[#0D0E12] border border-[#262C37]/80 hover:border-[#5865F2]/40 rounded-2xl p-3.5 flex items-center justify-between gap-3 transition-colors">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div
                                    class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                    style="background-color: {{ $iconColor }}1A; color: {{ $iconColor }}; border: 1px solid {{ $iconColor }}33;"
                                >
                                    @if($catIcon === 'car')
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V3.75m0 3.75H7.5m9 0h2.25" />
                                        </svg>
                                    @elseif($catIcon === 'utensils')
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                        </svg>
                                    @elseif($catIcon === 'bed')
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 .414-.336.75-.75.75h-1.5a.75.75 0 0 1-.75-.75v-1.5H6.75v1.5a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1-.75-.75v-4.25m16.5 0a3.75 3.75 0 0 0-3.75-3.75H12m8.25 3.75H3.75m0 0a3.75 3.75 0 0 1 3.75-3.75h1.5m-5.25 3.75v-8.5c0-.414.336-.75.75-.75h1.5c.414 0 .75.336.75.75v4.25" />
                                        </svg>
                                    @elseif($catIcon === 'coffee')
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5a3.75 3.75 0 0 0 3.75 3.75h1.5a3.75 3.75 0 0 0 3.75-3.75v-1.5m-9 1.5a3.75 3.75 0 0 1-3.75 3.75h-1.5A3.75 3.75 0 0 1 3 8.25v-1.5m9 7.5v4.5m0 0H8.25m3.75 0h3.75" />
                                        </svg>
                                    @elseif($catIcon === 'ticket')
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
                                    <h4 class="text-sm font-semibold text-white truncate">{{ $act->title }}</h4>
                                    <p class="text-xs text-[#9CA3AF] truncate">
                                        {{ $act->formatted_date_time }} &bull; <span class="text-[#818CF8]">{{ $cat?->name ?? ($act->type === 'transfer' ? 'Transfer' : 'Kategori') }}</span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2.5 flex-shrink-0">
                                <div class="text-right">
                                    <span class="text-sm font-bold font-mono-numbers block {{ $act->type === 'expense' ? 'text-white' : 'text-[#00E676]' }}">
                                        {{ $act->formatted_amount }}
                                    </span>
                                    <span class="text-[11px] text-[#9CA3AF] truncate max-w-[100px] block">
                                        {{ $act->wallet->account_label ?? ($act->wallet->name ?? 'Kas') }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-1">
                                    @if($act->type !== 'transfer')
                                        <button
                                            type="button"
                                            x-data
                                            @click="$dispatch('open-edit-transaction-modal', { transactionId: {{ $act->id }} })"
                                            aria-label="Edit transaksi {{ $act->title }}"
                                            title="Edit transaksi"
                                            class="w-8 h-8 rounded-lg bg-[#17181E] border border-[#262C37] text-[#9CA3AF] hover:text-white hover:border-[#5865F2] flex items-center justify-center transition-colors cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                            </svg>
                                        </button>
                                    @endif

                                    <button
                                        type="button"
                                        wire:click="deleteTransaction({{ $act->id }})"
                                        wire:confirm="Hapus transaksi '{{ $act->title }}' dan sesuaikan saldo dompet?"
                                        aria-label="Hapus transaksi {{ $act->title }}"
                                        title="Hapus transaksi"
                                        class="w-8 h-8 rounded-lg bg-[#17181E] border border-[#262C37] text-[#9CA3AF] hover:text-red-400 hover:border-red-500/40 flex items-center justify-center transition-colors cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-[#9CA3AF]">
                            <p class="text-xs">Tidak ada aktivitas yang cocok dengan filter atau rentang waktu ini.</p>
                        </div>
                    @endforelse
                </div>

                @if($activities->hasPages())
                    <div class="pt-4 mt-4 border-t border-[#262C37]/60">
                        {{ $activities->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
