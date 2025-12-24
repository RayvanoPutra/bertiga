@extends('layouts.admin')

@section('title', 'Master Jurusan')
@section('header_title', 'Master Jurusan')

@section('content')
<div class="p-6">
    <div class="bg-white p-5 rounded-xl shadow-sm mb-6 border border-gray-100">
        <form id="filterForm" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Cari Jurusan</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" id="filterSearch" onkeyup="fetchJurusan()" placeholder="Ketik nama atau kode jurusan..." 
                        class="w-full pl-9 p-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                </div>
            </div>
            <button type="button" onclick="resetFilter()" class="w-full bg-gray-100 text-gray-600 font-bold py-2 rounded-lg hover:bg-gray-200 transition text-sm border border-gray-200 flex items-center justify-center gap-2">
                🔄 Reset
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="text-lg font-bold text-gray-800">Daftar Jurusan</h3>
            <button onclick="openModalJurusan()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-indigo-700 shadow-md flex items-center gap-2 transition transform active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Tambah Jurusan
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase text-[10px] font-bold tracking-widest">
                    <tr>
                        <th class="px-6 py-4 text-center w-16">No</th>
                        <th class="px-6 py-4">Kode Jurusan</th>
                        <th class="px-6 py-4">Nama Jurusan</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyJurusan" class="divide-y divide-gray-100 text-sm">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalJurusan" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4 transition-all">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all scale-95 opacity-0" id="modalContainer">
        <div class="bg-indigo-700 p-5 flex justify-between items-center text-white">
            <div>
                <h3 id="modalTitle" class="text-xl font-bold">Tambah Jurusan</h3>
                <p class="text-xs text-indigo-100 opacity-80">Masukkan informasi program keahlian</p>
            </div>
            <button onclick="closeModalJurusan()" class="text-white/70 hover:text-white text-2xl">✕</button>
        </div>

        <form id="formJurusan" class="p-6 space-y-5">
            <input type="hidden" id="kode_jurusan_lama">
            
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kode Jurusan</label>
                <input type="text" id="input_kode_jurusan" placeholder="Contoh: TKJ" 
                    class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none font-mono font-bold uppercase" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Jurusan</label>
                <input type="text" id="input_nama_jurusan" placeholder="Contoh: Teknik Komputer dan Jaringan" 
                    class="w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none font-semibold" required>
            </div>

            <div id="errorJurusan" class="hidden p-3 bg-red-50 text-red-600 text-xs rounded-xl border border-red-100 font-bold"></div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeModalJurusan()" class="px-5 py-2.5 text-gray-500 font-bold hover:bg-gray-100 rounded-xl transition">Batal</button>
                <button type="submit" id="btnSimpanJurusan" class="px-8 py-2.5 bg-indigo-600 text-white font-bold rounded-xl shadow-lg hover:bg-indigo-700 transition transform active:scale-95">Simpan Jurusan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const authToken = localStorage.getItem('petugas_token');
    const BASE_URL = "/api";

    document.addEventListener('DOMContentLoaded', () => {
        if (!authToken) window.location.href = "/admin/login";
        fetchJurusan();
    });

    async function fetchJurusan() {
    const tbody = document.getElementById('tbodyJurusan');
    const fSearch = document.getElementById('filterSearch').value.toLowerCase();
    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-10 text-gray-400 italic">Memuat data...</td></tr>';

    try {
        const res = await fetch(`${BASE_URL}/master/jurusan`, {
            headers: { 
                'Authorization': `Bearer ${authToken}`, 
                'Accept': 'application/json' 
            }
        });

        // --- TAMBAHKAN PENGECEKAN INI DI SINI ---
        if (res.status === 401) {
            alert("Sesi Anda telah berakhir. Silakan login kembali.");
            localStorage.removeItem('petugas_token'); // Hapus token yang sudah basi
            window.location.href = "/admin/login";    // Ganti ke URL login Anda
            return; // Hentikan eksekusi kode di bawahnya
        }

        if (!res.ok) throw new Error("Gagal mengambil data");
        // ----------------------------------------

        let data = await res.json();

        if (fSearch) {
            data = data.filter(i => i.nama_jurusan.toLowerCase().includes(fSearch) || i.kode_jurusan.toLowerCase().includes(fSearch));
        }

        tbody.innerHTML = '';
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-10 text-gray-400">Data jurusan kosong.</td></tr>';
            return;
        }

        data.forEach((item, index) => {
            const row = `
                <tr class="hover:bg-blue-50/30 transition border-b border-gray-100 last:border-0">
                    <td class="px-6 py-4 text-center text-gray-400 font-mono text-xs">${index + 1}</td>
                    <td class="px-6 py-4"><span class="bg-indigo-50 text-indigo-700 px-2 py-1 rounded font-bold text-xs uppercase border border-indigo-100">${item.kode_jurusan}</span></td>
                    <td class="px-6 py-4 font-bold text-gray-800">${item.nama_jurusan}</td>
                    <td class="px-6 py-4">
                        <div class="flex justify-center gap-2">
                            <button onclick="editJurusan('${item.kode_jurusan}', '${item.nama_jurusan}')" class="p-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition shadow-sm border border-blue-100">✏️</button>
                            <button onclick="deleteJurusan('${item.kode_jurusan}')" class="p-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition shadow-sm border border-red-100">🗑️</button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });
    } catch (e) { 
        console.error(e);
        // Jika koneksi benar-benar mati/server mati
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-10 text-red-400 font-bold">Koneksi ke server gagal!</td></tr>';
    }
}

    // Modal Helpers
    function openModalJurusan() {
        const modal = document.getElementById('modalJurusan');
        const container = document.getElementById('modalContainer');
        modal.classList.remove('hidden');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
        document.getElementById('formJurusan').reset();
        document.getElementById('kode_jurusan_lama').value = '';
        document.getElementById('modalTitle').innerText = "Tambah Jurusan";
    }

    function closeModalJurusan() {
        const container = document.getElementById('modalContainer');
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        setTimeout(() => document.getElementById('modalJurusan').classList.add('hidden'), 300);
    }

    document.getElementById('formJurusan').addEventListener('submit', async (e) => {
        e.preventDefault();
        const kodeLama = document.getElementById('kode_jurusan_lama').value;
        const url = kodeLama ? `${BASE_URL}/master/jurusan/${kodeLama}` : `${BASE_URL}/master/jurusan`;
        const method = kodeLama ? 'PUT' : 'POST';

        const payload = {
            kode_jurusan: document.getElementById('input_kode_jurusan').value,
            nama_jurusan: document.getElementById('input_nama_jurusan').value
        };

        try {
            const res = await fetch(url, {
                method: method,
                headers: { 'Authorization': `Bearer ${authToken}`, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            if (res.ok) { closeModalJurusan(); fetchJurusan(); }
            else { alert("Gagal menyimpan data."); }
        } catch (e) { alert("Error koneksi."); }
    });

    function editJurusan(kode, nama) {
        openModalJurusan();
        document.getElementById('modalTitle').innerText = "Edit Jurusan";
        document.getElementById('kode_jurusan_lama').value = kode;
        document.getElementById('input_kode_jurusan').value = kode;
        document.getElementById('input_nama_jurusan').value = nama;
    }

    async function deleteJurusan(kode) {
        if (!confirm(`Hapus jurusan ${kode}? Semua kelas di bawahnya mungkin akan bermasalah.`)) return;
        const res = await fetch(`${BASE_URL}/master/jurusan/${kode}`, {
            method: 'DELETE', headers: { 'Authorization': `Bearer ${authToken}` }
        });
        if (res.ok) fetchJurusan(); else alert("Gagal menghapus.");
    }

    function resetFilter() {
        document.getElementById('filterSearch').value = '';
        fetchJurusan();
    }
</script>
@endpush