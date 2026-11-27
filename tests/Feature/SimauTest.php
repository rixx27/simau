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
use App\Livewire\Wallets\DailyLimitModal;
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

    public function test_can_create_income_transaction_to_wallet()
    {
        $user = User::first();
        $wallet = Wallet::where('type', 'operational')->first();
        $category = Category::where('type', 'income')->first();

        $initialBalance = $wallet->current_balance;
        $incomeAmount = 500000;

        Livewire::actingAs($user)
            ->test(CreateModal::class)
            ->call('openModal', 'income')
            ->set('amount', $incomeAmount)
            ->set('selectedWalletId', $wallet->id)
            ->set('selectedCategoryId', $category->id)
            ->set('title', 'Gaji Bulanan & Bonus')
            ->set('transactionDate', '2026-11-13T10:00')
            ->call('save')
            ->assertDispatched('transaction-saved');

        $this->assertDatabaseHas('transactions', [
            'title' => 'Gaji Bulanan & Bonus',
            'amount' => $incomeAmount,
            'type' => 'income',
            'wallet_id' => $wallet->id,
            'trip_id' => null,
        ]);

        $wallet->refresh();
        $this->assertEquals($initialBalance + $incomeAmount, $wallet->current_balance);
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

    public function test_can_deposit_with_decimal_amount()
    {
        $user = User::first();
        $goalWallet = Wallet::where('user_id', $user->id)->where('type', 'trip')->first();

        $initialGoal = $goalWallet->current_balance;
        $deposit = 75500.50;

        Livewire::actingAs($user)
            ->test(WalletsIndex::class)
            ->call('openDepositModal', $goalWallet->id)
            ->set('depositAmount', $deposit)
            ->set('sourceMode', 'external')
            ->set('depositNote', 'Nabung desimal')
            ->call('saveDeposit')
            ->assertDispatched('transaction-saved');

        $goalWallet->refresh();
        $this->assertEquals((float)($initialGoal + $deposit), (float)$goalWallet->current_balance);
    }

    public function test_can_update_daily_spending_limit()
    {
        $user = User::first();
        $opWallet = Wallet::where('user_id', $user->id)->where('type', 'operational')->first();

        Livewire::actingAs($user)
            ->test(DailyLimitModal::class)
            ->call('openModal', $opWallet->id)
            ->set('dailyLimit', 250000)
            ->call('save')
            ->assertDispatched('transaction-saved');

        $opWallet->refresh();
        $this->assertEquals(250000, (int) $opWallet->daily_limit);
    }

    public function test_can_disable_daily_spending_limit()
    {
        $user = User::first();
        $opWallet = Wallet::where('user_id', $user->id)->where('type', 'operational')->first();

        Livewire::actingAs($user)
            ->test(DailyLimitModal::class)
            ->call('openModal', $opWallet->id)
            ->call('toggleEnabled')
            ->call('save')
            ->assertDispatched('transaction-saved');

        $opWallet->refresh();
        $this->assertNull($opWallet->daily_limit);
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

    public function test_analytics_history_week_selection_dispatches_event_and_persists_chart()
    {
        $user = User::first();

        Livewire::actingAs($user)
            ->test(History::class)
            ->call('selectWeek', 2)
            ->assertDispatched('week-selected', week: 2)
            ->assertSee('Min 2')
            ->assertSee('wire:ignore', false)
            ->call('selectWeek', 3)
            ->assertDispatched('week-selected', week: 3)
            ->assertSee('Min 3');
    }

    public function test_user_cannot_create_transaction_on_another_users_wallet_or_trip()
    {
        $victim = User::first();
        $victimWallet = Wallet::where('user_id', $victim->id)->where('type', 'operational')->first();
        $victimTrip = Trip::where('user_id', $victim->id)->first();
        $category = Category::where('type', 'expense')->first();
        $victimInitialBalance = $victimWallet->current_balance;

        $attacker = User::factory()->create();

        Livewire::actingAs($attacker)
            ->test(CreateModal::class)
            ->call('openModal', 'daily')
            ->set('amount', 50000)
            ->set('selectedWalletId', $victimWallet->id)
            ->set('selectedCategoryId', $category->id)
            ->set('title', 'Percobaan IDOR Wallet')
            ->set('transactionDate', now()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasErrors(['selectedWalletId']);

        $attackerWallet = Wallet::where('user_id', $attacker->id)->where('type', 'operational')->first();
        $attackerWallet->update(['current_balance' => 500000]);

        Livewire::actingAs($attacker)
            ->test(CreateModal::class)
            ->call('openModal', 'trip')
            ->set('amount', 50000)
            ->set('selectedWalletId', $attackerWallet->id)
            ->set('selectedTripId', $victimTrip->id)
            ->set('selectedCategoryId', $category->id)
            ->set('title', 'Percobaan IDOR Trip')
            ->set('transactionDate', now()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasErrors(['selectedTripId']);

        $victimWallet->refresh();
        $this->assertEquals($victimInitialBalance, $victimWallet->current_balance);
        $this->assertDatabaseMissing('transactions', ['title' => 'Percobaan IDOR Wallet']);
        $this->assertDatabaseMissing('transactions', ['title' => 'Percobaan IDOR Trip']);
    }

    public function test_user_cannot_deposit_using_another_users_wallet()
    {
        $victim = User::first();
        $victimWallet = Wallet::where('user_id', $victim->id)->where('type', 'operational')->first();
        $victimInitialBalance = $victimWallet->current_balance;

        $attacker = User::factory()->create();
        $attackerSaving = Wallet::where('user_id', $attacker->id)->where('type', 'saving')->first();

        Livewire::actingAs($attacker)
            ->test(WalletsIndex::class)
            ->call('openDepositModal', $attackerSaving->id)
            ->set('depositAmount', 100000)
            ->set('sourceMode', 'operational')
            ->set('sourceWalletId', $victimWallet->id)
            ->call('saveDeposit')
            ->assertHasErrors(['sourceWalletId']);

        $victimWallet->refresh();
        $this->assertEquals($victimInitialBalance, $victimWallet->current_balance);
    }

    public function test_expense_and_transfer_reject_insufficient_balance()
    {
        $user = User::factory()->create();
        $opWallet = Wallet::where('user_id', $user->id)->where('type', 'operational')->first();
        $savingWallet = Wallet::where('user_id', $user->id)->where('type', 'saving')->first();
        $category = Category::where('type', 'expense')->first();

        Livewire::actingAs($user)
            ->test(CreateModal::class)
            ->call('openModal', 'daily')
            ->set('amount', 50000)
            ->set('selectedWalletId', $opWallet->id)
            ->set('selectedCategoryId', $category->id)
            ->set('title', 'Belanja Melebihi Saldo')
            ->set('transactionDate', now()->format('Y-m-d\TH:i'))
            ->call('save')
            ->assertHasErrors(['amount']);

        Livewire::actingAs($user)
            ->test(WalletsIndex::class)
            ->call('openDepositModal', $savingWallet->id)
            ->set('depositAmount', 50000)
            ->set('sourceMode', 'operational')
            ->set('sourceWalletId', $opWallet->id)
            ->call('saveDeposit')
            ->assertHasErrors(['depositAmount']);
    }

    public function test_creating_inline_category_in_income_mode_sets_income_type()
    {
        $user = User::first();

        Livewire::actingAs($user)
            ->test(CreateModal::class)
            ->call('openModal', 'income')
            ->set('newCategoryName', 'Dividen Saham')
            ->set('newCategoryColor', '#00E676')
            ->call('saveNewCategory')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Dividen Saham',
            'type' => 'income',
        ]);
    }

    public function test_changing_email_in_profile_requires_current_password_and_resets_verification()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'password' => bcrypt('SecretPass123!'),
        ]);

        Livewire::actingAs($user)
            ->test(ProfileEdit::class)
            ->set('email', 'newemail@example.com')
            ->call('save')
            ->assertHasErrors(['current_password']);

        Livewire::actingAs($user)
            ->test(ProfileEdit::class)
            ->set('email', 'newemail@example.com')
            ->set('current_password', 'SecretPass123!')
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertEquals('newemail@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_spreadsheet_value_escapes_formula_prefixes()
    {
        $this->assertEquals("'=CMD()", \App\Support\SpreadsheetValue::escape('=CMD()'));
        $this->assertEquals("'+1+1", \App\Support\SpreadsheetValue::escape('+1+1'));
        $this->assertEquals("'-100", \App\Support\SpreadsheetValue::escape('-100'));
        $this->assertEquals("'@SUM(A1)", \App\Support\SpreadsheetValue::escape('@SUM(A1)'));
        $this->assertEquals('Makan Siang', \App\Support\SpreadsheetValue::escape('Makan Siang'));
    }

    public function test_toast_listener_is_present_in_main_layout()
    {
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('@notify.window', false);
    }

    public function test_can_edit_and_delete_transaction_with_balance_reconciliation()
    {
        $user = User::first();
        $wallet = Wallet::where('user_id', $user->id)->where('type', 'operational')->first();
        $category = Category::where('name', 'Makanan')->first();
        $initialBalance = (float) $wallet->current_balance;

        $tx = \App\Models\Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 100000,
            'title' => 'Belanja Awal',
            'transaction_date' => now(),
        ]);
        $wallet->decrement('current_balance', 100000);

        Livewire::actingAs($user)
            ->test(CreateModal::class)
            ->call('openEditModal', $tx->id)
            ->set('amount', 150000)
            ->set('title', 'Belanja Diubah')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('transaction-saved');

        $wallet->refresh();
        $tx->refresh();
        $this->assertEquals('Belanja Diubah', $tx->title);
        $this->assertEquals(150000, (float) $tx->amount);
        $this->assertEquals($initialBalance - 150000, (float) $wallet->current_balance);

        Livewire::actingAs($user)
            ->test(History::class)
            ->call('deleteTransaction', $tx->id)
            ->assertDispatched('transaction-saved');

        $wallet->refresh();
        $this->assertEquals($initialBalance, (float) $wallet->current_balance);
        $this->assertDatabaseMissing('transactions', ['id' => $tx->id]);
    }

    public function test_user_cannot_edit_or_delete_another_users_transaction()
    {
        $victim = User::first();
        $victimTx = \App\Models\Transaction::where('user_id', $victim->id)->where('type', 'expense')->first();
        $attacker = User::factory()->create();

        Livewire::actingAs($attacker)
            ->test(History::class)
            ->call('deleteTransaction', $victimTx->id)
            ->assertStatus(404);

        Livewire::actingAs($attacker)
            ->test(CreateModal::class)
            ->call('openEditModal', $victimTx->id)
            ->assertStatus(404);

        $this->assertDatabaseHas('transactions', ['id' => $victimTx->id]);
    }

    public function test_mutasi_search_and_type_filter_work()
    {
        $user = User::first();

        Livewire::actingAs($user)
            ->test(History::class)
            ->set('selectedWeek', null)
            ->set('search', 'Dimsum')
            ->assertSee('Sarapan Bubur')
            ->assertDontSee('GrabCar ke Stasiun')
            ->set('search', '')
            ->set('typeFilter', 'income')
            ->assertDontSee('GrabCar ke Stasiun');
    }

    public function test_can_edit_delete_wallet_and_transfer_between_wallets()
    {
        $user = User::first();
        $opWallet = Wallet::where('user_id', $user->id)->where('type', 'operational')->first();
        $tripWallet = Wallet::where('user_id', $user->id)->where('type', 'trip')->first();

        $opBefore = (float) $opWallet->current_balance;
        $tripBefore = (float) $tripWallet->current_balance;

        Livewire::actingAs($user)
            ->test(WalletsIndex::class)
            ->call('openTransferModal', $tripWallet->id)
            ->set('fromWalletId', $tripWallet->id)
            ->set('toWalletId', $opWallet->id)
            ->set('transferAmount', 200000)
            ->set('transferNote', 'Cairkan dana ke kas')
            ->call('saveTransfer')
            ->assertHasNoErrors()
            ->assertDispatched('transaction-saved');

        $opWallet->refresh();
        $tripWallet->refresh();
        $this->assertEquals($tripBefore - 200000, (float) $tripWallet->current_balance);
        $this->assertEquals($opBefore + 200000, (float) $opWallet->current_balance);

        // Create empty wallet, edit it, and delete it
        Livewire::actingAs($user)
            ->test(WalletsIndex::class)
            ->call('openCreateModal')
            ->set('name', 'Dompet Sementara')
            ->set('type', 'saving')
            ->set('initialBalance', 0)
            ->call('saveWallet');

        $emptyWallet = Wallet::where('user_id', $user->id)->where('name', 'Dompet Sementara')->firstOrFail();

        Livewire::actingAs($user)
            ->test(WalletsIndex::class)
            ->call('openEditModal', $emptyWallet->id)
            ->set('name', 'Dompet Diperbarui')
            ->call('saveWallet');

        $this->assertDatabaseHas('wallets', ['id' => $emptyWallet->id, 'name' => 'Dompet Diperbarui']);

        Livewire::actingAs($user)
            ->test(WalletsIndex::class)
            ->call('deleteWallet', $emptyWallet->id);

        $this->assertDatabaseMissing('wallets', ['id' => $emptyWallet->id]);
    }

    public function test_can_edit_and_delete_trip()
    {
        $user = User::first();
        $trip = Trip::where('user_id', $user->id)->firstOrFail();

        Livewire::actingAs($user)
            ->test(TripsIndex::class)
            ->call('openEditModal', $trip->id)
            ->set('name', 'Trip Jogja Revisi')
            ->set('budget', 5500000)
            ->call('saveTrip')
            ->assertHasNoErrors();

        $trip->refresh();
        $this->assertEquals('Trip Jogja Revisi', $trip->name);
        $this->assertEquals(5500000, (int) $trip->budget);

        Livewire::actingAs($user)
            ->test(TripsIndex::class)
            ->call('deleteTrip', $trip->id)
            ->assertDispatched('transaction-saved');

        $this->assertDatabaseMissing('trips', ['id' => $trip->id]);
    }

    public function test_global_search_modal_searches_only_authenticated_users_records()
    {
        $user = User::first();
        $otherUser = User::factory()->create();

        \App\Models\Transaction::create([
            'user_id' => $otherUser->id,
            'wallet_id' => Wallet::where('user_id', $otherUser->id)->firstOrFail()->id,
            'type' => 'expense',
            'amount' => 99000,
            'title' => 'Transaksi Rahasia Orang Lain',
            'transaction_date' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Search\GlobalModal::class)
            ->call('openModal')
            ->set('query', 'Dimsum')
            ->assertSee('Sarapan Bubur')
            ->set('query', 'Rahasia Orang Lain')
            ->assertDontSee('Transaksi Rahasia Orang Lain')
            ->set('query', 'Jogja')
            ->assertSee('Dompet Liburan Jogja')
            ->assertSee('Liburan Eksplor Jogja');
    }

    public function test_dashboard_and_profile_reflect_real_security_status_without_dead_links()
    {
        $dash = $this->get('/dashboard');
        $dash->assertStatus(200);
        $dash->assertSee('Keamanan Akun SIMAU');
        $dash->assertSee('Belum ada Passkey atau 2FA');
        $dash->assertDontSee('Proteksi saldo bank real-time');
        $dash->assertSee('open-global-search', false);

        $profile = $this->get('/profile');
        $profile->assertStatus(200);
        $profile->assertDontSee('href="#"', false);
        $profile->assertDontSee('secretpassword123');
        $profile->assertSee('Belum ada Passkey terdaftar');
    }
}
