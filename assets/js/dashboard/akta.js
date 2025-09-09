// assets/js/dashboard/akta.js
// Akta page specific logic

class AktaDashboard {
  constructor() {
    // Chart colors for different akta types
    this.chartColors = {
      cerai: window.DashboardCommon.chartColors.danger,
      lahir: window.DashboardCommon.chartColors.success,
      mati: window.DashboardCommon.chartColors.secondary,
      wajib: window.DashboardCommon.chartColors.warning,
      memiliki: window.DashboardCommon.chartColors.success,
      belumMemiliki: window.DashboardCommon.chartColors.danger,
      lakiLaki: window.DashboardCommon.chartColors.primary,
      perempuan: window.DashboardCommon.chartColors.pink,
    }

    this.currentData = {}
    this.currentStats = {}
    this.allProvinces = []
    this.activeTab = "akta_cerai"
    this.charts = {}
    this.pagination = {
      currentPage: 1,
      itemsPerPage: 25,
      totalItems: 0,
    }

    this.filters = {
      search: "",
      sort: "wilayah_asc",
    }

    this.init()
  }

  init() {
    // Setup filter event listeners
    window.DashboardCommon.setupFilterEventListeners(() => this.loadData())

    // Setup tab event listeners
    this.setupTabEventListeners()

    // Setup akta type filter
    this.setupAktaTypeFilter()

    this.setupSearchAndSortListeners()

    // Initial data load
    this.loadData()

    // Listen for global refresh events
    window.addEventListener("dataRefresh", () => this.loadData())
  }

  setupTabEventListeners() {
    const tabButtons = document.querySelectorAll(".tab-button")
    tabButtons.forEach((button) => {
      button.addEventListener("click", (e) => {
        e.preventDefault()
        const tabType = button.dataset.tab
        this.switchTab(tabType)
      })
    })
  }

  setupAktaTypeFilter() {
    const aktaTypeFilter = document.getElementById("aktaTypeFilter")
    if (aktaTypeFilter) {
      aktaTypeFilter.addEventListener("change", () => this.loadData())
    }
  }

  setupSearchAndSortListeners() {
    const tabs = ["akta_cerai", "akta_lahir", "akta_mati"]

    tabs.forEach((tab) => {
      // Search input
      const searchInput = document.getElementById(`${tab}-search`)
      if (searchInput) {
        searchInput.addEventListener("input", (e) => {
          this.filters.search = e.target.value.toLowerCase()
          this.pagination.currentPage = 1 // Reset to first page
          this.updateDataTable()
          this.updateDataCounter() // Update counter when search changes
        })
      }

      // Sort select
      const sortSelect = document.getElementById(`${tab}-sort`)
      if (sortSelect) {
        sortSelect.addEventListener("change", (e) => {
          this.filters.sort = e.target.value
          this.pagination.currentPage = 1 // Reset to first page
          this.updateDataTable()
        })
      }

      const refreshBtn = document.getElementById(`${tab}-refresh`)
      if (refreshBtn) {
        refreshBtn.addEventListener("click", (e) => {
          e.preventDefault()
          this.loadData()
        })
      }
    })
  }

  switchTab(tabType) {
    // Update active tab
    this.activeTab = tabType

    // Update tab buttons
    document.querySelectorAll(".tab-button").forEach((btn) => {
      btn.classList.remove("active")
    })
    document.querySelector(`[data-tab="${tabType}"]`).classList.add("active")

    // Update tab content
    document.querySelectorAll(".tab-content").forEach((content) => {
      content.classList.remove("active")
      content.style.display = "none"
    })

    // Show only the active tab
    const activeTabContent = document.getElementById(`${tabType}-content`)
    if (activeTabContent) {
      activeTabContent.style.display = "block"
      // Use setTimeout to ensure display change takes effect before adding active class
      setTimeout(() => {
        activeTabContent.classList.add("active")
      }, 10)
    }

    this.filters.search = ""
    this.filters.sort = "wilayah_asc"
    this.pagination.currentPage = 1

    // Update search and sort controls for new tab
    const searchInput = document.getElementById(`${tabType}-search`)
    const sortSelect = document.getElementById(`${tabType}-sort`)

    if (searchInput) searchInput.value = ""
    if (sortSelect) sortSelect.value = "wilayah_asc"

    // Update stats and charts for active tab
    this.updateTabContent()
  }

  async loadData() {
    try {
      // Show loading state
      const loadingElements = ["statCerai", "statLahir", "statMati", "statTotal"]
      window.DashboardCommon.showLoading(loadingElements)

      // Get current filter values
      const filters = this.getCurrentFilters()

      // API call
      const result = await window.API.getAktaData(filters)

      if (result.success) {
        this.currentData = result.data || {}
        this.currentStats = result.stats || {}
        this.allProvinces = result.provinces || []

        this.removeDuplicateData()

        // Update UI components
        this.updateOverallStats()
        window.DashboardCommon.populateProvinceFilter(this.allProvinces)
        this.updateDataCounter()

        // Update active tab content
        this.updateTabContent()

        // Show success notification
        if (window.mainApp && window.mainApp.showNotification) {
          const activeTabData = this.currentData[this.activeTab] || []
          const activeTabCount = Array.isArray(activeTabData) ? activeTabData.length : 0
          window.mainApp.showNotification(
            `Data berhasil dimuat: ${activeTabCount} record untuk ${this.activeTab.replace("_", " ")}`,
            "success",
            3000,
          )
        }

        console.log("Akta data loaded:", this.currentData)
      } else {
        throw new Error(result.error?.message || "Gagal memuat data")
      }
    } catch (error) {
      console.error("Error loading akta data:", error)

      // Show error notification
      if (window.mainApp && window.mainApp.showNotification) {
        window.mainApp.showNotification(error.message || "Gagal memuat data akta", "error")
      }

      // Update UI error state
      window.DashboardCommon.showError(["statCerai", "statLahir", "statMati", "statTotal"], "Error")
    }
  }

  removeDuplicateData() {
    Object.keys(this.currentData).forEach((tabKey) => {
      if (Array.isArray(this.currentData[tabKey])) {
        const uniqueData = []
        const seenCodes = new Set()

        this.currentData[tabKey].forEach((item) => {
          if (!seenCodes.has(item.kode)) {
            seenCodes.add(item.kode)
            uniqueData.push(item)
          }
        })

        this.currentData[tabKey] = uniqueData
      }
    })
  }

  getCurrentFilters() {
    const baseFilters = window.DashboardCommon.getCurrentFilters()
    const aktaTypeFilter = document.getElementById("aktaTypeFilter")

    return {
      ...baseFilters,
      akta_type: aktaTypeFilter ? aktaTypeFilter.value : "all",
    }
  }

  updateOverallStats() {
    if (!this.currentStats) return

    // Calculate overall statistics
    let totalCerai = 0,
      totalLahir = 0,
      totalMati = 0,
      grandTotal = 0

    if (this.currentStats.akta_cerai) {
      totalCerai = Number.parseInt(this.currentStats.akta_cerai.total_memiliki) || 0
    }
    if (this.currentStats.akta_lahir) {
      totalLahir = Number.parseInt(this.currentStats.akta_lahir.total_memiliki) || 0
    }
    if (this.currentStats.akta_mati) {
      totalMati = Number.parseInt(this.currentStats.akta_mati.grand_total) || 0
    }

    grandTotal = totalCerai + totalLahir + totalMati

    // Update stat cards
    const cardMappings = [
      ["statCerai", totalCerai],
      ["statLahir", totalLahir],
      ["statMati", totalMati],
      ["statTotal", grandTotal],
    ]

    cardMappings.forEach(([elementId, value]) => {
      const element = document.getElementById(elementId)
      if (element) {
        element.style.opacity = "0.5"
        setTimeout(() => {
          element.textContent = value.toLocaleString("id-ID")
          element.style.opacity = "1"
        }, 100)
      }
    })
  }

  updateDataCounter() {
    const counterElement = document.getElementById("dataCount")
    if (counterElement) {
      const activeTabData = this.currentData[this.activeTab]
      const filteredData = this.getFilteredAndSortedData(activeTabData)
      const activeTabCount = filteredData.length
      counterElement.textContent = activeTabCount.toLocaleString("id-ID")
    }
  }

  updateTabContent() {
    // Update stats for active tab
    this.updateTabStats()

    setTimeout(() => this.createRealCharts(), 150)

    // Update data table
    this.updateDataTable()
  }

  updateTabStats() {
    const tabStats = this.currentStats[this.activeTab]
    if (!tabStats) return

    const tabStatsContainer = document.getElementById(`${this.activeTab}-stats`)
    if (!tabStatsContainer) return

    // Clear existing stats
    tabStatsContainer.innerHTML = ""

    if (this.activeTab === "akta_mati") {
      // Akta mati has different structure
      tabStatsContainer.innerHTML = `
                <div class="stat-card lakiLaki">
                    <h3>Laki-laki</h3>
                    <div class="value">${(Number.parseInt(tabStats.total_laki_laki) || 0).toLocaleString("id-ID")}</div>
                </div>
                <div class="stat-card perempuan">
                    <h3>Perempuan</h3>
                    <div class="value">${(Number.parseInt(tabStats.total_perempuan) || 0).toLocaleString("id-ID")}</div>
                </div>
                <div class="stat-card total">
                    <h3>Total</h3>
                    <div class="value">${(Number.parseInt(tabStats.grand_total) || 0).toLocaleString("id-ID")}</div>
                </div>
            `
    } else {
      // Akta cerai and lahir have similar structure
      const totalWajib = Number.parseInt(tabStats.total_wajib) || 0
      const totalMemiliki = Number.parseInt(tabStats.total_memiliki) || 0
      const totalBelumMemiliki = Number.parseInt(tabStats.total_belum_memiliki) || 0
      const persentase = totalWajib > 0 ? ((totalMemiliki / totalWajib) * 100).toFixed(1) : 0

      tabStatsContainer.innerHTML = `
                <div class="stat-card wajib">
                    <h3>Wajib Akta</h3>
                    <div class="value">${totalWajib.toLocaleString("id-ID")}</div>
                </div>
                <div class="stat-card memiliki">
                    <h3>Memiliki Akta</h3>
                    <div class="value">${totalMemiliki.toLocaleString("id-ID")}</div>
                </div>
                <div class="stat-card belum-memiliki">
                    <h3>Belum Memiliki</h3>
                    <div class="value">${totalBelumMemiliki.toLocaleString("id-ID")}</div>
                </div>
                <div class="stat-card total">
                    <h3>Persentase Kepemilikan</h3>
                    <div class="value">${persentase}%</div>
                </div>
            `
    }
  }

  createRealCharts() {
    if (typeof window.Chart === "undefined") {
      console.error("Chart.js not loaded")
      return
    }

    // Destroy existing charts
    Object.keys(this.charts).forEach((key) => {
      this.charts[key] = window.DashboardCommon.destroyChart(this.charts[key])
    })

    const tabData = this.currentData[this.activeTab]
    if (!Array.isArray(tabData) || tabData.length === 0) {
      this.showNoDataMessage()
      return
    }

    this.createWilayahDistributionChart(tabData)
    this.createKepemilikanOverviewChart(tabData)
  }

  createWilayahDistributionChart(data) {
    const canvas = document.getElementById(`${this.activeTab}-bar-chart`)
    if (!canvas) return

    const ctx = canvas.getContext("2d")

    let chartData = [...data]

    if (this.activeTab === "akta_mati") {
      // Sort by total (laki-laki + perempuan) descending
      chartData.sort((a, b) => {
        const totalA = (a.laki_laki || 0) + (a.perempuan || 0)
        const totalB = (b.laki_laki || 0) + (b.perempuan || 0)
        return totalB - totalA
      })
    } else {
      // Sort by total (memiliki + belum_memiliki) descending for akta_cerai and akta_lahir
      chartData.sort((a, b) => {
        const totalA = (a.memiliki || 0) + (a.belum_memiliki || 0)
        const totalB = (b.memiliki || 0) + (b.belum_memiliki || 0)
        return totalB - totalA
      })
    }

    chartData = chartData.slice(0, 10) // Show top 10 regions with highest totals

    let datasets

    if (this.activeTab === "akta_mati") {
      datasets = [
        {
          label: "Laki-laki",
          data: chartData.map((item) => item.laki_laki || 0),
          backgroundColor: this.chartColors.lakiLaki,
          borderRadius: 4,
        },
        {
          label: "Perempuan",
          data: chartData.map((item) => item.perempuan || 0),
          backgroundColor: this.chartColors.perempuan,
          borderRadius: 4,
        },
      ]
    } else {
      datasets = [
        {
          label: "Memiliki Akta",
          data: chartData.map((item) => item.memiliki || 0),
          backgroundColor: this.chartColors.memiliki,
          borderRadius: 4,
        },
        {
          label: "Belum Memiliki",
          data: chartData.map((item) => item.belum_memiliki || 0),
          backgroundColor: this.chartColors.belumMemiliki,
          borderRadius: 4,
        },
      ]
    }

    this.charts[`${this.activeTab}_bar`] = new window.Chart(ctx, {
      type: "bar",
      data: {
        labels: chartData.map((item) => {
          const cleanName = item.wilayah.replace(/^(KAB\.|KOTA|KABUPATEN|PROVINSI)\s*/i, "")
          return cleanName.length > 15 ? cleanName.substring(0, 15) + "..." : cleanName
        }),
        datasets: datasets,
      },
      options: {
        ...window.DashboardCommon.chartDefaults,
        scales: {
          x: {
            stacked: true,
            grid: { display: false },
            ticks: {
              maxRotation: 45,
              minRotation: 0,
              font: { size: 10 },
            },
          },
          y: {
            stacked: true,
            beginAtZero: true,
            grid: { color: "#f1f3f4" },
            ticks: {
              callback: (value) => value.toLocaleString("id-ID"),
            },
          },
        },
        plugins: {
          legend: {
            display: true,
            position: "top",
          },
          tooltip: {
            ...window.DashboardCommon.chartDefaults.plugins.tooltip,
            callbacks: {
              label: (context) => `${context.dataset.label}: ${context.parsed.y.toLocaleString("id-ID")}`,
            },
          },
        },
      },
    })
  }

  createKepemilikanOverviewChart(data) {
    const canvas = document.getElementById(`${this.activeTab}-pie-chart`)
    if (!canvas) return

    const ctx = canvas.getContext("2d")

    const filteredData = this.getFilteredAndSortedData(data)

    let pieData, pieLabels, pieColors

    if (this.activeTab === "akta_mati") {
      const totals = filteredData.reduce(
        (acc, item) => {
          acc.lakiLaki += item.laki_laki || 0
          acc.perempuan += item.perempuan || 0
          return acc
        },
        { lakiLaki: 0, perempuan: 0 },
      )

      pieData = [totals.lakiLaki, totals.perempuan]
      pieLabels = ["Laki-laki", "Perempuan"]
      pieColors = [this.chartColors.lakiLaki, this.chartColors.perempuan]
    } else {
      const totals = filteredData.reduce(
        (acc, item) => {
          acc.memiliki += item.memiliki || 0
          acc.belumMemiliki += item.belum_memiliki || 0
          return acc
        },
        { memiliki: 0, belumMemiliki: 0 },
      )

      pieData = [totals.memiliki, totals.belumMemiliki]
      pieLabels = ["Memiliki Akta", "Belum Memiliki"]
      pieColors = [this.chartColors.memiliki, this.chartColors.belumMemiliki]
    }

    this.charts[`${this.activeTab}_pie`] = new window.Chart(ctx, {
      type: "doughnut",
      data: {
        labels: pieLabels,
        datasets: [
          {
            data: pieData,
            backgroundColor: pieColors,
            borderWidth: 3,
            borderColor: "#fff",
          },
        ],
      },
      options: {
        ...window.DashboardCommon.chartDefaults,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              usePointStyle: true,
              pointStyle: "circle",
              font: { size: 12 },
              padding: 15,
            },
          },
          tooltip: {
            ...window.DashboardCommon.chartDefaults.plugins.tooltip,
            callbacks: {
              label: (context) => {
                const total = context.dataset.data.reduce((a, b) => a + b, 0)
                const percentage = ((context.parsed * 100) / total).toFixed(2).replace(".", ",")
                return `${context.label}: ${context.parsed.toLocaleString("id-ID")} (${percentage}%)`
              },
            },
          },
        },
        cutout: "60%",
      },
    })
  }

  showNoDataMessage() {
    const chartIds = [`${this.activeTab}-bar-chart`, `${this.activeTab}-pie-chart`]

    chartIds.forEach((chartId) => {
      const canvas = document.getElementById(chartId)
      if (canvas) {
        window.DashboardCommon.showNoDataChart(canvas)
      }
    })
  }

  updateDataTable() {
    const tableContainer = document.getElementById(`${this.activeTab}-table`)
    if (!tableContainer) return

    const tabData = this.currentData[this.activeTab]
    if (!Array.isArray(tabData) || tabData.length === 0) {
      tableContainer.innerHTML = '<p class="text-center">Tidak ada data untuk ditampilkan</p>'
      return
    }

    const filteredData = this.getFilteredAndSortedData(tabData)

    this.pagination.totalItems = filteredData.length
    const totalPages = Math.ceil(this.pagination.totalItems / this.pagination.itemsPerPage)
    const startIndex = (this.pagination.currentPage - 1) * this.pagination.itemsPerPage
    const endIndex = startIndex + this.pagination.itemsPerPage
    const paginatedData = filteredData.slice(startIndex, endIndex)

    let tableHTML = `
      <div class="table-controls">
        <div class="per-page-selector">
          <label for="itemsPerPage">Tampilkan:</label>
          <select id="itemsPerPage" onchange="window.aktaDashboard.changeItemsPerPage(this.value)">
            <option value="10" ${this.pagination.itemsPerPage === 10 ? "selected" : ""}>10 data</option>
            <option value="25" ${this.pagination.itemsPerPage === 25 ? "selected" : ""}>25 data</option>
            <option value="50" ${this.pagination.itemsPerPage === 50 ? "selected" : ""}>50 data</option>
            <option value="100" ${this.pagination.itemsPerPage === 100 ? "selected" : ""}>100 data</option>
          </select>
        </div>
        <div class="pagination-info">
          Menampilkan ${startIndex + 1}-${Math.min(endIndex, this.pagination.totalItems)} dari ${this.pagination.totalItems} data
        </div>
      </div>
    `

    tableHTML += '<table class="data-table"><thead><tr>'

    if (this.activeTab === "akta_mati") {
      tableHTML +=
        '<th>Kode Wilayah</th><th>Wilayah</th><th class="number">Laki-laki</th><th class="number">Perempuan</th><th class="number">Total</th>'
    } else {
      tableHTML +=
        '<th>Kode Wilayah</th><th>Wilayah</th><th class="number">Wajib</th><th class="number">Memiliki</th><th class="number">Belum Memiliki</th><th class="percentage">Persentase</th>'
    }

    tableHTML += "</tr></thead><tbody>"

    paginatedData.forEach((item) => {
      tableHTML += "<tr>"
      tableHTML += `<td>${item.kode || "-"}</td>`
      tableHTML += `<td>${item.wilayah}</td>`

      if (this.activeTab === "akta_mati") {
        tableHTML += `<td class="number">${(item.laki_laki || 0).toLocaleString("id-ID")}</td>`
        tableHTML += `<td class="number">${(item.perempuan || 0).toLocaleString("id-ID")}</td>`
        tableHTML += `<td class="number">${(item.total || 0).toLocaleString("id-ID")}</td>`
      } else {
        let persentase = item.persentase || "0,00"

        // If persentase is a number, convert to string with proper formatting
        if (typeof persentase === "number") {
          persentase = persentase.toString().replace(".", ",")
        }

        // Ensure it's a string and handle decimal formatting
        persentase = String(persentase).replace(".", ",")

        let persentaseClass = "low"
        const numPersentase = Number.parseFloat(String(persentase).replace(",", "."))
        if (numPersentase >= 80) persentaseClass = "high"
        else if (numPersentase >= 60) persentaseClass = "medium"

        tableHTML += `<td class="number">${(item.wajib || 0).toLocaleString("id-ID")}</td>`
        tableHTML += `<td class="number">${(item.memiliki || 0).toLocaleString("id-ID")}</td>`
        tableHTML += `<td class="number">${(item.belum_memiliki || 0).toLocaleString("id-ID")}</td>`
        tableHTML += `<td class="percentage ${persentaseClass}">${persentase}%</td>`
      }

      tableHTML += "</tr>"
    })

    tableHTML += "</tbody></table>"

    if (totalPages > 1) {
      tableHTML += '<div class="pagination-controls">'

      // Previous button
      if (this.pagination.currentPage > 1) {
        tableHTML += `<button class="pagination-btn" onclick="window.aktaDashboard.goToPage(${this.pagination.currentPage - 1})">
          <i class="fas fa-chevron-left"></i> Sebelumnya
        </button>`
      }

      // Page numbers
      const maxVisiblePages = 5
      let startPage = Math.max(1, this.pagination.currentPage - Math.floor(maxVisiblePages / 2))
      const endPage = Math.min(totalPages, startPage + maxVisiblePages - 1)

      if (endPage - startPage + 1 < maxVisiblePages) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1)
      }

      for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === this.pagination.currentPage ? "active" : ""
        tableHTML += `<button class="pagination-btn page-number ${activeClass}" onclick="window.aktaDashboard.goToPage(${i})">${i}</button>`
      }

      // Next button
      if (this.pagination.currentPage < totalPages) {
        tableHTML += `<button class="pagination-btn" onclick="window.aktaDashboard.goToPage(${this.pagination.currentPage + 1})">
          Selanjutnya <i class="fas fa-chevron-right"></i>
        </button>`
      }

      tableHTML += "</div>"
    }

    tableContainer.innerHTML = tableHTML
  }

  getFilteredAndSortedData(data) {
    if (!Array.isArray(data)) return []

    let filteredData = [...data]

    // Apply search filter
    if (this.filters.search) {
      filteredData = filteredData.filter((item) => item.wilayah.toLowerCase().includes(this.filters.search))
    }

    filteredData.sort((a, b) => {
      const [field, order] = this.filters.sort.split("_")
      let aVal, bVal

      switch (field) {
        case "kode":
          aVal = Number.parseFloat(a.kode) || 0
          bVal = Number.parseFloat(b.kode) || 0
          break
        case "wilayah":
          aVal = a.wilayah || ""
          bVal = b.wilayah || ""
          break
        case "wajib":
          aVal = a.wajib || 0
          bVal = b.wajib || 0
          break
        case "memiliki":
          aVal = a.memiliki || 0
          bVal = b.memiliki || 0
          break
        case "belum":
          aVal = a.belum_memiliki || 0
          bVal = b.belum_memiliki || 0
          break
        case "persentase":
          aVal = a.persentase || 0
          bVal = b.persentase || 0
          break
        case "laki":
          aVal = a.laki_laki || 0
          bVal = b.laki_laki || 0
          break
        case "perempuan":
          aVal = a.perempuan || 0
          bVal = b.perempuan || 0
          break
        case "total":
          aVal = a.total || 0
          bVal = b.total || 0
          break
        default:
          aVal = a.wilayah || ""
          bVal = b.wilayah || ""
      }

      if (typeof aVal === "string") {
        return order === "asc" ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal)
      } else {
        return order === "asc" ? aVal - bVal : bVal - aVal
      }
    })

    return filteredData
  }

  // Public methods
  refresh() {
    this.loadData()
  }

  exportData() {
    const activeData = this.currentData[this.activeTab]
    if (window.Utils && window.Utils.exportToCSV && activeData) {
      window.Utils.exportToCSV(activeData, `data-${this.activeTab}`)
    } else {
      console.warn("Export utility not available or no data")
    }
  }

  changeItemsPerPage(newItemsPerPage) {
    this.pagination.itemsPerPage = Number.parseInt(newItemsPerPage)
    this.pagination.currentPage = 1 // Reset to first page
    this.updateDataTable()
  }

  goToPage(pageNumber) {
    const totalPages = Math.ceil(this.pagination.totalItems / this.pagination.itemsPerPage)
    if (pageNumber >= 1 && pageNumber <= totalPages) {
      this.pagination.currentPage = pageNumber
      this.updateDataTable()
    }
  }
}

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  const initAkta = () => {
    // Wait for dependencies
    if (window.DashboardCommon && window.API) {
      try {
        const dashboard = new AktaDashboard()

        // Make instance available globally
        window.aktaDashboard = dashboard

        console.log("Akta dashboard initialized")
      } catch (error) {
        console.error("Error initializing akta dashboard:", error)
      }
    } else {
      console.log("Waiting for dependencies...")
      setTimeout(initAkta, 100)
    }
  }

  initAkta()
})

// Add API method for akta data
if (window.API) {
  window.API.getAktaData = async function (filters = {}) {
    try {
      const params = new URLSearchParams(filters)
      const response = await fetch(`${this.baseURL}akta.php?${params}`)

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      return await response.json()
    } catch (error) {
      console.error("API Error:", error)
      return {
        success: false,
        error: { message: error.message },
      }
    }
  }
}
