<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../landingpage/db.php"; // Adjusted path to db.php

// Redirect if not logged in or not resident
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'resident') {
    header("Location: ../landingpage/login.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle new request
$msg = '';
if (isset($_POST['request'])) {
    $equipment_id = intval($_POST['equipment_id']);
    $qty = intval($_POST['qty']);
    $borrow_date = $_POST['borrow_date'];
    $return_date = $_POST['return_date'];

    $sql = "INSERT INTO borrow_requests (user_id, equipment_id, qty, borrow_date, return_date, status, created_at)
            VALUES ($user_id, $equipment_id, $qty, '$borrow_date', '$return_date', 'pending', NOW())";
    $conn->query($sql);
    $msg = "Request submitted successfully!";
}

// Search + filter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

$sql = "SELECT * FROM equipment WHERE 1=1";
if ($search) $sql .= " AND name LIKE '%$search%'";
if ($status_filter) $sql .= " AND status='$status_filter'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Browse Equipment | E-Borrow System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="flex bg-gray-100 min-h-screen">

  <!-- Sidebar -->
  <?php include "resident_sidebar.php"; ?>

  <!-- Main -->
  <main class="flex-1 p-8 ml-64">
    <h1 class="text-2xl font-bold mb-6">Browse Equipment</h1>

    <?php if (!empty($msg)) echo "<p class='text-green-600 mb-4'>$msg</p>"; ?>

    <!-- Search + Filter -->
    <form method="get" class="flex flex-col md:flex-row gap-4 mb-6">
      <input type="text" name="search" placeholder="Search equipment..." 
             value="<?php echo htmlspecialchars($search); ?>"
             class="flex-1 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
      <select name="status" 
              class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="">All</option>
        <option value="available" <?php if($status_filter=="available") echo "selected"; ?>>Available</option>
        <option value="maintenance" <?php if($status_filter=="maintenance") echo "selected"; ?>>Under Maintenance</option>
      </select>
      <button type="submit" 
              class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
        Filter
      </button>
    </form>

    <!-- Equipment Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition flex flex-col">
          
          <!-- Equipment Image -->
          <?php if (!empty($row['image'])): ?>
            <img src="../photos/<?php echo htmlspecialchars($row['image']); ?>" 
                 class="w-full h-48 object-cover rounded mb-3 cursor-pointer hover:scale-105 transition-transform"
                 alt="<?php echo htmlspecialchars($row['name']); ?>"
                 onclick="openImageModal('<?php echo htmlspecialchars($row['image']); ?>')">
          <?php endif; ?>
          
          <h2 class="text-lg font-semibold mb-1"><?php echo htmlspecialchars($row['name']); ?></h2>
          <p class="text-gray-500 mb-2 text-sm"><?php echo htmlspecialchars($row['description']); ?></p>
          <p class="mb-1"><span class="font-bold">Qty:</span> <?php echo $row['quantity']; ?></p>
          <p class="mb-4">
            <span class="px-2 py-1 rounded text-sm 
              <?php echo $row['status']=="available"?"bg-green-100 text-green-700":"bg-yellow-100 text-yellow-700"; ?>">
              <?php echo ucfirst($row['status']); ?>
            </span>
          </p>

          <?php if ($row['status']=="available"): ?>
            <button onclick="openRequestModal(<?php echo $row['id']; ?>)"
                    class="mt-auto w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
              Request
            </button>

            <!-- Request Modal -->
            <div id="requestModal<?php echo $row['id']; ?>" 
                 class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
              <div class="bg-white p-6 rounded-xl shadow-lg w-96 relative">
                <button onclick="closeRequestModal(<?php echo $row['id']; ?>)" 
                        class="absolute top-2 right-2 text-gray-600 text-xl font-bold">&times;</button>
                
                <h2 class="text-xl font-bold mb-4"><?php echo htmlspecialchars($row['name']); ?></h2>
                <?php if (!empty($row['image'])): ?>
                  <img src="../photos/<?php echo htmlspecialchars($row['image']); ?>" 
                       class="w-full h-48 object-cover rounded mb-3">
                <?php endif; ?>
                <p class="text-gray-500 mb-2 text-sm"><?php echo htmlspecialchars($row['description']); ?></p>
                <p class="mb-2"><span class="font-bold">Available Qty:</span> <?php echo $row['quantity']; ?></p>

                <form method="post" class="space-y-2">
                  <input type="hidden" name="equipment_id" value="<?php echo $row['id']; ?>">
                  <input type="number" name="qty" min="1" max="<?php echo $row['quantity']; ?>" value="1"
                         class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                  <input type="date" name="borrow_date" 
                         class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                  <input type="date" name="return_date" 
                         class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
                  <button type="submit" name="request" 
                          class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                    Submit Request
                  </button>
                </form>
              </div>
            </div>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    </div>
  </main>

  <!-- Image Modal -->
  <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="relative">
      <button onclick="closeImageModal()" 
              class="absolute top-2 right-2 text-white text-2xl font-bold">&times;</button>
      <img id="modalImage" src="" class="max-h-[80vh] max-w-[90vw] rounded shadow-lg">
    </div>
  </div>

  <script>
    feather.replace();

    // Image modal
    function openImageModal(src) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        modalImg.src = '../photos/' + src;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    document.getElementById('imageModal').addEventListener('click', function(e) {
        if (e.target === this) closeImageModal();
    });

    // Request modals
    function openRequestModal(id) {
        document.getElementById('requestModal'+id).classList.remove('hidden');
        document.getElementById('requestModal'+id).classList.add('flex');
    }
    function closeRequestModal(id) {
        document.getElementById('requestModal'+id).classList.add('hidden');
        document.getElementById('requestModal'+id).classList.remove('flex');
    }
    document.querySelectorAll('[id^=requestModal]').forEach(modal => {
        modal.addEventListener('click', function(e){
            if (e.target === this) closeRequestModal(this.id.replace('requestModal',''));
        });
    });
  </script>

</body>
</html>
