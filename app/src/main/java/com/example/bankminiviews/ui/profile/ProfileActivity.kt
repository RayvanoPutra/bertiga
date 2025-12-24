package com.example.bankminiviews.ui.profile

import android.os.Bundle
import android.text.InputType
import android.view.View
import android.widget.Button
import android.widget.EditText
import android.widget.ImageButton
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import com.example.bankminiviews.R
import com.example.bankminiviews.data.model.NasabahData
import com.example.bankminiviews.data.model.SetorResponse
import com.example.bankminiviews.data.model.UpdateProfileRequest
import com.example.bankminiviews.data.network.ApiClient
import com.example.bankminiviews.util.SessionManager
import com.google.android.material.appbar.MaterialToolbar
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class ProfileActivity : AppCompatActivity() {

    private lateinit var sessionManager: SessionManager

    // View Display (Header)
    private lateinit var textNama: TextView
    private lateinit var textNis: TextView
    private lateinit var tvKelas: TextView
    private lateinit var tvJurusan: TextView

    // Input Editable
    private lateinit var etEmail: EditText
    private lateinit var etNoHp: EditText

    // Tombol
    private lateinit var btnEditEmail: ImageButton
    private lateinit var btnEditHp: ImageButton
    private lateinit var btnGantiPass: TextView
    private lateinit var btnSimpan: Button
    private lateinit var toolbar: MaterialToolbar

    private var originalData: NasabahData? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_profile)

        sessionManager = SessionManager(this)
        initViews()

        toolbar.setNavigationOnClickListener { finish() }

        val token = sessionManager.fetchAuthToken()
        if (token != null) {
            fetchProfileData(token)
        }

        // Listener Tombol Edit Email (Aktifkan Input)
        btnEditEmail.setOnClickListener {
            enableInput(etEmail)
        }

        // Listener Tombol Edit HP (Aktifkan Input)
        btnEditHp.setOnClickListener {
            enableInput(etNoHp)
        }

        // Listener Ganti Password (Munculkan Dialog)
        btnGantiPass.setOnClickListener {
            showChangePasswordDialog()
        }

        // Listener Simpan Perubahan
        btnSimpan.setOnClickListener {
            saveChanges()
        }
    }

    private fun initViews() {
        textNama = findViewById(R.id.textNamaProfile)
        textNis = findViewById(R.id.textNisProfile)
        tvKelas = findViewById(R.id.tvKelas)
        tvJurusan = findViewById(R.id.tvJurusan)

        etEmail = findViewById(R.id.etEmail)
        etNoHp = findViewById(R.id.etNoHp)

        btnEditEmail = findViewById(R.id.btnEditEmail)
        btnEditHp = findViewById(R.id.btnEditHp)
        btnGantiPass = findViewById(R.id.btnGantiPassword)
        btnSimpan = findViewById(R.id.btnSimpan)
        toolbar = findViewById(R.id.toolbarProfile)
    }

    private fun enableInput(editText: EditText) {
        editText.isEnabled = true
        editText.requestFocus()
        // Tampilkan tombol simpan jika ada perubahan
        btnSimpan.visibility = View.VISIBLE
    }

    private fun fetchProfileData(token: String) {
        ApiClient.instance.getNasabahData(token).enqueue(object : Callback<NasabahData> {
            override fun onResponse(call: Call<NasabahData>, response: Response<NasabahData>) {
                if (response.isSuccessful) {
                    response.body()?.let {
                        originalData = it
                        updateUI(it)
                    }
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
        textNis.text = "No. Rek: ${data.noRekening}" // Tampilkan No Rekening di Header

        tvKelas.text = data.kelas?.namaKelas ?: "-"
        tvJurusan.text = data.kelas?.jurusan?.namaJurusan ?: "-"

        etEmail.setText(data.email ?: "")
        etNoHp.setText(data.noHp ?: "")
    }

    // Dialog untuk Ganti Password
    private fun showChangePasswordDialog() {
        val input = EditText(this)
        input.hint = "Password Baru"
        input.inputType = InputType.TYPE_CLASS_TEXT or InputType.TYPE_TEXT_VARIATION_PASSWORD

        // Buat container untuk memberi margin pada input dialog
        val container = android.widget.FrameLayout(this)
        val params = android.widget.FrameLayout.LayoutParams(
            android.view.ViewGroup.LayoutParams.MATCH_PARENT,
            android.view.ViewGroup.LayoutParams.WRAP_CONTENT
        )

        // Hitung margin dalam pixel (24dp)
        val marginInDp = 24
        val marginInPx = (marginInDp * resources.displayMetrics.density).toInt()

        params.leftMargin = marginInPx
        params.rightMargin = marginInPx
        input.layoutParams = params
        container.addView(input)

        AlertDialog.Builder(this)
            .setTitle("Ganti Password")
            .setView(container)
            .setPositiveButton("Simpan") { dialog, _ ->
                val newPass = input.text.toString()
                if (newPass.length >= 6) {
                    // Panggil saveChanges dengan password baru
                    saveChanges(newPassword = newPass)
                } else {
                    Toast.makeText(this, "Password minimal 6 karakter", Toast.LENGTH_SHORT).show()
                }
            }
            .setNegativeButton("Batal", null)
            .show()
    }

    private fun saveChanges(newPassword: String? = null) {
        val token = sessionManager.fetchAuthToken() ?: return

        val newEmail = etEmail.text.toString().trim()
        val newHp = etNoHp.text.toString().trim()

        btnSimpan.isEnabled = false
        btnSimpan.text = "Menyimpan..."

        // KOREKSI UTAMA: Hapus 'username' dari request
        val request = UpdateProfileRequest(
            email = newEmail,
            noTelp = newHp,
            password = newPassword // Kirim null jika cuma edit email/hp, atau string jika ganti pass
        )

        ApiClient.instance.updateProfile(token, request).enqueue(object : Callback<SetorResponse> {
            override fun onResponse(call: Call<SetorResponse>, response: Response<SetorResponse>) {
                btnSimpan.isEnabled = true
                btnSimpan.text = "Simpan Perubahan"

                if (response.isSuccessful) {
                    Toast.makeText(this@ProfileActivity, "Berhasil diperbarui!", Toast.LENGTH_SHORT).show()

                    // Reset UI ke mode baca (read-only)
                    etEmail.isEnabled = false
                    etNoHp.isEnabled = false
                    btnSimpan.visibility = View.GONE

                    fetchProfileData(token) // Refresh data terbaru
                } else {
                    Toast.makeText(this@ProfileActivity, "Gagal update. Cek data Anda.", Toast.LENGTH_LONG).show()
                }
            }

            override fun onFailure(call: Call<SetorResponse>, t: Throwable) {
                btnSimpan.isEnabled = true
                btnSimpan.text = "Simpan Perubahan"
                Toast.makeText(this@ProfileActivity, "Koneksi Gagal", Toast.LENGTH_SHORT).show()
            }
        })
    }
}