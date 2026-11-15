<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Default User
        $user = User::create([
            'name' => 'Krisna Pradana',
            'username' => 'krisnapradana',
            'email' => 'krisna@simau.app',
            'phone' => '+62 812-3456-7890',
            'password' => Hash::make('password'),
            'avatar_path' => null,
            'biometric_enabled' => true,
            'email_verified_at' => now(),
        ]);

        // 2. Wallets
        $operationalWallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'Rekening Utama',
            'account_label' => 'Mandiri Prioritas',
            'type' => 'operational',
            'current_balance' => 4302000,
            'target_amount' => null,
            'daily_limit' => 350000,
            'status' => 'active',
        ]);

        $tripWallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'Dompet Liburan Jogja',
            'account_label' => 'BCA Prioritas',
            'type' => 'trip',
            'current_balance' => 4200000,
            'target_amount' => 5000000,
            'daily_limit' => null,
            'status' => 'active',
        ]);

        // 3. Categories
        $catMakanan = Category::create([
            'user_id' => null,
            'name' => 'Makanan',
            'icon' => 'utensils',
            'color' => '#10B981',
            'type' => 'expense',
        ]);

        $catGrabCar = Category::create([
            'user_id' => null,
            'name' => 'GrabCar',
            'icon' => 'car',
            'color' => '#3B82F6',
            'type' => 'expense',
        ]);

        $catPenginapan = Category::create([
            'user_id' => null,
            'name' => 'Penginapan',
            'icon' => 'bed',
            'color' => '#8B5CF6',
            'type' => 'expense',
        ]);

        $catTiket = Category::create([
            'user_id' => null,
            'name' => 'Tiket Wisata',
            'icon' => 'ticket',
            'color' => '#EC4899',
            'type' => 'expense',
        ]);

        $catKopi = Category::create([
            'user_id' => null,
            'name' => 'Kopi & Cafe',
            'icon' => 'coffee',
            'color' => '#F59E0B',
            'type' => 'expense',
        ]);

        // 4. Sample Transactions
        // Reference date: current date (e.g. 13 Nov 2026 or today)
        $now = Carbon::parse('2026-11-13 19:42:00');

        // Today's transactions (13 Nov 2026) -> Total spent today = 147.000 (Remaining = 203.000 / 350.000 = 58%)
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'category_id' => $catGrabCar->id,
            'type' => 'expense',
            'amount' => 45000,
            'title' => 'GrabCar ke Stasiun',
            'note' => 'Transportasi ke stasiun Gambir',
            'transaction_date' => $now->copy()->setTime(9, 30),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'category_id' => $catMakanan->id,
            'type' => 'expense',
            'amount' => 32000,
            'title' => 'Makan Siang',
            'note' => 'Nasi Padang Garuda',
            'transaction_date' => $now->copy()->setTime(12, 45),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'category_id' => $catMakanan->id,
            'type' => 'expense',
            'amount' => 70000,
            'title' => 'Sarapan Bubur & Dimsum',
            'note' => 'Sarapan pagi santai',
            'transaction_date' => $now->copy()->setTime(7, 30),
        ]);

        // Yesterday's transactions (12 Nov 2026)
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'category_id' => $catPenginapan->id,
            'type' => 'expense',
            'amount' => 480000,
            'title' => 'Hotel Malioboro',
            'note' => 'Akomodasi DP penginapan',
            'transaction_date' => $now->copy()->subDay()->setTime(14, 0),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'category_id' => $catKopi->id,
            'type' => 'expense',
            'amount' => 24000,
            'title' => 'Kopi Kenangan',
            'note' => 'Kopi Kenangan Mantan Large',
            'transaction_date' => $now->copy()->subDay()->setTime(16, 30),
        ]);

        // Min 3 (15 - 21 Nov 2026) -> Matching Mockup 2:
        // GrabCar Sudirman - SCBD: Rp 72.000
        // Kopi Kenangan Senopati: Rp 38.000
        // Artotel Thamrin DP: Rp 342.000
        // Total Min 3 = 452.000!
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'category_id' => $catGrabCar->id,
            'type' => 'expense',
            'amount' => 72000,
            'title' => 'GrabCar Sudirman - SCBD',
            'note' => 'Meeting client Senopati',
            'transaction_date' => Carbon::parse('2026-11-19 18:24:00'),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'category_id' => $catKopi->id,
            'type' => 'expense',
            'amount' => 38000,
            'title' => 'Kopi Kenangan Senopati',
            'note' => 'Meeting snack',
            'transaction_date' => Carbon::parse('2026-11-18 14:10:00'),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'category_id' => $catPenginapan->id,
            'type' => 'expense',
            'amount' => 342000,
            'title' => 'Artotel Thamrin DP',
            'note' => 'Staycation akhir pekan',
            'transaction_date' => Carbon::parse('2026-11-16 11:05:00'),
        ]);

        // Min 1 (1 - 7 Nov 2026) -> Total 420.000
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'category_id' => $catMakanan->id,
            'type' => 'expense',
            'amount' => 250000,
            'title' => 'Belanja Mingguan Supermarket',
            'note' => 'Bahan makanan mingguan',
            'transaction_date' => Carbon::parse('2026-11-03 10:15:00'),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'category_id' => $catGrabCar->id,
            'type' => 'expense',
            'amount' => 170000,
            'title' => 'GrabCar Bandara Soetta',
            'note' => 'Perjalanan dinas',
            'transaction_date' => Carbon::parse('2026-11-05 06:45:00'),
        ]);

        // Min 4 (22 - 28 Nov 2026) -> Total 486.000
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'category_id' => $catMakanan->id,
            'type' => 'expense',
            'amount' => 188000,
            'title' => 'Dinner Akhir Pekan',
            'note' => 'Makan malam keluarga',
            'transaction_date' => Carbon::parse('2026-11-23 19:30:00'),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'category_id' => $catTiket->id,
            'type' => 'expense',
            'amount' => 262000,
            'title' => 'Tiket Bioskop & Snack IMAX',
            'note' => 'Weekend movie',
            'transaction_date' => Carbon::parse('2026-11-25 15:00:00'),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'category_id' => $catKopi->id,
            'type' => 'expense',
            'amount' => 36000,
            'title' => 'Cafe Work from Anywhere',
            'note' => 'Coffee & pastry',
            'transaction_date' => Carbon::parse('2026-11-27 13:20:00'),
        ]);

        // Seed an initial income/top-up and trip saving transfer
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'destination_wallet_id' => $tripWallet->id,
            'category_id' => null,
            'type' => 'transfer',
            'amount' => 1000000,
            'title' => 'Nabung Trip Jogja',
            'note' => 'Alokasi tabungan liburan bulanan',
            'transaction_date' => Carbon::parse('2026-11-01 10:00:00'),
        ]);

        // Seed some October sample transactions for period filter testing
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'category_id' => $catMakanan->id,
            'type' => 'expense',
            'amount' => 850000,
            'title' => 'Kuliner Bulanan Oktober',
            'note' => 'Total makan bulanan',
            'transaction_date' => Carbon::parse('2026-10-15 12:00:00'),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'category_id' => $catGrabCar->id,
            'type' => 'expense',
            'amount' => 450000,
            'title' => 'Transportasi Oktober',
            'note' => 'Grab & Gojek bulanan',
            'transaction_date' => Carbon::parse('2026-10-20 09:00:00'),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'category_id' => $catPenginapan->id,
            'type' => 'expense',
            'amount' => 600000,
            'title' => 'Hotel Bandung Weekend',
            'note' => 'Liburan singkat',
            'transaction_date' => Carbon::parse('2026-10-28 15:00:00'),
        ]);
    }
}
