package com.example.bankminiviews.ui.login

import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.TextView
import androidx.appcompat.app.AppCompatActivity
import com.example.bankminiviews.R
import com.example.bankminiviews.data.model.LoginRequest
import com.example.bankminiviews.data.model.LoginResponse
import com.example.bankminiviews.data.network.ApiClient
import com.example.bankminiviews.ui.dashboard.DashboardActivity
import com.example.bankminiviews.ui.forgotpassword.ForgotPasswordActivity
import com.example.bankminiviews.util.SessionManager
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.google.android.material.textfield.TextInputEditText
import com.google.android.material.textfield.TextInputLayout
import org.json.JSONObject
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class LoginActivity : AppCompatActivity() {

    private lateinit var sessionManager: SessionManager

    // Deklarasi View (Sesuai ID di XML baru)
    private lateinit var layoutUsername: TextInputLayout
    private lateinit var layoutPassword: TextInputLayout
    private lateinit var etUsername: TextInputEditText
    private lateinit var etPassword: TextInputEditText
    private lateinit var btnLogin: Button
    private lateinit var tvLupaPass: TextView

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_login)

        sessionManager = SessionManager(this)

        // Inisialisasi View
        layoutUsername = findViewById(R.id.textFieldUsername)
        layoutPassword = findViewById(R.id.textFieldPassword)
        etUsername = findViewById(R.id.editTextUsername)
        etPassword = findViewById(R.id.editTextPassword)
        btnLogin = findViewById(R.id.buttonLogin)
        tvLupaPass = findViewById(R.id.tvLupaPassword)

        // Tombol Login
        btnLogin.setOnClickListener {
            // Ambil input (Username sekarang berisi No Rekening)
            val noRekeningInput = etUsername.text.toString().trim()
            val passwordInput = etPassword.text.toString().trim()

            // Reset error
            layoutUsername.error = null
            layoutPassword.error = null

            // Validasi Input
            if (validateInput(noRekeningInput, passwordInput)) {
                login(noRekeningInput, passwordInput)
            }
        }

        // Tombol Lupa Password
        tvLupaPass.setOnClickListener {
            startActivity(Intent(this, ForgotPasswordActivity::class.java))
        }
    }

    private fun validateInput(noRekening: String, password: String): Boolean {
        var isValid = true
        if (noRekening.isEmpty()) {
            layoutUsername.error = "Nomor Rekening tidak boleh kosong"
            isValid = false
        }
        if (password.isEmpty()) {
            layoutPassword.error = "Password wajib diisi"
            isValid = false
        }
        return isValid
    }

    private fun login(noRekening: String, password: String) {
        setLoadingState(true)

        // Membuat request login (no_rekening & password)
        val loginRequest = LoginRequest(noRekening, password)

        ApiClient.instance.loginNasabah(loginRequest)
            .enqueue(object : Callback<LoginResponse> {
                override fun onResponse(call: Call<LoginResponse>, response: Response<LoginResponse>) {
                    setLoadingState(false)

                    if (response.isSuccessful) {
                        val loginResponse = response.body()
                        // Cek token (Kunci JSON: token)
                        if (loginResponse != null && loginResponse.token != null) {
                            // Simpan token & pindah halaman
                            sessionManager.saveAuthToken(loginResponse.token)
                            navigateToDashboard()
                        } else {
                            showErrorDialog("Gagal Masuk", "Token tidak ditemukan dalam respons server.")
                        }
                    } else {
                        // --- MENANGANI ERROR DARI LARAVEL (401, 422, 500) ---
                        val errorBody = response.errorBody()?.string()
                        val errorMessage = try {
                            val jsonObject = JSONObject(errorBody ?: "")

                            // Cek jika ada field 'errors' (Validasi 422 Laravel)
                            if (jsonObject.has("errors")) {
                                val errors = jsonObject.getJSONObject("errors")
                                // Ambil pesan error pertama dari field manapun
                                val keys = errors.keys()
                                if (keys.hasNext()) {
                                    val firstKey = keys.next()
                                    errors.getJSONArray(firstKey).getString(0)
                                } else {
                                    jsonObject.getString("message")
                                }
                            } else {
                                // Ambil pesan error global ('message')
                                jsonObject.getString("message")
                            }
                        } catch (e: Exception) {
                            "Terjadi kesalahan kode: ${response.code()}"
                        }

                        showErrorDialog("Login Gagal", errorMessage)
                    }
                }

                override fun onFailure(call: Call<LoginResponse>, t: Throwable) {
                    setLoadingState(false)
                    showErrorDialog("Koneksi Gagal", "Tidak dapat terhubung ke server.\nCek internet atau IP Address laptop Anda.")
                }
            })
    }

    private fun showErrorDialog(title: String, message: String) {
        MaterialAlertDialogBuilder(this)
            .setTitle(title)
            .setMessage(message)
            .setPositiveButton("Oke") { dialog, _ -> dialog.dismiss() }
            .show()
    }

    private fun setLoadingState(isLoading: Boolean) {
        if (isLoading) {
            btnLogin.isEnabled = false
            btnLogin.text = "Memproses..."
        } else {
            btnLogin.isEnabled = true
            btnLogin.text = "Login"
        }
    }

    private fun navigateToDashboard() {
        val intent = Intent(this, DashboardActivity::class.java)
        // Hapus history login agar tombol back tidak kembali ke login
        intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
        startActivity(intent)
        finish()
    }
}