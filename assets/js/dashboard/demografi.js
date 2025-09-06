// assets/js/dashboard/demografi.js
// Demografi page specific logic

class DemografiDashboard {
    constructor() {
        // Use chart colors from DashboardCommon
        this.chartColors = {
            lakiLaki: window.DashboardCommon.chartColors.primary,    // Blue
            perempuan: window.DashboardCommon.chartColors.pink       // Pink
        };

        this.currentData = [];
        this.allProvinces = [];
        this.barChart = null;
        this.pieChart = null;
        
        this.init();
    }

    init() {
        // Setup filter event listeners
        window.DashboardCommon.setupFilterEventListeners(() => this.loadData());
        
        // Initial data load
        this.loadData();
        
        // Listen for global refresh events
        window.addEventListener('dataRefresh', () => this.loadData());
        
        // Setup export button
        const exportBtn = document.getElementById('exportBtn');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.exportData());
        }
    }

    async loadData() {
        try {
            // Show loading state
            const loadingElements = ['statLakiLaki', 'statPerempuan', 'statTotal'];
            window.DashboardCommon.showLoading(loadingElements);

            // Get current filter values
            const filters = window.DashboardCommon.getCurrentFilters();

            // API call to demografi endpoint
            const result = await window.API.getDemografiData(filters);

            if (result.success) {
                this.currentData = result.data || [];
                this.allProvinces = result.provinces || [];
                
                // Update UI components
                this.updateStats(result.stats);
                window.DashboardCommon.populateProvinceFilter(this.allProvinces);
                window.DashboardCommon.updateDataCounter(this.currentData);
                this.populateDataTable();
                
                // Create charts with slight delay for smooth transition
                setTimeout(() => this.createCharts(), 100);
                
                // Show success notification
                if (window.mainApp && window.mainApp.showNotification) {
                    window.mainApp.showNotification(
                        `Data berhasil dimuat: ${this.currentData.length} wilayah`, 
                        'success', 
                        3000
                    );
                }
                
                console.log('Demografi data loaded:', this.currentData.length, 'items');
            } else {
                throw new Error(result.error?.message || 'Gagal memuat data');
            }

        } catch (error) {
            console.error('Error loading demografi data:', error);
            
            // Show error notification
            if (window.mainApp && window.mainApp.showNotification) {
                window.mainApp.showNotification(
                    error.message || 'Gagal memuat data demografi', 
                    'error'
                );
            }
            
            // Update UI error state
            window.DashboardCommon.showError(
                ['statLakiLaki', 'statPerempuan', 'statTotal'],
                'Error'
            );
        }
    }

    updateStats(stats) {
        if (!stats) {
            console.warn('No stats data received');
            return;
        }

        // Define stat card mappings for demografi
        const cardMappings = [
            ['statLakiLaki', 'total_laki_laki'],
            ['statPerempuan', 'total_perempuan'],
            ['statTotal', 'grand_total']
        ];
        
        // Use common function to update stats
        window.DashboardCommon.updateStatsCards(stats, cardMappings);
    }

    createCharts() {
        if (typeof Chart === 'undefined') {
            console.error('Chart.js not loaded');
            return;
        }
        
        this.createBarChart();
        this.createPieChart();
    }

    createBarChart() {
        const canvas = document.getElementById('barChart');
        if (!canvas) {
            console.warn('Bar chart canvas not found');
            return;
        }
        
        // Destroy existing chart
        this.barChart = window.DashboardCommon.destroyChart(this.barChart);
        
        // Prepare data (limit to 15 for readability)
        const chartData = this.currentData.slice(0, 15);
        
        if (chartData.length === 0) {
            window.DashboardCommon.showNoDataChart(canvas);
            return;
        }

        try {
            const ctx = canvas.getContext('2d');
            
            this.barChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.map(item => {
                        const cleanName = item.wilayah
                            .replace(/^(KAB\.|KOTA|KABUPATEN|PROVINSI)\s*/i, '');
                        return window.Utils ? 
                            window.Utils.truncateText(cleanName, 25) : 
                            cleanName.substring(0, 25) + (cleanName.length > 25 ? '...' : '');
                    }),
                    datasets: [
                        {
                            label: 'Laki-laki',
                            data: chartData.map(item => item.laki_laki || 0),
                            backgroundColor: this.chartColors.lakiLaki,
                            borderRadius: 4
                        },
                        {
                            label: 'Perempuan',
                            data: chartData.map(item => item.perempuan || 0),
                            backgroundColor: this.chartColors.perempuan,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    ...window.DashboardCommon.chartDefaults,
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 0,
                                font: { size: 10 }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f3f4' },
                            ticks: {
                                callback: function(value) {
                                    return window.Utils ? 
                                        window.Utils.formatNumber(value) : 
                                        value.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: { 
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            ...window.DashboardCommon.chartDefaults.plugins.tooltip,
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    const formattedValue = window.Utils ? 
                                        window.Utils.formatNumber(context.parsed.y) : 
                                        context.parsed.y.toLocaleString('id-ID');
                                    return `${context.dataset.label}: ${formattedValue}`;
                                }
                            }
                        }
                    }
                }
            });

            console.log('Bar chart created successfully');
        } catch (error) {
            console.error('Error creating bar chart:', error);
        }
    }

    createPieChart() {
        const canvas = document.getElementById('pieChart');
        if (!canvas) {
            console.warn('Pie chart canvas not found');
            return;
        }
        
        // Destroy existing chart
        this.pieChart = window.DashboardCommon.destroyChart(this.pieChart);
        
        // Calculate totals for pie chart
        const totals = this.currentData.reduce((acc, item) => {
            acc.lakiLaki += item.laki_laki || 0;
            acc.perempuan += item.perempuan || 0;
            return acc;
        }, { lakiLaki: 0, perempuan: 0 });

        if (Object.values(totals).every(val => val === 0)) {
            window.DashboardCommon.showNoDataChart(canvas);
            return;
        }

        try {
            const ctx = canvas.getContext('2d');
            
            this.pieChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Laki-laki', 'Perempuan'],
                    datasets: [{
                        data: [totals.lakiLaki, totals.perempuan],
                        backgroundColor: [
                            this.chartColors.lakiLaki,
                            this.chartColors.perempuan
                        ],
                        borderWidth: 3,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    ...window.DashboardCommon.chartDefaults,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: { size: 12 },
                                padding: 15
                            }
                        },
                        tooltip: {
                            ...window.DashboardCommon.chartDefaults.plugins.tooltip,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed * 100) / total).toFixed(1);
                                    const formattedValue = window.Utils ? 
                                        window.Utils.formatNumber(context.parsed) : 
                                        context.parsed.toLocaleString('id-ID');
                                    return `${context.label}: ${formattedValue} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });

            console.log('Pie chart created successfully');
        } catch (error) {
            console.error('Error creating pie chart:', error);
        }
    }

    populateDataTable() {
        const tableBody = document.getElementById('dataTableBody');
        if (!tableBody) return;

        tableBody.innerHTML = '';

        if (this.currentData.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center">Tidak ada data</td></tr>';
            return;
        }

        this.currentData.forEach(item => {
            const total = item.total || (item.laki_laki + item.perempuan);
            const percentLaki = total > 0 ? ((item.laki_laki / total) * 100).toFixed(1) : 0;
            const percentPerempuan = total > 0 ? ((item.perempuan / total) * 100).toFixed(1) : 0;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.kode}</td>
                <td>${item.wilayah}</td>
                <td>${window.Utils ? window.Utils.formatNumber(item.laki_laki) : item.laki_laki.toLocaleString('id-ID')}</td>
                <td>${window.Utils ? window.Utils.formatNumber(item.perempuan) : item.perempuan.toLocaleString('id-ID')}</td>
                <td><strong>${window.Utils ? window.Utils.formatNumber(total) : total.toLocaleString('id-ID')}</strong></td>
                <td><span class="badge bg-primary">${percentLaki}%</span></td>
                <td><span class="badge bg-success">${percentPerempuan}%</span></td>
            `;
            tableBody.appendChild(row);
        });
    }

    // Public method to refresh data
    refresh() {
        this.loadData();
    }

    // Public method to export data
    exportData() {
        if (window.Utils && window.Utils.exportToCSV) {
            // Prepare data for export
            const exportData = this.currentData.map(item => ({
                'Kode': item.kode,
                'Wilayah': item.wilayah,
                'Laki-laki': item.laki_laki,
                'Perempuan': item.perempuan,
                'Total': item.total || (item.laki_laki + item.perempuan),
                'Persen_Laki_laki': item.total > 0 ? ((item.laki_laki / (item.total || (item.laki_laki + item.perempuan))) * 100).toFixed(1) + '%' : '0%',
                'Persen_Perempuan': item.total > 0 ? ((item.perempuan / (item.total || (item.laki_laki + item.perempuan))) * 100).toFixed(1) + '%' : '0%'
            }));
            
            window.Utils.exportToCSV(exportData, 'data-demografi');
        } else {
            console.warn('Export utility not available');
            alert('Fitur export belum tersedia');
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const initDemografi = () => {
        // Wait for dependencies
        if (window.DashboardCommon && window.API) {
            try {
                const dashboard = new DemografiDashboard();
                
                // Make instance available globally for debugging
                window.demografiDashboard = dashboard;
                
                console.log('Demografi dashboard initialized');
            } catch (error) {
                console.error('Error initializing demografi dashboard:', error);
            }
        } else {
            console.log('Waiting for dependencies...');
            setTimeout(initDemografi, 100);
        }
    };
    
    initDemografi();
});