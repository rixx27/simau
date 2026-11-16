<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserStarterKitService
{
    /**
     * Inisialisasi data starter kit untuk pengguna baru yang baru mendaftar:
     * - Username unik otomatis
     * - Dompet operasional harian utama (Kas Utama)
     * - Pundi tabungan awal (Pundi Impian)
     */
    public function setup(User $user): void
    {
        DB::transaction(function () use ($user) {
            // 1. Generate username unik jika belum ada
            if (empty($user->username)) {
                $baseSlug = Str::slug(Str::words($user->name, 1, '')) ?: 'user';
                $candidate = $baseSlug . rand(10, 99);
                while (User::where('username', $candidate)->where('id', '!=', $user->id)->exists()) {
                    $candidate = $baseSlug . rand(100, 999);
                }
                $user->username = $candidate;
                $user->save();
            }

            // 2. Buat Dompet Operasional Kas Utama (jika belum punya)
            $hasOpWallet = Wallet::where('user_id', $user->id)
                ->where('type', 'operational')
                ->exists();

            if (!$hasOpWallet) {
                Wallet::create([
                    'user_id' => $user->id,
                    'name' => 'Kas Utama',
                    'account_label' => 'Dompet Harian / Tunai',
                    'type' => 'operational',
                    'current_balance' => 0,
                    'daily_limit' => 350000,
                    'status' => 'active',
                ]);
            }

            // 3. Buat Pundi Tabungan Awal (Pundi Impian)
            $hasGoalWallet = Wallet::where('user_id', $user->id)
                ->whereIn('type', ['saving', 'trip'])
                ->exists();

            if (!$hasGoalWallet) {
                Wallet::create([
                    'user_id' => $user->id,
                    'name' => 'Pundi Impian',
                    'account_label' => 'Tabungan Masa Depan',
                    'type' => 'saving',
                    'current_balance' => 0,
                    'target_amount' => 5000000,
                    'status' => 'active',
                ]);
            }

            // 4. Set Avatar Vektor Default Otomatis (bukan foto orang asli)
            if (empty($user->avatar_path)) {
                $avatarIndex = (abs(crc32($user->email ?: ($user->name ?: 'user'))) % 6) + 1;
                $user->avatar_path = "avatars/avatar-{$avatarIndex}.svg";
                $user->save();
            }
        });
    }
}
