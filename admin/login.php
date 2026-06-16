<?php
require_once __DIR__ . '/../config/db.php';
startSession();

if (isLoggedIn()) { redirect('/admin/dashboard.php'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = dbConnect();
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, username, full_name, role, password, is_active FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (!$user['is_active']) {
            $error = 'Account is disabled.';
        } elseif (password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_username'] = $user['username'];
            $conn->query("UPDATE users SET last_login = NOW() WHERE id = " . $user['id']);
            redirect('/admin/dashboard.php');
        } else {
            $error = 'Invalid password.';
        }
    } else {
        $error = 'User not found.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Gourmet Bites</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; background: #f8f9fa; }
        .login-card { max-width: 400px; width: 100%; }
        .login-logo { width: 64px; height: 64px; background: #dc3545; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card login-card shadow border-0 rounded-4">
                    <div class="card-body p-4">
                        <div class="login-logo"><i class="bi bi-shop text-white fs-2"></i></div>
                        <h4 class="text-center fw-bold mb-1">Admin Panel</h4>
                        <p class="text-muted text-center small mb-4">QR Code Digital Menu System</p>

                        <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show py-2"><i class="bi bi-exclamation-circle"></i> <?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-medium">Username or Email</label>
                                <input type="text" name="username" class="form-control form-control-lg rounded-3" required autofocus>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-medium">Password</label>
                                <input type="password" name="password" class="form-control form-control-lg rounded-3" required>
                            </div>
                            <button type="submit" class="btn btn-danger w-100 btn-lg rounded-3 fw-bold"><i class="bi bi-box-arrow-in-right"></i> Sign In</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="../index.php" class="text-muted small text-decoration-none">View Menu</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
