<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../landingpage/db.php"; // Adjusted path

// Redirect if not logged in or not resident
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'resident') {
    header("Location: ../landingpage/login.php"); // Adjusted path
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT br.*, e.name AS equipment
        FROM borrow_requests br
        JOIN equipment e ON br.equipment_id = e.id
        WHERE br.user_id=$user_id
        ORDER BY br.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Requests | E-Borrow System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="flex bg-gray-100 min-h-screen">

  <!-- Sidebar include -->
  <?php include "resident_sidebar.php"; ?>

  <!-- Main Content -->
  <main class="flex-1 ml-64 p-10">
    <h1 class="text-3xl font-bold mb-8 text-gray-800">My Requests</h1>

    <div class="bg-white rounded-2xl shadow-md overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Equipment</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Qty</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Borrow Date</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Return Date</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 text-gray-800"><?php echo htmlspecialchars($row['equipment']); ?></td>
                <td class="px-6 py-4 text-gray-800"><?php echo $row['qty']; ?></td>
                <td class="px-6 py-4 text-gray-800"><?php echo date("M d, Y", strtotime($row['borrow_date'])); ?></td>
                <td class="px-6 py-4 text-gray-800"><?php echo date("M d, Y", strtotime($row['return_date'])); ?></td>
                <td class="px-6 py-4">
                  <span class="px-3 py-1 rounded-full text-sm font-medium
                    <?php echo $row['status']=="pending"?"bg-yellow-100 text-yellow-700":
                                ($row['status']=="approved"?"bg-green-100 text-green-700":
                                ($row['status']=="rejected"?"bg-red-100 text-red-700":"bg-gray-100 text-gray-700")); ?>">
                    <?php echo ucfirst($row['status']); ?>
                  </span>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="px-6 py-4 text-center text-gray-500">No requests found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <script>
    feather.replace();
  </script>
</body>
</html>
