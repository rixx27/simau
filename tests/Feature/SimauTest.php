<?php

namespace Tests\Feature;

use App\Exports\SimauReportExport;
use App\Exports\SingleTripReportExport;
use App\Livewire\Analytics\History;
use App\Livewire\Dashboard\Index;
use App\Livewire\Profile\Edit as ProfileEdit;
use App\Livewire\Transactions\CreateModal;
use App\Livewire\Trips\Index as TripsIndex;
use App\Livewire\Trips\Report as TripsReport;
use App\Livewire\Wallets\Index as WalletsIndex;
use App\Models\Category;
use App\Models\Trip;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class SimauTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $user = User::first();
        if ($user) {
            $this->actingAs($user);
        }
    }

    public function test_dashboard_page_renders_successfully()
    {
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('TOTAL KAS & TABUNGAN');
        $response->assertSee('Batas Harian');
        $response->assertSee('Pundi Liburan');
        $response->assertSee('Transaksi Terbaru');
    }

    public function test_mutasi_page_renders_successfully()
    {
        $response = $this->get('/mutasi');
        $response->assertStatus(200);
        $response->assertSee('Statistik');
        $response->assertSee('Tren Siklus Mingguan');
        $response->assertSee('Kategori Pengeluaran');
        $response->assertSee('Aktivitas Terkini');
    }

    public function test_tabungan_page_renders_successfully()
    {
        $response = $this->get('/tabungan');
        $response->assertStatus(200);
        $response->assertSee('Tabungan');
        $response->assertSee('Dompet Liburan Jogja');
        $response->assertSee('Rekening Utama');
    }

    public function test_profile_page_renders_successfully()
    {
        $response = $this->get('/profile');
        $response->assertStatus(200);
        $response->assertSee('Edit Profil');
        $response->assertSee('Informasi Pribadi');
        $response->assertSee('Keamanan');
        $response->assertSee('Login Biometrik');
    }

    public function test_can_create_daily_expense_transaction()
    {
        $user = User::first();
        $wallet = Wallet::where('type', 'operational')->first();
        $category = Category::where('name', 'Makanan')->first();

        $initialBalance = $wallet->current_balance;
        $expenseAmount = 50000;

        Livewire::actingAs($user)
            ->test(CreateModal::class)
            ->call('openModal', 'daily')
            ->set('amount', $expenseAmount)
            ->set('selectedWalletId', $wallet->id)
            ->set('selectedCategoryId', $category->id)
            ->set('title', 'Makan Malam Sushi')
            ->set('transactionDate', '2026-11-13T20:00')
            ->call('save')
            ->assertDispatched('transaction-saved');

        $this->assertDatabaseHas('transactions', [
            'title' => 'Makan Malam Sushi',
            'amount' => $expenseAmount,
            'type' => 'expense',
            'trip_id' => null,
        ]);

        $wallet->refresh();
        $this->assertEquals($initialBalance - $expenseAmount, $wallet->current_balance);
    }

    public function test_can_create_trip_expense_transaction()
    {
        $user = User::first();
        $wallet = Wallet::where('type', 'operational')->first();
        $trip = Trip::where('status', 'active')->first();
        $category = Category::where('name', 'Makanan')->first();

        $initialBalance = $wallet->current_balance;
        $tripInitialSpent = $trip->total_spent;
        $expenseAmount = 120000;

        Livewire::actingAs($user)
            ->test(CreateModal::class)
            ->call('openModal', 'trip')
            ->set('amount', $expenseAmount)
            ->set('selectedWalletId', $wallet->id)
            ->set('selectedTripId', $trip->id)
            ->set('selectedCategoryId', $category->id)
            ->set('title', 'Kuliner Malam Malioboro')
            ->set('transactionDate', '2026-11-13T21:00')
            ->call('save')
            ->assertDispatched('transaction-saved');

        $this->assertDatabaseHas('transactions', [
            'title' => 'Kuliner Malam Malioboro',
            'amount' => $expenseAmount,
            'type' => 'expense',
            'trip_id' => $trip->id,
        ]);

        $wallet->refresh();
        $this->assertEquals($initialBalance - $expenseAmount, $wallet->current_balance);
        $this->assertEquals($tripInitialSpent + $expenseAmount, $trip->total_spent);
    }

    public function test_can_deposit_to_savings_goal_from_operational_cash()
    {
        $user = User::first();
        $opWallet = Wallet::where('user_id', $user->id)->where('type', 'operational')->first();
        $goalWallet = Wallet::where('user_id', $user->id)->where('type', 'trip')->first();

        $initialOp = $opWallet->current_balance;
        $initialGoal = $goalWallet->current_balance;
        $deposit = 250000;

        Livewire::actingAs($user)
            ->test(WalletsIndex::class)
            ->call('openDepositModal', $goalWallet->id)
            ->set('depositAmount', $deposit)
            ->set('sourceMode', 'operational')
            ->set('sourceWalletId', $opWallet->id)
            ->set('depositNote', 'Sisihkan gaji untuk liburan')
            ->call('saveDeposit')
            ->assertDispatched('transaction-saved');

        $opWallet->refresh();
        $goalWallet->refresh();

        $this->assertEquals($initialOp - $deposit, $opWallet->current_balance);
        $this->assertEquals($initialGoal + $deposit, $goalWallet->current_balance);

        $this->assertDatabaseHas('transactions', [
            'type' => 'transfer',
            'wallet_id' => $opWallet->id,
            'destination_wallet_id' => $goalWallet->id,
            'amount' => $deposit,
        ]);
    }

    public function test_can_deposit_to_savings_goal_from_external_income()
    {
        $user = User::first();
        $goalWallet = Wallet::where('user_id', $user->id)->where('type', 'trip')->first();

        $initialGoal = $goalWallet->current_balance;
        $deposit = 500000;

        Livewire::actingAs($user)
            ->test(WalletsIndex::class)
            ->call('openDepositModal', $goalWallet->id)
            ->set('depositAmount', $deposit)
            ->set('sourceMode', 'external')
            ->set('depositNote', 'Bonus lembur')
            ->call('saveDeposit')
            ->assertDispatched('transaction-saved');

        $goalWallet->refresh();

        $this->assertEquals($initialGoal + $deposit, $goalWallet->current_balance);

        $this->assertDatabaseHas('transactions', [
            'type' => 'income',
            'wallet_id' => $goalWallet->id,
            'amount' => $deposit,
        ]);
    }

    public function test_trips_index_page_renders_successfully()
    {
        $response = $this->get('/trips');
        $response->assertStatus(200);
        $response->assertSee('Perjalanan Liburan');
        $response->assertSee('Liburan Eksplor Jogja');
        $response->assertSee('Staycation');
    }

    public function test_can_create_new_trip()
    {
        $user = User::first();

        Livewire::actingAs($user)
            ->test(TripsIndex::class)
            ->call('openCreateModal')
            ->set('name', 'Liburan Akhir Tahun Bali')
            ->set('destination', 'Denpasar, Bali')
            ->set('startDate', '2026-12-25')
            ->set('endDate', '2026-12-30')
            ->set('budget', 6500000)
            ->set('status', 'planned')
            ->set('notes', 'Snorkeling di Nusa Penida')
            ->call('saveTrip')
            ->assertDispatched('transaction-saved');

        $this->assertDatabaseHas('trips', [
            'user_id' => $user->id,
            'name' => 'Liburan Akhir Tahun Bali',
            'destination' => 'Denpasar, Bali',
            'budget' => 6500000,
            'status' => 'planned',
        ]);
    }

    public function test_can_complete_active_trip()
    {
        $user = User::first();
        $activeTrip = Trip::where('user_id', $user->id)->where('status', 'active')->first();

        Livewire::actingAs($user)
            ->test(TripsIndex::class)
            ->call('completeTrip', $activeTrip->id)
            ->assertDispatched('transaction-saved');

        $activeTrip->refresh();
        $this->assertEquals('completed', $activeTrip->status);
    }

    public function test_trip_report_page_renders_successfully()
    {
        $completedTrip = Trip::where('status', 'completed')->first();

        $response = $this->get('/trips/' . $completedTrip->id . '/rekap');
        $response->assertStatus(200);
        $response->assertSee('Rekap Pasca-Trip');
        $response->assertSee($completedTrip->name);
        $response->assertSee('Plafon Anggaran');
        $response->assertSee('Realisasi Terpakai');
        $response->assertSee('Selisih Anggaran');
        $response->assertSee('Distribusi Kategori Liburan');
        $response->assertDontSee('--Rp');
        $response->assertDontSee('🍔');
        $response->assertDontSee('🚗');
        $response->assertDontSee('🏨');
        $response->assertDontSee('🎟️');
        $response->assertDontSee('☕');
        $response->assertDontSee('💳');
    }

    public function test_single_trip_excel_export_download()
    {
        Excel::fake();
        $completedTrip = Trip::where('status', 'completed')->first();

        $response = $this->get('/trips/' . $completedTrip->id . '/export');
        $response->assertStatus(200);

        Excel::assertDownloaded('SIMAU_Rekap_' . str_replace(' ', '_', $completedTrip->name) . '_' . now()->format('Ymd_His') . '.xlsx', function (SingleTripReportExport $export) {
            return true;
        });
    }

    public function test_excel_export_download()
    {
        Excel::fake();

        $response = $this->get('/export/excel');
        $response->assertStatus(200);

        Excel::assertDownloaded('SIMAU_Laporan_Keuangan_' . now()->format('Ymd_His') . '.xlsx', function (SimauReportExport $export) {
            return true;
        });
    }

    public function test_unauthenticated_user_is_redirected_to_login()
    {
        auth()->logout();

        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_new_user_registration_automatically_creates_starter_kit()
    {
        auth()->logout();

        $userData = [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'Budi123!SafePass',
            'password_confirmation' => 'Budi123!SafePass',
        ];

        $response = $this->post('/register', $userData);
        $response->assertRedirect('/dashboard');

        $budi = User::where('email', 'budi@example.com')->first();
        $this->assertNotNull($budi);
        $this->assertNotEmpty($budi->username);

        // Verify Operational Wallet
        $opWallet = Wallet::where('user_id', $budi->id)->where('type', 'operational')->first();
        $this->assertNotNull($opWallet);
        $this->assertEquals('Kas Utama', $opWallet->name);
        $this->assertEquals(0, $opWallet->current_balance);

        // Verify Savings / Dream Goal Wallet
        $goalWallet = Wallet::where('user_id', $budi->id)->where('type', 'saving')->first();
        $this->assertNotNull($goalWallet);
        $this->assertEquals('Pundi Impian', $goalWallet->name);

        // Verify Automatic Vector Avatar (not an unsplash human photo)
        $this->assertNotNull($budi->avatar_path);
        $this->assertStringContainsString('avatars/avatar-', $budi->avatar_path);
        $this->assertStringNotContainsString('unsplash.com', $budi->avatar_url);
    }

    public function test_multi_user_data_isolation()
    {
        $budi = User::create([
            'name' => 'Budi',
            'email' => 'budi2@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($budi);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Halo, Budi!');

        $tabunganResponse = $this->get('/tabungan');
        $tabunganResponse->assertStatus(200);
        $tabunganResponse->assertSee('Kas Utama');
        $tabunganResponse->assertSee('Pundi Impian');

        $this->assertEquals(0, $budi->transactions()->count());
    }

    public function test_demo_login_is_disabled_and_login_page_requires_credentials()
    {
        auth()->logout();

        // Ensure backdoor route /demo-login does not exist
        $response = $this->get('/demo-login');
        $response->assertStatus(404);

        // Ensure login page does not expose quick demo button
        $loginPage = $this->get('/login');
        $loginPage->assertStatus(200);
        $loginPage->assertDontSee('Masuk Cepat Demo');
        $loginPage->assertDontSee('Akun Krisna');

        // Standard authentication works with credentials
        $loginPost = $this->post('/login', [
            'email' => 'krisna@simau.app',
            'password' => 'password',
        ]);
        $loginPost->assertRedirect('/dashboard');
        $this->assertTrue(auth()->check());
        $this->assertEquals('krisna@simau.app', auth()->user()->email);
    }

    public function test_pwa_manifest_and_meta_tags_available()
    {
        $user = User::first();
        $this->actingAs($user);

        $dash = $this->get('/dashboard');
        $dash->assertStatus(200);
        $dash->assertSee('/manifest.json');
        $dash->assertSee('apple-mobile-web-app-capable');

        $this->assertFileExists(public_path('manifest.json'));
        $manifest = json_decode(file_get_contents(public_path('manifest.json')), true);
        $this->assertEquals('SIMAU', $manifest['short_name']);
        $this->assertEquals('standalone', $manifest['display']);

        $this->assertFileExists(public_path('sw.js'));
    }

    public function test_user_can_select_preset_vector_avatar()
    {
        $user = User::first();

        Livewire::actingAs($user)
            ->test(ProfileEdit::class)
            ->call('selectAvatar', 4)
            ->assertSee('Avatar profil berhasil dipilih!');

        $user->refresh();
        $this->assertEquals('avatars/avatar-4.svg', $user->avatar_path);
        $this->assertStringContainsString('avatars/avatar-4.svg', $user->avatar_url);
    }
}
