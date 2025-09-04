<?php
// includes/sidebar.php

// Get current page from URL parameter, default to 'akta'
$current_page = isset($_GET['page']) ? $_GET['page'] : 'akta';

// Define menu items
$menu_items = [
    'akta' => [
        'icon' => 'fas fa-file-alt',
        'label' => 'Akta (Cerai/Lahir/Mati)',
        'active' => $current_page === 'akta'
    ],
    'demografi' => [
        'icon' => 'fas fa-users', 
        'label' => 'Demografi',
        'active' => $current_page === 'demografi'
    ],
    'disabilitas' => [
        'icon' => 'fas fa-heart',
        'label' => 'Disabilitas', 
        'active' => $current_page === 'disabilitas'
    ],
    'kelompok-umur' => [
        'icon' => 'fas fa-birthday-cake',
        'label' => 'Kelompok Umur',
        'active' => $current_page === 'kelompok-umur'
    ],
    'kepala-keluarga' => [
        'icon' => 'fas fa-user-friends',
        'label' => 'Kepala Keluarga',
        'active' => $current_page === 'kepala-keluarga'
    ]
];
?>

<div class="sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-chart-bar"></i>
            <span>Data Analytics</span>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <!-- Dashboard Home -->
        <div class="nav-section">
            <div class="section-title">Dashboard</div>
            <ul class="nav-list">
                <?php foreach ($menu_items as $page_key => $menu_item): ?>
                <li class="nav-item">
                    <a href="?page=<?php echo $page_key; ?>" 
                       class="nav-link <?php echo $menu_item['active'] ? 'active' : ''; ?>">
                        <i class="<?php echo $menu_item['icon']; ?>"></i>
                        <span class="nav-text"><?php echo $menu_item['label']; ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- AI Assistant Section -->
        <div class="nav-section">
            <div class="section-title">Tools</div>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="ai-assistant/" class="nav-link">
                        <i class="fas fa-robot"></i>
                        <span class="nav-text">AI Assistant</span>
                        <span class="nav-badge">Soon</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="footer-info">
            <div class="version">v1.0.0</div>
            <div class="copyright">
                <i class="fas fa-bolt"></i>
                Made in Bolt
            </div>
        </div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-toggle" id="mobileToggle">
        <i class="fas fa-bars"></i>
    </button>
</div>

<!-- Sidebar CSS -->
<style>
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 280px;
    height: 100vh;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

/* Header */
.sidebar-header {
    padding: 25px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.05);
}

.logo {
    display: flex;
    align-items: center;
    font-size: 20px;
    font-weight: 600;
    color: white;
}

.logo i {
    margin-right: 12px;
    color: #3498db;
    font-size: 24px;
}

/* Navigation */
.sidebar-nav {
    flex: 1;
    padding: 20px 0;
    overflow-y: auto;
}

.nav-section {
    margin-bottom: 30px;
}

.section-title {
    padding: 0 20px 10px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.5);
    letter-spacing: 1px;
}

.nav-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-item {
    margin-bottom: 2px;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 14px 20px;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
    position: relative;
}

.nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
    border-left-color: #3498db;
    transform: translateX(2px);
}

.nav-link.active {
    background-color: rgba(52, 152, 219, 0.2);
    color: white;
    border-left-color: #3498db;
    font-weight: 500;
}

.nav-link.active::before {
    content: '';
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    width: 6px;
    height: 6px;
    background-color: #3498db;
    border-radius: 50%;
}

.nav-link i {
    width: 20px;
    margin-right: 12px;
    text-align: center;
    font-size: 16px;
}

.nav-text {
    flex: 1;
    font-size: 14px;
}

.nav-badge {
    background: #e74c3c;
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    font-weight: 600;
}

/* Footer */
.sidebar-footer {
    padding: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.1);
}

.footer-info {
    text-align: center;
}

.version {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.6);
    margin-bottom: 5px;
}

.copyright {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.copyright i {
    color: #f39c12;
}

/* Mobile Toggle */
.mobile-toggle {
    display: none;
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1001;
    background: #3498db;
    color: white;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.mobile-toggle:hover {
    background: #2980b9;
    transform: scale(1.05);
}

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    .mobile-toggle {
        display: block;
    }
}

/* Smooth scrollbar for sidebar nav */
.sidebar-nav::-webkit-scrollbar {
    width: 6px;
}

.sidebar-nav::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}
</style>

<!-- Sidebar JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const mobileToggle = document.getElementById('mobileToggle');
    
    // Mobile menu toggle
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            const isClickInsideSidebar = sidebar.contains(e.target);
            const isClickOnToggle = mobileToggle && mobileToggle.contains(e.target);
            
            if (!isClickInsideSidebar && !isClickOnToggle) {
                sidebar.classList.remove('active');
            }
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
        }
    });
});
</script>