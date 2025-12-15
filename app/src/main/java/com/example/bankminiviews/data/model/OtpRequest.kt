package com.example.bankminiviews.data.model
import com.google.gson.annotations.SerializedName

data class OtpRequest(
    @SerializedName("otp")
    val otp: String
)