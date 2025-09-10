// assets/js/utils.js - Utility functions for the dashboard

// Global utility functions
window.Utils = {
  // Format number with Indonesian locale
  formatNumber: (num) => {
    if (num == null || isNaN(num)) return "0"
    return new Intl.NumberFormat("id-ID").format(num)
  },

  // Format percentage
  formatPercent: (num, decimals = 1) => {
    if (num == null || isNaN(num)) return "0%"
    return new Intl.NumberFormat("id-ID", {
      style: "percent",
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals,
    }).format(num / 100)
  },

  // Truncate text
  truncateText: (text, maxLength = 20) => {
    if (!text || text.length <= maxLength) return text
    return text.substring(0, maxLength) + "..."
  },

  // Generate random ID
  generateId: () => {
    return "_" + Math.random().toString(36).substr(2, 9)
  },

  // Deep clone object
  deepClone: (obj) => {
    return JSON.parse(JSON.stringify(obj))
  },
}

// Global formatting functions (for backward compatibility)
window.formatNumber = (num) => {
  if (num == null || isNaN(num)) return "0"
  return new Intl.NumberFormat("id-ID").format(num)
}

window.formatPercent = (num, decimals = 1) => {
  if (num == null || isNaN(num)) return "0%"
  return new Intl.NumberFormat("id-ID", {
    style: "percent",
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
  }).format(num / 100)
}

// Loading and error handling functions
window.showLoadingSpinner = () => {
  const spinner = document.getElementById("loadingSpinner")
  if (spinner) {
    spinner.style.display = "block"
  } else {
    // Create spinner if it doesn't exist
    const spinnerDiv = document.createElement("div")
    spinnerDiv.id = "loadingSpinner"
    spinnerDiv.innerHTML = `
            <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; background: rgba(255,255,255,0.9); padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 20px; height: 20px; border: 2px solid #f3f3f3; border-top: 2px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    <span>Memuat data...</span>
                </div>
            </div>
            <style>
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        `
    document.body.appendChild(spinnerDiv)
  }
}

window.hideLoadingSpinner = () => {
  const spinner = document.getElementById("loadingSpinner")
  if (spinner) {
    spinner.style.display = "none"
  }
}

window.showError = (message) => {
  // Remove existing error messages
  const existingErrors = document.querySelectorAll(".error-notification")
  existingErrors.forEach((error) => error.remove())

  const errorDiv = document.createElement("div")
  errorDiv.className = "error-notification"
  errorDiv.innerHTML = `
        <div style="position: fixed; top: 20px; right: 20px; z-index: 10000; background: #e74c3c; color: white; padding: 15px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); max-width: 400px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-exclamation-circle"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.parentElement.remove()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer; margin-left: auto;">×</button>
            </div>
        </div>
    `
  document.body.appendChild(errorDiv)

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (document.body.contains(errorDiv)) {
      errorDiv.remove()
    }
  }, 5000)
}

window.showSuccess = (message) => {
  const successDiv = document.createElement("div")
  successDiv.className = "success-notification"
  successDiv.innerHTML = `
        <div style="position: fixed; top: 20px; right: 20px; z-index: 10000; background: #27ae60; color: white; padding: 15px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); max-width: 400px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-check-circle"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.parentElement.remove()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer; margin-left: auto;">×</button>
            </div>
        </div>
    `
  document.body.appendChild(successDiv)

  // Auto remove after 3 seconds
  setTimeout(() => {
    if (document.body.contains(successDiv)) {
      successDiv.remove()
    }
  }, 3000)
}
