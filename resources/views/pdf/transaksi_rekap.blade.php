<!DOCTYPE html>
<html>

<head>
    <title>Rekap Transaksi - SMK Yadika 2</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat Modern */
        .kop-surat {
            text-align: center;
            border-bottom: 3px solid #1e3a8a;
            padding-bottom: 10px;
            margin-bottom: 5px;
            margin-top: 10px;
        }

        .garis-bawah {
            border-bottom: 1px solid #1e3a8a;
            margin-bottom: 20px;
        }

        .nama-yayasan {
            font-size: 14px;
            margin: 0;
            text-transform: uppercase;
            color: #1e3a8a;
            font-weight: bold;
        }

        .nama-sekolah {
            font-size: 26px;
            margin: 5px 0;
            text-transform: uppercase;
            font-weight: bold;
            color: #1e3a8a;
        }

        .judul-laporan {
            text-align: center;
            font-size: 16px;
            margin: 15px 0;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Box Info Filter */
        .info-container {
            background-color: #f8fafc;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #e2e8f0;
        }

        .table-info {
            font-size: 11px;
            width: 100%;
        }

        .label {
            color: #64748b;
            font-weight: bold;
        }

        /* Gaya Tabel Sesuai Gambar dengan Warna */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .table-data th {
            background-color: #1e3a8a;
            /* Biru Navy */
            color: white;
            padding: 10px 4px;
            border: 1px solid #0f172a;
            text-align: center;
            text-transform: uppercase;
        }

        .table-data td {
            padding: 8px 4px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }

        /* Baris Selang-seling */
        .table-data tr:nth-child(even) {
            background-color: #f1f5f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        /* Warna Indikator */
        .text-id {
            color: #64748b;
            font-size: 8px;
        }

        .text-admin {
            color: #dc2626;
            font-weight: bold;
        }

        /* Merah */
        .text-netto {
            color: #16a34a;
            font-weight: bold;
        }

        /* Hijau */

        .footer-sign {
            margin-top: 30px;
            width: 100%;
            font-size: 11px;
        }

        .nama-petugas {
            color: #1e3a8a;
            text-decoration: underline;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="kop-surat">
        <p class="nama-yayasan">Yayasan Abdi Karya (Yadika)</p>

        <p class="nama-sekolah">{{ $settings['nama_website'] ?? 'SMK YADIKA 2 JAKARTA' }}</p>

        <p style="font-size: 10px; margin: 0; color: #555;">
            {{ $settings['alamat'] ?? 'Jl. Tanah Merdeka No. 20, Jakarta Timur' }}<br>

            Telp: {{ $settings['no_telp'] ?? '-' }} | Email: {{ $settings['email_website'] ?? '-' }}
        </p>
    </div>
    <div class="garis-bawah"></div>

    <div class="judul-laporan">REKAPITULASI TRANSAKSI BANK MINI</div>

    <div class="info-container">
        <table class="table-info">
            <tr>
                <td width="12%"><span class="label">Tahun Ajaran</span></td>
                <td>: {{ $infoFilter['kode_tahun_ajaran'] }}</td>
                <td width="12%"><span class="label">Periode Bulan</span></td>
                <td>: {{ $infoFilter['bulan'] }}</td>
            </tr>
            <tr>
                <td><span class="label">Kelas</span></td>
                <td colspan="3">: {{ $infoFilter['kelas'] }}</td>
            </tr>
        </table>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th width="12%">Tgl / ID</th>
                <th width="20%">Nama Nasabah</th>
                <th width="18%">Kelas & Jurusan</th>
                <th width="8%">Jenis</th>
                <th width="18%">Keterangan</th>
                <th width="10%">Nominal</th>
                <th width="10%">Biaya Admin</th>
                <th width="12%">Total Netto</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach ($transaksi as $item)
                @php
                    $nominalAwal = $item->jumlah ?? 0;
                    $ket = strtolower($item->keterangan_nasabah ?? '');

                    // Logika Biaya Admin (Sesuai Controller Anda)
                    $biayaAdmin = str_contains($ket, 'admin') || str_contains($ket, 'awal') ? 14000 : 0;

                    // Perhitungan Netto
                    $totalNetto = $item->kode_jenis === 'SETOR' ? $nominalAwal - $biayaAdmin : $nominalAwal;
                    $grandTotal += $totalNetto;
                @endphp
                <tr>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($item->tgl_transaksi)->format('d/m/Y') }}<br>
                        <span class="text-id">{{ $item->kode_transaksi }}</span>
                    </td>
                    <td>
                        <div class="font-bold">{{ $item->nasabah->nama ?? $item->nama_saat_transaksi }}</div>
                        <div style="font-size: 9px; color: #64748b;">{{ $item->no_rekening }}</div>
                    </td>
                    <td>
                        {{ $item->nasabah->kelas->nama_kelas ?? ($item->kelas_saat_transaksi ?? '-') }}<br>
                        <span
                            style="font-size: 8px; color: #64748b;">{{ $item->nasabah->kelas->jurusan->nama_jurusan ?? ($item->jurusan_saat_transaksi ?? '') }}</span>
                    </td>
                    <td class="text-center">
                        <span class="font-bold">{{ $item->kode_jenis }}</span>
                    </td>
                    <td style="font-style: italic; color: #475569;">{{ $item->keterangan_nasabah }}</td>
                    <td class="text-right">Rp {{ number_format($nominalAwal, 0, ',', '.') }}</td>
                    <td class="text-right text-admin">
                        {{ $biayaAdmin > 0 ? 'Rp ' . number_format($biayaAdmin, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right text-netto">
                        Rp {{ number_format($totalNetto, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #e2e8f0;">
                <td colspan="7" class="text-right font-bold">TOTAL SALDO REKAPITULASI</td>
                <td class="text-right font-bold" style="color: #1e3a8a; font-size: 11px;">
                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <table class="footer-sign">
        <tr>
            <td width="70%"></td>
            <td class="text-center">
                Jakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
            </td>
        </tr>
    </table>

</body>

</html>
