// assets/js/dashboard/kelompok_umur.js

// Declare necessary functions
function showLoadingSpinner() {
  // Implementation for showLoadingSpinner
  console.log("Loading spinner shown")
}

function hideLoadingSpinner() {
  // Implementation for hideLoadingSpinner
  console.log("Loading spinner hidden")
}

function showSuccess(message) {
  // Implementation for showSuccess
  console.log("Success message shown:", message)
}

function showError(message) {
  // Implementation for showError
  console.log("Error message shown:", message)
}

function formatNumber(number) {
  // Implementation for formatNumber
  return number.toLocaleString()
}

class KelompokUmurDashboard {
  constructor() {
    this.currentTab = "kelompok_detail"
    this.rawData = []
    this.filteredData = []
    this.statsData = {}
    this.charts = {}
    this.init()
  }

  async init() {
    this.setupEventListeners()

    const connectionOk = await this.testConnection()
    if (connectionOk) {
      await this.loadData()
      this.renderCurrentTab()
    }
  }

  async testConnection() {
    try {
      showLoadingSpinner()
      const response = await fetch(`${window.API_BASE_URL}kelompok_umur.php?action=test_connection`)
      const result = await response.json()

      if (!result.success) {
        throw new Error(result.message || "Database connection failed")
      }

      console.log("Database connection successful:", result)
      showSuccess("Koneksi database berhasil")
      return true
    } catch (error) {
      console.error("Database connection test failed:", error)
      showError("Koneksi database gagal: " + error.message)
      return false
    } finally {
      hideLoadingSpinner()
    }
  }

  setupEventListeners() {
    // Tab switching
    document
      .querySelectorAll(".tab-button")
      .forEach((button) => {
        button.addEventListener("click", (e) => {
          this.switchTab(e.target.closest(".tab-button").dataset.tab)
        })
      })

    // Search and sort for each tab
    ;["kelompok_detail", "distribusi_wilayah"].forEach((tab) => {
      const searchInput = document.getElementById(`${tab}-search`)
      const sortSelect = document.getElementById(`${tab}-sort`)

      if (searchInput) {
        searchInput.addEventListener("input", () => this.handleSearch(tab))
      }
      if (sortSelect) {
        sortSelect.addEventListener("change", () => this.handleSort(tab))
      }
    })
  }

  async loadData() {
    try {
      showLoadingSpinner()

      // Load main data
      const response = await fetch(`${window.API_BASE_URL}kelompok_umur.php?action=get_all`)

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const result = await response.json()

      // Load statistics data
      const statsResponse = await fetch(`${window.API_BASE_URL}kelompok_umur.php?action=get_stats`)

      if (!statsResponse.ok) {
        throw new Error(`HTTP error! status: ${statsResponse.status}`)
      }

      const statsResult = await statsResponse.json()

      if (result.success && statsResult.success) {
        this.rawData = result.data
        this.filteredData = [...this.rawData]
        this.statsData = statsResult.data
        this.updateOverallStats()

        console.log("Data loaded successfully:", {
          records: result.total_records,
          stats: Object.keys(statsResult.data),
        })

        showSuccess(`Data berhasil dimuat: ${result.total_records} record`)
      } else {
        throw new Error(result.message || statsResult.message || "Failed to load data")
      }
    } catch (error) {
      console.error("Error loading data:", error)
      showError("Terjadi kesalahan saat memuat data: " + error.message)
    } finally {
      hideLoadingSpinner()
    }
  }

  updateOverallStats() {
    if (!this.statsData.overall) {
      console.error("Stats data not available")
      return
    }

    const stats = this.statsData.overall

    // Update overall stat cards
    document.getElementById("statBalita").innerHTML = formatNumber(stats.total_balita)
    document.getElementById("statAnak").innerHTML = formatNumber(stats.total_anak)
    document.getElementById("statDewasa").innerHTML = formatNumber(stats.total_dewasa)
    document.getElementById("statLansia").innerHTML = formatNumber(stats.total_lansia)
    document.getElementById("statTotal").innerHTML = formatNumber(stats.total_keseluruhan)
  }

  calculateOverallStats() {
    const stats = {
      balita: 0, // 0-4 tahun
      anak: 0, // 5-14 tahun
      dewasa: 0, // 15-59 tahun
      lansia: 0, // 60+ tahun
      total: 0,
    }

    this.rawData.forEach((row) => {
      // Balita (0-4)
      stats.balita += Number.parseInt(row["00_04"] || 0)

      // Anak (5-14)
      stats.anak += Number.parseInt(row["05_09"] || 0) + Number.parseInt(row["10_14"] || 0)

      // Dewasa (15-59)
      stats.dewasa +=
        Number.parseInt(row["15_19"] || 0) +
        Number.parseInt(row["20_24"] || 0) +
        Number.parseInt(row["25_29"] || 0) +
        Number.parseInt(row["30_34"] || 0) +
        Number.parseInt(row["35_39"] || 0) +
        Number.parseInt(row["40_44"] || 0) +
        Number.parseInt(row["45_49"] || 0) +
        Number.parseInt(row["50_54"] || 0) +
        Number.parseInt(row["55_59"] || 0)

      // Lansia (60+)
      stats.lansia +=
        Number.parseInt(row["60_64"] || 0) +
        Number.parseInt(row["65_69"] || 0) +
        Number.parseInt(row["70_74"] || 0) +
        Number.parseInt(row[">75"] || 0)
    })

    stats.total = stats.balita + stats.anak + stats.dewasa + stats.lansia

    return stats
  }

  switchTab(tabId) {
    // Update active tab button
    document.querySelectorAll(".tab-button").forEach((btn) => {
      btn.classList.remove("active")
    })
    document.querySelector(`[data-tab="${tabId}"]`).classList.add("active")

    // Update active tab content
    document.querySelectorAll(".tab-content").forEach((content) => {
      content.classList.remove("active")
    })
    document.getElementById(`${tabId}-content`).classList.add("active")

    this.currentTab = tabId
    this.renderCurrentTab()
  }

  renderCurrentTab() {
    switch (this.currentTab) {
      case "kelompok_detail":
        this.renderKelompokDetailTab()
        break
      case "distribusi_wilayah":
        this.renderDistribusiWilayahTab()
        break
    }
  }

  renderKelompokDetailTab() {
    this.renderKelompokDetailStats()
    this.renderKelompokDetailCharts()
    this.renderKelompokDetailTable()
  }

  renderDistribusiWilayahTab() {
    this.renderDistribusiWilayahStats()
    this.renderDistribusiWilayahCharts()
    this.renderDistribusiWilayahTable()
  }

  renderKelompokDetailStats() {
    if (!this.statsData.detail) return

    const stats = this.statsData.detail
    const total = Object.values(stats).reduce((sum, val) => sum + val, 0)
    const container = document.getElementById("kelompok_detail-stats")

    container.innerHTML = `
            <div class="stat-card age-0-4">
                <h4>0-4 Tahun</h4>
                <div class="value">${formatNumber(stats["00_04"])}</div>
                <div class="percentage">${((stats["00_04"] / total) * 100).toFixed(1)}%</div>
            </div>
            <div class="stat-card age-5-9">
                <h4>5-9 Tahun</h4>
                <div class="value">${formatNumber(stats["05_09"])}</div>
                <div class="percentage">${((stats["05_09"] / total) * 100).toFixed(1)}%</div>
            </div>
            <div class="stat-card age-10-14">
                <h4>10-14 Tahun</h4>
                <div class="value">${formatNumber(stats["10_14"])}</div>
                <div class="percentage">${((stats["10_14"] / total) * 100).toFixed(1)}%</div>
            </div>
            <div class="stat-card age-15-19">
                <h4>15-19 Tahun</h4>
                <div class="value">${formatNumber(stats["15_19"])}</div>
                <div class="percentage">${((stats["15_19"] / total) * 100).toFixed(1)}%</div>
            </div>
        `
  }

  renderDistribusiWilayahStats() {
    if (!this.statsData.top_wilayah) return

    const topWilayah = this.statsData.top_wilayah
    const container = document.getElementById("distribusi_wilayah-stats")

    container.innerHTML = `
            <div class="stat-card top-balita">
                <h4>Balita Terbanyak</h4>
                <div class="value">${topWilayah.balita.wilayah}</div>
                <div class="count">${formatNumber(topWilayah.balita.count)} jiwa</div>
            </div>
            <div class="stat-card top-anak">
                <h4>Anak Terbanyak</h4>
                <div class="value">${topWilayah.anak.wilayah}</div>
                <div class="count">${formatNumber(topWilayah.anak.count)} jiwa</div>
            </div>
            <div class="stat-card top-dewasa">
                <h4>Dewasa Terbanyak</h4>
                <div class="value">${topWilayah.dewasa.wilayah}</div>
                <div class="count">${formatNumber(topWilayah.dewasa.count)} jiwa</div>
            </div>
            <div class="stat-card top-lansia">
                <h4>Lansia Terbanyak</h4>
                <div class="value">${topWilayah.lansia.wilayah}</div>
                <div class="count">${formatNumber(topWilayah.lansia.count)} jiwa</div>
            </div>
        `
  }

  renderKelompokDetailCharts() {
    this.renderBarChart("kelompok_detail-bar-chart")
    this.renderPieChart("kelompok_detail-pie-chart")
    this.renderPyramidChart("kelompok_detail-pyramid-chart")
  }

  renderDistribusiWilayahCharts() {
    this.renderComparisonChart("distribusi_wilayah-comparison-chart")
    this.renderCategoryChart("distribusi_wilayah-category-chart")
    this.renderHeatmapChart("distribusi_wilayah-heatmap-chart")
  }

  renderBarChart(canvasId) {
    const ctx = document.getElementById(canvasId)
    if (!ctx) return

    if (typeof window.Chart === "undefined") {
      console.error("Chart.js library not loaded")
      showError("Chart.js library tidak tersedia")
      return
    }

    // Destroy existing chart
    if (this.charts[canvasId]) {
      this.charts[canvasId].destroy()
    }

    const data = this.filteredData.slice(0, 10).map((row) => ({
      wilayah: row.WILAYAH,
      balita: Number.parseInt(row["00_04"] || 0),
      anak: Number.parseInt(row["05_09"] || 0) + Number.parseInt(row["10_14"] || 0),
      dewasa:
        Number.parseInt(row["15_19"] || 0) +
        Number.parseInt(row["20_24"] || 0) +
        Number.parseInt(row["25_29"] || 0) +
        Number.parseInt(row["30_34"] || 0) +
        Number.parseInt(row["35_39"] || 0) +
        Number.parseInt(row["40_44"] || 0) +
        Number.parseInt(row["45_49"] || 0) +
        Number.parseInt(row["50_54"] || 0) +
        Number.parseInt(row["55_59"] || 0),
      lansia:
        Number.parseInt(row["60_64"] || 0) +
        Number.parseInt(row["65_69"] || 0) +
        Number.parseInt(row["70_74"] || 0) +
        Number.parseInt(row[">75"] || 0),
    }))

    this.charts[canvasId] = new window.Chart(ctx, {
      type: "bar",
      data: {
        labels: data.map((d) => d.wilayah),
        datasets: [
          {
            label: "Balita (0-4)",
            data: data.map((d) => d.balita),
            backgroundColor: "#FF6B6B",
            borderWidth: 1,
          },
          {
            label: "Anak (5-14)",
            data: data.map((d) => d.anak),
            backgroundColor: "#4ECDC4",
            borderWidth: 1,
          },
          {
            label: "Dewasa (15-59)",
            data: data.map((d) => d.dewasa),
            backgroundColor: "#45B7D1",
            borderWidth: 1,
          },
          {
            label: "Lansia (60+)",
            data: data.map((d) => d.lansia),
            backgroundColor: "#96CEB4",
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
              callback: (value) => formatNumber(value),
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
              label: (context) => context.dataset.label + ": " + formatNumber(context.parsed.y),
            },
          },
        },
      },
    })
  }

  renderPieChart(canvasId) {
    const ctx = document.getElementById(canvasId)
    if (!ctx) return

    if (typeof window.Chart === "undefined") {
      console.error("Chart.js library not loaded")
      return
    }

    if (this.charts[canvasId]) {
      this.charts[canvasId].destroy()
    }

    const stats = this.calculateOverallStats()

    this.charts[canvasId] = new window.Chart(ctx, {
      type: "pie",
      data: {
        labels: ["Balita (0-4)", "Anak (5-14)", "Dewasa (15-59)", "Lansia (60+)"],
        datasets: [
          {
            data: [stats.balita, stats.anak, stats.dewasa, stats.lansia],
            backgroundColor: ["#FF6B6B", "#4ECDC4", "#45B7D1", "#96CEB4"],
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
                const total = context.dataset.data.reduce((a, b) => a + b, 0)
                const percentage = ((context.parsed / total) * 100).toFixed(1)
                return context.label + ": " + formatNumber(context.parsed) + " (" + percentage + "%)"
              },
            },
          },
        },
      },
    })
  }

  renderPyramidChart(canvasId) {
    const ctx = document.getElementById(canvasId)
    if (!ctx) return

    if (typeof window.Chart === "undefined") {
      console.error("Chart.js library not loaded")
      return
    }

    if (this.charts[canvasId]) {
      this.charts[canvasId].destroy()
    }

    if (!this.statsData.detail) return

    const stats = this.statsData.detail
    const ageGroups = [
      "00_04",
      "05_09",
      "10_14",
      "15_19",
      "20_24",
      "25_29",
      "30_34",
      "35_39",
      "40_44",
      "45_49",
      "50_54",
      "55_59",
      "60_64",
      "65_69",
      "70_74",
      ">75",
    ]
    const labels = [
      "0-4",
      "5-9",
      "10-14",
      "15-19",
      "20-24",
      "25-29",
      "30-34",
      "35-39",
      "40-44",
      "45-49",
      "50-54",
      "55-59",
      "60-64",
      "65-69",
      "70-74",
      "75+",
    ]

    this.charts[canvasId] = new window.Chart(ctx, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Jumlah Penduduk",
            data: ageGroups.map((group) => stats[group]),
            backgroundColor: "#45B7D1",
            borderColor: "#2E8BC0",
            borderWidth: 1,
          },
        ],
      },
      options: {
        indexAxis: "y",
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: {
            beginAtZero: true,
            ticks: {
              callback: (value) => formatNumber(value),
            },
          },
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: (context) => "Jumlah: " + formatNumber(context.parsed.x),
            },
          },
        },
      },
    })
  }

  renderComparisonChart(canvasId) {
    // Implementation for comparison chart
    this.renderBarChart(canvasId)
  }

  renderCategoryChart(canvasId) {
    // Implementation for category chart
    this.renderPieChart(canvasId)
  }

  renderHeatmapChart(canvasId) {
    // Implementation for heatmap chart (simplified as bar chart for now)
    this.renderBarChart(canvasId)
  }

  renderKelompokDetailTable() {
    this.renderTable("kelompok_detail-table", "detail")
  }

  renderDistribusiWilayahTable() {
    this.renderTable("distribusi_wilayah-table", "wilayah")
  }

  renderTable(containerId, type) {
    const container = document.getElementById(containerId)
    if (!container) return

    let headers, rows

    if (type === "detail") {
      headers = [
        "Wilayah",
        "0-4",
        "5-9",
        "10-14",
        "15-19",
        "20-24",
        "25-29",
        "30-34",
        "35-39",
        "40-44",
        "45-49",
        "50-54",
        "55-59",
        "60-64",
        "65-69",
        "70-74",
        "75+",
        "Total",
      ]
      rows = this.filteredData.map((row) => {
        const total = Object.keys(row).reduce((sum, key) => {
          if (key !== "KODE" && key !== "WILAYAH") {
            return sum + Number.parseInt(row[key] || 0)
          }
          return sum
        }, 0)

        return [
          row.WILAYAH,
          formatNumber(row["00_04"] || 0),
          formatNumber(row["05_09"] || 0),
          formatNumber(row["10_14"] || 0),
          formatNumber(row["15_19"] || 0),
          formatNumber(row["20_24"] || 0),
          formatNumber(row["25_29"] || 0),
          formatNumber(row["30_34"] || 0),
          formatNumber(row["35_39"] || 0),
          formatNumber(row["40_44"] || 0),
          formatNumber(row["45_49"] || 0),
          formatNumber(row["50_54"] || 0),
          formatNumber(row["55_59"] || 0),
          formatNumber(row["60_64"] || 0),
          formatNumber(row["65_69"] || 0),
          formatNumber(row["70_74"] || 0),
          formatNumber(row[">75"] || 0),
          formatNumber(total),
        ]
      })
    } else {
      headers = ["Wilayah", "Balita (0-4)", "Anak (5-14)", "Remaja (15-24)", "Dewasa (25-59)", "Lansia (60+)", "Total"]
      rows = this.filteredData.map((row) => {
        const balita = Number.parseInt(row["00_04"] || 0)
        const anak = Number.parseInt(row["05_09"] || 0) + Number.parseInt(row["10_14"] || 0)
        const remaja = Number.parseInt(row["15_19"] || 0) + Number.parseInt(row["20_24"] || 0)
        const dewasa =
          Number.parseInt(row["25_29"] || 0) +
          Number.parseInt(row["30_34"] || 0) +
          Number.parseInt(row["35_39"] || 0) +
          Number.parseInt(row["40_44"] || 0) +
          Number.parseInt(row["45_49"] || 0) +
          Number.parseInt(row["50_54"] || 0) +
          Number.parseInt(row["55_59"] || 0)
        const lansia =
          Number.parseInt(row["60_64"] || 0) +
          Number.parseInt(row["65_69"] || 0) +
          Number.parseInt(row["70_74"] || 0) +
          Number.parseInt(row[">75"] || 0)
        const total = balita + anak + remaja + dewasa + lansia

        return [
          row.WILAYAH,
          formatNumber(balita),
          formatNumber(anak),
          formatNumber(remaja),
          formatNumber(dewasa),
          formatNumber(lansia),
          formatNumber(total),
        ]
      })
    }

    const tableHTML = `
      <table>
        <thead>
          <tr>
            ${headers.map((header) => `<th>${header}</th>`).join("")}
          </tr>
        </thead>
        <tbody>
          ${rows.map((row) => `<tr>${row.map((cell) => `<td>${cell}</td>`).join("")}</tr>`).join("")}
        </tbody>
      </table>
    `

    container.innerHTML = tableHTML
  }

  handleSearch(tab) {
    const searchInput = document.getElementById(`${tab}-search`)
    const searchTerm = searchInput.value.toLowerCase()

    this.filteredData = this.rawData.filter((row) => row.WILAYAH.toLowerCase().includes(searchTerm))

    this.renderCurrentTab()
  }

  handleSort(tab) {
    const sortSelect = document.getElementById(`${tab}-sort`)
    const sortValue = sortSelect.value
    const [field, direction] = sortValue.split("_")

    this.filteredData.sort((a, b) => {
      let aVal, bVal

      if (field === "wilayah") {
        aVal = a.WILAYAH
        bVal = b.WILAYAH
      } else if (field === "total") {
        // Calculate total for each row
        aVal = Object.keys(a).reduce((sum, key) => {
          if (key !== "KODE" && key !== "WILAYAH") {
            return sum + Number.parseInt(a[key] || 0)
          }
          return sum
        }, 0)
        bVal = Object.keys(b).reduce((sum, key) => {
          if (key !== "KODE" && key !== "WILAYAH") {
            return sum + Number.parseInt(b[key] || 0)
          }
          return sum
        }, 0)
      } else if (field === "balita") {
        aVal = Number.parseInt(a["00_04"] || 0)
        bVal = Number.parseInt(b["00_04"] || 0)
      } else if (field === "anak") {
        aVal = Number.parseInt(a["05_09"] || 0) + Number.parseInt(a["10_14"] || 0)
        bVal = Number.parseInt(b["05_09"] || 0) + Number.parseInt(b["10_14"] || 0)
      } else if (field === "dewasa") {
        aVal =
          Number.parseInt(a["15_19"] || 0) +
          Number.parseInt(a["20_24"] || 0) +
          Number.parseInt(a["25_29"] || 0) +
          Number.parseInt(a["30_34"] || 0) +
          Number.parseInt(a["35_39"] || 0) +
          Number.parseInt(a["40_44"] || 0) +
          Number.parseInt(a["45_49"] || 0) +
          Number.parseInt(a["50_54"] || 0) +
          Number.parseInt(a["55_59"] || 0)
        bVal =
          Number.parseInt(b["15_19"] || 0) +
          Number.parseInt(b["20_24"] || 0) +
          Number.parseInt(b["25_29"] || 0) +
          Number.parseInt(b["30_34"] || 0) +
          Number.parseInt(b["35_39"] || 0) +
          Number.parseInt(b["40_44"] || 0) +
          Number.parseInt(b["45_49"] || 0) +
          Number.parseInt(b["50_54"] || 0) +
          Number.parseInt(b["55_59"] || 0)
      } else if (field === "lansia") {
        aVal =
          Number.parseInt(a["60_64"] || 0) +
          Number.parseInt(a["65_69"] || 0) +
          Number.parseInt(a["70_74"] || 0) +
          Number.parseInt(a[">75"] || 0)
        bVal =
          Number.parseInt(b["60_64"] || 0) +
          Number.parseInt(b["65_69"] || 0) +
          Number.parseInt(b["70_74"] || 0) +
          Number.parseInt(b[">75"] || 0)
      } else {
        // Handle numeric fields (age groups)
        const fieldKey = field.replace("_", "_")
        aVal = Number.parseInt(a[fieldKey] || 0)
        bVal = Number.parseInt(b[fieldKey] || 0)
      }

      if (direction === "desc") {
        return bVal > aVal ? 1 : -1
      } else {
        return aVal > bVal ? 1 : -1
      }
    })

    this.renderCurrentTab()
  }

  exportData() {
    if (this.filteredData.length === 0) {
      showError("Tidak ada data untuk diekspor")
      return
    }

    const headers = [
      "Kode",
      "Wilayah",
      "0-4",
      "5-9",
      "10-14",
      "15-19",
      "20-24",
      "25-29",
      "30-34",
      "35-39",
      "40-44",
      "45-49",
      "50-54",
      "55-59",
      "60-64",
      "65-69",
      "70-74",
      "75+",
    ]
    const csvContent = [
      headers.join(","),
      ...this.filteredData.map((row) =>
        [
          row.KODE,
          `"${row.WILAYAH}"`,
          row["00_04"] || 0,
          row["05_09"] || 0,
          row["10_14"] || 0,
          row["15_19"] || 0,
          row["20_24"] || 0,
          row["25_29"] || 0,
          row["30_34"] || 0,
          row["35_39"] || 0,
          row["40_44"] || 0,
          row["45_49"] || 0,
          row["50_54"] || 0,
          row["55_59"] || 0,
          row["60_64"] || 0,
          row["65_69"] || 0,
          row["70_74"] || 0,
          row[">75"] || 0,
        ].join(","),
      ),
    ].join("\n")

    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" })
    const link = document.createElement("a")
    const url = URL.createObjectURL(blob)
    link.setAttribute("href", url)
    link.setAttribute("download", `kelompok_umur_${new Date().toISOString().split("T")[0]}.csv`)
    link.style.visibility = "hidden"
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)

    showSuccess("Data berhasil diekspor")
  }
}

// Initialize dashboard when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  window.kelompokUmurDashboard = new KelompokUmurDashboard()
})
