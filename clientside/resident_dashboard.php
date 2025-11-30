<?php
session_start();
include "../db.php";

// Redirect if not logged in or not resident
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'resident') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get avatar from session or default
$avatar = !empty($_SESSION['avatar']) 
    ? "../photos/avatars/" . $_SESSION['avatar'] 
    : "../photos/avatars/default.png";

// --- STAT COUNTS ---
$sql_pending   = "SELECT COUNT(*) AS c FROM borrow_requests WHERE user_id=$user_id AND status='pending'";
$sql_active    = "SELECT COUNT(*) AS c FROM borrow_requests WHERE user_id=$user_id AND status='approved'";
$sql_borrowed  = "SELECT COUNT(*) AS c FROM borrow_logs WHERE user_id=$user_id AND actual_return_date IS NULL";
$sql_completed = "SELECT COUNT(*) AS c FROM borrow_logs WHERE user_id=$user_id AND actual_return_date IS NOT NULL";

$pending   = $conn->query($sql_pending)->fetch_assoc()['c'];
$active    = $conn->query($sql_active)->fetch_assoc()['c'];
$borrowed  = $conn->query($sql_borrowed)->fetch_assoc()['c'];
$completed = $conn->query($sql_completed)->fetch_assoc()['c'];

// --- RECENT REQUESTS ---
$recent_sql = "SELECT br.id, e.name AS equipment, br.status, br.created_at
               FROM borrow_requests br
               JOIN equipment e ON br.equipment_id = e.id
               WHERE br.user_id=$user_id
               ORDER BY br.created_at DESC LIMIT 5";
$recent = $conn->query($recent_sql);

// --- UPCOMING RETURNS ---
$upcoming_sql = "SELECT bl.id, e.name AS equipment, bl.expected_return_date
                 FROM borrow_logs bl
                 JOIN equipment e ON bl.equipment_id = e.id
                 WHERE bl.user_id=$user_id AND bl.actual_return_date IS NULL
                 ORDER BY bl.expected_return_date ASC LIMIT 5";
$upcoming = $conn->query($upcoming_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>E-Borrow System | Resident Dashboard</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
    
    body {
      font-family: 'Inter', sans-serif;
    }
    
    .stat-card {
      transition: all 0.3s ease;
    }
    
    .stat-card:hover {
      transform: translateY(-4px);
    }
  </style>
</head>
<body class="flex flex-col bg-gray-50 min-h-screen">

  <!-- Sidebar include -->
  <?php include "resident_sidebar.php"; ?>
  
  <!-- Header -->
  <?php 
    $page_title = "Dashboard"; 
    include "header.php"; 
  ?>

  <!-- Main Content -->
  <main class="flex-1 ml-16 md:ml-64 p-4 sm:p-6 lg:p-8 pt-24 md:pt-28 lg:pt-32">
    
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

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5 mb-8">
      
      <!-- Pending Requests -->
      <div class="stat-card bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-gray-100">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <p class="text-sm text-gray-600 font-medium mb-1">Pending Requests</p>
            <p class="text-3xl sm:text-4xl font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($pending); ?></p>
            <p class="text-xs sm:text-sm text-orange-600 font-medium">Awaiting approval</p>
          </div>
          <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-orange-500 flex items-center justify-center flex-shrink-0">
            <i data-feather="clock" class="w-6 h-6 sm:w-7 sm:h-7 text-white"></i>
          </div>
        </div>
      </div>

      <!-- Active Requests -->
      <div class="stat-card bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-gray-100">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <p class="text-sm text-gray-600 font-medium mb-1">Active Requests</p>
            <p class="text-3xl sm:text-4xl font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($active); ?></p>
            <p class="text-xs sm:text-sm text-blue-600 font-medium">Approved</p>
          </div>
          <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-blue-500 flex items-center justify-center flex-shrink-0">
            <i data-feather="check-circle" class="w-6 h-6 sm:w-7 sm:h-7 text-white"></i>
          </div>
        </div>
      </div>

      <!-- Currently Borrowed -->
      <div class="stat-card bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-gray-100">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <p class="text-sm text-gray-600 font-medium mb-1">Currently Borrowed</p>
            <p class="text-3xl sm:text-4xl font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($borrowed); ?></p>
            <p class="text-xs sm:text-sm text-green-600 font-medium">In your possession</p>
          </div>
          <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-green-500 flex items-center justify-center flex-shrink-0">
            <i data-feather="package" class="w-6 h-6 sm:w-7 sm:h-7 text-white"></i>
          </div>
        </div>
      </div>

      <!-- Completed -->
      <div class="stat-card bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-gray-100">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <p class="text-sm text-gray-600 font-medium mb-1">Completed</p>
            <p class="text-3xl sm:text-4xl font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($completed); ?></p>
            <p class="text-xs sm:text-sm text-purple-600 font-medium">Returned items</p>
          </div>
          <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-purple-500 flex items-center justify-center flex-shrink-0">
            <i data-feather="check-square" class="w-6 h-6 sm:w-7 sm:h-7 text-white"></i>
          </div>
        </div>
      </div>

    </div>

    <!-- Quick Actions -->
    <div class="mb-8">
      <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">Quick Actions</h3>
      <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
        <a href="browse_equipment.php" class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3.5 rounded-xl font-semibold transition-all shadow-sm hover:shadow-md">
          <i data-feather="search" class="w-5 h-5"></i>
          <span>Browse Equipment</span>
        </a>
        <a href="my_requests.php" class="flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white px-6 py-3.5 rounded-xl font-semibold transition-all shadow-sm hover:shadow-md">
          <i data-feather="file-text" class="w-5 h-5"></i>
          <span>My Requests</span>
        </a>
        <a href="my_borrowings.php" class="flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-6 py-3.5 rounded-xl font-semibold transition-all shadow-sm hover:shadow-md">
          <i data-feather="clock" class="w-5 h-5"></i>
          <span>Borrow History</span>
        </a>
      </div>
    </div>

    <!-- Recent Requests & Upcoming Returns Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

      <!-- Recent Requests -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
          <h4 class="text-base sm:text-lg font-bold text-gray-900">Recent Requests</h4>
          <span class="text-sm text-blue-600 font-medium">Latest activity</span>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gray-50 text-left">
                <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Equipment</th>
                <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php if ($recent->num_rows > 0): ?>
                <?php while ($r = $recent->fetch_assoc()): ?>
                  <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($r['equipment']); ?></td>
                    <td class="px-5 py-4 text-sm text-gray-600"><?php echo date("M d, Y", strtotime($r['created_at'])); ?></td>
                    <td class="px-5 py-4">
                      <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-semibold
                        <?php echo $r['status']=='pending'?'bg-yellow-100 text-yellow-800':
                                    ($r['status']=='approved'?'bg-green-100 text-green-800':
                                    ($r['status']=='rejected'?'bg-red-100 text-red-800':'bg-gray-100 text-gray-800')); ?>">
                        <?php echo ucfirst($r['status']); ?>
                      </span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="3" class="px-5 py-8 text-center text-gray-500 text-sm">No recent requests.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Upcoming Returns -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
          <h4 class="text-base sm:text-lg font-bold text-gray-900">Upcoming Returns</h4>
          <span class="text-sm text-orange-600 font-medium">Due soon</span>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gray-50 text-left">
                <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Equipment</th>
                <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Due Date</th>
                <th class="px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php if ($upcoming->num_rows > 0): ?>
                <?php while ($u = $upcoming->fetch_assoc()): ?>
                  <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($u['equipment']); ?></td>
                    <td class="px-5 py-4 text-sm font-semibold text-blue-600"><?php echo date("M d, Y", strtotime($u['expected_return_date'])); ?></td>
                    <td class="px-5 py-4">
                      <span class="inline-flex px-2.5 py-1 rounded-lg bg-blue-100 text-blue-800 text-xs font-semibold">Active</span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="3" class="px-5 py-8 text-center text-gray-500 text-sm">No upcoming returns.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>

  </main>

  <!-- Footer -->
  <footer class="ml-16 md:ml-64">
    <?php include "footer.php"; ?>
  </footer>

  <script>
    feather.replace();
  </script>

</body>
</html>