// assets/js/dashboard/common.js
window.DashboardCommon = {
    setupFilterEventListeners(loadDataFunction) {
        const regionTypeFilter = document.getElementById('regionTypeFilter');
        const provinceFilter = document.getElementById('provinceFilter');
        const sortFilter = document.getElementById('sortFilter');
        const refreshBtn = document.getElementById('refreshBtn');
        
        if (regionTypeFilter) regionTypeFilter.addEventListener('change', loadDataFunction);
        if (provinceFilter) provinceFilter.addEventListener('change', loadDataFunction);
        if (sortFilter) sortFilter.addEventListener('change', loadDataFunction);
        if (refreshBtn) refreshBtn.addEventListener('click', loadDataFunction);
    },

    populateProvinceFilter(allProvinces) {
        const select = document.getElementById('provinceFilter');
        if (!select) return;
        
        const currentValue = select.value;
        select.innerHTML = '<option value="all">Semua Provinsi</option>';
        
        allProvinces.forEach(province => {
            const option = document.createElement('option');
            option.value = province.kode;
            option.textContent = province.wilayah;
            select.appendChild(option);
        });
        
        if (currentValue && [...select.options].some(opt => opt.value === currentValue)) {
            select.value = currentValue;
        }
    },

    updateDataCounter(currentData) {
        const counterElement = document.getElementById('dataCount');
        if (counterElement && currentData) {
            counterElement.textContent = currentData.length;
        }
    },

    showLoading(elementIds) {
        const spinnerHTML = '<div class="loading-spinner"></div>';
        elementIds.forEach(id => {
            const element = document.getElementById(id);
            if (element) element.innerHTML = spinnerHTML;
        });
    },

    showError(elementIds) {
        elementIds.forEach(id => {
            const element = document.getElementById(id);
            if (element) element.textContent = 'Error';
        });
        
        const counterElement = document.getElementById('dataCount');
        if (counterElement) counterElement.textContent = '0';
    },

    destroyChart(chartInstance) {
        if (chartInstance) {
            chartInstance.destroy();
            return null;
        }
        return null;
    }
};