<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Jurusan - Bank Mini</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{ asset('css/jurusan.css') }}"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    </head>
<body>
    <div id="app-container">

    <aside id="sidebar">
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
            <a href="{{ route('superadmin.jurusan') }}" class="active" title="Jurusan"> 
                <i class="bi bi-mortarboard"></i>
                <span>Jurusan</span>
            </a>
            <a href="#" title="Akun">
                <i class="bi bi-person-circle"></i>
                <span>Akun</span>
            </a>
            <a href="{{ route('superadmin.tahun_ajaran') }}" title="Tahun Ajaran">
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
    </aside>

        <main id="main-content">
            <header>
                <h1 class="page-title">Daftar Jurusan</h1>
                <button id="tambahJurusanBtn" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Tambah Jurusan
                </button>
            </header>
            
            <section class="main-card">
                <div class="table-responsive">
                    <table class="table" id="jurusanTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Jurusan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <div id="jurusanModal" class="modal">
        <div class="modal-content" style="max-width: 450px;">
            <span class="close-btn" id="closeJurusanModal">&times;</span>
            <h3 id="jurusanModalTitle">Tambah Jurusan Baru</h3>
            <form id="jurusanForm">
                <input type="hidden" id="jurusanId" name="kode_jurusan"> 

                <div class="form-group-col">
                    <label for="inputNamaJurusan">Nama Jurusan</label>
                    <input type="text" id="inputNamaJurusan" name="nama_jurusan" required class="form-control" placeholder="Contoh: Rekayasa Perangkat Lunak">
                </div>

                <div class="form-group-col">
                    <label for="modalJurusanTahunAjaran">Tahun Ajaran</label>
                    <select id="modalJurusanTahunAjaran" name="kode_tahun_ajaran" required class="form-control">
                        </select>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btnBatalJurusan">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanJurusan">Simpan</button>
                </div>
                
                <div id="jurusanFormMessage" class="form-message" style="margin-top: 15px;"></div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/jurusan.js') }}"></script>
</body>
</html>