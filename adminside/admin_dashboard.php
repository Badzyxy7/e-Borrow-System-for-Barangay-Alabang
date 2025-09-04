<?php
session_start();
include "../landingpage/db.php"; // adjust path to your db.php

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../landingpage/login.php");
    exit();
}

// --- Stats ---
$total_eq_sql = "SELECT COUNT(*) AS c FROM equipment";
$available_eq_sql = "SELECT COUNT(*) AS c FROM equipment WHERE status='available'";
$pending_req_sql = "SELECT COUNT(*) AS c FROM borrow_requests WHERE status='pending'";
$borrowed_eq_sql = "SELECT COUNT(*) AS c FROM borrow_logs WHERE actual_return_date IS NULL";

$total_eq = $conn->query($total_eq_sql)->fetch_assoc()['c'];
$available_eq = $conn->query($available_eq_sql)->fetch_assoc()['c'];
$pending_req = $conn->query($pending_req_sql)->fetch_assoc()['c'];
$borrowed_eq = $conn->query($borrowed_eq_sql)->fetch_assoc()['c'];

// --- Recent Equipment ---
$recent_eq_sql = "SELECT * FROM equipment ORDER BY id DESC LIMIT 5";
$recent_eq = $conn->query($recent_eq_sql);

// --- Recent Requests ---
$recent_req_sql = "SELECT br.id, u.name AS user_name, e.name AS equipment, br.status, br.created_at
                   FROM borrow_requests br
                   JOIN users u ON br.user_id = u.id
                   JOIN equipment e ON br.equipment_id = e.id
                   ORDER BY br.created_at DESC LIMIT 5";
$recent_req = $conn->query($recent_req_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="flex bg-gray-100 min-h-screen">

  <!-- Sidebar -->
  <?php include "admin_sidebar.php"; ?>

  <!-- Main Content -->
  <main class="flex-1 ml-64 p-10">
    <h1 class="text-3xl font-bold mb-8 text-gray-800">Admin Dashboard</h1>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
      <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-blue-100 text-blue-600 rounded-full">
            <i data-feather="box"></i>
          </div>
          <div>
            <p class="text-sm text-gray-500">Total Equipment</p>
            <p class="text-2xl font-bold"><?php echo $total_eq; ?></p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-green-100 text-green-600 rounded-full">
            <i data-feather="check-circle"></i>
          </div>
          <div>
            <p class="text-sm text-gray-500">Available Items</p>
            <p class="text-2xl font-bold"><?php echo $available_eq; ?></p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-yellow-100 text-yellow-600 rounded-full">
            <i data-feather="clock"></i>
          </div>
          <div>
            <p class="text-sm text-gray-500">Pending Requests</p>
            <p class="text-2xl font-bold"><?php echo $pending_req; ?></p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-purple-100 text-purple-600 rounded-full">
            <i data-feather="archive"></i>
          </div>
          <div>
            <p class="text-sm text-gray-500">Currently Borrowed</p>
            <p class="text-2xl font-bold"><?php echo $borrowed_eq; ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="flex gap-4 mb-10">
      <a href="inventory.php" class="bg-blue-900 text-white px-6 py-3 rounded hover:bg-blue-700">Add Equipment</a>
      <a href="users.php" class="bg-blue-900 text-white px-6 py-3 rounded hover:bg-blue-700">Manage Users</a>
      <a href="requests.php" class="bg-blue-900 text-white px-6 py-3 rounded hover:bg-blue-700">Review Requests</a>
    </div>

    <!-- Recent Equipment & Requests -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Equipment -->
      <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
        <h2 class="text-lg font-semibold mb-4 text-gray-700">Recent Equipment</h2>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Equipment</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Category</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Availability</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Condition</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <?php while($eq = $recent_eq->fetch_assoc()): ?>
              <tr>
                <td class="px-4 py-2"><?php echo htmlspecialchars($eq['name']); ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($eq['category']); ?></td>
                <td class="px-4 py-2"><?php echo ucfirst($eq['status']); ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($eq['condition']); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

      <!-- Recent Requests -->
      <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
        <h2 class="text-lg font-semibold mb-4 text-gray-700">Recent Requests</h2>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Name</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Equipment</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Date</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Status</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <?php while($req = $recent_req->fetch_assoc()): ?>
              <tr>
                <td class="px-4 py-2"><?php echo htmlspecialchars($req['user_name']); ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($req['equipment']); ?></td>
                <td class="px-4 py-2"><?php echo date("M d, Y", strtotime($req['created_at'])); ?></td>
                <td class="px-4 py-2">
                  <span class="px-2 py-1 rounded text-sm <?php 
                    echo $req['status']=='pending'?'bg-yellow-100 text-yellow-700':
                         ($req['status']=='approved'?'bg-green-100 text-green-700':'bg-red-100 text-red-700'); ?>">
                    <?php echo ucfirst($req['status']); ?>
                  </span>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>

  <script>
    feather.replace();
  </script>
</body>
</html>
