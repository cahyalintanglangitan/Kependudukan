// assets/js/dashboard/disabilitas.js
class DisabilitasDashboard {
    constructor() {
        this.chartColors = {
            fisik: window.CHART_COLORS.primary,
            netra: window.CHART_COLORS.success,
            rungu: window.CHART_COLORS.warning,
            mental: window.CHART_COLORS.purple,
            fisikMental: window.CHART_COLORS.pink,
            lainnya: window.CHART_COLORS.danger
        };
        this.currentData = [];
        this.allProvinces = [];
        this.barChart = null;
        this.pieChart = null;
        
        this.init();
    }

    init() {
        window.DashboardCommon.setupFilterEventListeners(() => this.loadData());
        this.loadData();
        
        // Listen for global refresh events
        window.addEventListener('dataRefresh', () => this.loadData());
    }

    async loadData() {
        try {
            const loadingElements = ['statFisik', 'statNetra', 'statRungu', 'statTotal'];
            window.DashboardCommon.showLoading(loadingElements);

            const filters = {
                region_type: document.getElementById('regionTypeFilter').value,
                province: document.getElementById('provinceFilter').value,
                sort_by: document.getElementById('sortFilter').value,
                limit: 20
            };

            const result = await window.API.getDisabilitasData(filters);

            if (result.success) {
                this.currentData = result.data;
                this.allProvinces = result.provinces || [];
                
                this.updateStats(result.stats);
                window.DashboardCommon.populateProvinceFilter(this.allProvinces);
                window.DashboardCommon.updateDataCounter(this.currentData);
                
                setTimeout(() => this.createCharts(), 100);
                
                window.mainApp.showNotification(`Data berhasil dimuat: ${this.currentData.length} wilayah`, 'success', 3000);
            } else {
                throw new Error(result.error?.message || 'Gagal memuat data');
            }

        } catch (error) {
            console.error('Error loading disabilitas data:', error);
            window.mainApp.showNotification(error.message || 'Gagal memuat data disabilitas', 'error');
            window.DashboardCommon.showError(['statFisik', 'statNetra', 'statRungu', 'statTotal']);
        }
    }

    updateStats(stats) {
        if (!stats) return;

        const updates = [
            ['statFisik', window.Utils.formatNumber(stats.total_fisik || 0)],
            ['statNetra', window.Utils.formatNumber(stats.total_netra || 0)],
            ['statRungu', window.Utils.formatNumber(stats.total_rungu || 0)],
            ['statTotal', window.Utils.formatNumber(stats.grand_total || 0)]
        ];
        
        updates.forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) element.textContent = value;
        });
    }

    createCharts() {
        if (typeof Chart === 'undefined') return;
        this.createBarChart();
        this.createPieChart();
    }

    createBarChart() {
        const canvas = document.getElementById('barChart');
        if (!canvas) return;
        
        this.barChart = window.DashboardCommon.destroyChart(this.barChart);
        
        const ctx = canvas.getContext('2d');
        const chartData = this.currentData.slice(0, 15);
        
        if (chartData.length === 0) {
            ctx.font = '16px Arial';
            ctx.fillStyle = '#666';
            ctx.textAlign = 'center';
            ctx.fillText('Tidak ada data untuk ditampilkan', canvas.width / 2, canvas.height / 2);
            return;
        }

        this.barChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.map(item => 
                    window.Utils.truncateText(
                        item.wilayah.replace(/^(KAB\.|KOTA|KABUPATEN|PROVINSI)\s*/i, ''),
                        25
                    )
                ),
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
                ...window.CHART_OPTIONS,
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { maxRotation: 45, minRotation: 0, font: { size: 10 } }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        grid: { color: '#f1f3f4' },
                        ticks: { callback: (value) => window.Utils.formatNumber(value) }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...window.CHART_OPTIONS.plugins.tooltip,
                        callbacks: {
                            label: (context) => `${context.dataset.label}: ${window.Utils.formatNumber(context.parsed.y)}`
                        }
                    }
                }
            }
        });
    }

    createPieChart() {
        const canvas = document.getElementById('pieChart');
        if (!canvas) return;
        
        this.pieChart = window.DashboardCommon.destroyChart(this.pieChart);
        
        const ctx = canvas.getContext('2d');
        
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
            ctx.font = '16px Arial';
            ctx.fillStyle = '#666';
            ctx.textAlign = 'center';
            ctx.fillText('Tidak ada data untuk ditampilkan', canvas.width / 2, canvas.height / 2);
            return;
        }

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
                ...window.CHART_OPTIONS,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, pointStyle: 'circle', font: { size: 12 }, padding: 15 }
                    },
                    tooltip: {
                        ...window.CHART_OPTIONS.plugins.tooltip,
                        callbacks: {
                            label: (context) => {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed * 100) / total).toFixed(1);
                                return `${context.label}: ${window.Utils.formatNumber(context.parsed)} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const initDisabilitas = () => {
        if (window.mainApp && window.API && window.DashboardCommon) {
            new DisabilitasDashboard();
        } else {
            setTimeout(initDisabilitas, 100);
        }
    };
    initDisabilitas();
});