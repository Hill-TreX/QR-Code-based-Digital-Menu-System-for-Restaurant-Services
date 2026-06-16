<?php
$pageTitle  = 'QR Tables';
$activePage = 'qr';
require_once __DIR__ . '/../config/db.php';
requireLogin();
$conn = dbConnect();

// Create tables table
$conn->query("CREATE TABLE IF NOT EXISTS `tables` (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    table_number VARCHAR(20)  NOT NULL,
    table_uid    VARCHAR(64)  NOT NULL UNIQUE,
    description  VARCHAR(255) DEFAULT NULL,
    qr_image     TEXT         DEFAULT NULL,
    qr_data      TEXT         DEFAULT NULL,
    is_active    TINYINT(1)   DEFAULT 1,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
)");

// ── Build the base URL for QR codes ────────────────────────────────────────
function buildQrBaseUrl() {
    $httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/');
    $isRealDomain = !preg_match('/^(localhost$|127\.|192\.168\.|172\.\d+\.|10\.)/', $httpHost);

    if ($isRealDomain) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $scheme . '://' . $httpHost . $basePath;
    }
    $serverAddr = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
    if (!preg_match('/^127\./', $serverAddr)) {
        $host = $serverAddr;
    } else {
        $host = null;
        foreach (array_filter(array_map('trim', explode(' ', trim(shell_exec('hostname -I') ?: '')))) as $ip) {
            if (preg_match('/^(192\.168\.|172\.\d+\.|10\.)/', $ip)) { $host = $ip; break; }
        }
        $host = $host ?: $httpHost;
    }
    return 'http://' . $host . $basePath;
}

// ── Add table ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $tableNumber = trim($_POST['table_number'] ?? '');
    $description = trim($_POST['description']  ?? '') ?: null;

    if ($tableNumber === '') {
        setFlash('danger', 'Table number is required.');
        redirect('/admin/qr.php');
    }

    // Unique table UID
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    do {
        $rand = '';
        for ($i = 0; $i < 6; $i++) $rand .= $chars[random_int(0, 35)];
        $tableUid = 'TBL-' . $rand;
    } while ($conn->query("SELECT id FROM `tables` WHERE table_uid='$tableUid'")->num_rows);

    // QR payload URL
    $base   = buildQrBaseUrl();
    $qrData = $base . '/index.php?tid=' . urlencode($tableUid) . '&tnum=' . urlencode($tableNumber);
    $qrImage= 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrData);

    $stmt = $conn->prepare("INSERT INTO `tables` (table_number, table_uid, description, qr_image, qr_data) VALUES (?,?,?,?,?)");
    $stmt->bind_param("sssss", $tableNumber, $tableUid, $description, $qrImage, $qrData);
    $stmt->execute();
    setFlash('success', 'Table QR created for Table ' . $tableNumber);
    redirect('/admin/qr.php');
}

// ── Delete ──────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM `tables` WHERE id=$id");
    setFlash('success', 'Table deleted');
    redirect('/admin/qr.php');
}

// ── Toggle active ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    $conn->query("UPDATE `tables` SET is_active = NOT is_active WHERE id=" . intval($_POST['id']));
    redirect('/admin/qr.php');
}

$tables = $conn->query("SELECT * FROM `tables` ORDER BY CAST(table_number AS UNSIGNED), table_number");

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Each table gets a permanent QR code. Scan → browse menu → order.</p>
    <button class="btn btn-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-lg"></i> Add Table
    </button>
</div>

<?php if ($tables->num_rows === 0): ?>
<div class="text-center py-5">
    <i class="bi bi-qr-code display-1 text-muted"></i>
    <p class="text-muted mt-3">No tables yet. Add your first table to generate a QR code.</p>
    <button class="btn btn-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-lg"></i> Add Table
    </button>
</div>
<?php else: ?>
<div class="row g-4">
<?php while ($t = $tables->fetch_assoc()): ?>
<div class="col-sm-6 col-lg-4 col-xl-3">
    <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body text-center">

            <!-- Header: active toggle -->
            <div class="d-flex justify-content-between align-items-start mb-3">
                <span class="badge bg-light text-dark border fs-6 fw-bold">Table <?= htmlspecialchars($t['table_number']) ?></span>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button class="btn btn-sm <?= $t['is_active'] ? 'btn-success' : 'btn-secondary' ?> rounded-pill">
                        <?= $t['is_active'] ? 'Active' : 'Inactive' ?>
                    </button>
                </form>
            </div>

            <!-- Table UID -->
            <div class="font-monospace text-muted small mb-2"><?= htmlspecialchars($t['table_uid']) ?></div>

            <!-- Description -->
            <?php if ($t['description']): ?>
            <div class="small text-muted mb-2"><i class="bi bi-info-circle"></i> <?= htmlspecialchars($t['description']) ?></div>
            <?php endif; ?>

            <!-- QR image -->
            <img src="<?= htmlspecialchars($t['qr_image']) ?>"
                 class="img-fluid rounded-3 mb-3" style="max-height:160px;"
                 alt="QR Table <?= htmlspecialchars($t['table_number']) ?>">

            <!-- URL copy -->
            <div class="input-group input-group-sm mb-3">
                <input type="text" class="form-control form-control-sm font-monospace"
                    value="<?= htmlspecialchars($t['qr_data']) ?>" readonly>
                <button class="btn btn-outline-secondary btn-sm" type="button"
                    onclick="copyUrl('<?= htmlspecialchars($t['qr_data'], ENT_QUOTES) ?>', this)">
                    <i class="bi bi-clipboard"></i>
                </button>
            </div>

            <!-- Actions -->
            <div class="d-flex gap-2 justify-content-center">
                <a href="print_qr.php?id=<?= $t['id'] ?>" target="_blank"
                   class="btn btn-danger btn-sm rounded-pill px-3">
                    <i class="bi bi-printer"></i> Print
                </a>
                <form method="POST" class="d-inline" onsubmit="return confirm('Delete Table <?= htmlspecialchars($t['table_number'], ENT_QUOTES) ?>?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button class="btn btn-outline-danger btn-sm rounded-pill px-3">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
<?php endwhile; ?>
</div>
<?php endif; ?>

<!-- Add Table Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-qr-code-scan text-danger"></i> Add Table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Table Number <span class="text-danger">*</span></label>
                        <input type="text" name="table_number" class="form-control rounded-3"
                            placeholder="e.g. 1, 12, VIP 1, Bar 3" required autofocus>
                        <div class="form-text">This will appear on the printed QR label and in orders.</div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-medium">Description <span class="text-muted">(optional)</span></label>
                        <input type="text" name="description" class="form-control rounded-3"
                            placeholder="e.g. Window seat, 4 persons">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                        <i class="bi bi-qr-code"></i> Generate QR
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function copyUrl(url, btn) {
    navigator.clipboard.writeText(url).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check"></i>';
        setTimeout(() => btn.innerHTML = orig, 2000);
    });
}
</script>

<?php include 'footer.php'; ?>
