package com.example.bankminiviews.data.model

import com.google.gson.annotations.SerializedName

/**
 * Ini adalah "Kotak" JSON yang kita TERIMA dari server
 * sebagai balasan.
 */
data class SetorResponse(
    @SerializedName("message")
    val message: String
)