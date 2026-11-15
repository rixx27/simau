<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Trip;
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
            'avatar_path' => 'avatars/krisna.png',
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

        // 3. Trips (Travel Lifecycle)
        $activeTrip = Trip::create([
            'user_id' => $user->id,
            'name' => 'Liburan Eksplor Jogja',
            'destination' => 'Yogyakarta',
            'start_date' => '2026-11-12',
            'end_date' => '2026-11-16',
            'budget' => 5000000,
            'status' => 'active',
            'cover_image' => 'https://images.unsplash.com/photo-1584810359583-96fc3448beaa?auto=format&fit=crop&w=600&q=80',
            'notes' => 'Eksplor Malioboro, Candi Prambanan, dan Pantai Gunungkidul',
        ]);

        $completedTrip = Trip::create([
            'user_id' => $user->id,
            'name' => 'Staycation & Kuliner Bandung',
            'destination' => 'Bandung',
            'start_date' => '2026-10-26',
            'end_date' => '2026-10-29',
            'budget' => 2500000,
            'status' => 'completed',
            'cover_image' => 'https://images.unsplash.com/photo-1570789210967-2cac24afeb00?auto=format&fit=crop&w=600&q=80',
            'notes' => 'Wisata Lembang, belanja Factory Outlet, dan kopi Braga',
        ]);

        // 4. Categories
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

        // 5. Sample Transactions
        $now = Carbon::parse('2026-11-13 19:42:00');

        // Today's transactions (13 Nov 2026)
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'trip_id' => $activeTrip->id,
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
            'trip_id' => $activeTrip->id,
            'category_id' => $catMakanan->id,
            'type' => 'expense',
            'amount' => 32000,
            'title' => 'Makan Siang',
            'note' => 'Gudeg Yu Djum Malioboro',
            'transaction_date' => $now->copy()->setTime(12, 45),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'trip_id' => null, // Daily expense
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
            'trip_id' => $activeTrip->id,
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
            'trip_id' => $activeTrip->id,
            'category_id' => $catKopi->id,
            'type' => 'expense',
            'amount' => 24000,
            'title' => 'Kopi Kenangan',
            'note' => 'Kopi Kenangan Mantan Large',
            'transaction_date' => $now->copy()->subDay()->setTime(16, 30),
        ]);

        // Min 3 (15 - 21 Nov 2026)
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'trip_id' => null,
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
            'trip_id' => null,
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
            'trip_id' => null,
            'category_id' => $catPenginapan->id,
            'type' => 'expense',
            'amount' => 342000,
            'title' => 'Artotel Thamrin DP',
            'note' => 'Staycation akhir pekan',
            'transaction_date' => Carbon::parse('2026-11-16 11:05:00'),
        ]);

        // Min 1 (1 - 7 Nov 2026)
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'trip_id' => null,
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
            'trip_id' => null,
            'category_id' => $catGrabCar->id,
            'type' => 'expense',
            'amount' => 170000,
            'title' => 'GrabCar Bandara Soetta',
            'note' => 'Perjalanan dinas',
            'transaction_date' => Carbon::parse('2026-11-05 06:45:00'),
        ]);

        // Min 4 (22 - 28 Nov 2026)
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'trip_id' => null,
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
            'trip_id' => null,
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
            'trip_id' => null,
            'category_id' => $catKopi->id,
            'type' => 'expense',
            'amount' => 36000,
            'title' => 'Cafe Work from Anywhere',
            'note' => 'Coffee & pastry',
            'transaction_date' => Carbon::parse('2026-11-27 13:20:00'),
        ]);

        // October Sample Transactions (Linked to Bandung Trip)
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'trip_id' => $completedTrip->id,
            'category_id' => $catPenginapan->id,
            'type' => 'expense',
            'amount' => 1250000,
            'title' => 'Hotel Padma Bandung 2 Malam',
            'note' => 'Penginapan liburan Bandung',
            'transaction_date' => Carbon::parse('2026-10-26 14:00:00'),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'trip_id' => $completedTrip->id,
            'category_id' => $catMakanan->id,
            'type' => 'expense',
            'amount' => 580000,
            'title' => 'Kuliner Iga Bakar & Cafe Bandung',
            'note' => 'Makan malam dan cafe hop Lembang',
            'transaction_date' => Carbon::parse('2026-10-27 19:30:00'),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $operationalWallet->id,
            'trip_id' => $completedTrip->id,
            'category_id' => $catTiket->id,
            'type' => 'expense',
            'amount' => 350000,
            'title' => 'Tiket Wisata Tangkuban Perahu',
            'note' => 'Tiket masuk & parkir wisata',
            'transaction_date' => Carbon::parse('2026-10-28 11:00:00'),
        ]);
    }
}
