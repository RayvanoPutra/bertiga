<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Nasabah</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    </head>
<body>

<div class="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <form id="filterForm" class="form-inline">
                                    <div class="form-group">
                                        <select class="form-control form-control-sm" id="kelasFilter" name="kelas_id">
                                            <option value="">Kelas</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-info btn-sm mr-2" id="btnCari">Cari</button>
                                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalTambahNasabah">
                                        Tambah Nasabah
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered">
                                <thead class="thead-dark">
                                    </thead>
                                <tbody id="nasabahTableBody">
                                    <tr><td colspan="9" class="text-center">Memuat data...</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-3">
                            <nav id="paginationLinks">
                                {{-- Link pagination akan diisi oleh JavaScript --}}
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL FORM TAMBAH NASABAH --}}
<div class="modal fade" id="modalTambahNasabah" tabindex="-1" role="dialog" aria-labelledby="modalTambahNasabahLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTambahNasabahLabel">Form Tambah Nasabah</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- 🛑 PERBAIKAN: Ganti ID ke 'tambahNasabahForm' 🛑 --}}
                <form id="tambahNasabahForm"> 
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="no_induk">Nomor Induk <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="no_induk" name="no_induk" required>
                            </div>
                            
                            {{-- 🛑 PERBAIKAN: Hapus Input No. Rekening yang digenerate oleh Controller 🛑 --}}
            
                            <div class="form-group">
                                <label for="nama">Nama <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama" name="nama" required>
                            </div>
                            <div class="form-group">
                                <label for="username">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            <div class="form-group">
                                <label for="no_telp">Nomor Telepon</label>
                                <input type="text" class="form-control" id="no_telp" name="no_telp">
                            </div>
                            <div class="form-group">
                                <label for="alamat">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="2"></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="jenis_rekening">Jenis Rekening <span class="text-danger">*</span></label>
                                <select class="form-control" id="jenis_rekening" name="jenis_rekening" required>
                                    <option value="">Pilih Jenis Rekening</option>
                                    <option value="siswa">Siswa</option>
                                    <option value="guru">Guru/Staff</option>
                                </select>
                            </div>
                            
                            {{-- 🛑 PERBAIKAN: Ganti ID ke 'siswa-specific-fields' 🛑 --}}
                            <div id="siswa-specific-fields"> 
                                <div class="form-group">
                                    <label for="jurusan_id">Jurusan</label>
                                    <select class="form-control" id="jurusan_id" name="jurusan_id">
                                        <option value="">Pilih Jurusan</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="kelas_id">Kelas <span class="text-danger" id="kelasRequired" style="display:none;">*</span></label>
                                    {{-- 🛑 PERBAIKAN: Ganti name="kelas_id" menjadi name="kode_kelas" 🛑 --}}
                                    <select class="form-control" id="kelas_id" name="kode_kelas">
                                        <option value="">Pilih Jurusan terlebih dahulu</option>
                                    </select>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="form-group">
                                <label for="saldo_awal">Setoran Awal (Min. Rp <span id="minSaldo">0</span>) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="saldo_awal" name="saldo_awal" required min="0">
                            </div>
                            
                            <div class="form-group">
                                <label>Potongan Admin Pendaftaran</label>
                                <input type="text" class="form-control bg-light" id="potongan_admin" readonly value="Rp. 0">
                            </div>
                            
                            <div class="form-group">
                                <label>Hasil Bersih (Saldo Akhir)</label>
                                <input type="text" class="form-control bg-info text-white font-weight-bold" id="hasil_bersih" readonly value="Rp. 0">
                            </div>
                        </div>
                    </div>
                    
                    <div id="errorMessages" class="mt-3 alert alert-danger" style="display:none;"></div>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSimpan">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script src="{{ asset('js/nasabah.js') }}"></script> 

</body>
</html>