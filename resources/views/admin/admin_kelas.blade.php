@extends('layouts.admin')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Master Kelas</h1>
            <p class="text-sm text-gray-500">Kelola pembagian kelas siswa per tahun ajaran</p>
        </div>
        <button onclick="openModalKelas()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-bold shadow-lg transition-all flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Tambah Kelas
        </button>
    </div>

    <div class="bg-white p-5 rounded-xl shadow-sm mb-6 border border-gray-100">
    <form id="filterForm" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
        <div class="md:col-span-2">
            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Cari Kelas</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" id="filterSearch" onkeyup="fetchKelas()" placeholder="Ketik nama kelas..." 
                    class="w-full pl-9 p-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
            </div>
        </div>
        
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Jurusan</label>
            <select id="filter_jurusan" onchange="fetchKelas()" 
                class="w-full p-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition">
                <option value="">Semua Jurusan</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Tahun Ajaran</label>
            <select id="filter_ta" onchange="fetchKelas()" 
                class="w-full p-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition">
                <option value="">Semua TA</option>
            </select>
        </div>

        <div class="flex">
            <button type="button" onclick="resetFilter()" 
                class="w-full bg-gray-100 text-gray-600 font-bold py-2 rounded-lg hover:bg-gray-200 transition text-sm border border-gray-200 flex items-center justify-center gap-2">
                <span class="text-lg">🔄</span> Reset Filter
            </button>
        </div>
    </form>
</div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 uppercase">No</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Kode Kelas</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Nama Kelas</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">Jurusan</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 uppercase">Tahun Ajaran</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody id="tbodyKelas">
                </tbody>
        </table>
    </div>
</div>

<div id="modalKelas" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4 transition-all duration-300">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all scale-95 opacity-0" id="modalContainer">
        
        <div class="bg-indigo-700 p-5 flex justify-between items-center text-white">
            <div>
                <h3 id="modalTitle" class="text-xl font-bold">Tambah Kelas Baru</h3>
                <p class="text-xs text-indigo-100 opacity-80">Pastikan data jurusan dan TA sudah benar</p>
            </div>
            <button onclick="closeModalKelas()" class="text-white/70 hover:text-white transition text-2xl">✕</button>
        </div>

        <form id="formKelas" class="p-6 space-y-5">
            <input type="hidden" id="kelas_kode_lama">
            
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Nama Kelas</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </span>
                    <input type="text" id="input_nama_kelas" placeholder="Contoh: X RPL 1" 
                        class="w-full pl-9 p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition font-semibold text-gray-800" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Jurusan</label>
                    <select id="select_jurusan" class="w-full p-3 border border-gray-300 rounded-xl bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition font-medium" required>
                        <option value="">Pilih Jurusan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Tahun Ajaran</label>
                    <select id="select_ta" class="w-full p-3 border border-gray-300 rounded-xl bg-gray-50 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition font-medium" required>
                        <option value="">Pilih TA</option>
                    </select>
                </div>
            </div>

            <div id="errorKelas" class="hidden p-3 bg-red-50 text-red-600 text-xs rounded-xl border border-red-100 font-bold flex items-center gap-2 italic">
                <span>⚠️</span> <span id="errorText">Gagal menyimpan data</span>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeModalKelas()" 
                    class="px-5 py-2.5 text-gray-500 font-bold hover:bg-gray-100 rounded-xl transition text-sm">
                    Batal
                </button>
                <button type="submit" id="btnSimpanKelas" 
                    class="px-8 py-2.5 bg-indigo-600 text-white font-bold rounded-xl shadow-lg hover:bg-indigo-700 hover:shadow-indigo-200 transition transform active:scale-95 text-sm">
                    Simpan Kelas
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const authToken = localStorage.getItem('petugas_token');
    const BASE_URL = "/api";

    // 1. Inisialisasi Data
    document.addEventListener('DOMContentLoaded', () => {
        if (!authToken) {
            alert("Sesi berakhir, silakan login kembali.");
            window.location.href = "/login";
            return;
        }
        fetchDropdownData();
        fetchKelas();
    });

    // 2. Ambil data untuk Dropdown (Filter & Form)
    async function fetchDropdownData() {
        try {
            const headers = { 
                'Authorization': `Bearer ${authToken}`, 
                'Accept': 'application/json' 
            };
            const [resJurusan, resTA] = await Promise.all([
                fetch(`${BASE_URL}/master/jurusan`, { headers }),
                fetch(`${BASE_URL}/master/tahun-ajaran`, { headers })
            ]);

            const jurusans = await resJurusan.json();
            const tas = await resTA.json();

            const sJurusan = document.getElementById('select_jurusan');
            const fJurusan = document.getElementById('filter_jurusan');
            const sTA = document.getElementById('select_ta');
            const fTA = document.getElementById('filter_ta');

            sJurusan.innerHTML = '<option value="">Pilih Jurusan</option>';
            fJurusan.innerHTML = '<option value="">Semua Jurusan</option>';
            sTA.innerHTML = '<option value="">Pilih TA</option>';
            fTA.innerHTML = '<option value="">Semua Tahun Ajaran</option>';

            jurusans.forEach(j => {
                const opt = `<option value="${j.kode_jurusan}">${j.nama_jurusan}</option>`;
                sJurusan.insertAdjacentHTML('beforeend', opt);
                fJurusan.insertAdjacentHTML('beforeend', opt);
            });

            tas.forEach(t => {
                const opt = `<option value="${t.kode_tahun_ajaran}">${t.tahun_ajaran}</option>`;
                sTA.insertAdjacentHTML('beforeend', opt);
                fTA.insertAdjacentHTML('beforeend', opt);
            });
        } catch (e) {
            console.error("Gagal memuat dropdown:", e);
        }
    }

    // 3. Ambil dan Tampilkan Data Tabel
    async function fetchKelas() {
        const tbody = document.getElementById('tbodyKelas');
        const fJurusan = document.getElementById('filter_jurusan').value;
        const fTA = document.getElementById('filter_ta').value;

        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-400">Memuat data...</td></tr>';

        try {
            const response = await fetch(`${BASE_URL}/master/kelas`, {
                headers: { 
                    'Authorization': `Bearer ${authToken}`, 
                    'Accept': 'application/json' 
                }
            });

            if (response.status === 401) {
            localStorage.removeItem('petugas_token');
            window.location.href = "/admin/login";
            return;
        }

            let data = await response.json();

            // Filter data di sisi Frontend
            if (fJurusan) data = data.filter(i => i.kode_jurusan === fJurusan);
            if (fTA) data = data.filter(i => i.kode_tahun_ajaran === fTA);

            tbody.innerHTML = '';
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-400">Tidak ada data kelas.</td></tr>';
                return;
            }

            data.forEach((item, index) => {
                const row = `
                    <tr class="hover:bg-blue-50/30 transition-colors border-b border-gray-100 last:border-0">
                        <td class="px-6 py-4 text-center text-gray-400 font-mono text-xs">${index + 1}</td>
                        <td class="px-6 py-4 font-mono text-xs font-bold text-indigo-600">${item.kode_kelas}</td>
                        <td class="px-6 py-4 font-bold text-gray-800">${item.nama_kelas}</td>
                        <td class="px-6 py-4 text-gray-600 text-sm font-medium">${item.jurusan?.nama_jurusan || '-'}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 border border-indigo-200">
                                ${item.tahun_ajaran?.tahun_ajaran || '-'}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <button onclick="editKelas('${item.kode_kelas}', '${item.nama_kelas}', '${item.kode_jurusan}', '${item.kode_tahun_ajaran}')" class="p-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 active:scale-90 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" /></svg>
                                </button>
                                <button onclick="deleteKelas('${item.kode_kelas}')" class="p-1.5 bg-rose-500 text-white rounded-lg hover:bg-rose-600 active:scale-90 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', row);
            });
        } catch (e) {
            console.error("Detail Error:", e);
            alert("Gagal memuat data: " + e.message);
        }
    }

    // --- MODAL & CRUD ---
    function openModalKelas() {
    const modal = document.getElementById('modalKelas');
    const container = document.getElementById('modalContainer');
    
    modal.classList.remove('hidden');
    // Beri sedikit delay agar transisi CSS terbaca
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        container.classList.remove('scale-95', 'opacity-0');
        container.classList.add('scale-100', 'opacity-100');
    }, 10);

    document.getElementById('formKelas').reset();
    document.getElementById('errorKelas').classList.add('hidden');
    document.getElementById('kelas_kode_lama').value = '';
    document.getElementById('modalTitle').innerText = "Tambah Kelas Baru";
}

function closeModalKelas() {
    const modal = document.getElementById('modalKelas');
    const container = document.getElementById('modalContainer');

    container.classList.remove('scale-100', 'opacity-100');
    container.classList.add('scale-95', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

    document.getElementById('formKelas').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btnSimpanKelas');
        const kodeLama = document.getElementById('kelas_kode_lama').value;
        const errorMsg = document.getElementById('errorKelas');
        
        btn.disabled = true; btn.innerText = "Menyimpan...";
        errorMsg.classList.add('hidden');

        const payload = {
            nama_kelas: document.getElementById('input_nama_kelas').value,
            kode_jurusan: document.getElementById('select_jurusan').value,
            kode_tahun_ajaran: document.getElementById('select_ta').value
        };

        const url = kodeLama ? `${BASE_URL}/master/kelas/${kodeLama}` : `${BASE_URL}/master/kelas`;
        const method = kodeLama ? 'PUT' : 'POST';

        try {
            const res = await fetch(url, {
                method: method,
                headers: { 
                    'Authorization': `Bearer ${authToken}`, 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json' 
                },
                body: JSON.stringify(payload)
            });

            const result = await res.json();
            if (res.ok) {
                alert("✅ Berhasil!\nKelas ditambahkan dengan Kode: " + result.data.kode_kelas);
                closeModalKelas();
                fetchKelas();
            } else {
                errorMsg.innerText = result.message || "Gagal menyimpan data";
                errorMsg.classList.remove('hidden');
            }
        } catch (e) {
            alert("Koneksi gagal");
        } finally {
            btn.disabled = false; btn.innerText = "Simpan";
        }
    });

    function editKelas(kode, nama, jurusan, ta) {
        openModalKelas();
        document.getElementById('modalTitle').innerText = "Edit Kelas";
        document.getElementById('kelas_kode_lama').value = kode;
        document.getElementById('input_nama_kelas').value = nama;
        document.getElementById('select_jurusan').value = jurusan;
        document.getElementById('select_ta').value = ta;
    }

    async function deleteKelas(kode) {
    if (!confirm(`Hapus kelas ${kode}? Peringatan: Data tidak bisa dihapus jika sudah ada nasabah di dalamnya.`)) return;
    try {
        const res = await fetch(`${BASE_URL}/master/kelas/${kode}`, {
            method: 'DELETE',
            headers: { 
                'Authorization': `Bearer ${authToken}`,
                'Accept': 'application/json' 
            }
        });
        
        const result = await res.json(); // Ambil pesan error dari server

        if (res.ok) {
            fetchKelas();
        } else {
            // Tampilkan pesan error spesifik (misal: "Integrity constraint violation")
            alert("Gagal menghapus: " + (result.message || "Data sedang digunakan."));
        }
    } catch (e) {
        alert("Terjadi kesalahan jaringan.");
    }
}
    function resetFilter() {
        document.getElementById('filter_jurusan').value = '';
        document.getElementById('filter_ta').value = '';
        fetchKelas();
    }
</script>
@endpush