@extends('layouts.admin')

@section('title', 'Kelola Nasabah')
@section('header_title', 'Manajemen Nasabah')

@section('content')
    <div class="bg-white p-5 rounded-xl shadow-sm mb-6 border border-gray-100">
        <form id="filterForm" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Pencarian</label>
                <input type="text" id="filterSearch" placeholder="Nama / No. Rekening / NIS"
                    class="w-full p-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Jurusan</label>
                <select id="filterJurusan"
                    class="w-full p-2 border border-gray-300 rounded-lg text-sm bg-white outline-none">
                    <option value="">Semua Jurusan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Tahun Ajaran</label>
                <select id="filterTahunAjaran"
                    class="w-full p-2 border border-gray-300 rounded-lg text-sm bg-white outline-none">
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
                <button type="submit"
                    class="flex-1 bg-blue-600 text-white font-bold py-2 rounded-lg hover:bg-blue-700 transition text-sm shadow-md">Cari</button>
                <button type="button" onclick="resetFilter()"
                    class="px-3 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 text-sm border border-gray-200">🔄</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap justify-between items-center bg-gray-50/50 gap-3">
            <h3 class="text-lg font-bold text-gray-800">Daftar Nasabah</h3>
            <div class="flex gap-2">
                <button onclick="cetakLaporanPDF()"
                    class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-red-700 shadow-md flex items-center gap-2 transition">
                    📄 PDF Laporan
                </button>
                <button onclick="openModal()"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-blue-700 shadow-md transition transform hover:-translate-y-0.5">
                    + Tambah Nasabah
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left whitespace-nowrap">
                <thead
                    class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase text-[10px] font-bold tracking-widest">
                    <tr>
                        <th class="px-6 py-3">Nama / NIS</th>
                        <th class="px-6 py-3">No. Rekening</th>
                        <th class="px-6 py-3">Jenis</th>
                        <th class="px-6 py-3">Kelas / Jurusan</th>
                        <th class="px-6 py-3">Thn Ajar</th>
                        <th class="px-6 py-3 text-right">Saldo</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="nasabahList" class="divide-y divide-gray-100 text-sm bg-white text-gray-700"></tbody>
            </table>
        </div>

        <div id="loadingIndicator" class="hidden text-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            <p class="text-gray-400 mt-3 text-sm italic">Memproses data...</p>
        </div>
    </div>

    <div id="modalAdd"
        class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50 p-4 transition-all duration-300">
        <div class="modal-overlay absolute w-full h-full bg-black opacity-50" onclick="closeModal()"></div>
        <div
            class="modal-container bg-white w-11/12 md:max-w-2xl mx-auto rounded-xl shadow-2xl z-50 overflow-y-auto max-h-[90vh] transform scale-95 opacity-0 transition-all duration-300">
            <div class="modal-content py-4 text-left px-6">
                <div class="flex justify-between items-center pb-3 border-b mb-4">
                    <p class="text-xl font-bold text-blue-900">Pendaftaran Nasabah Baru</p>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-red-500 text-2xl">✕</button>
                </div>
                <form id="formAddNasabah" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" id="nama"
                                class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 outline-none"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">NIS / NIP</label>
                            <input type="text" id="no_induk"
                                class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 outline-none"
                                required>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                            <input type="email" id="email"
                                class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 outline-none"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nomor HP</label>
                            <input type="text" id="no_telp"
                                class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Jenis Rekening</label>
                        <select id="jenis_rekening" class="w-full p-2 border border-gray-300 rounded bg-white outline-none"
                            onchange="toggleSiswaFields()">
                            <option value="siswa">Siswa</option>
                            <option value="guru">Guru / Staff</option>
                        </select>
                    </div>
                    <div id="siswaFields" class="bg-blue-50 p-4 rounded-lg border border-blue-100 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-blue-800 mb-1">Jurusan</label>
                                <select id="jurusan_id" class="w-full p-2 border border-blue-200 rounded bg-white"
                                    onchange="filterKelasModal()"></select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-blue-800 mb-1">Tahun Ajaran</label>
                                <select id="tahun_ajaran_id" class="w-full p-2 border border-blue-200 rounded bg-white"
                                    onchange="filterKelasModal()"></select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-blue-800 mb-1">Kelas</label>
                            <select id="kode_kelas"
                                class="w-full p-2 border border-blue-200 rounded bg-white disabled:bg-gray-200" disabled>
                                <option value="">Pilih Jurusan & TA Dulu</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                        <input type="text" id="password"
                            class="w-full p-2 border border-gray-300 rounded outline-none" placeholder="Min. 6 karakter"
                            required>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                        <label class="block text-sm font-bold text-green-800 mb-1">Saldo Awal</label>
                        <div class="relative">
                            <span
                                class="absolute inset-y-0 left-0 pl-3 flex items-center text-green-600 font-bold">Rp</span>
                            <input type="number" id="saldo_awal"
                                class="w-full pl-10 p-2 border border-green-300 rounded font-bold text-green-800"
                                placeholder="20000" required>
                        </div>
                        <p class="text-[10px] text-green-600 mt-1 italic">
                            * Saldo dipotong otomatis <span id="display_admin_fee" class="font-bold">Rp 0</span> untuk
                            biaya admin.
                        </p>
                    </div>
                    <div class="flex justify-end pt-4 space-x-3 border-t">
                        <button type="button" onclick="closeModal()"
                            class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold">Batal</button>
                        <button type="submit" id="btnSimpan"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">Simpan
                            Nasabah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modalEdit"
        class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center z-50 p-4 transition-all duration-300">
        <div class="modal-overlay absolute w-full h-full bg-black opacity-50" onclick="toggleModal('modalEdit')"></div>
        <div
            class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded-xl shadow-2xl z-50 transform scale-95 opacity-0 transition-all duration-300">
            <div class="modal-content py-4 text-left px-6">
                <div class="flex justify-between items-center pb-3 border-b mb-4">
                    <p class="text-xl font-bold text-gray-800">Edit Data Nasabah</p>
                    <button onclick="toggleModal('modalEdit')"
                        class="text-gray-400 hover:text-red-500 text-2xl">✕</button>
                </div>
                <form id="formEditNasabah" class="space-y-4">
                    <input type="hidden" id="edit_no_rekening">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" id="edit_nama"
                            class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 outline-none"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                        <input type="email" id="edit_email"
                            class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 outline-none"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nomor HP</label>
                        <input type="text" id="edit_no_telp"
                            class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Status Akun</label>
                        <select id="edit_status"
                            class="w-full p-2 border border-gray-300 rounded bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                        <p class="text-xs text-red-500 mt-1">*Jika Nonaktif, nasabah tidak bisa login.</p>
                    </div>
                    <div class="flex justify-end pt-4 space-x-2 border-t">
                        <button type="button" onclick="toggleModal('modalEdit')"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">Batal</button>
                        <button type="submit" id="btnUpdate"
                            class="px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700">Simpan
                            Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const BASE_URL = "{{ \Illuminate\Support\Facades\URL::to('/api') }}";
        const authToken = localStorage.getItem('petugas_token');
        const userRole = localStorage.getItem('role');
        let allKelas = [];

        document.addEventListener('DOMContentLoaded', () => {
            if (!authToken) {
                window.location.href = "{{ \Illuminate\Support\Facades\URL::to('/admin/login') }}";
                return;
            }

            if (userRole !== 'superadmin') {
                const restrictedMenus = ['Tahun Ajaran', 'Jurusan', 'Kelas', 'Pengaturan Sistem', 'Manajemen Akun'];
                document.querySelectorAll('.sidebar-link').forEach(link => {
                    if (restrictedMenus.some(m => link.innerText.includes(m))) {
                        link.remove();
                    }
                });
            }

            loadMasterData();
            fetchNasabah();

            // --- PROSES SIMPAN NASABAH ---
            const formAdd = document.getElementById('formAddNasabah');
            if (formAdd) {
                formAdd.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const btn = document.getElementById('btnSimpan');
                    btn.disabled = true;
                    btn.innerHTML =
                    `<span class="inline-block animate-spin mr-2">⏳</span> Memproses...`;

                    const payload = {
                        nama: document.getElementById('nama').value,
                        no_induk: document.getElementById('no_induk').value,
                        email: document.getElementById('email').value,
                        no_telp: document.getElementById('no_telp').value,
                        password: document.getElementById('password').value,
                        jenis_rekening: document.getElementById('jenis_rekening').value,
                        saldo_awal: parseInt(document.getElementById('saldo_awal').value) || 0,
                        kode_kelas: document.getElementById('jenis_rekening').value === 'siswa' ?
                            document.getElementById('kode_kelas').value : null
                    };

                    try {
                        const response = await fetch(`${BASE_URL}/master/nasabah`, {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${authToken}`,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
                        const result = await response.json();

                        if (response.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Nasabah baru berhasil didaftarkan.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            let errorMsg = result.message || "Gagal menyimpan data.";
                            if (response.status === 422) {
                                errorMsg = Object.values(result.errors).map(err => `<li>${err[0]}</li>`)
                                    .join('');
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Validasi',
                                html: `<ul class="text-left text-sm text-red-600">${errorMsg}</ul>`
                            });
                        }
                    } catch (e) {
                        Swal.fire('Error', 'Terjadi kesalahan koneksi server.', 'error');
                    } finally {
                        btn.disabled = false;
                        btn.innerText = "Simpan Nasabah";
                    }
                });
            }

            // --- PROSES UPDATE DATA NASABAH (DENGAN STATUS) ---
            const formEdit = document.getElementById('formEditNasabah');
            if (formEdit) {
                formEdit.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const norek = document.getElementById('edit_no_rekening').value;
                    const btn = document.getElementById('btnUpdate');
                    btn.disabled = true;
                    btn.innerText = "Memperbarui...";

                    const payload = {
                        nama: document.getElementById('edit_nama').value,
                        email: document.getElementById('edit_email').value,
                        no_telp: document.getElementById('edit_no_telp').value,
                        status: document.getElementById('edit_status').value // Tambah Status
                    };

                    try {
                        const response = await fetch(`${BASE_URL}/master/nasabah/${norek}`, {
                            method: 'PUT',
                            headers: {
                                'Authorization': `Bearer ${authToken}`,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const result = await response.json(); // Ambil JSON response

                        if (response.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Data nasabah telah diperbarui.',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                            toggleModal('modalEdit');
                            fetchNasabah();
                        } else {
                            // Logic Error Handling yang lebih detail
                            console.log("Error Update:", result);
                            let pesanError = result.message || "Gagal update data.";
                            if (result.errors) {
                                const firstKey = Object.keys(result.errors)[0];
                                pesanError = result.errors[firstKey][0];
                            }
                            Swal.fire('Gagal', pesanError, 'error');
                        }
                    } catch (e) {
                        Swal.fire('Error', 'Masalah koneksi server.', 'error');
                    } finally {
                        btn.disabled = false;
                        btn.innerText = "Simpan Perubahan";
                    }
                });
            }
        });

        async function loadMasterData() {
            try {
                const headers = {
                    'Authorization': `Bearer ${authToken}`,
                    'Accept': 'application/json'
                };
                const [resJur, resTA, resKls, resPengaturan] = await Promise.all([
                    fetch(`${BASE_URL}/master/jurusan`, {
                        headers
                    }),
                    fetch(`${BASE_URL}/master/tahun-ajaran`, {
                        headers
                    }),
                    fetch(`${BASE_URL}/master/kelas`, {
                        headers
                    }),
                    fetch(`${BASE_URL}/pengaturan`, {
                        headers
                    })
                ]);

                const jur = await resJur.json();
                const ta = await resTA.json();
                allKelas = await resKls.json();
                const settings = await resPengaturan.json();

                // Logic Biaya Admin
                const dataArr = Array.isArray(settings) ? settings : (settings.data || []);
                const settingPotongan = dataArr.find(s => s.nama_pengaturan === 'jumlah_potongan');
                if (settingPotongan) {
                    const fee = new Intl.NumberFormat('id-ID').format(settingPotongan.nilai);
                    document.getElementById('display_admin_fee').innerText = `Rp ${fee}`;
                }

                // Helper Populate Biasa
                const populate = (id, data, valKey, labelKey, defaultText) => {
                    const el = document.getElementById(id);
                    if (!el) return;
                    const list = Array.isArray(data) ? data : (data.data || []);
                    el.innerHTML = `<option value="">${defaultText}</option>`;
                    list.forEach(item => {
                        el.insertAdjacentHTML('beforeend',
                            `<option value="${item[valKey]}">${item[labelKey]}</option>`);
                    });
                };

                // 1. Populate Filter (Semua Data Muncul & Aktif)
                populate('filterJurusan', jur, 'kode_jurusan', 'nama_jurusan', 'Semua Jurusan');
                populate('filterTahunAjaran', ta, 'kode_tahun_ajaran', 'tahun_ajaran', 'Semua TA');
                populate('filterKelas', allKelas, 'kode_kelas', 'nama_kelas', 'Semua Kelas');

                // 2. Populate Modal Tambah (Jurusan Biasa)
                populate('jurusan_id', jur, 'kode_jurusan', 'nama_jurusan', 'Pilih Jurusan');

                // --- 3. CUSTOM POPULATE UNTUK TAHUN AJARAN (LOGIC DISABLE) ---
                const elTa = document.getElementById('tahun_ajaran_id');
                const listTa = Array.isArray(ta) ? ta : (ta.data || []);

                elTa.innerHTML = '<option value="">Pilih TA</option>';

                listTa.forEach(t => {
                    // Cek Status
                    const isNonaktif = t.status === 'nonaktif';

                    // Jika Nonaktif: Tambah atribut disabled dan styling abu-abu
                    const disabledAttr = isNonaktif ? 'disabled class="bg-gray-100 text-gray-400 italic"' : '';
                    const labelStatus = isNonaktif ? ' (Nonaktif)' : '';

                    elTa.insertAdjacentHTML('beforeend',
                        `<option value="${t.kode_tahun_ajaran}" ${disabledAttr}>
                            ${t.tahun_ajaran}${labelStatus}
                        </option>`
                    );
                });
                // -------------------------------------------------------------

            } catch (e) {
                console.error("Gagal load master:", e);
            }
        }

        async function fetchNasabah() {
            const list = document.getElementById('nasabahList');
            const loader = document.getElementById('loadingIndicator');
            const params = new URLSearchParams({
                search: document.getElementById('filterSearch').value,
                jurusan: document.getElementById('filterJurusan').value,
                tahun_ajaran: document.getElementById('filterTahunAjaran').value,
                kelas: document.getElementById('filterKelas').value
            });
            list.innerHTML = '';
            loader.classList.remove('hidden');

            try {
                const res = await fetch(`${BASE_URL}/master/nasabah?${params.toString()}`, {
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                loader.classList.add('hidden');
                const arr = Array.isArray(data) ? data : (data.data || []);

                arr.forEach(n => {
                    const kls = n.kelas || null;
                    const ta = kls?.tahun_ajaran ? kls.tahun_ajaran.tahun_ajaran : '-';
                    const statusClass = n.status === 'nonaktif' ? 'bg-red-100 text-red-700' :
                        'bg-green-100 text-green-700';

                    // PENTING: KITA STRINGIFY OBJECT NASABAH AGAR AMAN DIKIRIM KE FUNGSI ONCLICK
                    // .replace(/"/g, '&quot;') digunakan agar tanda kutip di dalam data tidak merusak HTML
                    const nasabahData = JSON.stringify(n).replace(/"/g, '&quot;');

                    list.insertAdjacentHTML('beforeend', `
                    <tr class="hover:bg-blue-50/50 border-b transition">
                        <td class="px-6 py-4 font-bold text-gray-900">${n.nama}<br><span class="text-[10px] text-gray-400 italic font-mono">${n.no_induk}</span></td>
                        <td class="px-6 py-4 font-mono text-xs font-bold text-blue-700">${n.no_rekening}</td>
                        <td class="px-6 py-4 text-[9px] uppercase font-black text-gray-500">${n.jenis_rekening}</td>
                        <td class="px-6 py-4 text-xs"><b>${kls?.nama_kelas || '-'}</b><br><span class="text-[10px] text-gray-400 uppercase font-bold">${kls?.jurusan?.nama_jurusan || '-'}</span></td>
                        <td class="px-6 py-4 text-xs font-bold text-gray-400">${ta}</td>
                        <td class="px-6 py-4 font-black text-green-600 text-right italic">Rp ${new Intl.NumberFormat('id-ID').format(n.saldo)}</td>
                        <td class="px-6 py-4 text-center"><span class="px-2 py-1 rounded-full text-[10px] font-black uppercase ${statusClass}">${n.status}</span></td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-1.5">
                                <button onclick="openEditModal(${nasabahData})" class="p-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition">✏️</button>
                                <button onclick="hapusNasabah('${n.no_rekening}')" class="p-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition">🗑️</button>
                            </div>
                        </td>
                    </tr>
                `);
                });
            } catch (e) {
                loader.classList.add('hidden');
                console.error(e);
            }
        }

        function filterKelasModal() {
            const jur = document.getElementById('jurusan_id').value;
            const ta = document.getElementById('tahun_ajaran_id').value;
            const sel = document.getElementById('kode_kelas');
            sel.innerHTML = '<option value="">Pilih Kelas</option>';
            sel.disabled = true;

            if (!jur || !ta) return;
            const klsArray = Array.isArray(allKelas) ? allKelas : (allKelas.data || []);
            const filtered = klsArray.filter(k => k.kode_jurusan === jur && k.kode_tahun_ajaran === ta);
            if (filtered.length > 0) {
                filtered.forEach(k => sel.insertAdjacentHTML('beforeend',
                    `<option value="${k.kode_kelas}">${k.nama_kelas}</option>`));
                sel.disabled = false;
            }
        }

        // FUNGSI OPEN MODAL EDIT (YANG SUDAH DIPERBAIKI)
        function openEditModal(nasabah) {
            // Kita terima object utuh 'nasabah', lalu isi form
            document.getElementById('edit_no_rekening').value = nasabah.no_rekening;
            document.getElementById('edit_nama').value = nasabah.nama;
            document.getElementById('edit_email').value = nasabah.email;
            document.getElementById('edit_no_telp').value = nasabah.no_telp || '';
            document.getElementById('edit_status').value = nasabah.status; // Isi Status

            toggleModal('modalEdit');
        }

        async function hapusNasabah(norek) {
            Swal.fire({
                title: 'Hapus Nasabah?',
                text: `Rekening ${norek} akan dihapus. Pastikan saldo Rp 0.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const res = await fetch(`${BASE_URL}/master/nasabah/${norek}`, {
                            method: 'DELETE',
                            headers: {
                                'Authorization': `Bearer ${authToken}`,
                                'Accept': 'application/json'
                            }
                        });
                        const r = await res.json();
                        if (res.ok) {
                            Swal.fire('Terhapus!', 'Data nasabah berhasil dihapus.', 'success');
                            fetchNasabah();
                        } else {
                            Swal.fire('Gagal!', r.message || "Error sistem", 'error');
                        }
                    } catch (e) {
                        Swal.fire('Error', 'Kesalahan server.', 'error');
                    }
                }
            });
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

        function toggleSiswaFields() {
            const isSiswa = document.getElementById('jenis_rekening').value === 'siswa';
            document.getElementById('siswaFields').style.display = isSiswa ? 'block' : 'none';
        }

        function toggleModal(id) {
            const m = document.getElementById(id);
            const c = m.querySelector('.modal-container');
            if (m.classList.contains('opacity-0')) {
                m.classList.remove('opacity-0', 'pointer-events-none');
                c.classList.remove('scale-95', 'opacity-0');
                c.classList.add('scale-100', 'opacity-100');
            } else {
                m.classList.add('opacity-0', 'pointer-events-none');
                c.classList.add('scale-95', 'opacity-0');
                c.classList.remove('scale-100', 'opacity-100');
            }
        }

        function openModal() {
            toggleModal('modalAdd');
        }

        function closeModal() {
            toggleModal('modalAdd');
            document.getElementById('formAddNasabah').reset();
            toggleSiswaFields();
        }
        document.getElementById('filterForm').addEventListener('submit', (e) => {
            e.preventDefault();
            fetchNasabah();
        });

        function resetFilter() {
            document.getElementById('filterForm').reset();
            fetchNasabah();
        }
    </script>
@endpush
