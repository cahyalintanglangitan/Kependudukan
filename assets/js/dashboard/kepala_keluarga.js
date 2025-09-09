// assets/js/dashboard/kepala_keluarga.js

class KepalaKeluargaDashboard {
  constructor() {
    this.data = [];
    this.filteredData = [];
    this.charts = {};

    this.initializeFilters();
    this.bindEvents();
    this.loadData();
  }

  initializeFilters() {
    this.filters = {
      regionType: document.getElementById("regionTypeFilter")?.value || "all",
      province: document.getElementById("provinceFilter")?.value || "all",
      sort: document.getElementById("sortFilter")?.value || "total_desc",
    };
  }

  bindEvents() {
    document.getElementById("regionTypeFilter")?.addEventListener("change", () => this.handleFilterChange());
    document.getElementById("provinceFilter")?.addEventListener("change", () => this.handleFilterChange());
    document.getElementById("sortFilter")?.addEventListener("change", () => this.handleFilterChange());
    document.getElementById("refreshBtn")?.addEventListener("click", () => this.loadData(true));
  }

  async loadData(forceRefresh = false) {
    try {
      this.showLoadingState();
      
      const response = await fetch(`${window.API_BASE_URL}kepala_keluarga.php`);
      if (!response.ok) throw new Error("Gagal mengambil data dari server");

      const result = await response.json();
      if (result.status === "success" && Array.isArray(result.data)) {
        this.data = result.data;
        this.populateProvinceFilter();
        this.applyFilters();
      } else {
        throw new Error(result.message || "Format data tidak sesuai");
      }
    } catch (error) {
      console.error("Error memuat data:", error);
      this.showErrorState(error.message);
    } finally {
      this.hideLoadingState();
    }
  }

  populateProvinceFilter() {
    const select = document.getElementById("provinceFilter");
    if (!select) return;
    
    select.innerHTML = '<option value="all">Semua Provinsi</option>';
    
    const provinces = this.data
      .filter(item => item.type === 'provinsi')
      .sort((a, b) => a.name.localeCompare(b.name));

    provinces.forEach(province => {
      const option = document.createElement("option");
      option.value = province.kode;
      option.textContent = province.name;
      select.appendChild(option);
    });
  }

  handleFilterChange() {
    this.filters.regionType = document.getElementById("regionTypeFilter").value;
    this.filters.province = document.getElementById("provinceFilter").value;
    this.filters.sort = document.getElementById("sortFilter").value;
    this.applyFilters();
  }

  // PERBAIKAN UTAMA ADA DI FUNGSI INI
  applyFilters() {
    let tempData = [...this.data];

    // 1. Filter berdasarkan Tipe Wilayah
    if (this.filters.regionType === 'provinsi') {
        tempData = tempData.filter(item => item.type === 'provinsi');
    } else if (this.filters.regionType === 'kabupaten') {
        tempData = tempData.filter(item => item.type === 'kabupaten');
    } else if (this.filters.regionType === 'kota') {
        tempData = tempData.filter(item => item.type === 'kota');
    }
    // Jika 'all', tidak ada filter tipe, semua akan diproses ke filter provinsi

    // 2. Filter berdasarkan Provinsi (HANYA JIKA Tipe Wilayah BUKAN 'provinsi')
    if (this.filters.regionType !== 'provinsi' && this.filters.province !== 'all') {
      const provinceCodePrefix = String(this.filters.province).split('.')[0];
      tempData = tempData.filter(item => 
        // Hanya tampilkan kabupaten/kota dari provinsi yang dipilih
        String(item.kode).startsWith(provinceCodePrefix + '.') && item.type !== 'provinsi'
      );
    } else if (this.filters.regionType === 'all' && this.filters.province === 'all') {
      // Jika semua, defaultnya tampilkan semua kabupaten dan kota
      tempData = tempData.filter(item => item.type === 'kabupaten' || item.type === 'kota');
    }

    // 3. Lakukan sorting
    this.applySorting(tempData);
    this.filteredData = tempData;

    // 4. Update semua elemen UI
    this.updateUI();
  }

  applySorting(data) {
    const sortOption = this.filters.sort;
    data.sort((a, b) => {
      switch (sortOption) {
        case 'total_desc': return b.total - a.total;
        case 'total_asc': return a.total - b.total;
        case 'name_asc': return a.name.localeCompare(b.name);
        case 'name_desc': return b.name.localeCompare(a.name);
        case 'laki_desc': return b.laki_laki - a.laki_laki;
        case 'perempuan_desc': return b.perempuan - a.perempuan;
        default: return 0;
      }
    });
  }

  updateUI() {
    this.updateStats();
    this.updateCharts();
    const dataCountEl = document.getElementById("dataCount");
    if(dataCountEl) dataCountEl.textContent = this.formatNumber(this.filteredData.length);
  }

  calculateStats() {
    // Statistik sekarang selalu menghitung dari data yang sudah terfilter
    return this.filteredData.reduce((acc, item) => {
      acc.lakiLaki += item.laki_laki || 0;
      acc.perempuan += item.perempuan || 0;
      acc.total += item.total || 0;
      return acc;
    }, { lakiLaki: 0, perempuan: 0, total: 0 });
  }

  updateStats() {
    const stats = this.calculateStats();
    document.getElementById("statLakiLaki").textContent = this.formatNumber(stats.lakiLaki);
    document.getElementById("statPerempuan").textContent = this.formatNumber(stats.perempuan);
    document.getElementById("statTotal").textContent = this.formatNumber(stats.total);
  }

  updateCharts() {
    this.updateBarChart();
    this.updatePieChart();
  }

  updateBarChart() {
    const canvas = document.getElementById("barChart");
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    if (this.charts.bar) this.charts.bar.destroy();

    const top15 = this.filteredData.slice(0, 15);
    // Membersihkan label agar lebih pendek dan rapi
    const labels = top15.map(item => this.truncateLabel(item.name.replace(/KAB. |KOTA |ADM. /g, ''), 15));
    
    this.charts.bar = new Chart(ctx, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          { label: "Laki-laki", data: top15.map(item => item.laki_laki), backgroundColor: "#2563eb" },
          { label: "Perempuan", data: top15.map(item => item.perempuan), backgroundColor: "#dc2626" },
        ],
      },
      options: this.getChartOptions(),
    });
  }

  updatePieChart() {
    const canvas = document.getElementById("pieChart");
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    if (this.charts.pie) this.charts.pie.destroy();

    const stats = this.calculateStats();

    this.charts.pie = new Chart(ctx, {
      type: "doughnut",
      data: {
        labels: ["Kepala Keluarga Laki-laki", "Kepala Keluarga Perempuan"],
        datasets: [{
          data: [stats.lakiLaki, stats.perempuan],
          backgroundColor: ["#2563eb", "#dc2626"],
          borderColor: "#ffffff",
          borderWidth: 2,
        }],
      },
      options: this.getChartOptions(true),
    });
  }

  getChartOptions(isPie = false) {
    const options = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: isPie, position: 'bottom', labels: { padding: 20, usePointStyle: true } },
        tooltip: {
          callbacks: {
            label: (context) => {
              const value = context.parsed.y || context.parsed;
              const label = context.dataset.label || context.label || '';
              if (isPie) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                if (total === 0) return `${label}: 0 (0%)`; // Menghindari pembagian dengan nol
                const percentage = ((value * 100) / total).toFixed(1);
                return `${label}: ${this.formatNumber(value)} (${percentage}%)`;
              }
              return `${label}: ${this.formatNumber(value)}`;
            },
          },
        },
      },
      scales: isPie ? {} : {
        y: { beginAtZero: true, ticks: { callback: (value) => this.formatNumber(value) } },
        x: { ticks: { maxRotation: 45, minRotation: 0 } },
      },
    };
    return options;
  }

  showLoadingState() { document.querySelectorAll(".loading-spinner").forEach(el => el.style.display = 'block'); }
  hideLoadingState() { document.querySelectorAll(".loading-spinner").forEach(el => el.style.display = 'none'); }
  showErrorState(message) {
    this.hideLoadingState();
    const errorHtml = `<span style="color: #dc2626; font-size: 14px;">Error: ${message}</span>`;
    ["statLakiLaki", "statPerempuan", "statTotal"].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.innerHTML = errorHtml;
    });
  }
  formatNumber(number) { return new Intl.NumberFormat("id-ID").format(number || 0); }
  truncateLabel(label, maxLength) { return label.length > maxLength ? label.substring(0, maxLength - 3) + "..." : label; }
}

document.addEventListener("DOMContentLoaded", () => {
  window.kepalaKeluargaDashboard = new KepalaKeluargaDashboard();
});