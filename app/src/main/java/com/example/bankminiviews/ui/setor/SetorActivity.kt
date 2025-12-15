package com.example.bankminiviews.ui.setor

import android.os.Bundle
import android.widget.Button
import android.widget.EditText
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import com.example.bankminiviews.R
import com.example.bankminiviews.data.model.SetorRequest
import com.example.bankminiviews.data.model.SetorResponse
import com.example.bankminiviews.data.network.ApiClient
import com.example.bankminiviews.util.SessionManager
import com.google.android.material.appbar.MaterialToolbar
import com.google.android.material.chip.Chip
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response
import java.text.NumberFormat
import java.util.Locale

class SetorActivity : AppCompatActivity() {

    private lateinit var sessionManager: SessionManager
    private lateinit var etJumlah: EditText
    private lateinit var btnKonfirmasi: Button
    private lateinit var toolbar: MaterialToolbar

    // --- PERBAIKAN DI SINI ---
    // Sesuaikan dengan Seeder teman Anda: 'SETOR'
    private val KODE_JENIS_SETOR = "SETOR"
    // -------------------------

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_setor)

        sessionManager = SessionManager(this)

        etJumlah = findViewById(R.id.editTextJumlahSetor)
        btnKonfirmasi = findViewById(R.id.buttonKonfirmasiSetor)
        toolbar = findViewById(R.id.toolbarSetor)

        toolbar.setNavigationOnClickListener {
            finish()
        }

        // Setup Chip Listener
        setupChipListener(R.id.chip10k, 10000)
        setupChipListener(R.id.chip20k, 20000)
        setupChipListener(R.id.chip50k, 50000)
        setupChipListener(R.id.chip100k, 100000)
        setupChipListener(R.id.chip200k, 200000)
        setupChipListener(R.id.chip500k, 500000)

        btnKonfirmasi.setOnClickListener {
            val jumlahString = etJumlah.text.toString()
            val jumlahLong = jumlahString.toLongOrNull()

            if (jumlahLong == null || jumlahLong < 1000) {
                Toast.makeText(this, "Jumlah minimal Rp 1.000", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }

            showConfirmationDialog(jumlahLong)
        }
    }

    private fun setupChipListener(chipId: Int, nominal: Long) {
        findViewById<Chip>(chipId).setOnClickListener {
            etJumlah.setText(nominal.toString())
            etJumlah.setSelection(etJumlah.text.length)
        }
    }

    private fun showConfirmationDialog(jumlah: Long) {
        val formatter = NumberFormat.getCurrencyInstance(Locale("in", "ID"))
        val jumlahFormatted = formatter.format(jumlah)

        AlertDialog.Builder(this)
            .setTitle("Konfirmasi Setoran")
            .setMessage("Anda akan melakukan setoran sejumlah $jumlahFormatted. Lanjutkan?")
            .setPositiveButton("Ya, Lanjutkan") { dialog, _ ->
                sendSetorRequest(jumlah)
                dialog.dismiss()
            }
            .setNegativeButton("Batal") { dialog, _ ->
                dialog.dismiss()
            }
            .show()
    }

    private fun sendSetorRequest(jumlah: Long) {
        val token = sessionManager.fetchAuthToken()
        if (token == null) {
            Toast.makeText(this, "Sesi tidak valid", Toast.LENGTH_SHORT).show()
            return
        }

        // --- UPDATE REQUEST ---
        // Pastikan SetorRequest.kt Anda sudah menggunakan String untuk kodeJenis
        val request = SetorRequest(
            jumlah = jumlah,
            kodeJenis = KODE_JENIS_SETOR // Mengirim string "SETOR"
        )

        ApiClient.instance.requestSetor(token, request)
            .enqueue(object : Callback<SetorResponse> {
                override fun onResponse(call: Call<SetorResponse>, response: Response<SetorResponse>) {
                    if (response.isSuccessful) {
                        val message = response.body()?.message ?: "Permintaan berhasil dikirim"
                        Toast.makeText(this@SetorActivity, "$message (Menunggu persetujuan)", Toast.LENGTH_LONG).show()
                        finish()
                    } else {
                        Toast.makeText(this@SetorActivity, "Gagal: Kode ${response.code()}", Toast.LENGTH_LONG).show()
                    }
                }

                override fun onFailure(call: Call<SetorResponse>, t: Throwable) {
                    Toast.makeText(this@SetorActivity, "Koneksi Gagal: ${t.message}", Toast.LENGTH_LONG).show()
                }
            })
    }
}