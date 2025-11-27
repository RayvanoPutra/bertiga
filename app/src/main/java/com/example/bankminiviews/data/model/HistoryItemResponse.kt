package com.example.bankminiviews.data.model

import com.google.gson.annotations.SerializedName

/**
 * Ini adalah cetakan untuk SATU item riwayat transaksi
 * yang dikirim oleh server.
 */
data class HistoryItemResponse(
    @SerializedName("id")
    val id: Int,

    @SerializedName("jumlah")
    val jumlah: Long,

    @SerializedName("status")
    val status: String, // "approved" atau "rejected" atau "pending"

    @SerializedName("tgl_transaksi")
    val tglTransaksi: String?, // Contoh: "2025-11-17T10:30:00.000000Z"

    // --- TAMBAHKAN BARIS INI ---
    @SerializedName("keterangan_nasabah")
    val keteranganNasabah: String?, // "OES TEH SI"

    // Ini adalah objek di dalam objek
    @SerializedName("jenis_transaksi")
    val jenisTransaksi: JenisTransaksi
)

/**
 * Ini adalah cetakan untuk objek 'jenis_transaksi'
 * yang ada di dalam HistoryItemResponse.
 */
data class JenisTransaksi(
    @SerializedName("nama_jenis")
    val namaJenis: String // "Setor Tunai" atau "Tarik Tunai"
)