<div class="max-w-2xl mx-auto space-y-6">
    <!-- Top Header -->
    <div class="flex items-center justify-between pb-2 border-b border-[#262C37]/60">
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
                <h1 class="text-xl font-bold text-white tracking-tight">Edit Profil</h1>
                <p class="text-[11px] font-semibold text-[#5865F2] tracking-wider uppercase">SIMAU SECURITY</p>
            </div>
        </div>

        <button
            type="button"
            wire:click="save"
            class="text-xs font-bold text-white px-4 py-2 rounded-xl bg-[#17181E] hover:bg-[#5865F2] border border-[#262C37] hover:border-[#5865F2] transition-all cursor-pointer"
        >
            Simpan
        </button>
    </div>

    <!-- Success Message Notification -->
    @if($successMessage)
        <div class="p-4 rounded-2xl bg-[#00E676]/10 border border-[#00E676]/40 text-[#00E676] text-xs font-semibold flex items-center gap-2 animate-in fade-in">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ $successMessage }}</span>
        </div>
    @endif

    <!-- Profile Photo Section -->
    <div class="flex flex-col items-center justify-center pt-2 pb-4 text-center">
        <div class="relative group cursor-pointer" onclick="document.getElementById('avatar-upload').click()">
            <div class="w-28 h-28 rounded-full overflow-hidden ring-4 ring-[#00E676]/30 shadow-[0_0_24px_rgba(0,230,118,0.25)] bg-[#17181E]">
                @if($avatar)
                    <img src="{{ $avatar->temporaryUrl() }}" alt="Avatar Preview" class="w-full h-full object-cover" />
                @else
                    <img src="{{ $user?->avatar_url ?? asset('avatars/avatar-1.svg') }}" alt="{{ $user?->name }}" class="w-full h-full object-cover" />
                @endif
            </div>

            <!-- Camera button overlay badge -->
            <button
                type="button"
                class="absolute bottom-0 right-0 w-9 h-9 rounded-full bg-[#5865F2] hover:bg-[#4752C4] border-2 border-[#0D0E12] flex items-center justify-center text-white shadow-lg transition-transform group-hover:scale-110 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                </svg>
            </button>
        </div>

        <input
            id="avatar-upload"
            type="file"
            wire:model="avatar"
            accept="image/*"
            class="hidden"
        />

        <div class="mt-4">
            <h3 class="text-sm font-bold text-white">Avatar &amp; Foto Profil</h3>
            <p class="text-xs text-[#9CA3AF] mb-3">Pilih avatar favorit atau ketuk kamera untuk unggah foto sendiri</p>
            @error('avatar') <p class="text-xs text-red-400 mb-2">{{ $message }}</p> @enderror

            <!-- Preset Avatar Selector -->
            <div class="flex items-center justify-center gap-2.5 flex-wrap">
                @for($i = 1; $i <= 6; $i++)
                    <button
                        type="button"
                        wire:click="selectAvatar({{ $i }})"
                        class="w-10 h-10 rounded-full overflow-hidden border-2 transition-all hover:scale-110 cursor-pointer {{ ($user?->avatar_path === 'avatars/avatar-' . $i . '.svg') ? 'border-[#00E676] ring-2 ring-[#00E676]/40 scale-105' : 'border-[#262C37] hover:border-[#5865F2]' }}"
                        title="Pilih Avatar {{ $i }}"
                    >
                        <img src="{{ asset('avatars/avatar-' . $i . '.svg') }}" alt="Avatar {{ $i }}" class="w-full h-full object-cover" />
                    </button>
                @endfor
            </div>
        </div>
    </div>

    <!-- CARD 1: INFORMASI PRIBADI -->
    <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-5 md:p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-[#262C37]/60 pb-3">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-[#5865F2]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                <h2 class="text-sm font-bold text-white">Informasi Pribadi</h2>
            </div>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#00E676]">
                <span class="w-2 h-2 rounded-full bg-[#00E676]"></span>
                Terverifikasi
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Nama Lengkap -->
            <div>
                <label class="block text-[11px] font-semibold text-[#9CA3AF] mb-1.5">Nama Lengkap</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9CA3AF]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model="name"
                        class="w-full bg-[#0D0E12] border border-[#262C37] rounded-2xl pl-10 pr-3.5 py-3 text-xs text-white placeholder-[#9CA3AF] focus:outline-none focus:border-[#5865F2]"
                        placeholder="Krisna Pradana"
                    />
                </div>
                @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Username -->
            <div>
                <label class="block text-[11px] font-semibold text-[#9CA3AF] mb-1.5">Username</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9CA3AF]">
                        <span class="text-sm font-bold">@</span>
                    </div>
                    <input
                        type="text"
                        wire:model="username"
                        class="w-full bg-[#0D0E12] border border-[#262C37] rounded-2xl pl-10 pr-3.5 py-3 text-xs text-white placeholder-[#9CA3AF] focus:outline-none focus:border-[#5865F2]"
                        placeholder="krisnapradana"
                    />
                </div>
                @error('username') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Email Akun -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-[11px] font-semibold text-[#9CA3AF]">Email Akun</label>
                    <span class="text-[10px] font-bold text-[#00E676] flex items-center gap-1">
                        Email Utama
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </span>
                </div>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9CA3AF]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <input
                        type="email"
                        wire:model="email"
                        class="w-full bg-[#0D0E12] border border-[#262C37] rounded-2xl pl-10 pr-10 py-3 text-xs text-white placeholder-[#9CA3AF] focus:outline-none focus:border-[#5865F2]"
                        placeholder="krisna@simau.app"
                    />
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-[#00E676]">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
                @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Nomor Handphone -->
            <div>
                <label class="block text-[11px] font-semibold text-[#9CA3AF] mb-1.5">Nomor Handphone</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9CA3AF]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model="phone"
                        class="w-full bg-[#0D0E12] border border-[#262C37] rounded-2xl pl-10 pr-3.5 py-3 text-xs text-white placeholder-[#9CA3AF] focus:outline-none focus:border-[#5865F2]"
                        placeholder="+62 812-3456-7890"
                    />
                </div>
                @error('phone') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <!-- CARD 2: KEAMANAN & KATA SANDI -->
    <div class="bg-[#17181E] border border-[#262C37] rounded-3xl p-5 md:p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-[#262C37]/60 pb-3">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-[#5865F2]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <h2 class="text-sm font-bold text-white">Keamanan & Kata Sandi</h2>
            </div>
            <a href="#" class="text-xs font-semibold text-[#5865F2] hover:text-[#4752C4]">
                Lupa PIN?
            </a>
        </div>

        <div class="space-y-3.5">
            <!-- Kata Sandi Saat Ini -->
            <div>
                <label class="block text-[11px] font-semibold text-[#9CA3AF] mb-1.5">Kata Sandi Saat Ini</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9CA3AF]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                        </svg>
                    </div>
                    <input
                        type="{{ $showCurrentPassword ? 'text' : 'password' }}"
                        wire:model="current_password"
                        placeholder="secretpassword123"
                        class="w-full bg-[#0D0E12] border border-[#262C37] rounded-2xl pl-10 pr-10 py-3 text-xs text-white placeholder-[#6B7280] focus:outline-none focus:border-[#5865F2]"
                    />
                    <button
                        type="button"
                        wire:click="$toggle('showCurrentPassword')"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#9CA3AF] hover:text-white"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>
                @error('current_password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Kata Sandi Baru -->
            <div>
                <label class="block text-[11px] font-semibold text-[#9CA3AF] mb-1.5">Kata Sandi Baru</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9CA3AF]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                    </div>
                    <input
                        type="{{ $showNewPassword ? 'text' : 'password' }}"
                        wire:model="password"
                        placeholder="SimauNeo2026!"
                        class="w-full bg-[#0D0E12] border border-[#262C37] rounded-2xl pl-10 pr-10 py-3 text-xs text-white placeholder-[#6B7280] focus:outline-none focus:border-[#5865F2]"
                    />
                    <button
                        type="button"
                        wire:click="$toggle('showNewPassword')"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#9CA3AF] hover:text-white"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>
                @error('password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror

                <!-- Strength Indicator (Mockup 3) -->
                <div class="mt-2 space-y-1">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-[#9CA3AF]">Kekuatan Kata Sandi</span>
                        <span class="text-[#00E676] font-semibold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                            Kuat (Optimal)
                        </span>
                    </div>
                    <div class="grid grid-cols-4 gap-1.5">
                        <div class="h-1 bg-[#00E676] rounded-full"></div>
                        <div class="h-1 bg-[#00E676] rounded-full"></div>
                        <div class="h-1 bg-[#00E676] rounded-full"></div>
                        <div class="h-1 bg-[#00E676] rounded-full"></div>
                    </div>
                </div>
            </div>

            <!-- Konfirmasi Kata Sandi Baru -->
            <div>
                <label class="block text-[11px] font-semibold text-[#9CA3AF] mb-1.5">Konfirmasi Kata Sandi Baru</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9CA3AF]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                    </div>
                    <input
                        type="{{ $showConfirmPassword ? 'text' : 'password' }}"
                        wire:model="password_confirmation"
                        placeholder="SimauNeo2026!"
                        class="w-full bg-[#0D0E12] border border-[#262C37] rounded-2xl pl-10 pr-10 py-3 text-xs text-white placeholder-[#6B7280] focus:outline-none focus:border-[#5865F2]"
                    />
                    <button
                        type="button"
                        wire:click="$toggle('showConfirmPassword')"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#9CA3AF] hover:text-white"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Login Biometrik Switch -->
            <div class="p-3.5 bg-[#0D0E12] border border-[#262C37] rounded-2xl flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#17181E] border border-[#262C37] flex items-center justify-center text-[#9CA3AF]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.864 4.243A7.5 7.5 0 0 1 19.5 10.5c0 2.92-.556 5.709-1.568 8.268M5.742 6.364A7.465 7.465 0 0 0 4.5 10.5a7.464 7.464 0 0 1-1.15 3.993m1.989 3.559A11.209 11.209 0 0 0 8.25 10.5a3.75 3.75 0 1 1 7.5 0c0 .527-.021 1.049-.064 1.565M12 10.5a14.94 14.94 0 0 1-3.6 9.75m6.6-3.3a17.9 17.9 0 0 1-3 3.55" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-white">Login Biometrik</h4>
                        <p class="text-[11px] text-[#9CA3AF]">Face ID & Fingerprint aktif</p>
                    </div>
                </div>

                <!-- Toggle Switch -->
                <button
                    type="button"
                    wire:click="toggleBiometric"
                    class="w-12 h-6 flex items-center rounded-full p-0.5 transition-colors cursor-pointer {{ $biometric_enabled ? 'bg-[#5865F2]' : 'bg-[#262C37]' }}"
                >
                    <div class="bg-white w-5 h-5 rounded-full shadow-md transform transition-transform duration-200 {{ $biometric_enabled ? 'translate-x-6' : 'translate-x-0' }}"></div>
                </button>
            </div>

            <!-- Kelola Sesi Masuk -->
            <div class="p-3.5 bg-[#0D0E12] border border-[#262C37] rounded-2xl flex items-center justify-between cursor-pointer hover:border-[#5865F2]/50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#17181E] border border-[#262C37] flex items-center justify-center text-[#9CA3AF]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-white">Kelola Sesi Masuk</h4>
                        <p class="text-[11px] text-[#9CA3AF]">iPhone 15 Pro &bull; Jakarta, ID (Aktif)</p>
                    </div>
                </div>
                <svg class="w-4 h-4 text-[#9CA3AF]" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </div>
        </div>
    </div>

    <!-- BOTTOM ACTION BUTTONS (Batal & Simpan Perubahan) -->
    <div class="flex items-center gap-3 pt-2">
        <button
            type="button"
            wire:click="cancel"
            class="flex-1 py-3.5 px-4 rounded-2xl bg-[#17181E] hover:bg-[#262C37] border border-[#262C37] text-xs font-bold text-[#9CA3AF] hover:text-white transition-all text-center cursor-pointer"
        >
            Batal
        </button>

        <button
            type="button"
            wire:click="save"
            class="flex-1 py-3.5 px-4 rounded-2xl bg-gradient-to-r from-[#5865F2] to-[#4F46E5] hover:from-[#4F46E5] hover:to-[#4338CA] text-xs font-bold text-white shadow-xl shadow-[#5865F2]/30 hover:shadow-[#5865F2]/50 transition-all flex items-center justify-center gap-2 cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25 7.5 16.5V3.75m9 0H7.5m9 0a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21H7.5a2.25 2.25 0 0 1-2.25-2.25V6A2.25 2.25 0 0 1 7.5 3.75h9Z" />
            </svg>
            <span>Simpan Perubahan</span>
        </button>
    </div>

    <!-- LOGOUT BUTTON -->
    <div class="pt-4 border-t border-[#262C37]/60">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="w-full py-3 px-4 rounded-2xl bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-xs font-bold text-red-400 hover:text-red-300 transition-all flex items-center justify-center gap-2 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>
                <span>Keluar dari Akun (Logout)</span>
            </button>
        </form>
    </div>
</div>
