<script src="{{ asset('assets/js/argon-dashboard.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/chartjs.min.js') }}"></script>
<script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>

    var ctx1 = document.getElementById("chart-line").getContext("2d");

    var gradientStroke1 = ctx1.createLinearGradient(0, 230, 0, 50);

    gradientStroke1.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
    gradientStroke1.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
    gradientStroke1.addColorStop(0, 'rgba(94, 114, 228, 0)');
    new Chart(ctx1, {
      type: "bar",
      data: {
        labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
          label: "Mobile apps",
          tension: 0.4,
          borderWidth: 0,
          pointRadius: 0,
          borderColor: "#5e72e4",
          backgroundColor: gradientStroke1,
          borderWidth: 3,
          fill: true,
          data: [50, 40, 300, 220, 500, 250, 400, 230, 500],
          maxBarThickness: 6

        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              padding: 10,
              color: '#fbfbfb',
              font: {
                size: 11,
                family: "Open Sans",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              color: '#ccc',
              padding: 20,
              font: {
                size: 11,
                family: "Open Sans",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
        },
      },
    });
  </script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/argon-dashboard.min.js?v=2.1.0"></script>


  {{-- table nasabah_admin --}}
  <script src="{{ asset('assets/js/plugins/chartjs.min.js') }}"></script>

{{-- random norek --}}
<script>
  // generateRandomSuffix: menghasilkan n digit angka acak
  function generateRandomSuffix(digits = 4) {
    return Math.floor(Math.random() * Math.pow(10, digits)).toString().padStart(digits, '0');
  }

  // format rekening: contoh BR + tahun + - + NIS + - + 4digit
  function formatRekening(nis) {
    const year = new Date().getFullYear();
    const suffix = generateRandomSuffix(4);
    // contoh: BR2025-123456-0456
    return `BR${year}-${nis}-${suffix}`;
  }

  // bila NIS berubah, update rekening otomatis
  document.getElementById('nis').addEventListener('input', function() {
    const nis = this.value.trim();
    if (nis.length > 0) {
      document.getElementById('rekening').value = formatRekening(nis);
    } else {
      document.getElementById('rekening').value = '';
    }
  });

  // tombol regenerate
  document.getElementById('regenRek').addEventListener('click', function() {
    const nis = document.getElementById('nis').value.trim();
    if (!nis) {
      alert('Masukkan NIS terlebih dahulu.');
      return;
    }
    document.getElementById('rekening').value = formatRekening(nis);
  });

  // Optional: generate saat modal terbuka (jika form di modal)
  var tambahModal = document.getElementById('tambahNasabahModal');
  if (tambahModal) {
    tambahModal.addEventListener('show.bs.modal', function () {
      const nisField = document.getElementById('nis');
      if (nisField && nisField.value.trim() !== '') {
        document.getElementById('rekening').value = formatRekening(nisField.value.trim());
      } else {
        document.getElementById('rekening').value = '';
      }
    });
  }
</script>



  
