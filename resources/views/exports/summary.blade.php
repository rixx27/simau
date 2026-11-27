<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<table>
    <thead>
        <tr>
            <th colspan="4" style="font-size: 16px; font-weight: bold; background-color: #5865F2; color: #FFFFFF; text-align: center; height: 35px;">
                SIMAU - LAPORAN KEUANGAN DAN RINGKASAN ANGGARAN
            </th>
        </tr>
        <tr>
            <th colspan="4" style="background-color: #17181E; color: #9CA3AF; text-align: center; height: 25px;">
                Periode: {{ $monthName }} | Pengguna: {{ $user->name }} ({{ $user->email }})
            </th>
        </tr>
    </thead>
    <tbody>
        <tr><td></td></tr>
        <tr>
            <td colspan="2" style="font-weight: bold; background-color: #E0E7FF; color: #1E1B4B;">METRIK UTAMA</td>
            <td colspan="2" style="font-weight: bold; background-color: #E0E7FF; color: #1E1B4B;">NILAI (IDR)</td>
        </tr>
        <tr>
            <td colspan="2">Total Saldo Aktif Saat Ini</td>
            <td colspan="2" style="font-weight: bold;">Rp {{ number_format($totalBalance, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="2">Total Saldo Operasional</td>
            <td colspan="2">Rp {{ number_format($operationalBalance, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="2">Total Saldo Pundi Liburan &amp; Tabungan</td>
            <td colspan="2" style="color: #059669; font-weight: bold;">Rp {{ number_format($tripBalance, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="2">Total Pengeluaran (Bulan Ini)</td>
            <td colspan="2" style="color: #DC2626; font-weight: bold;">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="2">Total Pemasukan / Top-up (Bulan Ini)</td>
            <td colspan="2" style="color: #16A34A; font-weight: bold;">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="2">Total Alokasi Nabung / Transfer</td>
            <td colspan="2">Rp {{ number_format($totalTransfer, 0, ',', '.') }}</td>
        </tr>

        <tr><td></td></tr>
        <tr>
            <th colspan="4" style="font-weight: bold; background-color: #F3F4F6;">DISTRIBUSI PENGELUARAN PER KATEGORI</th>
        </tr>
        <tr style="background-color: #E5E7EB; font-weight: bold;">
            <td>No</td>
            <td>Kategori</td>
            <td>Tipe</td>
            <td>Total Pengeluaran (Rp)</td>
        </tr>
        @php $no = 1; @endphp
        @forelse($categoryExpenses as $category)
            @if(($category->transactions_sum_amount ?? 0) > 0)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ \App\Support\SpreadsheetValue::escape($category->name) }}</td>
                    <td>{{ ucfirst($category->type) }}</td>
                    <td>Rp {{ number_format($category->transactions_sum_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
        @empty
            <tr>
                <td colspan="4">Tidak ada data transaksi kategori pada periode ini.</td>
            </tr>
        @endforelse

        <tr><td></td></tr>
        <tr>
            <th colspan="4" style="font-weight: bold; background-color: #F3F4F6;">DAFTAR REKENING &amp; DOMPET</th>
        </tr>
        <tr style="background-color: #E5E7EB; font-weight: bold;">
            <td>Nama Dompet</td>
            <td>Label / Bank</td>
            <td>Tipe</td>
            <td>Saldo Saat Ini</td>
        </tr>
        @foreach($wallets as $wallet)
            <tr>
                <td>{{ \App\Support\SpreadsheetValue::escape($wallet->name) }}</td>
                <td>{{ \App\Support\SpreadsheetValue::escape($wallet->account_label) ?: '-' }}</td>
                <td>{{ ucfirst($wallet->type) }}</td>
                <td>Rp {{ number_format($wallet->current_balance, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
