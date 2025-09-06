<?php
$current_file = basename($_SERVER['PHP_SELF'], '.php');
$menu_items = [
    'akta' => [
        'icon' => 'fas fa-file-alt',
        'label' => 'Akta (Cerai/Lahir/Mati)',
        'active' => $current_file === 'akta'
    ],
    'demografi' => [
        'icon' => 'fas fa-users',
        'label' => 'Demografi',
        'active' => $current_file === 'demografi'
    ],
    'disabilitas' => [
        'icon' => 'fas fa-wheelchair',
        'label' => 'Disabilitas',
        'active' => $current_file === 'disabilitas'
    ],
    'kelompok-umur' => [
        'icon' => 'fas fa-calendar-alt',
        'label' => 'Kelompok Umur',
        'active' => $current_file === 'kelompok-umur'
    ],
    'kepala-keluarga' => [
        'icon' => 'fas fa-user-tie',
        'label' => 'Kepala Keluarga',
        'active' => $current_file === 'kepala-keluarga'
    ]
];
?>

<div id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-chart-pie logo-icon"></i>
        <span class="logo-text">Data Analytics Kependudukan</span>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <?php foreach ($menu_items as $page_key => $menu_item): ?>
                <li>
                    <a href="../dashboard/<?php echo $page_key; ?>.php"
                        id="nav-<?php echo $page_key; ?>"
                        class="nav-link <?php echo $menu_item['active'] ? 'active' : ''; ?>">
                        <i class="<?php echo $menu_item['icon']; ?>"></i>
                        <span><?php echo $menu_item['label']; ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="nav-section">
            <h4 class="section-title">Tools</h4>
            <a href="ai-assistant/" id="nav-ai" class="nav-link ai-link">
                <i class="fas fa-robot"></i>
                <span>AI Assistant</span>
                <span class="soon">Soon</span>
            </a>
        </div>
    </nav>
</div>

<style>
    /* Sidebar */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 270px;
        height: 100%;
        background: linear-gradient(160deg, #0f172a, #1e293b, #0f172a);
        color: #e2e8f0;
        display: flex;
        flex-direction: column;
        box-shadow: 6px 0 25px rgba(0, 0, 0, 0.4);
        font-family: 'Poppins', sans-serif;
        z-index: 1000;
        border-right: 1px solid rgba(255, 255, 255, 0.05);
        animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Header */
    .sidebar-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 24px;
        font-size: 20px;
        font-weight: 700;
        color: white;
        letter-spacing: .5px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .logo-icon {
        font-size: 26px;
        color: #38bdf8;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
            color: #38bdf8;
        }

        50% {
            transform: scale(1.15);
            color: #60a5fa;
        }
    }

    /* Navigation */
    .sidebar-nav {
        flex: 1;
        padding: 20px 0;
    }

    .nav-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 22px;
        margin: 6px 14px;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 500;
        color: #cbd5e1;
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
    }

    .nav-link i {
        font-size: 17px;
        width: 22px;
        text-align: center;
    }

    .nav-link:hover {
        background: rgba(56, 189, 248, 0.15);
        color: #f8fafc;
        transform: translateX(5px);
    }

    .nav-link.active {
        background: linear-gradient(90deg, #2563eb, #38bdf8);
        color: #fff;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(56, 189, 248, 0.4);
    }

    .nav-link.active i {
        color: #fff;
    }

    .section-title {
        font-size: 11px;
        text-transform: uppercase;
        margin: 24px 20px 10px;
        color: #64748b;
        letter-spacing: 1.5px;
        font-weight: 600;
    }

    /* AI Assistant */
    .ai-link {
        position: relative;
    }

    .soon {
        font-size: 10px;
        background: #ef4444;
        color: white;
        padding: 3px 7px;
        border-radius: 12px;
        margin-left: auto;
        font-weight: bold;
        box-shadow: 0 2px 6px rgba(239, 68, 68, 0.5);
    }
</style>