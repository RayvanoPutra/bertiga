<!DOCTYPE html>
<html>
<head>
    <title>Laporan Keuangan Android</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        .info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #1e3a8a; color: white; padding: 7px; border: 1px solid #000; }
        td { padding: 7px; border: 1px solid #000; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $nama_sekolah }}</h2>
        <p>{{ $alamat }}</p>
    </div>

    <div class="info">
        <p><strong>Nama Nasabah :</strong> {{ $nasabah->nama }}</p>
        <p><strong>No. Rekening  :</strong> {{ $nasabah->no_rekening }}</p>
        <p><strong>Periode       :</strong> {{ $periode }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @forelse($transaksi as $index => $t)
                @php $total += $t->jumlah; @endphp
                <tr>
                    <td align="center">{{ $index + 1 }}</td>
                    <td>{{ date('d-m-Y', strtotime($t->tgl_transaksi)) }}</td>
                    <td>{{ $t->keterangan_nasabah ?? 'Transaksi Bank Mini' }}</td>
                    <td class="text-right">Rp {{ number_format($t->jumlah, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" align="center">Tidak ada transaksi pada periode ini.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background: #eee;">
                <td colspan="3" class="text-right">TOTAL</td>
                <td class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <p style="margin-top: 20px;">Dicetak pada: {{ $tgl_cetak }}</p>
</body>
</html>