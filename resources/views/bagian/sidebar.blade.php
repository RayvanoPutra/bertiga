<aside id="sidebar">
    <button id="sidebar-toggler" title="Toggle Sidebar">
        <i class="bi bi-arrow-left-square-fill"></i>
    </button>

    <div class="logo">BANK MINI</div>
    
    <nav>
        <a href="{{ route('superadmin.dashboard') }}" class="active" title="Dashboard">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('superadmin.nasabah') }}" title="Nasabah">
            <i class="bi bi-person-badge"></i>
            <span>Nasabah</span>
        </a>
        <a href="#" title="Transaksi">
            <i class="bi bi-arrow-left-right"></i>
            <span>Transaksi</span>
        </a>
        
        <div class="menu-section">MASTER</div>
        <a href="{{ route('superadmin.kelas') }}" title="Katalog">
            <i class="bi bi-journals"></i>
            <span>Kelas</span>
        </a>
        <a href="{{ route('superadmin.jurusan') }}" title="Katalog">
            <i class="bi bi-journals"></i>
            <span>Jurusan</span>
        </a>
        <a href="#" title="Akun">
            <i class="bi bi-person-circle"></i>
            <span>Akun</span>
        </a>
        {{-- <a href="{{ route('superadmin.tahun-ajaran') }}" title="Tahun Ajaran">
            <i class="bi bi-calendar-check"></i>
            <span>Tahun Ajaran</span>
        </a> --}}

        <a href="{{ route('superadmin.tahun_ajaran') }}" title="Katalog">
            <i class="bi bi-calender-check"></i>
            <span>Tahun Ajaran</span>
        </a>

        <a href="#" title="Pengaturan">
            <i class="bi bi-gear"></i>
            <span>Pengaturan</span>
        </a>
    </nav>
    
    <a href="#" id="logoutButton" class="logout-btn" title="Keluar">
        <i class="bi bi-box-arrow-right"></i>
        <span>Keluar</span>
    </a>
</aside>