<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<table>
    <thead>
        <tr>
            <th colspan="8" style="font-size: 16px; font-weight: bold; background-color: #5865F2; color: #FFFFFF; text-align: center; height: 35px;">
                SIMAU - DETAIL MUTASI TRANSAKSI
            </th>
        </tr>
        <tr>
            <th colspan="8" style="background-color: #17181E; color: #9CA3AF; text-align: center; height: 25px;">
                Pengguna: {{ $user->name }} ({{ $user->email }})
            </th>
        </tr>
        <tr style="background-color: #E0E7FF; font-weight: bold; height: 28px;">
            <th style="text-align: center;">No</th>
            <th>Tanggal dan Waktu</th>
            <th>Tipe</th>
            <th>Dompet / Sumber</th>
            <th>Kategori</th>
            <th>Judul Transaksi</th>
            <th>Catatan</th>
            <th style="text-align: right;">Nominal (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($transactions as $index => $trx)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($trx->transaction_date)->translatedFormat('d M Y, H:i') }} WIB</td>
                <td style="text-transform: capitalize;">
                    @if($trx->type === 'expense')
                        Pengeluaran
                    @elseif($trx->type === 'income')
                        Pemasukan
                    @else
                        Nabung / Transfer
                    @endif
                </td>
                <td>
                    {{ $trx->wallet->name ?? '-' }}
                    @if($trx->destinationWallet)
                        -&gt; {{ $trx->destinationWallet->name }}
                    @endif
                </td>
                <td>{{ $trx->category->name ?? ($trx->type === 'transfer' ? 'Nabung Trip' : '-') }}</td>
                <td style="font-weight: 500;">{{ $trx->title }}</td>
                <td>{{ $trx->note ?? '-' }}</td>
                <td style="text-align: right; font-weight: bold; color: {{ $trx->type === 'expense' ? '#DC2626' : '#16A34A' }};">
                    {{ $trx->type === 'expense' ? '-Rp ' : '+Rp ' }}{{ number_format($trx->amount, 0, ',', '.') }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align: center; color: #6B7280;">Tidak ada catatan mutasi pada periode ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>
</body>
</html>
