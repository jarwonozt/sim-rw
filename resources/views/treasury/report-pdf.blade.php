<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Kas {{ $periodLabel }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
        }

        h1 {
            font-size: 15px;
            text-align: center;
            margin-bottom: 2px;
        }

        .subtitle {
            text-align: center;
            margin-bottom: 16px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 5px 8px;
            text-align: left;
        }

        th {
            background: #f3f4f6;
        }

        .amount {
            text-align: right;
        }

        .summary {
            width: 60%;
            margin: 0 0 20px auto;
        }

        .summary td {
            border: none;
            padding: 2px 8px;
        }

        .summary .label {
            color: #555;
        }

        .summary .total-row td {
            border-top: 2px solid #1a1a1a;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Laporan Rekapitulasi Kas RW</h1>
    <p class="subtitle">Periode: {{ $periodLabel }}</p>

    <table class="summary">
        <tr>
            <td class="label">Total Kas Masuk</td>
            <td class="amount">Rp {{ number_format($summary['total_masuk'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Total Kas Keluar</td>
            <td class="amount">Rp {{ number_format($summary['total_keluar'], 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <td class="label">Saldo Akhir Periode</td>
            <td class="amount">Rp {{ number_format($summary['saldo_akhir'], 0, ',', '.') }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Kategori</th>
                <th>Keterangan</th>
                <th class="amount">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->transaction_date->format('d-m-Y') }}</td>
                    <td>{{ $transaction->type === 'in' ? 'Masuk' : 'Keluar' }}</td>
                    <td>{{ $transaction->category->name }}</td>
                    <td>{{ $transaction->description }}</td>
                    <td class="amount">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Tidak ada transaksi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
