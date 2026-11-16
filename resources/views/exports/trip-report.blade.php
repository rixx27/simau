<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Rekap Finansial Perjalanan - {{ $trip->name }}</title>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="6" style="font-size: 16px; font-weight: bold; text-align: center; background-color: #5865F2; color: #ffffff; height: 35px;">
                    SIMAU - REKAP EVALUASI PERJALANAN (POST-TRIP REVIEW)
                </th>
            </tr>
            <tr>
                <th colspan="6" style="text-align: center; color: #555555; height: 20px;">
                    Perjalanan: {{ $trip->name }} &bull; Destinasi: {{ $trip->destination }} &bull; Periode: {{ $trip->formatted_date_range }} ({{ $trip->days_duration }} Hari)
                </th>
            </tr>
            <tr><th colspan="6"></th></tr>
        </thead>
        <tbody>
            <!-- Ringkasan Anggaran -->
            <tr style="background-color: #17181E; color: #ffffff;">
                <th colspan="6" style="font-weight: bold;">1. RINGKASAN ANGGARAN &amp; REALISASI</th>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: bold;">Status Perjalanan:</td>
                <td colspan="4">{{ $trip->status_label }}</td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: bold;">Plafon Anggaran (Budget Target):</td>
                <td colspan="4">{{ $trip->formatted_budget }}</td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: bold;">Total Realisasi Pengeluaran:</td>
                <td colspan="4">Rp {{ number_format($totalSpent, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: bold;">Hasil Evaluasi Finansial:</td>
                <td colspan="4" style="font-weight: bold; color: {{ $surplusDeficit >= 0 ? '#00A854' : '#DC2626' }};">
                    {{ $surplusDeficit >= 0 ? 'Surplus / Hemat Rp ' . number_format($surplusDeficit, 0, ',', '.') : 'Defisit / Overbudget Rp ' . number_format(abs($surplusDeficit), 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: bold;">Rata-rata Pengeluaran Per Hari:</td>
                <td colspan="4">{{ $trip->formatted_average_daily_spent }} / hari</td>
            </tr>

            <tr><td colspan="6"></td></tr>

            <!-- Breakdown per Kategori -->
            <tr style="background-color: #17181E; color: #ffffff;">
                <th colspan="6" style="font-weight: bold;">2. DISTRIBUSI PENGELUARAN PER KATEGORI</th>
            </tr>
            <tr style="background-color: #F3F4F6; font-weight: bold;">
                <td colspan="3">Kategori</td>
                <td style="text-align: center;">Jumlah Transaksi</td>
                <td colspan="2" style="text-align: right;">Total Nominal (Rp)</td>
            </tr>
            @foreach($categoryBreakdown as $cat)
                <tr>
                    <td colspan="3">{{ $cat['name'] }}</td>
                    <td style="text-align: center;">{{ $cat['count'] }}</td>
                    <td colspan="2" style="text-align: right;">Rp {{ number_format($cat['total'], 0, ',', '.') }}</td>
                </tr>
            @endforeach

            <tr><td colspan="6"></td></tr>

            <!-- Log Kronologis Transaksi -->
            <tr style="background-color: #17181E; color: #ffffff;">
                <th colspan="6" style="font-weight: bold;">3. RINCIAN LOG TRANSAKSI PERJALANAN</th>
            </tr>
            <tr style="background-color: #F3F4F6; font-weight: bold;">
                <th style="width: 40px; text-align: center;">No</th>
                <th style="width: 140px;">Waktu &amp; Tanggal</th>
                <th style="width: 130px;">Kategori</th>
                <th style="width: 220px;">Uraian Transaksi</th>
                <th style="width: 180px;">Catatan</th>
                <th style="width: 140px; text-align: right;">Nominal (Rp)</th>
            </tr>
            @forelse($transactions as $index => $tx)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $tx->transaction_date ? $tx->transaction_date->format('d/m/Y H:i') : '-' }}</td>
                    <td>{{ $tx->category ? $tx->category->name : 'Lainnya' }}</td>
                    <td>{{ $tx->title }}</td>
                    <td>{{ $tx->note ?? '-' }}</td>
                    <td style="text-align: right;">Rp {{ number_format($tx->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #888888;">Belum ada pengeluaran yang dicatat untuk perjalanan ini.</td>
                </tr>
            @endforelse
            <tr style="background-color: #E5E7EB; font-weight: bold;">
                <td colspan="5" style="text-align: right;">TOTAL PENGELUARAN TRIP:</td>
                <td style="text-align: right;">Rp {{ number_format($totalSpent, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
