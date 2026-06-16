<?php
$pageTitle  = 'Orders';
$activePage = 'orders';
require_once __DIR__ . '/../config/db.php';
requireLogin();
$conn = dbConnect();

// Create base tables
$conn->query("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_number VARCHAR(20) DEFAULT NULL,
    status ENUM('pending','preparing','ready','completed','cancelled') DEFAULT 'pending',
    total DECIMAL(10,2) DEFAULT 0.00,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");
$conn->query("CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)");

// Migrations for new columns
function addOrderCol($conn, $col, $def) {
    if (!$conn->query("SHOW COLUMNS FROM orders LIKE '$col'")->num_rows)
        $conn->query("ALTER TABLE orders ADD COLUMN $col $def");
}
addOrderCol($conn, 'tracking_id',    'VARCHAR(20) DEFAULT NULL');
addOrderCol($conn, 'customer_name',  'VARCHAR(100) DEFAULT NULL');
addOrderCol($conn, 'table_uid',      'VARCHAR(64) DEFAULT NULL');
addOrderCol($conn, 'payment_method', "VARCHAR(20) DEFAULT 'pay_on_receipt'");
addOrderCol($conn, 'payment_status', "VARCHAR(20) DEFAULT 'pending'");

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'status') {
    $id     = intval($_POST['id']);
    $status = in_array($_POST['status'], ['pending','preparing','ready','completed','cancelled'])
              ? $_POST['status'] : 'pending';
    $conn->query("UPDATE orders SET status='$status' WHERE id=$id");
    setFlash('success', 'Order status updated');
    redirect('/admin/orders.php' . (isset($_GET['status']) ? '?status=' . $_GET['status'] : ''));
}

// Confirm payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'confirm_payment') {
    $id = intval($_POST['id']);
    $conn->query("UPDATE orders SET payment_status='confirmed' WHERE id=$id");
    setFlash('success', 'Payment confirmed');
    redirect('/admin/orders.php');
}

// Mark cash as paid (pay_on_receipt)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_paid') {
    $id = intval($_POST['id']);
    $conn->query("UPDATE orders SET payment_status='confirmed', status='completed' WHERE id=$id");
    setFlash('success', 'Order marked as paid and completed');
    redirect('/admin/orders.php');
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM orders WHERE id=$id");
    setFlash('success', 'Order deleted');
    redirect('/admin/orders.php');
}

$statusFilter = $_GET['status'] ?? 'all';
$where = $statusFilter !== 'all'
    ? "WHERE o.status='" . $conn->real_escape_string($statusFilter) . "'"
    : '';
$orders = $conn->query("SELECT * FROM orders o $where ORDER BY o.created_at DESC");

$counts = [];
foreach (['pending','preparing','ready','completed','cancelled'] as $s) {
    $counts[$s] = $conn->query("SELECT COUNT(*) c FROM orders WHERE status='$s'")->fetch_assoc()['c'];
}
$counts['all'] = array_sum($counts);

// Pending payment count for badge
$pendingPayments = $conn->query("SELECT COUNT(*) c FROM orders WHERE payment_status='submitted'")->fetch_assoc()['c'];

include 'header.php';
?>

<?php if ($pendingPayments > 0): ?>
<div class="alert alert-warning rounded-4 d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <span><strong><?= $pendingPayments ?></strong> payment<?= $pendingPayments > 1 ? 's' : '' ?> awaiting your confirmation.</span>
</div>
<?php endif; ?>

<!-- Status filter tabs -->
<div class="d-flex gap-2 flex-wrap mb-4">
<?php
$tabs = ['all'=>['All','secondary'],'pending'=>['Pending','warning'],'preparing'=>['Preparing','primary'],'ready'=>['Ready','info'],'completed'=>['Completed','success'],'cancelled'=>['Cancelled','danger']];
foreach ($tabs as $key => [$label, $color]):
?>
<a href="?status=<?= $key ?>" class="btn btn-sm btn-<?= $statusFilter===$key ? $color : 'outline-'.$color ?> rounded-pill">
    <?= $label ?> <span class="badge bg-white text-dark ms-1"><?= $counts[$key] ?></span>
</a>
<?php endforeach; ?>
</div>

<?php if ($orders->num_rows === 0): ?>
<div class="text-center py-5">
    <i class="bi bi-bag-x display-1 text-muted"></i>
    <p class="text-muted mt-3">No orders found.</p>
</div>
<?php else: ?>
<div class="row g-3">
<?php while ($order = $orders->fetch_assoc()):
    $orderItems = $conn->query("SELECT * FROM order_items WHERE order_id=" . intval($order['id']));
    $statusColors = ['pending'=>'warning','preparing'=>'primary','ready'=>'info','completed'=>'success','cancelled'=>'danger'];
    $sColor = $statusColors[$order['status']] ?? 'secondary';

    $payMethod = $order['payment_method'] ?? 'pay_on_receipt';
    $payStatus = $order['payment_status'] ?? 'pending';
    $payBadges = ['pending'=>['warning','Awaiting Payment'],'submitted'=>['info','Payment Submitted'],'confirmed'=>['success','Payment Confirmed'],'on_receipt'=>['secondary','Cash on Receipt'],'cancelled'=>['danger','Cancelled']];
    [$pColor, $pLabel] = $payBadges[$payStatus] ?? ['secondary', ucfirst($payStatus)];
?>
<div class="col-12 col-md-6 col-xl-4">
    <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="fw-bold">#<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></span>
                    <?php if (!empty($order['tracking_id'])): ?>
                    <span class="text-muted small font-monospace ms-1"><?= htmlspecialchars($order['tracking_id']) ?></span>
                    <?php endif; ?>
                    <div class="text-muted small"><?= date('d M, H:i', strtotime($order['created_at'])) ?></div>
                </div>
                <span class="badge bg-<?= $sColor ?> rounded-pill"><?= ucfirst($order['status']) ?></span>
            </div>

            <!-- Table & Customer -->
            <div class="d-flex gap-3 mb-2 small">
                <?php if ($order['table_number']): ?>
                <span><i class="bi bi-grid-3x3-gap-fill text-danger"></i> <strong>Table <?= htmlspecialchars($order['table_number']) ?></strong></span>
                <?php endif; ?>
                <?php if ($order['customer_name']): ?>
                <span><i class="bi bi-person text-danger"></i> <?= htmlspecialchars($order['customer_name']) ?></span>
                <?php endif; ?>
            </div>
            <?php if (!empty($order['table_uid'])): ?>
            <div class="text-muted small font-monospace mb-2"><?= htmlspecialchars($order['table_uid']) ?></div>
            <?php endif; ?>

            <!-- Items -->
            <ul class="list-unstyled small mb-3">
            <?php while ($item = $orderItems->fetch_assoc()): ?>
                <li class="d-flex justify-content-between border-bottom py-1">
                    <span><?= $item['quantity'] ?>× <?= htmlspecialchars($item['name']) ?></span>
                    <span class="text-muted">$<?= number_format($item['price']*$item['quantity'],2) ?></span>
                </li>
            <?php endwhile; ?>
            </ul>

            <div class="d-flex justify-content-between fw-bold mb-3">
                <span>Total</span>
                <span class="text-danger">$<?= number_format($order['total'],2) ?></span>
            </div>

            <!-- Payment status -->
            <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded-3 bg-light">
                <span class="small fw-semibold"><i class="bi bi-credit-card"></i> Payment</span>
                <span class="badge bg-<?= $pColor ?>"><?= $pLabel ?></span>
            </div>

            <!-- Payment action -->
            <?php if ($payStatus === 'submitted'): ?>
            <form method="POST" class="mb-2">
                <input type="hidden" name="action" value="confirm_payment">
                <input type="hidden" name="id" value="<?= $order['id'] ?>">
                <button class="btn btn-success w-100 rounded-3 btn-sm fw-bold">
                    <i class="bi bi-check-circle"></i> Confirm Payment Received
                </button>
            </form>
            <?php elseif ($payStatus === 'on_receipt' && $order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
            <form method="POST" class="mb-2">
                <input type="hidden" name="action" value="mark_paid">
                <input type="hidden" name="id" value="<?= $order['id'] ?>">
                <button class="btn btn-outline-success w-100 rounded-3 btn-sm">
                    <i class="bi bi-cash-coin"></i> Mark as Paid & Completed
                </button>
            </form>
            <?php endif; ?>

            <!-- Order status update -->
            <form method="POST" class="d-flex gap-2">
                <input type="hidden" name="action" value="status">
                <input type="hidden" name="id" value="<?= $order['id'] ?>">
                <select name="status" class="form-select form-select-sm rounded-3">
                <?php foreach (array_keys($statusColors) as $s): ?>
                    <option value="<?= $s ?>" <?= $order['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-danger rounded-3">Update</button>
            </form>
        </div>

        <!-- Footer: delete -->
        <div class="card-footer bg-transparent border-0 pb-3 d-flex justify-content-end">
            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this order?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $order['id'] ?>">
                <button class="btn btn-sm btn-outline-danger rounded-pill"><i class="bi bi-trash"></i></button>
            </form>
        </div>
    </div>
</div>
<?php endwhile; ?>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>
