<?php
$pageTitle = 'Analytics';
$activePage = 'analytics';
require_once __DIR__ . '/../config/db.php';
requireLogin();
$conn = dbConnect();

$days    = intval($_GET['days'] ?? 30);
$section = $_GET['section'] ?? 'orders';

// ── Views ─────────────────────────────────────────────────────────────────────
$totalViews    = $conn->query("SELECT COUNT(*) c FROM menu_views")->fetch_assoc()['c'];
$todayViews    = $conn->query("SELECT COUNT(*) c FROM menu_views WHERE DATE(viewed_at)=CURDATE()")->fetch_assoc()['c'];
$thisWeekViews = $conn->query("SELECT COUNT(*) c FROM menu_views WHERE viewed_at>=DATE_SUB(NOW(),INTERVAL 7 DAY)")->fetch_assoc()['c'];
$uniqueVisitors= $conn->query("SELECT COUNT(DISTINCT ip_address) c FROM menu_views")->fetch_assoc()['c'];

// ── Orders ────────────────────────────────────────────────────────────────────
$totalOrders    = $conn->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'];
$todayOrders    = $conn->query("SELECT COUNT(*) c FROM orders WHERE DATE(created_at)=CURDATE()")->fetch_assoc()['c'];
$thisWeekOrders = $conn->query("SELECT COUNT(*) c FROM orders WHERE created_at>=DATE_SUB(NOW(),INTERVAL 7 DAY)")->fetch_assoc()['c'];
$completedOrders= $conn->query("SELECT COUNT(*) c FROM orders WHERE status='completed'")->fetch_assoc()['c'];
$cancelledOrders= $conn->query("SELECT COUNT(*) c FROM orders WHERE status='cancelled'")->fetch_assoc()['c'];
$activeOrders   = $conn->query("SELECT COUNT(*) c FROM orders WHERE status IN ('pending','preparing','ready')")->fetch_assoc()['c'];

// Order status breakdown for progress bars
$statusBreakdown = [
    'Completed'  => ['success', $completedOrders],
    'Cancelled'  => ['danger',  $cancelledOrders],
    'Active'     => ['warning', $activeOrders],
];
$maxStatus = max(array_column($statusBreakdown, 1) ?: [1]);

// Recent orders for table
$recentOrdersRes = $conn->query("SELECT tracking_id, customer_name, table_number, total, status, payment_method, created_at FROM orders ORDER BY created_at DESC LIMIT 10");

// ── Revenue ───────────────────────────────────────────────────────────────────
$totalRevenue  = $conn->query("SELECT COALESCE(SUM(total),0) r FROM orders WHERE status='completed'")->fetch_assoc()['r'];
$todayRevenue  = $conn->query("SELECT COALESCE(SUM(total),0) r FROM orders WHERE status='completed' AND DATE(created_at)=CURDATE()")->fetch_assoc()['r'];
$weekRevenue   = $conn->query("SELECT COALESCE(SUM(total),0) r FROM orders WHERE status='completed' AND created_at>=DATE_SUB(NOW(),INTERVAL 7 DAY)")->fetch_assoc()['r'];
$pendingRevenue= $conn->query("SELECT COALESCE(SUM(total),0) r FROM orders WHERE status NOT IN ('completed','cancelled')")->fetch_assoc()['r'];

// Revenue by payment method for progress bars
$revPayNow     = $conn->query("SELECT COALESCE(SUM(total),0) r FROM orders WHERE status='completed' AND payment_method='pay_now'")->fetch_assoc()['r'];
$revOnReceipt  = $conn->query("SELECT COALESCE(SUM(total),0) r FROM orders WHERE status='completed' AND payment_method='pay_on_receipt'")->fetch_assoc()['r'];
$revenueBreakdown = [
    'Bank Transfer (Pay Now)' => ['primary', floatval($revPayNow)],
    'Cash on Receipt'         => ['warning', floatval($revOnReceipt)],
];
$maxRevBreak = max(array_column($revenueBreakdown, 1) ?: [1]);

// Top items by revenue
$topItemsRes = $conn->query("SELECT oi.name, SUM(oi.quantity) as qty_sold, SUM(oi.price * oi.quantity) as revenue FROM order_items oi JOIN orders o ON oi.order_id=o.id WHERE o.status='completed' GROUP BY oi.name ORDER BY revenue DESC LIMIT 10");

// ── Chart data ────────────────────────────────────────────────────────────────
$dailyViews = []; $dailyOrders = []; $dailyRevenue = [];

$res = $conn->query("SELECT DATE(viewed_at) d, COUNT(*) c FROM menu_views WHERE viewed_at>=DATE_SUB(CURDATE(),INTERVAL $days DAY) GROUP BY DATE(viewed_at)");
while ($r = $res->fetch_assoc()) $dailyViews[$r['d']] = intval($r['c']);

$res = $conn->query("SELECT DATE(created_at) d, COUNT(*) c FROM orders WHERE created_at>=DATE_SUB(CURDATE(),INTERVAL $days DAY) GROUP BY DATE(created_at)");
while ($r = $res->fetch_assoc()) $dailyOrders[$r['d']] = intval($r['c']);

$res = $conn->query("SELECT DATE(created_at) d, COALESCE(SUM(total),0) r FROM orders WHERE status='completed' AND created_at>=DATE_SUB(CURDATE(),INTERVAL $days DAY) GROUP BY DATE(created_at)");
while ($r = $res->fetch_assoc()) $dailyRevenue[$r['d']] = floatval($r['r']);

$chartData = [];
for ($i = $days; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chartData[] = ['date' => $d, 'views' => $dailyViews[$d] ?? 0, 'orders' => $dailyOrders[$d] ?? 0, 'revenue' => $dailyRevenue[$d] ?? 0];
}
$maxViews   = max(array_column($chartData, 'views')   ?: [1]);
$maxOrders  = max(array_column($chartData, 'orders')  ?: [1]);
$maxRevenue = max(array_column($chartData, 'revenue') ?: [1]);

// ── Views extras ──────────────────────────────────────────────────────────────
$popItems = $conn->query("SELECT m.id, m.name, m.image, m.view_count, c.name as category_name FROM menu_items m LEFT JOIN categories c ON m.category_id=c.id ORDER BY m.view_count DESC LIMIT 10");
$catViews = $conn->query("SELECT c.id, c.name, COUNT(v.id) as views FROM categories c LEFT JOIN menu_views v ON c.id=v.category_id GROUP BY c.id ORDER BY views DESC");
$maxCat = 1; $catData = [];
while ($c = $catViews->fetch_assoc()) { $catData[] = $c; $maxCat = max($maxCat, $c['views']); }

$sections = ['views' => ['Views', 'bi-eye', 'primary'], 'orders' => ['Orders', 'bi-bag-check', 'danger'], 'revenue' => ['Revenue', 'bi-cash-stack', 'success']];
[$sLabel, $sIcon, $sColor] = $sections[$section];

$statusColors = ['pending'=>'warning','preparing'=>'primary','ready'=>'info','completed'=>'success','cancelled'=>'danger'];

include 'header.php';
?>

<!-- Toolbar -->
<div class="d-flex flex-wrap gap-2 align-items-center mb-4">
    <div class="dropdown me-2">
        <button class="btn btn-<?= $sColor ?> rounded-pill dropdown-toggle px-4" type="button" data-bs-toggle="dropdown">
            <i class="bi <?= $sIcon ?>"></i> <?= $sLabel ?>
        </button>
        <ul class="dropdown-menu rounded-3 shadow border-0">
            <?php foreach ($sections as $key => [$label, $icon, $color]): ?>
            <li>
                <a class="dropdown-item <?= $section===$key ? 'active' : '' ?> py-2" href="?days=<?= $days ?>&section=<?= $key ?>">
                    <i class="bi <?= $icon ?> me-2"></i> <?= $label ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <a href="?days=7&section=<?= $section ?>"  class="btn btn-sm <?= $days==7  ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill">7 Days</a>
    <a href="?days=14&section=<?= $section ?>" class="btn btn-sm <?= $days==14 ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill">14 Days</a>
    <a href="?days=30&section=<?= $section ?>" class="btn btn-sm <?= $days==30 ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill">30 Days</a>
</div>

<?php if ($section === 'views'): ?>
<!-- ══════════════ VIEWS ══════════════ -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><h5 class="fw-bold text-primary"><?= number_format($totalViews) ?></h5><small class="text-muted">Total Views</small></div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><h5 class="fw-bold text-success"><?= number_format($todayViews) ?></h5><small class="text-muted">Today</small></div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><h5 class="fw-bold text-warning"><?= number_format($thisWeekViews) ?></h5><small class="text-muted">This Week</small></div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><h5 class="fw-bold text-info"><?= number_format($uniqueVisitors) ?></h5><small class="text-muted">Unique Visitors</small></div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h5 class="fw-bold mb-4"><i class="bi bi-graph-up text-danger"></i> Daily Views (Last <?= $days ?> Days)</h5>
                <div class="d-flex align-items-end gap-1" style="height:200px;">
                    <?php foreach ($chartData as $d): ?>
                    <div class="flex-fill d-flex flex-column align-items-center gap-1" style="min-width:8px;">
                        <div class="w-100 bg-primary rounded-top" style="height:<?= $maxViews ? round(($d['views']/$maxViews)*180) : 0 ?>px; opacity:.8;" title="<?= $d['date'] ?>: <?= $d['views'] ?> views"></div>
                        <small class="text-muted" style="font-size:9px;"><?= date('d', strtotime($d['date'])) ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h5 class="fw-bold mb-4"><i class="bi bi-pie-chart text-primary"></i> By Category</h5>
                <?php foreach ($catData as $c): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1"><span><?= htmlspecialchars($c['name']) ?></span><span class="text-muted"><?= $c['views'] ?></span></div>
                    <div class="progress" style="height:6px;"><div class="progress-bar bg-danger" style="width:<?= $maxCat ? ($c['views']/$maxCat)*100 : 0 ?>%"></div></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mt-4">
    <div class="card-body">
        <h5 class="fw-bold mb-4"><i class="bi bi-trophy text-warning"></i> Most Viewed Items</h5>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>#</th><th>Item</th><th>Category</th><th class="text-end">Views</th></tr></thead>
                <tbody>
                    <?php $i = 1; while ($item = $popItems->fetch_assoc()): ?>
                    <tr>
                        <td class="text-muted"><?= $i++ ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="../<?= $item['image'] ?>" class="rounded" style="width:36px;height:36px;object-fit:cover;">
                                <span class="fw-medium"><?= htmlspecialchars($item['name']) ?></span>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($item['category_name']) ?></span></td>
                        <td class="text-end fw-bold"><i class="bi bi-eye text-muted"></i> <?= $item['view_count'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php elseif ($section === 'orders'): ?>
<!-- ══════════════ ORDERS ══════════════ -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><h5 class="fw-bold text-danger"><?= number_format($totalOrders) ?></h5><small class="text-muted">Total Orders</small></div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><h5 class="fw-bold text-success"><?= number_format($todayOrders) ?></h5><small class="text-muted">Today</small></div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><h5 class="fw-bold text-primary"><?= number_format($thisWeekOrders) ?></h5><small class="text-muted">This Week</small></div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><h5 class="fw-bold text-warning"><?= number_format($activeOrders) ?></h5><small class="text-muted">Active Now</small></div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h5 class="fw-bold mb-4"><i class="bi bi-graph-up text-danger"></i> Daily Orders (Last <?= $days ?> Days)</h5>
                <div class="d-flex align-items-end gap-1" style="height:200px;">
                    <?php foreach ($chartData as $d): ?>
                    <div class="flex-fill d-flex flex-column align-items-center gap-1" style="min-width:8px;">
                        <div class="w-100 bg-danger rounded-top" style="height:<?= $maxOrders ? round(($d['orders']/$maxOrders)*180) : 0 ?>px; opacity:.8;" title="<?= $d['date'] ?>: <?= $d['orders'] ?> orders"></div>
                        <small class="text-muted" style="font-size:9px;"><?= date('d', strtotime($d['date'])) ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h5 class="fw-bold mb-4"><i class="bi bi-pie-chart text-danger"></i> By Status</h5>
                <?php foreach ($statusBreakdown as $label => [$color, $count]): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1"><span><?= $label ?></span><span class="text-muted"><?= $count ?></span></div>
                    <div class="progress" style="height:6px;"><div class="progress-bar bg-<?= $color ?>" style="width:<?= $maxStatus ? ($count/$maxStatus)*100 : 0 ?>%"></div></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mt-4">
    <div class="card-body">
        <h5 class="fw-bold mb-4"><i class="bi bi-clock-history text-danger"></i> Recent Orders</h5>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Order ID</th><th>Customer</th><th>Table</th><th>Payment</th><th>Status</th><th class="text-end">Total</th><th class="text-end">Date</th></tr></thead>
                <tbody>
                    <?php while ($o = $recentOrdersRes->fetch_assoc()):
                        $sCol = $statusColors[$o['status']] ?? 'secondary';
                    ?>
                    <tr>
                        <td class="font-monospace small"><?= htmlspecialchars($o['tracking_id']) ?></td>
                        <td><?= htmlspecialchars($o['customer_name'] ?: 'Guest') ?></td>
                        <td><?= $o['table_number'] ? 'Table ' . htmlspecialchars($o['table_number']) : '<span class="text-muted">—</span>' ?></td>
                        <td><span class="badge bg-light text-dark border"><?= $o['payment_method']==='pay_now' ? 'Bank' : 'Cash' ?></span></td>
                        <td><span class="badge bg-<?= $sCol ?>"><?= ucfirst($o['status']) ?></span></td>
                        <td class="text-end fw-bold">$<?= number_format($o['total'], 2) ?></td>
                        <td class="text-end text-muted small"><?= date('d M, H:i', strtotime($o['created_at'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php elseif ($section === 'revenue'): ?>
<!-- ══════════════ REVENUE ══════════════ -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><h5 class="fw-bold text-success">$<?= number_format($totalRevenue, 2) ?></h5><small class="text-muted">Total Revenue</small></div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><h5 class="fw-bold text-success">$<?= number_format($todayRevenue, 2) ?></h5><small class="text-muted">Today</small></div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><h5 class="fw-bold text-success">$<?= number_format($weekRevenue, 2) ?></h5><small class="text-muted">This Week</small></div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm rounded-4"><div class="card-body"><h5 class="fw-bold text-warning">$<?= number_format($pendingRevenue, 2) ?></h5><small class="text-muted">Pending</small></div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h5 class="fw-bold mb-4"><i class="bi bi-graph-up text-success"></i> Daily Revenue (Last <?= $days ?> Days)</h5>
                <div class="d-flex align-items-end gap-1" style="height:200px;">
                    <?php foreach ($chartData as $d): ?>
                    <div class="flex-fill d-flex flex-column align-items-center gap-1" style="min-width:8px;">
                        <div class="w-100 bg-success rounded-top" style="height:<?= $maxRevenue ? round(($d['revenue']/$maxRevenue)*180) : 0 ?>px; opacity:.8;" title="<?= $d['date'] ?>: $<?= number_format($d['revenue'],2) ?>"></div>
                        <small class="text-muted" style="font-size:9px;"><?= date('d', strtotime($d['date'])) ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h5 class="fw-bold mb-4"><i class="bi bi-pie-chart text-success"></i> By Payment Method</h5>
                <?php foreach ($revenueBreakdown as $label => [$color, $amount]): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1"><span><?= $label ?></span><span class="text-muted">$<?= number_format($amount, 2) ?></span></div>
                    <div class="progress" style="height:6px;"><div class="progress-bar bg-<?= $color ?>" style="width:<?= $maxRevBreak ? ($amount/$maxRevBreak)*100 : 0 ?>%"></div></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mt-4">
    <div class="card-body">
        <h5 class="fw-bold mb-4"><i class="bi bi-trophy text-warning"></i> Top Items by Revenue</h5>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>#</th><th>Item</th><th class="text-end">Qty Sold</th><th class="text-end">Revenue</th></tr></thead>
                <tbody>
                    <?php $i = 1; while ($item = $topItemsRes->fetch_assoc()): ?>
                    <tr>
                        <td class="text-muted"><?= $i++ ?></td>
                        <td class="fw-medium"><?= htmlspecialchars($item['name']) ?></td>
                        <td class="text-end"><?= $item['qty_sold'] ?></td>
                        <td class="text-end fw-bold text-success">$<?= number_format($item['revenue'], 2) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php endif; ?>

<?php include 'footer.php'; ?>
