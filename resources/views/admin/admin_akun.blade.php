@extends('layouts.admin')
@section('title', 'Manajemen Akun')
@section('header_title', 'Pengaturan Akun Petugas')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Daftar Petugas</h2>
        <button onclick="openModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-bold shadow hover:bg-indigo-700 transition flex items-center gap-2">
            <span>➕</span> Tambah Petugas
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="px-6 py-4 text-center">No</th>
                    <th class="px-6 py-4">Kode Petugas</th>
                    <th class="px-6 py-4">Nama Petugas</th>
                    <th class="px-6 py-4">Username</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="tbodyPetugas" class="divide-y divide-gray-100 text-sm">
                </tbody>
        </table>
    </div>
</div>

<div id="modalPetugas" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4 transition-all duration-300">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 opacity-0 transition-all" id="modalContainer">
        <div class="bg-indigo-700 p-5 text-white flex justify-between items-center">
            <h3 class="font-bold">Tambah Petugas Baru</h3>
            <button onclick="closeModal()" class="text-2xl hover:text-gray-200">✕</button>
        </div>
        <form id="formPetugas" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Petugas</label>
                <input type="text" id="nama_petugas" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none font-semibold" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Username</label>
                <input type="text" id="username" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none font-semibold" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Password</label>
                <input type="password" id="password" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Role / Jabatan</label>
                <select id="role" class="w-full p-2.5 border border-gray-300 rounded-lg bg-gray-50 outline-none font-medium" required>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Super Admin</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-500 font-bold hover:bg-gray-100 rounded-lg transition">Batal</button>
                <button type="submit" id="btnSimpanPetugas" class="px-6 py-2 bg-indigo-600 text-white font-bold rounded-lg shadow-lg hover:bg-indigo-700 transition active:scale-95">Simpan Petugas</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const authToken = localStorage.getItem('petugas_token');
    const BASE_URL = "/api";

    async function fetchPetugas() {
        const tbody = document.getElementById('tbodyPetugas');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-6 text-gray-400 italic">Memuat data...</td></tr>';
        
        try {
            const res = await fetch(`${BASE_URL}/master/petugas`, {
                headers: { 'Authorization': `Bearer ${authToken}` }
            });
            const data = await res.json();
            tbody.innerHTML = '';

            data.forEach((p, i) => {
                const roleBadge = p.role === 'superadmin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700';
                const row = `
                    <tr class="hover:bg-indigo-50/30 transition border-b border-gray-100 last:border-0">
                        <td class="px-6 py-4 text-center text-gray-400 font-mono text-xs">${i + 1}</td>
                        <td class="px-6 py-4 font-mono text-xs font-bold text-indigo-600">${p.kode_petugas}</td>
                        <td class="px-6 py-4 font-bold text-gray-800">${p.nama_petugas}</td>
                        <td class="px-6 py-4 text-gray-500 font-mono">${p.username}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-[10px] font-black uppercase ${roleBadge}">${p.role.replace('_', ' ')}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center">
                                <button onclick="deletePetugas('${p.kode_petugas}')" 
                                    class="p-1.5 bg-rose-500 text-white rounded-lg hover:bg-rose-600 active:scale-90 transition shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                tbody.insertAdjacentHTML('beforeend', row);
            });
        } catch (e) {
            console.error("Gagal memuat data petugas");
        }
    }

    document.getElementById('formPetugas').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btnSimpanPetugas');
        btn.disabled = true;
        btn.innerText = "Memproses...";

        const payload = {
            nama_petugas: document.getElementById('nama_petugas').value,
            username: document.getElementById('username').value,
            password: document.getElementById('password').value,
            role: document.getElementById('role').value
        };

        try {
            const res = await fetch(`${BASE_URL}/master/petugas`, {
                method: 'POST',
                headers: { 
                    'Authorization': `Bearer ${authToken}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const result = await res.json();

            if (res.ok) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: `Petugas baru ditambahkan. Kode: ${result.data.kode_petugas}`, timer: 2000, showConfirmButton: false });
                closeModal();
                fetchPetugas();
            } else {
                let errorMsg = result.errors ? Object.values(result.errors).flat().join("\n") : result.message;
                Swal.fire('Gagal!', errorMsg, 'error');
            }
        } catch (e) {
            Swal.fire('Error!', 'Gagal terhubung ke server.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = "Simpan Petugas";
        }
    });

    async function deletePetugas(kode) {
        const confirm = await Swal.fire({
            title: 'Hapus Petugas?',
            text: "Data petugas akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#f43f5e',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        });

        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch(`${BASE_URL}/master/petugas/${kode}`, {
                method: 'DELETE',
                headers: { 'Authorization': `Bearer ${authToken}` }
            });
            if (res.ok) {
                Swal.fire('Terhapus!', 'Petugas telah dihapus.', 'success');
                fetchPetugas();
            }
        } catch (e) {
            Swal.fire('Error!', 'Gagal menghapus data.', 'error');
        }
    }

    function openModal() {
        const modal = document.getElementById('modalPetugas');
        const container = document.getElementById('modalContainer');
        modal.classList.remove('hidden');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
        document.getElementById('formPetugas').reset();
    }

    function closeModal() {
        const modal = document.getElementById('modalPetugas');
        const container = document.getElementById('modalContainer');
        container.classList.add('scale-95', 'opacity-0');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    fetchPetugas();
</script>
@endpush