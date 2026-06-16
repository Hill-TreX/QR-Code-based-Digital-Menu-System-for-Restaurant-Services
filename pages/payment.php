<?php
require_once __DIR__ . '/../config/db.php';
$conn = dbConnect();

$tid = $_GET['tid'] ?? '';
if (!$tid) { header('Location: ../index.php'); exit; }

$order = $conn->query("SELECT * FROM orders WHERE tracking_id='" . $conn->real_escape_string($tid) . "'")->fetch_assoc();
if (!$order || $order['payment_method'] !== 'pay_now') { header('Location: ../index.php'); exit; }

// Bank details from settings (fallback to placeholders)
$bank = ['bank_name' => 'Your Bank Name', 'bank_account_number' => '0000000000', 'bank_account_name' => 'Gourmet Bites Restaurant'];
$res = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('bank_name','bank_account_number','bank_account_name')");
if ($res) while ($s = $res->fetch_assoc()) $bank[$s['setting_key']] = $s['setting_value'];

$alreadySubmitted = $order['payment_status'] === 'submitted';
$cancelled        = $order['payment_status'] === 'cancelled' || $order['status'] === 'cancelled';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Gourmet Bites</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <link href="../css/style.css" rel="stylesheet">
    <style>
        .countdown { font-size: 2.5rem; font-weight: 700; font-variant-numeric: tabular-nums; color: #212529; }
        .countdown.urgent { color: #dc3545; animation: pulse 1s infinite; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
        .bank-box { background: #f8f9fa; border-radius: 12px; padding: 1.25rem; }
        .bank-row { display:flex; justify-content:space-between; align-items:center; padding: .5rem 0; border-bottom: 1px solid #e9ecef; }
        .bank-row:last-child { border-bottom:none; }
        .copy-btn { font-size:.75rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-danger sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php"><i class="bi bi-shop"></i> Gourmet Bites</a>
        </div>
    </nav>

    <div class="container py-4" style="max-width:560px;">

        <?php if ($cancelled): ?>
        <!-- Cancelled -->
        <div class="text-center py-5">
            <i class="bi bi-x-circle-fill display-1 text-danger"></i>
            <h4 class="fw-bold mt-3">Order Cancelled</h4>
            <p class="text-muted">This order has been cancelled.</p>
            <a href="../index.php" class="btn btn-danger rounded-pill px-4">Back to Menu</a>
        </div>

        <?php elseif ($alreadySubmitted): ?>
        <!-- Already submitted — show confirmation -->
        <div class="text-center py-4">
            <div class="display-1 text-success mb-3"><i class="bi bi-check-circle-fill"></i></div>
            <h4 class="fw-bold">Payment Submitted!</h4>
            <p class="text-muted">Awaiting confirmation from the restaurant.</p>
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <div class="text-muted small mb-1">Your Tracking ID</div>
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <span class="fw-bold fs-4 font-monospace text-danger"><?= htmlspecialchars($tid) ?></span>
                    <button class="btn btn-outline-secondary btn-sm rounded-pill copy-btn" onclick="copyText('<?= htmlspecialchars($tid) ?>', this)">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
            </div>
            <button class="btn btn-danger rounded-pill px-4" onclick="clearTableSession(); window.location.href='track.php?id=<?= urlencode($tid) ?>'">
                <i class="bi bi-map"></i> Track My Order
            </button>
        </div>

        <?php else: ?>
        <!-- Payment screen -->
        <h5 class="fw-bold mb-1">Complete Your Payment</h5>
        <p class="text-muted small mb-4">Transfer the exact amount below to our bank account, then tap "I've Made Payment".</p>

        <!-- Countdown -->
        <div class="card border-0 shadow-sm rounded-4 mb-3 text-center p-3">
            <div class="text-muted small mb-1"><i class="bi bi-clock"></i> Time remaining to complete payment</div>
            <div class="countdown" id="countdown">30:00</div>
            <div id="expiredMsg" class="text-danger small d-none mt-1">Payment window expired. Please place a new order.</div>
        </div>

        <!-- Amount -->
        <div class="card border-danger border-2 rounded-4 mb-3 text-center p-3">
            <div class="text-muted small">Amount to Transfer</div>
            <div class="fw-bold text-danger" style="font-size:2rem;">$<?= number_format($order['total'], 2) ?></div>
        </div>

        <!-- Bank details -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-bank text-danger"></i> Bank Account Details</h6>
                <div class="bank-box">
                    <div class="bank-row">
                        <span class="text-muted small">Bank</span>
                        <span class="fw-semibold"><?= htmlspecialchars($bank['bank_name']) ?></span>
                    </div>
                    <div class="bank-row">
                        <span class="text-muted small">Account Name</span>
                        <span class="fw-semibold"><?= htmlspecialchars($bank['bank_account_name']) ?></span>
                    </div>
                    <div class="bank-row">
                        <span class="text-muted small">Account Number</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold font-monospace fs-5"><?= htmlspecialchars($bank['bank_account_number']) ?></span>
                            <button class="btn btn-outline-secondary btn-sm rounded-pill copy-btn"
                                onclick="copyText('<?= htmlspecialchars($bank['bank_account_number']) ?>', this)">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        </div>
                    </div>
                    <div class="bank-row">
                        <span class="text-muted small">Reference / Order</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold font-monospace"><?= htmlspecialchars($tid) ?></span>
                            <button class="btn btn-outline-secondary btn-sm rounded-pill copy-btn"
                                onclick="copyText('<?= htmlspecialchars($tid) ?>', this)">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-grid gap-2">
            <button class="btn btn-success rounded-pill py-3 fw-bold fs-5" id="paidBtn" onclick="markPaid()">
                <i class="bi bi-check-circle"></i> I've Made Payment
            </button>
            <button class="btn btn-outline-danger rounded-pill" onclick="cancelOrder()">
                <i class="bi bi-x-circle"></i> Cancel Order
            </button>
        </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const TRACKING_ID = <?= json_encode($tid) ?>;

    function clearTableSession() {
        sessionStorage.removeItem('tableNumber');
        sessionStorage.removeItem('tableUid');
    }

    // 30-min countdown starting from first page open (persists across refreshes)
    const deadlineKey = 'payDeadline_' + TRACKING_ID;
    let deadline = parseInt(localStorage.getItem(deadlineKey) || '0');
    if (!deadline) {
        deadline = Date.now() + 30 * 60 * 1000;
        localStorage.setItem(deadlineKey, deadline);
    }

    function tick() {
        const left = Math.max(0, Math.floor((deadline - Date.now()) / 1000));
        const el = document.getElementById('countdown');
        if (!el) return;
        el.textContent = String(Math.floor(left / 60)).padStart(2, '0') + ':' + String(left % 60).padStart(2, '0');
        if (left <= 300) el.classList.add('urgent');
        if (left === 0) {
            document.getElementById('expiredMsg')?.classList.remove('d-none');
            const paidBtn = document.getElementById('paidBtn');
            if (paidBtn) paidBtn.disabled = true;
        }
    }
    tick();
    setInterval(tick, 1000);

    function copyText(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i> Copied';
            setTimeout(() => btn.innerHTML = orig, 2000);
        });
    }

    function markPaid() {
        const btn = document.getElementById('paidBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting…';
        fetch('order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'payment_submitted', tracking_id: TRACKING_ID })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) { clearTableSession(); localStorage.removeItem(deadlineKey); location.reload(); }
            else { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-circle"></i> I\'ve Made Payment'; alert('Error. Try again.'); }
        });
    }

    function cancelOrder() {
        if (!confirm('Cancel this order?')) return;
        fetch('order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'cancel', tracking_id: TRACKING_ID })
        })
        .then(r => r.json())
        .then(() => location.reload());
    }
    </script>
</body>
</html>
