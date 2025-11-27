package com.example.bankminiviews.data.network

import com.example.bankminiviews.data.model.LoginRequest
import com.example.bankminiviews.data.model.LoginResponse
import com.example.bankminiviews.data.model.NasabahData
import com.example.bankminiviews.data.model.SetorRequest
import com.example.bankminiviews.data.model.SetorResponse
// (Nanti Anda perlu import model untuk Tarik dan History)
// import com.example.bankminiviews.data.model.TarikRequest
// import com.example.bankminiviews.data.model.HistoryResponse
import com.example.bankminiviews.data.model.HistoryItemResponse
import retrofit2.Call
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.Header
import retrofit2.http.POST

interface ApiService {

    @POST("login/nasabah")
    fun loginNasabah(
        @Body request: LoginRequest
    ): Call<LoginResponse>

    @GET("user")
    fun getNasabahData(
        @Header("Authorization") token: String
    ): Call<NasabahData>

    // --- ALAMAT INI TELAH DIPERBARUI ---
    // Sesuai dengan Route::prefix('transaksi')->group
    // dan Route::post('/request-setor', ...)
    @POST("transaksi/request-setor")
    fun requestSetor(
        @Header("Authorization") token: String,
        @Body request: SetorRequest
    ): Call<SetorResponse>

    @GET("transaksi/history")
    fun getHistory(
        @Header("Authorization") token: String
    ): Call<List<HistoryItemResponse>> // <-- Kita minta DAFTAR (List) dari HistoryItem

    // --- Rute lain dari file routes/api.php (bisa Anda gunakan nanti) ---

    // (Anda akan butuh TarikRequest.kt dan TarikResponse.kt nanti)
    // @POST("transaksi/request-tarik")
    // fun requestTarik(
    //     @Header("Authorization") token: String,
    //     @Body request: TarikRequest
    // ): Call<TarikResponse>

    // (Anda akan butuh HistoryResponse.kt nanti)
    // @GET("transaksi/history")
    // fun getHistory(
    //     @Header("Authorization") token: String
    // ): Call<List<HistoryResponse>>
}