<?php

namespace Tests\Feature;

use App\Exports\SimauReportExport;
use App\Livewire\Analytics\History;
use App\Livewire\Dashboard\Index;
use App\Livewire\Profile\Edit;
use App\Livewire\Transactions\CreateModal;
use App\Livewire\Wallets\Index as WalletsIndex;
use App\Models\Category;
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
    }

    public function test_dashboard_page_renders_successfully()
    {
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('TOTAL SALDO AKTIF');
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

    public function test_can_create_expense_transaction()
    {
        $user = User::first();
        $wallet = Wallet::where('type', 'operational')->first();
        $category = Category::where('name', 'Makanan')->first();

        $initialBalance = $wallet->current_balance;
        $expenseAmount = 50000;

        Livewire::actingAs($user)
            ->test(CreateModal::class)
            ->call('openModal', 'expense')
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
        ]);

        $wallet->refresh();
        $this->assertEquals($initialBalance - $expenseAmount, $wallet->current_balance);
    }

    public function test_can_create_trip_saving_transfer()
    {
        $user = User::first();
        $opWallet = Wallet::where('type', 'operational')->first();
        $tripWallet = Wallet::where('type', 'trip')->first();

        $opInitial = $opWallet->current_balance;
        $tripInitial = $tripWallet->current_balance;
        $savingAmount = 200000;

        Livewire::actingAs($user)
            ->test(CreateModal::class)
            ->call('openModal', 'saving')
            ->set('amount', $savingAmount)
            ->set('selectedWalletId', $opWallet->id)
            ->set('destinationWalletId', $tripWallet->id)
            ->set('title', 'Alokasi Nabung Tambahan')
            ->set('transactionDate', '2026-11-13T20:00')
            ->call('save')
            ->assertDispatched('transaction-saved');

        $this->assertDatabaseHas('transactions', [
            'title' => 'Alokasi Nabung Tambahan',
            'amount' => $savingAmount,
            'type' => 'transfer',
        ]);

        $opWallet->refresh();
        $tripWallet->refresh();

        $this->assertEquals($opInitial - $savingAmount, $opWallet->current_balance);
        $this->assertEquals($tripInitial + $savingAmount, $tripWallet->current_balance);
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
}
