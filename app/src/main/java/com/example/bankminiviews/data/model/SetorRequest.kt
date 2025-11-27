package com.example.bankminiviews.data.model

import com.google.gson.annotations.SerializedName

/**
 * Sesuai klarifikasi Anda:
 * Kita HANYA mengirim jumlah dan ID jenis transaksi
 */
data class SetorRequest(
    @SerializedName("jumlah")
    val jumlah: Long,

    @SerializedName("jenis_transaksi_id")
    val jenisTransaksiId: Int

    // 'keterangan_nasabah' TIDAK DIKIRIM (otomatis akan null di server)
)