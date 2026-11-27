<?php

namespace App\Livewire\Profile;

use App\Concerns\PasswordValidationRules;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    use PasswordValidationRules, WithFileUploads;

    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $phone = '';
    public bool $biometric_enabled = true;

    // Avatar upload
    public mixed $avatar = null;

    // Password change
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    // Password eye toggles
    public bool $showCurrentPassword = false;
    public bool $showNewPassword = false;
    public bool $showConfirmPassword = false;

    // Success notification
    public ?string $successMessage = null;

    public function mount(): void
    {
        $user = Auth::user();
        if ($user) {
            $this->name = $user->name ?? '';
            $this->username = $user->username ?? '';
            $this->email = $user->email ?? '';
            $this->phone = $user->phone ?? '';
            $this->biometric_enabled = (bool)($user->biometric_enabled ?? true);
        }
    }

    public function toggleBiometric(): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $this->biometric_enabled = !$this->biometric_enabled;
        $user->update(['biometric_enabled' => $this->biometric_enabled]);
    }

    public function save(): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $emailChanged = strtolower(trim($this->email)) !== strtolower($user->email);
        $passwordChanged = !empty($this->password) || !empty($this->password_confirmation);

        $rules = [
            'name' => 'required|string|max:100',
            'username' => 'nullable|string|max:50|unique:users,username,' . $user->id,
            'email' => 'required|email|max:100|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:25',
        ];

        if ($emailChanged || $passwordChanged) {
            $rules['current_password'] = $this->currentPasswordRules();
        }

        if ($passwordChanged) {
            $rules['password'] = $this->passwordRules();
        }

        if ($this->avatar) {
            $rules['avatar'] = 'image|max:5120'; // 5MB
        }

        $this->validate($rules, [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email akun wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'current_password.required' => 'Masukkan kata sandi saat ini untuk mengubah email atau kata sandi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak cocok.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak sama.',
            'password.min' => 'Kata sandi baru belum memenuhi panjang minimal.',
            'avatar.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $updateData = [
            'name' => $this->name,
            'username' => ltrim($this->username, '@'),
            'email' => strtolower(trim($this->email)),
            'phone' => $this->phone,
            'biometric_enabled' => $this->biometric_enabled,
        ];

        if (!empty($this->password)) {
            $updateData['password'] = Hash::make($this->password);
        }

        if ($this->avatar) {
            $path = $this->avatar->store('avatars', 'public');
            $updateData['avatar_path'] = 'storage/' . $path;
        }

        $user->update($updateData);

        if ($emailChanged) {
            $user->email_verified_at = null;
            $user->save();
            $user->sendEmailVerificationNotification();
        }

        // Reset passwords
        $this->reset(['current_password', 'password', 'password_confirmation', 'avatar']);
        $this->successMessage = $emailChanged
            ? 'Profil diperbarui! Silakan verifikasi alamat email baru Anda.'
            : 'Profil dan keamanan berhasil diperbarui!';
    }

    public function selectAvatar(int $index): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        if ($index >= 1 && $index <= 6) {
            $user->update(['avatar_path' => "avatars/avatar-{$index}.svg"]);
            $this->avatar = null;
            $this->successMessage = 'Avatar profil berhasil dipilih!';
        }
    }

    public function cancel(): void
    {
        $this->mount();
        $this->reset(['current_password', 'password', 'password_confirmation', 'avatar', 'successMessage']);
    }

    public function render(): View
    {
        $user = Auth::user();

        return view('livewire.profile.edit', [
            'user' => $user,
        ]);
    }
}
