package com.example.bankminiviews.data.model

import com.google.gson.annotations.SerializedName

/**
 * Ini adalah "cetakan" untuk JSON dari API login/nasabah
 * Kita sesuaikan berdasarkan tes login petugas:
 * - Menggunakan "access_token" (bukan "token")
 * - Menggunakan "user" (bukan "nasabah")
 */
data class LoginResponse(
    @SerializedName("message")
    val message: String,

    @SerializedName("access_token")
    val token: String?,

    @SerializedName("user")
    val nasabah: NasabahData? // Key "user" akan berisi data Nasabah
)