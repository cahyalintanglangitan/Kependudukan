// assets/js/dashboard/kepala_keluarga.js

class KepalaKeluargaDashboard {
  constructor() {
    this.data = [];
    this.filteredData = [];
    this.charts = {};
    this.provinces = [];

    this.initializeFilters();
    this.bindEvents();
    this.loadData();
  }

  initializeFilters() {
    // Initialize filter values
    this.filters = {
      regionType: "all",
      province: "all",
      sort: "total_desc",
    };
  }

  bindEvents() {
    // Filter change events
    document
      .getElementById("regionTypeFilter")
      .addEventListener("change", () => {
        this.handleFilterChange();
      });

    document.getElementById("provinceFilter").addEventListener("change", () => {
      this.handleFilterChange();
    });

    document.getElementById("sortFilter").addEventListener("change", () => {
      this.handleFilterChange();
    });

    // Refresh button
    document.getElementById("refreshBtn").addEventListener("click", () => {
      this.loadData(true);
    });
  }

  async loadData(forceRefresh = false) {
    try {
      this.showLoadingState();

      // Load provinces for filter
      await this.loadProvinces();

      // Load kepala keluarga data
      const response = await fetch(`${window.API_BASE_URL}kepala_keluarga.php`);
      if (!response.ok) throw new Error("Failed to fetch data");

      const result = await response.json();
      if (result.status === "success") {
        this.data = result.data;
        this.applyFilters();
        this.updateStats();
        this.updateCharts();
      } else {
        throw new Error(result.message || "Failed to load data");
      }
    } catch (error) {
      console.error("Error loading data:", error);
      this.showErrorState(error.message);
    }
  }

  async loadProvinces() {
    try {
      const response = await fetch(`${window.API_BASE_URL}provinces.php`);
      if (!response.ok) throw new Error("Failed to fetch provinces");

      const result = await response.json();
      if (result.status === "success") {
        this.provinces = result.data;
        this.populateProvinceFilter();
      }
    } catch (error) {
      console.error("Error loading provinces:", error);
    }
  }

  populateProvinceFilter() {
    const select = document.getElementById("provinceFilter");
    select.innerHTML = '<option value="all">Semua Provinsi</option>';

    this.provinces.forEach((province) => {
      const option = document.createElement("option");
      option.value = province.id;
      option.textContent = province.name;
      select.appendChild(option);
    });
  }

  handleFilterChange() {
    this.filters.regionType = document.getElementById("regionTypeFilter").value;
    this.filters.province = document.getElementById("provinceFilter").value;
    this.filters.sort = document.getElementById("sortFilter").value;

    this.applyFilters();
    this.updateStats();
    this.updateCharts();
  }

  applyFilters() {
    this.filteredData = [...this.data];

    // Apply region type filter
    if (this.filters.regionType !== "all") {
      this.filteredData = this.filteredData.filter((item) => {
        return item.type === this.filters.regionType;
      });
    }

    // Apply province filter
    if (this.filters.province !== "all") {
      this.filteredData = this.filteredData.filter((item) => {
        return item.province_id === parseInt(this.filters.province);
      });
    }

    // Apply sorting
    this.applySorting();

    // Update filter stats
    document.getElementById("dataCount").textContent = this.filteredData.length;
  }

  applySorting() {
    const sortMap = {
      total_desc: (a, b) => b.total - a.total,
      total_asc: (a, b) => a.total - b.total,
      name_asc: (a, b) => a.name.localeCompare(b.name),
      name_desc: (a, b) => b.name.localeCompare(a.name),
      laki_desc: (a, b) => b.laki_laki - a.laki_laki,
      perempuan_desc: (a, b) => b.perempuan - a.perempuan,
    };

    if (sortMap[this.filters.sort]) {
      this.filteredData.sort(sortMap[this.filters.sort]);
    }
  }

  updateStats() {
    const stats = this.calculateStats();

    document.getElementById("statLakiLaki").innerHTML = this.formatNumber(
      stats.lakiLaki
    );
    document.getElementById("statPerempuan").innerHTML = this.formatNumber(
      stats.perempuan
    );
    document.getElementById("statTotal").innerHTML = this.formatNumber(
      stats.total
    );
  }

  calculateStats() {
    return this.filteredData.reduce(
      (acc, item) => {
        acc.lakiLaki += parseInt(item.laki_laki || 0);
        acc.perempuan += parseInt(item.perempuan || 0);
        acc.total += parseInt(item.total || 0);
        return acc;
      },
      { lakiLaki: 0, perempuan: 0, total: 0 }
    );
  }

  updateCharts() {
    this.updateBarChart();
    this.updatePieChart();
  }

  updateBarChart() {
    const ctx = document.getElementById("barChart");
    if (!ctx) return;

    // Destroy existing chart
    if (this.charts.bar) {
      this.charts.bar.destroy();
    }

    // Prepare data (top 15 regions)
    const top15 = this.filteredData.slice(0, 15);
    const labels = top15.map((item) => this.truncateLabel(item.name, 15));
    const lakiLakiData = top15.map((item) => parseInt(item.laki_laki || 0));
    const perempuanData = top15.map((item) => parseInt(item.perempuan || 0));

    this.charts.bar = new Chart(ctx, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Kepala Keluarga Laki-laki",
            data: lakiLakiData,
            backgroundColor: "#2563eb",
            borderColor: "#1d4ed8",
            borderWidth: 1,
          },
          {
            label: "Kepala Keluarga Perempuan",
            data: perempuanData,
            backgroundColor: "#dc2626",
            borderColor: "#b91c1c",
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
                return new Intl.NumberFormat("id-ID").format(value);
              },
            },
          },
          x: {
            ticks: {
              maxRotation: 45,
              minRotation: 0,
            },
          },
        },
        plugins: {
          legend: {
            display: false, // Using custom legend
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                return (
                  context.dataset.label +
                  ": " +
                  new Intl.NumberFormat("id-ID").format(context.parsed.y)
                );
              },
            },
          },
        },
      },
    });
  }

  updatePieChart() {
    const ctx = document.getElementById("pieChart");
    if (!ctx) return;

    // Destroy existing chart
    if (this.charts.pie) {
      this.charts.pie.destroy();
    }

    const stats = this.calculateStats();

    this.charts.pie = new Chart(ctx, {
      type: "doughnut",
      data: {
        labels: ["Kepala Keluarga Laki-laki", "Kepala Keluarga Perempuan"],
        datasets: [
          {
            data: [stats.lakiLaki, stats.perempuan],
            backgroundColor: ["#2563eb", "#dc2626"],
            borderColor: ["#1d4ed8", "#b91c1c"],
            borderWidth: 2,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              padding: 20,
              usePointStyle: true,
            },
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((context.parsed * 100) / total).toFixed(1);
                return (
                  context.label +
                  ": " +
                  new Intl.NumberFormat("id-ID").format(context.parsed) +
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

  showLoadingState() {
    const loadingElements = document.querySelectorAll(".loading-spinner");
    loadingElements.forEach((element) => {
      element.style.display = "block";
    });
  }

  hideLoadingState() {
    const loadingElements = document.querySelectorAll(".loading-spinner");
    loadingElements.forEach((element) => {
      element.style.display = "none";
    });
  }

  showErrorState(message) {
    this.hideLoadingState();

    // Show error in stats
    ["statLakiLaki", "statPerempuan", "statTotal"].forEach((id) => {
      const element = document.getElementById(id);
      if (element) {
        element.innerHTML =
          '<span style="color: #dc2626; font-size: 14px;">Error loading data</span>';
      }
    });

    console.error("Dashboard Error:", message);
  }

  formatNumber(number) {
    return new Intl.NumberFormat("id-ID").format(number || 0);
  }

  truncateLabel(label, maxLength) {
    if (label.length <= maxLength) return label;
    return label.substring(0, maxLength - 3) + "...";
  }
}

// Initialize dashboard when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  window.kepalaKeluargaDashboard = new KepalaKeluargaDashboard();
});
