package com.example.bankminiviews.ui.forgotpassword

import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.example.bankminiviews.R
import com.example.bankminiviews.data.model.SetorResponse
import com.example.bankminiviews.data.model.VerifyOtpRequest
import com.example.bankminiviews.data.network.ApiClient
import com.google.android.material.textfield.TextInputEditText
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class VerifyOtpActivity : AppCompatActivity() {

    private lateinit var etOtp: TextInputEditText
    private lateinit var btnVerifikasi: Button
    private var noRekening: String? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_verify_otp)

        // Ambil No Rekening yang dikirim dari activity sebelumnya
        noRekening = intent.getStringExtra("NO_REK")

        if (noRekening == null) {
            Toast.makeText(this, "Data Error", Toast.LENGTH_SHORT).show()
            finish()
            return
        }

        etOtp = findViewById(R.id.etOtp)
        btnVerifikasi = findViewById(R.id.btnVerifikasi)

        btnVerifikasi.setOnClickListener {
            val otpCode = etOtp.text.toString().trim()
            if (otpCode.length < 6) {
                Toast.makeText(this, "Masukkan 6 digit kode OTP", Toast.LENGTH_SHORT).show()
            } else {
                verifyOtp(otpCode)
            }
        }
    }

    private fun verifyOtp(otpCode: String) {
        btnVerifikasi.isEnabled = false
        btnVerifikasi.text = "Memverifikasi..."

        val request = VerifyOtpRequest(noRekening!!, otpCode)

        ApiClient.instance.verifyForgotPasswordOtp(request).enqueue(object : Callback<SetorResponse> {
            override fun onResponse(call: Call<SetorResponse>, response: Response<SetorResponse>) {
                btnVerifikasi.isEnabled = true
                btnVerifikasi.text = "Verifikasi Kode"

                if (response.isSuccessful) {
                    Toast.makeText(this@VerifyOtpActivity, "OTP Benar!", Toast.LENGTH_SHORT).show()

                    // Pindah ke halaman Reset Password
                    val intent = Intent(this@VerifyOtpActivity, ResetPasswordActivity::class.java)
                    intent.putExtra("NO_REK", noRekening)
                    intent.putExtra("OTP_CODE", otpCode) // Bawa OTP untuk validasi akhir
                    startActivity(intent)
                    finish()
                } else {
                    Toast.makeText(this@VerifyOtpActivity, "Kode Salah / Kadaluarsa", Toast.LENGTH_LONG).show()
                }
            }

            override fun onFailure(call: Call<SetorResponse>, t: Throwable) {
                btnVerifikasi.isEnabled = true
                btnVerifikasi.text = "Verifikasi Kode"
                Toast.makeText(this@VerifyOtpActivity, "Koneksi Gagal", Toast.LENGTH_SHORT).show()
            }
        })
    }
}