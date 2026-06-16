<?php
require_once __DIR__ . '/../config/db.php';
$conn  = dbConnect();
$tid   = trim($_GET['id'] ?? '');
$isNew = isset($_GET['new']);
$order = null;
$items = [];

if ($tid) {
    $order = $conn->query("SELECT * FROM orders WHERE tracking_id='" . $conn->real_escape_string($tid) . "'")->fetch_assoc();
    if ($order) {
        $res = $conn->query("SELECT * FROM order_items WHERE order_id=" . intval($order['id']));
        while ($row = $res->fetch_assoc()) $items[] = $row;
    }
}

$statusSteps = ['pending', 'preparing', 'ready', 'completed'];
$statusLabels = ['pending' => 'Order Received', 'preparing' => 'Preparing', 'ready' => 'Ready for Pickup', 'completed' => 'Completed'];
$statusIcons  = ['pending' => 'bi-hourglass-split', 'preparing' => 'bi-fire', 'ready' => 'bi-bag-check', 'completed' => 'bi-check-circle-fill'];
$currentStep  = $order ? array_search($order['status'], $statusSteps) : -1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order - Gourmet Bites</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <link href="../css/style.css" rel="stylesheet">
    <style>
        .tracker { display:flex; align-items:flex-start; gap:0; }
        .step { flex:1; text-align:center; position:relative; }
        .step-icon { width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 8px; font-size:1.3rem; border:3px solid #dee2e6; background:#fff; color:#adb5bd; transition:.3s; }
        .step.done .step-icon  { border-color:#dc3545; background:#dc3545; color:#fff; }
        .step.active .step-icon { border-color:#dc3545; color:#dc3545; box-shadow:0 0 0 4px rgba(220,53,69,.2); }
        .step-line { position:absolute; top:24px; left:50%; width:100%; height:3px; background:#dee2e6; z-index:0; }
        .step:last-child .step-line { display:none; }
        .step.done .step-line { background:#dc3545; }
        .step-icon { z-index:1; position:relative; }
        .step-label { font-size:.75rem; font-weight:600; color:#adb5bd; }
        .step.done .step-label, .step.active .step-label { color:#212529; }
    </style>
    <?php if ($order && $order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
    <meta http-equiv="refresh" content="30">
    <?php endif; ?>
</head>
<body>
    <nav class="navbar navbar-dark bg-danger sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php"><i class="bi bi-shop"></i> Gourmet Bites</a>
        </div>
    </nav>

    <div class="container py-4" style="max-width:560px;">

        <?php if ($isNew && $order && $order['payment_method'] === 'pay_on_receipt'): ?>
        <!-- New cash order success banner -->
        <div class="alert alert-success rounded-4 d-flex align-items-center gap-3 mb-4">
            <i class="bi bi-check-circle-fill fs-3"></i>
            <div>
                <div class="fw-bold">Order Placed!</div>
                <div class="small">Pay cash when your order arrives at your table.</div>
            </div>
        </div>
        <?php endif; ?>

        <h5 class="fw-bold mb-4"><i class="bi bi-map text-danger"></i> Track Your Order</h5>

        <!-- Search form -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="id" value="<?= htmlspecialchars($tid) ?>"
                        class="form-control rounded-3 font-monospace"
                        placeholder="Enter tracking ID  e.g. ORD-ABC12345">
                    <button class="btn btn-danger rounded-3 px-3" type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>

        <?php if ($tid && !$order): ?>
        <div class="alert alert-warning rounded-4">No order found for <strong><?= htmlspecialchars($tid) ?></strong>. Please check your tracking ID.</div>

        <?php elseif ($order): ?>

        <!-- Tracking ID + copy -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">Tracking ID</div>
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <span class="fw-bold fs-4 font-monospace text-danger"><?= htmlspecialchars($order['tracking_id']) ?></span>
                    <button class="btn btn-outline-secondary btn-sm rounded-pill" onclick="copyId()">
                        <i class="bi bi-clipboard" id="copyIcon"></i>
                    </button>
                </div>
                <?php if ($order['table_number']): ?>
                <div class="text-muted small mt-2"><i class="bi bi-grid-3x3-gap-fill text-danger"></i>
                    Table <?= htmlspecialchars($order['table_number']) ?>
                    <?php if (!empty($order['table_uid'])): ?>
                    &nbsp;•&nbsp; <span class="font-monospace"><?= htmlspecialchars($order['table_uid']) ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php if ($order['customer_name']): ?>
                <div class="text-muted small"><i class="bi bi-person"></i> <?= htmlspecialchars($order['customer_name']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($order['status'] === 'cancelled'): ?>
        <div class="alert alert-danger rounded-4 text-center">
            <i class="bi bi-x-circle-fill fs-3 d-block mb-2"></i>
            <strong>Order Cancelled</strong>
        </div>
        <?php else: ?>

        <!-- Status tracker -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body py-4">
                <div class="tracker px-2">
                <?php foreach ($statusSteps as $i => $step):
                    $isDone   = $i < $currentStep;
                    $isActive = $i === $currentStep;
                    $cls = $isDone ? 'done' : ($isActive ? 'active' : '');
                ?>
                <div class="step <?= $cls ?>">
                    <div class="step-line"></div>
                    <div class="step-icon"><i class="bi <?= $statusIcons[$step] ?>"></i></div>
                    <div class="step-label"><?= $statusLabels[$step] ?></div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Payment status -->
        <?php
        $payMethod = $order['payment_method'] ?? 'pay_on_receipt';
        $payStatus = $order['payment_status'] ?? 'pending';
        $payBadge  = ['pending' => ['warning','Awaiting Payment'], 'submitted' => ['info','Payment Submitted — Awaiting Confirmation'], 'confirmed' => ['success','Payment Confirmed'], 'on_receipt' => ['secondary','Cash on Receipt'], 'cancelled' => ['danger','Cancelled']];
        [$badgeColor, $badgeLabel] = $payBadge[$payStatus] ?? ['secondary', ucfirst($payStatus)];
        ?>
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div><i class="bi bi-credit-card text-danger"></i> <span class="fw-semibold">Payment</span></div>
                <span class="badge bg-<?= $badgeColor ?> rounded-pill px-3 py-2"><?= $badgeLabel ?></span>
            </div>
            <?php if ($payMethod === 'pay_now' && $payStatus === 'pending'): ?>
            <div class="card-footer bg-transparent border-top-0 pb-3 text-center">
                <a href="payment.php?tid=<?= urlencode($order['tracking_id']) ?>" class="btn btn-danger btn-sm rounded-pill px-4">
                    <i class="bi bi-credit-card"></i> Complete Payment
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Order items -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-receipt text-danger"></i> Order Items</h6>
                <?php foreach ($items as $item): ?>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span><?= $item['quantity'] ?>× <?= htmlspecialchars($item['name']) ?></span>
                    <span class="text-muted small">$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                </div>
                <?php endforeach; ?>
                <div class="d-flex justify-content-between fw-bold mt-2 pt-1">
                    <span>Total</span>
                    <span class="text-danger">$<?= number_format($order['total'], 2) ?></span>
                </div>
            </div>
        </div>

        <?php if ($order['status'] !== 'completed'): ?>
        <p class="text-center text-muted small"><i class="bi bi-arrow-repeat"></i> Page auto-refreshes every 30 seconds</p>
        <?php endif; ?>

        <?php endif; // not cancelled ?>
        <?php endif; // order found ?>

    </div>

    <script>
    function copyId() {
        const tid = <?= json_encode($order['tracking_id'] ?? '') ?>;
        navigator.clipboard.writeText(tid).then(() => {
            const icon = document.getElementById('copyIcon');
            icon.className = 'bi bi-check';
            setTimeout(() => icon.className = 'bi bi-clipboard', 2000);
        });
    }
    </script>
</body>
</html>
