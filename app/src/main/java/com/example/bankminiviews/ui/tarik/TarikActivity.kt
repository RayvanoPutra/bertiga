package com.example.bankminiviews.ui.tarik

import android.os.Bundle
import android.widget.Button
import android.widget.EditText
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import com.example.bankminiviews.R
import com.example.bankminiviews.data.model.NasabahData
import com.example.bankminiviews.data.model.SetorResponse
import com.example.bankminiviews.data.model.TarikRequest
import com.example.bankminiviews.data.network.ApiClient
import com.example.bankminiviews.util.SessionManager
import com.google.android.material.appbar.MaterialToolbar
import com.google.android.material.chip.Chip
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response
import java.text.NumberFormat
import java.util.Locale

class TarikActivity : AppCompatActivity() {

    private lateinit var sessionManager: SessionManager
    private lateinit var etJumlah: EditText
    private lateinit var etKeterangan: EditText
    private lateinit var btnKonfirmasi: Button
    private lateinit var toolbar: MaterialToolbar
    private lateinit var textSaldoSaatIni: TextView

    // --- PERBAIKAN DI SINI ---
    // Sesuaikan dengan Seeder teman Anda: 'TARIK'
    private val KODE_JENIS_TARIK = "TARIK"
    // -------------------------

    private var currentSaldo: Long = 0

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_tarik)

        sessionManager = SessionManager(this)

        etJumlah = findViewById(R.id.editTextJumlahTarik)
        etKeterangan = findViewById(R.id.editTextKeterangan)
        btnKonfirmasi = findViewById(R.id.buttonKonfirmasiTarik)
        toolbar = findViewById(R.id.toolbarTarik)
        textSaldoSaatIni = findViewById(R.id.textSaldoSaatIni)

        toolbar.setNavigationOnClickListener { finish() }

        val token = sessionManager.fetchAuthToken()
        if (token != null) {
            fetchSaldo(token)
        }

        setupChipListener(R.id.chip50k, 50000)
        setupChipListener(R.id.chip100k, 100000)
        setupChipListener(R.id.chip200k, 200000)

        btnKonfirmasi.setOnClickListener {
            val jumlahString = etJumlah.text.toString()
            val keterangan = etKeterangan.text.toString().trim()
            val jumlahLong = jumlahString.toLongOrNull()

            if (jumlahLong == null || jumlahLong < 1000) {
                Toast.makeText(this, "Minimal penarikan Rp 1.000", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }

            if (jumlahLong > currentSaldo) {
                Toast.makeText(this, "Saldo tidak mencukupi!", Toast.LENGTH_LONG).show()
                return@setOnClickListener
            }

            if (keterangan.isEmpty()) {
                etKeterangan.error = "Keterangan wajib diisi!"
                return@setOnClickListener
            }

            showConfirmationDialog(jumlahLong, keterangan)
        }
    }

    private fun fetchSaldo(token: String) {
        ApiClient.instance.getNasabahData(token).enqueue(object : Callback<NasabahData> {
            override fun onResponse(call: Call<NasabahData>, response: Response<NasabahData>) {
                if (response.isSuccessful) {
                    val data = response.body()
                    if (data != null) {
                        currentSaldo = data.saldo
                        val formatter = NumberFormat.getCurrencyInstance(Locale("in", "ID"))
                        textSaldoSaatIni.text = formatter.format(currentSaldo)
                    }
                }
            }
            override fun onFailure(call: Call<NasabahData>, t: Throwable) {
                textSaldoSaatIni.text = "Gagal memuat saldo"
            }
        })
    }

    private fun setupChipListener(chipId: Int, nominal: Long) {
        findViewById<Chip>(chipId).setOnClickListener {
            etJumlah.setText(nominal.toString())
            etJumlah.setSelection(etJumlah.text.length)
        }
    }

    private fun showConfirmationDialog(jumlah: Long, keterangan: String) {
        val formatter = NumberFormat.getCurrencyInstance(Locale("in", "ID"))
        val jumlahFormatted = formatter.format(jumlah)

        AlertDialog.Builder(this)
            .setTitle("Konfirmasi Penarikan")
            .setMessage("Nominal: $jumlahFormatted\nKeperluan: $keterangan\n\nLanjutkan?")
            .setPositiveButton("Ya, Tarik") { dialog, _ ->
                sendTarikRequest(jumlah, keterangan)
                dialog.dismiss()
            }
            .setNegativeButton("Batal") { dialog, _ ->
                dialog.dismiss()
            }
            .show()
    }

    private fun sendTarikRequest(jumlah: Long, keterangan: String) {
        val token = sessionManager.fetchAuthToken() ?: return

        btnKonfirmasi.isEnabled = false
        btnKonfirmasi.text = "Mengirim..."

        // --- UPDATE REQUEST ---
        val request = TarikRequest(
            jumlah = jumlah,
            kodeJenis = KODE_JENIS_TARIK, // Mengirim string "TARIK"
            keterangan = keterangan
        )

        ApiClient.instance.requestTarik(token, request).enqueue(object : Callback<SetorResponse> {
            override fun onResponse(call: Call<SetorResponse>, response: Response<SetorResponse>) {
                if (response.isSuccessful) {
                    Toast.makeText(this@TarikActivity, "Permintaan terkirim!", Toast.LENGTH_LONG).show()
                    finish()
                } else {
                    btnKonfirmasi.isEnabled = true
                    btnKonfirmasi.text = "Tarik Tunai Sekarang"
                    Toast.makeText(this@TarikActivity, "Gagal: Kode ${response.code()}", Toast.LENGTH_SHORT).show()
                }
            }
            override fun onFailure(call: Call<SetorResponse>, t: Throwable) {
                btnKonfirmasi.isEnabled = true
                btnKonfirmasi.text = "Tarik Tunai Sekarang"
                Toast.makeText(this@TarikActivity, "Koneksi Gagal", Toast.LENGTH_SHORT).show()
            }
        })
    }
}