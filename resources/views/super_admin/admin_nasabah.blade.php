<!DOCTYPE html>
<html lang="en">

@include('layouts.partials.head')

@section('title', 'nasabah')

<style>
  .dropdown-toggle::after {
    display: none !important;
  }
</style>

<body class="g-sidenav-show   bg-gray-100">
  @include('layouts.partials.sidebar')

  <main class="main-content position-relative border-radius-lg">
    <!-- Navbar -->
    @include('layouts.partials.navbar')
    <!-- End Navbar -->
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
              <h6 class="mb-0">Data Nasabah</h6>
              <div class="d-flex align-items-center gap-3">
                <div class="input-group" style="width: 240px;">
                  <input type="text" 
                         class="form-control py-2" 
                         placeholder="Cari nasabah..." 
                         style="height: 45px; line-height: 1.2;">
                  <button class="btn bg-gradient-primary text-white d-flex align-items-center justify-content-center " data-bs-toggle="modal" data-bs-target="#tambahNasabahModal" 
                          type="button" 
                          style="height: 45px; padding-top: 0; padding-bottom: 0;">
                    <i class="bi bi-search fs-5"></i>
                  </button>
                </div>
                  <a href="#" class="btn btn-sm bg-gradient-success text-white" data-bs-toggle="modal" data-bs-target="#tambahNasabahModal">
                    <i class="bi bi-person-fill-add fs-5 me-1"></i> Tambah Nasabah
                  </a>

                  <div class="modal fade" id="tambahNasabahModal" tabindex="-1" aria-labelledby="tambahNasabahLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered"> <!-- ubah ke modal-xl agar lebih lebar -->
                      <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-gradient-primary text-white">
                          <h5 class="modal-title" id="tambahNasabahLabel">
                            <i class="bi bi-person-plus-fill me-2"></i>Tambah Nasabah
                          </h5>
                          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                  
                        <div class="modal-body">
                          <form>
                            <div class="row g-3">
                              <!-- Kolom Kiri -->
                              <div class="col-md-6">
                                <div class="mb-3">
                                  <label class="form-label fw-bold">NIS</label>
                                  <input id="nis" name="nis" type="text" class="form-control" placeholder="Masukkan NIS siswa">
                                </div>
                  
                                <div class="mb-3">
                                  <label class="form-label fw-bold">Nomor Rekening</label>
                                  <div class="input-group">
                                    <input id="rekening" name="rekening" type="text" class="form-control" style="height: 42px;" placeholder="Nomor rekening otomatis" readonly>
                                    <button id="regenRek" class="btn btn-outline-secondary" type="button" title="Regenerate">⟳</button>
                                  </div>
                                </div>
                  
                                <div class="mb-3">
                                  <label class="form-label fw-bold">Nama Nasabah</label>
                                  <input type="text" class="form-control" placeholder="Masukkan nama nasabah">
                                </div>

                                <div class="mb-3">
                                  <label class="form-label fw-bold">Nomor Telephone</label>
                                  <input type="text" class="form-control" placeholder="Masukkan nama nasabah">
                                </div>

                                <div class="mb-3">
                                  <label class="form-label fw-bold">Tempat / Tanggal Lahir</label>
                                  <div class="row g-2">
                                    <div class="col-md-6">
                                      <input type="text" class="form-control" placeholder="Tempat lahir">
                                    </div>
                                    <div class="col-md-6">
                                      <input type="date" class="form-control">
                                    </div>
                                  </div>
                                </div>
                                <div class="mb-3">
                                  <label class="form-label fw-bold">Alamat</label>
                                  <textarea class="form-control" rows="3" placeholder="Masukkan alamat"></textarea>
                                </div>
                              </div>                  
                              <!-- Kolom Kanan -->
                              <div class="col-md-6">
                                <div class="mb-3">
                                  <label class="form-label fw-bold">Jenis Kelamin</label>
                                  <select class="form-select">
                                    <option value="">-- Pilih Jenis Kelamin --</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                  </select>
                                </div>
                  
                                <div class="mb-3">
                                  <label class="form-label fw-bold">Jenis Rekening</label>
                                  <select class="form-select">
                                    <option value="">-- Pilih Jenis Rekening --</option>
                                    <option value="Guru">Guru</option>
                                    <option value="Siswa">Siswa</option>
                                  </select>
                                </div>
                  
                                <div class="mb-3">
                                  <label class="form-label fw-bold">Kelas</label>
                                  <select class="form-select">
                                    <option value="">-- Pilih Kelas --</option>
                                    <option>AKL1</option>
                                    <option>AKL2</option>
                                    <option>AKL3</option>
                                  </select>
                                </div>
                  
                                <div class="mb-3">
                                  <label class="form-label fw-bold">Jurusan</label>
                                  <select class="form-select">
                                    <option value="">-- Pilih Jurusan --</option>
                                    <option>Akuntansi</option>
                                    <option>Teknik Komputer & Jaringan</option>
                                    <option>Manajemen Perkantoran</option>
                                  </select>
                                </div>
                  
                                <div class="mb-3">
                                  <label class="form-label fw-bold">Username</label>
                                  <input type="text" class="form-control" placeholder="Masukkan username">
                                </div>
                  
                                <div class="mb-3">
                                  <label class="form-label fw-bold">Password</label>
                                  <input type="password" class="form-control" placeholder="Masukkan password">
                                </div>
                              </div>
                            </div>
                  
                            <div class="d-flex justify-content-end mt-3">
                              <button type="button" class="btn bg-gradient-secondary me-2" data-bs-dismiss="modal">Batal</button>
                              <button type="button" id="btnSimpan" class="btn bg-gradient-primary text-white">Simpan</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                  
              </div>
            </div>
            
            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                        Rekening
                        <i class="bi bi-filter ms-1 text-primary" style="cursor: pointer;"></i>
                      </th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                        Nama
                        <i class="bi bi-filter ms-1 text-primary" style="cursor: pointer;"></i>
                      </th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 ps-2">
                        Jenis Rekening
                        <i class="bi bi-filter ms-1 text-primary" style="cursor: pointer;"></i>
                      </th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 ps-2">
                        Jurusan
                        <i class="bi bi-filter ms-1 text-primary" style="cursor: pointer;"></i>
                      </th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 ps-2">
                        Kelas
                        <i class="bi bi-filter ms-1 text-primary" style="cursor: pointer;"></i>
                      </th>
                      <!-- Kolom Header -->
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 position-relative">
                        Status Nasabah
                        <!-- 🔽 HANYA INI ICONNYA -->
                        <a href="#" class="text-primary dropdown-toggle no-caret" id="filterStatusDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none;">
                          <i class="bi bi-filter ms-1"></i>
                        </a>
                        <!-- Dropdown Filter -->
                        <ul class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="filterStatusDropdown" style="min-width: 130px;">
                          <li>
                            <button class="dropdown-item d-flex align-items-center gap-2" type="button">
                              <i class="bi bi-check-circle-fill text-success"></i> Aktif
                            </button>
                          </li>
                          <li>
                            <button class="dropdown-item d-flex align-items-center gap-2" type="button">
                              <i class="bi bi-x-circle-fill text-danger"></i> Pasif
                            </button>
                          </li>
                          <li><hr class="dropdown-divider"></li>
                          <li>
                            <button class="dropdown-item text-secondary" type="button">
                              <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                            </button>
                          </li>
                        </ul>
                      </th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                        Tanggal Pembukaan
                        <i class="bi bi-filter ms-1 text-primary" style="cursor: pointer;"></i>
                      </th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 ps-2 text-center">
                        Aksi
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">1234567890</span>
                      </td>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Rayvano Putra Pratama</h6>
                            <p class="text-xs text-secondary mb-0">RayvanoIclik@gmail.com</p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">Siswa</p>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">Akuntansi 1</p>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">XII AKL 1</p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="badge badge-sm bg-gradient-success">Aktif</span>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">23/04/18</span>
                      </td>
                      <td class="align-middle text-center">
                        <a href="javascript:;" class="btn btn-sm btn-primary me-1" data-bs-toggle="tooltip" title="Edit user">
                          <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="{{ route('super_admin.detil_nasabah') }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Lihat detail">
                          <i class="fas fa-eye me-1"></i> Lihat
                        </a>
                      </td>                      
                    </tr>
                    
                    <tr>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">1234567890</span>
                      </td>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Ahmad Taufan Hidayat</h6>
                            <p class="text-xs text-secondary mb-0">Kumis_manis@gmail.com</p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">Siswa</p>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">Teknik Komputer Jaringan 2</p>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">XII TKJ 2</p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="badge badge-sm bg-gradient-success">Aktif</span>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">23/04/18</span>
                      </td>
                      <td class="align-middle text-center">
                        <a href="javascript:;" class="btn btn-sm btn-primary me-1" data-bs-toggle="tooltip" title="Edit user">
                          <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="javascript:;" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Lihat detail">
                          <i class="fas fa-eye me-1"></i> Lihat
                        </a>
                      </td>                      
                    </tr>

                    <tr>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">1234567890</span>
                      </td>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Adrian Dzariat</h6>
                            <p class="text-xs text-secondary mb-0">Bule_Cikarang@gmail.com</p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">Siswa</p>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">Manajemen Perkantoran</p>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">XII AKL 1</p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="badge badge-sm bg-gradient-success">Aktif</span>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">23/04/18</span>
                      </td>
                      <td class="align-middle text-center">
                        <a href="javascript:;" class="btn btn-sm btn-primary me-1" data-bs-toggle="tooltip" title="Edit user">
                          <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="javascript:;" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Lihat detail">
                          <i class="fas fa-eye me-1"></i> Lihat
                        </a>
                      </td>                      
                    </tr>

                    <tr>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">1234567890</span>
                      </td>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Rayvano Putra Pratama</h6>
                            <p class="text-xs text-secondary mb-0">RayvanoIclik@gmail.com</p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">Siswa</p>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">Akuntansi 1</p>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">XII AKL 1</p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="badge badge-sm bg-gradient-success">Aktif</span>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">23/04/18</span>
                      </td>
                      <td class="align-middle text-center">
                        <a href="javascript:;" class="btn btn-sm btn-primary me-1" data-bs-toggle="tooltip" title="Edit user">
                          <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="javascript:;" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Lihat detail">
                          <i class="fas fa-eye me-1"></i> Lihat
                        </a>
                      </td>                      
                    </tr>
                    
                    <tr>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">1234567890</span>
                      </td>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Ahmad Taufan Hidayat</h6>
                            <p class="text-xs text-secondary mb-0">Kumis_manis@gmail.com</p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">Siswa</p>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">Teknik Komputer Jaringan 2</p>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">XII TKJ 2</p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="badge badge-sm bg-gradient-success">Aktif</span>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">23/04/18</span>
                      </td>
                      <td class="align-middle text-center">
                        <a href="javascript:;" class="btn btn-sm btn-primary me-1" data-bs-toggle="tooltip" title="Edit user">
                          <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="javascript:;" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Lihat detail">
                          <i class="fas fa-eye me-1"></i> Lihat
                        </a>
                      </td>                      
                    </tr>

                    <tr>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">1234567890</span>
                      </td>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Adrian Dzariat</h6>
                            <p class="text-xs text-secondary mb-0">Bule_Cikarang@gmail.com</p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">Siswa</p>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">Manajemen Perkantoran</p>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0">XII AKL 1</p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="badge badge-sm bg-gradient-success">Aktif</span>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">23/04/18</span>
                      </td>
                      <td class="align-middle text-center">
                        <a href="javascript:;" class="btn btn-sm btn-primary me-1" data-bs-toggle="tooltip" title="Edit user">
                          <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="javascript:;" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Lihat detail">
                          <i class="fas fa-eye me-1"></i> Lihat
                        </a>
                      </td>                      
                    </tr>
                  </tbody>
                  
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      {{-- footer --}}
    </div>
  </main>
  <!--   Core JS Files   -->
  @include('layouts.partials.script')
  {{-- logout --}}
  <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
    @csrf
    </form>
    <div class="collapse mt-3" id="formTambah">
      <div class="card card-body">
        <form>
          <div class="mb-3">
            <label class="form-label">Nama</label>
            <input type="text" class="form-control">
          </div>
          <button type="submit" class="btn bg-gradient-primary text-white">Simpan</button>
        </form>
      </div>
    </div>
    

</body>
@include('layouts.partials.script')
</html>