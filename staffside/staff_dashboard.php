<?php
session_start();
include "../db.php"; // adjust path to your db.php

// Redirect if not staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: ../login.php");
    exit();
}

// Set page title for header
$page_title = "Staff Dashboard";

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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $page_title; ?> - Barangay Alabang</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#eff6ff',
              100: '#dbeafe',
              500: '#3b82f6',
              600: '#2563eb',
              700: '#1d4ed8',
              900: '#1e3a8a'
            }
          }
        }
      }
    }
  </script>
</head>
<body class="flex bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">

  <!-- Sidebar -->
  <?php include "staff_sidebar.php"; ?>

  <!-- Main Content Wrapper -->
  <div class="flex-1 ml-64 flex flex-col">
    
    <!-- Header -->
    <?php include "header_staff.php"; ?>

    <!-- Main Content -->
    <main class="flex-1 p-8 mt-16">
      <!-- Welcome Section -->
      <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">
          Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!
        </h2>
        <p class="text-gray-600">Here's what's happening with your equipment lending system.</p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">
        <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 mb-1">Total Equipment</p>
              <p class="text-3xl font-bold text-gray-900"><?php echo $total_eq; ?></p>
              <p class="text-sm text-blue-600 font-medium">All items</p>
            </div>
            <div class="p-4 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl shadow-lg">
              <i data-feather="box" class="w-6 h-6"></i>
            </div>
          </div>
        </div>

        <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 mb-1">Available Items</p>
              <p class="text-3xl font-bold text-gray-900"><?php echo $available_eq; ?></p>
              <p class="text-sm text-green-600 font-medium">Ready to use</p>
            </div>
            <div class="p-4 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl shadow-lg">
              <i data-feather="check-circle" class="w-6 h-6"></i>
            </div>
          </div>
        </div>

        <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 mb-1">Pending Requests</p>
              <p class="text-3xl font-bold text-gray-900"><?php echo $pending_req; ?></p>
              <p class="text-sm text-yellow-600 font-medium">Needs review</p>
            </div>
            <div class="p-4 bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-2xl shadow-lg">
              <i data-feather="clock" class="w-6 h-6"></i>
            </div>
          </div>
        </div>

        <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 mb-1">Currently Borrowed</p>
              <p class="text-3xl font-bold text-gray-900"><?php echo $borrowed_eq; ?></p>
              <p class="text-sm text-purple-600 font-medium">In use</p>
            </div>
            <div class="p-4 bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl shadow-lg">
              <i data-feather="archive" class="w-6 h-6"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions (Staff Version - No User Management) -->
      <div class="mb-10">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-4">
          <a href="staff_inventory.php" class="group bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-4 rounded-2xl hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center gap-3">
            <i data-feather="plus" class="w-5 h-5"></i>
            <span class="font-medium">Add Equipment</span>
          </a>
          <a href="requests_tab.php" class="group bg-gradient-to-r from-purple-600 to-purple-700 text-white px-8 py-4 rounded-2xl hover:from-purple-700 hover:to-purple-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center gap-3">
            <i data-feather="clipboard" class="w-5 h-5"></i>
            <span class="font-medium">Review Requests</span>
          </a>
          <a href="schedule_booking.php" class="group bg-gradient-to-r from-green-600 to-green-700 text-white px-8 py-4 rounded-2xl hover:from-green-700 hover:to-green-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center gap-3">
            <i data-feather="calendar" class="w-5 h-5"></i>
            <span class="font-medium">Manage Schedule</span>
          </a>
          <a href="reports_tab.php" class="group bg-gradient-to-r from-orange-600 to-orange-700 text-white px-8 py-4 rounded-2xl hover:from-orange-700 hover:to-orange-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center gap-3">
            <i data-feather="bar-chart-2" class="w-5 h-5"></i>
            <span class="font-medium">View Reports</span>
          </a>
        </div>
      </div>

      <!-- Recent Equipment & Requests -->
      <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8">
        <!-- Recent Equipment -->
        <div class="bg-white/70 backdrop-blur-sm border border-white/20 rounded-3xl shadow-xl overflow-hidden">
          <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-semibold text-gray-800">Recent Equipment</h2>
              <div class="p-2 bg-blue-100 text-blue-600 rounded-xl">
                <i data-feather="package" class="w-5 h-5"></i>
              </div>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50/50">
                <tr>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Equipment</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Category</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Condition</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <?php while($eq = $recent_eq->fetch_assoc()): ?>
                  <tr class="hover:bg-gray-50/50 transition-colors duration-200">
                    <td class="px-6 py-4">
                      <div class="font-medium text-gray-900"><?php echo htmlspecialchars($eq['name']); ?></div>
                    </td>
                    <td class="px-6 py-4">
                      <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full font-medium">
                        <?php echo isset($eq['category']) ? htmlspecialchars($eq['category']) : 'Uncategorized'; ?>
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <span class="px-3 py-1 rounded-full text-sm font-medium <?php 
                        echo $eq['status']=='available'?'bg-green-100 text-green-800':
                             ($eq['status']=='borrowed'?'bg-yellow-100 text-yellow-800':'bg-red-100 text-red-800'); ?>">
                        <?php echo ucfirst($eq['status']); ?>
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <span class="text-gray-700"><?php echo isset($eq['condition']) ? htmlspecialchars($eq['condition']) : 'Unknown'; ?></span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Requests -->
        <div class="bg-white/70 backdrop-blur-sm border border-white/20 rounded-3xl shadow-xl overflow-hidden">
          <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-semibold text-gray-800">Recent Requests</h2>
              <div class="p-2 bg-purple-100 text-purple-600 rounded-xl">
                <i data-feather="inbox" class="w-5 h-5"></i>
              </div>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50/50">
                <tr>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">User</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Equipment</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Date</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <?php while($req = $recent_req->fetch_assoc()): ?>
                  <tr class="hover:bg-gray-50/50 transition-colors duration-200">
                    <td class="px-6 py-4">
                      <div class="font-medium text-gray-900"><?php echo htmlspecialchars($req['user_name']); ?></div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-gray-700"><?php echo htmlspecialchars($req['equipment']); ?></div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm text-gray-600"><?php echo date("M d, Y", strtotime($req['created_at'])); ?></div>
                    </td>
                    <td class="px-6 py-4">
                      <span class="px-3 py-1 rounded-full text-sm font-medium <?php 
                        echo $req['status']=='pending'?'bg-yellow-100 text-yellow-800':
                             ($req['status']=='approved'?'bg-green-100 text-green-800':'bg-red-100 text-red-800'); ?>">
                        <?php echo ucfirst($req['status']); ?>
                      </span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </main>

    <!-- Footer -->
    <?php include "footer_staff.php"; ?>

  </div>

  <script>
    feather.replace();
  </script>
  
</body>
</html>