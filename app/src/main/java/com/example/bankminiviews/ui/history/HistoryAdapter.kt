package com.example.bankminiviews.ui.history

import android.content.Context
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.RecyclerView
import com.example.bankminiviews.R
import com.example.bankminiviews.data.model.HistoryItemResponse
import java.text.NumberFormat
import java.time.LocalDateTime // <-- IMPORT BARU
import java.time.ZoneId // <-- IMPORT BARU
import java.time.ZonedDateTime
import java.time.format.DateTimeFormatter
import java.util.Locale

class HistoryAdapter(
    private val context: Context,
    private val historyList: List<HistoryItemResponse>
) : RecyclerView.Adapter<HistoryAdapter.HistoryViewHolder>() {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): HistoryViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_transaksi, parent, false)
        return HistoryViewHolder(view)
    }

    override fun onBindViewHolder(holder: HistoryViewHolder, position: Int) {
        val item = historyList[position]
        holder.bind(item, context)
    }

    override fun getItemCount(): Int = historyList.size

    class HistoryViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        private val itemStatus: TextView = itemView.findViewById(R.id.itemStatus)
        private val itemJumlah: TextView = itemView.findViewById(R.id.itemJumlah)
        private val itemTanggal: TextView = itemView.findViewById(R.id.itemTanggal)
        private val itemKeterangan: TextView = itemView.findViewById(R.id.itemKeterangan)
        private val itemJenis: TextView = itemView.findViewById(R.id.itemJenis)
        private val itemTipe: TextView = itemView.findViewById(R.id.itemTipe)

        // --- BUAT FORMATTER BARU UNTUK TANGGAL DARI DATABASE ---
        private val dbDateFormatter = DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss")
        // Formatter untuk tampilan
        private val displayFormatter = DateTimeFormatter.ofPattern("d MMM yyyy, HH:mm", Locale("id"))

        fun bind(item: HistoryItemResponse, context: Context) {
            val formatter = NumberFormat.getCurrencyInstance(Locale("in", "ID"))
            val jumlahFormatted = formatter.format(item.jumlah)

            // 1. Status
            itemStatus.text = item.status.uppercase(Locale.ROOT)
            when (item.status) {
                "pending" -> itemStatus.setTextColor(ContextCompat.getColor(context, R.color.text_secondary))
                "rejected" -> itemStatus.setTextColor(ContextCompat.getColor(context, android.R.color.holo_red_dark))
                else -> itemStatus.setTextColor(ContextCompat.getColor(context, android.R.color.holo_green_dark)) // approved
            }

            // --- 2. TANGGAL (INI YANG DIPERBAIKI) ---
            try {
                // item.tglTransaksi sekarang String? (nullable)
                // Kita gunakan 'let' untuk mengecek null dengan aman
                item.tglTransaksi?.let { tglString ->
                    // tglString di sini dijamin tidak null
                    val ldt = LocalDateTime.parse(tglString, dbDateFormatter)
                    val localDateTime = ldt.atZone(ZoneId.of("UTC"))
                        .withZoneSameInstant(ZoneId.systemDefault())
                        .toLocalDateTime()
                    itemTanggal.text = "TGL: " + localDateTime.format(displayFormatter)
                } ?: run {
                    // Ini akan dijalankan jika item.tglTransaksi == null
                    itemTanggal.text = "TGL: Menunggu persetujuan"
                }
            } catch (e: Exception) {
                // Ini akan menangkap jika parsing GAGAL (meskipun tidak null)
                itemTanggal.text = "TGL: Format tanggal salah"
            }
            // --- AKHIR PERBAIKAN ---

            // 3. Keterangan
            itemKeterangan.text = item.keteranganNasabah ?: "Tidak ada keterangan"
            if (item.status == "pending") {
                itemKeterangan.text = item.jenisTransaksi.namaJenis // (Contoh: "Request Setor Tunai")
            }

            // 4. Jenis, Jumlah, dan Tipe (DB/CR)
            if (item.jenisTransaksi.namaJenis == "Setor Tunai") {
                itemJenis.text = "TRANSAKSI KREDIT"
                itemJumlah.text = "+$jumlahFormatted"
                itemJumlah.setTextColor(ContextCompat.getColor(context, android.R.color.holo_green_dark))
                itemTipe.text = "CR"
                itemTipe.setTextColor(ContextCompat.getColor(context, android.R.color.holo_green_dark))
            } else if (item.jenisTransaksi.namaJenis == "Tarik Tunai") {
                itemJenis.text = "TRANSAKSI DEBIT"
                itemJumlah.text = "-$jumlahFormatted"
                itemJumlah.setTextColor(ContextCompat.getColor(context, android.R.color.holo_red_dark))
                itemTipe.text = "DB"
                itemTipe.setTextColor(ContextCompat.getColor(context, android.R.color.holo_red_dark))
            } else {
                itemJenis.text = item.jenisTransaksi.namaJenis.uppercase(Locale.ROOT)
                itemJumlah.text = jumlahFormatted
                itemJumlah.setTextColor(ContextCompat.getColor(context, R.color.text_secondary))
                itemTipe.text = ""
            }
        }
    }
}