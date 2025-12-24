package com.example.bankminiviews.data.model

import com.google.gson.annotations.SerializedName

data class LoginRequest(
    // PENTING: Ganti "username" menjadi "no_rekening"
    // Ini agar sesuai dengan validasi di Laravel AuthController Anda
    @SerializedName("no_rekening")
    val noRekening: String,

    @SerializedName("password")
    val password: String
)