// assets/js/sidebar.js
// Global sidebar and navigation functions

window.Sidebar = {
    // Current active page
    currentPage: null,

    // Initialize sidebar
    init() {
        this.setActivePage();
        this.setupEventListeners();
        this.setupMobileToggle();
        console.log('Sidebar initialized');
    },

    // Set active page based on current URL
    setActivePage(pageId = null) {
        // If pageId not provided, detect from URL
        if (!pageId) {
            const currentPath = window.location.pathname;
            
            // Map URLs to page IDs
            const pageMapping = {
                '/dashboard/akta': 'nav-akta',
                '/dashboard/demografi': 'nav-demografi', 
                '/dashboard/disabilitas': 'nav-disabilitas',
                '/dashboard/kelompok-umur': 'nav-kelompok-umur',
                '/dashboard/kepala-keluarga': 'nav-kepala-keluarga',
                '/tools/ai': 'nav-ai'
            };
            
            // Find matching page
            for (const [path, id] of Object.entries(pageMapping)) {
                if (currentPath.includes(path)) {
                    pageId = id;
                    break;
                }
            }
        }

        // Remove active class from all nav items
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
            link.parentElement?.classList.remove('active');
        });

        // Add active class to current page
        if (pageId) {
            const activeElement = document.getElementById(pageId);
            if (activeElement) {
                activeElement.classList.add('active');
                activeElement.parentElement?.classList.add('active');
                this.currentPage = pageId;
                
                // Update breadcrumb if function exists
                this.updateBreadcrumb();
            }
        }
    },

    // Update breadcrumb navigation
    updateBreadcrumb() {
        const breadcrumbContainer = document.getElementById('breadcrumb');
        if (!breadcrumbContainer) return;

        const pageInfo = this.getPageInfo(this.currentPage);
        if (!pageInfo) return;

        // Create breadcrumb items
        const breadcrumbHTML = `
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="/dashboard/akta">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    ${pageInfo.category ? `
                        <li class="breadcrumb-item">
                            <span>${pageInfo.category}</span>
                        </li>
                    ` : ''}
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="${pageInfo.icon}"></i> ${pageInfo.title}
                    </li>
                </ol>
            </nav>
        `;

        breadcrumbContainer.innerHTML = breadcrumbHTML;
    },

    // Get page information for breadcrumb
    getPageInfo(pageId) {
        const pageInfoMap = {
            'nav-akta': {
                title: 'Akta Kelahiran',
                category: 'Data Analytics Kependudukan',
                icon: 'fas fa-file-alt'
            },
            'nav-demografi': {
                title: 'Demografi',
                category: 'Data Analytics Kependudukan', 
                icon: 'fas fa-users'
            },
            'nav-disabilitas': {
                title: 'Disabilitas',
                category: 'Data Analytics Kependudukan',
                icon: 'fas fa-wheelchair'
            },
            'nav-kelompok-umur': {
                title: 'Kelompok Umur',
                category: 'Data Analytics Kependudukan',
                icon: 'fas fa-calendar-alt'
            },
            'nav-kepala-keluarga': {
                title: 'Kepala Keluarga',
                category: 'Data Analytics Kependudukan',
                icon: 'fas fa-user'
            },
            'nav-ai': {
                title: 'AI Assistant',
                category: 'Tools',
                icon: 'fas fa-robot'
            }
        };

        return pageInfoMap[pageId] || null;
    },

    // Setup event listeners for navigation
    setupEventListeners() {
        // Handle navigation clicks
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                
                // If it's a valid internal link, update active state
                if (href && !href.startsWith('http') && !href.includes('#')) {
                    // Small delay to allow navigation to complete
                    setTimeout(() => {
                        this.setActivePage();
                    }, 50);
                }
            });
        });

        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.setActivePage();
            }
        });

        // Handle browser back/forward
        window.addEventListener('popstate', () => {
            this.setActivePage();
        });
    },

    // Setup mobile sidebar toggle
    setupMobileToggle() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                this.toggleSidebar();
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                this.closeSidebar();
            });
        }

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeSidebar();
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                this.closeSidebar();
            }
        });
    },

    // Toggle sidebar for mobile
    toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (sidebar) {
            const isOpen = sidebar.classList.contains('show');
            
            if (isOpen) {
                this.closeSidebar();
            } else {
                this.openSidebar();
            }
        }
    },

    // Open sidebar
    openSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (sidebar) {
            sidebar.classList.add('show');
            document.body.classList.add('sidebar-open');
        }
        
        if (overlay) {
            overlay.classList.add('show');
        }
    },

    // Close sidebar
    closeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (sidebar) {
            sidebar.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        }
        
        if (overlay) {
            overlay.classList.remove('show');
        }
    },

    // Collapse/expand sidebar sections
    toggleSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.classList.toggle('collapsed');
            
            // Save state to localStorage
            const isCollapsed = section.classList.contains('collapsed');
            localStorage.setItem(`sidebar-${sectionId}`, isCollapsed ? 'collapsed' : 'expanded');
        }
    },

    // Restore sidebar section states
    restoreSectionStates() {
        const sections = ['dashboard-section', 'tools-section'];
        
        sections.forEach(sectionId => {
            const savedState = localStorage.getItem(`sidebar-${sectionId}`);
            const section = document.getElementById(sectionId);
            
            if (savedState === 'collapsed' && section) {
                section.classList.add('collapsed');
            }
        });
    },

    // Highlight active navigation item
    highlightNavItem(itemId) {
        // Remove previous highlights
        document.querySelectorAll('.nav-link.highlight').forEach(item => {
            item.classList.remove('highlight');
        });

        // Add highlight to specified item
        const item = document.getElementById(itemId);
        if (item) {
            item.classList.add('highlight');
            
            // Remove highlight after animation
            setTimeout(() => {
                item.classList.remove('highlight');
            }, 2000);
        }
    },

    // Update page title
    updatePageTitle(title) {
        if (title) {
            document.title = `${title} - Data Analytics Kependudukan`;
            
            // Update page header if exists
            const pageHeader = document.getElementById('pageTitle');
            if (pageHeader) {
                pageHeader.textContent = title;
            }
        }
    },

    // Get current page information
    getCurrentPageInfo() {
        return this.getPageInfo(this.currentPage);
    }
};

// Initialize sidebar when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Small delay to ensure all elements are rendered
    setTimeout(() => {
        window.Sidebar.init();
        window.Sidebar.restoreSectionStates();
    }, 100);
});

// Make functions available globally for backward compatibility
window.setActivePage = (pageId) => window.Sidebar.setActivePage(pageId);
window.updateBreadcrumb = () => window.Sidebar.updateBreadcrumb();
window.toggleSidebar = () => window.Sidebar.toggleSidebar();