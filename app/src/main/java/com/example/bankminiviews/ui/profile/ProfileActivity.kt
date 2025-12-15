package com.example.bankminiviews.ui.profile

import android.os.Bundle
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.example.bankminiviews.R
import com.example.bankminiviews.data.model.NasabahData
import com.example.bankminiviews.data.network.ApiClient
import com.example.bankminiviews.util.SessionManager
import com.google.android.material.appbar.MaterialToolbar
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class ProfileActivity : AppCompatActivity() {

    private lateinit var sessionManager: SessionManager

    // View
    private lateinit var textNama: TextView
    private lateinit var textNis: TextView
    private lateinit var tvEmail: TextView
    private lateinit var tvNoHp: TextView
    private lateinit var tvKelas: TextView
    private lateinit var tvJurusan: TextView
    private lateinit var toolbar: MaterialToolbar

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_profile)

        sessionManager = SessionManager(this)

        // Init View
        textNama = findViewById(R.id.textNamaProfile)
        textNis = findViewById(R.id.textNisProfile)
        tvEmail = findViewById(R.id.tvEmail)
        tvNoHp = findViewById(R.id.tvNoHp)
        tvKelas = findViewById(R.id.tvKelas)
        tvJurusan = findViewById(R.id.tvJurusan)
        toolbar = findViewById(R.id.toolbarProfile)

        toolbar.setNavigationOnClickListener { finish() }

        val token = sessionManager.fetchAuthToken()
        if (token != null) {
            fetchProfileData(token)
        }
    }

    private fun fetchProfileData(token: String) {
        ApiClient.instance.getNasabahData(token).enqueue(object : Callback<NasabahData> {
            override fun onResponse(call: Call<NasabahData>, response: Response<NasabahData>) {
                if (response.isSuccessful) {
                    response.body()?.let { updateUI(it) }
                } else {
                    Toast.makeText(this@ProfileActivity, "Gagal memuat profil", Toast.LENGTH_SHORT).show()
                }
            }

            override fun onFailure(call: Call<NasabahData>, t: Throwable) {
                Toast.makeText(this@ProfileActivity, "Koneksi Gagal", Toast.LENGTH_SHORT).show()
            }
        })
    }

    private fun updateUI(data: NasabahData) {
        textNama.text = data.nama
        textNis.text = "NIS: ${data.noInduk}"

        tvEmail.text = data.email ?: "-" // Jika kosong, tampilkan strip
        tvNoHp.text = data.noHp ?: "-"

        // Ambil data dari objek kelas
        tvKelas.text = data.kelas?.namaKelas ?: "Belum diatur"
        tvJurusan.text = data.kelas?.jurusan?.namaJurusan ?: "Belum diatur"
    }
}