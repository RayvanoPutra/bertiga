<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Bank Mini</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h2>Masuk ke Akun</h2>

            <div id="error-message" style="color: red;"></div>

            <form id="loginForm"> 
                <div>
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required />
                </div>
            
                <div>
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required />
                </div>
            
                <button type="submit" id="loginButton">Login</button>
            </form>
            
            <script>
                document.getElementById('loginForm').addEventListener('submit', async function(event) {
                    event.preventDefault(); // Mencegah form submit tradisional
                    
                    document.getElementById('error-message').innerHTML = ''; // Clear error
                    const button = document.getElementById('loginButton');
                    button.disabled = true; // Nonaktifkan tombol saat proses

                    const username = document.getElementById('username').value;
                    const password = document.getElementById('password').value;
                    
                    // URL API harus merujuk ke route API yang Anda daftarkan: /api/login/petugas
                    const API_URL = '/api/login/petugas'; 

                    try {
                        const response = await fetch(API_URL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ username, password })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            // Jika respons bukan 200 (misal 422 Validasi error)
                            let errorMessage = 'Login gagal. Silakan coba lagi.';
                            if (data.errors && data.errors.username) {
                                errorMessage = data.errors.username[0]; // Ambil pesan error validasi
                            }
                            document.getElementById('error-message').innerHTML = `<p>${errorMessage}</p>`;
                            return;
                        }

                        // ✅ Login Berhasil
                        const token = data.access_token;
                        
                        // 1. Simpan token ke LocalStorage (cara standar API)
                        localStorage.setItem('sanctum_token', token);
                        
                        // 2. Arahkan pengguna ke dashboard (misalnya, /dashboard)
                        window.location.href = '/superadmin/dashboard';

                    } catch (error) {
                        console.error('Error:', error);
                        document.getElementById('error-message').innerHTML = '<p>Terjadi kesalahan jaringan.</p>';
                    } finally {
                        button.disabled = false; // Aktifkan kembali tombol
                    }
                });
            </script>
        </div>
    </div>
</body>
</html>