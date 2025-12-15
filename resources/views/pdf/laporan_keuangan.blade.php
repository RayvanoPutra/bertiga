<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan - {{ $nasabah->nama }}</title>
    <style>
        /* Reset & Font Dasar */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }

        /* Kop Surat */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px double #0D47A1;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #0D47A1;
            font-size: 24px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 14px;
            color: #666;
        }

        /* Info Nasabah */
        .info-section {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            color: #555;
            width: 120px;
        }

        /* Kotak Saldo */
        .saldo-box {
            background-color: #E3F2FD;
            border: 1px solid #90CAF9;
            padding: 10px;
            text-align: right;
            border-radius: 5px;
        }
        .saldo-title {
            font-size: 10px;
            color: #0D47A1;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .saldo-amount {
            font-size: 18px;
            font-weight: bold;
            color: #0D47A1;
            margin-top: 5px;
        }

        /* Tabel Transaksi */
        .transaksi-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .transaksi-table th {
            background-color: #0D47A1;
            color: #ffffff;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        .transaksi-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
        }
        .transaksi-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        /* Helper Classes */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-green { color: #2E7D32; font-weight: bold; }
        .text-red { color: #C62828; font-weight: bold; }
        .text-gray { color: #999; text-decoration: line-through; } /* Style untuk Rejected */
        .text-bold { font-weight: bold; }
        
        /* Footer */
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <div class="header">
        <h1>Bank Mini Sekolah</h1>
        <p>Laporan Mutasi Rekening Nasabah</p>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y, H:i') }}</p>
    </div>

    <!-- INFORMASI NASABAH -->
    <table class="info-section">
        <tr>
            <td width="60%">
                <table class="info-table">
                    <tr>
                        <td class="label">Nama Nasabah</td>
                        <td>: <strong>{{ $nasabah->nama }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">Nomor Rekening</td>
                        <td>: {{ $nasabah->no_rekening }}</td>
                    </tr>
                    <tr>
                        <td class="label">NIS / NIP</td>
                        <td>: {{ $nasabah->no_induk }}</td>
                    </tr>
                    <tr>
                        <td class="label">Kelas / Jurusan</td>
                        <td>: 
                            @if($nasabah->kelas)
                                {{ $nasabah->kelas->nama_kelas }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
            <td width="40%" style="vertical-align: middle;">
                <div class="saldo-box">
                    <div class="saldo-title">Sisa Saldo Akhir</div>
                    <div class="saldo-amount">Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <hr style="border: 0; border-top: 1px solid #ddd; margin: 20px 0;">

    <!-- TABEL TRANSAKSI -->
    <h3 style="color: #444; margin-bottom: 10px;">Rincian Transaksi</h3>
    
    <table class="transaksi-table">
        <thead>
            <tr>
                <th width="20%">Tanggal</th>
                <th width="25%">Jenis Transaksi</th>
                <th width="30%">Keterangan</th>
                <th width="10%" class="text-center">Status</th>
                <th width="15%" class="text-right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksi as $t)
            <tr>
                <td>
                    {{ \Carbon\Carbon::parse($t->tgl_transaksi)->format('d/m/Y') }} <br>
                    <span style="font-size: 10px; color: #888;">
                        {{ \Carbon\Carbon::parse($t->tgl_transaksi)->format('H:i') }} WIB
                    </span>
                </td>
                <td class="text-bold">{{ $t->jenisTransaksi->nama_jenis }}</td>
                <td>{{ $t->keterangan_nasabah ?? '-' }}</td>
                
                <!-- KOLOM STATUS (CR/DB/X) -->
                <td class="text-center">
                    @if($t->status == 'rejected')
                        <!-- STATUS BATAL (X) -->
                        <span style="background: #EEEEEE; color: #757575; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold;">X</span>
                    @elseif($t->jenisTransaksi->nama_jenis == 'Setor Tunai' || $t->jenisTransaksi->nama_jenis == 'Saldo Awal')
                        <!-- STATUS MASUK (CR) -->
                        <span style="background: #E8F5E9; color: #2E7D32; padding: 2px 6px; border-radius: 4px; font-size: 10px;">CR</span>
                    @else
                        <!-- STATUS KELUAR (DB) -->
                        <span style="background: #FFEBEE; color: #C62828; padding: 2px 6px; border-radius: 4px; font-size: 10px;">DB</span>
                    @endif
                </td>

                <!-- KOLOM JUMLAH -->
                <td class="text-right">
                    @if($t->status == 'rejected')
                        <!-- JIKA DITOLAK: CORET & ABU-ABU -->
                        <span class="text-gray">{{ number_format($t->jumlah, 0, ',', '.') }}</span>
                    @elseif($t->jenisTransaksi->nama_jenis == 'Setor Tunai' || $t->jenisTransaksi->nama_jenis == 'Saldo Awal')
                        <!-- JIKA MASUK: HIJAU (+) -->
                        <span class="text-green">+ {{ number_format($t->jumlah, 0, ',', '.') }}</span>
                    @else
                        <!-- JIKA KELUAR: MERAH (-) -->
                        <span class="text-red">- {{ number_format($t->jumlah, 0, ',', '.') }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="padding: 20px; color: #999;">
                    Tidak ada riwayat transaksi yang ditemukan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini dibuat secara otomatis oleh sistem Bank Mini Sekolah pada tanggal {{ date('d-m-Y') }}.</p>
        <p>Laporan ini sah tanpa tanda tangan basah.</p>
    </div>

</body>
</html>