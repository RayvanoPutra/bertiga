// ISI FILE public/js/tahun_ajaran.js

document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('sanctum_token');
    if (!token) {
        window.location.href = '/login';
        return;
    }

    // --- 1. REFERENSI ELEMEN & KONSTANTA ---
    const API_BASE_URL = '/api/master/tahun-ajaran';
    const tahunAjaranTableBody = document.querySelector('#tahunAjaranTable tbody');

    // **PERUBAHAN 1: Menyimpan data yang sudah diambil dari API secara global**
    let allTahunAjaranData = []; 
    
    // **PERUBAHAN 2: Elemen Filter**
    const statusFilter = document.getElementById('statusFilter');

    // Elemen Modal
    const tahunAjaranModal = document.getElementById('tahunAjaranModal');
    const tambahTahunAjaranBtn = document.getElementById('tambahTahunAjaranBtn');
    const closeTahunAjaranModalBtn = document.getElementById('closeTahunAjaranModal');
    const btnBatalTahunAjaran = document.getElementById('btnBatalTahunAjaran');
    const tahunAjaranForm = document.getElementById('tahunAjaranForm');
    const tahunAjaranModalTitle = document.getElementById('tahunAjaranModalTitle');
    const tahunAjaranIdInput = document.getElementById('tahunAjaranId');
    const inputTahunAjaran = document.getElementById('inputTahunAjaran');
    const inputStatus = document.getElementById('inputStatus');
    const tahunAjaranFormMessage = document.getElementById('tahunAjaranFormMessage');
    const logoutButton = document.getElementById('logoutButton');


    // --- 2. FUNGSI UTILITY & HANDLER ---

    function handleLogout() {
        localStorage.removeItem('sanctum_token');
        window.location.href = '/login';
    }

    function openModal(mode = 'create', data = {}) {
        tahunAjaranForm.reset();
        tahunAjaranFormMessage.style.display = 'none';

        if (mode === 'create') {
            tahunAjaranModalTitle.textContent = 'Tambah Tahun Ajaran Baru';
            tahunAjaranIdInput.value = '';
            inputStatus.value = 'nonaktif';
        } else if (mode === 'edit' && data.kode_tahun_ajaran) {
            tahunAjaranModalTitle.textContent = 'Edit Tahun Ajaran: ' + data.kode_tahun_ajaran;
            tahunAjaranIdInput.value = data.kode_tahun_ajaran;
            inputTahunAjaran.value = data.tahun_ajaran;
            inputStatus.value = data.status;
        }
        tahunAjaranModal.style.display = 'block';
    }

    function closeModal() {
        tahunAjaranModal.style.display = 'none';
        tahunAjaranFormMessage.style.display = 'none';
    }

    function showMessage(element, message, type) {
        element.innerHTML = message;
        element.className = `form-message ${type}`;
        element.style.display = 'block';
    }

    // **Fungsi Baru untuk Menerapkan Filter**
    function applyFilter() {
        const selectedStatus = statusFilter.value;

        const filteredList = allTahunAjaranData.filter(ta => {
            if (selectedStatus === 'all') {
                return true;
            }
            return ta.status === selectedStatus;
        });
        
        renderTable(filteredList);
    }


    // --- 3. FUNGSI RENDER TABEL ---
    // Diubah agar menerima taList yang sudah difilter
    function renderTable(taList) {
        tahunAjaranTableBody.innerHTML = '';
        if (taList.length === 0) {
            tahunAjaranTableBody.innerHTML =
                '<tr><td colspan="4" style="text-align: center; padding: 20px; font-style: italic;">Tidak ada data Tahun Ajaran yang ditemukan.</td></tr>';
            return;
        }

        taList.forEach((ta, index) => {
            const row = tahunAjaranTableBody.insertRow();
            // PERUBAHAN 3: Menggunakan index + 1 dari array yang difilter
            row.innerHTML = `
                <td>${index + 1}</td> 
                <td>${ta.tahun_ajaran}</td>
                <td><span class="badge status-${ta.status}">${ta.status.toUpperCase()}</span></td>
                <td>
                    <button class="btn-sm btn-warning edit-btn" data-id="${ta.kode_tahun_ajaran}" data-ta="${ta.tahun_ajaran}" data-status="${ta.status}">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button class="btn-sm btn-danger delete-btn" data-id="${ta.kode_tahun_ajaran}">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </td>
            `;
        });
        attachTAActionListeners();
    }


    // --- 4. FUNGSI FETCH DATA TAHUN AJARAN (READ) ---
    async function fetchTahunAjaran() {
        tahunAjaranTableBody.innerHTML =
            '<tr><td colspan="4" style="text-align: center; color: #555;">⏳ Memuat data...</td></tr>';

        try {
            const response = await fetch(API_BASE_URL, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (response.status === 401) throw new Error('Unauthorized');
            
            const taList = await response.json();
            
            // PERUBAHAN 4: Simpan data mentah ke variabel global
            allTahunAjaranData = taList.data || taList;
            
            // Tampilkan data dengan filter saat ini (default: 'all')
            applyFilter(); 

        } catch (error) {
            console.error('Gagal mengambil data Tahun Ajaran:', error);
            tahunAjaranTableBody.innerHTML =
                '<tr><td colspan="4" style="color: var(--danger-color); text-align: center;">❌ Gagal memuat data. Cek koneksi API.</td></tr>';
            if (error.message === 'Unauthorized') handleLogout();
        }
    }


    // --- 5. SUBMIT FORM KE API (CREATE/UPDATE) ---
    tahunAjaranForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        tahunAjaranFormMessage.style.display = 'none';

        const kodeTA = tahunAjaranIdInput.value;
        const method = kodeTA ? 'PUT' : 'POST';
        const url = kodeTA ? `${API_BASE_URL}/${kodeTA}` : API_BASE_URL;

        const formData = new FormData(tahunAjaranForm);
        const data = Object.fromEntries(formData.entries());

        if (!kodeTA) {
            // Ini hanya contoh, idealnya kode dibuat di backend
            data['kode_tahun_ajaran'] = data.tahun_ajaran.replace('/', '-'); 
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
                if (response.status === 422 && result.errors) {
                    errorMessage = Object.values(result.errors).flat().join('<br>');
                } else if (result.message) {
                    errorMessage = result.message;
                }
                showMessage(tahunAjaranFormMessage, errorMessage, 'error');
                return;
            }

            showMessage(tahunAjaranFormMessage, `✅ Tahun Ajaran berhasil ${kodeTA ? 'diperbarui' : 'ditambahkan'}!`,
                'success');
            
            // PERUBAHAN 5: Panggil ulang fetchTahunAjaran setelah sukses
            setTimeout(() => {
                closeModal();
                fetchTahunAjaran();
            }, 1500);

        } catch (error) {
            console.error('API Error:', error);
            showMessage(tahunAjaranFormMessage, 'Terjadi kesalahan jaringan atau server tidak merespons.', 'error');
        }
    });


    // --- 6. FUNGSI ACTION LISTENERS (Hapus & Edit) ---

    async function handleTADelete(kode) {
        if (!confirm(`❓ Apakah Anda yakin ingin menghapus Tahun Ajaran ${kode}? Aksi ini tidak dapat dibatalkan.`)) {
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

            alert('✅ Tahun Ajaran berhasil dihapus!');
            // PERUBAHAN 6: Panggil ulang fetchTahunAjaran setelah sukses hapus
            fetchTahunAjaran();

        } catch (error) {
            console.error('Delete API Error:', error);
            alert('Terjadi kesalahan jaringan saat menghapus.');
        }
    }

    function attachTAActionListeners() {
        // Edit Handler
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.removeEventListener('click', handleEditClick);
            button.addEventListener('click', handleEditClick);
        });

        function handleEditClick(e) {
            const kode = e.currentTarget.getAttribute('data-id');
            const ta = e.currentTarget.getAttribute('data-ta');
            const status = e.currentTarget.getAttribute('data-status');
            openModal('edit', {
                kode_tahun_ajaran: kode,
                tahun_ajaran: ta,
                status: status
            });
        }

        // Delete Handler
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.removeEventListener('click', handleDeleteClick);
            button.addEventListener('click', handleDeleteClick);
        });

        function handleDeleteClick(e) {
            const kode = e.currentTarget.getAttribute('data-id');
            handleTADelete(kode);
        }
    }


    // --- 7. INITIAL CALLS & EVENT LISTENERS LAINNYA ---

    fetchTahunAjaran();
    
    // **PERUBAHAN 7: Listener untuk Filter**
    statusFilter.addEventListener('change', applyFilter);


    tambahTahunAjaranBtn.addEventListener('click', () => openModal('create'));
    closeTahunAjaranModalBtn.addEventListener('click', closeModal);
    btnBatalTahunAjaran.addEventListener('click', closeModal);
    
    window.addEventListener('click', function(event) {
        if (event.target === tahunAjaranModal) {
            closeModal();
        }
    });

    // Logika Sidebar Toggle (TIDAK ADA PERUBAHAN)
    const appContainer = document.getElementById('app-container');
    const sidebarToggler = document.getElementById('sidebar-toggler');
    const togglerIcon = sidebarToggler ? sidebarToggler.querySelector('i') : null;

    if (sidebarToggler && appContainer && togglerIcon) {
        sidebarToggler.addEventListener('click', function() {
            appContainer.classList.toggle('minimized');
            if (appContainer.classList.contains('minimized')) {
                togglerIcon.classList.replace('bi-arrow-left-square-fill', 'bi-arrow-right-square-fill');
            } else {
                togglerIcon.classList.replace('bi-arrow-right-square-fill', 'bi-arrow-left-square-fill');
            }
        });
    }

    // Logika Logout (TIDAK ADA PERUBAHAN)
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