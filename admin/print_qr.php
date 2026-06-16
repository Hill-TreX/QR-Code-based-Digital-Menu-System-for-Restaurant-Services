<?php
require_once __DIR__ . '/../config/db.php';
requireLogin();
$conn = dbConnect();

$id    = intval($_GET['id'] ?? 0);
$table = $conn->query("SELECT * FROM `tables` WHERE id=$id")->fetch_assoc();
if (!$table) { header('Location: qr.php'); exit; }

// Restaurant name from settings
$restaurantName = 'Gourmet Bites';
$res = $conn->query("SELECT setting_value FROM settings WHERE setting_key='restaurant_name'");
if ($res && $row = $res->fetch_assoc()) $restaurantName = $row['setting_value'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print QR — Table <?= htmlspecialchars($table['table_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f0f0f0; }

        .controls {
            text-align: center;
            padding: 20px;
        }

        /* The printable card */
        .label-card {
            width: 85mm;          /* credit-card width — fits A6 / small stand */
            margin: 30px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,.15);
            overflow: hidden;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .label-header {
            background: #dc3545;
            color: #fff;
            text-align: center;
            padding: 14px 10px 10px;
        }
        .label-header .brand {
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: .5px;
        }
        .label-body {
            padding: 16px 10px;
            text-align: center;
        }
        .label-body img {
            width: 200px;
            height: 200px;
            display: block;
            margin: 0 auto 12px;
        }
        .label-table-num {
            font-size: 2.4rem;
            font-weight: 800;
            color: #212529;
            line-height: 1;
        }
        .label-uid {
            font-size: .7rem;
            letter-spacing: 1px;
            color: #6c757d;
            font-family: monospace;
            margin: 4px 0 8px;
        }
        .label-tagline {
            font-size: .78rem;
            color: #495057;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
            margin-top: 4px;
        }
        <?php if ($table['description']): ?>
        .label-desc {
            font-size: .72rem;
            color: #6c757d;
            margin-bottom: 4px;
        }
        <?php endif; ?>

        /* Print styles */
        @media print {
            body { background: #fff; }
            .controls { display: none !important; }
            .label-card {
                box-shadow: none;
                margin: 0 auto;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Screen-only controls -->
    <div class="controls">
        <a href="qr.php" class="btn btn-outline-secondary rounded-pill me-2">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <button class="btn btn-danger rounded-pill" onclick="window.print()">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>

    <!-- Printable label -->
    <div class="label-card">
        <div class="label-header">
            <div class="brand"><i class="bi bi-shop"></i> <?= htmlspecialchars($restaurantName) ?></div>
        </div>
        <div class="label-body">
            <img src="<?= htmlspecialchars($table['qr_image']) ?>"
                 alt="QR Code Table <?= htmlspecialchars($table['table_number']) ?>">

            <div class="label-table-num">Table <?= htmlspecialchars($table['table_number']) ?></div>
            <div class="label-uid"><?= htmlspecialchars($table['table_uid']) ?></div>

            <?php if ($table['description']): ?>
            <div class="label-desc"><?= htmlspecialchars($table['description']) ?></div>
            <?php endif; ?>

            <div class="label-tagline">
                <i class="bi bi-qr-code-scan"></i> Scan to view menu &amp; order
            </div>
        </div>
    </div>

    <script>
    // Auto-trigger print dialog on load
    window.addEventListener('load', function() { window.print(); });
    </script>
</body>
</html>
