<?php
/**
 * QR Code Digital Menu System - Database Configuration
 * For XAMPP localhost environment
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'restaurant_menu');
$_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', $_scheme . '://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])));

define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('QR_DIR', dirname(__DIR__) . '/qr/');
define('IMAGES_URL', BASE_URL . '/images/');

// Create database connection
function dbConnect() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Start secure session
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'use_strict_mode' => true
        ]);
    }
}

// Check if admin is logged in
function isLoggedIn() {
    startSession();
    return isset($_SESSION['admin_id']) && $_SESSION['admin_id'] > 0;
}

// Require admin login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

// Redirect helper
function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit;
}

// JSON response helper
function jsonResponse($success, $data = null, $message = '') {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit;
}

// Flash message helper
function setFlash($type, $message) {
    startSession();
    $_SESSION['flash'][$type] = $message;
}

function getFlash() {
    startSession();
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

function hasFlash() {
    startSession();
    return !empty($_SESSION['flash']);
}
