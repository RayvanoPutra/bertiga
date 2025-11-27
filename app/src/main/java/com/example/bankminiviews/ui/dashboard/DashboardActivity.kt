package com.example.bankminiviews.ui.dashboard

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.widget.Button
import android.widget.ImageButton
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout // <-- IMPORT BARU
import com.example.bankminiviews.R
import com.example.bankminiviews.data.model.HistoryItemResponse
import com.example.bankminiviews.data.model.NasabahData
import com.example.bankminiviews.data.network.ApiClient
import com.example.bankminiviews.ui.history.HistoryActivity
import com.example.bankminiviews.ui.history.HistoryAdapter
import com.example.bankminiviews.ui.login.LoginActivity
import com.example.bankminiviews.ui.setor.SetorActivity
import com.example.bankminiviews.util.SessionManager
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response
import java.text.NumberFormat
import java.util.Locale

class DashboardActivity : AppCompatActivity() {

    private lateinit var sessionManager: SessionManager
    private var currentToken: String? = null

    // Deklarasi View
    private lateinit var tvNama: TextView
    private lateinit var tvSaldo: TextView
    private lateinit var btnToggleSaldo: ImageButton
    private lateinit var tvNoRekening: TextView
    private lateinit var buttonSetor: Button
    private lateinit var buttonRiwayat: Button
    private lateinit var recyclerRecentTransactions: RecyclerView
    private lateinit var textNoTransaksi: TextView
    private lateinit var swipeRefresh: SwipeRefreshLayout // <-- VIEW BARU

    private var historyAdapter: HistoryAdapter? = null

    // Variabel state
    private var saldoAsli: Long = 0
    private var saldoAsliNoRek: String = ""
    private var isSaldoVisible: Boolean = true

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_dashboard)

        sessionManager = SessionManager(this)
        initViews()

        currentToken = sessionManager.fetchAuthToken()
        if (currentToken == null) {
            Toast.makeText(this, "Anda harus login terlebih dahulu", Toast.LENGTH_LONG).show()
            redirectToLogin()
            return
        }

        setupListeners()
    }

    override fun onResume() {
        super.onResume()
        // Refresh otomatis saat kembali ke layar ini
        currentToken?.let {
            loadInitialData(it)
        }
    }

    private fun initViews() {
        tvNama = findViewById(R.id.textViewSelamatDatang)
        tvSaldo = findViewById(R.id.textViewSaldo)
        btnToggleSaldo = findViewById(R.id.buttonToggleSaldo)
        tvNoRekening = findViewById(R.id.textViewNoRekening)
        buttonSetor = findViewById(R.id.buttonSetor)
        buttonRiwayat = findViewById(R.id.buttonRiwayat)
        recyclerRecentTransactions = findViewById(R.id.recyclerRecentTransactions)
        textNoTransaksi = findViewById(R.id.textNoTransaksi)

        // Inisialisasi Swipe Refresh
        swipeRefresh = findViewById(R.id.swipeRefresh) // <-- HUBUNGKAN ID

        recyclerRecentTransactions.layoutManager = LinearLayoutManager(this)
    }

    private fun setupListeners() {
        btnToggleSaldo.setOnClickListener {
            isSaldoVisible = !isSaldoVisible
            updateSaldoVisibility()
        }
        buttonSetor.setOnClickListener {
            val intent = Intent(this, SetorActivity::class.java)
            startActivity(intent)
        }
        buttonRiwayat.setOnClickListener {
            val intent = Intent(this, HistoryActivity::class.java)
            startActivity(intent)
        }

        // --- LISTENER TARIK KE BAWAH ---
        swipeRefresh.setOnRefreshListener {
            currentToken?.let { token ->
                // Panggil ulang data
                fetchDashboardData(token)
                fetchHistoryData(token)
            } ?: run {
                swipeRefresh.isRefreshing = false
            }
        }
    }

    private fun loadInitialData(token: String) {
        // Panggil API (Loading manual tidak perlu di sini,
        // karena SwipeRefresh sudah punya loading sendiri)
        fetchDashboardData(token)
        fetchHistoryData(token)
    }

    private fun fetchDashboardData(token: String) {
        ApiClient.instance.getNasabahData(token)
            .enqueue(object: Callback<NasabahData> {
                override fun onResponse(call: Call<NasabahData>, response: Response<NasabahData>) {
                    if (response.isSuccessful) {
                        response.body()?.let { updateUI(it) }
                    } else if (response.code() == 401) {
                        sessionManager.clearToken()
                        redirectToLogin()
                    }
                }
                override fun onFailure(call: Call<NasabahData>, t: Throwable) {
                    tvNama.text = "Gagal memuat saldo"
                }
            })
    }

    private fun fetchHistoryData(token: String) {
        ApiClient.instance.getHistory(token)
            .enqueue(object : Callback<List<HistoryItemResponse>> {
                override fun onResponse(
                    call: Call<List<HistoryItemResponse>>,
                    response: Response<List<HistoryItemResponse>>
                ) {
                    // --- HENTIKAN LOADING SWIPE REFRESH ---
                    swipeRefresh.isRefreshing = false

                    if (response.isSuccessful) {
                        val allHistory = response.body()
                        if (allHistory != null && allHistory.isNotEmpty()) {
                            val recentHistory = allHistory.take(5)
                            recyclerRecentTransactions.visibility = View.VISIBLE
                            textNoTransaksi.visibility = View.GONE
                            historyAdapter = HistoryAdapter(this@DashboardActivity, recentHistory)
                            recyclerRecentTransactions.adapter = historyAdapter
                        } else {
                            recyclerRecentTransactions.visibility = View.GONE
                            textNoTransaksi.visibility = View.VISIBLE
                            textNoTransaksi.text = "Tidak ada transaksi terbaru."
                        }
                    } else {
                        recyclerRecentTransactions.visibility = View.GONE
                        textNoTransaksi.visibility = View.VISIBLE
                        textNoTransaksi.text = "Gagal memuat riwayat."
                    }
                }

                override fun onFailure(call: Call<List<HistoryItemResponse>>, t: Throwable) {
                    // --- HENTIKAN LOADING SWIPE REFRESH ---
                    swipeRefresh.isRefreshing = false

                    recyclerRecentTransactions.visibility = View.GONE
                    textNoTransaksi.visibility = View.VISIBLE
                    textNoTransaksi.text = "Koneksi Gagal."
                }
            })
    }

    private fun updateUI(data: NasabahData) {
        saldoAsli = data.saldo
        saldoAsliNoRek = data.noRekening
        tvNama.text = "Hi, ${data.nama}!"
        updateSaldoVisibility()
    }

    private fun updateSaldoVisibility() {
        if (isSaldoVisible) {
            val formatter = NumberFormat.getCurrencyInstance(Locale("in", "ID"))
            tvSaldo.text = formatter.format(saldoAsli)
            tvNoRekening.text = "No. Rek: $saldoAsliNoRek"
            btnToggleSaldo.setImageResource(R.drawable.ic_visibility)
        } else {
            tvSaldo.text = "Rp ••••••"
            tvNoRekening.text = "No. Rek: ••••••••••"
            btnToggleSaldo.setImageResource(R.drawable.ic_visibility_off)
        }
    }

    private fun redirectToLogin() {
        val intent = Intent(this, LoginActivity::class.java)
        intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
        startActivity(intent)
        finish()
    }
}