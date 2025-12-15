<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Daftar Kelas - Bank Mini</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<style>
    /* --- Style Modal --- */
    .modal {
        display: none;
        /* Sembunyikan secara default */
        position: fixed;
        z-index: 1000;
        /* Pastikan di atas elemen lain */
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.6);
        /* Background gelap transparan */
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0
        }

        to {
            opacity: 1
        }
    }

    .modal-content {
        background-color: #ffffff;
        margin: 10% auto;
        /* Jarak dari atas dan tengah horizontal */
        padding: 30px;
        border-radius: 8px;
        width: 90%;
        /* Lebar di perangkat kecil */
        max-width: 600px;
        /* Lebar maksimum */
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        position: relative;
    }

    .close-btn {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close-btn:hover,
    .close-btn:focus {
        color: #333;
        text-decoration: none;
    }

    /* Style untuk grid form di modal */
    .form-group-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 15px;
    }

    .form-group-col {
        display: flex;
        flex-direction: column;
    }

    .form-group-col label {
        margin-bottom: 5px;
        font-weight: 600;
    }

    .form-control {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 1em;
    }

    .modal-footer {
        padding-top: 20px;
        text-align: right;
        border-top: 1px solid #eee;
        margin-top: 20px;
    }

    .form-message {
        padding: 10px;
        border-radius: 4px;
        display: none;
        font-size: 0.9em;
    }

    .form-message.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .form-message.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    /* Penyesuaian button untuk konsistensi */
    .btn-primary {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 4px;
        cursor: pointer;
        margin-right: 10px;
    }
</style>

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
                <a href="{{ route('superadmin.kelas') }}" class="active" title="Kelas">
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
                <a href="#" title="Tahun Ajaran">
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
                <h1 class="page-title">Daftar Kelas</h1>
                <button id="tambahKelasBtn" class="btn btn-primary" style="float: right;">
                    <i class="bi bi-plus-lg"></i> Tambah Kelas
                </button>
            </header>

            <section class="main-card">
                <div class="filter-controls" style="display: flex; gap: 20px; margin-bottom: 20px;">
                    <div style="flex: 1;">
                        <label for="filterTahunAjaran">Tahun Ajaran</label>
                        <select id="filterTahunAjaran" class="form-control" style="width: 100%;">
                            <option value="">Semua Tahun Ajaran</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label for="filterJurusan">Jurusan</label>
                        <select id="filterJurusan" class="form-control" style="width: 100%;">
                            <option value="">Semua Jurusan</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table" id="kelasTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kelas</th>
                                <th>Jurusan</th>
                                <th>Tahun Ajaran</th>
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

    {{-- form input --}}
    <div id="kelasModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h3 id="modalTitle">Tambah Kelas Baru</h3>
            <form id="kelasForm">
                <input type="hidden" id="kelasId" name="kode_kelas">

                <div class="form-group-grid">
                    <div class="form-group-col">
                        <label for="kode_jurusan">Jurusan</label>
                        <select id="kode_jurusan" name="kode_jurusan" required class="form-control">
                        </select>
                    </div>

                    <div class="form-group-col">
                        <label for="nama_kelas">Kelas</label>
                        <input type="text" id="nama_kelas" name="nama_kelas" required class="form-control"
                            placeholder="Contoh: X RPL 1">
                    </div>
                </div>

                <div class="form-group-grid">
                    <div class="form-group-col">
                        <label for="kode_tahun_ajaran">Tahun Ajaran</label>
                        <select id="kode_tahun_ajaran" name="kode_tahun_ajaran" required class="form-control">
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btnBatal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpan">Simpan</button>
                </div>

                <div id="formMessage" class="form-message" style="margin-top: 15px;"></div>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('sanctum_token');
            if (!token) {
                window.location.href = '/login';
                return;
            }
    
            // --- 1. REFERENSI ELEMEN ---
            const kelasTableBody = document.querySelector('#kelasTable tbody');
            const filterJurusan = document.getElementById('filterJurusan');
            const filterTahunAjaran = document.getElementById('filterTahunAjaran');
    
            // Elemen Modal
            const kelasModal = document.getElementById('kelasModal');
            const tambahKelasBtn = document.getElementById('tambahKelasBtn');
            const closeBtn = document.querySelector('.close-btn');
            const btnBatal = document.getElementById('btnBatal');
            const kelasForm = document.getElementById('kelasForm');
            const modalJurusanSelect = document.getElementById('kode_jurusan');
            const modalTahunAjaranSelect = document.getElementById('kode_tahun_ajaran');
            const formMessage = document.getElementById('formMessage');
            
            // Asumsi nama_kelas memiliki ID 'nama_kelas'
            const inputNamaKelas = document.getElementById('nama_kelas'); 
    
            // --- 2. FUNGSI UTILITY & HANDLER ---
    
            function handleLogout() {
                localStorage.removeItem('sanctum_token');
                window.location.href = '/login';
            }
    
            function openModal() {
                kelasModal.style.display = 'block';
            }
    
            function closeModal() {
                kelasModal.style.display = 'none';
            }
    
            function showMessage(message, type) {
                formMessage.innerHTML = message;
                formMessage.className = `form-message ${type}`;
                formMessage.style.display = 'block';
            }
            
            // --- FUNGSI BARU UNTUK EDIT ---
            async function fetchKelasById(kodeKelas) {
                try {
                    const response = await fetch(`/api/master/kelas/${kodeKelas}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    if (response.status === 401) throw new Error('Unauthorized');
                    if (!response.ok) throw new Error('Kelas tidak ditemukan');
                    return await response.json();
                } catch (error) {
                    console.error('Gagal mengambil data kelas untuk edit:', error);
                    if (error.message === 'Unauthorized') handleLogout();
                    return null;
                }
            }
            
            async function handleEdit(kodeKelas) {
                const kelas = await fetchKelasById(kodeKelas);
                if (!kelas) return;
    
                // Isi Modal
                document.getElementById('modalTitle').textContent = 'Edit Kelas';
                kelasForm.reset();
                formMessage.style.display = 'none';
    
                // 1. Set Primary Key (kode_kelas) di hidden input (id='kelasId')
                // Input ini harus memiliki name="kode_kelas" di HTML
                document.getElementById('kelasId').value = kelas.kode_kelas; 
                
                // 2. Isi field lainnya
                inputNamaKelas.value = kelas.nama_kelas; 
                modalJurusanSelect.value = kelas.kode_jurusan; 
                modalTahunAjaranSelect.value = kelas.kode_tahun_ajaran; 
    
                openModal();
            }
    
            // --- FUNGSI BARU UNTUK DELETE ---
            async function handleDelete(kodeKelas) {
                if (!confirm('Apakah Anda yakin ingin menghapus kelas ini? Tindakan ini tidak dapat dibatalkan.')) {
                    return;
                }
    
                try {
                    const response = await fetch(`/api/master/kelas/${kodeKelas}`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
    
                    if (response.status === 401) throw new Error('Unauthorized');
                    if (!response.ok) {
                        const result = await response.json();
                        throw new Error(result.message || 'Gagal menghapus data kelas.');
                    }
    
                    alert('Kelas berhasil dihapus!');
                    fetchKelas(); // Muat ulang data
                } catch (error) {
                    console.error('Gagal menghapus kelas:', error);
                    alert(`Gagal menghapus kelas: ${error.message}`);
                    if (error.message.includes('Unauthorized')) handleLogout();
                }
            }
    
            // --- 3. FUNGSI FETCH DATA MASTER ---
    
            async function fetchMasterData() {
                try {
                    // Fetch Jurusan
                    const resJurusan = await fetch('/api/master/jurusan', {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    const dataJurusan = await resJurusan.json();
    
                    // Isi Jurusan di Filter dan Modal
                    [filterJurusan, modalJurusanSelect].forEach(select => {
                        if (select.id === 'filterJurusan') {
                            select.innerHTML = '<option value="">Semua Jurusan</option>';
                        } else {
                            select.innerHTML = ''; 
                        }
    
                        dataJurusan.forEach(jurusan => {
                            const option = document.createElement('option');
                            option.value = jurusan.kode_jurusan;
                            option.textContent = jurusan.nama_jurusan;
                            select.appendChild(option);
                        });
                    });
    
                    // Fetch Tahun Ajaran
                    const resTA = await fetch('/api/master/tahun-ajaran', {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    const dataTA = await resTA.json();
    
                    // Isi Tahun Ajaran di Filter dan Modal
                    [filterTahunAjaran, modalTahunAjaranSelect].forEach(select => {
                        if (select.id === 'filterTahunAjaran') {
                            select.innerHTML = '<option value="">Semua Tahun Ajaran</option>';
                        } else {
                            select.innerHTML = ''; 
                        }
    
                        dataTA.forEach(ta => {
                            const option = document.createElement('option');
                            option.value = ta.kode_tahun_ajaran;
                            option.textContent = ta.tahun_ajaran;
                            select.appendChild(option);
                        });
                    });
    
                } catch (error) {
                    console.error('Gagal memuat Data Master:', error);
                    if (error.message.includes('Unauthorized')) handleLogout();
                }
            }
    
            // --- 4. FUNGSI FETCH KELAS ---
            async function fetchKelas() {
                kelasTableBody.innerHTML =
                    '<tr><td colspan="5" style="text-align: center;">⏳ Memuat data kelas...</td></tr>';
    
                const selectedJurusan = filterJurusan.value;
                const selectedTahunAjaran = filterTahunAjaran.value;
    
                try {
                    const response = await fetch('/api/master/kelas', {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
    
                    if (response.status === 401) throw new Error('Unauthorized');
    
                    const kelasList = await response.json();
    
                    // Client-Side Filtering
                    const filteredList = kelasList.filter(kelas => {
                        const matchJurusan = !selectedJurusan || (kelas.jurusan && kelas.jurusan
                            .kode_jurusan === selectedJurusan);
                        const matchTahunAjaran = !selectedTahunAjaran || (kelas.tahun_ajaran && kelas
                            .tahun_ajaran.kode_tahun_ajaran == selectedTahunAjaran);
                        return matchJurusan && matchTahunAjaran;
                    });
    
                    kelasTableBody.innerHTML = '';
    
                    if (filteredList.length === 0) {
                        kelasTableBody.innerHTML =
                            '<tr><td colspan="5" style="text-align: center;">Tidak ada data kelas yang ditemukan.</td></tr>';
                        return;
                    }
    
                    filteredList.forEach((kelas, index) => {
                        const row = kelasTableBody.insertRow();
                        row.innerHTML = `
                            <td>${index + 1}</td>
                            <td>${kelas.nama_kelas}</td>
                            <td>${kelas.jurusan ? kelas.jurusan.nama_jurusan : 'T/A'}</td>
                            <td>${kelas.tahun_ajaran ? kelas.tahun_ajaran.tahun_ajaran : 'T/A'}</td>
                            <td>
                                <button class="btn-sm btn-warning edit-btn" data-id="${kelas.kode_kelas}">Edit</button>
                                <button class="btn-sm btn-danger delete-btn" data-id="${kelas.kode_kelas}">Hapus</button>
                            </td>
                        `;
                    });
                    
                    // --- Menghubungkan Event Edit dan Hapus setelah data dimuat ---
                    document.querySelectorAll('.edit-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            const kodeKelas = this.getAttribute('data-id');
                            handleEdit(kodeKelas);
                        });
                    });
    
                    document.querySelectorAll('.delete-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            const kodeKelas = this.getAttribute('data-id');
                            handleDelete(kodeKelas);
                        });
                    });
    
                } catch (error) {
                    console.error('Gagal mengambil data kelas:', error);
                    kelasTableBody.innerHTML =
                        '<tr><td colspan="5" style="color: red; text-align: center;">❌ Gagal memuat data kelas.</td></tr>';
                    if (error.message === 'Unauthorized') handleLogout();
                }
            }
    
            // --- 5. SUBMIT FORM KE API ---
            kelasForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                formMessage.style.display = 'none';
    
                // Menggunakan kode_kelas dari hidden input
                const kodeKelas = document.getElementById('kelasId').value;
                const method = kodeKelas ? 'PUT' : 'POST';
                
                // URL menggunakan kode_kelas
                const url = kodeKelas ?
                    `/api/master/kelas/${kodeKelas}` :
                    '/api/master/kelas';
    
                const formData = new FormData(kelasForm);
                const data = Object.fromEntries(formData.entries());
    
                // JIKA MODE TAMBAH, GENERATE KODE_KELAS YANG UNIK (PENTING!)
                if (!kodeKelas) {
                    // Hapus key "id" jika ada (walaupun seharusnya sudah name="kode_kelas")
                    if (data.id) delete data.id; 
                    
                    // Generate kode_kelas (contoh robust, idealnya di backend)
                    const namaKelasUpper = data.nama_kelas.replace(/\s/g, '').toUpperCase().substring(0, 5);
                    const jur = data.kode_jurusan.substring(0, 3).toUpperCase();
                    const ta = data.kode_tahun_ajaran.substring(0, 4);
                    const random = Math.floor(100 + Math.random() * 900); // Angka 3 digit
                    
                    // Contoh: KLS-AKL1-AKT-2024-543
                    data['kode_kelas'] = 
                        `KLS-${namaKelasUpper}-${jur}-${ta}-${random}`;
                }
    
                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });
    
                    const result = await response.json();
    
                    if (!response.ok) {
                        let errorMessage = 'Gagal menyimpan data.';
                        // Tampilkan error validasi dari Laravel
                        if (response.status === 422 && result.errors) {
                            errorMessage = Object.values(result.errors).flat().join('<br>');
                        } else if (result.message) {
                            errorMessage = result.message;
                        }
                        showMessage(errorMessage, 'error');
                        return;
                    }
    
                    showMessage(`Kelas berhasil ${kodeKelas ? 'diperbarui' : 'ditambahkan'}!`,
                        'success');
                    
                    setTimeout(() => {
                        closeModal();
                        fetchKelas(); 
                    }, 1500);
    
                } catch (error) {
                    console.error('API Error:', error);
                    showMessage('Terjadi kesalahan jaringan.', 'error');
                }
            });
    
            // --- 6. INITIAL CALLS & EVENT LISTENERS ---
    
            fetchMasterData();
            fetchKelas();
    
            // Event Listener untuk Filter
            filterJurusan.addEventListener('change', fetchKelas);
            filterTahunAjaran.addEventListener('change', fetchKelas);
    
            // Event Listener Modal
            tambahKelasBtn.addEventListener('click', function() {
                document.getElementById('modalTitle').textContent = 'Tambah Kelas Baru';
                kelasForm.reset();
                document.getElementById('kelasId').value = '';
                formMessage.style.display = 'none';
                openModal();
            });
            closeBtn.addEventListener('click', closeModal);
            btnBatal.addEventListener('click', closeModal);
    
            // Tutup modal ketika mengklik di luar modal
            window.addEventListener('click', function(event) {
                if (event.target === kelasModal) {
                    closeModal();
                }
            });
            
            // Asumsi Logout ada di id='logoutButton'
            const logoutButton = document.getElementById('logoutButton');
            if (logoutButton) {
                logoutButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    handleLogout();
                });
            }
            
            // Logika Sidebar Toggle (Dipertahankan dari skrip Anda)
            const appContainer = document.getElementById('app-container');
            const sidebarToggler = document.getElementById('sidebar-toggler');
            const togglerIcon = sidebarToggler ? sidebarToggler.querySelector('i') : null;
    
            if (sidebarToggler && appContainer && togglerIcon) {
                sidebarToggler.addEventListener('click', function() {
                    appContainer.classList.toggle('minimized');
                    if (appContainer.classList.contains('minimized')) {
                        togglerIcon.classList.replace('bi-arrow-left-square-fill',
                            'bi-arrow-right-square-fill');
                    } else {
                        togglerIcon.classList.replace('bi-arrow-right-square-fill',
                            'bi-arrow-left-square-fill');
                    }
                });
            }
        });
    </script>
</body>

</html>
