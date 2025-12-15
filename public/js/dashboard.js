document.addEventListener('DOMContentLoaded', function() {
  const token = localStorage.getItem('sanctum_token');
  const welcomeMessage = document.getElementById('welcome-message');
  const logoutButton = document.getElementById('logoutButton');

  if (!token) {
      window.location.href = '/login';
      return;
  }

  // Fungsi untuk Mengambil Data Pengguna dan Menampilkan Pesan Selamat Datang
  fetch('/api/user', {
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
      welcomeMessage.innerHTML = `Selamat Datang, ${user.nama_petugas} (${user.role})!`;
  })
  .catch(error => {
      console.error('Gagal mengambil data pengguna:', error);
      welcomeMessage.innerText = 'Gagal memuat data pengguna.';
  });
  
  // Logika Logout
  logoutButton.addEventListener('click', async function(e) {
      e.preventDefault();
      // Logic logout sama seperti yang saya berikan di respons sebelumnya (API call, remove token, redirect)
      try {
          await fetch('/api/logout', { method: 'POST', headers: {'Authorization': `Bearer ${token}`} });
      } catch (err) {
          console.error('Logout API call failed, but clearing local storage.');
      } finally {
          localStorage.removeItem('sanctum_token');
          window.location.href = '/login';
      }
  });
});


// File: resources/views/super_admin/dashboard.blade.php (di dalam <script>)

document.addEventListener('DOMContentLoaded', function() {
// ... (Kode autentikasi dan logout Anda yang sudah ada) ...

const sidebarToggler = document.getElementById('sidebar-toggler');
const appContainer = document.getElementById('app-container');
const togglerIcon = sidebarToggler.querySelector('i');

// LOGIC SIDEBAR TOGGLE
sidebarToggler.addEventListener('click', function() {
// Menambahkan/menghapus class 'minimized' pada container utama
appContainer.classList.toggle('minimized');

// Mengganti ikon panah untuk feedback visual
if (appContainer.classList.contains('minimized')) {
  togglerIcon.classList.replace('bi-arrow-left-square-fill', 'bi-arrow-right-square-fill');
} else {
  togglerIcon.classList.replace('bi-arrow-right-square-fill', 'bi-arrow-left-square-fill');
}
});

});