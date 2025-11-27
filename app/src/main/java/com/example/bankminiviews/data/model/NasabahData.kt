package com.example.bankminiviews.data.model

import com.google.gson.annotations.SerializedName

/**
 * Ini adalah "cetakan" data untuk profil Nasabah.
 * Sekarang dia punya file sendiri.
 */
data class NasabahData(
    @SerializedName("no_rekening")
    val noRekening: String,

    @SerializedName("nama")
    val nama: String,

    @SerializedName("no_induk")
    val noInduk: String,

    @SerializedName("saldo")
    val saldo: Long
)