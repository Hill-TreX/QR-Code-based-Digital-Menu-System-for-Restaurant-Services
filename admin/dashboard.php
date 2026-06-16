<?php
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/../config/db.php';
requireLogin();
$conn = dbConnect();

// Stats
$totalOrders  = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$totalRevenue = $conn->query("SELECT COALESCE(SUM(total),0) as r FROM orders WHERE status='completed'")->fetch_assoc()['r'];
$totalItems = $conn->query("SELECT COUNT(*) as c FROM menu_items")->fetch_assoc()['c'];
$totalCategories = $conn->query("SELECT COUNT(*) as c FROM categories")->fetch_assoc()['c'];
$availableItems = $conn->query("SELECT COUNT(*) as c FROM menu_items WHERE is_available = 1")->fetch_assoc()['c'];
$unavailableItems = $conn->query("SELECT COUNT(*) as c FROM menu_items WHERE is_available = 0")->fetch_assoc()['c'];
$totalViews = $conn->query("SELECT COUNT(*) as c FROM menu_views")->fetch_assoc()['c'];
$todayViews = $conn->query("SELECT COUNT(*) as c FROM menu_views WHERE DATE(viewed_at) = CURDATE()")->fetch_assoc()['c'];
$totalQR = $conn->query("SELECT COUNT(*) as c FROM `tables` WHERE is_active = 1")->fetch_assoc()['c'];
$mostViewed = $conn->query("SELECT id, name, view_count, image FROM menu_items ORDER BY view_count DESC LIMIT 1")->fetch_assoc();
$recentUpdates = $conn->query("SELECT id, name, updated_at FROM menu_items ORDER BY updated_at DESC LIMIT 5");

// Recent orders for activity feed
$recentOrders = $conn->query("
    SELECT tracking_id, customer_name, table_number, table_uid, total, payment_method, created_at
    FROM orders
    ORDER BY created_at DESC
    LIMIT 8
");

include 'header.php';
?>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-danger bg-gradient text-white">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3 class="mb-0 fw-bold"><?= number_format($totalOrders) ?></h3>
                    <small>Total Orders</small>
                </div>
                <i class="bi bi-bag-check fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-success bg-gradient text-white">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3 class="mb-0 fw-bold">$<?= number_format($totalRevenue, 2) ?></h3>
                    <small>Total Revenue</small>
                </div>
                <i class="bi bi-cash-stack fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-primary bg-gradient text-white">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3 class="mb-0 fw-bold"><?= $totalItems ?></h3>
                    <small>Total Menu Items</small>
                </div>
                <i class="bi bi-menu-button-wide fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-success bg-gradient text-white">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3 class="mb-0 fw-bold"><?= $totalCategories ?></h3>
                    <small>Categories</small>
                </div>
                <i class="bi bi-grid fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-info bg-gradient text-white">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3 class="mb-0 fw-bold"><?= $availableItems ?></h3>
                    <small>Available</small>
                </div>
                <i class="bi bi-check-circle fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-warning bg-gradient text-white">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3 class="mb-0 fw-bold"><?= $unavailableItems ?></h3>
                    <small>Unavailable</small>
                </div>
                <i class="bi bi-x-circle fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-danger bg-gradient text-white">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3 class="mb-0 fw-bold"><?= $totalViews ?></h3>
                    <small>Total Views</small>
                </div>
                <i class="bi bi-eye fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-secondary bg-gradient text-white">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3 class="mb-0 fw-bold"><?= $todayViews ?></h3>
                    <small>Today's Views</small>
                </div>
                <i class="bi bi-calendar-check fs-2 opacity-50"></i>
            </div>
    </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-dark bg-gradient text-white">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3 class="mb-0 fw-bold"><?= $totalQR ?></h3>
                    <small>Active QR Codes</small>
                </div>
                <i class="bi bi-qr-code fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3 class="mb-0 fw-bold"><?= $conn->query("SELECT COUNT(DISTINCT ip_address) as c FROM menu_views")->fetch_assoc()['c'] ?></h3>
                    <small>Unique Visitors</small>
                </div>
                <i class="bi bi-people fs-2 opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Most Viewed -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h5 class="fw-bold mb-4"><i class="bi bi-trophy text-warning"></i> Most Viewed Item</h5>
                <?php if ($mostViewed): ?>
                <div class="d-flex gap-3">
                    <img src="../<?= $mostViewed['image'] ?>" class="rounded-3" style="width:100px;height:100px;object-fit:cover;">
                    <div>
                        <h5 class="fw-bold mb-1"><?= $mostViewed['name'] ?></h5>
                        <p class="text-muted mb-2"><i class="bi bi-eye"></i> <?= $mostViewed['view_count'] ?> views</p>
                        <a href="menu.php" class="btn btn-sm btn-outline-danger rounded-pill">View All Items</a>
                    </div>
                </div>
                <?php else: ?><p class="text-muted">No data yet.</p><?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Quick Actions</h5>
                <div class="list-group list-group-flush">
                    <a href="menu.php?action=add" class="list-group-item list-group-item-action px-0"><i class="bi bi-plus-circle text-danger"></i> Add Menu Item</a>
                    <a href="categories.php" class="list-group-item list-group-item-action px-0"><i class="bi bi-grid text-primary"></i> Manage Categories</a>
                    <a href="qr.php" class="list-group-item list-group-item-action px-0"><i class="bi bi-qr-code text-success"></i> Generate QR Code</a>
                    <a href="analytics.php" class="list-group-item list-group-item-action px-0"><i class="bi bi-graph-up text-warning"></i> View Analytics</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row g-4 mt-0">
    <!-- Recent Orders -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-bag-check text-danger"></i> Recent Orders</h5>
                    <a href="orders.php" class="btn btn-sm btn-outline-danger rounded-pill">View All</a>
                </div>
                <?php if ($recentOrders->num_rows === 0): ?>
                <p class="text-muted small">No orders yet.</p>
                <?php else: ?>
                <div class="d-flex flex-column gap-2">
                <?php while ($o = $recentOrders->fetch_assoc()):
                    $isPayNow = $o['payment_method'] === 'pay_now';
                ?>
                <div class="d-flex align-items-start gap-3 p-2 rounded-3 bg-light">
                    <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;">
                        <i class="bi bi-bag text-danger"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="fw-semibold"><?= htmlspecialchars($o['customer_name'] ?: 'Guest') ?></span>
                                <?php if ($o['table_number']): ?>
                                <span class="badge bg-danger ms-1">Table <?= htmlspecialchars($o['table_number']) ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="fw-bold text-danger small ms-2 flex-shrink-0">$<?= number_format($o['total'], 2) ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="font-monospace small text-muted"><?= htmlspecialchars($o['tracking_id']) ?></span>
                            <span class="badge <?= $isPayNow ? 'bg-success' : 'bg-secondary' ?> bg-opacity-75 rounded-pill" style="font-size:.65rem;">
                                <?= $isPayNow ? 'Pay Now' : 'Cash' ?>
                            </span>
                        </div>
                        <div class="text-muted" style="font-size:.72rem;"><?= date('d M Y, h:i A', strtotime($o['created_at'])) ?></div>
                    </div>
                </div>
                <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Menu Updates -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-3"><i class="bi bi-clock-history text-primary"></i> Menu Updates</h5>
                <?php if ($recentUpdates->num_rows === 0): ?>
                <p class="text-muted small">No updates yet.</p>
                <?php else: ?>
                <div class="d-flex flex-column gap-2">
                <?php while ($u = $recentUpdates->fetch_assoc()): ?>
                <div class="d-flex align-items-center gap-3 p-2 rounded-3 bg-light">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px;height:36px;">
                        <i class="bi bi-pencil text-primary small"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small"><?= htmlspecialchars($u['name']) ?></div>
                        <div class="text-muted" style="font-size:.72rem;"><?= date('d M Y, h:i A', strtotime($u['updated_at'])) ?></div>
                    </div>
                </div>
                <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
