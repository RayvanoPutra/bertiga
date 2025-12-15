document.addEventListener('DOMContentLoaded', function() {
    
    // ====================================================================
    // --- 1. OTENTIKASI DAN SETUP AWAL ---
    // ====================================================================

    const token = localStorage.getItem('sanctum_token');
    const welcomeMessage = document.getElementById('welcome-message');
    const logoutButton = document.getElementById('logoutButton');
    const apiBaseUrl = '/api'; // Menggunakan relative path jika di domain yang sama
    
    if (!token) {
        window.location.href = '/login';
        return;
    }

    // --- HELPER FUNCTION ---
    const formatRupiah = (angka) => {
        if (isNaN(angka) || angka === null) return 'Rp. 0';
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    };

    // Fungsi untuk Mengambil Data Pengguna dan Menampilkan Pesan Selamat Datang
    fetch(`${apiBaseUrl}/user`, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (response.status === 401) {
            localStorage.removeItem('sanctum_token');
            window.location.href = '/login';
            return Promise.reject('Unauthorized');
        }
        return response.json();
    })
    .then(user => {
        // Asumsi user memiliki properti nama_petugas dan role
        welcomeMessage.innerHTML = `Selamat Datang, ${user.nama_petugas} (${user.role})!`;
    })
    .catch(error => {
        console.error('Gagal mengambil data pengguna:', error);
        welcomeMessage.innerText = 'Gagal memuat data pengguna.';
    });
    
    // Logika Logout
    if (logoutButton) {
        logoutButton.addEventListener('click', async function(e) {
            e.preventDefault();
            try {
                await fetch(`${apiBaseUrl}/logout`, { 
                    method: 'POST', 
                    headers: {'Authorization': `Bearer ${token}`} 
                });
            } catch (err) {
                console.error('Logout API call failed, but clearing local storage.');
            } finally {
                localStorage.removeItem('sanctum_token');
                window.location.href = '/login';
            }
        });
    }

    // ====================================================================
    // --- 2. LOGIKA DASHBOARD METRICS ---
    // ====================================================================
    
    function loadDashboardMetrics() {
        fetch(`${apiBaseUrl}/dashboard/metrics`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(dataResponse => {
            if (dataResponse.status === 'success' && dataResponse.data) {
                const data = dataResponse.data;

                // --- UPDATE SEMUA METRIC BOXES ---
                
                // 1. Tabungan Hari Ini (metric-green)
                const tabunganHariIni = document.getElementById('tabungan-hari-ini-value');
                if (tabunganHariIni) tabunganHariIni.innerText = formatRupiah(data.tabungan_hari_ini);

                // 2. Total Tabungan Keseluruhan (metric-blue)
                const totalTabungan = document.getElementById('total-tabungan-value');
                if (totalTabungan) totalTabungan.innerText = formatRupiah(data.total_tabungan);

                // 3. Jumlah Nasabah (metric-purple)
                const jumlahNasabah = document.getElementById('jumlah-nasabah-value');
                if (jumlahNasabah) jumlahNasabah.innerText = data.jumlah_nasabah.toLocaleString('id-ID'); 

                // 4. Jumlah Transaksi Hari Ini (metric-orange)
                const jumlahTransaksi = document.getElementById('jumlah-transaksi-value');
                if (jumlahTransaksi) jumlahTransaksi.innerText = data.jumlah_transaksi.toLocaleString('id-ID');
                
            } else {
                console.error('Respon API Dashboard tidak valid:', dataResponse);
            }
        })
        .catch(error => {
            console.error('Gagal memuat metrik dashboard:', error);
            // Menampilkan status gagal di semua metrik
            document.querySelectorAll('.metric-value').forEach(el => el.innerText = 'Gagal');
        });
    }

    // ====================================================================
    // --- 3. LOGIKA SIDEBAR & INISIALISASI ---
    // ====================================================================

    const sidebarToggler = document.getElementById('sidebar-toggler');
    const appContainer = document.getElementById('app-container');
    const togglerIcon = sidebarToggler ? sidebarToggler.querySelector('i') : null;

    // LOGIC SIDEBAR TOGGLE
    if (sidebarToggler && appContainer && togglerIcon) {
        sidebarToggler.addEventListener('click', function() {
            appContainer.classList.toggle('minimized');
            if (appContainer.classList.contains('minimized')) {
                togglerIcon.classList.replace('bi-arrow-left-square-fill', 'bi-arrow-right-square-fill');
            } else {
                togglerIcon.classList.replace('bi-arrow-right-square-fill', 'bi-arrow-left-square-fill');
            }
        });
    }
    
    // Panggil fungsi untuk memuat metrik saat DOMContentLoaded
    loadDashboardMetrics(); 

}); // END document.addEventListener('DOMContentLoaded', function() {