package com.example.bankminiviews.data.model

import com.google.gson.annotations.SerializedName

/**
 * Update sesuai migrasi:
 * - Menggunakan String untuk semua kode/ID (no_rekening, kode_kelas, kode_jurusan)
 */
data class NasabahData(
    @SerializedName("no_rekening")
    val noRekening: String, // String (Primary Key)

    @SerializedName("nama")
    val nama: String,

    @SerializedName("no_induk")
    val noInduk: String,

    @SerializedName("saldo")
    val saldo: Long,

    @SerializedName("no_telp")
    val noHp: String?,

    @SerializedName("email")
    val email: String?,

    // Relasi ke Kelas
    @SerializedName("kelas")
    val kelas: KelasData?
)

data class KelasData(
    @SerializedName("kode_kelas") // Sesuai migrasi: kode_kelas
    val kodeKelas: String, // String

    @SerializedName("nama_kelas")
    val namaKelas: String,

    @SerializedName("jurusan")
    val jurusan: JurusanData?
)

data class JurusanData(
    @SerializedName("kode_jurusan") // Sesuai migrasi: kode_jurusan
    val kodeJurusan: String, // String

    @SerializedName("nama_jurusan")
    val namaJurusan: String
)