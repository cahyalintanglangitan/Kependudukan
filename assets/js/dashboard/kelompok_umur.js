// assets/js/dashboard/kelompok_umur.js

class KelompokUmurDashboard {
  constructor() {
    this.currentTab = "overview";
    this.currentData = null;
    this.charts = {};
    this.currentPage = 1;
    this.itemsPerPage = 25;
    this.currentSort = "kode_asc";
    this.currentSearch = "";

    this.init();
  }

  init() {
    this.setupEventListeners();
    this.loadInitialData();
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
    const tabs = ["overview", "balita", "anak", "dewasa", "lansia"];
    tabs.forEach((tab) => {
      const searchInput = document.getElementById(`${tab}-search`);
      const sortSelect = document.getElementById(`${tab}-sort`);
      const refreshBtn = document.getElementById(`${tab}-refresh`);

      if (searchInput) {
        searchInput.addEventListener("input", () => {
          this.currentSearch = searchInput.value;
          this.currentPage = 1;
          this.renderTable();
        });
      }

      if (sortSelect) {
        sortSelect.addEventListener("change", () => {
          this.currentSort = sortSelect.value;
          this.currentPage = 1;
          this.renderTable();
        });
      }

      if (refreshBtn) {
        refreshBtn.addEventListener("click", () => {
          this.loadInitialData();
        });
      }
    });
  }

  async loadInitialData() {
    try {
      this.showLoading();

      // Load all kelompok umur data
      const response = await fetch(`${window.API_BASE_URL}kelompok_umur.php`);
      if (!response.ok) throw new Error("Failed to fetch data");

      const data = await response.json();
      this.currentData = data;

      this.updateOverallStats();
      this.renderCurrentTab();
    } catch (error) {
      console.error("Error loading data:", error);
      this.showError("Gagal memuat data. Silakan coba lagi.");
    }
  }

  showLoading() {
    const statElements = [
      "statBalita",
      "statAnak",
      "statDewasa",
      "statLansia",
      "statTotal",
    ];
    statElements.forEach((id) => {
      const element = document.getElementById(id);
      if (element) {
        element.innerHTML = '<div class="loading-spinner"></div>';
      }
    });
  }

  showError(message) {
    const statElements = [
      "statBalita",
      "statAnak",
      "statDewasa",
      "statLansia",
      "statTotal",
    ];
    statElements.forEach((id) => {
      const element = document.getElementById(id);
      if (element) {
        element.innerHTML =
          '<span style="color: #dc3545; font-size: 14px;">Error</span>';
      }
    });

    // Show error in current table
    const tableContainer = document.getElementById(`${this.currentTab}-table`);
    if (tableContainer) {
      tableContainer.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Terjadi Kesalahan</h3>
                    <p>${message}</p>
                </div>
            `;
    }
  }

  updateOverallStats() {
    if (!this.currentData) return;

    // Calculate totals based on sample data provided
    const stats = {
      balita: 25837,
      anak: 70203, // 34349 + 35853 + additional data
      dewasa: 275633,
      lansia: 108176,
      total: 479851,
    };

    // Update stat cards
    document.getElementById(
      "statBalita"
    ).innerHTML = `<span>${this.formatNumber(stats.balita)}</span>`;
    document.getElementById("statAnak").innerHTML = `<span>${this.formatNumber(
      stats.anak
    )}</span>`;
    document.getElementById(
      "statDewasa"
    ).innerHTML = `<span>${this.formatNumber(stats.dewasa)}</span>`;
    document.getElementById(
      "statLansia"
    ).innerHTML = `<span>${this.formatNumber(stats.lansia)}</span>`;
    document.getElementById("statTotal").innerHTML = `<span>${this.formatNumber(
      stats.total
    )}</span>`;
  }

  switchTab(tabId) {
    // Update active tab button
    document.querySelectorAll(".tab-button").forEach((btn) => {
      btn.classList.remove("active");
    });
    document.querySelector(`[data-tab="${tabId}"]`).classList.add("active");

    // Update active tab content
    document.querySelectorAll(".tab-content").forEach((content) => {
      content.classList.remove("active");
    });
    document.getElementById(`${tabId}-content`).classList.add("active");

    this.currentTab = tabId;
    this.currentPage = 1;
    this.renderCurrentTab();
  }

  renderCurrentTab() {
    switch (this.currentTab) {
      case "overview":
        this.renderOverviewTab();
        break;
      case "balita":
        this.renderBalitaTab();
        break;
      case "anak":
        this.renderAnakTab();
        break;
      case "dewasa":
        this.renderDewasaTab();
        break;
      case "lansia":
        this.renderLansiaTab();
        break;
    }
  }

  renderOverviewTab() {
    this.createOverviewCharts();
    this.renderOverviewTable();
  }

  renderBalitaTab() {
    this.updateTabStats("balita");
    this.createAgeGroupCharts("balita");
    this.renderTable();
  }

  renderAnakTab() {
    this.updateTabStats("anak");
    this.createAgeGroupCharts("anak");
    this.renderTable();
  }

  renderDewasaTab() {
    this.updateTabStats("dewasa");
    this.createAgeGroupCharts("dewasa");
    this.renderTable();
  }

  renderLansiaTab() {
    this.updateTabStats("lansia");
    this.createAgeGroupCharts("lansia");
    this.renderTable();
  }

  updateTabStats(ageGroup) {
    const statsContainer = document.getElementById(`${ageGroup}-stats`);
    if (!statsContainer) return;

    // Sample stats for each age group
    const groupStats = {
      balita: {
        total: 25837,
        percentage: 5.4,
        highest: "Aceh Utara",
        lowest: "Aceh Jaya",
      },
      anak: {
        total: 70203,
        percentage: 14.7,
        highest: "Aceh Besar",
        lowest: "Aceh Singkil",
      },
      dewasa: {
        total: 275633,
        percentage: 57.4,
        highest: "Banda Aceh",
        lowest: "Aceh Tamiang",
      },
      lansia: {
        total: 108176,
        percentage: 22.5,
        highest: "Aceh Barat",
        lowest: "Aceh Selatan",
      },
    };

    const stats = groupStats[ageGroup];

    statsContainer.innerHTML = `
            <div class="stat-card">
                <h3>Total ${this.getAgeGroupLabel(ageGroup)}</h3>
                <div class="value">${this.formatNumber(stats.total)}</div>
            </div>
            <div class="stat-card">
                <h3>Persentase dari Total</h3>
                <div class="value">${stats.percentage}%</div>
            </div>
            <div class="stat-card">
                <h3>Wilayah Tertinggi</h3>
                <div class="value" style="font-size: 18px;">${
                  stats.highest
                }</div>
            </div>
            <div class="stat-card">
                <h3>Wilayah Terendah</h3>
                <div class="value" style="font-size: 18px;">${
                  stats.lowest
                }</div>
            </div>
        `;
  }

  createOverviewCharts() {
    // Destroy existing charts
    if (this.charts["overview-pie"]) this.charts["overview-pie"].destroy();
    if (this.charts["overview-bar"]) this.charts["overview-bar"].destroy();

    // Pie Chart - Age Group Distribution
    const pieCtx = document.getElementById("overview-pie-chart");
    if (pieCtx) {
      this.charts["overview-pie"] = new Chart(pieCtx, {
        type: "pie",
        data: {
          labels: [
            "Balita (0-4)",
            "Anak (5-14)",
            "Dewasa (15-59)",
            "Lansia (60+)",
          ],
          datasets: [
            {
              data: [25837, 70203, 275633, 108176],
              backgroundColor: ["#ff6b6b", "#4ecdc4", "#45b7d1", "#96ceb4"],
              borderWidth: 2,
              borderColor: "#fff",
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "bottom",
            },
            tooltip: {
              callbacks: {
                label: (context) => {
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = ((context.raw / total) * 100).toFixed(1);
                  return `${context.label}: ${this.formatNumber(
                    context.raw
                  )} (${percentage}%)`;
                },
              },
            },
          },
        },
      });
    }

    // Bar Chart - Sample regional comparison
    const barCtx = document.getElementById("overview-bar-chart");
    if (barCtx) {
      this.charts["overview-bar"] = new Chart(barCtx, {
        type: "bar",
        data: {
          labels: [
            "Banda Aceh",
            "Aceh Besar",
            "Aceh Utara",
            "Aceh Barat",
            "Aceh Selatan",
          ],
          datasets: [
            {
              label: "Balita",
              data: [1200, 950, 1100, 800, 750],
              backgroundColor: "#ff6b6b",
            },
            {
              label: "Anak",
              data: [3200, 2800, 3100, 2500, 2200],
              backgroundColor: "#4ecdc4",
            },
            {
              label: "Dewasa",
              data: [12000, 11000, 10500, 9500, 9000],
              backgroundColor: "#45b7d1",
            },
            {
              label: "Lansia",
              data: [4500, 4200, 3800, 3500, 3200],
              backgroundColor: "#96ceb4",
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: {
              stacked: true,
            },
            y: {
              stacked: true,
              beginAtZero: true,
            },
          },
          plugins: {
            legend: {
              position: "bottom",
            },
          },
        },
      });
    }
  }

  createAgeGroupCharts(ageGroup) {
    // Destroy existing charts
    if (this.charts[`${ageGroup}-bar`])
      this.charts[`${ageGroup}-bar`].destroy();
    if (this.charts[`${ageGroup}-pie`])
      this.charts[`${ageGroup}-pie`].destroy();

    const colors = {
      balita: "#ff6b6b",
      anak: "#4ecdc4",
      dewasa: "#45b7d1",
      lansia: "#96ceb4",
    };

    // Sample data for charts
    const sampleData = this.generateSampleData(ageGroup);

    // Bar Chart
    const barCtx = document.getElementById(`${ageGroup}-bar-chart`);
    if (barCtx) {
      this.charts[`${ageGroup}-bar`] = new Chart(barCtx, {
        type: "bar",
        data: {
          labels: sampleData.labels,
          datasets: [
            {
              label: this.getAgeGroupLabel(ageGroup),
              data: sampleData.values,
              backgroundColor: colors[ageGroup],
              borderColor: colors[ageGroup],
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
            },
          },
          plugins: {
            legend: {
              display: false,
            },
          },
        },
      });
    }

    // Pie Chart
    const pieCtx = document.getElementById(`${ageGroup}-pie-chart`);
    if (pieCtx) {
      this.charts[`${ageGroup}-pie`] = new Chart(pieCtx, {
        type: "pie",
        data: {
          labels: sampleData.labels.slice(0, 5),
          datasets: [
            {
              data: sampleData.values.slice(0, 5),
              backgroundColor: [
                colors[ageGroup],
                this.adjustColor(colors[ageGroup], -20),
                this.adjustColor(colors[ageGroup], -40),
                this.adjustColor(colors[ageGroup], -60),
                this.adjustColor(colors[ageGroup], -80),
              ],
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "bottom",
            },
          },
        },
      });
    }
  }

  renderOverviewTable() {
    const tableContainer = document.getElementById("overview-table");
    if (!tableContainer) return;

    // Sample comprehensive data
    const sampleData = [
      {
        kode: "11.00",
        wilayah: "ACEH",
        balita: 25837,
        anak: 70203,
        dewasa: 275633,
        lansia: 108176,
        total: 479851,
      },
      {
        kode: "11.01",
        wilayah: "ACEH SELATAN",
        balita: 1200,
        anak: 3200,
        dewasa: 12000,
        lansia: 4500,
        total: 20900,
      },
      {
        kode: "11.02",
        wilayah: "ACEH TENGGARA",
        balita: 950,
        anak: 2800,
        dewasa: 11000,
        lansia: 4200,
        total: 18950,
      },
      {
        kode: "11.03",
        wilayah: "ACEH TIMUR",
        balita: 1100,
        anak: 3100,
        dewasa: 10500,
        lansia: 3800,
        total: 18500,
      },
      {
        kode: "11.04",
        wilayah: "ACEH TENGAH",
        balita: 800,
        anak: 2500,
        dewasa: 9500,
        lansia: 3500,
        total: 16300,
      },
      {
        kode: "11.05",
        wilayah: "ACEH BARAT",
        balita: 750,
        anak: 2200,
        dewasa: 9000,
        lansia: 3200,
        total: 15150,
      },
    ];

    this.renderDataTable(tableContainer, sampleData, "overview");
  }

  renderTable() {
    const tableContainer = document.getElementById(`${this.currentTab}-table`);
    if (!tableContainer || this.currentTab === "overview") return;

    // Sample data for specific age group
    const sampleData = this.generateTableData(this.currentTab);
    this.renderDataTable(tableContainer, sampleData, this.currentTab);
  }

  renderDataTable(container, data, type) {
    if (!data || data.length === 0) {
      container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-table"></i>
                    <h3>Tidak ada data</h3>
                    <p>Data tidak tersedia untuk ditampilkan</p>
                </div>
            `;
      return;
    }

    // Filter and sort data
    let filteredData = this.filterData(data);
    let sortedData = this.sortData(filteredData);

    // Pagination
    const startIndex = (this.currentPage - 1) * this.itemsPerPage;
    const paginatedData = sortedData.slice(
      startIndex,
      startIndex + this.itemsPerPage
    );

    let tableHTML = '<table class="data-table">';

    // Table header
    if (type === "overview") {
      tableHTML += `
                <thead>
                    <tr>
                        <th>Kode Wilayah</th>
                        <th>Wilayah</th>
                        <th>Balita (0-4)</th>
                        <th>Anak (5-14)</th>
                        <th>Dewasa (15-59)</th>
                        <th>Lansia (60+)</th>
                        <th>Total</th>
                    </tr>
                </thead>
            `;
    } else {
      tableHTML += `
                <thead>
                    <tr>
                        <th>Kode Wilayah</th>
                        <th>Wilayah</th>
                        <th>Jumlah ${this.getAgeGroupLabel(type)}</th>
                        <th>Persentase</th>
                    </tr>
                </thead>
            `;
    }

    // Table body
    tableHTML += "<tbody>";
    paginatedData.forEach((row) => {
      if (type === "overview") {
        tableHTML += `
                    <tr>
                        <td>${row.kode}</td>
                        <td>${row.wilayah}</td>
                        <td>${this.formatNumber(row.balita)}</td>
                        <td>${this.formatNumber(row.anak)}</td>
                        <td>${this.formatNumber(row.dewasa)}</td>
                        <td>${this.formatNumber(row.lansia)}</td>
                        <td><strong>${this.formatNumber(
                          row.total
                        )}</strong></td>
                    </tr>
                `;
      } else {
        const percentage = ((row.value / row.total) * 100).toFixed(1);
        tableHTML += `
                    <tr>
                        <td>${row.kode}</td>
                        <td>${row.wilayah}</td>
                        <td>${this.formatNumber(row.value)}</td>
                        <td><span class="${this.getPercentageClass(
                          percentage
                        )}">${percentage}%</span></td>
                    </tr>
                `;
      }
    });
    tableHTML += "</tbody></table>";

    // Add pagination
    tableHTML += this.generatePagination(sortedData.length);

    container.innerHTML = tableHTML;
  }

  generateSampleData(ageGroup) {
    const regions = [
      "Banda Aceh",
      "Aceh Besar",
      "Aceh Utara",
      "Aceh Barat",
      "Aceh Selatan",
      "Aceh Timur",
    ];
    const baseValues = {
      balita: [1200, 950, 1100, 800, 750, 900],
      anak: [3200, 2800, 3100, 2500, 2200, 2600],
      dewasa: [12000, 11000, 10500, 9500, 9000, 9800],
      lansia: [4500, 4200, 3800, 3500, 3200, 3600],
    };

    return {
      labels: regions,
      values: baseValues[ageGroup],
    };
  }

  generateTableData(ageGroup) {
    const sampleData = [
      {
        kode: "11.01",
        wilayah: "ACEH SELATAN",
        balita: 1200,
        anak: 3200,
        dewasa: 12000,
        lansia: 4500,
        total: 20900,
      },
      {
        kode: "11.02",
        wilayah: "ACEH TENGGARA",
        balita: 950,
        anak: 2800,
        dewasa: 11000,
        lansia: 4200,
        total: 18950,
      },
      {
        kode: "11.03",
        wilayah: "ACEH TIMUR",
        balita: 1100,
        anak: 3100,
        dewasa: 10500,
        lansia: 3800,
        total: 18500,
      },
      {
        kode: "11.04",
        wilayah: "ACEH TENGAH",
        balita: 800,
        anak: 2500,
        dewasa: 9500,
        lansia: 3500,
        total: 16300,
      },
      {
        kode: "11.05",
        wilayah: "ACEH BARAT",
        balita: 750,
        anak: 2200,
        dewasa: 9000,
        lansia: 3200,
        total: 15150,
      },
    ];

    return sampleData.map((item) => ({
      kode: item.kode,
      wilayah: item.wilayah,
      value: item[ageGroup],
      total: item.total,
    }));
  }

  filterData(data) {
    if (!this.currentSearch) return data;

    return data.filter(
      (item) =>
        item.wilayah.toLowerCase().includes(this.currentSearch.toLowerCase()) ||
        item.kode.toLowerCase().includes(this.currentSearch.toLowerCase())
    );
  }

  sortData(data) {
    const [field, direction] = this.currentSort.split("_");

    return data.sort((a, b) => {
      let valueA, valueB;

      switch (field) {
        case "kode":
          valueA = a.kode;
          valueB = b.kode;
          break;
        case "wilayah":
          valueA = a.wilayah;
          valueB = b.wilayah;
          break;
        case "total":
          valueA = a.total;
          valueB = b.total;
          break;
        case "balita":
          valueA = a.balita || a.value;
          valueB = b.balita || b.value;
          break;
        case "anak":
        case "dewasa":
        case "lansia":
          valueA = a[field] || a.value;
          valueB = b[field] || b.value;
          break;
        case "persentase":
          valueA = (a.value / a.total) * 100;
          valueB = (b.value / b.total) * 100;
          break;
        default:
          valueA = a[field];
          valueB = b[field];
      }

      if (typeof valueA === "string") {
        valueA = valueA.toLowerCase();
        valueB = valueB.toLowerCase();
      }

      if (direction === "asc") {
        return valueA > valueB ? 1 : -1;
      } else {
        return valueA < valueB ? 1 : -1;
      }
    });
  }

  generatePagination(totalItems) {
    const totalPages = Math.ceil(totalItems / this.itemsPerPage);
    const startItem = (this.currentPage - 1) * this.itemsPerPage + 1;
    const endItem = Math.min(this.currentPage * this.itemsPerPage, totalItems);

    let paginationHTML = `
            <div class="table-pagination">
                <div class="pagination-info">
                    Menampilkan ${startItem}-${endItem} dari ${totalItems} data
                </div>
                <div class="pagination-controls">
                    <button class="pagination-btn" ${
                      this.currentPage === 1 ? "disabled" : ""
                    } onclick="window.kelompokUmurDashboard.changePage(${
      this.currentPage - 1
    })">
                        <i class="fas fa-chevron-left"></i>
                    </button>
        `;

    // Page numbers
    for (
      let i = Math.max(1, this.currentPage - 2);
      i <= Math.min(totalPages, this.currentPage + 2);
      i++
    ) {
      paginationHTML += `
                <button class="pagination-btn ${
                  i === this.currentPage ? "active" : ""
                }" onclick="window.kelompokUmurDashboard.changePage(${i})">
                    ${i}
                </button>
            `;
    }

    paginationHTML += `
                    <button class="pagination-btn" ${
                      this.currentPage === totalPages ? "disabled" : ""
                    } onclick="window.kelompokUmurDashboard.changePage(${
      this.currentPage + 1
    })">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        `;

    return paginationHTML;
  }

  changePage(page) {
    this.currentPage = page;
    this.renderCurrentTab();
  }

  getAgeGroupLabel(ageGroup) {
    const labels = {
      balita: "Balita (0-4)",
      anak: "Anak (5-14)",
      dewasa: "Dewasa (15-59)",
      lansia: "Lansia (60+)",
    };
    return labels[ageGroup] || ageGroup;
  }

  getPercentageClass(percentage) {
    if (percentage >= 15) return "percentage-high";
    if (percentage >= 10) return "percentage-medium";
    return "percentage-low";
  }

  adjustColor(color, amount) {
    // Simple color adjustment function
    const num = parseInt(color.replace("#", ""), 16);
    const amt = Math.round(2.55 * amount);
    const R = (num >> 16) + amt;
    const G = ((num >> 8) & 0x00ff) + amt;
    const B = (num & 0x0000ff) + amt;
    return (
      "#" +
      (
        0x1000000 +
        (R < 255 ? (R < 1 ? 0 : R) : 255) * 0x10000 +
        (G < 255 ? (G < 1 ? 0 : G) : 255) * 0x100 +
        (B < 255 ? (B < 1 ? 0 : B) : 255)
      )
        .toString(16)
        .slice(1)
    );
  }

  formatNumber(number) {
    if (number === null || number === undefined) return "-";
    return new Intl.NumberFormat("id-ID").format(number);
  }

  async exportData() {
    try {
      let dataToExport = [];
      let filename = "kelompok_umur_";

      if (this.currentTab === "overview") {
        // Export comprehensive overview data
        dataToExport = [
          [
            "Kode Wilayah",
            "Wilayah",
            "Balita (0-4)",
            "Anak (5-14)",
            "Dewasa (15-59)",
            "Lansia (60+)",
            "Total",
          ],
          [
            "11.00",
            "ACEH",
            "25,837",
            "70,203",
            "275,633",
            "108,176",
            "479,851",
          ],
          [
            "11.01",
            "ACEH SELATAN",
            "1,200",
            "3,200",
            "12,000",
            "4,500",
            "20,900",
          ],
          [
            "11.02",
            "ACEH TENGGARA",
            "950",
            "2,800",
            "11,000",
            "4,200",
            "18,950",
          ],
          [
            "11.03",
            "ACEH TIMUR",
            "1,100",
            "3,100",
            "10,500",
            "3,800",
            "18,500",
          ],
          ["11.04", "ACEH TENGAH", "800", "2,500", "9,500", "3,500", "16,300"],
          ["11.05", "ACEH BARAT", "750", "2,200", "9,000", "3,200", "15,150"],
        ];
        filename += "overview";
      } else {
        // Export specific age group data
        dataToExport = [
          [
            "Kode Wilayah",
            "Wilayah",
            `Jumlah ${this.getAgeGroupLabel(this.currentTab)}`,
            "Persentase",
          ],
        ];

        const tableData = this.generateTableData(this.currentTab);
        tableData.forEach((row) => {
          const percentage = ((row.value / row.total) * 100).toFixed(1);
          dataToExport.push([
            row.kode,
            row.wilayah,
            this.formatNumber(row.value),
            `${percentage}%`,
          ]);
        });
        filename += this.currentTab;
      }

      // Convert to CSV
      const csv = dataToExport
        .map((row) => row.map((cell) => `"${cell}"`).join(","))
        .join("\n");

      // Download
      const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.download = `${filename}_${
        new Date().toISOString().split("T")[0]
      }.csv`;
      link.click();
    } catch (error) {
      console.error("Error exporting data:", error);
      alert("Gagal mengekspor data. Silakan coba lagi.");
    }
  }

  // Utility method to show loading state for charts
  showChartLoading(chartId) {
    const canvas = document.getElementById(chartId);
    if (canvas) {
      const container = canvas.parentElement;
      container.innerHTML = `
                <div class="chart-loading">
                    <div class="loading-spinner"></div>
                    <span>Memuat grafik...</span>
                </div>
            `;
    }
  }

  // Method to handle responsive chart updates
  handleResize() {
    Object.values(this.charts).forEach((chart) => {
      if (chart && typeof chart.resize === "function") {
        chart.resize();
      }
    });
  }

  // Clean up method
  destroy() {
    // Destroy all charts
    Object.values(this.charts).forEach((chart) => {
      if (chart && typeof chart.destroy === "function") {
        chart.destroy();
      }
    });
    this.charts = {};

    // Remove event listeners
    window.removeEventListener("resize", this.handleResize.bind(this));
  }
}

// Initialize dashboard when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  window.kelompokUmurDashboard = new KelompokUmurDashboard();

  // Handle window resize for responsive charts
  window.addEventListener("resize", () => {
    if (window.kelompokUmurDashboard) {
      window.kelompokUmurDashboard.handleResize();
    }
  });
});

// Cleanup when page is unloaded
window.addEventListener("beforeunload", () => {
  if (window.kelompokUmurDashboard) {
    window.kelompokUmurDashboard.destroy();
  }
});
