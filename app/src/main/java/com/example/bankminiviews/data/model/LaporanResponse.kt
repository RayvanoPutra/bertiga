package com.example.bankminiviews.data.model
import com.google.gson.annotations.SerializedName

data class LaporanResponse(
    @SerializedName("message")
    val message: String,

    @SerializedName("url") // URL PDF dari server
    val urlPdf: String?
)