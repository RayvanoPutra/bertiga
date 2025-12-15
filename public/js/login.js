// Setelah login berhasil, simpan token di localStorage
localStorage.setItem('access_token', response.data.access_token);

// Untuk request ke halaman yang dilindungi (seperti dashboard)
axios.get('/dashboard', {
    headers: {
        'Authorization': `Bearer ${localStorage.getItem('access_token')}`
    }
})
.then(response => {
    // Lakukan sesuatu setelah dashboard berhasil dimuat
})
.catch(error => {
    console.error('Akses ditolak:', error);
});