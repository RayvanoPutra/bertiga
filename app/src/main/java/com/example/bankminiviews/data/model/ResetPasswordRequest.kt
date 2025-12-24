package com.example.bankminiviews.data.model

import com.google.gson.annotations.SerializedName

data class ResetPasswordRequest(
    @SerializedName("no_rekening")
    val noRekening: String,

    @SerializedName("otp")
    val otp: String, // Kirim OTP lagi untuk validasi akhir

    @SerializedName("new_password")
    val newPassword: String,

    @SerializedName("new_password_confirmation")
    val newPasswordConfirmation: String
)