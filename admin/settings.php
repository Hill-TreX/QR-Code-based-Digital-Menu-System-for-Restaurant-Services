<?php
$pageTitle = 'Settings';
$activePage = 'settings';
require_once __DIR__ . '/../config/db.php';
requireLogin();
$conn = dbConnect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $settingKey = substr($key, 8);
            $val = sanitize($value);
            // Insert if not exists, otherwise update
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?");
            $stmt->bind_param("sss", $settingKey, $val, $val);
            $stmt->execute();
        }
    }
    setFlash('success', 'Settings saved successfully');
    redirect('/admin/settings.php');
}

$settings = [];
$res = $conn->query("SELECT * FROM settings ORDER BY setting_key");
while ($s = $res->fetch_assoc()) $settings[$s['setting_key']] = $s;

include 'header.php';
?>

<form method="POST">
    <!-- Restaurant Info -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-4"><i class="bi bi-shop text-danger"></i> Restaurant Information</h5>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-medium">Restaurant Name</label><input type="text" name="setting_restaurant_name" value="<?= $settings['restaurant_name']['setting_value'] ?>" class="form-control rounded-3"></div>
                <div class="col-md-6"><label class="form-label fw-medium">Tagline</label><input type="text" name="setting_restaurant_tagline" value="<?= $settings['restaurant_tagline']['setting_value'] ?>" class="form-control rounded-3"></div>
                <div class="col-md-6"><label class="form-label fw-medium">Address</label><input type="text" name="setting_restaurant_address" value="<?= $settings['restaurant_address']['setting_value'] ?>" class="form-control rounded-3"></div>
                <div class="col-md-6"><label class="form-label fw-medium">Phone</label><input type="text" name="setting_restaurant_phone" value="<?= $settings['restaurant_phone']['setting_value'] ?>" class="form-control rounded-3"></div>
                <div class="col-12"><label class="form-label fw-medium">Email</label><input type="email" name="setting_restaurant_email" value="<?= $settings['restaurant_email']['setting_value'] ?>" class="form-control rounded-3"></div>
            </div>
        </div>
    </div>

    <!-- Currency -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-4"><i class="bi bi-currency-dollar text-success"></i> Currency</h5>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label fw-medium">Currency Symbol</label><input type="text" name="setting_currency_symbol" value="<?= $settings['currency_symbol']['setting_value'] ?>" class="form-control rounded-3"></div>
                <div class="col-md-4"><label class="form-label fw-medium">Currency Code</label><input type="text" name="setting_currency_code" value="<?= $settings['currency_code']['setting_value'] ?>" class="form-control rounded-3"></div>
                <div class="col-md-4"><label class="form-label fw-medium">Tax Rate (%)</label><input type="number" step="0.1" name="setting_tax_rate" value="<?= $settings['tax_rate']['setting_value'] ?>" class="form-control rounded-3"></div>
            </div>
        </div>
    </div>

    <!-- Features -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-4"><i class="bi bi-toggle-on text-primary"></i> Features</h5>
            <?php
            $featureSettings = ['enable_dark_mode', 'enable_search', 'allow_analytics'];
            foreach ($featureSettings as $key):
                $val = $settings[$key]['setting_value'];
            ?>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="setting_<?= $key ?>" value="1" <?= $val ? 'checked' : '' ?>>
                <label class="form-check-label"><?= ucwords(str_replace('_', ' ', $key)) ?></label>
            </div>
            <?php endforeach; ?>
            <div class="mb-3">
                <label class="form-label fw-medium">Items Per Page</label>
                <input type="number" name="setting_items_per_page" value="<?= $settings['items_per_page']['setting_value'] ?>" class="form-control rounded-3" style="width:100px;">
            </div>
        </div>
    </div>

    <!-- Payment / Bank Details -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-4"><i class="bi bi-bank text-danger"></i> Payment &amp; Bank Details</h5>
            <p class="text-muted small mb-3">These details are shown on the payment page when customers choose "Pay Now".</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Bank Name</label>
                    <input type="text" name="setting_bank_name"
                        value="<?= htmlspecialchars($settings['bank_name']['setting_value'] ?? '') ?>"
                        class="form-control rounded-3" placeholder="e.g. First National Bank">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Account Holder Name</label>
                    <input type="text" name="setting_bank_account_name"
                        value="<?= htmlspecialchars($settings['bank_account_name']['setting_value'] ?? '') ?>"
                        class="form-control rounded-3" placeholder="e.g. Gourmet Bites Restaurant">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Account Number</label>
                    <input type="text" name="setting_bank_account_number"
                        value="<?= htmlspecialchars($settings['bank_account_number']['setting_value'] ?? '') ?>"
                        class="form-control rounded-3" placeholder="e.g. 0123456789">
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mb-4">
        <button type="submit" class="btn btn-danger rounded-pill px-4"><i class="bi bi-save"></i> Save Settings</button>
    </div>
</form>

<?php include 'footer.php'; ?>
