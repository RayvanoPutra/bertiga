@extends('layouts.admin')

@section('title', 'Tahun Ajaran')
@section('header_title', 'Master Tahun Ajaran')

@section('content')
    <div class="flex justify-between items-center mb-6 px-6 pt-6">
        <h2 class="text-2xl font-bold text-gray-800">Daftar Tahun Ajaran</h2>
        <button onclick="openModalTA()" class="bg-blue-600 text-white px-5 py-2 rounded-lg font-bold hover:bg-blue-700 shadow flex items-center gap-2 transition transform hover:-translate-y-0.5">
            <span>+</span> Tambah Tahun Ajaran
        </button>
    </div>

    <div class="px-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase text-[10px] font-bold">
                    <tr>
                        <th class="px-6 py-4 w-20 text-center">No</th>
                        <th class="px-6 py-4">Tahun Ajaran</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="taList" class="divide-y divide-gray-100 text-sm">
                    </tbody>
            </table>
        </div>
    </div>

    <div id="modalTA" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50 transition-all duration-300">
        <div class="modal-overlay absolute w-full h-full bg-black/50" onclick="closeModalTA()"></div>
        <div class="modal-container bg-white w-full max-w-md mx-auto rounded-xl shadow-2xl z-50 overflow-y-auto transform transition-all scale-95 opacity-0">
            <div class="py-4 text-left px-6">
                <div class="flex justify-between items-center pb-3 border-b mb-4">
                    <p class="text-xl font-bold text-gray-800" id="modalTitle">Tahun Ajaran</p>
                    <button onclick="closeModalTA()" class="text-gray-400 hover:text-red-500 font-bold">✕</button>
                </div>
                <form id="formTA" class="space-y-4">
                    <input type="hidden" id="ta_kode_lama">
                    
                    <div id="containerErrorTA" class="hidden p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p id="errorTA" class="text-red-600 font-bold text-[10px] italic uppercase text-center"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nama Tahun Ajaran</label>
                        <input type="text" id="input_tahun_ajaran" placeholder="Contoh: 2024/2025" class="w-full p-3 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Status</label>
                        <select id="input_status" class="w-full p-3 border border-gray-300 rounded-lg outline-none bg-white font-semibold">
                            <option value="nonaktif">Tidak Aktif</option>
                            <option value="aktif">Aktif</option>
                        </select>
                    </div>
                    <div class="flex justify-end pt-4 space-x-2">
                        <button type="button" onclick="closeModalTA()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold">Batal</button>
                        <button type="submit" id="btnSimpanTA" class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition shadow-md">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const BASE_URL = "{{ url('/api') }}";
    const authToken = localStorage.getItem('petugas_token');

    // Proteksi Halaman
    if (!authToken) window.location.href = "{{ url('/admin/login') }}";

    // Fungsi Buka/Tutup Modal dengan Animasi
    function toggleModal(modalID) {
        const modal = document.getElementById(modalID);
        const content = modal.querySelector('.modal-container');
        if (modal.classList.contains('opacity-0')) {
            modal.classList.remove('opacity-0', 'pointer-events-none');
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        } else {
            modal.classList.add('opacity-0', 'pointer-events-none');
            content.classList.add('scale-95', 'opacity-0');
            content.classList.remove('scale-100', 'opacity-100');
        }
    }

    function openModalTA() {
        document.getElementById('formTA').reset();
        document.getElementById('ta_kode_lama').value = '';
        document.getElementById('modalTitle').innerText = "Tambah Tahun Ajaran";
        document.getElementById('containerErrorTA').classList.add('hidden');
        toggleModal('modalTA');
    }

    function closeModalTA() { toggleModal('modalTA'); }

    // Mengambil Data dari API
    async function fetchTA() {
        const tbody = document.getElementById('taList');
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-10 text-gray-400 italic">Memuat data...</td></tr>';
        try {
            const response = await fetch(`${BASE_URL}/master/tahun-ajaran`, {
                headers: { 'Authorization': `Bearer ${authToken}` }
            });
            const data = await response.json();
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-10 text-gray-400">Belum ada data tahun ajaran.</td></tr>';
                return;
            }

            data.forEach((item, index) => {
                // Tampilan Badge Status
                const statusBadge = item.status === 'aktif' 
                    ? `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                        Aktif</span>`
                    : `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-gray-400" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                        Nonaktif</span>`;

                // TAMPILAN TOMBOL AKSI (Tetap Sesuai Desain Awal)
                const row = `
                    <tr class="hover:bg-blue-50/30 transition-colors border-b border-gray-100 last:border-0">
                        <td class="px-6 py-4 text-center text-gray-400 font-mono text-xs">${index + 1}</td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-800">${item.tahun_ajaran}</div>
                            <div class="text-[10px] text-gray-400 uppercase tracking-widest">${item.kode_tahun_ajaran}</div>
                        </td>
                        <td class="px-6 py-4">${statusBadge}</td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center gap-3">
                                <button onclick="editTA('${item.kode_tahun_ajaran}', '${item.tahun_ajaran}', '${item.status}')" 
                                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-lg shadow-sm hover:bg-indigo-700 active:scale-95 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                    Edit
                                </button>
                                <button onclick="deleteTA('${item.kode_tahun_ajaran}')" 
                                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-rose-500 text-white text-xs font-bold rounded-lg shadow-sm hover:bg-rose-600 active:scale-95 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>`;
                tbody.insertAdjacentHTML('beforeend', row);
            });
        } catch (error) { console.error(error); }
    }

    // Handle Simpan/Update
    document.getElementById('formTA').addEventListener('submit', async (e) => {
        e.preventDefault();
        const kodeLama = document.getElementById('ta_kode_lama').value;
        const tahunAjaran = document.getElementById('input_tahun_ajaran').value;
        const statusVal = document.getElementById('input_status').value;
        const containerError = document.getElementById('containerErrorTA');
        const errorMsg = document.getElementById('errorTA');
        const btn = document.getElementById('btnSimpanTA');

        // Loading State
        btn.disabled = true; 
        btn.innerText = "Memproses...";
        containerError.classList.add('hidden');

        const method = kodeLama ? 'PUT' : 'POST';
        const url = kodeLama ? `${BASE_URL}/master/tahun-ajaran/${kodeLama}` : `${BASE_URL}/master/tahun-ajaran`;

        // Logic Auto-Generate Kode (Contoh: 2024/2025 -> TA-2425)
        let generatedKode = kodeLama;
        if (!kodeLama) {
            const part = tahunAjaran.split('/');
            const thnAwal = part[0].substring(2);
            const thnAkhir = part[1] ? part[1].substring(2) : '';
            generatedKode = `TA-${thnAwal}${thnAkhir}`;
        }

        try {
            const response = await fetch(url, {
                method: method,
                headers: { 
                    'Authorization': `Bearer ${authToken}`, 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json' 
                },
                body: JSON.stringify({ 
                    kode_tahun_ajaran: generatedKode, 
                    tahun_ajaran: tahunAjaran, 
                    status: statusVal 
                })
            });

            const result = await response.json();

            // Handle Error Validasi (Misal: Tahun Ajaran Duplikat)
            if (response.status === 422) {
                containerError.classList.remove('hidden');
                errorMsg.innerText = result.errors ? Object.values(result.errors)[0][0] : result.message;
            } else if (response.ok) {
                // Notifikasi Popup Berhasil (SweetAlert2)
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: kodeLama ? 'Data berhasil diperbarui' : 'Data berhasil ditambahkan',
                    timer: 1500,
                    showConfirmButton: false
                });
                closeModalTA(); 
                fetchTA();
            } else {
                Swal.fire('Gagal!', result.message || 'Terjadi kesalahan', 'error');
            }
        } catch (error) { 
            Swal.fire('Error!', 'Koneksi ke server bermasalah', 'error');
        } finally { 
            btn.disabled = false; 
            btn.innerText = "Simpan"; 
        }
    });

    // Mengisi Form saat Tombol Edit diklik
    function editTA(kode, val, status) {
        document.getElementById('ta_kode_lama').value = kode;
        document.getElementById('input_tahun_ajaran').value = val;
        document.getElementById('input_status').value = status;
        document.getElementById('modalTitle').innerText = "Edit Tahun Ajaran";
        document.getElementById('containerErrorTA').classList.add('hidden');
        toggleModal('modalTA');
    }

    // Fungsi Hapus dengan Konfirmasi Bagus
    async function deleteTA(kode) {
        const confirm = await Swal.fire({
            title: 'Hapus data?',
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        });

        if (!confirm.isConfirmed) return;

        try {
            const response = await fetch(`${BASE_URL}/master/tahun-ajaran/${kode}`, {
                method: 'DELETE', 
                headers: { 'Authorization': `Bearer ${authToken}` }
            });
            if (response.ok) {
                Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
                fetchTA();
            } else {
                Swal.fire('Gagal!', 'Gagal menghapus data.', 'error');
            }
        } catch (error) { 
            Swal.fire('Error!', 'Koneksi bermasalah', 'error'); 
        }
    }

    // Jalankan fungsi fetch saat halaman dimuat
    fetchTA();
</script>
@endpush