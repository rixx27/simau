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
                <span class="text-[#00E676] text-sm">✨</span>
                <span>Akun Anda otomatis dilengkapi <b>Kas Utama</b> dan <b>Pundi Impian</b> gratis agar langsung siap mencatat transaksi.</span>
            </div>

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full bg-[#5865F2] hover:bg-[#4752C4] text-black font-semibold py-2.5" data-test="register-user-button">
                    Buat Akun Sekarang &rarr;
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>Sudah memiliki akun?</span>
            <flux:link :href="route('login')" class="text-[#5865F2] font-semibold" wire:navigate>Masuk di sini</flux:link>
        </div>
    </div>
</x-layouts::auth>
