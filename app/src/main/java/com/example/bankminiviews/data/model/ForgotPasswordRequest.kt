package com.example.bankminiviews.data.model

import com.google.gson.annotations.SerializedName

data class ForgotPasswordRequest(
    @SerializedName("no_rekening")
    val noRekening: String,

    @SerializedName("email")
    val email: String
)