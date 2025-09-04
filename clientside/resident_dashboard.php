<?php
session_start();
include "../landingpage/db.php"; // db.php is in landingpage folder

// Redirect if not logged in or not resident
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'resident') {
    header("Location: ../landingpage/login.php"); // login.php is in landingpage
    exit();
}

$user_id = $_SESSION['user_id'];

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
  <title>Resident Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="flex bg-gray-100 min-h-screen">

  <!-- Sidebar include -->
  <?php include "resident_sidebar.php"; ?>

  <!-- Main Content -->
  <main class="flex-1 ml-64 p-10">
    <h1 class="text-3xl font-bold mb-8 text-gray-800">
      Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> 👋
    </h1>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
      <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-yellow-100 text-yellow-600 rounded-full">
            <i data-feather="clock"></i>
          </div>
          <div>
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold"><?php echo $pending; ?></p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-blue-100 text-blue-600 rounded-full">
            <i data-feather="check-circle"></i>
          </div>
          <div>
            <p class="text-sm text-gray-500">Active</p>
            <p class="text-2xl font-bold"><?php echo $active; ?></p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-green-100 text-green-600 rounded-full">
            <i data-feather="archive"></i>
          </div>
          <div>
            <p class="text-sm text-gray-500">Borrowed</p>
            <p class="text-2xl font-bold"><?php echo $borrowed; ?></p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
        <div class="flex items-center gap-4">
          <div class="p-3 bg-purple-100 text-purple-600 rounded-full">
            <i data-feather="check-square"></i>
          </div>
          <div>
            <p class="text-sm text-gray-500">Completed</p>
            <p class="text-2xl font-bold"><?php echo $completed; ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent + Upcoming -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Requests -->
      <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
        <h2 class="text-lg font-semibold mb-4 text-gray-700">Recent Requests</h2>
        <ul class="space-y-3">
          <?php if ($recent->num_rows > 0): ?>
            <?php while ($r = $recent->fetch_assoc()): ?>
              <li class="flex justify-between items-center border-b pb-2 last:border-0">
                <span class="text-gray-800"><?php echo htmlspecialchars($r['equipment']); ?></span>
                <span class="text-sm px-2 py-1 rounded-full 
                  <?php echo $r['status']=='pending'?'bg-yellow-100 text-yellow-700':
                              ($r['status']=='approved'?'bg-green-100 text-green-700':
                              ($r['status']=='rejected'?'bg-red-100 text-red-700':'bg-gray-100 text-gray-700')); ?>">
                  <?php echo ucfirst($r['status']); ?>
                </span>
              </li>
            <?php endwhile; ?>
          <?php else: ?>
            <li class="text-gray-500">No recent requests.</li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- Upcoming Returns -->
      <div class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">
        <h2 class="text-lg font-semibold mb-4 text-gray-700">Upcoming Returns</h2>
        <ul class="space-y-3">
          <?php if ($upcoming->num_rows > 0): ?>
            <?php while ($u = $upcoming->fetch_assoc()): ?>
              <li class="flex justify-between items-center border-b pb-2 last:border-0">
                <span class="text-gray-800"><?php echo htmlspecialchars($u['equipment']); ?></span>
                <span class="text-sm text-gray-600">
                  Due: <?php echo date("M d, Y", strtotime($u['expected_return_date'])); ?>
                </span>
              </li>
            <?php endwhile; ?>
          <?php else: ?>
            <li class="text-gray-500">No upcoming returns.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </main>

  <script>
    feather.replace();
  </script>
</body>
</html>
