<?php
session_start();
include "../db.php";

// Check auth
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die('Unauthorized');
}

$tab = $_GET['tab'] ?? 'dashboard';

switch($tab) {
    case 'dashboard':
        include 'content/dashboard_content.php';
        break;
    case 'inventory':
        include 'content/inventory_content.php';
        break;
    case 'requests':
        include 'content/requests_content.php';
        break;
    case 'users':
        include 'content/users_content.php';
        break;
    case 'schedule':
        include 'content/schedule_content.php';
        break;
    case 'reports':
        include 'content/reports_content.php';
        break;
    case 'settings':
        include 'content/settings_content.php';
        break;
    default:
        echo '<div class="p-6"><h1 class="text-2xl font-bold">Page not found</h1></div>';
}
?>