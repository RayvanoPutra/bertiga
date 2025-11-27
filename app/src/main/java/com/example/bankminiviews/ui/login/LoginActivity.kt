package com.example.bankminiviews.ui.login

import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.example.bankminiviews.R
import com.example.bankminiviews.data.model.LoginRequest
import com.example.bankminiviews.data.model.LoginResponse
import com.example.bankminiviews.data.network.ApiClient
import com.example.bankminiviews.ui.dashboard.DashboardActivity
import com.example.bankminiviews.util.SessionManager
import com.google.android.material.textfield.TextInputEditText
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

// Pastikan import R sudah benar (sesuai package Anda)
// import com.example.bankminiviews.R

class LoginActivity : AppCompatActivity() {

    private lateinit var sessionManager: SessionManager

    // Deklarasi View
    private lateinit var etUsername: TextInputEditText
    private lateinit var etPassword: TextInputEditText
    private lateinit var btnLogin: Button

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_login)

        sessionManager = SessionManager(this)

        // 1. Referensi ke View
        // Perhatikan: ID-nya adalah 'editTextUsername' dan 'editTextPassword'
        // yang ada di dalam TextInputLayout
        etUsername = findViewById(R.id.editTextUsername)
        etPassword = findViewById(R.id.editTextPassword)
        btnLogin = findViewById(R.id.buttonLogin)

        // 2. Set OnClickListener untuk Tombol Login
        btnLogin.setOnClickListener {
            val username = etUsername.text.toString().trim()
            val password = etPassword.text.toString().trim()

            if (username.isEmpty() || password.isEmpty()) {
                Toast.makeText(this, "Username dan Password tidak boleh kosong", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }

            // Panggil fungsi login
            login(username, password)
        }
    }

    private fun login(username: String, password: String) {
        // Tampilkan loading (jika ada)
        btnLogin.isEnabled = false
        btnLogin.text = "Loading..."

        val loginRequest = LoginRequest(username, password)

        ApiClient.instance.loginNasabah(loginRequest)
            .enqueue(object : Callback<LoginResponse> {

                override fun onResponse(call: Call<LoginResponse>, response: Response<LoginResponse>) {
                    // Kembalikan tombol ke normal
                    btnLogin.isEnabled = true
                    btnLogin.text = "Login"

                    if (response.isSuccessful) {
                        val loginResponse = response.body()

                        // Kita pakai logika 'if' yang sudah diperbaiki
                        if (loginResponse != null && loginResponse.token != null) {

                            // LOGIN BERHASIL
                            sessionManager.saveAuthToken(loginResponse.token)
                            Toast.makeText(this@LoginActivity, loginResponse.message, Toast.LENGTH_SHORT).show()

                            // Pindah ke Dashboard
                            navigateToDashboard()

                        } else {
                            // Gagal (misal: password salah dari server)
                            Toast.makeText(this@LoginActivity, loginResponse?.message ?: "Login Gagal", Toast.LENGTH_LONG).show()
                        }
                    } else {
                        // Gagal (Error server 404, 500, 422, dll)
                        Toast.makeText(this@LoginActivity, "Login Gagal. Kode: ${response.code()}", Toast.LENGTH_LONG).show()
                    }
                }

                override fun onFailure(call: Call<LoginResponse>, t: Throwable) {
                    // Kembalikan tombol ke normal
                    btnLogin.isEnabled = true
                    btnLogin.text = "Login"

                    // Gagal (Tidak ada koneksi, URL salah, dll)
                    Toast.makeText(this@LoginActivity, "Koneksi Gagal: ${t.message}", Toast.LENGTH_LONG).show()
                }
            })
    }

    private fun navigateToDashboard() {
        val intent = Intent(this, DashboardActivity::class.java)
        startActivity(intent)
        finish() // Tutup LoginActivity agar tidak bisa kembali
    }
}