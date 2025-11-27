🏦 Aplikasi Bank Mini Sekolah (Android)

Aplikasi mobile banking sederhana namun modern yang dibuat khusus untuk siswa sekolah. Aplikasi ini terintegrasi dengan backend Laravel untuk pengelolaan tabungan siswa secara real-time.

📱 Fitur Utama

1. Keamanan & Autentikasi

Login Siswa: Sistem login aman menggunakan Token (Bearer Auth).

Auto-Logout: Keamanan tambahan di mana sesi otomatis berakhir jika aplikasi ditutup (tidak ada keep-login yang berisiko).

2. Dashboard Modern

UI Bersih: Menggunakan Material Design dengan palet warna Biru & Putih.

Privasi Saldo: Fitur "Mata" untuk menyembunyikan/menampilkan nominal saldo.

Swipe Refresh: Tarik layar ke bawah untuk memperbarui saldo secara instan.

Menu Cepat: Akses mudah ke fitur Setor, Tarik, Riwayat, dan Laporan.

3. Transaksi & Mutasi

Request Setor Tunai: Formulir input nominal setor dengan validasi minimal (Rp 1.000).

Riwayat Transaksi: Daftar mutasi lengkap (Debit/Kredit) dengan detail (Tanggal, Jam, Keterangan).

Smart Filter: Filter canggih untuk mencari transaksi berdasarkan:

Jenis (Uang Masuk / Uang Keluar)

Rentang Tanggal (Custom Date Picker)

🛠️ Teknologi yang Digunakan

Bahasa: Kotlin

Arsitektur: Clean Architecture (Pemisahan UI, Data, dan Network)

Networking: Retrofit & Gson (Komunikasi dengan REST API Laravel)

UI Components:

Material Design 3

RecyclerView (untuk daftar dinamis)

SwipeRefreshLayout

Material Date Picker

Backend: Laravel (API, Database MySQL)

📸 Screenshot

(Anda bisa menambahkan screenshot aplikasi di sini nanti)

🚀 Cara Menjalankan

Clone repository ini.

Buka di Android Studio.

Pastikan file ApiClient.kt mengarah ke IP server Laravel yang benar.

Build dan Run di Emulator atau HP Android.

Dibuat sebagai Project Tugas Sekolah.