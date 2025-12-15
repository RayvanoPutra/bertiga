package com.example.bankminiviews.ui.login

import android.content.Intent
import android.os.Bundle
import android.widget.Button
import androidx.appcompat.app.AppCompatActivity
import com.example.bankminiviews.R
import com.example.bankminiviews.data.model.LoginRequest
import com.example.bankminiviews.data.model.LoginResponse
import com.example.bankminiviews.data.network.ApiClient
import com.example.bankminiviews.ui.dashboard.DashboardActivity
import com.example.bankminiviews.util.SessionManager
import com.google.android.material.dialog.MaterialAlertDialogBuilder // Import Dialog Cantik
import com.google.android.material.textfield.TextInputEditText
import com.google.android.material.textfield.TextInputLayout // Import Layout Input
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class LoginActivity : AppCompatActivity() {

    private lateinit var sessionManager: SessionManager

    // Deklarasi View
    // Kita butuh Layout-nya untuk menampilkan error merah, bukan cuma EditText-nya
    private lateinit var layoutUsername: TextInputLayout
    private lateinit var layoutPassword: TextInputLayout

    private lateinit var etUsername: TextInputEditText
    private lateinit var etPassword: TextInputEditText
    private lateinit var btnLogin: Button

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_login)

        sessionManager = SessionManager(this)

        // 1. Inisialisasi View
        // Pastikan ID ini sesuai dengan di activity_login.xml Anda
        layoutUsername = findViewById(R.id.textFieldUsername)
        layoutPassword = findViewById(R.id.textFieldPassword)

        etUsername = findViewById(R.id.editTextUsername)
        etPassword = findViewById(R.id.editTextPassword)
        btnLogin = findViewById(R.id.buttonLogin)

        // 2. Listener Tombol Login
        btnLogin.setOnClickListener {
            val username = etUsername.text.toString().trim()
            val password = etPassword.text.toString().trim()

            // Bersihkan error sebelumnya (jika ada)
            layoutUsername.error = null
            layoutPassword.error = null

            // Validasi Input Kosong (Desain Merah di Bawah Input)
            if (validateInput(username, password)) {
                login(username, password)
            }
        }
    }

    /**
     * Fungsi untuk mengecek input kosong.
     * Jika kosong, tampilkan pesan merah di bawah kotak input.
     */
    private fun validateInput(username: String, password: String): Boolean {
        var isValid = true

        if (username.isEmpty()) {
            layoutUsername.error = "Username tidak boleh kosong" // Muncul teks merah
            isValid = false
        }

        if (password.isEmpty()) {
            layoutPassword.error = "Password wajib diisi" // Muncul teks merah
            isValid = false
        }

        return isValid
    }

    private fun login(username: String, password: String) {
        // Ubah tombol jadi loading
        setLoadingState(true)

        val loginRequest = LoginRequest(username, password)

        ApiClient.instance.loginNasabah(loginRequest)
            .enqueue(object : Callback<LoginResponse> {

                override fun onResponse(call: Call<LoginResponse>, response: Response<LoginResponse>) {
                    setLoadingState(false) // Kembalikan tombol

                    if (response.isSuccessful) {
                        val loginResponse = response.body()

                        if (loginResponse != null && loginResponse.token != null) {
                            // LOGIN BERHASIL
                            sessionManager.saveAuthToken(loginResponse.token)

                            // Langsung pindah (tidak perlu dialog kalau sukses)
                            navigateToDashboard()
                        } else {
                            // Respons sukses tapi data kosong (Jarang terjadi)
                            showErrorDialog("Gagal Masuk", "Terjadi kesalahan data. Silakan coba lagi.")
                        }
                    } else {
                        // LOGIN GAGAL (Password Salah / Akun Tidak Ditemukan)
                        // Kode 401 biasanya muncul di sini
                        handleLoginError(response.code())
                    }
                }

                override fun onFailure(call: Call<LoginResponse>, t: Throwable) {
                    setLoadingState(false)
                    // Error Jaringan (Mati lampu / Server mati)
                    showErrorDialog("Koneksi Gagal", "Tidak dapat terhubung ke server.\nPastikan internet Anda lancar.")
                }
            })
    }

    /**
     * Menangani pesan error berdasarkan kode HTTP dari server
     */
    private fun handleLoginError(code: Int) {
        when (code) {
            401 -> {
                // Ini yang paling sering (Password Salah)
                showErrorDialog(
                    "Login Gagal",
                    "Username atau Password yang Anda masukkan salah.\nSilakan periksa kembali."
                )
            }
            404 -> {
                showErrorDialog("Terjadi Kesalahan", "Alamat server tidak ditemukan (404).")
            }
            500 -> {
                showErrorDialog("Gangguan Server", "Server sedang mengalami masalah. Mohon coba beberapa saat lagi.")
            }
            else -> {
                showErrorDialog("Gagal", "Terjadi kesalahan dengan kode: $code")
            }
        }
    }

    /**
     * Menampilkan Dialog Cantik (Pop-up)
     */
    private fun showErrorDialog(title: String, message: String) {
        MaterialAlertDialogBuilder(this)
            .setTitle(title)
            .setMessage(message)
            .setPositiveButton("Oke") { dialog, _ ->
                dialog.dismiss()
            }
            .show()
    }

    /**
     * Mengatur tampilan tombol saat loading
     */
    private fun setLoadingState(isLoading: Boolean) {
        if (isLoading) {
            btnLogin.isEnabled = false
            btnLogin.text = "Sedang Memproses..."
        } else {
            btnLogin.isEnabled = true
            btnLogin.text = "Login"
        }
    }

    private fun navigateToDashboard() {
        val intent = Intent(this, DashboardActivity::class.java)
        startActivity(intent)
        finish()
    }
}