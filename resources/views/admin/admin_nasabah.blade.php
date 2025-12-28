@extends('layouts.admin')

@section('title', 'Kelola Nasabah')
@section('header_title', 'Manajemen Nasabah')

@section('content')
<div class="bg-white p-5 rounded-xl shadow-sm mb-6 border border-gray-100">
    <form id="filterForm" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
        <div class="md:col-span-2">
            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Pencarian</label>
            <input type="text" id="filterSearch" placeholder="Nama / No. Rekening / NIS" class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Jurusan</label>
            <select id="filterJurusan" class="w-full p-2 border border-gray-300 rounded-lg text-sm bg-white outline-none">
                <option value="">Semua Jurusan</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Tahun Ajaran</label>
            <select id="filterTahunAjaran" class="w-full p-2 border border-gray-300 rounded-lg text-sm bg-white outline-none">
                <option value="">Semua TA</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Kelas</label>
            <select id="filterKelas" class="w-full p-2 border border-gray-300 rounded-lg text-sm bg-white outline-none">
                <option value="">Semua Kelas</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-2 rounded-lg hover:bg-blue-700 transition text-sm shadow-md">Cari</button>
            <button type="button" onclick="resetFilter()" class="px-3 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 text-sm border border-gray-200">🔄</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap justify-between items-center bg-gray-50/50 gap-3">
        <h3 class="text-lg font-bold text-gray-800">Daftar Nasabah</h3>
        <div class="flex gap-2">
            <button onclick="cetakLaporanPDF()" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-red-700 shadow-md flex items-center gap-2 transition">
                📄 PDF Laporan
            </button>
            <button onclick="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-blue-700 shadow-md transition transform hover:-translate-y-0.5">
                + Tambah Nasabah
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-left whitespace-nowrap">
            <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase text-[10px] font-bold tracking-widest">
                <tr>
                    <th class="px-6 py-3">Nama / NIS</th>
                    <th class="px-6 py-3">No. Rekening</th>
                    <th class="px-6 py-3">Jenis</th>
                    <th class="px-6 py-3">Kelas / Jurusan</th>
                    <th class="px-6 py-3 text-right">Saldo</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="nasabahList" class="divide-y divide-gray-100 text-sm bg-white text-gray-700"></tbody>
        </table>
    </div>
    <div id="loadingIndicator" class="hidden text-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
    </div>
</div>

<div id="modalAdd" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50 p-4 transition-all duration-300">
    <div class="modal-overlay absolute w-full h-full bg-black opacity-50" onclick="closeModal()"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-2xl mx-auto rounded-xl shadow-2xl z-50 overflow-y-auto max-h-[90vh] transform scale-95 opacity-0 transition-all duration-300">
        <div class="modal-content py-4 text-left px-6">
            <div class="flex justify-between items-center pb-3 border-b mb-4">
                <p class="text-xl font-bold text-blue-900">Pendaftaran Nasabah Baru</p>
                <button onclick="closeModal()" class="text-gray-400 hover:text-red-500 text-2xl">✕</button>
            </div>
            <form id="formAddNasabah" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" id="nama" class="w-full p-2 border border-gray-300 rounded outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">NIS / NIP</label>
                        <input type="text" id="no_induk" class="w-full p-2 border border-gray-300 rounded outline-none" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Jenis Rekening</label>
                    <select id="jenis_rekening" class="w-full p-2 border border-gray-300 rounded bg-white" onchange="toggleSiswaFields()">
                        <option value="siswa">Siswa</option>
                        <option value="guru">Guru / Staff</option>
                    </select>
                </div>
                <div id="siswaFields" class="bg-blue-50 p-4 rounded-lg border border-blue-100 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <select id="jurusan_id" class="w-full p-2 border border-blue-200 rounded bg-white" onchange="filterKelasModal()"></select>
                        <select id="tahun_ajaran_id" class="w-full p-2 border border-blue-200 rounded bg-white" onchange="filterKelasModal()"></select>
                    </div>
                    <select id="kode_kelas" class="w-full p-2 border border-blue-200 rounded bg-white disabled:bg-gray-200" disabled></select>
                </div>
                <div class="flex justify-end pt-4 space-x-3 border-t">
                    <button type="button" onclick="closeModal()" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg">Batal</button>
                    <button type="submit" id="btnSimpan" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modalEdit" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50 p-4 transition-all duration-300">
    <div class="modal-overlay absolute w-full h-full bg-black opacity-50" onclick="toggleModal('modalEdit')"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded-xl shadow-2xl z-50 transform scale-95 opacity-0 transition-all duration-300">
        <div class="modal-content py-4 text-left px-6">
            <div class="flex justify-between items-center pb-3 border-b mb-4">
                <p class="text-xl font-bold text-gray-800">Edit Data Nasabah</p>
                <button onclick="toggleModal('modalEdit')" class="text-gray-400 hover:text-red-500 text-2xl">✕</button>
            </div>
            <form id="formEditNasabah" class="space-y-4">
                <input type="hidden" id="edit_no_rekening">
                <input type="text" id="edit_nama" placeholder="Nama" class="w-full p-2 border border-gray-300 rounded" required>
                <input type="email" id="edit_email" placeholder="Email" class="w-full p-2 border border-gray-300 rounded" required>
                <input type="text" id="edit_no_telp" placeholder="Nomor HP" class="w-full p-2 border border-gray-300 rounded">
                <div class="flex justify-end pt-4 space-x-2 border-t">
                    <button type="button" onclick="toggleModal('modalEdit')" class="px-4 py-2 bg-gray-200 rounded-lg">Batal</button>
                    <button type="submit" id="btnUpdate" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const BASE_URL = "{{ \Illuminate\Support\Facades\URL::to('/api') }}";
    const authToken = localStorage.getItem('petugas_token');
    let allKelas = [];

    document.addEventListener('DOMContentLoaded', () => {
        if (!authToken) {
            window.location.href = "{{ \Illuminate\Support\Facades\URL::to('/admin/login') }}";
            return;
        }

        // Jalankan Load Data Master & Tabel Utama
        loadMasterData();
        fetchNasabah();

        // Event Listener agar dropdown memicu pencarian otomatis
        document.getElementById('filterJurusan').addEventListener('change', fetchNasabah);
        document.getElementById('filterTahunAjaran').addEventListener('change', fetchNasabah);
        document.getElementById('filterKelas').addEventListener('change', fetchNasabah);
    });

    // --- FUNGSI LOAD DATA MASTER KE DROPDOWN ---
    async function loadMasterData() {
        const headers = { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' };
        try {
            const [resJur, resTA, resKls] = await Promise.all([
                fetch(`${BASE_URL}/master/jurusan`, { headers }),
                fetch(`${BASE_URL}/master/tahun-ajaran`, { headers }),
                fetch(`${BASE_URL}/master/kelas`, { headers })
            ]);

            const jur = await resJur.json();
            const ta = await resTA.json();
            allKelas = await resKls.json();

            const populate = (id, data, valKey, labelKey, def) => {
                const el = document.getElementById(id);
                if (!el) return;
                const list = Array.isArray(data) ? data : (data.data || []);
                el.innerHTML = `<option value="">${def}</option>`;
                list.forEach(i => el.insertAdjacentHTML('beforeend', `<option value="${i[valKey]}">${i[labelKey]}</option>`));
            };

            // Isi dropdown Filter
            populate('filterJurusan', jur, 'kode_jurusan', 'nama_jurusan', 'Semua Jurusan');
            populate('filterTahunAjaran', ta, 'kode_tahun_ajaran', 'tahun_ajaran', 'Semua TA');
            populate('filterKelas', allKelas, 'kode_kelas', 'nama_kelas', 'Semua Kelas');
            
            // Isi dropdown Modal Add
            populate('jurusan_id', jur, 'kode_jurusan', 'nama_jurusan', 'Pilih Jurusan');
            populate('tahun_ajaran_id', ta, 'kode_tahun_ajaran', 'tahun_ajaran', 'Pilih TA');

        } catch (e) { console.error("Error load master:", e); }
    }

    // --- FUNGSI UTAMA FETCH TABEL ---
    async function fetchNasabah() {
        const list = document.getElementById('nasabahList');
        const loader = document.getElementById('loadingIndicator');
        const params = new URLSearchParams({
            search: document.getElementById('filterSearch').value,
            jurusan: document.getElementById('filterJurusan').value,
            tahun_ajaran: document.getElementById('filterTahunAjaran').value,
            kelas: document.getElementById('filterKelas').value
        });

        list.innerHTML = ''; loader.classList.remove('hidden');
        try {
            const res = await fetch(`${BASE_URL}/master/nasabah?${params.toString()}`, {
                headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
            });
            const data = await res.json();
            loader.classList.add('hidden');
            const arr = Array.isArray(data) ? data : (data.data || []);
            
            if (arr.length === 0) {
                list.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Data tidak ditemukan</td></tr>`;
                return;
            }

            arr.forEach(n => {
                const kls = n.kelas || null;
                list.insertAdjacentHTML('beforeend', `
                    <tr class="hover:bg-blue-50/50 border-b transition">
                        <td class="px-6 py-4 font-bold text-gray-900">${n.nama}<br><span class="text-[10px] text-gray-400 italic">${n.no_induk}</span></td>
                        <td class="px-6 py-4 font-mono text-xs font-bold text-blue-700">${n.no_rekening}</td>
                        <td class="px-6 py-4 text-[9px] uppercase font-black text-gray-500">${n.jenis_rekening}</td>
                        <td class="px-6 py-4 text-xs"><b>${kls?.nama_kelas || '-'}</b><br><span class="text-[10px] text-gray-400">${kls?.jurusan?.nama_jurusan || '-'}</span></td>
                        <td class="px-6 py-4 font-bold text-green-600 text-right">Rp ${new Intl.NumberFormat('id-ID').format(n.saldo)}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-1.5">
                                <button onclick="openEditModal('${n.no_rekening}', '${n.nama}', '${n.email}', '${n.no_telp || ''}')" class="p-1.5 bg-blue-50 text-blue-600 rounded-lg">✏️</button>
                                <button onclick="hapusNasabah('${n.no_rekening}')" class="p-1.5 bg-red-50 text-red-600 rounded-lg">🗑️</button>
                            </div>
                        </td>
                    </tr>
                `);
            });
        } catch (e) { loader.classList.add('hidden'); }
    }

    function cetakLaporanPDF() {
        const params = new URLSearchParams({
            search: document.getElementById('filterSearch').value,
            jurusan: document.getElementById('filterJurusan').value,
            tahun_ajaran: document.getElementById('filterTahunAjaran').value,
            kelas: document.getElementById('filterKelas').value
        });
        window.open(`{{ url('/admin/nasabah/cetak-pdf') }}?${params.toString()}`, '_blank');
    }

    function filterKelasModal() {
        const jur = document.getElementById('jurusan_id').value;
        const ta = document.getElementById('tahun_ajaran_id').value;
        const sel = document.getElementById('kode_kelas');
        sel.innerHTML = '<option value="">Pilih Kelas</option>'; sel.disabled = true;
        if (!jur || !ta) return;
        const items = Array.isArray(allKelas) ? allKelas : (allKelas.data || []);
        const filtered = items.filter(k => k.kode_jurusan === jur && k.kode_tahun_ajaran === ta);
        if (filtered.length > 0) {
            filtered.forEach(k => sel.insertAdjacentHTML('beforeend', `<option value="${k.kode_kelas}">${k.nama_kelas}</option>`));
            sel.disabled = false;
        }
    }

    function toggleModal(id) {
        const m = document.getElementById(id);
        m.classList.toggle('opacity-0');
        m.classList.toggle('pointer-events-none');
        m.querySelector('.modal-container').classList.toggle('scale-95');
        m.querySelector('.modal-container').classList.toggle('opacity-0');
    }

    function closeModal() { toggleModal('modalAdd'); document.getElementById('formAddNasabah').reset(); }
    function openModal() { toggleModal('modalAdd'); }
    function openEditModal(norek, nama, email, telp) {
        document.getElementById('edit_no_rekening').value = norek;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_no_telp').value = telp;
        toggleModal('modalEdit');
    }
    function resetFilter() { document.getElementById('filterForm').reset(); fetchNasabah(); }
    document.getElementById('filterForm').addEventListener('submit', (e) => { e.preventDefault(); fetchNasabah(); });
</script>
@endpush