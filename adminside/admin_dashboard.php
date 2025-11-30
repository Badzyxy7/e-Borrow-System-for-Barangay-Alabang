<?php
session_start();
include "../db.php"; // adjust path to your db.php

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
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

// --- Upcoming Due (within 3 days) ---
$upcoming_due_sql = "
    SELECT bl.*, u.name AS user_name, e.name AS equipment_name
    FROM borrow_logs bl
    JOIN users u ON bl.user_id = u.id
    JOIN equipment e ON bl.equipment_id = e.id
    WHERE bl.actual_return_date IS NULL
      AND bl.expected_return_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
    ORDER BY bl.expected_return_date ASC
";
$upcoming_due = $conn->query($upcoming_due_sql);

// --- Past Due ---
$past_due_sql = "
    SELECT bl.*, u.name AS user_name, e.name AS equipment_name
    FROM borrow_logs bl
    JOIN users u ON bl.user_id = u.id
    JOIN equipment e ON bl.equipment_id = e.id
    WHERE bl.actual_return_date IS NULL
      AND bl.expected_return_date < CURDATE()
    ORDER BY bl.expected_return_date ASC
";
$past_due = $conn->query($past_due_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>E-Borrow System | Admin Dashboard</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
    
    * {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>

<body class="bg-gray-50 min-h-screen">

  <!-- Sidebar -->
  <?php include "admin_sidebar.php"; ?>
  
  <!-- Header -->
  <?php 
    $page_title = "Admin Dashboard"; 
    include "header_admin.php"; 
  ?>

  <!-- Main Content -->
  <main class="ml-16 md:ml-64 p-6 pt-24 md:pt-28">

     <!-- Welcome Banner with Philippine Pattern -->
<div class="relative bg-gradient-to-r from-blue-900 via-blue-800 to-blue-700 rounded-2xl p-6 sm:p-8 mb-6 sm:mb-8 shadow-lg overflow-hidden">
  <!-- Philippine Pattern Background Image -->
  <div class="absolute inset-0 opacity-25">
    <img src="../photos/logo1.jpg" alt="" class="w-full h-full object-cover">
  </div>
  
  <!-- Gradient overlay: dark on left (text area) fading to transparent on right (pattern area) -->
  <div class="absolute inset-0 bg-gradient-to-r from-blue-900/90 from-50% via-blue-900/50 via-70% to-transparent"></div>
  
  <!-- Content -->
  <div class="relative z-10">
    <h2 class="text-2xl sm:text-3xl font-bold text-white mb-2">
      Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> 👋
    </h2>
    <p class="text-blue-100 text-sm sm:text-base">Here's what's happening with your equipment requests today.</p>
  </div>
</div>


    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
      
      <!-- Total Equipment -->
      <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-sm text-gray-600 font-medium mb-2">Total Equipment</p>
            <p class="text-4xl font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($total_eq); ?></p>
            <p class="text-sm text-blue-600 font-medium">All items</p>
          </div>
          <div class="w-14 h-14 rounded-xl bg-blue-500 flex items-center justify-center flex-shrink-0">
            <i data-feather="box" class="w-7 h-7 text-white"></i>
          </div>
        </div>
      </div>

      <!-- Available Items -->
      <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-sm text-gray-600 font-medium mb-2">Available Items</p>
            <p class="text-4xl font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($available_eq); ?></p>
            <p class="text-sm text-green-600 font-medium">Ready to use</p>
          </div>
          <div class="w-14 h-14 rounded-xl bg-green-500 flex items-center justify-center flex-shrink-0">
            <i data-feather="check-circle" class="w-7 h-7 text-white"></i>
          </div>
        </div>
      </div>

      <!-- Pending Requests -->
      <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-sm text-gray-600 font-medium mb-2">Pending Requests</p>
            <p class="text-4xl font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($pending_req); ?></p>
            <p class="text-sm text-orange-600 font-medium">Needs review</p>
          </div>
          <div class="w-14 h-14 rounded-xl bg-orange-500 flex items-center justify-center flex-shrink-0">
            <i data-feather="clock" class="w-7 h-7 text-white"></i>
          </div>
        </div>
      </div>

      <!-- Currently Borrowed -->
      <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-sm text-gray-600 font-medium mb-2">Currently Borrowed</p>
            <p class="text-4xl font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($borrowed_eq); ?></p>
            <p class="text-sm text-purple-600 font-medium">In use</p>
          </div>
          <div class="w-14 h-14 rounded-xl bg-purple-500 flex items-center justify-center flex-shrink-0">
            <i data-feather="archive" class="w-7 h-7 text-white"></i>
          </div>
        </div>
      </div>

    </div>

    <!-- Quick Actions -->
    <div class="mb-8">
      <h3 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h3>
      <div class="flex flex-wrap gap-4">
        <a href="inventory.php" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold transition-colors shadow-sm">
          <i data-feather="plus" class="w-5 h-5"></i>
          <span>Add Equipment</span>
        </a>
        <a href="users_tab.php" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-semibold transition-colors shadow-sm">
          <i data-feather="users" class="w-5 h-5"></i>
          <span>Manage Users</span>
        </a>
        <a href="requests_tab.php" class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-xl font-semibold transition-colors shadow-sm">
          <i data-feather="clipboard" class="w-5 h-5"></i>
          <span>Review Requests</span>
        </a>
      </div>
    </div>

    <!-- Tables Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

      <!-- Upcoming Due Returns -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
          <h4 class="text-lg font-bold text-gray-900">Upcoming Due Returns</h4>
          <span class="text-sm text-blue-600 font-semibold">Due within 3</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Borrower</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Equipment</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Qty</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Due Date</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php if($upcoming_due && $upcoming_due->num_rows): ?>
                <?php while($r = $upcoming_due->fetch_assoc()): ?>
                  <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($r['user_name']); ?></td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($r['equipment_name']); ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?php echo (int)$r['qty']; ?></td>
                    <td class="px-6 py-4 text-sm font-semibold text-blue-600"><?php echo date("M d, Y", strtotime($r['expected_return_date'])); ?></td>
                    <td class="px-6 py-4">
                      <span class="inline-block px-3 py-1 rounded-lg bg-blue-100 text-blue-700 text-xs font-semibold">Upcoming</span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">No upcoming returns in the next 3 days.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Past Due Returns -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
          <h4 class="text-lg font-bold text-gray-900">Past Due Returns</h4>
          <span class="text-sm text-red-600 font-semibold">Overdue</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Borrower</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Equipment</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Qty</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Due Date</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php if($past_due && $past_due->num_rows): ?>
                <?php while($r = $past_due->fetch_assoc()): ?>
                  <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($r['user_name']); ?></td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($r['equipment_name']); ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?php echo (int)$r['qty']; ?></td>
                    <td class="px-6 py-4 text-sm font-semibold text-red-600"><?php echo date("M d, Y", strtotime($r['expected_return_date'])); ?></td>
                    <td class="px-6 py-4">
                      <span class="inline-block px-3 py-1 rounded-lg bg-red-100 text-red-700 text-xs font-semibold">Overdue</span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">No past due items.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>

  </main>

  <!-- Footer -->
  <footer class="ml-16 md:ml-64 mt-8">
    <?php include "footer_admin.php"; ?>
  </footer>

  <script>
    feather.replace();
  </script>

</body>
</html>