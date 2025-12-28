@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header_title', 'Dashboard Overview')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3 hover:shadow-md transition">
        <div class="p-2 bg-green-100 rounded-lg text-green-600 text-xl">💰</div>
        <div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Masuk Hari Ini</p>
            <h3 class="text-lg font-bold text-gray-800" id="statToday">Rp 0</h3>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3 hover:shadow-md transition">
        <div class="p-2 bg-red-100 rounded-lg text-red-600 text-xl">💸</div>
        <div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Keluar Hari Ini</p>
            <h3 class="text-lg font-bold text-gray-800" id="statOut">Rp 0</h3>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3 hover:shadow-md transition">
        <div class="p-2 bg-blue-100 rounded-lg text-blue-600 text-xl">🏦</div>
        <div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Total Aset (Kas)</p>
            <h3 class="text-lg font-bold text-gray-800" id="statTotal">Rp 0</h3>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3 hover:shadow-md transition">
        <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600 text-xl">👥</div>
        <div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Total Nasabah</p>
            <h3 class="text-lg font-bold text-gray-800" id="statNasabah">0</h3>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-3 hover:shadow-md transition">
        <div class="p-2 bg-orange-100 rounded-lg text-orange-600 text-xl">📊</div>
        <div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Total Transaksi</p>
            <h3 class="text-lg font-bold text-gray-800" id="statTrx">0</h3>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Perbandingan Arus Kas Bulanan</h3>
            <button onclick="loadDashboardData()" class="text-[10px] bg-gray-50 text-gray-500 px-2 py-1 rounded hover:bg-blue-600 hover:text-white transition uppercase font-bold">Refresh</button>
        </div>
        <div class="h-64">
            <canvas id="financeChart"></canvas>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Persetujuan Pending</h3>
            <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full font-bold" id="badgePendingCount">0</span>
        </div>
        <div class="flex-1 overflow-y-auto space-y-3 pr-1 max-h-64" id="notificationList">
            <div class="text-center py-10 text-gray-400 text-sm italic font-mono">Memuat antrean...</div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 italic">10 Transaksi Terakhir</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-left whitespace-nowrap">
            <thead class="bg-gray-50 text-gray-500 text-[10px] uppercase font-black">
                <tr>
                    <th class="px-6 py-3">No. Rekening</th>
                    <th class="px-6 py-3">Nama Nasabah</th>
                    <th class="px-6 py-3">Jumlah</th>
                    <th class="px-6 py-3">Tipe</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody id="todayTransactionList" class="divide-y divide-gray-100 text-sm text-gray-700">
                <tr><td colspan="5" class="text-center py-10 text-gray-400 italic">Memuat data transaksi...</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const BASE_URL = "{{ \Illuminate\Support\Facades\URL::to('/api') }}";
    const authToken = localStorage.getItem('petugas_token');

    if (!authToken) window.location.href = "{{ \Illuminate\Support\Facades\URL::to('/admin/login') }}";

    document.addEventListener('DOMContentLoaded', () => { loadDashboardData(); });

    async function loadDashboardData() {
        await Promise.all([
            fetchPending(),
            fetchTodayStats(),
            fetchTotalNasabah()
        ]);
    }

    // --- 1. AMBIL TRANSAKSI PENDING ---
    async function fetchPending() {
        try {
            const res = await fetch(`${BASE_URL}/transaksi/pending`, {
                headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
            });
            const data = await res.json();
            const list = document.getElementById('notificationList');
            const badge = document.getElementById('badgePendingCount');
            
            list.innerHTML = '';
            badge.innerText = data.length;

            if (data.length === 0) {
                list.innerHTML = '<div class="text-center py-10 text-gray-400 text-sm italic">Tidak ada pengajuan baru.</div>';
                return;
            }

            data.forEach(trx => {
                const isSetor = trx.jenis_transaksi.nama_jenis.toLowerCase().includes('setor');
                const nama = trx.nasabah ? trx.nasabah.nama : (trx.nama_saat_transaksi || 'Nasabah');
                
                list.insertAdjacentHTML('beforeend', `
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 shadow-sm transition hover:bg-white hover:border-blue-300">
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-xs font-black text-gray-800 uppercase">${nama}</p>
                            <span class="text-[8px] ${isSetor ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'} px-1.5 py-0.5 rounded font-black tracking-tighter">PENDING</span>
                        </div>
                        <p class="text-[10px] text-gray-500 mb-2 font-mono">${trx.jenis_transaksi.nama_jenis} - <b class="text-blue-600">${formatRupiah(trx.jumlah)}</b></p>
                        <div class="flex gap-2">
                            <button onclick="processTransaction('${trx.kode_transaksi}', 'approve')" class="flex-1 bg-blue-600 text-white text-[9px] font-bold py-1.5 rounded shadow-sm">SETUJU</button>
                            <button onclick="processTransaction('${trx.kode_transaksi}', 'reject')" class="flex-1 bg-gray-100 text-gray-500 text-[9px] font-bold py-1.5 rounded">TOLAK</button>
                        </div>
                    </div>
                `);
            });
        } catch (e) { console.error(e); }
    }

    // --- 2. AMBIL STATISTIK HARIAN & TABEL ---
    async function fetchTodayStats() {
        try {
            const res = await fetch(`${BASE_URL}/transaksi/history-admin`, {
                headers: { 'Authorization': `Bearer ${authToken}` }
            });
            const data = await res.json();
            const today = new Date().toISOString().slice(0, 10);
            
            let totalMasukHariIni = 0;
            let totalKeluarHariIni = 0;
            let totalTrxHariIni = 0;
            
            const tableBody = document.getElementById('todayTransactionList');
            tableBody.innerHTML = '';

            data.forEach((trx, idx) => {
                const isSetor = trx.jenis_transaksi.nama_jenis.toLowerCase().includes('setor');
                const isToday = trx.tgl_transaksi?.startsWith(today);
                
                // Hitung Statistik Harian
                if (isToday && trx.status === 'success') {
                    totalTrxHariIni++;
                    if (isSetor) totalMasukHariIni += parseInt(trx.jumlah);
                    else totalKeluarHariIni += parseInt(trx.jumlah);
                }

                // Tampilkan 10 transaksi terbaru di tabel
                if (idx < 10) {
                    const badgeColor = trx.status === 'success' ? 'bg-green-100 text-green-700' : (trx.status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-gray-100');
                    tableBody.insertAdjacentHTML('beforeend', `
                        <tr class="hover:bg-blue-50/30 border-b last:border-0 transition">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-blue-700 italic">${trx.no_rekening}</td>
                            <td class="px-6 py-4 text-xs font-bold">${trx.nasabah ? trx.nasabah.nama : (trx.nama_saat_transaksi || '-')}</td>
                            <td class="px-6 py-4 font-black ${isSetor ? 'text-green-600' : 'text-red-600'} text-xs">${isSetor ? '+' : '-'}${formatRupiah(trx.jumlah)}</td>
                            <td class="px-6 py-4 text-[10px] font-black uppercase text-gray-500">${trx.jenis_transaksi.nama_jenis}</td>
                            <td class="px-6 py-4"><span class="${badgeColor} text-[9px] px-2 py-0.5 rounded-full font-black uppercase">${trx.status}</span></td>
                        </tr>
                    `);
                }
            });

            document.getElementById('statToday').innerText = formatRupiah(totalMasukHariIni);
            document.getElementById('statOut').innerText = formatRupiah(totalKeluarHariIni);
            document.getElementById('statTrx').innerText = totalTrxHariIni;
            
            renderChart(data); // Render grafik

        } catch (e) { console.error("Gagal history:", e); }
    }

    // --- 3. AMBIL TOTAL NASABAH & TOTAL ASET ---
    async function fetchTotalNasabah() {
        try {
            const res = await fetch(`${BASE_URL}/master/nasabah`, {
                headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
            });
            const data = await res.json();
            document.getElementById('statNasabah').innerText = data.length;
            
            // Hitung Total Aset (Jumlah Saldo Semua Nasabah)
            const totalSaldo = data.reduce((sum, n) => sum + parseInt(n.saldo), 0);
            document.getElementById('statTotal').innerText = formatRupiah(totalSaldo);
        } catch (e) { console.error("Gagal nasabah:", e); }
    }

    // --- GRAFIK PEMASUKAN VS PENGELUARAN (DINAMIS) ---
    let myChart = null;
    function renderChart(allTransactions) {
        const ctx = document.getElementById('financeChart').getContext('2d');
        if (myChart) myChart.destroy(); // Hapus chart lama

        const monthlyIncome = new Array(12).fill(0);
        const monthlyOutcome = new Array(12).fill(0);

        // Kelompokkan data transaksi sukses per bulan
        allTransactions.forEach(t => {
            if (t.status === 'success' && t.tgl_transaksi) {
                const month = new Date(t.tgl_transaksi).getMonth();
                const isSetor = t.jenis_transaksi.nama_jenis.toLowerCase().includes('setor');
                
                if (isSetor) monthlyIncome[month] += parseInt(t.jumlah);
                else monthlyOutcome[month] += parseInt(t.jumlah);
            }
        });

        myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [
                    {
                        label: 'Pemasukan (Setoran)',
                        data: monthlyIncome,
                        backgroundColor: '#10b981', // Hijau
                        borderRadius: 4
                    },
                    {
                        label: 'Pengeluaran (Penarikan)',
                        data: monthlyOutcome,
                        backgroundColor: '#ef4444', // Merah
                        borderRadius: 4
                    }
                ]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { legend: { position: 'bottom', labels: { font: { size: 10, weight: 'bold' } } } },
                scales: { 
                    y: { 
                        beginAtZero: true, 
                        ticks: { font: { size: 9 }, callback: v => 'Rp ' + v.toLocaleString('id-ID') } 
                    },
                    x: { ticks: { font: { size: 10, weight: 'bold' } } }
                }
            }
        });
    }

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    }

    async function processTransaction(kode, action) {
        if (!confirm(`Konfirmasi ${action.toUpperCase()} transaksi?`)) return;
        try {
            const res = await fetch(`${BASE_URL}/transaksi/${action}/${kode}`, {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
            });
            if (res.ok) { loadDashboardData(); } 
            else { alert("Gagal memproses."); }
        } catch (e) { alert("Error sistem."); }
    }
</script>
@endpush