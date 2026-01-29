<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Petugas - Bank Mini</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-sm">
        <h1 class="text-2xl font-bold text-center text-blue-900 mb-6">Login Petugas</h1>

        <form id="loginForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username"
                    class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                    placeholder="Username" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password"
                    class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                    placeholder="Password" required>
            </div>

            <button type="submit" id="btnLogin"
                class="w-full bg-blue-700 text-white p-3 rounded-lg font-bold hover:bg-blue-800 transition transform active:scale-95">
                MASUK
            </button>
        </form>

        <div id="loginError" class="bg-red-100 text-red-700 p-3 rounded mt-4 hidden text-sm border border-red-200">
        </div>
    </div>

    <script>
        // --- KONFIGURASI URL ---
        const API_BASE_URL = "{{ \Illuminate\Support\Facades\URL::to('/api') }}";
        const DASHBOARD_URL = "{{ \Illuminate\Support\Facades\URL::to('/admin/dashboard') }}";

        // 1. Cek Login: Kalau sudah ada token, langsung lempar ke dashboard
        if (localStorage.getItem('petugas_token')) {
            window.location.href = DASHBOARD_URL;
        }

        // 2. Logic Login
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const btn = document.getElementById('btnLogin');
            const errorContainer = document.getElementById('loginError');
            const usernameInput = document.getElementById('username').value;
            const passwordInput = document.getElementById('password').value;

            // UI Loading State
            btn.disabled = true;
            btn.innerText = "Memproses...";
            errorContainer.classList.add('hidden');

            try {
                // Fetch ke API Login
                const response = await fetch(`${API_BASE_URL}/auth/petugas/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        username: usernameInput,
                        password: passwordInput
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    // --- SUKSES ---
                    // Simpan Token & Data User
                    localStorage.setItem('petugas_token', data.access_token);
                    localStorage.setItem('role', data.user.role);
                    localStorage.setItem('nama_petugas', data.user.nama_petugas);

                    // Redirect
                    window.location.href = DASHBOARD_URL;
                } else {
                    // --- GAGAL (401/422) ---
                    throw new Error(data.message || data.error || 'Login Gagal. Periksa Username/Password.');
                }
            } catch (error) {
                // Tampilkan Error di Kotak Merah
                errorContainer.innerText = error.message;
                errorContainer.classList.remove('hidden');
            } finally {
                // Reset Tombol
                btn.disabled = false;
                btn.innerText = "MASUK";
            }
        });
    </script>
</body>

</html>
