<?php require_once __DIR__ . '/../config/db.php'; requireLogin(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin' ?> - Gourmet Bites</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <link href="../css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <div class="logo"><i class="bi bi-shop"></i></div>
                <span class="fw-bold text-white">Admin Panel</span>
            </div>
            <ul class="nav flex-column">
                <li><a href="dashboard.php" class="nav-link <?= $activePage == 'dashboard' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="categories.php" class="nav-link <?= $activePage == 'categories' ? 'active' : '' ?>"><i class="bi bi-grid"></i> Categories</a></li>
                <li><a href="menu.php" class="nav-link <?= $activePage == 'menu' ? 'active' : '' ?>"><i class="bi bi-menu-button-wide"></i> Menu Items</a></li>
                <li><a href="orders.php" class="nav-link <?= $activePage == 'orders' ? 'active' : '' ?>"><i class="bi bi-bag-check"></i> Orders</a></li>
                <li><a href="qr.php" class="nav-link <?= $activePage == 'qr' ? 'active' : '' ?>"><i class="bi bi-qr-code"></i> QR Codes</a></li>
                <li><a href="analytics.php" class="nav-link <?= $activePage == 'analytics' ? 'active' : '' ?>"><i class="bi bi-graph-up"></i> Analytics</a></li>
                <li><a href="settings.php" class="nav-link <?= $activePage == 'settings' ? 'active' : '' ?>"><i class="bi bi-gear"></i> Settings</a></li>
            </ul>
            <div class="sidebar-footer">
                <div class="d-flex align-items-center gap-2 px-3 mb-2">
                    <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <span class="text-white fw-bold small"><?= substr($_SESSION['admin_name'], 0, 1) ?></span>
                    </div>
                    <div class="text-white small">
                        <div class="fw-medium"><?= $_SESSION['admin_name'] ?></div>
                        <div class="text-white-50" style="font-size:11px;"><?= $_SESSION['admin_role'] ?></div>
                    </div>
                </div>
                <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left"></i> Sign Out</a>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <header class="topbar">
                <button class="btn btn-sm btn-light d-lg-none" id="sidebarToggle"><i class="bi bi-list"></i></button>
                <h5 class="mb-0 fw-bold"><?= $pageTitle ?? 'Dashboard' ?></h5>
                <div></div>
            </header>
            <div class="content-area">
                <?php if (hasFlash()): $flash = getFlash(); ?>
                <div class="alert alert-<?= array_keys($flash)[0] ?> alert-dismissible fade show">
                    <?= array_values($flash)[0] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
