@extends('layouts.admin')
@section('title', 'Manajemen Akun')
@section('header_title', 'Pengaturan Akun Petugas')

@section('content')
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">Daftar Petugas</h2>
            <button onclick="openModal()"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-bold shadow hover:bg-indigo-700 transition flex items-center gap-2">
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

    <div id="modalPetugas" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 transition-all">
            <div class="bg-indigo-700 p-5 text-white flex justify-between items-center">
                <h3 class="font-bold">Tambah Petugas Baru</h3>
                <button onclick="closeModal()" class="text-2xl">✕</button>
            </div>
            <form id="formPetugas" class="p-6 space-y-4">
                <!-- <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kode Petugas</label>
                                    <input type="text" id="kode_petugas" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" required>
                                </div> -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Petugas</label>
                    <input type="text" id="nama_petugas"
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
                        required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Username</label>
                    <input type="text" id="username"
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
                        required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Password</label>
                    <input type="password" id="password"
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"
                        required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Role / Jabatan</label>
                    <select id="role" class="w-full p-2.5 border border-gray-300 rounded-lg bg-gray-50 outline-none"
                        required>
                        <option value="admin">Admin</option>
                        <option value="superadmin">Super Admin</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-500 font-bold">Batal</button>
                    <button type="submit"
                        class="px-6 py-2 bg-indigo-600 text-white font-bold rounded-lg shadow-lg hover:bg-indigo-700">Simpan
                        Petugas</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
    const authToken = localStorage.getItem('petugas_token');
    const BASE_URL = "/api";

    // 1. FUNGSI AMBIL DATA (FETCH)
    async function fetchPetugas() {
        try {
            const res = await fetch(`${BASE_URL}/master/petugas`, {
                headers: {
                    'Authorization': `Bearer ${authToken}`
                }
            });

            if (!res.ok) throw new Error("Gagal mengambil data");

            const responseData = await res.json();
            const tbody = document.getElementById('tbodyPetugas');
            tbody.innerHTML = '';

            // Deteksi format data (Array langsung atau dibungkus .data)
            const listData = Array.isArray(responseData) ? responseData : (responseData.data || []);

            if (listData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">Tidak ada data petugas.</td></tr>';
                return;
            }

            listData.forEach((p, i) => {
                const roleBadge = p.role === 'superadmin' ? 'bg-purple-100 text-purple-700' :
                    'bg-blue-100 text-blue-700';
                
                const row = `
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 text-center text-gray-400 font-mono text-xs">${i + 1}</td>
                    <td class="px-6 py-4 font-bold text-gray-800">${p.kode_petugas}</td>
                    <td class="px-6 py-4 text-gray-600">${p.nama_petugas}</td>
                    <td class="px-6 py-4 text-gray-500 font-mono">${p.username}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-[10px] font-black uppercase ${roleBadge}">${p.role.replace('_', ' ')}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <button onclick="deletePetugas('${p.kode_petugas}')" class="p-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition">
                            🗑️
                        </button>
                    </td>
                </tr>
            `;
                tbody.insertAdjacentHTML('beforeend', row);
            });
        } catch (error) {
            console.error("Fetch Error:", error);
            alert("Gagal memuat list petugas. Cek console untuk detail.");
        }
    }

    // 2. FUNGSI TAMBAH DATA (SUBMIT)
    document.getElementById('formPetugas').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = e.submitter;
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = "Loading...";

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
                // --- SUKSES ---
                alert(`✅ Berhasil!\nPetugas ditambahkan dengan Kode: ${result.data?.kode_petugas || 'Baru'}`);
                closeModal();
                fetchPetugas(); // Refresh tabel otomatis
                e.target.reset(); // Reset form
            } else {
                // --- GAGAL (LOGIKA SESUAI SCREENSHOT KAMU) ---
                console.log("Error Server:", result); // Cek F12 jika masih bingung

                let pesanError = "Gagal menyimpan data.";
                
                // Ambil daftar key error (misal: ['password'] atau ['username'])
                const errorKeys = Object.keys(result);

                if (errorKeys.length > 0) {
                    // Ambil key pertama
                    const firstField = errorKeys[0];
                    // Ambil isinya (contoh: ["The password field must be..."])
                    const errorContent = result[firstField];

                    // Cek apakah isinya array atau string
                    if (Array.isArray(errorContent)) {
                        pesanError = errorContent[0];
                    } else if (typeof errorContent === 'string') {
                        pesanError = errorContent;
                    }
                }

                alert("Gagal: " + pesanError);
                e.target.reset();
            }
        } catch (e) {
            console.error(e);
            alert("Terjadi kesalahan koneksi / Server Error.");
        } finally {
            btn.disabled = false;
            btn.innerText = originalText;
        }
    });

    // 3. FUNGSI HAPUS
    async function deletePetugas(kode) {
        if (!confirm("Hapus akun petugas ini?")) return;
        try {
            const res = await fetch(`${BASE_URL}/master/petugas/${kode}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${authToken}`
                }
            });
            if (res.ok) {
                fetchPetugas();
            } else {
                alert("Gagal menghapus data.");
            }
        } catch (error) {
            alert("Error koneksi saat menghapus.");
        }
    }

    // 4. MODAL UTILS
    function openModal() {
        const modal = document.getElementById('modalPetugas');
        if(modal) modal.classList.remove('hidden');
    }

    function closeModal() {
        const modal = document.getElementById('modalPetugas');
        if(modal) modal.classList.add('hidden');
    }

    // 5. EKSEKUSI SAAT PERTAMA KALI
    fetchPetugas();
</script>
@endpush
