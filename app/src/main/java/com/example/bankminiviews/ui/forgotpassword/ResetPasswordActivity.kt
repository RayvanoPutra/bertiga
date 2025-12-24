package com.example.bankminiviews.ui.forgotpassword

import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.example.bankminiviews.R
import com.example.bankminiviews.data.model.ResetPasswordRequest
import com.example.bankminiviews.data.model.SetorResponse
import com.example.bankminiviews.data.network.ApiClient
import com.example.bankminiviews.ui.login.LoginActivity
import com.google.android.material.textfield.TextInputEditText
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class ResetPasswordActivity : AppCompatActivity() {

    private lateinit var etPass: TextInputEditText
    private lateinit var etConfPass: TextInputEditText
    private lateinit var btnSimpan: Button

    private var noRekening: String? = null
    private var otpCode: String? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_reset_password)

        noRekening = intent.getStringExtra("NO_REK")
        otpCode = intent.getStringExtra("OTP_CODE")

        etPass = findViewById(R.id.etPass)
        etConfPass = findViewById(R.id.etConfPass)
        btnSimpan = findViewById(R.id.btnSimpanPass)

        btnSimpan.setOnClickListener {
            val pass = etPass.text.toString()
            val confPass = etConfPass.text.toString()

            if (pass.isEmpty() || pass.length < 6) {
                Toast.makeText(this, "Password minimal 6 karakter", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }

            if (pass != confPass) {
                Toast.makeText(this, "Konfirmasi password tidak cocok", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }

            doResetPassword(pass, confPass)
        }
    }

    private fun doResetPassword(pass: String, confPass: String) {
        btnSimpan.isEnabled = false
        btnSimpan.text = "Menyimpan..."

        val request = ResetPasswordRequest(noRekening!!, otpCode!!, pass, confPass)

        ApiClient.instance.resetPassword(request).enqueue(object : Callback<SetorResponse> {
            override fun onResponse(call: Call<SetorResponse>, response: Response<SetorResponse>) {
                if (response.isSuccessful) {
                    Toast.makeText(this@ResetPasswordActivity, "Password Berhasil Diubah! Silakan Login.", Toast.LENGTH_LONG).show()

                    // Kembali ke halaman login utama dan hapus semua history activity
                    val intent = Intent(this@ResetPasswordActivity, LoginActivity::class.java)
                    intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
                    startActivity(intent)
                    finish()
                } else {
                    btnSimpan.isEnabled = true
                    btnSimpan.text = "Simpan Password"
                    Toast.makeText(this@ResetPasswordActivity, "Gagal mengubah password.", Toast.LENGTH_LONG).show()
                }
            }

            override fun onFailure(call: Call<SetorResponse>, t: Throwable) {
                btnSimpan.isEnabled = true
                btnSimpan.text = "Simpan Password"
                Toast.makeText(this@ResetPasswordActivity, "Koneksi Gagal", Toast.LENGTH_SHORT).show()
            }
        })
    }
}