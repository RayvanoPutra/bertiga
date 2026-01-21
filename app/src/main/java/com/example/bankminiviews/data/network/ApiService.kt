package com.example.bankminiviews.data.network

import com.example.bankminiviews.data.model.ForgotPasswordRequest
import com.example.bankminiviews.data.model.HistoryItemResponse
import com.example.bankminiviews.data.model.LaporanResponse
import com.example.bankminiviews.data.model.LoginRequest
import com.example.bankminiviews.data.model.LoginResponse
import com.example.bankminiviews.data.model.NasabahData
import com.example.bankminiviews.data.model.OtpRequest
import com.example.bankminiviews.data.model.ResetPasswordRequest
import com.example.bankminiviews.data.model.SetorRequest
import com.example.bankminiviews.data.model.SetorResponse
import com.example.bankminiviews.data.model.TarikRequest
import com.example.bankminiviews.data.model.UpdateProfileRequest
import com.example.bankminiviews.data.model.VerifyOtpRequest
import retrofit2.Call
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.Header
import retrofit2.http.POST
import retrofit2.http.PUT
import okhttp3.ResponseBody
import retrofit2.http.Query
import retrofit2.http.Streaming

/**
 * ApiService adalah "Buku Menu" aplikasi.
 * Interface ini mendefinisikan semua "pesanan" (request) yang bisa dikirim aplikasi ke server.
 * Retrofit akan membaca interface ini dan menjalankan request yang sesuai.
 */
interface ApiService {

    /**
     * Menu 1: LOGIN NASABAH
     * ---------------------
     * - Metode: POST (Mengirim data rahasia)
     * - Alamat: /api/login/nasabah
     * - Input: LoginRequest (no_rekening & password) di dalam BODY (isi paket)
     * - Output: Call<LoginResponse> (Janji balasan berisi token & status)
     */
    @POST("login/nasabah")
    fun loginNasabah(
        @Body request: LoginRequest
    ): Call<LoginResponse>

    /**
     * Menu 2: AMBIL PROFIL NASABAH
     * ----------------------------
     * - Metode: GET (Meminta data, tidak mengirim data baru)
     * - Alamat: /api/user
     * - Input: Token (KTP Digital) di dalam HEADER (amplop luar)
     * Server butuh token ini untuk tahu SIAPA yang sedang login.
     * - Output: Call<NasabahData> (Janji balasan berisi nama, saldo, no rek)
     */
    @GET("user")
    fun getNasabahData(
        @Header("Authorization") token: String
    ): Call<NasabahData>

    /**
     * Menu 3: REQUEST SETOR TUNAI
     * ---------------------------
     * - Metode: POST (Mengirim data transaksi baru)
     * - Alamat: /api/transaksi/request
     * (Sesuai route di Laravel: prefix 'transaksi' + '/request')
     * - Input 1: Token di HEADER (Supaya server tahu akun mana yang setor)
     * - Input 2: SetorRequest di BODY (Berisi jumlah uang & jenis transaksi)
     * - Output: Call<SetorResponse> (Janji balasan berisi pesan sukses/gagal)
     */
    @POST("transaksi/request-setor")
    fun requestSetor(
        @Header("Authorization") token: String,
        @Body request: SetorRequest
    ): Call<SetorResponse>

    /**
     * Menu 4: REQUEST TARIK TUNAI
     * ---------------------------
     * - Metode: POST (Mengirim data transaksi baru)
     * - Alamat: /api/transaksi/request
     * - Input 2: TarikRequest di BODY (Berisi jumlah uang, jenis transaksi, dan keterangan)
     */
    @POST("transaksi/request-tarik")
    fun requestTarik(
        @Header("Authorization") token: String,
        @Body request: TarikRequest
    ): Call<SetorResponse>

    /**
     * Menu 5: AMBIL RIWAYAT TRANSAKSI
     * -------------------------------
     * - Metode: GET (Meminta daftar data)
     * - Alamat: /api/transaksi/history
     * - Input: Token di HEADER
     * - Output: Call<List<HistoryItemResponse>>
     * Perhatikan kata 'List'. Ini artinya server akan mengirim BANYAK data (Daftar),
     * bukan cuma satu. Makanya kita pakai List<...>.
     */
    @GET("transaksi/history")
    fun getHistory(
        @Header("Authorization") token: String
    ): Call<List<HistoryItemResponse>>

    // 1. Minta Kode OTP Laporan
    // --- FITUR CETAK LAPORAN KEUANGAN (TANPA OTP) ---

    /**
     * Menu 6: UNDUH LAPORAN PDF (RENTANG BULAN)
     * - Alamat: /api/transaksi/cetak-laporan
     * - Parameter: bulan_mulai & bulan_selesai (Contoh: "Januari" ke "Maret")
     * - Output: ResponseBody (Karena kita mengunduh file biner berupa PDF)[cite: 328, 360].
     */
    @GET("transaksi/cetak-laporan")
    @Streaming // Digunakan agar Retrofit tidak memuat seluruh file besar ke memori sekaligus
    fun downloadLaporan(
        @Header("Authorization") token: String,
        @Query("bulan_mulai") bulanMulai: String,
        @Query("bulan_selesai") bulanSelesai: String
    ): Call<ResponseBody>

    // --- UPDATE PROFIL ---

    @PUT("nasabah/update")
    fun updateProfile(
        @Header("Authorization") token: String,
        @Body request: UpdateProfileRequest
    ): Call<SetorResponse>


    // --- FITUR LUPA PASSWORD (NO TOKEN NEEDED) ---

    // 1. Request OTP Lupa Password
    @POST("forgot-password/request")
    fun requestForgotPasswordOtp(
        @Body request: ForgotPasswordRequest
    ): Call<SetorResponse>

    // 2. Verifikasi OTP Lupa Password
    @POST("forgot-password/verify")
    fun verifyForgotPasswordOtp(
        @Body request: VerifyOtpRequest
    ): Call<SetorResponse>

    // 3. Reset Password Baru
    @POST("forgot-password/reset")
    fun resetPassword(
        @Body request: ResetPasswordRequest
    ): Call<SetorResponse>

}