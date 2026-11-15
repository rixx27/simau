<?php

namespace App\Livewire\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Edit extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $phone = '';
    public bool $biometric_enabled = true;

    // Avatar upload
    public $avatar = null;

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
        $user = Auth::user() ?? User::first();
        if ($user) {
            $this->name = $user->name ?? '';
            $this->username = $user->username ?? '';
            $this->email = $user->email ?? '';
            $this->phone = $user->phone ?? '+62 812-3456-7890';
            $this->biometric_enabled = (bool)($user->biometric_enabled ?? true);
        }
    }

    public function toggleBiometric(): void
    {
        $this->biometric_enabled = !$this->biometric_enabled;
        $user = Auth::user() ?? User::first();
        if ($user) {
            $user->update(['biometric_enabled' => $this->biometric_enabled]);
        }
    }

    public function save(): void
    {
        $user = Auth::user() ?? User::first();
        if (!$user) {
            return;
        }

        $rules = [
            'name' => 'required|string|max:100',
            'username' => 'nullable|string|max:50|unique:users,username,' . $user->id,
            'email' => 'required|email|max:100|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:25',
        ];

        if (!empty($this->current_password) || !empty($this->password)) {
            $rules['current_password'] = 'required|current_password';
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        if ($this->avatar) {
            $rules['avatar'] = 'image|max:5120'; // 5MB
        }

        $this->validate($rules, [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email akun wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'current_password.current_password' => 'Kata sandi saat ini tidak cocok.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak sama.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'avatar.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $updateData = [
            'name' => $this->name,
            'username' => ltrim($this->username, '@'),
            'email' => $this->email,
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

        // Reset passwords
        $this->reset(['current_password', 'password', 'password_confirmation', 'avatar']);
        $this->successMessage = 'Profil dan keamanan berhasil diperbarui!';
    }

    public function cancel(): void
    {
        $this->mount();
        $this->reset(['current_password', 'password', 'password_confirmation', 'avatar', 'successMessage']);
    }

    public function render()
    {
        $user = Auth::user() ?? User::first();

        return view('livewire.profile.edit', [
            'user' => $user,
        ]);
    }
}
