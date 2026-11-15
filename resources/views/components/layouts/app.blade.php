<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'SIMAU - Neo Financial' }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#0D0E12] text-[#F3F4F6] min-h-screen font-sans selection:bg-[#5865F2] selection:text-white antialiased">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- DESKTOP SIDEBAR (>= 768px) -->
        <aside class="hidden md:flex md:w-64 flex-col fixed inset-y-0 left-0 bg-[#17181E] border-r border-[#262C37] z-30">
            <!-- Brand Header -->
            <div class="p-6 flex items-center gap-3 border-b border-[#262C37]/60">
                <div class="relative">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#5865F2] to-[#3B82F6] flex items-center justify-center shadow-lg shadow-[#5865F2]/25">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" />
                        </svg>
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-[#00E676] border-2 border-[#17181E] rounded-full"></span>
                </div>
                <div>
                    <h1 class="text-base font-bold tracking-tight text-white flex items-center gap-1.5">
                        SIMAU
                        <span class="text-[10px] uppercase font-semibold tracking-wider px-1.5 py-0.5 rounded bg-[#5865F2]/20 text-[#5865F2] border border-[#5865F2]/30">v1.2</span>
                    </h1>
                    <p class="text-xs text-[#9CA3AF] font-medium">Neo Financial</p>
                </div>
            </div>

            <!-- Primary Action Button: + Catat Transaksi -->
            <div class="px-5 pt-5 pb-3">
                <button
                    type="button"
                    x-data
                    @click="$dispatch('open-transaction-modal', { mode: 'expense' })"
                    class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-gradient-to-r from-[#5865F2] to-[#4F46E5] hover:from-[#4F46E5] hover:to-[#4338CA] text-white font-semibold text-sm transition-all duration-200 shadow-lg shadow-[#5865F2]/30 hover:shadow-[#5865F2]/50 active:scale-[0.98] cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Catat Transaksi</span>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-3 space-y-1.5 overflow-y-auto">
                @php
                    $navItems = [
                        [
                            'name' => 'Beranda',
                            'route' => 'dashboard',
                            'active' => request()->routeIs('dashboard') || request()->routeIs('home'),
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />'
                        ],
                        [
                            'name' => 'Mutasi',
                            'route' => 'mutasi',
                            'active' => request()->routeIs('mutasi'),
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />'
                        ],
                        [
                            'name' => 'Tabungan',
                            'route' => 'tabungan',
                            'active' => request()->routeIs('tabungan'),
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />'
                        ],
                        [
                            'name' => 'Akun',
                            'route' => 'profile',
                            'active' => request()->routeIs('profile*') || request()->routeIs('settings*'),
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />'
                        ],
                    ];
                @endphp

                @foreach($navItems as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        wire:navigate
                        class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-sm font-medium transition-colors duration-150 {{ $item['active'] ? 'bg-[#5865F2] text-white shadow-md shadow-[#5865F2]/25 font-semibold' : 'text-[#9CA3AF] hover:text-white hover:bg-[#1E2028]' }}"
                    >
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            {!! $item['icon'] !!}
                        </svg>
                        <span>{{ $item['name'] }}</span>
                    </a>
                @endforeach
            </nav>

            <!-- Bottom User Profile Card -->
            <div class="p-4 border-t border-[#262C37]/60">
                <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-3 p-2 rounded-xl hover:bg-[#1E2028] transition-colors group">
                    <img
                        src="{{ auth()->user()?->avatar_url ?? asset('avatars/krisna.png') }}"
                        alt="Avatar"
                        class="w-10 h-10 rounded-full object-cover ring-2 ring-[#5865F2]/40"
                    />
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate group-hover:text-[#5865F2] transition-colors">
                            {{ auth()->user()?->name ?? 'Krisna Pradana' }}
                        </p>
                        <p class="text-xs text-[#9CA3AF] truncate">
                            {{ auth()->user()?->username ? '@'.auth()->user()->username : 'krisnapradana' }}
                        </p>
                    </div>
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT WRAPPER -->
        <div class="flex-1 md:pl-64 flex flex-col min-h-screen">
            <!-- MOBILE TOP HEADER (< 768px) -->
            <header class="md:hidden sticky top-0 z-20 bg-[#0D0E12]/90 backdrop-blur-md border-b border-[#262C37]/70 px-4 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('profile') }}" wire:navigate class="relative">
                        <img
                            src="{{ auth()->user()?->avatar_url ?? asset('avatars/krisna.png') }}"
                            alt="Avatar"
                            class="w-9 h-9 rounded-full object-cover ring-2 ring-[#5865F2]/40"
                        />
                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-[#00E676] border-2 border-[#0D0E12] rounded-full"></span>
                    </a>
                    <div>
                        <h2 class="text-sm font-bold text-white tracking-tight flex items-center gap-1">
                            SIMAU
                        </h2>
                        <p class="text-[11px] text-[#9CA3AF]">Neo Financial</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a
                        href="{{ route('mutasi') }}"
                        wire:navigate
                        class="w-9 h-9 rounded-full bg-[#17181E] border border-[#262C37] flex items-center justify-center text-[#9CA3AF] hover:text-white hover:border-[#5865F2]/50 transition-colors"
                        title="Cari & Filter"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </a>
                    <button
                        type="button"
                        x-data
                        @click="$dispatch('open-transaction-modal', { mode: 'expense' })"
                        class="w-9 h-9 rounded-full bg-[#5865F2]/20 border border-[#5865F2]/40 flex items-center justify-center text-[#5865F2] hover:bg-[#5865F2] hover:text-white transition-colors cursor-pointer"
                        title="Catat Baru"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </button>
                </div>
            </header>

            <!-- DESKTOP TOP BAR (>= 768px) -->
            <header class="hidden md:flex items-center justify-between px-8 py-4 bg-[#17181E]/40 border-b border-[#262C37]/50 backdrop-blur-sm sticky top-0 z-20">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-[#17181E] border border-[#262C37] text-[#9CA3AF] flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#00E676] animate-pulse"></span>
                        Cloud Ledger Aktif
                    </span>
                    <span class="text-xs text-[#9CA3AF] font-mono-numbers">
                        {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('export.excel') }}"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#17181E] border border-[#262C37] text-xs font-medium text-[#9CA3AF] hover:text-white hover:border-[#5865F2] transition-colors"
                        title="Unduh Excel (.xlsx)"
                    >
                        <svg class="w-3.5 h-3.5 text-[#00E676]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span>Unduh .xlsx</span>
                    </a>
                    <a
                        href="{{ route('profile') }}"
                        wire:navigate
                        class="flex items-center gap-2.5 pl-3 border-l border-[#262C37]"
                    >
                        <img
                            src="{{ auth()->user()?->avatar_url ?? asset('avatars/krisna.png') }}"
                            alt="Avatar"
                            class="w-7 h-7 rounded-full object-cover ring-1 ring-[#5865F2]/50"
                        />
                        <span class="text-xs font-medium text-white">{{ auth()->user()?->name ?? 'Krisna' }}</span>
                    </a>
                </div>
            </header>

            <!-- MAIN CONTENT AREA -->
            <main class="flex-1 w-full max-w-6xl mx-auto p-4 sm:p-6 lg:p-8 pb-28 md:pb-8">
                {{ $slot }}
            </main>
        </div>

        <!-- MOBILE FLOATING BOTTOM NAVIGATION BAR (< 768px) -->
        <nav class="md:hidden fixed bottom-4 inset-x-4 max-w-sm mx-auto bg-[#17181E]/95 backdrop-blur-xl border border-[#262C37] rounded-3xl py-2 px-3 flex items-center justify-around z-40 shadow-2xl shadow-black/80">
            @foreach($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    wire:navigate
                    class="flex flex-col items-center gap-1 py-1.5 px-3 rounded-2xl transition-all duration-200 {{ $item['active'] ? 'text-[#5865F2] font-semibold scale-105' : 'text-[#9CA3AF] hover:text-white' }}"
                >
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="{{ $item['active'] ? '2.3' : '1.8' }}" stroke="currentColor">
                        {!! $item['icon'] !!}
                    </svg>
                    <span class="text-[11px] tracking-tight">{{ $item['name'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <!-- GLOBAL CREATE TRANSACTION MODAL -->
    <livewire:transactions.create-modal />

    @livewireScripts
</body>
</html>
