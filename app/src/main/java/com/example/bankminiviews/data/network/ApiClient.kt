package com.example.bankminiviews.data.network

import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory

object ApiClient {

    // TANYAKAN INI PADA TEMAN ANDA
    // Ini adalah alamat server Laravel-nya
    // HARUS diakhiri dengan /api/
    // Contoh: "http://192.168.1.10:8000/api/"
    private const val BASE_URL = "http://192.168.129.2:8000/api/"

    val instance: ApiService by lazy {
        // Ini untuk logging (melihat request & response di Logcat)
        val logging = HttpLoggingInterceptor()
        logging.setLevel(HttpLoggingInterceptor.Level.BODY)

        val client = OkHttpClient.Builder()
            .addInterceptor(logging)
            .build()

        val retrofit = Retrofit.Builder()
            .baseUrl(BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .client(client) // Gunakan client yg sudah ada logging-nya
            .build()

        retrofit.create(ApiService::class.java)
    }
}