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
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              500: '#3b82f6',
              600: '#2563eb',
              700: '#1d4ed8'
            }
          }
        }
      }
    }
  </script>
  <style>
    /* subtle shadow for the slide panel backdrop when open */
    .panel-open-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.25);
      z-index: 40;
      opacity: 0;
      pointer-events: none;
      transition: opacity .25s ease;
    }
    .panel-open-backdrop.show {
      opacity: 1;
      pointer-events: auto;
    }
  </style>
</head>

<body class="flex bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen text-sm">

  <!-- Sidebar -->
  <?php include "admin_sidebar.php"; ?>
  <!-- Header -->
  <?php 
    $page_title = "Admin Dashboard"; 
    include "header_admin.php"; 
  ?>
  

  <!-- Main Content -->
  <main class="flex-1 ml-64 flex flex-col min-h-screen">

 

    <!-- Page Title -->
    <div class="px-8 py-6 border-b border-gray-200">
      <h2 class="text-2xl font-semibold text-gray-800">Admin Dashboard</h2>
    </div>

    <!-- Page Content -->
    <div class="flex-1 px-8 py-6">

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">
        <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600 mb-1">Total Equipment</p>
              <p class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($total_eq); ?></p>
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
              <p class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($available_eq); ?></p>
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
              <p class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($pending_req); ?></p>
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
              <p class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($borrowed_eq); ?></p>
              <p class="text-sm text-purple-600 font-medium">In use</p>
            </div>
            <div class="p-4 bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl shadow-lg">
              <i data-feather="archive" class="w-6 h-6"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="mb-10">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Quick Actions</h3>
        <div class="flex flex-wrap gap-4">
          <a href="inventory.php" class="group bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-2xl hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-lg transform hover:-translate-y-1 flex items-center gap-2">
            <i data-feather="plus" class="w-5 h-5"></i>
            <span class="font-medium">Add Equipment</span>
          </a>
          <a href="users_tab.php" class="group bg-gradient-to-r from-green-600 to-green-700 text-white px-6 py-3 rounded-2xl hover:from-green-700 hover:to-green-800 transition-all duration-300 shadow-lg transform hover:-translate-y-1 flex items-center gap-2">
            <i data-feather="users" class="w-5 h-5"></i>
            <span class="font-medium">Manage Users</span>
          </a>
          <a href="requests_tab.php" class="group bg-gradient-to-r from-purple-600 to-purple-700 text-white px-6 py-3 rounded-2xl hover:from-purple-700 hover:to-purple-800 transition-all duration-300 shadow-lg transform hover:-translate-y-1 flex items-center gap-2">
            <i data-feather="clipboard" class="w-5 h-5"></i>
            <span class="font-medium">Review Requests</span>
          </a>
        </div>
      </div>

      <!-- Upcoming & Past Due -->
      <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">

        <!-- Upcoming Due -->
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
          <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h4 class="text-lg font-semibold text-gray-800">Upcoming Due Returns</h4>
            <div class="text-sm text-gray-500">Due within 3 days</div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Borrower</th>
                  <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Equipment</th>
                  <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Qty</th>
                  <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Due Date</th>
                  <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <?php if($upcoming_due && $upcoming_due->num_rows): ?>
                  <?php while($r = $upcoming_due->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                      <td class="px-6 py-3"><?php echo htmlspecialchars($r['user_name']); ?></td>
                      <td class="px-6 py-3"><?php echo htmlspecialchars($r['equipment_name']); ?></td>
                      <td class="px-6 py-3"><?php echo (int)$r['qty']; ?></td>
                      <td class="px-6 py-3 font-medium text-blue-600"><?php echo date("M d, Y", strtotime($r['expected_return_date'])); ?></td>
                      <td class="px-6 py-3"><span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-sm font-medium">Upcoming</span></td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No upcoming returns in the next 3 days.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Past Due -->
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
          <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h4 class="text-lg font-semibold text-gray-800">Past Due Returns</h4>
            <div class="text-sm text-red-600">Overdue</div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Borrower</th>
                  <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Equipment</th>
                  <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Qty</th>
                  <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Due Date</th>
                  <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <?php if($past_due && $past_due->num_rows): ?>
                  <?php while($r = $past_due->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                      <td class="px-6 py-3"><?php echo htmlspecialchars($r['user_name']); ?></td>
                      <td class="px-6 py-3"><?php echo htmlspecialchars($r['equipment_name']); ?></td>
                      <td class="px-6 py-3"><?php echo (int)$r['qty']; ?></td>
                      <td class="px-6 py-3 font-medium text-red-600"><?php echo date("M d, Y", strtotime($r['expected_return_date'])); ?></td>
                      <td class="px-6 py-3"><span class="px-3 py-1 rounded-full bg-red-50 text-red-700 text-sm font-medium">Overdue</span></td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No past due items.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div> <!-- /Upcoming & Past Due -->

    </div> <!-- /Page Content -->

    <!-- Footer -->
    <footer >
      <?php include "footer_admin.php"; ?>
    </footer>

  </main>

 


  <script>
    feather.replace();



  </script>




</body>
</html>