package com.example.bankminiviews.data.model

import com.google.gson.annotations.SerializedName

data class UpdateProfileRequest(
//    @SerializedName("username")
//    val username: String,

    @SerializedName("email")
    val email: String,

    @SerializedName("no_telp")
    val noTelp: String,

    @SerializedName("password")
    val password: String? = null // Boleh null jika tidak ingin ganti password
)