// assets/js/dashboard/kelompok_umur.js
// Versi Lengkap dan Sudah Diperbaiki

class KelompokUmurDashboard {
  constructor() {
    this.currentTab = "overview";
    this.charts = {};
    this.data = {};
    this.filteredData = {};
    this.colors = {
      balita: "#FF6B9D",
      anak: "#4ECDC4",
      dewasa: "#45B7D1",
      lansia: "#96CEB4",
      total: "#6C5CE7",
      laki_laki: "#36A2EB",
      perempuan: "#FF6384",
    };

    this.init();
  }

  async init() {
    this.setupEventListeners();
    await this.loadAllData();
    this.updateOverallStats();
    this.renderCurrentTab();
  }

  setupEventListeners() {
    // Tab switching
    document.querySelectorAll(".tab-button").forEach((button) => {
      button.addEventListener("click", (e) => {
        const tabId = e.target.closest(".tab-button").dataset.tab;
        this.switchTab(tabId);
      });
    });

    // Search and sort for each tab
    ["overview", "balita", "anak", "dewasa", "lansia"].forEach((tab) => {
      const searchInput = document.getElementById(`${tab}-search`);
      const sortSelect = document.getElementById(`${tab}-sort`);
      const refreshBtn = document.getElementById(`${tab}-refresh`);

      if (searchInput) {
        searchInput.addEventListener("input", () => this.handleSearch(tab));
      }

      if (sortSelect) {
        sortSelect.addEventListener("change", () => this.handleSort(tab));
      }

      if (refreshBtn) {
        refreshBtn.addEventListener("click", () => this.refreshData(tab));
      }
    });
  }

  async loadAllData() {
    try {
      // Load data for all age groups
      await Promise.all([
        this.loadOverviewData(),
        this.loadBalitaData(),
        this.loadAnakData(),
        this.loadDewasaData(),
        this.loadLansiaData(),
      ]);
    } catch (error) {
      console.error("Error loading kelompok umur data:", error);
      this.showError("Gagal memuat data kelompok umur");
    }
  }

  async loadOverviewData() {
    try {
      const response = await window.API.getKelompokUmurOverview();
      this.data.overview = response.data || [];
      this.filteredData.overview = [...this.data.overview];
    } catch (error) {
      console.error("Error loading overview data:", error);
      this.data.overview = [];
      this.filteredData.overview = [];
    }
  }

  async loadBalitaData() {
    try {
      const response = await window.API.getKelompokUmurBalita();
      this.data.balita = response.data || [];
      this.filteredData.balita = [...this.data.balita];
    } catch (error) {
      console.error("Error loading balita data:", error);
      this.data.balita = [];
      this.filteredData.balita = [];
    }
  }

  async loadAnakData() {
    try {
      const response = await window.API.getKelompokUmurAnak();
      this.data.anak = response.data || [];
      this.filteredData.anak = [...this.data.anak];
    } catch (error) {
      console.error("Error loading anak data:", error);
      this.data.anak = [];
      this.filteredData.anak = [];
    }
  }

  async loadDewasaData() {
    try {
      const response = await window.API.getKelompokUmurDewasa();
      this.data.dewasa = response.data || [];
      this.filteredData.dewasa = [...this.data.dewasa];
    } catch (error) {
      console.error("Error loading dewasa data:", error);
      this.data.dewasa = [];
      this.filteredData.dewasa = [];
    }
  }

  async loadLansiaData() {
    try {
      const response = await window.API.getKelompokUmurLansia();
      this.data.lansia = response.data || [];
      this.filteredData.lansia = [...this.data.lansia];
    } catch (error) {
      console.error("Error loading lansia data:", error);
      this.data.lansia = [];
      this.filteredData.lansia = [];
    }
  }


  updateOverallStats() {
    const stats = this.calculateOverallStats();

    document.getElementById("statBalita").innerHTML = this.formatNumber(
      stats.totalBalita
    );
    document.getElementById("statAnak").innerHTML = this.formatNumber(
      stats.totalAnak
    );
    document.getElementById("statDewasa").innerHTML = this.formatNumber(
      stats.totalDewasa
    );
    document.getElementById("statLansia").innerHTML = this.formatNumber(
      stats.totalLansia
    );
    document.getElementById("statTotal").innerHTML = this.formatNumber(
      stats.totalKeseluruhan
    );
  }

  calculateOverallStats() {
    const overview = this.data.overview || [];

    return {
      totalBalita: overview.reduce(
        (sum, item) => sum + (parseInt(item.balita) || 0),
        0
      ),
      totalAnak: overview.reduce(
        (sum, item) => sum + (parseInt(item.anak) || 0),
        0
      ),
      totalDewasa: overview.reduce(
        (sum, item) => sum + (parseInt(item.dewasa) || 0),
        0
      ),
      totalLansia: overview.reduce(
        (sum, item) => sum + (parseInt(item.lansia) || 0),
        0
      ),
      totalKeseluruhan: overview.reduce(
        (sum, item) => sum + (parseInt(item.total) || 0),
        0
      ),
    };
  }

  switchTab(tabId) {
    // Update active tab button
    document
      .querySelectorAll(".tab-button")
      .forEach((btn) => btn.classList.remove("active"));
    document.querySelector(`[data-tab="${tabId}"]`).classList.add("active");

    // Update active tab content
    document
      .querySelectorAll(".tab-content")
      .forEach((content) => content.classList.remove("active"));
    document.getElementById(`${tabId}-content`).classList.add("active");

    this.currentTab = tabId;
    this.renderCurrentTab();
  }

  renderCurrentTab() {
    this.updateTabStats(this.currentTab);
    this.renderCharts(this.currentTab);
    this.renderTable(this.currentTab);
  }

  updateTabStats(tab) {
    const statsContainer = document.getElementById(`${tab}-stats`);
    if (!statsContainer) return;

    const stats = this.calculateTabStats(tab);
    statsContainer.innerHTML = this.generateTabStatsHTML(tab, stats);
  }

  calculateTabStats(tab) {
    const data = this.filteredData[tab] || [];

    if (tab === "overview") {
      return {
        totalWilayah: data.length,
        totalBalita: data.reduce(
          (sum, item) => sum + (parseInt(item.balita) || 0),
          0
        ),
        totalAnak: data.reduce(
          (sum, item) => sum + (parseInt(item.anak) || 0),
          0
        ),
        totalDewasa: data.reduce(
          (sum, item) => sum + (parseInt(item.dewasa) || 0),
          0
        ),
        totalLansia: data.reduce(
          (sum, item) => sum + (parseInt(item.lansia) || 0),
          0
        ),
        totalKeseluruhan: data.reduce(
          (sum, item) => sum + (parseInt(item.total) || 0),
          0
        ),
      };
    } else {
      return {
        totalWilayah: data.length,
        totalLakiLaki: data.reduce(
          (sum, item) => sum + (parseInt(item.laki_laki) || 0),
          0
        ),
        totalPerempuan: data.reduce(
          (sum, item) => sum + (parseInt(item.perempuan) || 0),
          0
        ),
        totalKeseluruhan: data.reduce(
          (sum, item) => sum + (parseInt(item.total) || 0),
          0
        ),
      };
    }
  }

  generateTabStatsHTML(tab, stats) {
    if (tab === "overview") {
      return `
                <div class="stat-card">
                    <h3>Total Wilayah</h3>
                    <div class="value">${stats.totalWilayah}</div>
                </div>
                <div class="stat-card balita">
                    <h3>Total Balita</h3>
                    <div class="value">${this.formatNumber(
                      stats.totalBalita
                    )}</div>
                </div>
                <div class="stat-card anak">
                    <h3>Total Anak</h3>
                    <div class="value">${this.formatNumber(
                      stats.totalAnak
                    )}</div>
                </div>
                <div class="stat-card dewasa">
                    <h3>Total Dewasa</h3>
                    <div class="value">${this.formatNumber(
                      stats.totalDewasa
                    )}</div>
                </div>
                <div class="stat-card lansia">
                    <h3>Total Lansia</h3>
                    <div class="value">${this.formatNumber(
                      stats.totalLansia
                    )}</div>
                </div>
            `;
    } else {
      const ageGroupName = this.getAgeGroupName(tab);
      return `
                <div class="stat-card">
                    <h3>Total Wilayah</h3>
                    <div class="value">${stats.totalWilayah}</div>
                </div>
                <div class="stat-card">
                    <h3>Total Laki-laki</h3>
                    <div class="value">${this.formatNumber(
                      stats.totalLakiLaki
                    )}</div>
                </div>
                <div class="stat-card">
                    <h3>Total Perempuan</h3>
                    <div class="value">${this.formatNumber(
                      stats.totalPerempuan
                    )}</div>
                </div>
                <div class="stat-card total">
                    <h3>Total ${ageGroupName}</h3>
                    <div class="value">${this.formatNumber(
                      stats.totalKeseluruhan
                    )}</div>
                </div>
            `;
    }
  }

  getAgeGroupName(tab) {
    const names = {
      balita: "Balita",
      anak: "Anak",
      dewasa: "Dewasa",
      lansia: "Lansia",
    };
    return names[tab] || tab;
  }

  renderCharts(tab) {
    // Destroy existing charts
    if (this.charts[`${tab}-bar`]) {
      this.charts[`${tab}-bar`].destroy();
    }
    if (this.charts[`${tab}-pie`]) {
      this.charts[`${tab}-pie`].destroy();
    }

    // Render new charts
    this.renderBarChart(tab);
    this.renderPieChart(tab);
  }

  renderBarChart(tab) {
    const canvas = document.getElementById(`${tab}-bar-chart`);
    if (!canvas) return;

    const ctx = canvas.getContext("2d");
    const data = this.filteredData[tab] || [];

    if (tab === "overview") {
      this.charts[`${tab}-bar`] = new Chart(ctx, {
        type: "bar",
        data: {
          labels: data.map((item) => item.nama_wilayah),
          datasets: [
            {
              label: "Balita",
              data: data.map((item) => parseInt(item.balita) || 0),
              backgroundColor: this.colors.balita,
              borderWidth: 1,
            },
            {
              label: "Anak",
              data: data.map((item) => parseInt(item.anak) || 0),
              backgroundColor: this.colors.anak,
              borderWidth: 1,
            },
            {
              label: "Dewasa",
              data: data.map((item) => parseInt(item.dewasa) || 0),
              backgroundColor: this.colors.dewasa,
              borderWidth: 1,
            },
            {
              label: "Lansia",
              data: data.map((item) => parseInt(item.lansia) || 0),
              backgroundColor: this.colors.lansia,
              borderWidth: 1,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function (value) {
                  return value.toLocaleString("id-ID");
                },
              },
            },
            x: {
              ticks: {
                maxRotation: 45,
                minRotation: 45,
              },
            },
          },
          plugins: {
            tooltip: {
              callbacks: {
                label: function (context) {
                  return (
                    context.dataset.label +
                    ": " +
                    context.parsed.y.toLocaleString("id-ID")
                  );
                },
              },
            },
          },
        },
      });
    } else {
      this.charts[`${tab}-bar`] = new Chart(ctx, {
        type: "bar",
        data: {
          labels: data.map((item) => item.nama_wilayah),
          datasets: [
            {
              label: `Total ${this.getAgeGroupName(tab)}`,
              data: data.map((item) => parseInt(item.total) || 0),
              backgroundColor: this.colors[tab] || this.colors.total,
              borderWidth: 1,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function (value) {
                  return value.toLocaleString("id-ID");
                },
              },
            },
            x: {
              ticks: {
                maxRotation: 45,
                minRotation: 45,
              },
            },
          },
          plugins: {
            tooltip: {
              callbacks: {
                label: function (context) {
                  return (
                    context.dataset.label +
                    ": " +
                    context.parsed.y.toLocaleString("id-ID")
                  );
                },
              },
            },
          },
        },
      });
    }
  }

  renderPieChart(tab) {
    const canvas = document.getElementById(`${tab}-pie-chart`);
    if (!canvas) return;

    const ctx = canvas.getContext("2d");
    const data = this.filteredData[tab] || [];

    if (tab === "overview") {
      const totals = {
        balita: data.reduce(
          (sum, item) => sum + (parseInt(item.balita) || 0),
          0
        ),
        anak: data.reduce((sum, item) => sum + (parseInt(item.anak) || 0), 0),
        dewasa: data.reduce(
          (sum, item) => sum + (parseInt(item.dewasa) || 0),
          0
        ),
        lansia: data.reduce(
          (sum, item) => sum + (parseInt(item.lansia) || 0),
          0
        ),
      };

      this.charts[`${tab}-pie`] = new Chart(ctx, {
        type: "pie",
        data: {
          labels: ["Balita", "Anak", "Dewasa", "Lansia"],
          datasets: [
            {
              data: [totals.balita, totals.anak, totals.dewasa, totals.lansia],
              backgroundColor: [
                this.colors.balita,
                this.colors.anak,
                this.colors.dewasa,
                this.colors.lansia,
              ],
              borderWidth: 2,
              borderColor: "#fff",
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            tooltip: {
              callbacks: {
                label: function (context) {
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = ((context.parsed * 100) / total).toFixed(
                    1
                  );
                  return (
                    context.label +
                    ": " +
                    context.parsed.toLocaleString("id-ID") +
                    " (" +
                    percentage +
                    "%)"
                  );
                },
              },
            },
          },
        },
      });
    } else {
      const totals = {
        laki_laki: data.reduce(
          (sum, item) => sum + (parseInt(item.laki_laki) || 0),
          0
        ),
        perempuan: data.reduce(
          (sum, item) => sum + (parseInt(item.perempuan) || 0),
          0
        ),
      };

      this.charts[`${tab}-pie`] = new Chart(ctx, {
        type: "pie",
        data: {
          labels: ["Laki-laki", "Perempuan"],
          datasets: [
            {
              data: [totals.laki_laki, totals.perempuan],
              backgroundColor: [this.colors.laki_laki, this.colors.perempuan],
              borderWidth: 2,
              borderColor: "#fff",
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            tooltip: {
              callbacks: {
                label: function (context) {
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = ((context.parsed * 100) / total).toFixed(
                    1
                  );
                  return (
                    context.label +
                    ": " +
                    context.parsed.toLocaleString("id-ID") +
                    " (" +
                    percentage +
                    "%)"
                  );
                },
              },
            },
          },
        },
      });
    }
  }

  renderTable(tab) {
    const container = document.getElementById(`${tab}-table`);
    if (!container) return;

    const data = this.filteredData[tab] || [];

    if (data.length === 0) {
      container.innerHTML = this.getEmptyStateHTML();
      return;
    }

    container.innerHTML = `
            <table class="data-table">
                <thead>
                    ${this.getTableHeaderHTML(tab)}
                </thead>
                <tbody>
                    ${data
                      .map((item) => this.getTableRowHTML(tab, item))
                      .join("")}
                </tbody>
            </table>
        `;
  }

  getTableHeaderHTML(tab) {
    if (tab === "overview") {
      return `
                <tr>
                    <th>Kode Wilayah</th>
                    <th>Nama Wilayah</th>
                    <th class="text-center">Balita</th>
                    <th class="text-center">Anak</th>
                    <th class="text-center">Dewasa</th>
                    <th class="text-center">Lansia</th>
                    <th class="text-center">Total</th>
                </tr>
            `;
    } else {
      return `
                <tr>
                    <th>Kode Wilayah</th>
                    <th>Nama Wilayah</th>
                    <th class="text-center">Laki-laki</th>
                    <th class="text-center">Perempuan</th>
                    <th class="text-center">Total</th>
                </tr>
            `;
    }
  }

  getTableRowHTML(tab, item) {
    if (tab === "overview") {
      return `
                <tr>
                    <td>${item.kode_wilayah || "-"}</td>
                    <td>${item.nama_wilayah || "-"}</td>
                    <td class="text-center number">${this.formatNumber(
                      item.balita || 0
                    )}</td>
                    <td class="text-center number">${this.formatNumber(
                      item.anak || 0
                    )}</td>
                    <td class="text-center number">${this.formatNumber(
                      item.dewasa || 0
                    )}</td>
                    <td class="text-center number">${this.formatNumber(
                      item.lansia || 0
                    )}</td>
                    <td class="text-center number"><strong>${this.formatNumber(
                      item.total || 0
                    )}</strong></td>
                </tr>
            `;
    } else {
      return `
                <tr>
                    <td>${item.kode_wilayah || "-"}</td>
                    <td>${item.nama_wilayah || "-"}</td>
                    <td class="text-center number">${this.formatNumber(
                      item.laki_laki || 0
                    )}</td>
                    <td class="text-center number">${this.formatNumber(
                      item.perempuan || 0
                    )}</td>
                    <td class="text-center number"><strong>${this.formatNumber(
                      item.total || 0
                    )}</strong></td>
                </tr>
            `;
    }
  }

  handleSearch(tab) {
    const searchInput = document.getElementById(`${tab}-search`);
    const searchTerm = searchInput.value.toLowerCase().trim();

    if (searchTerm === "") {
      this.filteredData[tab] = [...this.data[tab]];
    } else {
      this.filteredData[tab] = this.data[tab].filter(
        (item) =>
          (item.nama_wilayah &&
            item.nama_wilayah.toLowerCase().includes(searchTerm)) ||
          (item.kode_wilayah &&
            item.kode_wilayah.toString().toLowerCase().includes(searchTerm))
      );
    }

    this.handleSort(tab);
  }

  handleSort(tab) {
    const sortSelect = document.getElementById(`${tab}-sort`);
    const sortValue = sortSelect.value;

    if (!sortValue) return;

    const [field, direction] = sortValue.split("_");
    const isAsc = direction === "asc";

    this.filteredData[tab].sort((a, b) => {
      let valueA, valueB;

      if (field === "kode" || field === "wilayah") {
        const fieldName = field === "kode" ? "kode_wilayah" : "nama_wilayah";
        valueA = (a[fieldName] || "").toString().toLowerCase();
        valueB = (b[fieldName] || "").toString().toLowerCase();

        if (isAsc) {
          return valueA.localeCompare(valueB);
        } else {
          return valueB.localeCompare(valueA);
        }
      } else {
        // Numeric fields
        valueA = parseInt(a[field]) || 0;
        valueB = parseInt(b[field]) || 0;

        if (isAsc) {
          return valueA - valueB;
        } else {
          return valueB - valueA;
        }
      }
    });

    this.renderTable(tab);
    this.updateTabStats(tab);
  }

  async refreshData(tab) {
    const refreshBtn = document.getElementById(`${tab}-refresh`);
    const originalHTML = refreshBtn.innerHTML;

    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    refreshBtn.disabled = true;

    try {
      if (tab === "overview") {
        await this.loadOverviewData();
      } else {
        const capitalizedTab = tab.charAt(0).toUpperCase() + tab.slice(1);
        await this[`load${capitalizedTab}Data`]();
      }

      // Reset search and sort
      const searchInput = document.getElementById(`${tab}-search`);
      const sortSelect = document.getElementById(`${tab}-sort`);

      if (searchInput) searchInput.value = "";
      if (sortSelect) sortSelect.value = "kode_asc";
      
      this.handleSearch(tab); // Re-apply filter and sort
      
      if (tab === "overview") {
        this.updateOverallStats();
      }
    } catch (error) {
      console.error(`Error refreshing ${tab} data:`, error);
      this.showError(`Gagal memuat ulang data ${this.getAgeGroupName(tab)}`);
    } finally {
      refreshBtn.innerHTML = originalHTML;
      refreshBtn.disabled = false;
    }
  }

  exportData() {
    const data = this.filteredData[this.currentTab] || [];

    if (data.length === 0) {
      alert("Tidak ada data untuk diekspor");
      return;
    }

    let csvContent = "";
    let filename = "";

    if (this.currentTab === "overview") {
      csvContent =
        "Kode Wilayah,Nama Wilayah,Balita,Anak,Dewasa,Lansia,Total\n";
      csvContent += data
        .map(
          (item) =>
            `"${item.kode_wilayah}","${item.nama_wilayah}",${
              item.balita || 0
            },${item.anak || 0},${item.dewasa || 0},${item.lansia || 0},${
              item.total || 0
            }`
        )
        .join("\n");
      filename = "kelompok_umur_overview.csv";
    } else {
      csvContent = "Kode Wilayah,Nama Wilayah,Laki-laki,Perempuan,Total\n";
      csvContent += data
        .map(
          (item) =>
            `"${item.kode_wilayah}","${item.nama_wilayah}",${
              item.laki_laki || 0
            },${item.perempuan || 0},${item.total || 0}`
        )
        .join("\n");
      filename = `kelompok_umur_${this.currentTab}.csv`;
    }

    this.downloadCSV(csvContent, filename);
  }

  downloadCSV(content, filename) {
    const blob = new Blob([content], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");

    if (link.download !== undefined) {
      const url = URL.createObjectURL(blob);
      link.setAttribute("href", url);
      link.setAttribute("download", filename);
      link.style.visibility = "hidden";
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  }

  formatNumber(num) {
    if (num === null || num === undefined || num === "") return "0";
    return parseInt(num).toLocaleString("id-ID");
  }

  showError(message) {
    const containers = [
      "overview-table",
      "balita-table",
      "anak-table",
      "dewasa-table",
      "lansia-table",
    ];

    containers.forEach((containerId) => {
      const container = document.getElementById(containerId);
      if (container) {
        container.innerHTML = `
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>${message}</p>
                        <button onclick="location.reload()" class="btn btn-primary">
                            <i class="fas fa-refresh"></i> Muat Ulang
                        </button>
                    </div>
                `;
      }
    });
  }

  getEmptyStateHTML() {
    return `
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>Tidak Ada Data</h3>
                <p>Belum ada data kelompok umur untuk ditampilkan</p>
            </div>
        `;
  }

  // Utility method to get age group display name
  getAgeGroupDisplayName(tab) {
    const names = {
      overview: "Semua Kelompok Umur",
      balita: "Balita (0-4 tahun)",
      anak: "Anak (5-17 tahun)",
      dewasa: "Dewasa (18-59 tahun)",
      lansia: "Lansia (60+ tahun)",
    };
    return names[tab] || tab;
  }

  // Method to handle window resize for chart responsiveness
  handleResize() {
    Object.keys(this.charts).forEach((chartKey) => {
      if (this.charts[chartKey]) {
        this.charts[chartKey].resize();
      }
    });
  }

  // Cleanup method
  destroy() {
    Object.keys(this.charts).forEach((chartKey) => {
      if (this.charts[chartKey]) {
        this.charts[chartKey].destroy();
      }
    });

    window.removeEventListener("resize", this.handleResize.bind(this));
  }
}

// Initialize dashboard when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  window.kelompokUmurDashboard = new KelompokUmurDashboard();

  // Handle window resize for chart responsiveness
  window.addEventListener("resize", function () {
    if (window.kelompokUmurDashboard) {
      window.kelompokUmurDashboard.handleResize();
    }
  });
});

// Cleanup when page unloads
window.addEventListener("beforeunload", function () {
  if (window.kelompokUmurDashboard) {
    window.kelompokUmurDashboard.destroy();
  }
});