package com.example.bankminiviews.data.model

import com.google.gson.annotations.SerializedName

data class VerifyOtpRequest(
    @SerializedName("no_rekening")
    val noRekening: String,

    @SerializedName("otp")
    val otp: String
)