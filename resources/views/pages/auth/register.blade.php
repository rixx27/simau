<x-layouts::auth title="Daftar Akun SIMAU">
    <div class="flex flex-col gap-6">
        <x-auth-header title="Daftar Akun Baru" description="Mulai kelola keuangan harian & anggaran liburan Anda secara privat" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                label="Nama Lengkap"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="Nama Anda"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                label="Alamat Email"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                label="Kata Sandi"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Minimal 8 karakter"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                label="Konfirmasi Kata Sandi"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Ulangi kata sandi"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Info Starter Kit -->
            <div class="bg-[#17181E] border border-[#262C37] rounded-2xl p-3 text-[11px] text-[#9CA3AF] flex items-start gap-2">
                <svg class="w-4 h-4 text-[#00E676] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                </svg>
                <span>Akun Anda otomatis dilengkapi <b>Kas Utama</b> dan <b>Pundi Impian</b> gratis agar langsung siap mencatat transaksi.</span>
            </div>

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full bg-[#5865F2] hover:bg-[#4752C4] text-white font-semibold py-2.5 cursor-pointer" data-test="register-user-button">
                    Buat Akun Sekarang
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>Sudah memiliki akun?</span>
            <flux:link :href="route('login')" class="text-[#818CF8] font-semibold" wire:navigate>Masuk di sini</flux:link>
        </div>
    </div>
</x-layouts::auth>
