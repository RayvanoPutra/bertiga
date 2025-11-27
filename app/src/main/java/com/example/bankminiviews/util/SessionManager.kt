package com.example.bankminiviews.util // Pastikan nama paketnya sama

import android.content.Context
import android.content.SharedPreferences

class SessionManager(context: Context) {

    private var prefs: SharedPreferences =
        context.getSharedPreferences("BankMiniApp", Context.MODE_PRIVATE)

    companion object {
        const val AUTH_TOKEN = "auth_token"
    }

    /**
     * Menyimpan Auth Token
     */
    fun saveAuthToken(token: String) {
        val editor = prefs.edit()
        editor.putString(AUTH_TOKEN, "Bearer $token") // Simpan dengan "Bearer "
        editor.apply()
    }

    /**
     * Mengambil Auth Token
     */
    fun fetchAuthToken(): String? {
        return prefs.getString(AUTH_TOKEN, null)
    }

    /**
     * Menghapus Token (untuk Logout)
     */
    fun clearToken() {
        val editor = prefs.edit()
        editor.clear()
        editor.apply()
    }
}