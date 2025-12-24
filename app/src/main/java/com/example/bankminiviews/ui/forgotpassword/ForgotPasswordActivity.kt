package com.example.bankminiviews.ui.forgotpassword

import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.example.bankminiviews.R
import com.example.bankminiviews.data.model.ForgotPasswordRequest
import com.example.bankminiviews.data.model.SetorResponse
import com.example.bankminiviews.data.network.ApiClient
import com.google.android.material.textfield.TextInputEditText
import org.json.JSONObject // Import ini penting
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class ForgotPasswordActivity : AppCompatActivity() {

    private lateinit var etNoRek: TextInputEditText
    private lateinit var etEmail: TextInputEditText
    private lateinit var btnKirim: Button

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_forgot_password)

        etNoRek = findViewById(R.id.etNoRek)
        etEmail = findViewById(R.id.etEmail)
        btnKirim = findViewById(R.id.btnKirimKode)

        btnKirim.setOnClickListener {
            val noRek = etNoRek.text.toString().trim()
            val email = etEmail.text.toString().trim()

            if (noRek.isEmpty() || email.isEmpty()) {
                Toast.makeText(this, "Mohon lengkapi data", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }

            requestOtp(noRek, email)
        }
    }

    private fun requestOtp(noRek: String, email: String) {
        btnKirim.isEnabled = false
        btnKirim.text = "Mengirim..."

        val request = ForgotPasswordRequest(noRek, email)

        ApiClient.instance.requestForgotPasswordOtp(request).enqueue(object : Callback<SetorResponse> {
            override fun onResponse(call: Call<SetorResponse>, response: Response<SetorResponse>) {
                btnKirim.isEnabled = true
                btnKirim.text = "Kirim Kode OTP"

                if (response.isSuccessful) {
                    Toast.makeText(this@ForgotPasswordActivity, "OTP Terkirim! Cek Email.", Toast.LENGTH_LONG).show()

                    val intent = Intent(this@ForgotPasswordActivity, VerifyOtpActivity::class.java)
                    intent.putExtra("NO_REK", noRek)
                    startActivity(intent)
                    finish()
                } else {
                    // --- TANGKAP ERROR DARI SERVER ---
                    val errorMsg = try {
                        val errorBody = response.errorBody()?.string()
                        val jsonObject = JSONObject(errorBody ?: "")
                        jsonObject.getString("message") // Ambil pesan dari Laravel
                    } catch (e: Exception) {
                        "Gagal: Data tidak cocok (404)"
                    }

                    Toast.makeText(this@ForgotPasswordActivity, errorMsg, Toast.LENGTH_LONG).show()
                }
            }

            override fun onFailure(call: Call<SetorResponse>, t: Throwable) {
                btnKirim.isEnabled = true
                btnKirim.text = "Kirim Kode OTP"
                Toast.makeText(this@ForgotPasswordActivity, "Koneksi Gagal", Toast.LENGTH_SHORT).show()
            }
        })
    }
}