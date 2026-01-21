package com.example.bankminiviews.data.network

import com.google.gson.GsonBuilder
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

object ApiClient {

    // Pastikan IP ini sesuai dengan IP Laptop Anda saat ini (cek ipconfig)
    private const val BASE_URL = "http://192.168.30.253:8000/api/"

    val instance: ApiService by lazy {

        // 1. Logging (CCTV)
        val logging = HttpLoggingInterceptor()
        logging.setLevel(HttpLoggingInterceptor.Level.BODY)

        // 2. Client (Kendaraan) dengan Tambahan Header
        val client = OkHttpClient.Builder()
            // --- INI BAGIAN PENTING YANG BARU ---
            .addInterceptor { chain ->
                val request = chain.request().newBuilder()
                    .addHeader("Accept", "application/json") // <--- Paksa minta JSON
                    .build()
                chain.proceed(request)
            }
            // ------------------------------------
            .addInterceptor(logging)
            .connectTimeout(30, TimeUnit.SECONDS) // Tambah waktu tunggu (biar ga gampang timeout)
            .readTimeout(30, TimeUnit.SECONDS)
            .build()

        // 3. Gson yang "Sabar" (Lenient)
        val gson = GsonBuilder()
            .setLenient()
            .create()

        // 4. Retrofit
        val retrofit = Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create(gson))
            .client(client)
            .build()

        retrofit.create(ApiService::class.java)
    }
}