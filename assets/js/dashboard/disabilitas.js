// assets/js/dashboard/disabilitas.js
// Disabilitas page specific logic

class DisabilitasDashboard {
    constructor() {
        // Use chart colors from DashboardCommon
        this.chartColors = {
            fisik: window.DashboardCommon.chartColors.primary,
            netra: window.DashboardCommon.chartColors.success,
            rungu: window.DashboardCommon.chartColors.warning,
            mental: window.DashboardCommon.chartColors.purple,
            fisikMental: window.DashboardCommon.chartColors.pink,
            lainnya: window.DashboardCommon.chartColors.danger
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
    }

    async loadData() {
        try {
            // Show loading state
            const loadingElements = ['statFisik', 'statNetra', 'statRungu', 'statTotal'];
            window.DashboardCommon.showLoading(loadingElements);

            // Get current filter values
            const filters = window.DashboardCommon.getCurrentFilters();

            // API call
            const result = await window.API.getDisabilitasData(filters);

            if (result.success) {
                this.currentData = result.data || [];
                this.allProvinces = result.provinces || [];
                
                // Update UI components
                this.updateStats(result.stats);
                window.DashboardCommon.populateProvinceFilter(this.allProvinces);
                window.DashboardCommon.updateDataCounter(this.currentData);
                
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
                
                console.log('Disabilitas data loaded:', this.currentData.length, 'items');
            } else {
                throw new Error(result.error?.message || 'Gagal memuat data');
            }

        } catch (error) {
            console.error('Error loading disabilitas data:', error);
            
            // Show error notification
            if (window.mainApp && window.mainApp.showNotification) {
                window.mainApp.showNotification(
                    error.message || 'Gagal memuat data disabilitas', 
                    'error'
                );
            }
            
            // Update UI error state
            window.DashboardCommon.showError(
                ['statFisik', 'statNetra', 'statRungu', 'statTotal'],
                'Error'
            );
        }
    }

    updateStats(stats) {
        if (!stats) {
            console.warn('No stats data received');
            return;
        }

        // Define stat card mappings
        const cardMappings = [
            ['statFisik', 'total_fisik'],
            ['statNetra', 'total_netra'],
            ['statRungu', 'total_rungu'],
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
                            label: 'Disabilitas Fisik',
                            data: chartData.map(item => item.fisik || 0),
                            backgroundColor: this.chartColors.fisik,
                            borderRadius: 4
                        },
                        {
                            label: 'Disabilitas Netra',
                            data: chartData.map(item => item.netra || 0),
                            backgroundColor: this.chartColors.netra,
                            borderRadius: 4
                        },
                        {
                            label: 'Disabilitas Rungu/Wicara',
                            data: chartData.map(item => item.rungu || 0),
                            backgroundColor: this.chartColors.rungu,
                            borderRadius: 4
                        },
                        {
                            label: 'Disabilitas Mental',
                            data: chartData.map(item => item.mental || 0),
                            backgroundColor: this.chartColors.mental,
                            borderRadius: 4
                        },
                        {
                            label: 'Disabilitas Fisik & Mental',
                            data: chartData.map(item => item.fisik_mental || 0),
                            backgroundColor: this.chartColors.fisikMental,
                            borderRadius: 4
                        },
                        {
                            label: 'Disabilitas Lainnya',
                            data: chartData.map(item => item.lainnya || 0),
                            backgroundColor: this.chartColors.lainnya,
                            borderRadius: 4
                        }
                    ]
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
                                font: { size: 10 }
                            }
                        },
                        y: {
                            stacked: true,
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
                        legend: { display: false },
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
        
        // Calculate totals
        const totals = this.currentData.reduce((acc, item) => {
            acc.fisik += item.fisik || 0;
            acc.netra += item.netra || 0;
            acc.rungu += item.rungu || 0;
            acc.mental += item.mental || 0;
            acc.fisikMental += item.fisik_mental || 0;
            acc.lainnya += item.lainnya || 0;
            return acc;
        }, { fisik: 0, netra: 0, rungu: 0, mental: 0, fisikMental: 0, lainnya: 0 });

        if (Object.values(totals).every(val => val === 0)) {
            window.DashboardCommon.showNoDataChart(canvas);
            return;
        }

        try {
            const ctx = canvas.getContext('2d');
            
            this.pieChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: [
                        'Disabilitas Fisik',
                        'Disabilitas Netra',
                        'Disabilitas Rungu/Wicara',
                        'Disabilitas Mental',
                        'Disabilitas Fisik & Mental',
                        'Disabilitas Lainnya'
                    ],
                    datasets: [{
                        data: [
                            totals.fisik,
                            totals.netra,
                            totals.rungu,
                            totals.mental,
                            totals.fisikMental,
                            totals.lainnya
                        ],
                        backgroundColor: [
                            this.chartColors.fisik,
                            this.chartColors.netra,
                            this.chartColors.rungu,
                            this.chartColors.mental,
                            this.chartColors.fisikMental,
                            this.chartColors.lainnya
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

    // Public method to refresh data
    refresh() {
        this.loadData();
    }

    // Public method to export data
    exportData() {
        if (window.Utils && window.Utils.exportToCSV) {
            window.Utils.exportToCSV(this.currentData, 'data-disabilitas');
        } else {
            console.warn('Export utility not available');
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const initDisabilitas = () => {
        // Wait for dependencies
        if (window.DashboardCommon && window.API) {
            try {
                const dashboard = new DisabilitasDashboard();
                
                // Make instance available globally for debugging
                window.disabilitasDashboard = dashboard;
                
                console.log('Disabilitas dashboard initialized');
            } catch (error) {
                console.error('Error initializing disabilitas dashboard:', error);
            }
        } else {
            console.log('Waiting for dependencies...');
            setTimeout(initDisabilitas, 100);
        }
    };
    
    initDisabilitas();
});