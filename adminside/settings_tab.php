<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../db.php"; // Adjust path

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>System Settings | Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="flex bg-gray-100 min-h-screen">

  <?php include "admin_sidebar.php"; ?>

  <main class="flex-1 p-8 ml-64">
    <h1 class="text-2xl font-bold mb-6">System Settings</h1>

    <div class="bg-white p-6 rounded-xl shadow-md">
      <p class="text-gray-600">This is the System Settings section. Configuration options will appear here.</p>
    </div>
  </main>

  <script>feather.replace();</script>
</body>
</html>
