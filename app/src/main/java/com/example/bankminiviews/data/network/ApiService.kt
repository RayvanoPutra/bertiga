package com.example.bankminiviews.data.network

import com.example.bankminiviews.data.model.HistoryItemResponse
import com.example.bankminiviews.data.model.LoginRequest
import com.example.bankminiviews.data.model.LoginResponse
import com.example.bankminiviews.data.model.NasabahData
import com.example.bankminiviews.data.model.SetorRequest
import com.example.bankminiviews.data.model.SetorResponse
import com.example.bankminiviews.data.model.TarikRequest
import com.example.bankminiviews.data.model.OtpRequest
import com.example.bankminiviews.data.model.LaporanResponse
import retrofit2.Call
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.Header
import retrofit2.http.POST

interface ApiService {

    @POST("login/nasabah")
    fun loginNasabah(@Body request: LoginRequest): Call<LoginResponse>

    @GET("user")
    fun getNasabahData(@Header("Authorization") token: String): Call<NasabahData>

    // --- PERBAIKAN URL DI SINI ---
    // Dulu: "transaksi/request-setor"
    // Sekarang: "transaksi/request" (Sesuai routes/api.php teman Anda)
    @POST("transaksi/request")
    fun requestSetor(
        @Header("Authorization") token: String,
        @Body request: SetorRequest
    ): Call<SetorResponse>

    // --- PERBAIKAN URL DI SINI JUGA ---
    // Tarik tunai juga menggunakan pintu yang sama ("/request")
    // Server membedakan berdasarkan 'kode_jenis' yang kita kirim ("SETOR" atau "TARIK")
    @POST("transaksi/request")
    fun requestTarik(
        @Header("Authorization") token: String,
        @Body request: TarikRequest
    ): Call<SetorResponse> // Format balasan sama

    @GET("transaksi/history")
    fun getHistory(@Header("Authorization") token: String): Call<List<HistoryItemResponse>>

    // 1. Minta Kode OTP
    @POST("laporan/request-otp")
    fun requestOtp(
        @Header("Authorization") token: String
    ): Call<SetorResponse> // Kita pakai SetorResponse karena isinya cuma 'message'

    // 2. Kirim OTP untuk dapat URL PDF
    @POST("laporan/verify")
    fun verifyLaporanOtp(
        @Header("Authorization") token: String,
        @Body request: OtpRequest
    ): Call<LaporanResponse>
}