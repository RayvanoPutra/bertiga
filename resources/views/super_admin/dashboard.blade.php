<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Super Admin - Bank Mini</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div id="app-container">
        
        @include('bagian.sidebar')

        <main id="main-content">
            <header id="header-metrics">
                <div class="welcome-text" id="welcome-message">
                    <h1 class="page-title">Dashboard Utama</h1>
                    <p class="greeting-subtext">Selamat Datang kembali, <span id="user-name">Super Admin!</span> Berikut rangkuman aktivitas hari ini.</p>
                </div>
                <br>
                <br>
                <div class="metric-grid">
                    <div class="metric-box metric-green">
                        <div class="metric-info">
                            <span class="metric-title">Tabungan Hari Ini</span>
                            <span class="metric-value" id="tabungan-hari-ini-value">Rp. 0</span> 
                        </div>
                    </div>
                    
                    <div class="metric-box metric-blue">
                        <div class="metric-info">
                            <span class="metric-title">Total Tabungan</span>
                            <span class="metric-value" id="total-tabungan-value">Rp. 0</span> 
                        </div>
                    </div>
                    
                    <div class="metric-box metric-purple">
                        <i class="bi bi-person-bounding-box metric-icon"></i>
                        <div class="metric-info">
                            <span class="metric-title">Jumlah Nasabah</span>
                            <span class="metric-value">0</span>
                        </div>
                    </div>
                    
                    <div class="metric-box metric-orange">
                        <div class="metric-info">
                            <span class="metric-title">Jumlah Transaksi</span>
                            <span class="metric-value" id="jumlah-transaksi-value">0</span> 
                        </div>
                    </div>
                </div>
            </header>
            
            <section id="body-content">
                <div class="main-card chart-area">
                    <h3>Grafik Bulanan</h3>
                    <div class="chart-placeholder">Area Grafik Bar Bulanan</div>
                </div>

                <div class="main-card notification-area">
                    <h3><i class="bi bi-bell-fill"></i> Notifikasi Pengajuan</h3>
                    <div class="notification-list">
                        
                        <div class="notification-item item-type-setor">
                            <div class="info">
                                <span class="type-label">SETOR</span>
                                <p class="name-id">#22345135 - Siti Nurani</p>
                                <p class="amount">Rp.100.000,00</p>
                            </div>
                            <div class="actions">
                                <button class="btn-action btn-setor"><i class="bi bi-check-lg"></i> Setujui</button>
                                <button class="btn-action btn-tolak"><i class="bi bi-x-lg"></i> Tolak</button>
                            </div>
                        </div>
                        
                        <div class="notification-item item-type-tarik">
                            <div class="info">
                                <span class="type-label">TARIK</span>
                                <p class="name-id">#23134415 - Zainuddin</p>
                                <p class="amount">Rp.200.000,00</p>
                            </div>
                            <div class="actions">
                                <button class="btn-action btn-tarik"><i class="bi bi-check-lg"></i> Setujui</button>
                                <button class="btn-action btn-tolak"><i class="bi bi-x-lg"></i> Tolak</button>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </section>

            <section id="transaction-table">
                <div class="main-card">
                   <h3><i class="bi bi-clock-history"></i> Transaksi Nasabah Hari Ini</h3>
                   
                   <div class="table-responsive">
                       <table>
                           <thead>
                               <tr>
                                   <th>No</th>
                                   <th>No. Rekening</th>
                                   <th>Nama Nasabah</th>
                                   <th>Jumlah</th>
                                   <th>Jenis Rekening</th>
                                   <th>Jenis Transaksi</th>
                                   <th>Status</th>
                               </tr>
                           </thead>
                           <tbody>
                               <tr>
                                   <td>1</td>
                                   <td>123456789</td>
                                   <td>Burhan Jamaludin</td>
                                   <td>Rp 50.000,00</td>
                                   <td>Siswa</td>
                                   <td>Setor</td>
                                   <td><span class="status-badge status-success">Berhasil</span></td>
                               </tr>
                               <tr>
                                   <td>2</td>
                                   <td>987654321</td>
                                   <td>Fatimah Az-Zahra</td>
                                   <td>Rp 150.000,00</td>
                                   <td>Guru</td>
                                   <td>Tarik</td>
                                   <td><span class="status-badge status-pending">Tertunda</span></td>
                               </tr>
                               <tr>
                                   <td>3</td>
                                   <td>112233445</td>
                                   <td>Ahmad Subagio</td>
                                   <td>Rp 20.000,00</td>
                                   <td>Siswa</td>
                                   <td>Setor</td>
                                   <td><span class="status-badge status-failed">Gagal</span></td>
                               </tr>
                           </tbody>
                       </table>
                   </div>
                </div>
           </section>
        </main>
    </div>

    <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>