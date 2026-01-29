@extends('layouts.admin')

@section('title', 'Pengaturan Sistem')
@section('header_title', 'Pengaturan Sistem')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center">
                <div
                    class="w-full aspect-square bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl flex items-center justify-center mb-4 overflow-hidden group relative">
                    <img id="img_preview" src="" class="w-full h-full object-contain hidden">
                    <span id="img_placeholder" class="text-gray-400 italic">Logo Instansi</span>

                    <input type="file" id="input_logo" class="hidden" accept="image/*">

                    <button type="button" onclick="document.getElementById('input_logo').click()"
                        class="absolute inset-0 bg-indigo-600/10 opacity-0 group-hover:opacity-100 transition flex items-center justify-center font-bold text-indigo-600">
                        Ganti Logo
                    </button>
                </div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Logo Bank Mini</p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <span class="p-1.5 bg-indigo-100 rounded-lg text-indigo-600 text-sm">⚙️</span>
                    Pengaturan Admin
                </h3>

                <div class="space-y-6">
                    <div class="flex gap-4 p-1 bg-gray-100 rounded-xl">
                        <label
                            class="flex-1 flex items-center justify-center gap-2 py-2 cursor-pointer rounded-lg transition has-[:checked]:bg-white has-[:checked]:shadow-sm">
                            <input type="radio" name="tipe_admin" value="bulan" class="hidden" checked>
                            <span class="text-xs font-bold text-gray-600">BULAN</span>
                        </label>
                        <label
                            class="flex-1 flex items-center justify-center gap-2 py-2 cursor-pointer rounded-lg transition has-[:checked]:bg-white has-[:checked]:shadow-sm">
                            <input type="radio" name="tipe_admin" value="tahun" class="hidden">
                            <span class="text-xs font-bold text-gray-600">TAHUN</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 mb-4 uppercase tracking-tighter">Atur Jumlah
                            Potongan</label>
                        <input type="range" id="slider_admin" min="0" max="25000" step="500"
                            class="w-full h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                        <div class="mt-6 text-center">
                            <p class="text-xs text-gray-400 mb-1">Potongan per Nasabah:</p>
                            <div class="inline-block px-4 py-2 bg-indigo-50 rounded-2xl border border-indigo-100">
                                <span class="text-indigo-800 font-black text-xl">Rp. <span id="val_admin">0</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 mb-8 flex items-center gap-3">
                    Identitas Website
                    <span class="h-1 flex-1 bg-gray-50"></span>
                </h3>

                <form id="formPengaturan" class="space-y-6">
                    <div class="group">
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Nama Website</label>
                        <div class="flex gap-2">
                            <input type="text" id="nama_website" value="Bank Mini SMK Yadika 2"
                                class="flex-1 p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition disabled:bg-white disabled:text-gray-400"
                                disabled>
                            <button type="button" onclick="enableInput('nama_website')"
                                class="px-5 py-2 bg-indigo-50 text-indigo-600 rounded-xl font-bold hover:bg-indigo-600 hover:text-white transition text-sm border border-indigo-100">Edit</button>
                        </div>
                    </div>

                    <div class="group">
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Email Instansi</label>
                        <div class="flex gap-2">
                            <input type="email" id="email_website" value="admin@smkyadika2.sch.id"
                                class="flex-1 p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition disabled:bg-white disabled:text-gray-400"
                                disabled>
                            <button type="button" onclick="enableInput('email_website')"
                                class="px-5 py-2 bg-indigo-50 text-indigo-600 rounded-xl font-bold hover:bg-indigo-600 hover:text-white transition text-sm border border-indigo-100">Edit</button>
                        </div>
                    </div>

                    <div class="group">
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Nomor Telephone</label>
                        <div class="flex gap-2">
                            <input type="text" id="no_telp" value="021-12345678"
                                class="flex-1 p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition disabled:bg-white disabled:text-gray-400"
                                disabled>
                            <button type="button" onclick="enableInput('no_telp')"
                                class="px-5 py-2 bg-indigo-50 text-indigo-600 rounded-xl font-bold hover:bg-indigo-600 hover:text-white transition text-sm border border-indigo-100">Edit</button>
                        </div>
                    </div>

                    <div class="group">
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Alamat Lengkap</label>
                        <div class="flex gap-2">
                            <textarea id="alamat" rows="3"
                                class="flex-1 p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition disabled:bg-white disabled:text-gray-400"
                                disabled>Jl. Raya Kampung Melayu No. 2, Tangerang</textarea>
                            <button type="button" onclick="enableInput('alamat')"
                                class="px-5 py-2 bg-indigo-50 text-indigo-600 rounded-xl font-bold hover:bg-indigo-600 hover:text-white transition text-sm border border-indigo-100 h-fit">Edit</button>
                        </div>
                    </div>

                    <div class="pt-8">
                        <button type="submit" id="btnSimpan"
                            class="w-full bg-indigo-600 text-white font-black py-4 rounded-2xl shadow-xl shadow-indigo-100 hover:bg-indigo-700 hover:-translate-y-0.5 transition transform active:scale-95 uppercase tracking-widest">
                            Simpan Semua Pengaturan
                        </button>
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

        if (!authToken) {
            window.location.href = "{{ url('/admin/login') }}";
        }

        async function loadSettings() {
            try {
                const res = await fetch(`${BASE_URL}/pengaturan`, {
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();

                const dataArr = Array.isArray(data) ? data : (data.data || []);
                const getVal = (key) => {
                    const item = dataArr.find(i => i.nama_pengaturan === key);
                    return item ? item.nilai : null;
                };

                document.getElementById('nama_website').value = getVal('nama_website') || '';
                document.getElementById('email_website').value = getVal('email_website') || '';
                document.getElementById('no_telp').value = getVal('no_telp') || '';
                document.getElementById('alamat').value = getVal('alamat') || '';

                const potongan = getVal('jumlah_potongan');
                if (potongan) {
                    document.getElementById('slider_admin').value = potongan;
                    document.getElementById('val_admin').innerText = new Intl.NumberFormat('id-ID').format(potongan);
                }

                const tipe = getVal('tipe_potongan');
                if (tipe) {
                    const radio = document.querySelector(`input[name="tipe_admin"][value="${tipe}"]`);
                    if (radio) radio.checked = true;
                }

                const logo = getVal('logo_website');
                if (logo) {
                    const img = document.getElementById('img_preview');
                    img.src = logo.startsWith('http') ? logo : `{{ url('/') }}/${logo}`;
                    img.classList.remove('hidden');
                    document.getElementById('img_placeholder').classList.add('hidden');
                }
            } catch (e) {
                console.error("Gagal memuat pengaturan:", e);
            }
        }

        document.addEventListener('DOMContentLoaded', loadSettings);

        const slider = document.getElementById('slider_admin');
        slider.addEventListener('input', function() {
            document.getElementById('val_admin').innerText = new Intl.NumberFormat('id-ID').format(this.value);
        });

        function enableInput(id) {
            const input = document.getElementById(id);
            input.disabled = false;
            input.classList.remove('bg-gray-50');
            input.focus();
        }

        document.getElementById('input_logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const img = document.getElementById('img_preview');
                    img.src = event.target.result;
                    img.classList.remove('hidden');
                    document.getElementById('img_placeholder').classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('formPengaturan').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnSimpan');

            btn.disabled = true;
            btn.innerText = "SEDANG MENYIMPAN...";

            const formData = new FormData();
            formData.append('nama_website', document.getElementById('nama_website').value);
            formData.append('email_website', document.getElementById('email_website').value);
            formData.append('no_telp', document.getElementById('no_telp').value);
            formData.append('alamat', document.getElementById('alamat').value);
            formData.append('tipe_potongan', document.querySelector('input[name="tipe_admin"]:checked').value);
            formData.append('jumlah_potongan', document.getElementById('slider_admin').value);

            const logoFile = document.getElementById('input_logo').files[0];
            if (logoFile) {
                formData.append('logo_website', logoFile);
            }

            try {
                const res = await fetch(`${BASE_URL}/pengaturan/update`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await res.json();

                if (res.ok) {
                    // Notifikasi Sukses dengan SweetAlert2
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Pengaturan sistem telah diperbarui.',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    // Notifikasi Gagal dari Laravel
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: result.message || 'Terjadi kesalahan saat memperbarui data.'
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error Jaringan',
                    text: 'Gagal terhubung ke server.'
                });
            } finally {
                btn.disabled = false;
                btn.innerText = "SIMPAN SEMUA PENGATURAN";
            }
        });
    </script>
@endpush
