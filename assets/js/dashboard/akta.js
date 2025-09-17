// assets/js/dashboard/akta.js
// Akta page specific logic - VERSI FINAL DENGAN PERBAIKAN WARNA

class AktaDashboard {
  constructor() {
    // Definisi warna untuk chart
    this.chartColors = {
      memiliki: window.DashboardCommon.chartColors.success,
      belumMemiliki: window.DashboardCommon.chartColors.danger,
      lakiLaki: window.DashboardCommon.chartColors.primary,
      perempuan: window.DashboardCommon.chartColors.pink,
    };

    // State utama dashboard
    this.currentData = {};
    this.currentStats = {};
    this.activeTab = "akta_cerai";
    this.charts = {};
    
    // State untuk pagination tabel
    this.pagination = {
      currentPage: 1,
      itemsPerPage: 25,
      totalItems: 0,
    };

    // State untuk semua filter
    this.filters = {
      search: "",
      sort: "wilayah_asc",
      regionType: "all",
    };

    this.init();
  }

  init() {
    this.setupTabEventListeners();
    this.setupFilterAndControlListeners();
    this.loadData();
    window.addEventListener("dataRefresh", () => this.loadData());
  }

  setupTabEventListeners() {
    document.querySelectorAll(".tab-button").forEach((button) => {
      button.addEventListener("click", (e) => this.switchTab(e.currentTarget.dataset.tab));
    });
  }

  setupFilterAndControlListeners() {
    const tabs = ["akta_cerai", "akta_lahir", "akta_mati"];
    tabs.forEach((tab) => {
      const updateAndRefresh = (filterType, value) => {
        this.filters[filterType] = value;
        this.pagination.currentPage = 1;
        this.updateTabContent();
      };

      document.getElementById(`${tab}-search`)?.addEventListener("input", (e) => updateAndRefresh('search', e.target.value.toLowerCase()));
      document.getElementById(`${tab}-region-type`)?.addEventListener("change", (e) => updateAndRefresh('regionType', e.target.value));
      document.getElementById(`${tab}-sort`)?.addEventListener("change", (e) => updateAndRefresh('sort', e.target.value));
      document.getElementById(`${tab}-refresh`)?.addEventListener("click", (e) => {
          e.preventDefault();
          this.loadData();
      });
    });
  }

  switchTab(tabType) {
    this.activeTab = tabType;

    document.querySelectorAll(".tab-button").forEach((btn) => btn.classList.toggle("active", btn.dataset.tab === tabType));
    document.querySelectorAll(".tab-content").forEach((content) => {
        content.style.display = content.id === `${tabType}-content` ? "block" : "none";
        content.classList.toggle("active", content.id === `${tabType}-content`);
    });

    this.filters = { search: "", sort: "wilayah_asc", regionType: "all" };
    this.pagination.currentPage = 1;
    
    document.getElementById(`${tabType}-search`).value = "";
    document.getElementById(`${tabType}-region-type`).value = "all";
    document.getElementById(`${tabType}-sort`).value = "wilayah_asc";

    this.updateTabContent();
  }

  async loadData() {
    window.DashboardCommon.showLoading(["statCerai", "statLahir", "statMati", "statTotal"]);
    try {
      const result = await window.API.getAktaKelahiranData(window.DashboardCommon.getCurrentFilters());

      if (result.success) {
        this.currentData = result.data || {};
        this.currentStats = result.stats || {};
        this.updateOverallStats();
        this.updateTabContent();
      } else {
        throw new Error(result.error?.message || "Gagal memuat data dari API.");
      }
    } catch (error) {
      console.error("Error saat memuat data Akta:", error);
      window.mainApp?.showNotification(error.message, "error");
      window.DashboardCommon.showError(["statCerai", "statLahir", "statMati", "statTotal"], "Error");
    }
  }
  
  updateOverallStats() {
    const stats = this.currentStats;
    if (!stats) return;

    const totalCerai = Number(stats.akta_cerai?.total_memiliki) || 0;
    const totalLahir = Number(stats.akta_lahir?.total_memiliki) || 0;
    const totalMati = Number(stats.akta_mati?.grand_total) || 0;
    const grandTotal = totalCerai + totalLahir + totalMati;

    document.getElementById('statCerai').textContent = totalCerai.toLocaleString('id-ID');
    document.getElementById('statLahir').textContent = totalLahir.toLocaleString('id-ID');
    document.getElementById('statMati').textContent = totalMati.toLocaleString('id-ID');
    document.getElementById('statTotal').textContent = grandTotal.toLocaleString('id-ID');
  }

  updateTabContent() {
    this.updateTabStats();
    this.createRealCharts();
    this.updateDataTable();
  }

  /**
   * PERBAIKAN: Mengembalikan kelas CSS spesifik untuk warna stat card.
   * Kelas seperti 'wajib', 'memiliki', 'lakiLaki' akan membuat CSS dapat mewarnai kartu.
   */
  updateTabStats() {
    const tabStats = this.currentStats[this.activeTab];
    const container = document.getElementById(`${this.activeTab}-stats`);
    if (!container || !tabStats) return;

    let html = '';
    if (this.activeTab === 'akta_mati') {
        html = `<div class="stat-card lakiLaki"><h3>Laki-laki</h3><div class="value">${(Number(tabStats.total_laki_laki) || 0).toLocaleString("id-ID")}</div></div>
                <div class="stat-card perempuan"><h3>Perempuan</h3><div class="value">${(Number(tabStats.total_perempuan) || 0).toLocaleString("id-ID")}</div></div>
                <div class="stat-card total"><h3>Total</h3><div class="value">${(Number(tabStats.grand_total) || 0).toLocaleString("id-ID")}</div></div>`;
    } else {
        const wajib = Number(tabStats.total_wajib || 0);
        const memiliki = Number(tabStats.total_memiliki || 0);
        const persentase = wajib > 0 ? ((memiliki / wajib) * 100).toFixed(1) : 0;
        html = `<div class="stat-card wajib"><h3>Wajib Akta</h3><div class="value">${wajib.toLocaleString("id-ID")}</div></div>
                <div class="stat-card memiliki"><h3>Memiliki</h3><div class="value">${memiliki.toLocaleString("id-ID")}</div></div>
                <div class="stat-card belum-memiliki"><h3>Belum Memiliki</h3><div class="value">${(Number(tabStats.total_belum_memiliki) || 0).toLocaleString("id-ID")}</div></div>
                <div class="stat-card total"><h3>Persentase</h3><div class="value">${persentase}%</div></div>`;
    }
    container.innerHTML = html;
  }

  createRealCharts() {
    Object.values(this.charts).forEach(chart => chart?.destroy());

    const tabData = this.currentData[this.activeTab];
    if (!Array.isArray(tabData) || tabData.length === 0) {
      this.showNoDataMessage();
      return;
    }
    
    const processedData = this.getFilteredAndSortedData(tabData);

    if (processedData.length === 0) {
      this.showNoDataMessage();
      return;
    }
    
    this.createWilayahDistributionChart(processedData);
    this.createKepemilikanOverviewChart(processedData);
  }

  createWilayahDistributionChart(data) {
    const canvas = document.getElementById(`${this.activeTab}-bar-chart`);
    if (!canvas) return;
    const chartData = data.slice(0, 10);

    let datasets;
    if (this.activeTab === "akta_mati") {
      datasets = [
        { label: "Laki-laki", data: chartData.map(d => d.laki_laki), backgroundColor: this.chartColors.lakiLaki },
        { label: "Perempuan", data: chartData.map(d => d.perempuan), backgroundColor: this.chartColors.perempuan },
      ];
    } else {
      datasets = [
        { label: "Memiliki", data: chartData.map(d => d.memiliki), backgroundColor: this.chartColors.memiliki },
        { label: "Belum Memiliki", data: chartData.map(d => d.belum_memiliki), backgroundColor: this.chartColors.belumMemiliki },
      ];
    }
    
    this.charts.bar = new Chart(canvas.getContext("2d"), {
      type: 'bar',
      data: {
        labels: chartData.map(d => d.wilayah.replace(/^(KAB.|KOTA|PROVINSI)\s*/i, "")),
        datasets: datasets
      },
      options: { ...window.DashboardCommon.chartDefaults, scales: { x: { stacked: true }, y: { stacked: true } } }
    });
  }

  createKepemilikanOverviewChart(data) {
      const canvas = document.getElementById(`${this.activeTab}-pie-chart`);
      if (!canvas) return;
      
      let pieData, pieLabels, pieColors;
      if (this.activeTab === "akta_mati") {
          const totals = data.reduce((acc, item) => ({
              lakiLaki: acc.lakiLaki + item.laki_laki,
              perempuan: acc.perempuan + item.perempuan
          }), { lakiLaki: 0, perempuan: 0 });
          pieData = [totals.lakiLaki, totals.perempuan];
          pieLabels = ["Laki-laki", "Perempuan"];
          pieColors = [this.chartColors.lakiLaki, this.chartColors.perempuan];
      } else {
          const totals = data.reduce((acc, item) => ({
              memiliki: acc.memiliki + item.memiliki,
              belum: acc.belum + item.belum_memiliki
          }), { memiliki: 0, belum: 0 });
          pieData = [totals.memiliki, totals.belum];
          pieLabels = ["Memiliki Akta", "Belum Memiliki"];
          pieColors = [this.chartColors.memiliki, this.chartColors.belumMemiliki];
      }
      
      this.charts.pie = new Chart(canvas.getContext("2d"), {
          type: 'doughnut',
          data: { labels: pieLabels, datasets: [{ data: pieData, backgroundColor: pieColors }] },
          options: { ...window.DashboardCommon.chartDefaults, cutout: '60%' },
      });
  }
  
  showNoDataMessage() {
    ['bar-chart', 'pie-chart'].forEach(type => {
        const canvas = document.getElementById(`${this.activeTab}-${type}`);
        if(canvas) window.DashboardCommon.showNoDataChart(canvas);
    });
  }

  updateDataTable() {
    const tableContainer = document.getElementById(`${this.activeTab}-table`);
    if (!tableContainer) return;

    const filteredData = this.getFilteredAndSortedData(this.currentData[this.activeTab] || []);

    if (filteredData.length === 0) {
      tableContainer.innerHTML = '<p class="text-center">Tidak ada data yang cocok dengan filter.</p>';
      return;
    }


    this.pagination.totalItems = filteredData.length;
    const { currentPage, itemsPerPage, totalItems } = this.pagination;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
    const paginatedData = filteredData.slice(startIndex, endIndex);

    let headerHTML = '';
    if (this.activeTab === 'akta_mati') {
      headerHTML = '<th>Kode</th><th>Wilayah</th><th class="number">Laki-laki</th><th class="number">Perempuan</th><th class="number">Total</th>';
    } else {
      headerHTML = '<th>Kode</th><th>Wilayah</th><th class="number">Wajib</th><th class="number">Memiliki</th><th class="number">Belum</th><th class="percentage">Persentase</th>';
    }

    let bodyHTML = paginatedData.map(item => {
      if (this.activeTab === 'akta_mati') {
        return `<tr>
                  <td>${item.kode}</td>
                  <td>${item.wilayah}</td>
                  <td class="number">${(item.laki_laki || 0).toLocaleString('id-ID')}</td>
                  <td class="number">${(item.perempuan || 0).toLocaleString('id-ID')}</td>
                  <td class="number">${(item.total || 0).toLocaleString('id-ID')}</td>
                </tr>`;
      }
      // DIPERTAHANKAN: Menampilkan persentase dengan 2 desimal dari data asli
      const persentase = String(item.persentase || '0.00').replace('.', ',');
      const numPersentase = parseFloat(persentase.replace(',', '.'));
      const pClass = numPersentase >= 80 ? 'high' : numPersentase >= 60 ? 'medium' : 'low';
      return `<tr>
                <td>${item.kode}</td>
                <td>${item.wilayah}</td>
                <td class="number">${(item.wajib || 0).toLocaleString('id-ID')}</td>
                <td class="number">${(item.memiliki || 0).toLocaleString('id-ID')}</td>
                <td class="number">${(item.belum_memiliki || 0).toLocaleString('id-ID')}</td>
                <td class="percentage ${pClass}">${persentase}%</td>
              </tr>`;
    }).join('');

    let paginationHTML = '';
    if (totalPages > 1) {
      paginationHTML += '<div class="pagination-controls">';
      paginationHTML += `<button class="pagination-btn" onclick="window.aktaDashboard.goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>Sebelumnya</button>`;
      // Logika untuk menampilkan nomor halaman
      let pageNumbers = [];
      if(totalPages <= 5) {
          for(let i = 1; i <= totalPages; i++) pageNumbers.push(i);
      } else {
          pageNumbers.push(1);
          if(currentPage > 3) pageNumbers.push('...');
          if(currentPage > 2) pageNumbers.push(currentPage - 1);
          if(currentPage !== 1 && currentPage !== totalPages) pageNumbers.push(currentPage);
          if(currentPage < totalPages - 1) pageNumbers.push(currentPage + 1);
          if(currentPage < totalPages - 2) pageNumbers.push('...');
          pageNumbers.push(totalPages);
      }
      // Hapus duplikat '...' jika ada
      pageNumbers = [...new Set(pageNumbers)];
      
      pageNumbers.forEach(num => {
          if(num === '...') {
              paginationHTML += `<span>...</span>`;
          } else {
              paginationHTML += `<button class="pagination-btn page-number ${num === currentPage ? 'active' : ''}" onclick="window.aktaDashboard.goToPage(${num})">${num}</button>`;
          }
      });
      paginationHTML += `<button class="pagination-btn" onclick="window.aktaDashboard.goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Selanjutnya</button>`;
      paginationHTML += '</div>';
    }

    tableContainer.innerHTML = `
      <div class="table-controls">
        <div class="per-page-selector">
          <label for="itemsPerPage">Tampilkan:</label>
          <select id="itemsPerPage" onchange="window.aktaDashboard.changeItemsPerPage(this.value)">
            <option value="10" ${itemsPerPage === 10 ? 'selected' : ''}>10</option>
            <option value="25" ${itemsPerPage === 25 ? 'selected' : ''}>25</option>
            <option value="50" ${itemsPerPage === 50 ? 'selected' : ''}>50</option>
            <option value="100" ${itemsPerPage === 100 ? 'selected' : ''}>100</option>
          </select>
        </div>
        <div class="pagination-info">
          Menampilkan ${endIndex > 0 ? startIndex + 1 : 0} - ${endIndex} dari ${totalItems} data
        </div>
      </div>
      <table class="data-table">
        <thead><tr>${headerHTML}</tr></thead>
        <tbody>${bodyHTML}</tbody>
      </table>
      ${paginationHTML}
    `;
  }

  getFilteredAndSortedData(data) {
    if (!Array.isArray(data)) return [];
    let processedData = [...data];

    if (this.filters.search) {
      processedData = processedData.filter(item => item.wilayah.toLowerCase().includes(this.filters.search));
    }
    if (this.filters.regionType === 'provinsi') {
      processedData = processedData.filter(item => String(item.kode).endsWith('.00'));
    } else if (this.filters.regionType === 'kabupaten') {
      processedData = processedData.filter(item => !String(item.kode).endsWith('.00'));
    }

    const [field, order] = this.filters.sort.split("_");
    processedData.sort((a, b) => {
      let aVal, bVal;
      switch (field) {
        case "wilayah": aVal = a.wilayah; bVal = b.wilayah; break;
        case "persentase": aVal = parseFloat(String(a.persentase).replace(',', '.')); bVal = parseFloat(String(b.persentase).replace(',', '.')); break;
        default: aVal = a[field.replace('_laki', '_laki_laki')] || 0; bVal = b[field.replace('_laki', '_laki_laki')] || 0;
      }
      if (typeof aVal === 'string') return order === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
      return order === 'asc' ? aVal - bVal : bVal - aVal;
    });

    return processedData;
  }
  
  changeItemsPerPage(value) {
    this.pagination.itemsPerPage = Number(value);
    this.pagination.currentPage = 1;
    this.updateDataTable();
  }

  goToPage(pageNumber) {
    const totalPages = Math.ceil(this.pagination.totalItems / this.pagination.itemsPerPage);
    if (pageNumber >= 1 && pageNumber <= totalPages) {
      this.pagination.currentPage = pageNumber;
      this.updateDataTable();
    }
  }
  
  exportData() {
      const dataToExport = this.getFilteredAndSortedData(this.currentData[this.activeTab] || []);
      if(dataToExport.length > 0) {
          window.mainApp?.showNotification(`Mengekspor ${dataToExport.length} baris data...`, "success");
      } else {
          window.mainApp?.showNotification("Tidak ada data untuk diekspor.", "warning");
      }
  }
}

document.addEventListener("DOMContentLoaded", () => {
  if (window.DashboardCommon && window.API) {
    window.aktaDashboard = new AktaDashboard();
  }
});