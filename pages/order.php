<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');
$conn = dbConnect();

// DB migrations — safe for all MySQL/MariaDB versions
function addCol($conn, $col, $def) {
    if (!$conn->query("SHOW COLUMNS FROM orders LIKE '$col'")->num_rows)
        $conn->query("ALTER TABLE orders ADD COLUMN $col $def");
}
addCol($conn, 'tracking_id',    'VARCHAR(20) DEFAULT NULL');
addCol($conn, 'customer_name',  'VARCHAR(100) DEFAULT NULL');
addCol($conn, 'table_uid',      'VARCHAR(64) DEFAULT NULL');
addCol($conn, 'payment_method', "VARCHAR(20) DEFAULT 'pay_on_receipt'");
addCol($conn, 'payment_status', "VARCHAR(20) DEFAULT 'pending'");

// GET: look up a table by its UID (TBL-XXXXXX)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $uid = $conn->real_escape_string(strtoupper(trim($_GET['uid'] ?? '')));
    $row = $uid ? $conn->query("SELECT table_number, description FROM `tables` WHERE table_uid='$uid' AND is_active=1")->fetch_assoc() : null;
    echo $row
        ? json_encode(['found' => true,  'table_number' => $row['table_number'], 'description' => $row['description']])
        : json_encode(['found' => false]);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

// Mark payment submitted (customer clicked "I've Made Payment")
if ($action === 'payment_submitted') {
    $tid = $conn->real_escape_string($body['tracking_id'] ?? '');
    $conn->query("UPDATE orders SET payment_status='submitted' WHERE tracking_id='$tid' AND payment_method='pay_now'");
    echo json_encode(['success' => true]);
    exit;
}

// Cancel order
if ($action === 'cancel') {
    $tid = $conn->real_escape_string($body['tracking_id'] ?? '');
    $conn->query("UPDATE orders SET status='cancelled', payment_status='cancelled' WHERE tracking_id='$tid'");
    echo json_encode(['success' => true]);
    exit;
}

// Create order
if (empty($body['items'])) {
    echo json_encode(['success' => false, 'message' => 'No items']);
    exit;
}

$tableNum  = $conn->real_escape_string($body['table_number']   ?? '');
$tableUid  = $conn->real_escape_string($body['table_uid']      ?? '');
$custName  = $conn->real_escape_string($body['customer_name']  ?? 'Guest');
$method    = in_array($body['payment_method'] ?? '', ['pay_now','pay_on_receipt'])
             ? $body['payment_method'] : 'pay_on_receipt';
$payStatus = $method === 'pay_on_receipt' ? 'on_receipt' : 'pending';

$total = 0;
foreach ($body['items'] as $item)
    $total += floatval($item['price']) * intval($item['qty']);

// Unique tracking ID
$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
do {
    $rand = '';
    for ($i = 0; $i < 8; $i++) $rand .= $chars[random_int(0, 35)];
    $tid  = 'ORD-' . $rand;
} while ($conn->query("SELECT id FROM orders WHERE tracking_id='$tid'")->num_rows);

$stmt = $conn->prepare(
    "INSERT INTO orders (table_number, table_uid, customer_name, payment_method, payment_status, tracking_id, total, status)
     VALUES (?,?,?,?,?,?,?,'pending')"
);
$stmt->bind_param("ssssssd", $tableNum, $tableUid, $custName, $method, $payStatus, $tid, $total);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'DB error']);
    exit;
}
$oid = $conn->insert_id;

$is = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, name, price, quantity) VALUES (?,?,?,?,?)");
foreach ($body['items'] as $item) {
    $mid = intval($item['id']);
    $n   = $item['name'];
    $p   = floatval($item['price']);
    $q   = intval($item['qty']);
    $is->bind_param("iisdi", $oid, $mid, $n, $p, $q);
    $is->execute();
}

echo json_encode([
    'success'        => true,
    'order_id'       => $oid,
    'tracking_id'    => $tid,
    'total'          => $total,
    'payment_method' => $method,
]);
