<?php
require_once __DIR__ . '/../config/db.php';
startSession();
$_SESSION = [];
session_destroy();
redirect('/admin/login.php');
