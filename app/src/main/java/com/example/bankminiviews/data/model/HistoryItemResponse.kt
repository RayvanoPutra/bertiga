package com.example.bankminiviews.data.model

import com.google.gson.annotations.SerializedName

/**
 * Model Data untuk Riwayat Transaksi.
 * Sesuai dengan tabel 'transaksi' dan relasi 'jenis_transaksi' di Laravel.
 */
data class HistoryItemResponse(
    // Primary Key sekarang adalah String (TRX-...)
    @SerializedName("kode_transaksi")
    val kodeTransaksi: String,

    @SerializedName("jumlah")
    val jumlah: Long,

    // Nilai: 'pending', 'success', 'rejected'
    @SerializedName("status")
    val status: String,

    @SerializedName("tgl_transaksi")
    val tglTransaksi: String?, // Bisa null jika pending

    @SerializedName("keterangan_nasabah")
    val keteranganNasabah: String?,

    // Ini objek hasil dari ->with('jenisTransaksi')
    @SerializedName("jenis_transaksi")
    val jenisTransaksi: JenisTransaksiData
)

data class JenisTransaksiData(
    // Primary Key Jenis sekarang adalah String (SETOR, TARIK)
    @SerializedName("kode_jenis")
    val kodeJenis: String,

    @SerializedName("nama_jenis")
    val namaJenis: String
)