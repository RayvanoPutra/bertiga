// ISI FILE public/js/jurusan.js

document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('sanctum_token');
    if (!token) {
        window.location.href = '/login';
        return;
    }

    // --- 1. REFERENSI ELEMEN & KONSTANTA ---
    const API_BASE_URL = '/api/master/jurusan';
    const jurusanTableBody = document.querySelector('#jurusanTable tbody');

    // Elemen Modal Jurusan
    const jurusanModal = document.getElementById('jurusanModal');
    const tambahJurusanBtn = document.getElementById('tambahJurusanBtn');
    const closeJurusanModalBtn = document.getElementById('closeJurusanModal');
    const btnBatalJurusan = document.getElementById('btnBatalJurusan');
    const jurusanForm = document.getElementById('jurusanForm');
    const jurusanModalTitle = document.getElementById('jurusanModalTitle');
    const jurusanIdInput = document.getElementById('jurusanId');
    const inputNamaJurusan = document.getElementById('inputNamaJurusan');
    const jurusanFormMessage = document.getElementById('jurusanFormMessage');
    const modalJurusanTahunAjaran = document.getElementById('modalJurusanTahunAjaran');
    const logoutButton = document.getElementById('logoutButton');


    // --- FUNGSI UTILITY ---
    function handleLogout() {
        localStorage.removeItem('sanctum_token');
        window.location.href = '/login';
    }

    function openModal(mode = 'create', data = {}) {
        jurusanForm.reset();
        jurusanFormMessage.style.display = 'none';

        if (mode === 'create') {
            jurusanModalTitle.textContent = 'Tambah Jurusan Baru';
            jurusanIdInput.value = '';
        } else if (mode === 'edit' && data.kode_jurusan) {
            jurusanModalTitle.textContent = 'Edit Jurusan: ' + data.kode_jurusan;
            jurusanIdInput.value = data.kode_jurusan;
            inputNamaJurusan.value = data.nama_jurusan;
            modalJurusanTahunAjaran.value = data.kode_tahun_ajaran;
        }
        jurusanModal.style.display = 'block';
    }

    function closeModal() {
        jurusanModal.style.display = 'none';
        jurusanFormMessage.style.display = 'none';
    }

    function showMessage(element, message, type) {
        element.innerHTML = message;
        element.className = `form-message ${type}`;
        element.style.display = 'block';
    }

    // --- FUNGSI RENDER TABEL JURUSAN ---
    function renderJurusanTable(jurusanList) {
        jurusanTableBody.innerHTML = '';
        if (jurusanList.length === 0) {
            jurusanTableBody.innerHTML =
                '<tr><td colspan="3" style="text-align: center; padding: 20px; font-style: italic;">Tidak ada data Jurusan yang ditemukan.</td></tr>';
            return;
        }

        jurusanList.forEach((jurusan, index) => {
            const row = jurusanTableBody.insertRow();
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${jurusan.nama_jurusan} (${jurusan.tahun_ajaran ? jurusan.tahun_ajaran.tahun_ajaran : '-'})</td>
                <td>
                    <button class="btn-sm btn-warning edit-btn" data-id="${jurusan.kode_jurusan}" data-name="${jurusan.nama_jurusan}" data-ta-code="${jurusan.kode_tahun_ajaran}">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button class="btn-sm btn-danger delete-btn" data-id="${jurusan.kode_jurusan}">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </td>
            `;
        });
        attachJurusanActionListeners(); // <-- Panggil listener setelah render
    }


    // --- FUNGSI FETCH DATA JURUSAN (READ) ---
    async function fetchJurusan() {
        jurusanTableBody.innerHTML =
            '<tr><td colspan="3" style="text-align: center; color: #555;">⏳ Memuat data...</td></tr>';

        try {
            const response = await fetch(API_BASE_URL, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (response.status === 401) throw new Error('Unauthorized');
            
            const result = await response.json();
            renderJurusanTable(result.data || result); 

        } catch (error) {
            console.error('Gagal mengambil data Jurusan:', error);
            jurusanTableBody.innerHTML =
                '<tr><td colspan="3" style="color: var(--danger-color); text-align: center;">❌ Gagal memuat data. Cek koneksi API.</td></tr>';
            if (error.message === 'Unauthorized') handleLogout();
        }
    }
    
    // --- FUNGSI FETCH TAHUN AJARAN UNTUK SELECT MODAL ---
    async function fetchTahunAjaranForSelect() {
        try {
            const response = await fetch('/api/master/tahun-ajaran', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();
            const taList = result.data || result;
            
            modalJurusanTahunAjaran.innerHTML = '<option value="">Pilih Tahun Ajaran</option>';
            taList.forEach(ta => {
                const option = document.createElement('option');
                option.value = ta.kode_tahun_ajaran;
                option.textContent = `${ta.tahun_ajaran} (${ta.status.toUpperCase()})`;
                modalJurusanTahunAjaran.appendChild(option);
            });
        } catch (error) {
             console.error('Gagal memuat pilihan Tahun Ajaran:', error);
        }
    }


    // --- FUNGSI SUBMIT FORM KE API (CREATE/UPDATE) ---
    jurusanForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        jurusanFormMessage.style.display = 'none';

        const kodeJurusan = jurusanIdInput.value;
        const method = kodeJurusan ? 'PUT' : 'POST';
        const url = kodeJurusan ? `${API_BASE_URL}/${kodeJurusan}` : API_BASE_URL;

        const formData = new FormData(jurusanForm);
        const data = Object.fromEntries(formData.entries());

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
                if (response.status === 422 && result.errors) {
                    errorMessage = Object.values(result.errors).flat().join('<br>');
                } else if (result.message) {
                    errorMessage = result.message;
                }
                showMessage(jurusanFormMessage, errorMessage, 'error');
                return;
            }

            showMessage(jurusanFormMessage, `✅ Jurusan berhasil ${kodeJurusan ? 'diperbarui' : 'ditambahkan'}!`, 'success');
            setTimeout(() => {
                closeModal();
                fetchJurusan(); // Refresh data
            }, 1500);

        } catch (error) {
            console.error('API Error:', error);
            showMessage(jurusanFormMessage, 'Terjadi kesalahan jaringan atau server tidak merespons.', 'error');
        }
    });

    // --- FUNGSI HAPUS (DELETE) ---
    async function handleJurusanDelete(kode) {
        if (!confirm(`❓ Apakah Anda yakin ingin menghapus Jurusan ${kode}? Aksi ini tidak dapat dibatalkan.`)) {
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}/${kode}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                let errorMessage = result.message || 'Gagal menghapus data.';
                alert(`❌ Gagal: ${errorMessage}`);
                return;
            }

            alert('✅ Jurusan berhasil dihapus!');
            fetchJurusan();

        } catch (error) {
            console.error('Delete API Error:', error);
            alert('Terjadi kesalahan jaringan saat menghapus.');
        }
    }


    // --- FUNGSI ACTION LISTENERS (Hapus & Edit) ---
    function attachJurusanActionListeners() {
        // Edit Handler
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.removeEventListener('click', handleEditClick);
            button.addEventListener('click', handleEditClick);
        });

        function handleEditClick(e) {
            const kode = e.currentTarget.getAttribute('data-id');
            const nama = e.currentTarget.getAttribute('data-name');
            const taCode = e.currentTarget.getAttribute('data-ta-code');
            
            openModal('edit', {
                kode_jurusan: kode,
                nama_jurusan: nama,
                kode_tahun_ajaran: taCode
            });
        }

        // Delete Handler
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.removeEventListener('click', handleDeleteClick);
            button.addEventListener('click', handleDeleteClick);
        });

        function handleDeleteClick(e) {
            const kode = e.currentTarget.getAttribute('data-id');
            handleJurusanDelete(kode);
        }
    }


    // --- INITIAL CALLS & EVENT LISTENERS LAINNYA ---
    fetchJurusan();
    fetchTahunAjaranForSelect();

    tambahJurusanBtn.addEventListener('click', () => openModal('create'));
    closeJurusanModalBtn.addEventListener('click', closeModal);
    btnBatalJurusan.addEventListener('click', closeModal);

    // Event listener untuk menutup modal saat klik di luar area
    window.addEventListener('click', function(event) {
        if (event.target === jurusanModal) {
            closeModal();
        }
    });

    // Logika Logout
    if (logoutButton) {
        logoutButton.addEventListener('click', async function(e) {
            e.preventDefault();
            if (confirm('Anda yakin ingin keluar?')) {
                try {
                    await fetch('/api/logout', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`
                        }
                    });
                } catch (err) {
                    console.error('Logout API call failed, but clearing local storage.');
                } finally {
                    handleLogout();
                }
            }
        });
    }
});