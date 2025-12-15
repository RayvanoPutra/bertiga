package com.example.bankminiviews.data.model

import com.google.gson.annotations.SerializedName

/**
 * Update sesuai respons Login Nasabah dari Server:
 * {
 * "message": "Login Berhasil",
 * "token": "...",  <-- Nama kuncinya 'token'
 * "data": { ... }  <-- Nama kuncinya 'data' (berisi profil nasabah)
 * }
 */
data class LoginResponse(
    @SerializedName("message")
    val message: String,

    // Ganti "access_token" menjadi "token"
    @SerializedName("token")
    val token: String?,

    // Ganti "user" menjadi "data"
    @SerializedName("data")
    val nasabah: NasabahData?
)