<?php require_once __DIR__ . '/../config/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Gourmet Bites</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <link href="../css/style.css" rel="stylesheet">
    <style>
        .pay-option { cursor: pointer; transition: border-color .2s, box-shadow .2s; }
        .pay-option:has(input:checked) { border-color: #dc3545 !important; box-shadow: 0 0 0 3px rgba(220,53,69,.15); }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-danger sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php"><i class="bi bi-arrow-left"></i> Back to Menu</a>
        </div>
    </nav>

    <div class="container py-4" style="max-width:600px;">
        <h4 class="fw-bold mb-1"><i class="bi bi-bag-check text-danger"></i> Checkout</h4>

        <!-- Table info / manual input -->
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-grid-3x3-gap-fill text-danger"></i> Your Table</h6>

                <!-- QR scan: read-only -->
                <div id="tableDisplay" class="d-none">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger fs-6 px-3 py-2" id="tableInfo"></span>
                        <span class="text-muted small font-monospace" id="tableUidDisplay"></span>
                    </div>
                </div>

                <!-- Manual: enter TBL-XXXXXX from the table card -->
                <div id="tableInputWrap">
                    <label class="form-label text-muted small">Enter the Table ID printed on your table card</label>
                    <div class="input-group">
                        <span class="input-group-text fw-bold font-monospace bg-danger text-white border-danger">TBL-</span>
                        <input type="text" id="manualTableUid" class="form-control font-monospace text-uppercase"
                            placeholder="A3F9B2" maxlength="6" autocomplete="off"
                            oninput="lookupTable(this.value)">
                    </div>
                    <div class="invalid-feedback" id="tableError">Table ID not found. Check the code on your table card.</div>
                    <!-- Result shown after lookup -->
                    <div id="tableFound" class="d-none mt-2 p-2 rounded-3 bg-success bg-opacity-10 border border-success d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span class="fw-semibold" id="tableFoundName"></span>
                        <span class="text-muted small" id="tableFoundDesc"></span>
                    </div>
                    <div id="tableSearching" class="d-none mt-2 text-muted small">
                        <span class="spinner-border spinner-border-sm me-1"></span> Looking up table…
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty cart -->
        <div id="emptyState" class="text-center py-5 d-none">
            <i class="bi bi-cart-x display-1 text-muted"></i>
            <p class="mt-3 text-muted">Your cart is empty.</p>
            <a href="../index.php" class="btn btn-danger rounded-pill px-4">Browse Menu</a>
        </div>

        <!-- Checkout form -->
        <div id="checkoutForm">
            <!-- Order summary -->
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-receipt text-danger"></i> Your Order</h6>
                    <div id="itemsList"></div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total</span>
                        <span class="text-danger" id="orderTotal">$0.00</span>
                    </div>
                </div>
            </div>

            <!-- Name -->
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-person text-danger"></i> Your Name</h6>
                    <input type="text" id="customerName" class="form-control rounded-3" placeholder="Enter your name" autocomplete="name">
                    <div class="invalid-feedback">Please enter your name.</div>
                </div>
            </div>

            <!-- Payment method -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-credit-card text-danger"></i> How would you like to pay?</h6>
                    <div class="d-grid gap-3">
                        <label class="pay-option border rounded-4 p-3 d-flex align-items-center gap-3">
                            <input type="radio" name="payMethod" value="pay_now" class="form-check-input mt-0 flex-shrink-0" style="width:22px;height:22px;">
                            <div>
                                <div class="fw-semibold"><i class="bi bi-phone-fill text-success"></i> Pay Now</div>
                                <div class="small text-muted">Bank transfer — we'll confirm receipt</div>
                            </div>
                        </label>
                        <label class="pay-option border rounded-4 p-3 d-flex align-items-center gap-3">
                            <input type="radio" name="payMethod" value="pay_on_receipt" class="form-check-input mt-0 flex-shrink-0" style="width:22px;height:22px;" checked>
                            <div>
                                <div class="fw-semibold"><i class="bi bi-cash-coin text-warning"></i> Pay when I receive my order</div>
                                <div class="small text-muted">Pay cash when your order arrives at your table</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <button class="btn btn-danger w-100 rounded-pill py-3 fw-bold fs-5" id="placeBtn" onclick="submitOrder()">
                <i class="bi bi-check-circle"></i> Place Order
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const cart        = JSON.parse(localStorage.getItem('cart') || '[]');
    const tableUid    = sessionStorage.getItem('tableUid')    || '';
    const tableNumber = sessionStorage.getItem('tableNumber') || '';

    // Track manually-looked-up table
    let resolvedTableNumber = tableNumber;
    let resolvedTableUid    = tableUid;

    if (tableNumber) {
        // Came from QR scan — show read-only badge, hide input
        document.getElementById('tableInfo').textContent       = 'Table ' + tableNumber;
        document.getElementById('tableUidDisplay').textContent = tableUid;
        document.getElementById('tableDisplay').classList.remove('d-none');
        document.getElementById('tableInputWrap').classList.add('d-none');
    }

    // Debounce helper
    let lookupTimer = null;
    function lookupTable(val) {
        val = 'TBL-' + val.trim().toUpperCase();
        const foundEl     = document.getElementById('tableFound');
        const searchingEl = document.getElementById('tableSearching');
        const errorEl     = document.getElementById('tableError');
        const inputEl     = document.getElementById('manualTableUid');

        foundEl.classList.add('d-none');
        inputEl.classList.remove('is-invalid');
        resolvedTableNumber = '';
        resolvedTableUid    = '';

        if (val.length < 10) { searchingEl.classList.add('d-none'); return; }

        clearTimeout(lookupTimer);
        lookupTimer = setTimeout(() => {
            searchingEl.classList.remove('d-none');
            fetch('order.php?uid=' + encodeURIComponent(val))
                .then(r => r.json())
                .then(res => {
                    searchingEl.classList.add('d-none');
                    if (res.found) {
                        resolvedTableNumber = res.table_number;
                        resolvedTableUid    = val;
                        document.getElementById('tableFoundName').textContent = 'Table ' + res.table_number;
                        document.getElementById('tableFoundDesc').textContent = res.description || '';
                        foundEl.classList.remove('d-none');
                        inputEl.classList.remove('is-invalid');
                    } else {
                        inputEl.classList.add('is-invalid');
                        errorEl.textContent = 'Table ID not found. Check the code on your table card.';
                    }
                });
        }, 500);
    }

    if (cart.length === 0) {
        document.getElementById('emptyState').classList.remove('d-none');
        document.getElementById('checkoutForm').classList.add('d-none');
    } else {
        let total = 0, html = '';
        cart.forEach(item => {
            const sub = item.price * item.qty;
            total += sub;
            html += `<div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                <span>${item.qty}× <span class="fw-medium">${item.name}</span></span>
                <span class="text-muted small">$${sub.toFixed(2)}</span>
            </div>`;
        });
        document.getElementById('itemsList').innerHTML = html;
        document.getElementById('orderTotal').textContent = '$' + total.toFixed(2);
    }

    function submitOrder() {
        const nameEl = document.getElementById('customerName');
        if (!nameEl.value.trim()) {
            nameEl.classList.add('is-invalid');
            nameEl.focus();
            return;
        }
        nameEl.classList.remove('is-invalid');

        // Validate table — must be resolved (from QR or manual lookup)
        if (!resolvedTableNumber) {
            const inputEl = document.getElementById('manualTableUid');
            const errorEl = document.getElementById('tableError');
            if (inputEl) {
                inputEl.classList.add('is-invalid');
                errorEl.textContent = resolvedTableUid
                    ? 'Table ID not found. Check the code on your table card.'
                    : 'Please enter your Table ID before placing the order.';
                inputEl.focus();
            }
            return;
        }

        const method = document.querySelector('input[name="payMethod"]:checked').value;
        const btn = document.getElementById('placeBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Placing order…';

        fetch('order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                table_number:   resolvedTableNumber,
                table_uid:      resolvedTableUid,
                customer_name:  nameEl.value.trim(),
                payment_method: method,
                items:          cart
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                localStorage.removeItem('cart');
                sessionStorage.removeItem('tableNumber');
                sessionStorage.removeItem('tableUid');
                if (method === 'pay_now') {
                    location.href = 'payment.php?tid=' + res.tracking_id;
                } else {
                    location.href = 'track.php?id=' + res.tracking_id + '&new=1';
                }
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle"></i> Place Order';
                alert('Something went wrong. Please try again.');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Place Order';
            alert('Connection error. Please try again.');
        });
    }
    </script>
</body>
</html>
