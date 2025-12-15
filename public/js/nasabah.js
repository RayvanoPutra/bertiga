// nasabah.js
$(document).ready(function() {
    // ====================================================================
    // --- KONFIGURASI API & TOKEN ---
    // ====================================================================
    
    const apiBaseUrl = 'http://localhost:8000/api';
    const apiMasterDataPrefix = `${apiBaseUrl}/master`; 
    const apiUrlNasabah = `${apiBaseUrl}/nasabah`;
    const apiStoreNasabah = `${apiBaseUrl}/nasabah`; 
    
    let currentPage = 1;
    let biayaAdminPendaftaran = 0; // Global variable untuk menyimpan biaya admin
    
    // FUNGSI PENTING: Mendapatkan Auth Token 
    const getAuthToken = () => {
        // 🛑 PERBAIKAN KRITIS: GANTI STRING INI dengan token yang BARU dan VALID
        const hardcodedToken = '68|4Fu4brAT20xfbFDJhZ7diVSHeA3kgUQifmwpz4IM19245151'; // <-- CONTOH TOKEN VALID
        // Anda harus mendapatkan token baru setelah sukses login Petugas
        
        return localStorage.getItem('authToken') || hardcodedToken; 
    }

    // --- HELPER FUNCTIONS ---
    const formatRupiah = (angka) => {
        if (isNaN(angka) || angka === null) return 'Rp. 0';
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    };

    const showLoading = (targetId, message = 'Memuat data...') => {
        $(`#${targetId}`).empty().append(`<option value="">${message}</option>`);
    };

    // ====================================================================
    // --- LOGIKA PERHITUNGAN SALDO (REAL-TIME) ---
    // ====================================================================

    // Fungsi 1: Ambil nilai Biaya Admin dari API
    function getBiayaAdmin() {
        const authToken = getAuthToken();
        
        // 🛑 PERBAIKAN URL: Gunakan endpoint yang eksplisit untuk Pengaturan Biaya Admin
        $.ajax({
            url: `${apiMasterDataPrefix}/pengaturan/biaya-admin`, // Asumsi Anda buat route ini
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + authToken, 'Accept': 'application/json' },
            success: function(response) {
                // Asumsi API mengembalikan { nilai: 10000 }
                const nilaiAdmin = response.nilai || response.data?.nilai;
                biayaAdminPendaftaran = parseInt(nilaiAdmin) || 0;
                
                $('#minSaldo').text(formatRupiah(biayaAdminPendaftaran).replace('Rp', '').trim()); // Update teks minimal saldo (tanpa Rp.)
                calculateSaldo(); 
            },
            error: function(xhr) {
                console.error('Gagal memuat biaya admin. Error:', xhr.responseText);
                biayaAdminPendaftaran = 0; // Fallback ke 0
                $('#minSaldo').text('0');
            }
        });
    }

    // Fungsi 2: Hitung Saldo Real-Time
    function calculateSaldo() {
        const saldoAwal = parseInt($('#saldo_awal').val()) || 0;
        
        // Potongan hanya berlaku jika saldo awal >= biaya admin
        const potongan = (saldoAwal >= biayaAdminPendaftaran) ? biayaAdminPendaftaran : 0;
        const saldoBersih = saldoAwal - potongan;

        // Update tampilan
        $('#potongan_admin').val(formatRupiah(potongan));
        $('#hasil_bersih').val(formatRupiah(saldoBersih));

        // Tampilkan pesan error jika saldo awal kurang dari minimum yang dibutuhkan
        const errorDiv = $('#errorMessages');
        if (saldoAwal < biayaAdminPendaftaran) {
             errorDiv.html(`<p>Setoran Awal minimal ${formatRupiah(biayaAdminPendaftaran)} untuk menutupi biaya admin.</p>`).show();
        } else {
             errorDiv.empty().hide();
        }
    }

    // ====================================================================
    // --- LOGIKA MASTER DATA & DROPDOWN BERTINGKAT ---
    // ====================================================================

    // Fungsi Generik untuk Memuat Data Master (untuk Filter & Form)
    function loadMasterData(endpoint, selectId, valueKey, labelKey, prependOption = true) {
        const authToken = getAuthToken();
        const $select = $(`#${selectId}`);
        const apiPath = `${apiMasterDataPrefix}${endpoint}`; 

        // ... (sisanya sama seperti sebelumnya) ...
        if (!authToken) {
            $select.empty().append(`<option value="">⚠️ Token Belum Diatur</option>`);
            return;
        }
        
        showLoading(selectId);
        let options = '';
        if (prependOption) {
             let label = selectId.includes('Filter') ? selectId.replace('Filter', '') : selectId.replace('_id', '');
             options = `<option value="">Pilih ${label.replace(/([A-Z])/g, ' $1').trim()}</option>`; 
        }

        $.ajax({
            url: apiPath,
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + authToken, 'Accept': 'application/json' },
            success: function(response) {
                const dataArray = Array.isArray(response) ? response : response.data || [];
                // ... (sisanya sama seperti sebelumnya) ...
                if (dataArray.length > 0) {
                     $.each(dataArray, function(index, item) {
                         options += `<option value="${item[valueKey]}">${item[labelKey]}</option>`; 
                     });
                } else {
                     options = `<option value="">Tidak ada data</option>`;
                }
                $select.html(options);
            },
            error: function(xhr) {
                let errorMsg = xhr.status === 401 ? 'Unauthenticated (401)' : `Error ${xhr.status}`;
                $select.html(`<option value="">⚠️ Gagal memuat (${errorMsg})</option>`);
            }
        });
    }
    
    // ... (Fungsi loadKelasByJurusan tetap sama) ...
    function loadKelasByJurusan(jurusanKode) {
        const kelasSelect = $('#kelas_id'); 
        kelasSelect.html('<option value="">Memuat Kelas...</option>');

        if (!jurusanKode) {
             kelasSelect.html('<option value="">Pilih Jurusan terlebih dahulu</option>');
             return;
        }

        const authToken = getAuthToken();
        const endpoint = `/kelas?kode_jurusan=${jurusanKode}`; 
        
        $.ajax({
            url: apiMasterDataPrefix + endpoint, 
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + authToken, 'Accept': 'application/json' },
            success: function(response) {
                let options = '<option value="">Pilih Kelas</option>';
                const dataArray = Array.isArray(response) ? response : response.data || [];

                if (dataArray.length > 0) {
                    $.each(dataArray, function(index, item) {
                        options += `<option value="${item.kode_kelas}">${item.nama_kelas}</option>`; 
                    });
                } else {
                    options += '<option value="">Tidak ada Kelas untuk Jurusan ini</option>';
                }
                kelasSelect.html(options);
            },
            error: function(xhr) {
                kelasSelect.html('<option value="">⚠️ Gagal memuat data kelas</option>');
            }
        });
    }

    // Event Listener untuk Jurusan Form Tambah Nasabah
    $('#jurusan_id').on('change', function() {
        const selectedJurusanKode = $(this).val(); 
        loadKelasByJurusan(selectedJurusanKode);
    });
    
    // ... (Fungsi toggleSiswaFields dan loadNasabahData tetap sama) ...
    const toggleSiswaFields = () => {
        const jenisRekening = $('#jenis_rekening').val();
        const siswaFields = $('#siswa-specific-fields'); 
        
        if (jenisRekening === 'siswa') {
            siswaFields.show();
        } else {
            siswaFields.hide();
            $('#jurusan_id').val('');
            $('#kelas_id').html('<option value="">Pilih Kelas</option>');
        }
    };
    
    $('#jenis_rekening').on('change', toggleSiswaFields);
    
    // ... (Logika loadNasabahData tetap sama) ...
    function getCurrentFilters() {
        return {
            kelas_id: $('#kelasFilter').val(), 
            jurusan_id: $('#jurusanFilter').val(),
            tahun_ajaran_id: $('#tahunAjaranFilter').val(),
            cari: $('#searchInput').val()
        };
    }
    
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        const filters = getCurrentFilters();
        loadNasabahData(1, filters);
    });


    function loadNasabahData(page = 1, filters = {}) {
        $('#nasabahTableBody').html('<tr><td colspan="9" class="text-center">Memuat data...</td></tr>');
        currentPage = page;

        const params = { page: page, ...filters };
        const authToken = getAuthToken();
        
        if (!authToken) {
             $('#nasabahTableBody').html('<tr><td colspan="9" class="text-center text-danger">Token otentikasi tidak ditemukan.</td></tr>');
             return;
        }

        $.ajax({
            url: apiUrlNasabah, 
            method: 'GET',
            data: params,
            headers: {
                'Authorization': 'Bearer ' + authToken,
                'Accept': 'application/json'
            },
            success: function(response) {
                let rows = '';
                
                if (response.data && response.data.length > 0) {
                    $.each(response.data, function(i, nasabah) {
                        const no = i + 1 + (response.current_page - 1) * response.per_page;
                        
                        // Cek relasi null dengan aman
                        const kelasNama = nasabah.kelas ? nasabah.kelas.nama_kelas : '-';
                        const jurusanNama = nasabah.kelas && nasabah.kelas.jurusan ? nasabah.kelas.jurusan.nama_jurusan : '-';
                        const tahunAjaranNama = '-'; // Perlu relasi Tahun Ajaran di model Kelas
                        const saldoFormatted = formatRupiah(nasabah.saldo);

                        rows += `<tr>
                            <td>${no}</td>
                            <td>${nasabah.no_rekening}</td>
                            <td>${nasabah.nama}<br><small class="text-muted">Saldo: ${saldoFormatted}</small></td>
                            <td>${nasabah.jenis_rekening}</td>
                            <td>${kelasNama}</td>
                            <td>${jurusanNama}</td>
                            <td>${tahunAjaranNama}</td>
                            <td><span class="badge badge-success">${nasabah.status}</span></td>
                            <td>
                                <button class="btn btn-sm btn-info edit-btn" data-rekening="${nasabah.no_rekening}">Edit</button>
                                <button class="btn btn-sm btn-danger delete-btn" data-rekening="${nasabah.no_rekening}">Hapus</button>
                            </td>
                        </tr>`;
                    });
                } else {
                    rows = '<tr><td colspan="9" class="text-center">Tidak ada data nasabah.</td></tr>';
                }
                $('#nasabahTableBody').html(rows);
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : (xhr.status === 401 ? 'Unauthenticated' : 'Terjadi kesalahan saat memuat data.');
                $('#nasabahTableBody').html(`<tr><td colspan="9" class="text-center text-danger">Gagal memuat data: ${errorMsg}.</td></tr>`);
            }
        });
    }

    // ... (Logika Form Submission tetap sama) ...
    $('#tambahNasabahForm').on('submit', function(e) {
        e.preventDefault(); 
        
        const $form = $(this);
        const $btnSimpan = $form.find('#btnSimpan');
        $btnSimpan.prop('disabled', true).text('Menyimpan...');

        // Clear dan hide pesan error
        $('#errorMessages').empty().hide(); 

        const formData = $form.serializeArray();
        const requestData = {};
        
        $.each(formData, function(i, field) {
            requestData[field.name] = field.value;
        });

        // Pastikan saldo_awal adalah integer, bukan string
        requestData.saldo_awal = parseInt(requestData.saldo_awal); 

        const authToken = getAuthToken();

        $.ajax({
            url: apiStoreNasabah, 
            method: 'POST',
            data: requestData,
            headers: {
                'Authorization': 'Bearer ' + authToken,
                'Accept': 'application/json'
            },
            success: function(response) {
                alert('Sukses: ' + response.message); 
                $('#modalTambahNasabah').modal('hide'); 
                $form[0].reset(); 
                loadNasabahData(1); 
                // Reset perhitungan di form setelah sukses
                $('#potongan_admin').val(formatRupiah(0));
                $('#hasil_bersih').val(formatRupiah(0));
            },
            error: function(xhr) {
                let errorMsg = 'Terjadi kesalahan saat menyimpan data.';
                const errorDiv = $('#errorMessages');
                errorDiv.show(); 

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors || xhr.responseJSON;
                    let errorHtml = '<ul>';
                    $.each(errors, function(key, value) {
                        errorHtml += `<li><strong>${key.toUpperCase().replace('_', ' ')}:</strong> ${value.join('<br>')}</li>`;
                    });
                    errorHtml += '</ul>';
                    errorDiv.html(errorHtml);

                } else if (xhr.status === 500 || xhr.status === 401) {
                     errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : `Error Server (${xhr.status}).`;
                     errorDiv.html(`<p class="text-danger">⚠️ ${errorMsg}</p>`);
                } else {
                     errorDiv.html(`<p class="text-danger">Terjadi kesalahan tak terduga (${xhr.status}).</p>`);
                }
                console.error("Error Store Nasabah:", xhr.responseText);
            },
            complete: function() {
                $btnSimpan.prop('disabled', false).text('Simpan');
            }
        });
    });


    // =alahm ==============================================================
    // --- INISIALISASI APLIKASI ---
    // ====================================================================
    
    // 🛑 Tambahkan event listener untuk perhitungan saldo real-time
    $('#saldo_awal').on('input', calculateSaldo);

    // 1. Load Data Master
    loadMasterData('/kelas', 'kelasFilter', 'kode_kelas', 'nama_kelas'); 
    loadMasterData('/jurusan', 'jurusanFilter', 'kode_jurusan', 'nama_jurusan');
    loadMasterData('/tahun-ajaran', 'tahunAjaranFilter', 'id', 'nama_tahun_ajaran'); 
    loadMasterData('/jurusan', 'jurusan_id', 'kode_jurusan', 'nama_jurusan'); 

    // 2. Load Biaya Admin dan hitung saldo awal
    getBiayaAdmin();

    // 3. Load Data Nasabah awal
    loadNasabahData();
    
    // 4. Inisialisasi tampilan awal form
    toggleSiswaFields();
});