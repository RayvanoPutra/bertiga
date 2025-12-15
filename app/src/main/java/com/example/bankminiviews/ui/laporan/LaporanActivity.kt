package com.example.bankminiviews.ui.laporan

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.View
import android.widget.Button
import android.widget.LinearLayout
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.example.bankminiviews.R
import com.example.bankminiviews.data.model.LaporanResponse
import com.example.bankminiviews.data.model.OtpRequest
import com.example.bankminiviews.data.model.SetorResponse
import com.example.bankminiviews.data.network.ApiClient
import com.example.bankminiviews.util.SessionManager
import com.google.android.material.textfield.TextInputEditText
import org.json.JSONObject // <-- Import untuk baca JSON error
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class LaporanActivity : AppCompatActivity() {

    private lateinit var sessionManager: SessionManager
    private lateinit var btnRequestOtp: Button
    private lateinit var btnVerify: Button
    private lateinit var etOtp: TextInputEditText
    private lateinit var layoutInputOtp: LinearLayout

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_laporan)

        sessionManager = SessionManager(this)

        btnRequestOtp = findViewById(R.id.btnRequestOtp)
        btnVerify = findViewById(R.id.btnVerify)
        etOtp = findViewById(R.id.etOtp)
        layoutInputOtp = findViewById(R.id.layoutInputOtp)

        btnRequestOtp.setOnClickListener {
            requestOtpFromServer()
        }

        btnVerify.setOnClickListener {
            val otp = etOtp.text.toString()
            if (otp.length < 6) {
                Toast.makeText(this, "Masukkan 6 digit kode", Toast.LENGTH_SHORT).show()
            } else {
                verifyOtpAndDownload(otp)
            }
        }
    }

    private fun requestOtpFromServer() {
        val token = sessionManager.fetchAuthToken() ?: return

        btnRequestOtp.isEnabled = false
        btnRequestOtp.text = "Mengirim..."

        ApiClient.instance.requestOtp(token).enqueue(object : Callback<SetorResponse> {
            override fun onResponse(call: Call<SetorResponse>, response: Response<SetorResponse>) {
                btnRequestOtp.isEnabled = true

                if (response.isSuccessful) {
                    Toast.makeText(this@LaporanActivity, "Kode terkirim ke email!", Toast.LENGTH_LONG).show()
                    btnRequestOtp.visibility = View.GONE
                    layoutInputOtp.visibility = View.VISIBLE
                } else {
                    // --- PERBAIKAN: BACA ERROR ASLI DARI SERVER ---
                    val errorMsg = try {
                        val errorBodyString = response.errorBody()?.string()
                        val jsonObject = JSONObject(errorBodyString ?: "")
                        jsonObject.getString("message") // Ambil pesan dari Laravel
                    } catch (e: Exception) {
                        "Gagal mengirim email (Error ${response.code()})"
                    }

                    Toast.makeText(this@LaporanActivity, errorMsg, Toast.LENGTH_LONG).show()
                }
            }

            override fun onFailure(call: Call<SetorResponse>, t: Throwable) {
                btnRequestOtp.isEnabled = true
                btnRequestOtp.text = "Kirim Kode ke Email Saya"
                Toast.makeText(this@LaporanActivity, "Koneksi Gagal: ${t.message}", Toast.LENGTH_SHORT).show()
            }
        })
    }

    private fun verifyOtpAndDownload(otpCode: String) {
        val token = sessionManager.fetchAuthToken() ?: return

        btnVerify.isEnabled = false
        btnVerify.text = "Memverifikasi..."

        val request = OtpRequest(otpCode)

        ApiClient.instance.verifyLaporanOtp(token, request).enqueue(object : Callback<LaporanResponse> {
            override fun onResponse(call: Call<LaporanResponse>, response: Response<LaporanResponse>) {
                btnVerify.isEnabled = true
                btnVerify.text = "Verifikasi & Download PDF"

                if (response.isSuccessful) {
                    val url = response.body()?.urlPdf
                    if (!url.isNullOrEmpty()) {
                        val browserIntent = Intent(Intent.ACTION_VIEW, Uri.parse(url))
                        startActivity(browserIntent)
                        finish()
                    } else {
                        Toast.makeText(this@LaporanActivity, "Link PDF tidak ditemukan", Toast.LENGTH_SHORT).show()
                    }
                } else {
                    // Tampilkan error verifikasi juga
                    val errorMsg = try {
                        val json = JSONObject(response.errorBody()?.string() ?: "")
                        json.getString("message")
                    } catch (e: Exception) {
                        "Kode OTP Salah / Kadaluarsa"
                    }
                    Toast.makeText(this@LaporanActivity, errorMsg, Toast.LENGTH_LONG).show()
                }
            }

            override fun onFailure(call: Call<LaporanResponse>, t: Throwable) {
                btnVerify.isEnabled = true
                Toast.makeText(this@LaporanActivity, "Koneksi Gagal", Toast.LENGTH_SHORT).show()
            }
        })
    }
}