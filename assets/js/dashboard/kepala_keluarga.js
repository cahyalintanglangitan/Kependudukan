// JavaScript untuk halaman Kepala Keluarga
let currentPage = 1;
let itemsPerPage = 10;
let totalItems = 0;
let currentFilters = {};

// Inisialisasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    loadKepalaKeluargaData();
    setupEventListeners();
    createCharts();
});

// Setup event listeners
function setupEventListeners() {
    // Filter change events
    document.getElementById('pilihProvinsi').addEventListener('change', applyFilters);
    document.getElementById('urutanBerdasarkan').addEventListener('change', applyFilters);
    
    // Refresh button
    document.getElementById('refreshData').addEventListener('click', function() {
        currentPage = 1;
        loadKepalaKeluargaData();
        updateStats();
    });
    
    // Pagination
    document.getElementById('prevPage').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            loadKepalaKeluargaData();
        }
    });
    
    document.getElementById('nextPage').addEventListener('click', function() {
        if (currentPage * itemsPerPage < totalItems) {
            currentPage++;
            loadKepalaKeluargaData();
        }
    });
    
    // Export button
    document.querySelector('.btn-export').addEventListener('click', exportData);
}

// Load data kepala keluarga
function loadKepalaKeluargaData() {
    const params = new URLSearchParams({
        page: currentPage,
        limit: itemsPerPage,
        ...currentFilters
    });
    
    fetch(`../../api/kepala_keluarga.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTable(data.data);
                updatePagination(data.total);
                totalItems = data.total;
            } else {
                console.error('Error loading data:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Render table
function renderTable(data) {
    const tbody = document.getElementById('kkTableBody');
    tbody.innerHTML = '';
    
    data.forEach((item, index) => {
        const row = document.createElement('tr');
        const startNumber = (currentPage - 1) * itemsPerPage + index + 1;
        
        row.innerHTML = `
            <td>${startNumber}</td>
            <td>${item.nama}</td>
            <td>${item.nik}</td>
            <td>${item.jenis_kelamin}</td>
            <td>${item.umur} tahun</td>
            <td>${item.pekerjaan}</td>
            <td>${item.alamat}</td>
            <td>${item.jumlah_anggota} orang</td>
            <td><span class="status-badge status-${item.status.toLowerCase()}">${item.status}</span></td>
        `;
        tbody.appendChild(row);
    });
}

// Update pagination
function updatePagination(total) {
    totalItems = total;
    const totalPages = Math.ceil(total / itemsPerPage);
    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentPage * itemsPerPage, total);
    
    document.getElementById('showingStart').textContent = startItem;
    document.getElementById('showingEnd').textContent = endItem;
    document.getElementById('totalData').textContent = total;
    document.getElementById('currentPage').textContent = currentPage;
    
    // Update button states
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = currentPage >= totalPages;
}

// Apply filters
function applyFilters() {
    currentFilters = {
        provinsi: document.getElementById('pilihProvinsi').value,
        urutan: document.getElementById('urutanBerdasarkan').value
    };
    
    currentPage = 1;
    loadKepalaKeluargaData();
}

// Update statistics
function updateStats() {
    fetch('../../api/kepala_keluarga_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector('.stat-card-blue .stat-number').textContent = 
                    new Intl.NumberFormat('id-ID').format(data.stats.total);
                document.querySelector('.stat-card-green .stat-number').textContent = 
                    new Intl.NumberFormat('id-ID').format(data.stats.laki_laki);
                document.querySelector('.stat-card-orange .stat-number').textContent = 
                    new Intl.NumberFormat('id-ID').format(data.stats.perempuan);
                
                document.getElementById('dataCount').textContent = data.stats.total;
            }
        })
        .catch(error => {
            console.error('Error updating stats:', error);
        });
}

// Create charts
function createCharts() {
    createKKChart();
    createGenderChart();
}

// Create KK distribution chart
function createKKChart() {
    fetch('../../api/kepala_keluarga_chart.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const ctx = document.getElementById('kkChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.chartData.labels,
                        datasets: [{
                            label: 'Jumlah Kepala Keluarga',
                            data: data.chartData.values,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error creating chart:', error);
        });
}

// Create gender distribution chart
function createGenderChart() {
    fetch('../../api/kepala_keluarga_gender.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const ctx = document.getElementById('genderChart').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: data.chartData.labels,
                        datasets: [{
                            data: data.chartData.values,
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.6)',
                                'rgba(255, 99, 132, 0.6)'
                            ],
                            borderColor: [
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 99, 132, 1)'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error creating gender chart:', error);
        });
}

// Export data
function exportData() {
    const params = new URLSearchParams(currentFilters);
    window.open(`../../api/kepala_keluarga_export.php?${params}`, '_blank');
}
