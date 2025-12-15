<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Daftar Tahun Ajaran - Bank Mini</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{ asset('css/tahunajaran.css') }}"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <div id="app-container">

        {{-- <aside id="sidebar">
            <button id="sidebar-toggler" title="Toggle Sidebar">
                <i class="bi bi-arrow-left-square-fill"></i>
            </button>
            <div class="logo">BANK MINI</div>

            <nav>
                <a href="{{ route('superadmin.dashboard') }}" title="Dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" title="Nasabah">
                    <i class="bi bi-person-badge"></i>
                    <span>Nasabah</span>
                </a>
                <a href="#" title="Transaksi">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>Transaksi</span>
                </a>

                <div class="menu-section">MASTER</div>
                <a href="{{ route('superadmin.kelas') }}" title="Kelas">
                    <i class="bi bi-journals"></i>
                    <span>Kelas</span>
                </a>
                <a href="{{ route('superadmin.jurusan') }}" title="Jurusan">
                    <i class="bi bi-mortarboard"></i>
                    <span>Jurusan</span>
                </a>
                <a href="#" title="Akun">
                    <i class="bi bi-person-circle"></i>
                    <span>Akun</span>
                </a>
                <a href="{{ route('superadmin.tahun_ajaran') }}" class="active" title="Tahun Ajaran">
                    <i class="bi bi-calendar-check"></i>
                    <span>Tahun Ajaran</span>
                </a>
                <a href="#" title="Pengaturan">
                    <i class="bi bi-gear"></i>
                    <span>Pengaturan</span>
                </a>
            </nav>

            <a href="#" id="logoutButton" class="logout-btn" title="Keluar">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </a>
        </aside> --}}

        @include('bagian.sidebar')

        <main id="main-content">
            <header>
                <h1 class="page-title">Daftar Tahun Ajaran</h1>
                <button id="tambahTahunAjaranBtn" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Tambah Tahun Ajaran
                </button>
            </header>

            <section class="main-card">
                <div class="filter-controls">
                    <label for="statusFilter">Filter Status:</label>
                    <select id="statusFilter" class="form-control" style="width: 150px; display: inline-block;">
                        <option value="all">Semua</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                
                <div class="table-responsive" style="margin-top: 15px;">
                    <table class="table" id="tahunAjaranTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tahun Ajaran</th>
                                <th>Status</th>
                                <th style="width: 160px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    {{-- MODAL TAMBAH/EDIT TAHUN AJARAN (TIDAK ADA PERUBAHAN) --}}
    <div id="tahunAjaranModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeTahunAjaranModal">&times;</span>
            <h3 id="tahunAjaranModalTitle">Tambah Tahun Ajaran Baru</h3>
            <form id="tahunAjaranForm">
                <input type="hidden" id="tahunAjaranId" name="kode_tahun_ajaran">

                <div class="form-group-col">
                    <label for="inputTahunAjaran">Tahun Ajaran</label>
                    <input type="text" id="inputTahunAjaran" name="tahun_ajaran" required class="form-control"
                        placeholder="Contoh: 2024/2025">
                </div>

                <div class="form-group-col">
                    <label for="inputStatus">Status</label>
                    <select id="inputStatus" name="status" class="form-control" required>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btnBatalTahunAjaran">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanTahunAjaran">Simpan</button>
                </div>

                <div id="tahunAjaranFormMessage" class="form-message" style="margin-top: 15px;"></div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/tahun_ajaran.js') }}"></script>
</body>

</html>