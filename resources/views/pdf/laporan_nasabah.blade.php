<!DOCTYPE html>
<html>
<head>
    <title>Laporan Keuangan Nasabah</title>
    <style>
        /* Pengaturan Dasar */
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 11px; 
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat Modern */
        .kop { 
            text-align: center; 
            border-bottom: 3px double #1e3a8a; /* Warna Navy */
            padding-bottom: 10px; 
            margin-bottom: 20px; 
        }
        .kop h2 { 
            margin: 0; 
            color: #1e3a8a; 
            font-size: 20px;
            text-transform: uppercase;
        }
        .kop p { margin: 2px 0; color: #555; font-size: 10px; }

        /* Judul Laporan */
        .title-section {
            text-align: center;
            margin-bottom: 25px;
        }
        .title-section h3 {
            margin: 0;
            font-size: 16px;
            color: #1e3a8a;
            text-decoration: underline;
        }

        /* Info Header (TA, Jurusan, Kelas) */
        .info-container {
            margin-bottom: 20px;
            background-color: #f8fafc;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #e2e8f0;
        }
        .info-table { width: 100%; border: none; }
        .info-table td { padding: 2px 5px; border: none; vertical-align: top; }
        .label { font-weight: bold; color: #475569; width: 100px; }
        .separator { width: 10px; }

        /* Tabel Data */
        table.main-table { 
            width: 100%; 
            border-collapse: collapse; 
            background-color: #fff;
        }
        table.main-table th { 
            background-color: #1e3a8a; 
            color: white; 
            padding: 10px 8px;
            text-transform: uppercase;
            font-size: 10px;
            border: 1px solid #1e3a8a;
        }
        table.main-table td { 
            padding: 8px; 
            border: 1px solid #e2e8f0; 
        }
        
        /* Baris Selang-Seling (Zebra) */
        table.main-table tbody tr:nth-child(even) {
            background-color: #f1f5f9;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: 'Courier', monospace; font-size: 10px; }
        .bold { font-weight: bold; }

        /* Footer / Tanda Tangan */
        .footer-sign {
            margin-top: 40px;
            width: 100%;
        }
        .sign-box {
            float: right;
            width: 200px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="kop">
        <h2>{{ $settings['nama_website'] ?? 'SMK YADIKA 2 JAKARTA' }}</h2>
        <p>{{ $settings['alamat'] ?? 'Jl. Raya Kamal No.Kav. 2, RT.6/RW.3, Tegal Alur, Kec. Kalideres, Jakarta Barat' }}</p>
        <p>Telp: {{ $settings['no_telp'] ?? '-' }} | Email: {{ $settings['email_website'] ?? '-' }}</p>
    </div>

    <div class="title-section">
        <h3>LAPORAN DATA KEUANGAN NASABAH</h3>
    </div>

    <div class="info-container">
        <table class="info-table">
            <tr>
                <td class="label">Tanggal Cetak</td>
                <td class="separator">:</td>
                <td>{{ $tgl_cetak }}</td>
                <td class="label">Jurusan</td>
                <td class="separator">:</td>
                <td>{{ $filter['jurusan'] }}</td>
            </tr>
            <tr>
                <td class="label">Tahun Ajaran</td>
                <td class="separator">:</td>
                <td>{{ $filter['ta'] }}</td>
                <td class="label">Kelas</td>
                <td class="separator">:</td>
                <td>{{ $filter['kelas'] }}</td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">No. Rekening</th>
                <th>Nama Nasabah / NIS</th>
                <th width="15%">Jenis</th>
                <th width="20%" class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php $totalSaldo = 0; @endphp
            @forelse($nasabah as $index => $row)
            @php $totalSaldo += $row->saldo; @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-mono bold" style="color: #1e3a8a;">{{ $row->no_rekening }}</td>
                <td>
                    <span class="bold">{{ $row->nama }}</span><br>
                    <small style="color: #64748b;">NIS: {{ $row->no_induk }}</small>
                </td>
                <td class="text-center" style="text-transform: uppercase; font-size: 9px; font-weight: bold; color: #475569;">
                    {{ $row->jenis_rekening }}
                </td>
                <td class="text-right bold" style="color: #16a34a;">
                    Rp {{ number_format($row->saldo, 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="padding: 20px; color: #94a3b8;">Data tidak ditemukan</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #1e3a8a; color: white;">
                <td colspan="4" class="text-right bold" style="padding: 10px;">TOTAL SALDO KESELURUHAN</td>
                <td class="text-right bold" style="padding: 10px;">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-sign">
        <div class="sign-box">
            <p>Jakarta, {{ $tgl_cetak }}</p>
        </div>
    </div>

</body>
</html>