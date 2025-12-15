package com.example.bankminiviews.ui.history

import android.content.Context
import android.graphics.Paint // <-- IMPORT PENTING UNTUK CORETAN
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.RecyclerView
import com.example.bankminiviews.R
import com.example.bankminiviews.data.model.HistoryItemResponse
import java.text.NumberFormat
import java.time.LocalDateTime
import java.time.ZoneId
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

        private val dbDateFormatter = DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss")
        private val displayFormatter = DateTimeFormatter.ofPattern("d MMM yyyy, HH:mm", Locale("id"))

        fun bind(item: HistoryItemResponse, context: Context) {
            val formatter = NumberFormat.getCurrencyInstance(Locale("in", "ID"))
            val jumlahFormatted = formatter.format(item.jumlah)

            // 1. STATUS
            itemStatus.text = item.status.uppercase(Locale.ROOT)

            // Reset efek coret (PENTING: karena RecyclerView mendaur ulang tampilan)
            itemJumlah.paintFlags = itemJumlah.paintFlags and Paint.STRIKE_THRU_TEXT_FLAG.inv()

            when (item.status) {
                "pending" -> itemStatus.setTextColor(ContextCompat.getColor(context, R.color.text_secondary))
                "rejected" -> {
                    itemStatus.setTextColor(ContextCompat.getColor(context, android.R.color.holo_red_dark))
                    // Jika REJECTED, kita coret jumlah uangnya nanti di bawah
                }
                "success" -> itemStatus.setTextColor(ContextCompat.getColor(context, android.R.color.holo_green_dark))
                else -> itemStatus.setTextColor(ContextCompat.getColor(context, android.R.color.holo_green_dark))
            }

            // 2. TANGGAL
            try {
                item.tglTransaksi?.let { tglString ->
                    val ldt = LocalDateTime.parse(tglString, dbDateFormatter)
                    val localDateTime = ldt.atZone(ZoneId.of("UTC"))
                        .withZoneSameInstant(ZoneId.systemDefault())
                        .toLocalDateTime()
                    itemTanggal.text = "TGL: " + localDateTime.format(displayFormatter)
                } ?: run {
                    itemTanggal.text = "TGL: Menunggu persetujuan"
                }
            } catch (e: Exception) {
                itemTanggal.text = "TGL: -"
            }

            // 3. KETERANGAN
            itemKeterangan.text = item.keteranganNasabah ?: "-"
            if (item.status == "pending") {
                itemKeterangan.text = item.jenisTransaksi.namaJenis
            }

            // 4. LOGIKA TAMPILAN JUMLAH & JENIS (YANG DIPERBAIKI)
            val namaJenis = item.jenisTransaksi.namaJenis

            // Cek dulu apakah DITOLAK?
            if (item.status == "rejected") {
                // --- TAMPILAN KHUSUS REJECT ---
                itemJenis.text = namaJenis.uppercase(Locale.ROOT) + " (BATAL)"

                // Hapus tanda + atau -
                itemJumlah.text = jumlahFormatted

                // Warna Abu-abu (Netral)
                itemJumlah.setTextColor(ContextCompat.getColor(context, R.color.text_secondary))
                itemTipe.setTextColor(ContextCompat.getColor(context, R.color.text_secondary))

                // Tambahkan Efek Coretan (Strikethrough)
                itemJumlah.paintFlags = itemJumlah.paintFlags or Paint.STRIKE_THRU_TEXT_FLAG

                itemTipe.text = "X" // Tanda silang di tipe
            }
            else {
                // --- TAMPILAN NORMAL (Pending / Success) ---

                if (namaJenis.equals("Setor Tunai", ignoreCase = true) || namaJenis.equals("Saldo Awal", ignoreCase = true)) {
                    // UANG MASUK (KREDIT)
                    itemJenis.text = "TRANSAKSI KREDIT"
                    itemJumlah.text = "+$jumlahFormatted"
                    itemJumlah.setTextColor(ContextCompat.getColor(context, android.R.color.holo_green_dark))
                    itemTipe.text = "CR"
                    itemTipe.setTextColor(ContextCompat.getColor(context, android.R.color.holo_green_dark))
                }
                else if (namaJenis.equals("Tarik Tunai", ignoreCase = true) || namaJenis.equals("Biaya Admin", ignoreCase = true)) {
                    // UANG KELUAR (DEBIT)
                    itemJenis.text = "TRANSAKSI DEBIT"
                    itemJumlah.text = "-$jumlahFormatted"
                    itemJumlah.setTextColor(ContextCompat.getColor(context, android.R.color.holo_red_dark))
                    itemTipe.text = "DB"
                    itemTipe.setTextColor(ContextCompat.getColor(context, android.R.color.holo_red_dark))
                }
                else {
                    // LAINNYA
                    itemJenis.text = namaJenis.uppercase(Locale.ROOT)
                    itemJumlah.text = jumlahFormatted
                    itemJumlah.setTextColor(ContextCompat.getColor(context, R.color.text_secondary))
                    itemTipe.text = ""
                }
            }
        }
    }
}