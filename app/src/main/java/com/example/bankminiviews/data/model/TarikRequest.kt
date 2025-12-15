package com.example.bankminiviews.data.model

import com.google.gson.annotations.SerializedName

data class TarikRequest(
    @SerializedName("jumlah")
    val jumlah: Long,

    // Ganti Int menjadi String
    @SerializedName("kode_jenis")
    val kodeJenis: String,

    @SerializedName("keterangan_nasabah")
    val keterangan: String
)