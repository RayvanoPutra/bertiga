@extends('layouts.admin')

@section('title', 'Riwayat Transaksi')
@section('header_title', 'Manajemen Transaksi')

@section('content')
    <div class="bg-white p-5 rounded-xl shadow-sm mb-6 border border-gray-100">
        <form id="filterForm" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-8 gap-3 items-end">
            <div class="lg:col-span-1">
                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase tracking-wider">Cari</label>
                <input type="text" id="filterSearch" placeholder="Rekening/Nama"
                    class="w-full p-2 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase tracking-wider">Jenis</label>
                <select id="filterJenis" class="w-full p-2 border border-gray-300 rounded-lg text-xs bg-white outline-none">
                    <option value="">Semua</option>
                    <option value="SETOR">Setoran</option>
                    <option value="TARIK">Penarikan</option>
                    <option value="AWAL">Potongan Awal</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase tracking-wider">Bulan</label>
                <input type="month" id="filterBulan"
                    class="w-full p-2 border border-gray-300 rounded-lg text-xs outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase tracking-wider">Jurusan</label>
                <select id="filterJurusan"
                    class="w-full p-2 border border-gray-300 rounded-lg text-xs bg-white outline-none">
                    <option value="">Semua Jurusan</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase tracking-wider">Kelas</label>
                <select id="filterKelas" class="w-full p-2 border border-gray-300 rounded-lg text-xs bg-white outline-none">
                    <option value="">Semua Kelas</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase tracking-wider">Thn Ajaran</label>
                <select id="filterTahun" class="w-full p-2 border border-gray-300 rounded-lg text-xs bg-white outline-none">
                    <option value="">Semua Tahun</option>
                </select>
            </div>

            <div class="flex gap-1 lg:col-span-2">
                <button type="submit"
                    class="flex-1 bg-blue-600 text-white font-bold py-2 rounded-lg hover:bg-blue-700 transition text-xs shadow-md">
                    Cari
                </button>
                <button type="button" onclick="cetakRekap()"
                    class="flex-1 bg-red-600 text-white font-bold py-2 rounded-lg hover:bg-red-700 transition text-xs shadow-md">
                    📄 Laporan PDF
                </button>
                <button type="button" onclick="resetFilter()"
                    class="px-3 bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-200 transition text-xs border border-gray-200">
                    🔄
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Riwayat Transaksi</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left whitespace-nowrap">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase text-[10px] font-bold tracking-widest">
                    <tr>
                        <th class="px-6 py-3">Waktu / ID</th>
                        <th class="px-6 py-3">Nasabah</th>
                        <th class="px-6 py-3 text-center">Tipe</th>
                        <th class="px-6 py-3">Keterangan</th>
                        <th class="px-6 py-3 text-right">Jumlah</th>
                        <th class="px-6 py-3 text-center">Petugas</th>
                    </tr>
                </thead>
                <tbody id="transaksiList" class="divide-y divide-gray-100 text-sm bg-white text-gray-700">
                </tbody>
            </table>
        </div>

        <div id="loadingIndicator" class="hidden text-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            <p class="text-gray-400 mt-3 text-sm italic">Memuat data...</p>
        </div>
    </div>

    <script>
        const BASE_URL = "{{ url('/api') }}";
        const authToken = localStorage.getItem('petugas_token');
    
        document.addEventListener('DOMContentLoaded', () => {
            if (!authToken) {
                window.location.href = "{{ url('/admin/login') }}";
                return;
            }
    
            const now = new Date();
            const currentMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
            document.getElementById('filterBulan').value = currentMonth;
    
            loadMasterData();
            fetchRiwayatTransaksi();
        });
    
        document.getElementById('filterForm').addEventListener('submit', (e) => {
            e.preventDefault();
            fetchRiwayatTransaksi();
        });
    
        async function loadMasterData() {
            try {
                const headers = { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' };
                const [resThn, resJur, resKel] = await Promise.all([
                    fetch(`${BASE_URL}/master/tahun-ajaran`, { headers }),
                    fetch(`${BASE_URL}/master/jurusan`, { headers }),
                    fetch(`${BASE_URL}/master/kelas`, { headers })
                ]);

                populateDropdown('filterTahun', await resThn.json(), 'kode_tahun_ajaran', 'tahun_ajaran', 'Semua Tahun');
                populateDropdown('filterJurusan', await resJur.json(), 'kode_jurusan', 'nama_jurusan', 'Semua Jurusan');
                populateDropdown('filterKelas', await resKel.json(), 'kode_kelas', 'nama_kelas', 'Semua Kelas');
            } catch (e) { console.error("Gagal load master data:", e); }
        }
    
        function populateDropdown(id, data, valKey, textKey, defaultText) {
            const el = document.getElementById(id);
            if (!el) return;
            const items = Array.isArray(data) ? data : (data.data || []);
            el.innerHTML = `<option value="">${defaultText}</option>`;
            items.forEach(item => {
                el.insertAdjacentHTML('beforeend', `<option value="${item[valKey]}">${item[textKey]}</option>`);
            });
        }
    
        async function fetchRiwayatTransaksi() {
            const list = document.getElementById('transaksiList');
            const loader = document.getElementById('loadingIndicator');
    
            const params = new URLSearchParams({
                search: document.getElementById('filterSearch').value,
                jenis: document.getElementById('filterJenis').value,
                bulan: document.getElementById('filterBulan').value,
                kelas: document.getElementById('filterKelas').value,
                jurusan: document.getElementById('filterJurusan').value,
                tahun: document.getElementById('filterTahun').value
            });
    
            list.innerHTML = '';
            loader.classList.remove('hidden');
    
            try {
                const res = await fetch(`${BASE_URL}/transaksi/history-admin?${params.toString()}`, {
                    headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
                });
                const arr = await res.json();
                loader.classList.add('hidden');
    
                if (!arr || arr.length === 0) {
                    list.innerHTML = `<tr><td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">Tidak ada data transaksi.</td></tr>`;
                    return;
                }
                arr.forEach(t => renderRow(t, list));
            } catch (e) {
                loader.classList.add('hidden');
                list.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Error: ${e.message}</td></tr>`;
            }
        }
    
        function renderRow(t, container) {
            const jenis = t.kode_jenis;
            const isMasuk = jenis === 'SETOR';
            
            // Warna teks: Hijau untuk Masuk, Merah untuk Keluar
            const textColor = isMasuk ? 'text-green-600' : 'text-red-500';
            const badgeColor = isMasuk ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
            
            const formatIndo = new Intl.NumberFormat('id-ID');
            const nominal = parseFloat(t.jumlah) || 0;

            container.insertAdjacentHTML('beforeend', `
                <tr class="hover:bg-gray-50 border-b transition text-xs">
                    <td class="px-6 py-4">
                        <span class="font-bold">${t.tgl_transaksi ? new Date(t.tgl_transaksi).toLocaleDateString('id-ID') : '-'}</span><br>
                        <span class="text-[10px] text-gray-400">${t.kode_transaksi}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-bold text-gray-900">${t.nasabah?.nama || t.nama_saat_transaksi || 'N/A'}</span><br>
                        <span class="text-[10px] text-gray-500">${t.no_rekening}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 rounded-full text-[10px] font-black uppercase ${badgeColor}">
                            ${t.jenis_transaksi?.nama_jenis || jenis}
                        </span>
                    </td>
                    <td class="px-6 py-4 italic text-gray-500">${t.keterangan_nasabah || '-'}</td>
                    
                    <td class="px-6 py-4 font-bold text-right ${textColor}">
                        ${isMasuk ? '+' : '-'}Rp ${formatIndo.format(nominal)}
                    </td>
                    
                    <td class="px-6 py-4 text-center text-gray-600">
                        <span class="text-[10px] font-medium">${t.petugas?.nama_petugas || 'admin'}</span>
                    </td>
                </tr>
            `);
        }
    
        function cetakRekap() {
            const params = new URLSearchParams({
                search: document.getElementById('filterSearch').value,
                jenis: document.getElementById('filterJenis').value,
                bulan: document.getElementById('filterBulan').value,
                kelas: document.getElementById('filterKelas').value,
                jurusan: document.getElementById('filterJurusan').value,
                tahun: document.getElementById('filterTahun').value
            });
            window.open(`{{ url('/admin/transaksi/cetak-pdf') }}?${params.toString()}`, '_blank');
        }
    
        function resetFilter() {
            document.getElementById('filterForm').reset();
            const now = new Date();
            document.getElementById('filterBulan').value = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
            fetchRiwayatTransaksi();
        }
    </script>
@endsection