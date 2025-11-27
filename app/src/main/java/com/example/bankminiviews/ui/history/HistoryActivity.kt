package com.example.bankminiviews.ui.history

import android.os.Bundle
import android.view.View
import android.widget.ArrayAdapter
import android.widget.AutoCompleteTextView
import android.widget.Button
import android.widget.ProgressBar
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.example.bankminiviews.R
import com.example.bankminiviews.data.model.HistoryItemResponse
import com.example.bankminiviews.data.network.ApiClient
import com.example.bankminiviews.util.SessionManager
import com.google.android.material.appbar.MaterialToolbar
import com.google.android.material.datepicker.MaterialDatePicker
import com.google.android.material.textfield.TextInputEditText
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response
import java.time.Instant
import java.time.LocalDate
import java.time.LocalDateTime // <-- IMPORT BARU
import java.time.ZoneId
import java.time.ZonedDateTime
import java.time.format.DateTimeFormatter
import java.util.Locale

class HistoryActivity : AppCompatActivity() {

    // ... (Semua deklarasi variabel Anda sudah benar)
    private lateinit var sessionManager: SessionManager
    private var historyAdapter: HistoryAdapter? = null
    private lateinit var toolbar: MaterialToolbar
    private lateinit var recyclerView: RecyclerView
    private lateinit var progressBar: ProgressBar
    private lateinit var textNoData: TextView
    private lateinit var dropdownJenis: AutoCompleteTextView
    private lateinit var inputDariTanggal: TextInputEditText
    private lateinit var inputSampaiTanggal: TextInputEditText
    private lateinit var buttonCari: Button
    private var allTransactions: List<HistoryItemResponse> = emptyList()
    private var startDate: LocalDate? = null
    private var endDate: LocalDate? = null
    private var jenisFilter: String = "Semua"
    private val dateFormatter = DateTimeFormatter.ofPattern("d MMM yyyy", Locale("id"))

    // --- BUAT FORMATTER BARU UNTUK TANGGAL DARI DATABASE ---
    private val dbDateFormatter = DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss")


    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_history)

        sessionManager = SessionManager(this)

        // ... (Semua findViewById Anda sudah benar)
        toolbar = findViewById(R.id.toolbarHistory)
        recyclerView = findViewById(R.id.recyclerHistory)
        progressBar = findViewById(R.id.progressHistory)
        textNoData = findViewById(R.id.textNoData)
        dropdownJenis = findViewById(R.id.autoCompleteJenis)
        inputDariTanggal = findViewById(R.id.inputDariTanggal)
        inputSampaiTanggal = findViewById(R.id.inputSampaiTanggal)
        buttonCari = findViewById(R.id.buttonCari)

        // ... (Semua setup Anda sudah benar)
        toolbar.setNavigationOnClickListener { finish() }
        recyclerView.layoutManager = LinearLayoutManager(this)
        setupFilterListeners()
        fetchHistoryData()
    }

    private fun setupFilterListeners() {
        // ... (Fungsi ini tidak perlu diubah)
        val jenisOptions = arrayOf("Semua", "Uang Masuk", "Uang Keluar")
        val adapter = ArrayAdapter(this, android.R.layout.simple_dropdown_item_1line, jenisOptions)
        dropdownJenis.setAdapter(adapter)
        dropdownJenis.setOnItemClickListener { _, _, position, _ ->
            jenisFilter = jenisOptions[position]
        }
        inputDariTanggal.setOnClickListener { showDatePicker(isStartDate = true) }
        inputSampaiTanggal.setOnClickListener { showDatePicker(isStartDate = false) }
        buttonCari.setOnClickListener { applyFilter() }
    }

    private fun showDatePicker(isStartDate: Boolean) {
        // ... (Fungsi ini tidak perlu diubah)
        val picker = MaterialDatePicker.Builder.datePicker().build()
        picker.show(supportFragmentManager, if (isStartDate) "START_DATE_PICKER" else "END_DATE_PICKER")
        picker.addOnPositiveButtonClickListener { timestamp ->
            val selectedDate = Instant.ofEpochMilli(timestamp)
                .atZone(ZoneId.of("UTC")).toLocalDate()
            if (isStartDate) {
                startDate = selectedDate
                inputDariTanggal.setText(selectedDate.format(dateFormatter))
            } else {
                endDate = selectedDate
                inputSampaiTanggal.setText(selectedDate.format(dateFormatter))
            }
        }
    }

    private fun fetchHistoryData() {
        // ... (Fungsi ini tidak perlu diubah)
        val token = sessionManager.fetchAuthToken()
        if (token == null) {
            Toast.makeText(this, "Sesi tidak valid", Toast.LENGTH_SHORT).show()
            finish()
            return
        }
        showLoading(true)
        ApiClient.instance.getHistory(token)
            .enqueue(object : Callback<List<HistoryItemResponse>> {
                override fun onResponse(
                    call: Call<List<HistoryItemResponse>>,
                    response: Response<List<HistoryItemResponse>>
                ) {
                    showLoading(false)
                    if (response.isSuccessful) {
                        allTransactions = response.body() ?: emptyList()
                        updateAdapter(allTransactions)
                    } else {
                        showError("Gagal memuat riwayat. Kode: ${response.code()}")
                    }
                }
                override fun onFailure(call: Call<List<HistoryItemResponse>>, t: Throwable) {
                    showLoading(false)
                    showError("Koneksi Gagal: ${t.message}")
                }
            })
    }

    private fun applyFilter() {
        // ... (Fungsi ini tidak perlu diubah)
        if (startDate != null && endDate != null && startDate!!.isAfter(endDate)) {
            Toast.makeText(this, "'Dari Tanggal' tidak boleh lebih baru dari 'Sampai Tanggal'", Toast.LENGTH_LONG).show()
            return
        }
        var filteredList = allTransactions
        filteredList = when (jenisFilter) {
            "Uang Masuk" -> filteredList.filter { it.jenisTransaksi.namaJenis == "Setor Tunai" }
            "Uang Keluar" -> filteredList.filter { it.jenisTransaksi.namaJenis == "Tarik Tunai" }
            else -> filteredList
        }
        if (startDate != null) {
            filteredList = filteredList.filter {
                val transactionDate = parseDate(it.tglTransaksi)
                transactionDate != null && !transactionDate.isBefore(startDate)
            }
        }
        if (endDate != null) {
            filteredList = filteredList.filter {
                val transactionDate = parseDate(it.tglTransaksi)
                transactionDate != null && !transactionDate.isAfter(endDate)
            }
        }
        updateAdapter(filteredList)
    }

    // --- FUNGSI INI DIPERBAIKI ---
    /**
     * Helper function untuk mem-parsing string tanggal dari API
     */
    private fun parseDate(dateString: String?): LocalDate? {
        if (dateString == null) return null
        return try {
            // 1. Parsing string '2025-11-17 10:38:29' menjadi LocalDateTime
            val ldt = LocalDateTime.parse(dateString, dbDateFormatter)
            // 2. Asumsikan tanggal dari DB adalah UTC
            val zdt = ldt.atZone(ZoneId.of("UTC"))
            // 3. Konversi ke LocalDate (HANYA tanggalnya)
            zdt.toLocalDate()
        } catch (e: Exception) {
            null // Gagal parse
        }
    }
    // --- AKHIR PERBAIKAN ---

    private fun updateAdapter(history: List<HistoryItemResponse>) {
        // ... (Fungsi ini tidak perlu diubah)
        if (history.isNotEmpty()) {
            recyclerView.visibility = View.VISIBLE
            textNoData.visibility = View.GONE
            historyAdapter = HistoryAdapter(this@HistoryActivity, history)
            recyclerView.adapter = historyAdapter
        } else {
            recyclerView.visibility = View.GONE
            textNoData.visibility = View.VISIBLE
            textNoData.text = "Tidak ada transaksi pada periode ini."
        }
    }

    private fun showLoading(isLoading: Boolean) {
        // ... (Fungsi ini tidak perlu diubah)
        progressBar.visibility = if (isLoading) View.VISIBLE else View.GONE
    }

    private fun showError(message: String) {
        // ... (Fungsi ini tidak perlu diubah)
        recyclerView.visibility = View.GONE
        textNoData.visibility = View.VISIBLE
        textNoData.text = message
        Toast.makeText(this, message, Toast.LENGTH_LONG).show()
    }
}