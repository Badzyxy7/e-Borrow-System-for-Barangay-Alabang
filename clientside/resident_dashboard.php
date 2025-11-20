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
</head>
<body class="flex flex-col bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen text-sm">

  <!-- Sidebar include -->
  <?php include "resident_sidebar.php"; ?>
  
  <!-- Header -->
  <?php 
    $page_title = "Dashboard"; 
    include "header.php"; 
  ?>

  <!-- Main Content - Adjusted margin for mobile sidebar (ml-16) and desktop (md:ml-64) -->
  <main class="flex-1 ml-16 md:ml-64 p-4 sm:p-6 lg:p-10 pt-24 md:pt-28 lg:pt-32">
    <h2 class="text-xl sm:text-2xl font-semibold mb-6 sm:mb-8 text-gray-800">
      Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> 👋
    </h2>

    <!-- Stats Cards - Responsive grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 mb-8 sm:mb-10">
      <!-- Pending Requests Card -->
      <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs sm:text-sm font-medium text-gray-600 mb-1">Pending Requests</p>
            <p class="text-2xl sm:text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($pending); ?></p>
            <p class="text-xs sm:text-sm text-yellow-600 font-medium">Awaiting approval</p>
          </div>
          <div class="p-3 sm:p-4 bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-xl sm:rounded-2xl shadow-lg">
            <i data-feather="clock" class="w-5 h-5 sm:w-6 sm:h-6"></i>
          </div>
        </div>
      </div>

      <!-- Active Requests Card -->
      <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs sm:text-sm font-medium text-gray-600 mb-1">Active Requests</p>
            <p class="text-2xl sm:text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($active); ?></p>
            <p class="text-xs sm:text-sm text-blue-600 font-medium">Approved</p>
          </div>
          <div class="p-3 sm:p-4 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl sm:rounded-2xl shadow-lg">
            <i data-feather="check-circle" class="w-5 h-5 sm:w-6 sm:h-6"></i>
          </div>
        </div>
      </div>

      <!-- Currently Borrowed Card -->
      <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs sm:text-sm font-medium text-gray-600 mb-1">Currently Borrowed</p>
            <p class="text-2xl sm:text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($borrowed); ?></p>
            <p class="text-xs sm:text-sm text-green-600 font-medium">In your possession</p>
          </div>
          <div class="p-3 sm:p-4 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl sm:rounded-2xl shadow-lg">
            <i data-feather="archive" class="w-5 h-5 sm:w-6 sm:h-6"></i>
          </div>
        </div>
      </div>

      <!-- Completed Card -->
      <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-4 sm:p-6 rounded-2xl sm:rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs sm:text-sm font-medium text-gray-600 mb-1">Completed</p>
            <p class="text-2xl sm:text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($completed); ?></p>
            <p class="text-xs sm:text-sm text-purple-600 font-medium">Returned items</p>
          </div>
          <div class="p-3 sm:p-4 bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl sm:rounded-2xl shadow-lg">
            <i data-feather="check-square" class="w-5 h-5 sm:w-6 sm:h-6"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions - Responsive buttons -->
    <div class="mb-8 sm:mb-10">
      <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3">Quick Actions</h3>
      <div class="flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-4">
        <a href="browse_equipment.php" class="group bg-gradient-to-r from-blue-600 to-blue-700 text-white px-5 sm:px-6 py-3 rounded-xl sm:rounded-2xl hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-lg transform hover:-translate-y-1 flex items-center justify-center sm:justify-start gap-2">
          <i data-feather="search" class="w-4 h-4 sm:w-5 sm:h-5"></i>
          <span class="font-medium text-sm sm:text-base">Browse Equipment</span>
        </a>
        <a href="my_requests.php" class="group bg-gradient-to-r from-green-600 to-green-700 text-white px-5 sm:px-6 py-3 rounded-xl sm:rounded-2xl hover:from-green-700 hover:to-green-800 transition-all duration-300 shadow-lg transform hover:-translate-y-1 flex items-center justify-center sm:justify-start gap-2">
          <i data-feather="list" class="w-4 h-4 sm:w-5 sm:h-5"></i>
          <span class="font-medium text-sm sm:text-base">My Requests</span>
        </a>
        <a href="my_borrowings.php" class="group bg-gradient-to-r from-purple-600 to-purple-700 text-white px-5 sm:px-6 py-3 rounded-xl sm:rounded-2xl hover:from-purple-700 hover:to-purple-800 transition-all duration-300 shadow-lg transform hover:-translate-y-1 flex items-center justify-center sm:justify-start gap-2">
          <i data-feather="clock" class="w-4 h-4 sm:w-5 sm:h-5"></i>
          <span class="font-medium text-sm sm:text-base">Borrow History</span>
        </a>
      </div>
    </div>

    <!-- Recent & Upcoming - Stack on mobile, side-by-side on desktop -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 sm:gap-8">

      <!-- Recent Requests -->
      <div class="bg-white rounded-2xl sm:rounded-3xl shadow-xl overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between">
          <h4 class="text-base sm:text-lg font-semibold text-gray-800">Recent Requests</h4>
          <div class="text-xs sm:text-sm text-gray-500">Latest activity</div>
        </div>

        <!-- Mobile: Card view, Desktop: Table view -->
        <div class="block sm:hidden">
          <?php if ($recent->num_rows > 0): ?>
            <?php $recent->data_seek(0); // Reset pointer ?>
            <?php while ($r = $recent->fetch_assoc()): ?>
              <div class="p-4 border-b border-gray-100 last:border-0">
                <div class="flex justify-between items-start mb-2">
                  <span class="font-medium text-gray-900 text-sm"><?php echo htmlspecialchars($r['equipment']); ?></span>
                  <span class="px-2 py-1 rounded-full text-xs font-medium
                    <?php echo $r['status']=='pending'?'bg-yellow-50 text-yellow-700':
                                ($r['status']=='approved'?'bg-green-50 text-green-700':
                                ($r['status']=='rejected'?'bg-red-50 text-red-700':'bg-gray-50 text-gray-700')); ?>">
                    <?php echo ucfirst($r['status']); ?>
                  </span>
                </div>
                <p class="text-xs text-gray-600"><?php echo date("M d, Y", strtotime($r['created_at'])); ?></p>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="p-4 text-center text-gray-500 text-sm">No recent requests.</div>
          <?php endif; ?>
        </div>

        <!-- Desktop: Table view -->
        <div class="hidden sm:block overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 lg:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-700">Equipment</th>
                <th class="px-4 lg:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-700">Date</th>
                <th class="px-4 lg:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-700">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php $recent->data_seek(0); // Reset pointer ?>
              <?php if ($recent->num_rows > 0): ?>
                <?php while ($r = $recent->fetch_assoc()): ?>
                  <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 lg:px-6 py-3 font-medium text-gray-900 text-sm"><?php echo htmlspecialchars($r['equipment']); ?></td>
                    <td class="px-4 lg:px-6 py-3 text-gray-600 text-sm"><?php echo date("M d, Y", strtotime($r['created_at'])); ?></td>
                    <td class="px-4 lg:px-6 py-3">
                      <span class="px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium
                        <?php echo $r['status']=='pending'?'bg-yellow-50 text-yellow-700':
                                    ($r['status']=='approved'?'bg-green-50 text-green-700':
                                    ($r['status']=='rejected'?'bg-red-50 text-red-700':'bg-gray-50 text-gray-700')); ?>">
                        <?php echo ucfirst($r['status']); ?>
                      </span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="3" class="px-4 lg:px-6 py-4 text-center text-gray-500 text-sm">No recent requests.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Upcoming Returns -->
      <div class="bg-white rounded-2xl sm:rounded-3xl shadow-xl overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between">
          <h4 class="text-base sm:text-lg font-semibold text-gray-800">Upcoming Returns</h4>
          <div class="text-xs sm:text-sm text-blue-600">Due soon</div>
        </div>

        <!-- Mobile: Card view -->
        <div class="block sm:hidden">
          <?php if ($upcoming->num_rows > 0): ?>
            <?php $upcoming->data_seek(0); // Reset pointer ?>
            <?php while ($u = $upcoming->fetch_assoc()): ?>
              <div class="p-4 border-b border-gray-100 last:border-0">
                <div class="flex justify-between items-start mb-2">
                  <span class="font-medium text-gray-900 text-sm"><?php echo htmlspecialchars($u['equipment']); ?></span>
                  <span class="px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-medium">Active</span>
                </div>
                <p class="text-xs font-medium text-blue-600"><?php echo date("M d, Y", strtotime($u['expected_return_date'])); ?></p>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="p-4 text-center text-gray-500 text-sm">No upcoming returns.</div>
          <?php endif; ?>
        </div>

        <!-- Desktop: Table view -->
        <div class="hidden sm:block overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 lg:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-700">Equipment</th>
                <th class="px-4 lg:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-700">Due Date</th>
                <th class="px-4 lg:px-6 py-3 text-left text-xs sm:text-sm font-medium text-gray-700">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php $upcoming->data_seek(0); // Reset pointer ?>
              <?php if ($upcoming->num_rows > 0): ?>
                <?php while ($u = $upcoming->fetch_assoc()): ?>
                  <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 lg:px-6 py-3 font-medium text-gray-900 text-sm"><?php echo htmlspecialchars($u['equipment']); ?></td>
                    <td class="px-4 lg:px-6 py-3 font-medium text-blue-600 text-sm"><?php echo date("M d, Y", strtotime($u['expected_return_date'])); ?></td>
                    <td class="px-4 lg:px-6 py-3">
                      <span class="px-2 sm:px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs sm:text-sm font-medium">Active</span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="3" class="px-4 lg:px-6 py-4 text-center text-gray-500 text-sm">No upcoming returns.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div> <!-- /Recent & Upcoming -->

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