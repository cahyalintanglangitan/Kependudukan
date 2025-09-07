class KepalaKeluargaDashboard {
    constructor() {
        this.chartColors = {
            laki: window.DashboardCommon.chartColors.primary,
            perempuan: window.DashboardCommon.chartColors.pink,
            total: window.DashboardCommon.chartColors.success
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
        window.addEventListener('dataRefresh', () => this.loadData());
    }

    async loadData() {
        try {
            const filters = window.DashboardCommon.getCurrentFilters();
            const result = await window.API.getKepalaKeluargaData(filters);

            if (result.success) {
                this.currentData = result.data || [];
                this.allProvinces = result.provinces || [];

                this.updateStats(result.stats);
                window.DashboardCommon.populateProvinceFilter(this.allProvinces);
                window.DashboardCommon.updateDataCounter(this.currentData);

                setTimeout(() => this.createCharts(), 100);
            }
        } catch (e) {
            console.error('Error load kepala keluarga:', e);
        }
    }

    updateStats(stats) {
        const cardMappings = [
            ['statLaki', 'total_laki'],
            ['statPerempuan', 'total_perempuan'],
            ['statTotal', 'total']
        ];
        window.DashboardCommon.updateStatsCards(stats, cardMappings);
    }

    createCharts() {
        this.createBarChart();
        this.createPieChart();
    }

    createBarChart() {
        const canvas = document.getElementById('barChart');
        if (!canvas) return;

        this.barChart = window.DashboardCommon.destroyChart(this.barChart);
        const chartData = this.currentData.slice(0, 15);

        if (chartData.length === 0) {
            window.DashboardCommon.showNoDataChart(canvas);
            return;
        }

        const ctx = canvas.getContext('2d');
        this.barChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.map(item => item['NAMA WILAYAH']),
                datasets: [
                    {
                        label: 'Laki-laki',
                        data: chartData.map(item => item['LAKI-LAKI'] || 0),
                        backgroundColor: this.chartColors.laki
                    },
                    {
                        label: 'Perempuan',
                        data: chartData.map(item => item['PEREMPUAN'] || 0),
                        backgroundColor: this.chartColors.perempuan
                    }
                ]
            },
            options: window.DashboardCommon.chartDefaults
        });
    }

    createPieChart() {
        const canvas = document.getElementById('pieChart');
        if (!canvas) return;

        this.pieChart = window.DashboardCommon.destroyChart(this.pieChart);

        const totals = {
            laki: this.currentData.reduce((a, b) => a + (parseInt(b['LAKI-LAKI']) || 0), 0),
            perempuan: this.currentData.reduce((a, b) => a + (parseInt(b['PEREMPUAN']) || 0), 0)
        };

        const ctx = canvas.getContext('2d');
        this.pieChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Laki-laki', 'Perempuan'],
                datasets: [{
                    data: [totals.laki, totals.perempuan],
                    backgroundColor: [this.chartColors.laki, this.chartColors.perempuan]
                }]
            },
            options: window.DashboardCommon.chartDefaults
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.DashboardCommon && window.API) {
        window.kepalaKeluargaDashboard = new KepalaKeluargaDashboard();
    }
});
