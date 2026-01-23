<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Petugas - Bank Mini</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-sm">
        <h1 class="text-2xl font-bold text-center text-blue-900 mb-6">Login Petugas</h1>
        
        <form id="loginForm" class="space-y-4">
            <!-- Input URL Server (Untuk Development, bisa disembunyikan nanti) -->
            <div>
                <!-- <label class="block text-xs font-bold text-gray-500 uppercase">Alamat API Server</label> -->
                <!-- PERBAIKAN: Menggunakan Facade URL::to -->
                <input type="hidden" id="serverUrl" class="mt-1 w-full p-2 border rounded text-sm font-mono bg-gray-50" value="{{ \Illuminate\Support\Facades\URL::to('/api') }}" required>
                <!-- <p class="text-xs text-gray-400 mt-1">Otomatis terisi alamat server ini.</p> -->
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="superadmin" required>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Password" required>
            </div>
            
            <button type="submit" id="btnLogin" class="w-full bg-blue-700 text-white p-3 rounded-lg font-bold hover:bg-blue-800 transition transform active:scale-95">
                MASUK
            </button>
        </form>
        
        <div id="loginError" class="bg-red-100 text-red-700 p-3 rounded mt-4 hidden text-sm border border-red-200"></div>
    </div>

    <script>
    const DEFAULT_API_URL = "{{ \Illuminate\Support\Facades\URL::to('/api') }}"; 
    // Ubah tujuan ke dashboard utama agar lebih profesional
    const DASHBOARD_URL = "{{ \Illuminate\Support\Facades\URL::to('/admin/dashboard') }}"; 
    
    const savedUrl = localStorage.getItem('api_url') || DEFAULT_API_URL;
    document.getElementById('serverUrl').value = savedUrl;

    // Cek Login: Kalau sudah ada token, langsung masuk
    if (localStorage.getItem('petugas_token')) {
        window.location.href = DASHBOARD_URL; 
    }

    function getBaseUrl() { 
        return document.getElementById('serverUrl').value.replace(/\/$/, ""); 
    }

    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btnLogin');
        const errorContainer = document.getElementById('loginError');
        
        const urlInput = getBaseUrl();
        localStorage.setItem('api_url', urlInput);

        btn.disabled = true; 
        btn.innerText = "Memproses...";
        errorContainer.classList.add('hidden');

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        try {
            // Kita gunakan rute login langsung agar lebih cepat
            const targetUrl = `${urlInput}/login/petugas`;
            
            const response = await fetch(targetUrl, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json' 
                },
                body: JSON.stringify({ username, password })
            });

            const data = await response.json();

            if (response.ok) {
                // MENYIMPAN SEMUA DATA YANG DIBUTUHKAN
                localStorage.setItem('petugas_token', data.access_token);
                localStorage.setItem('role', data.role); 
                localStorage.setItem('nama_petugas', data.user.nama_petugas);

                window.location.href = DASHBOARD_URL; 
            } else {
                throw new Error(data.message || 'Login Gagal.');
            }
        } catch (error) {
            errorContainer.innerText = error.message;
            errorContainer.classList.remove('hidden');
        } finally {
            btn.disabled = false; 
            btn.innerText = "MASUK";
        }
    });
</script>
</body>
</html>