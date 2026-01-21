package com.example.bankminiviews.ui.laporan

import android.os.Bundle
import android.os.Environment
import android.util.Log
import android.widget.ArrayAdapter
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.example.bankminiviews.data.network.ApiClient
import com.example.bankminiviews.databinding.ActivityLaporanBinding
import com.example.bankminiviews.util.SessionManager
import okhttp3.ResponseBody
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response
import java.io.*
import android.content.Intent
import android.app.DownloadManager
import android.media.MediaScannerConnection
import android.net.Uri

class LaporanActivity : AppCompatActivity() {

    private lateinit var binding: ActivityLaporanBinding
    // Gunakan camelCase untuk variabel agar berbeda dengan nama Class-nya
    private lateinit var sessionManager: SessionManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityLaporanBinding.inflate(layoutInflater)
        setContentView(binding.root)

        sessionManager = SessionManager(this)
        setupSpinner()

        binding.btnCetakLaporan.setOnClickListener {
            val mulai = binding.spinnerBulanMulai.selectedItem.toString()
            val selesai = binding.spinnerBulanSelesai.selectedItem.toString()

            // Jalankan proses download
            mulaiDownload(mulai, selesai)
        }

        binding.toolbarLaporan.setNavigationOnClickListener {
            onBackPressedDispatcher.onBackPressed()
        }
    }

    private fun setupSpinner() {
        val daftarBulan = arrayOf("Januari", "Februari", "Maret", "April", "Mei", "Juni",
            "Juli", "Agustus", "September", "Oktober", "November", "Desember")
        val adapter = ArrayAdapter(this, android.R.layout.simple_spinner_dropdown_item, daftarBulan)
        binding.spinnerBulanMulai.adapter = adapter
        binding.spinnerBulanSelesai.adapter = adapter
    }

    private fun mulaiDownload(mulai: String, selesai: String) {
        // Panggil fetchAuthToken() sesuai class SessionManager kamu
        val token = sessionManager.fetchAuthToken()

        if (token != null) {
            Toast.makeText(this, "Sedang mengunduh laporan...", Toast.LENGTH_SHORT).show()

            // Token sudah mengandung "Bearer " dari SessionManager
            ApiClient.instance.downloadLaporan(token, mulai, selesai).enqueue(object : Callback<ResponseBody> {
                override fun onResponse(call: Call<ResponseBody>, response: Response<ResponseBody>) {
                    if (response.isSuccessful) {
                        val fileBerhasil = saveFileToDisk(response.body(), "Laporan_${mulai}_${selesai}.pdf")
                        if (fileBerhasil) {
                            Toast.makeText(applicationContext, "Laporan berhasil disimpan di folder Download", Toast.LENGTH_LONG).show()
                            val intent = Intent(DownloadManager.ACTION_VIEW_DOWNLOADS)
                            startActivity(intent)
                        }
                        if (fileBerhasil) {
                            Toast.makeText(applicationContext, "Berhasil! Klik notifikasi untuk buka.", Toast.LENGTH_LONG).show()

                            // Fungsi untuk membuka folder Download secara otomatis
                            val intent = Intent(DownloadManager.ACTION_VIEW_DOWNLOADS)
                            intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK
                            startActivity(intent)
                        }
                    } else {
                        Toast.makeText(applicationContext, "Gagal mengunduh laporan", Toast.LENGTH_SHORT).show()
                    }
                }

                override fun onFailure(call: Call<ResponseBody>, t: Throwable) {
                    Toast.makeText(applicationContext, "Error: ${t.message}", Toast.LENGTH_SHORT).show()
                }
            })
        } else {
            Toast.makeText(this, "Sesi berakhir, silakan login kembali", Toast.LENGTH_SHORT).show()
        }
    }

    private fun saveFileToDisk(body: ResponseBody?, fileName: String): Boolean {
        if (body == null) return false

        return try {
            val file = File(Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_DOWNLOADS), fileName)
            var inputStream: InputStream? = null
            var outputStream: OutputStream? = null

            try {
                val fileReader = ByteArray(4096)
                inputStream = body.byteStream()
                outputStream = FileOutputStream(file)

                while (true) {
                    val read = inputStream.read(fileReader)
                    if (read == -1) break
                    outputStream.write(fileReader, 0, read)
                }
                outputStream.flush()
                MediaScannerConnection.scanFile(
                    this,
                    arrayOf(file.absolutePath),
                    null
                ) { path, uri ->
                    // File sekarang sudah terdaftar di sistem
                    Log.d("Download", "File terdaftar di: $path")
                }
                true
            } catch (e: IOException) {
                false
            } finally {
                inputStream?.close()
                outputStream?.close()
            }
        } catch (e: IOException) {
            false
        }
    }
}