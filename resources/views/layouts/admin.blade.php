<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Bank Mini</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style> 
        body { font-family: 'Inter', sans-serif; }
        .sidebar-link { transition: all 0.2s; }
        .sidebar-link:hover, .sidebar-link.active { background-color: rgba(255, 255, 255, 0.1); border-left: 4px solid #60A5FA; }
        .sidebar-link.active { background-color: rgba(255, 255, 255, 0.15); }
        .modal { transition: opacity 0.25s ease; }
        body.modal-active { overflow-x: hidden; overflow-y: hidden !important; }
        
        /* Custom SweetAlert Style agar senada dengan Blue 900 */
        .swal2-styled.swal2-confirm { background-color: #1e3a8a !important; }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">

    <aside class="w-64 bg-blue-900 text-white flex flex-col shadow-2xl z-20 hidden md:flex flex-shrink-0">
        <div class="h-16 flex items-center justify-center border-b border-blue-800 shadow-sm">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-blue-800">
                <img src="{{ asset('images/logo_login.png') }}" 
                     alt="Logo Bank Mini" 
                     class="w-20 h-20 object-contain rounded-md p-1">
                <span class="text-white font-black tracking-tighter text-xl uppercase">Bank Mini</span>
            </div>
        </div>

        <nav class="flex-1 px-2 py-6 space-y-1 overflow-y-auto">
            <p class="px-4 text-xs font-semibold text-blue-300 uppercase tracking-wider mb-2">Menu Utama</p>
            
            <a href="{{ url('/admin/dashboard') }}" class="sidebar-link {{ request()->is('admin/dashboard') ? 'active' : '' }} flex items-center px-4 py-3 text-blue-100 hover:text-white rounded-r-full group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                Dashboard
            </a>

            <a href="{{ url('/admin/nasabah') }}" class="sidebar-link {{ request()->is('admin/nasabah') ? 'active' : '' }} flex items-center px-4 py-3 text-blue-100 hover:text-white rounded-r-full group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                Data Nasabah
            </a>

            <p class="px-4 text-xs font-semibold text-blue-300 uppercase tracking-wider mt-6 mb-2">Data Master</p>
            <a href="{{ url('/admin/tahunajaran') }}" class="sidebar-link {{ request()->is('admin/tahunajaran') ? 'active' : '' }} flex items-center px-4 py-3 text-blue-100 hover:text-white rounded-r-full group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                Tahun Ajaran
            </a>

            <a href="{{ url('/admin/jurusan') }}" class="sidebar-link {{ request()->is('admin/jurusan') ? 'active' : '' }} flex items-center px-4 py-3 text-blue-100 hover:text-white rounded-r-full group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                </svg>
                Jurusan
            </a>

            <a href="{{ url('/admin/kelas') }}" class="sidebar-link {{ request()->is('admin/kelas') ? 'active' : '' }} flex items-center px-4 py-3 text-blue-100 hover:text-white rounded-r-full group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Kelas
            </a>

            <a href="{{ url('/admin/akun') }}" class="sidebar-link {{ request()->is('admin/akun') ? 'active' : '' }} flex items-center px-4 py-3 text-blue-100 hover:text-white rounded-r-full group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Manajemen Akun
            </a>

            <a href="{{ url('/admin/pengaturan') }}" class="sidebar-link {{ request()->is('admin/pengaturan') ? 'active' : '' }} flex items-center px-4 py-3 text-blue-100 hover:text-white rounded-r-full group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Pengaturan Sistem
            </a>
        </nav>

        <div class="border-t border-blue-800 p-4 bg-blue-900 bg-opacity-50 mt-auto">
            <div class="flex items-center gap-3 mb-4">
                <div id="userInitials" class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm border-2 border-blue-300">
                    ADM
                </div>
                <div>
                    <p id="userName" class="text-sm font-bold text-white leading-none">Administrator</p>
                    <p class="text-[10px] text-blue-300 uppercase tracking-widest mt-1">Online</p>
                </div>
            </div>
            <button onclick="handleLogout()" class="w-full bg-red-600 hover:bg-red-700 text-white text-xs font-bold py-2.5 px-4 rounded transition flex items-center justify-center gap-2 shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                KELUAR SISTEM
            </button>
        </div>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 z-10 border-b border-gray-100">
            <h2 class="text-xl font-bold text-gray-800">@yield('header_title')</h2>
        </header>

        <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const role = localStorage.getItem('role');
            const name = localStorage.getItem('petugas_nama'); // Jika Anda menyimpan nama saat login

            // Tampilkan Nama & Inisial User
            if(name) {
                document.getElementById('userName').innerText = name;
                document.getElementById('userInitials').innerText = name.substring(0,2).toUpperCase();
            }

            // Role Based Menu Filter
            if (role !== 'superadmin') {
                const restrictedMenus = [
                    'Tahun Ajaran', 
                    'Jurusan', 
                    'Kelas', 
                    'Pengaturan Sistem', 
                    'Manajemen Akun'
                ];

                document.querySelectorAll('.sidebar-link').forEach(link => {
                    const menuText = link.innerText.trim();
                    if (restrictedMenus.includes(menuText)) {
                        link.remove();
                    }
                });

                document.querySelectorAll('p').forEach(p => {
                    if (p.innerText.toUpperCase().includes('DATA MASTER')) p.remove();
                });
            }

            // Global Flash Alert (Jika Anda menggunakan Session Laravel)
            @if(session('success'))
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", timer: 3000 });
            @endif

            @if(session('error'))
                Swal.fire({ icon: 'error', title: 'Gagal!', text: "{{ session('error') }}" });
            @endif
        });

        // Elegant Logout Function
        async function handleLogout() {
            const result = await Swal.fire({
                title: 'Konfirmasi Keluar',
                text: "Sesi Anda akan berakhir. Pastikan semua pekerjaan telah disimpan.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#1e3a8a',
                confirmButtonText: 'Ya, Keluar!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            });

            if (result.isConfirmed) {
                // Tampilkan loading saat proses logout
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang menghapus sesi keamanan',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading() }
                });

                const token = localStorage.getItem('petugas_token');
                const BASE_URL = "/api";

                try {
                    await fetch(`${BASE_URL}/logout`, {
                        method: 'POST',
                        headers: { 
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                } catch (e) {
                    console.error("Gagal kontak server, paksa logout...");
                } finally {
                    localStorage.clear(); // Bersihkan semua storage (token, role, nama)
                    window.location.href = "/admin/login";
                }
            }
        }
    </script>
</body>
</html>