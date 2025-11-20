<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../db.php"; // Adjust this path based on your structure

// Redirect if not staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: ../login.php");
    exit();
}

// Set page title for header
$page_title = "Borrow Requests";

if (isset($_POST['action'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];

    // Get request details first
    $req = $conn->query("SELECT * FROM borrow_requests WHERE id=$id")->fetch_assoc();

    if ($action == "approve") {
        $conn->query("UPDATE borrow_requests SET status='approved' WHERE id=$id");

        // Make equipment unavailable automatically
        $conn->query("UPDATE equipment SET status='unavailable' WHERE id={$req['equipment_id']}");

    } elseif ($action == "decline") {
        $reason = $conn->real_escape_string($_POST['reason']);
        $conn->query("UPDATE borrow_requests SET status='declined', rejection_reason='$reason' WHERE id=$id");

    } elseif ($action == "picked_up") {
        $conn->query("UPDATE borrow_requests SET status='picked_up' WHERE id=$id");

        // Insert into borrow_logs when client picks up the item
        $conn->query("
            INSERT INTO borrow_logs 
            (request_id, user_id, equipment_id, qty, borrow_date, actual_pickup_date, expected_return_date, log_created_at)
            VALUES
            ({$req['id']}, {$req['user_id']}, {$req['equipment_id']}, {$req['qty']}, '{$req['borrow_date']}', NOW(), '{$req['return_date']}', NOW())
        ");

        // Ensure equipment is still unavailable
        $conn->query("UPDATE equipment SET status='unavailable' WHERE id={$req['equipment_id']}");

    } elseif ($action == "returned") {
        $condition_notes = $conn->real_escape_string($_POST['condition_notes']);
        $conn->query("UPDATE borrow_requests SET status='returned' WHERE id=$id");

        // Update borrow_logs with actual return and condition notes
        $conn->query("
            UPDATE borrow_logs 
            SET actual_return_date=NOW(), staff_checked_condition='checked', condition_notes='$condition_notes'
            WHERE request_id={$req['id']}
        ");

        // Make equipment available again
        $conn->query("UPDATE equipment SET status='available' WHERE id={$req['equipment_id']}");
    }
}

// Filters
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

$sql = "SELECT br.*, e.name AS equipment_name, u.name AS requester_name
        FROM borrow_requests br 
        JOIN equipment e ON br.equipment_id = e.id
        JOIN users u ON br.user_id = u.id
        WHERE 1=1";

if ($search) $sql .= " AND (e.name LIKE '%$search%' OR u.name LIKE '%$search%')";
if ($status_filter) $sql .= " AND br.status='$status_filter'";

$sql .= " ORDER BY br.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo $page_title; ?> - Barangay Alabang</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
</head>

<body class="flex bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen text-sm">

  <!-- Sidebar -->
  <?php include "staff_sidebar.php"; ?>

  <!-- Main Content Wrapper -->
  <div class="flex-1 ml-64 flex flex-col">
    
    <!-- Header -->
    <?php include "header_staff.php"; ?>

    <!-- Main Content -->
    <main class="flex-1 pt-16 flex flex-col min-h-screen">

      <!-- Page Title -->
      <div class="px-8 py-6 border-b border-gray-200">
        <h2 class="text-2xl font-semibold text-gray-800">All Borrow Requests</h2>
      </div>

      <!-- Page Content -->
      <div class="flex-1 px-8 py-6">

        <!-- Search + Filter -->
        <form method="get" class="flex flex-col md:flex-row gap-4 mb-6">
          <input type="text" name="search" placeholder="Search by name or equipment..." 
                 value="<?php echo htmlspecialchars($search); ?>"
                 class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm">
          <select name="status" 
                  class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm">
            <option value="">All Status</option>
            <option value="pending" <?php if($status_filter=="pending") echo "selected"; ?>>Pending</option>
            <option value="approved" <?php if($status_filter=="approved") echo "selected"; ?>>Approved</option>
            <option value="picked_up" <?php if($status_filter=="picked_up") echo "selected"; ?>>Picked Up</option>
            <option value="returned" <?php if($status_filter=="returned") echo "selected"; ?>>Returned</option>
            <option value="declined" <?php if($status_filter=="declined") echo "selected"; ?>>Declined</option>
          </select>
          <button type="submit" 
                  class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition-all duration-300 shadow-lg flex items-center gap-2">
            <i data-feather="search" class="w-5 h-5"></i>
            <span class="font-medium">Filter</span>
          </button>
        </form>

        <!-- Requests Table -->
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-4 text-sm font-semibold text-gray-700">Borrower</th>
                  <th class="px-6 py-4 text-sm font-semibold text-gray-700">Equipment</th>
                  <th class="px-6 py-4 text-sm font-semibold text-gray-700">Quantity</th>
                  <th class="px-6 py-4 text-sm font-semibold text-gray-700">Borrow Period</th>
                  <th class="px-6 py-4 text-sm font-semibold text-gray-700">Description</th>
                  <th class="px-6 py-4 text-sm font-semibold text-gray-700">Status</th>
                  <th class="px-6 py-4 text-sm font-semibold text-gray-700">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <?php if($result->num_rows > 0): ?>
                  <?php while ($row = $result->fetch_assoc()): ?>
                  <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 font-medium text-gray-800">
                      <?php echo htmlspecialchars($row['requester_name']); ?>
                    </td>
                    <td class="px-6 py-4 text-gray-700">
                      <?php echo htmlspecialchars($row['equipment_name']); ?>
                    </td>
                    <td class="px-6 py-4">
                      <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full font-medium">
                        <?php echo $row['qty']; ?>
                      </span>
                    </td>
                    <td class="px-6 py-4 text-gray-600 text-sm">
                      <div class="flex items-center gap-2">
                        <span><?php echo date('M d, Y', strtotime($row['borrow_date'])); ?></span>
                        <i data-feather="arrow-right" class="w-4 h-4"></i>
                        <span><?php echo date('M d, Y', strtotime($row['return_date'])); ?></span>
                      </div>
                    </td>
                    <td class="px-6 py-4 text-gray-700 text-sm max-w-xs">
                      <div class="truncate" title="<?php echo htmlspecialchars($row['description']); ?>">
                        <?php echo !empty($row['description']) 
                          ? htmlspecialchars($row['description']) 
                          : '<span class="italic text-gray-400">No description</span>'; ?>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <?php
                      // Check if return has been requested (with error handling)
                      $return_requested = false;
                      try {
                          $check_return = $conn->query("SELECT return_requested FROM borrow_logs WHERE request_id={$row['id']}");
                          if ($check_return) {
                              $return_data = $check_return->fetch_assoc();
                              $return_requested = $return_data && $return_data['return_requested'] == 1;
                          }
                      } catch (Exception $e) {
                          // Column doesn't exist yet, treat as not requested
                          $return_requested = false;
                      }
                      
                      if ($row['status'] == "pending") {
                          echo '<span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">Pending</span>';
                      } elseif ($row['status'] == "approved") {
                          echo '<span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">Approved</span>';
                      } elseif ($row['status'] == "picked_up") {
                          if ($return_requested) {
                              echo '<span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm font-medium">Picked Up</span>';
                              echo '<p class="text-xs text-orange-600 mt-1 font-medium flex items-center gap-1"><i data-feather="alert-circle" class="w-3 h-3"></i> Return requested</p>';
                          } else {
                              echo '<span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">Picked Up</span>';
                          }
                      } elseif ($row['status'] == "returned") {
                          echo '<span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">Returned</span>';
                      } elseif ($row['status'] == "declined") {
                          echo '<span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">Declined</span>';
                          if (!empty($row['rejection_reason'])) {
                              echo '<p class="text-xs text-red-600 mt-1">Reason: '.htmlspecialchars($row['rejection_reason']).'</p>';
                          }
                      }
                      ?>
                    </td>

                    <td class="px-6 py-4">
                      <div class="flex gap-2">
                        <?php if ($row['status']=="pending"): ?>
                          <form method="post" class="inline">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" 
                                    title="Approve Request"
                                    class="p-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-md hover:shadow-lg">
                              <i data-feather="check" class="w-5 h-5"></i>
                            </button>
                          </form>
                          <button type="button" 
                                  onclick="openRejectModal(<?php echo $row['id']; ?>)" 
                                  title="Reject Request"
                                  class="p-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition shadow-md hover:shadow-lg">
                            <i data-feather="x" class="w-5 h-5"></i>
                          </button>
                        <?php elseif ($row['status']=="approved"): ?>
                          <form method="post" class="inline">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="action" value="picked_up">
                            <button type="submit" 
                                    title="Mark as Picked Up"
                                    class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-md hover:shadow-lg">
                              <i data-feather="package" class="w-5 h-5"></i>
                            </button>
                          </form>
                        <?php elseif ($row['status']=="picked_up"): ?>
                          <?php
                          // Check if user has requested return (with error handling)
                          $return_requested = false;
                          try {
                              $check_return = $conn->query("SELECT return_requested FROM borrow_logs WHERE request_id={$row['id']}");
                              if ($check_return) {
                                  $return_data = $check_return->fetch_assoc();
                                  $return_requested = $return_data && $return_data['return_requested'] == 1;
                              }
                          } catch (Exception $e) {
                              // Column doesn't exist yet, treat as not requested
                              $return_requested = false;
                          }
                          ?>
                          
                          <?php if ($return_requested): ?>
                            <button type="button" 
                                    onclick="openReturnModal(<?php echo $row['id']; ?>)" 
                                    title="Mark as Returned"
                                    class="p-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition shadow-md hover:shadow-lg">
                              <i data-feather="corner-down-left" class="w-5 h-5"></i>
                            </button>
                          <?php else: ?>
                            <button type="button" 
                                    disabled
                                    title="Waiting for user to request return"
                                    class="p-2 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed">
                              <i data-feather="corner-down-left" class="w-5 h-5"></i>
                            </button>
                          <?php endif; ?>
                        <?php else: ?>
                          <span class="text-gray-400 text-sm italic">-</span>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                      <i data-feather="inbox" class="w-12 h-12 mx-auto mb-3 text-gray-400"></i>
                      <p class="text-lg">No requests found.</p>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- Footer -->
      <?php include "footer_staff.php"; ?>

    </main>

  </div>

  <!-- Reject Modal -->
  <div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md">
      <div class="flex items-center gap-3 mb-6">
        <div class="p-3 bg-red-100 rounded-full">
          <i data-feather="x-circle" class="w-6 h-6 text-red-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Reject Request</h2>
      </div>
      <form method="post">
        <input type="hidden" name="id" id="rejectId">
        <input type="hidden" name="action" value="decline">
        
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection *</label>
          <textarea name="reason" placeholder="Please provide a reason for rejection..." required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none h-32"></textarea>
        </div>

        <div class="flex justify-end gap-3">
          <button type="button" onclick="closeRejectModal()" 
                  class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium transition">
            Cancel
          </button>
          <button type="submit" 
                  class="px-6 py-3 rounded-xl bg-red-600 text-white hover:bg-red-700 font-medium transition shadow-lg">
            Reject Request
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Return Modal -->
  <div id="returnModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md">
      <div class="flex items-center gap-3 mb-6">
        <div class="p-3 bg-purple-100 rounded-full">
          <i data-feather="check-circle" class="w-6 h-6 text-purple-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Mark as Returned</h2>
      </div>
      
      <p class="text-sm text-gray-600 mb-6">Please inspect the equipment and document its condition below:</p>
      
      <form method="post">
        <input type="hidden" name="id" id="returnId">
        <input type="hidden" name="action" value="returned">
        
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">Equipment Condition Notes *</label>
          <textarea name="condition_notes" placeholder="Describe the condition of the returned equipment..." required
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none h-32"></textarea>
          <p class="text-xs text-gray-500 mt-2">Note any damages, wear, or issues found during inspection.</p>
        </div>

        <div class="flex justify-end gap-3">
          <button type="button" onclick="closeReturnModal()" 
                  class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium transition">
            Cancel
          </button>
          <button type="submit" 
                  class="px-6 py-3 rounded-xl bg-purple-600 text-white hover:bg-purple-700 font-medium transition shadow-lg">
            Confirm Return
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    feather.replace();
    
    function openRejectModal(id) {
      document.getElementById('rejectId').value = id;
      document.getElementById('rejectModal').classList.remove('hidden');
      document.getElementById('rejectModal').classList.add('flex');
    }
    
    function closeRejectModal() {
      document.getElementById('rejectModal').classList.add('hidden');
      document.getElementById('rejectModal').classList.remove('flex');
    }
    
    function openReturnModal(id) {
      document.getElementById('returnId').value = id;
      document.getElementById('returnModal').classList.remove('hidden');
      document.getElementById('returnModal').classList.add('flex');
    }
    
    function closeReturnModal() {
      document.getElementById('returnModal').classList.add('hidden');
      document.getElementById('returnModal').classList.remove('flex');
    }

    // Close modals when clicking outside
    document.getElementById('rejectModal').addEventListener('click', function(e) {
      if (e.target === this) closeRejectModal();
    });
    
    document.getElementById('returnModal').addEventListener('click', function(e) {
      if (e.target === this) closeReturnModal();
    });
  </script>

</body>
</html>