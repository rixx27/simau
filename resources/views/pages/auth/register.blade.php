<x-layouts::auth title="Daftar Akun SIMAU">
    <div class="flex flex-col gap-6">
        <div class="space-y-1.5">
            <h1 class="text-2xl font-semibold leading-none tracking-tight text-zinc-50">
                Buat Akun SIMAU
            </h1>
            <p class="text-sm text-zinc-400">
                Mulai kelola keuangan harian dan anggaran liburan Anda secara privat.
            </p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="grid gap-4">
            @csrf

            <!-- Name -->
            <div class="grid gap-2">
                <label for="name" class="text-sm font-medium leading-none text-zinc-300">
                    Nama Lengkap
                </label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Nama lengkap Anda"
                        class="flex h-10 w-full rounded-lg border border-zinc-800 bg-zinc-950 pl-10 pr-3 py-2 text-sm text-zinc-50 placeholder:text-zinc-400 focus-visible:outline-none focus-visible:border-zinc-500 focus-visible:ring-2 focus-visible:ring-zinc-500/25"
                    />
                </div>
                @error('name')
                    <p class="text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Address -->
            <div class="grid gap-2">
                <label for="email" class="text-sm font-medium leading-none text-zinc-300">
                    Email
                </label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        placeholder="nama@email.com"
                        class="flex h-10 w-full rounded-lg border border-zinc-800 bg-zinc-950 pl-10 pr-3 py-2 text-sm text-zinc-50 placeholder:text-zinc-400 focus-visible:outline-none focus-visible:border-zinc-500 focus-visible:ring-2 focus-visible:ring-zinc-500/25"
                    />
                </div>
                @error('email')
                    <p class="text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="grid gap-2" x-data="{ showPassword: false }">
                <label for="password" class="text-sm font-medium leading-none text-zinc-300">
                    Kata Sandi
                </label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                    <input
                        id="password"
                        name="password"
                        :type="showPassword ? 'text' : 'password'"
                        required
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                        passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                        class="flex h-10 w-full rounded-lg border border-zinc-800 bg-zinc-950 pl-10 pr-10 py-2 text-sm text-zinc-50 placeholder:text-zinc-400 focus-visible:outline-none focus-visible:border-zinc-500 focus-visible:ring-2 focus-visible:ring-zinc-500/25"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Lihat kata sandi'"
                        class="absolute right-1.5 top-1/2 -translate-y-1/2 p-2 rounded-md text-zinc-400 hover:text-zinc-100 transition-colors cursor-pointer"
                    >
                        <svg x-show="!showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg x-show="showPassword" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="grid gap-2" x-data="{ showConfirm: false }">
                <label for="password_confirmation" class="text-sm font-medium leading-none text-zinc-300">
                    Konfirmasi Kata Sandi
                </label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        :type="showConfirm ? 'text' : 'password'"
                        required
                        autocomplete="new-password"
                        placeholder="Ulangi kata sandi"
                        passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                        class="flex h-10 w-full rounded-lg border border-zinc-800 bg-zinc-950 pl-10 pr-10 py-2 text-sm text-zinc-50 placeholder:text-zinc-400 focus-visible:outline-none focus-visible:border-zinc-500 focus-visible:ring-2 focus-visible:ring-zinc-500/25"
                    />
                    <button
                        type="button"
                        @click="showConfirm = !showConfirm"
                        :aria-label="showConfirm ? 'Sembunyikan konfirmasi kata sandi' : 'Lihat konfirmasi kata sandi'"
                        class="absolute right-1.5 top-1/2 -translate-y-1/2 p-2 rounded-md text-zinc-400 hover:text-zinc-100 transition-colors cursor-pointer"
                    >
                        <svg x-show="!showConfirm" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg x-show="showConfirm" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                @error('password_confirmation')
                    <p class="text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Info Dompet Awal -->
            <div class="rounded-lg border border-zinc-800 bg-zinc-950/80 p-3 text-xs text-zinc-300 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-[#00E676] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Akun Anda otomatis dilengkapi <strong class="text-zinc-100">Kas Utama</strong> dan <strong class="text-zinc-100">Pundi Impian</strong> agar siap mencatat transaksi.</span>
            </div>

            <button
                type="submit"
                data-test="register-user-button"
                class="mt-1 inline-flex items-center justify-center w-full h-10 rounded-lg bg-zinc-50 text-zinc-950 text-sm font-semibold hover:bg-zinc-200 transition-colors cursor-pointer"
            >
                Buat Akun Sekarang
            </button>
        </form>

        <div class="pt-1 flex flex-col items-center gap-3 text-sm text-zinc-400">
            <div>
                Sudah memiliki akun?
                <a href="{{ route('login') }}" wire:navigate class="ml-1 font-medium text-zinc-100 hover:underline">
                    Masuk di sini
                </a>
            </div>
            <span class="text-[11px] tracking-wide text-zinc-500">
                SIMAU &bull; Sistem Manajemen Uangku
            </span>
        </div>
    </div>
</x-layouts::auth>
