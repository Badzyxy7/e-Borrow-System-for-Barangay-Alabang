<?php
session_start();

// Get the base directory (adminside folder)
$admin_base = dirname(__DIR__);

// Include database and functions with correct paths
include $admin_base . "/../db.php";
include __DIR__ . "/functions.php";

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo '<div class="p-8 text-center text-red-600">Access denied</div>';
    exit();
}

// Get and validate tab parameter
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'pending';
$valid_tabs = ['pending', 'approved', 'delivered', 'return_requests', 'returned', 'damaged'];

if (!in_array($tab, $valid_tabs)) {
    http_response_code(400);
    echo '<div class="p-8 text-center text-red-600">Invalid tab</div>';
    exit();
}

// Include the requested tab content
$tab_file = __DIR__ . "/{$tab}.php";
if (file_exists($tab_file)) {
    include $tab_file;
} else {
    http_response_code(404);
    echo '<div class="p-8 text-center text-red-600">Tab content not found</div>';
}
?>