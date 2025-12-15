<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Petugas - Bank Mini</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- LOGIN PAGE -->
    <div id="loginPage" class="flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-sm">
            <h1 class="text-2xl font-bold text-center text-blue-900 mb-6">Login Petugas</h1>
            
            <form id="loginForm" class="space-y-4">
                <!-- Input URL Server -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase">Alamat API Server</label>
                    <input type="text" id="serverUrl" class="mt-1 w-full p-2 border rounded text-sm font-mono bg-gray-50" required>
                    <p class="text-xs text-gray-400 mt-1">Otomatis terisi alamat server ini.</p>
                </div>

                <input type="text" id="username" class="w-full p-3 border rounded-lg" placeholder="Username (superadmin)" required>
                <input type="password" id="password" class="w-full p-3 border rounded-lg" placeholder="Password" required>
                
                <button type="submit" id="btnLogin" class="w-full bg-blue-700 text-white p-3 rounded-lg font-bold hover:bg-blue-800 transition">
                    MASUK
                </button>
            </form>
            <div id="loginError" class="bg-red-100 text-red-700 p-3 rounded mt-4 hidden text-sm"></div>
        </div>
    </div>

    <!-- DASHBOARD PAGE -->
    <div id="dashboardPage" class="hidden">
        <nav class="bg-blue-800 text-white p-4 sticky top-0 z-50 shadow-md">
            <div class="max-w-4xl mx-auto flex justify-between items-center">
                <h1 class="text-xl font-bold">Bank Mini Admin</h1>
                <div class="flex items-center gap-4">
                    <span id="serverIndicator" class="text-xs bg-blue-900 px-2 py-1 rounded text-gray-300 font-mono">-</span>
                    <button onclick="logout()" class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm">Keluar</button>
                </div>
            </div>
        </nav>

        <div class="max-w-4xl mx-auto p-4 mt-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Transaksi Pending</h2>
                <button onclick="fetchPendingTransactions()" class="bg-white border text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 shadow-sm">
                    🔄 Refresh
                </button>
            </div>

            <div id="loadingIndicator" class="hidden text-center py-10 text-gray-500">Memuat data...</div>
            <div id="emptyMessage" class="hidden text-center py-20 bg-white rounded-xl shadow-sm text-gray-400">
                Tidak ada transaksi yang perlu disetujui.
            </div>

            <div id="transactionList" class="space-y-4"></div>
        </div>
    </div>

    <script>
        // Otomatis Deteksi URL
        const origin = window.location.origin; 
        const DEFAULT_URL = origin + "/api";   
        
        const savedUrl = localStorage.getItem('api_url') || DEFAULT_URL;
        document.getElementById('serverUrl').value = savedUrl;

        let authToken = localStorage.getItem('petugas_token');
        
        function getBaseUrl() { 
            return document.getElementById('serverUrl').value.replace(/\/$/, ""); 
        }

        if (authToken) showDashboard();

        // --- LOGIN ---
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnLogin');
            const errorContainer = document.getElementById('loginError');
            
            const urlInput = document.getElementById('serverUrl').value.replace(/\/$/, "");
            localStorage.setItem('api_url', urlInput);

            btn.disabled = true; btn.innerText = "Loading...";
            errorContainer.classList.add('hidden');

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            try {
                const response = await fetch(`${getBaseUrl()}/login/petugas`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ username, password })
                });

                const data = await response.json();

                if (response.ok) {
                    authToken = data.access_token;
                    localStorage.setItem('petugas_token', authToken);
                    showDashboard();
                } else {
                    throw new Error(data.message || 'Login Gagal');
                }
            } catch (error) {
                console.error(error);
                errorContainer.innerHTML = "Error: " + error.message;
                errorContainer.classList.remove('hidden');
            } finally {
                btn.disabled = false; btn.innerText = "MASUK";
            }
        });

        function showDashboard() {
            document.getElementById('loginPage').classList.add('hidden');
            document.getElementById('dashboardPage').classList.remove('hidden');
            document.getElementById('serverIndicator').innerText = getBaseUrl();
            fetchPendingTransactions();
        }

        function logout() {
            localStorage.removeItem('petugas_token');
            location.reload();
        }

        async function fetchPendingTransactions() {
            const list = document.getElementById('transactionList');
            const loader = document.getElementById('loadingIndicator');
            const empty = document.getElementById('emptyMessage');

            list.innerHTML = ''; loader.classList.remove('hidden'); empty.classList.add('hidden');

            try {
                const response = await fetch(`${getBaseUrl()}/transaksi/pending`, {
                    method: 'GET',
                    headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
                });

                if (response.status === 401) { logout(); return; }

                const data = await response.json();
                console.log("Data Transaksi:", data); // Debugging: Lihat isi data di Console Browser

                loader.classList.add('hidden');

                if (data.length === 0) {
                    empty.classList.remove('hidden');
                    return;
                }

                data.forEach(trx => {
                    list.appendChild(createCard(trx));
                });

            } catch (error) {
                loader.classList.add('hidden');
                alert("Gagal ambil data: " + error.message);
            }
        }

        function createCard(trx) {
            const div = document.createElement('div');
            div.className = "bg-white p-6 rounded-xl shadow-md border-l-4 border-yellow-400 flex flex-col md:flex-row justify-between gap-4";
            
            const fmtMoney = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(trx.jumlah);
            let typeLabel = trx.jenis_transaksi ? trx.jenis_transaksi.nama_jenis : 'Transaksi';
            
            // --- PERBAIKAN LOGIKA NAMA ---
            // 1. Cek nama_saat_transaksi (Snapshot)
            // 2. Jika kosong, cek nasabah.nama (Relasi)
            // 3. Jika kosong semua, pakai fallback "Nasabah Tidak Dikenal"
            let namaNasabah = trx.nama_saat_transaksi;
            if (!namaNasabah && trx.nasabah) {
                namaNasabah = trx.nasabah.nama;
            }
            if (!namaNasabah) {
                namaNasabah = "Nasabah Tidak Dikenal";
            }
            // -----------------------------
            
            div.innerHTML = `
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded font-mono font-bold">${trx.kode_transaksi}</span>
                        <span class="text-blue-600 font-bold text-sm uppercase">${typeLabel}</span>
                    </div>
                    <h3 class="text-lg font-bold">${namaNasabah}</h3>
                    <p class="text-gray-500 text-sm">Rek: ${trx.no_rekening}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">${fmtMoney}</p>
                    <p class="text-xs text-gray-400 mt-1">Ket: ${trx.keterangan_nasabah || '-'}</p>
                </div>
                <div class="flex gap-2 items-center">
                    <button onclick="processTransaction('${trx.kode_transaksi}', 'reject')" class="bg-red-100 text-red-700 px-4 py-2 rounded-lg font-bold hover:bg-red-200">TOLAK</button>
                    <button onclick="processTransaction('${trx.kode_transaksi}', 'approve')" class="bg-green-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-green-700 shadow">SETUJUI</button>
                </div>
            `;
            return div;
        }

        async function processTransaction(kode, action) {
            if (!confirm(`Yakin ingin ${action.toUpperCase()} transaksi ini?`)) return;

            try {
                const response = await fetch(`${getBaseUrl()}/transaksi/${action}/${kode}`, {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
                });
                
                const result = await response.json();
                if (response.ok) {
                    fetchPendingTransactions();
                } else {
                    throw new Error(result.message || "Gagal");
                }
            } catch (error) {
                alert("Error: " + error.message);
            }
        }
    </script>
</body>
</html>