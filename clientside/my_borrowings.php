<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../db.php";

// Redirect if not logged in or not resident
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'resident') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle return request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_return'])) {
    $borrow_id = intval($_POST['borrow_id']);
    
    // Update the borrow log to set return_requested = 1
    $update_sql = "UPDATE borrow_logs SET return_requested = 1 WHERE id = $borrow_id AND user_id = $user_id";
    
    if ($conn->query($update_sql)) {
        $_SESSION['success_message'] = "Return request submitted successfully! Staff will be notified shortly.";
    } else {
        $_SESSION['error_message'] = "Failed to submit return request.";
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Pagination configuration
$records_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page);

// Calculate offset
$offset = ($current_page - 1) * $records_per_page;

// Get total number of records
$count_sql = "SELECT COUNT(*) as total FROM borrow_logs WHERE user_id=$user_id";
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get paginated records with user details
$sql = "SELECT bl.*, 
        e.name AS equipment, 
        e.image AS equipment_image,
        u.name,
        CONCAT(u.street, ', ', u.barangay, ', ', u.landmark) AS address,
        u.email,
        u.avatar
        FROM borrow_logs bl
        JOIN equipment e ON bl.equipment_id = e.id
        JOIN users u ON bl.user_id = u.id
        WHERE bl.user_id = $user_id
        ORDER BY bl.borrow_date DESC
        LIMIT $records_per_page OFFSET $offset";

$result = $conn->query($sql);

// Helper function to determine status
function getBorrowStatus($row) {
    if ($row['actual_return_date']) {
        return [
            'label' => 'Returned',
            'bg' => 'bg-emerald-100',
            'text' => 'text-emerald-700',
            'border' => 'border-emerald-300',
            'icon' => 'check-circle'
        ];
    } elseif ($row['return_approved'] == 1) {
        return [
            'label' => 'Return Approved - Prepare for Pickup',
            'bg' => 'bg-purple-100',
            'text' => 'text-purple-700',
            'border' => 'border-purple-300',
            'icon' => 'package'
        ];
    } elseif ($row['return_requested'] == 1) {
        return [
            'label' => 'Return Requested',
            'bg' => 'bg-amber-100',
            'text' => 'text-amber-700',
            'border' => 'border-amber-300',
            'icon' => 'clock'
        ];
    } elseif ($row['actual_pickup_date']) {
        return [
            'label' => 'Delivered',
            'bg' => 'bg-blue-100',
            'text' => 'text-blue-700',
            'border' => 'border-blue-300',
            'icon' => 'truck'
        ];
    } elseif ($row['request_id']) {
        return [
            'label' => 'Approved - Pending Delivery',
            'bg' => 'bg-teal-100',
            'text' => 'text-teal-700',
            'border' => 'border-teal-300',
            'icon' => 'check'
        ];
    } else {
        return [
            'label' => 'Pending Approval',
            'bg' => 'bg-gray-100',
            'text' => 'text-gray-700',
            'border' => 'border-gray-300',
            'icon' => 'clock'
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Borrowings | E-Borrow System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <style>
    @keyframes slideIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .alert-animate {
      animation: slideIn 0.3s ease-out;
    }
    .modal {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      animation: fadeIn 0.3s ease;
    }
    .modal.active {
      display: flex;
      align-items: center;
      justify-content: center;
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    @keyframes slideUp {
      from { transform: translateY(20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    .modal-content {
      animation: slideUp 0.3s ease;
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen flex flex-col">

  <!-- Sidebar include -->
  <?php include "resident_sidebar.php"; ?>

  <!-- Header include -->
  <?php 
    $page_title = "My Borrowings"; 
    include "header.php"; 
  ?>

  <!-- Main Content -->
  <main class="flex-1 ml-16 md:ml-64 p-4 sm:p-6 lg:p-8 pt-24 md:pt-28 lg:pt-32">
    
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
      <div class="mb-4 sm:mb-6 bg-green-50 border-l-4 border-green-500 p-3 sm:p-4 rounded-lg shadow-sm alert-animate">
        <div class="flex items-center">
          <i data-feather="check-circle" class="text-green-500 mr-2 sm:mr-3 flex-shrink-0 w-5 h-5"></i>
          <p class="text-green-700 font-medium text-sm sm:text-base"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
        </div>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
      <div class="mb-4 sm:mb-6 bg-red-50 border-l-4 border-red-500 p-3 sm:p-4 rounded-lg shadow-sm alert-animate">
        <div class="flex items-center">
          <i data-feather="alert-circle" class="text-red-500 mr-2 sm:mr-3 flex-shrink-0 w-5 h-5"></i>
          <p class="text-red-700 font-medium text-sm sm:text-base"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
        </div>
      </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="mb-4 sm:mb-6">
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">My Borrowings</h1>
      <p class="text-sm sm:text-base text-gray-600 mt-1">Track your borrowed equipment and manage returns</p>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
      
      <!-- Mobile Card View -->
      <div class="block md:hidden">
        <?php if ($result->num_rows > 0): ?>
          <?php $result->data_seek(0); ?>
          <?php while ($row = $result->fetch_assoc()): 
            $status = getBorrowStatus($row);
            $canRequestReturn = $row['actual_pickup_date'] && !$row['actual_return_date'] && $row['return_requested'] == 0;
            $isOverdue = !$row['actual_return_date'] && strtotime($row['expected_return_date']) < time();
          ?>
            <div class="p-4 border-b border-gray-200 last:border-0 hover:bg-blue-50 transition">
              <div class="flex items-start gap-3 mb-3">
                <div class="flex-shrink-0 h-16 w-16 rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                  <?php if (!empty($row['equipment_image'])): ?>
                    <img src="../photos/<?php echo htmlspecialchars($row['equipment_image']); ?>" 
                        alt="<?php echo htmlspecialchars($row['equipment']); ?>" 
                        class="h-full w-full object-cover"
                        onerror="this.parentElement.innerHTML='<div class=\'h-full w-full flex items-center justify-center bg-blue-100\'><i data-feather=\'package\' class=\'text-blue-600 w-6 h-6\'></i></div>'; feather.replace();">
                  <?php else: ?>
                    <div class="h-full w-full flex items-center justify-center bg-blue-100">
                      <i data-feather="package" class="text-blue-600 w-6 h-6"></i>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                  <h3 class="font-semibold text-gray-900 text-sm mb-1"><?php echo htmlspecialchars($row['equipment']); ?></h3>
                  <?php if ($row['condition_notes']): ?>
                    <p class="text-xs text-gray-500 mb-2">Condition: <?php echo htmlspecialchars($row['condition_notes']); ?></p>
                  <?php endif; ?>
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                      Qty: <?php echo $row['qty']; ?>
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border <?php echo $status['bg'] . ' ' . $status['text'] . ' ' . $status['border']; ?>">
                      <i data-feather="<?php echo $status['icon']; ?>" class="w-3 h-3 mr-1"></i>
                      <?php echo $status['label']; ?>
                    </span>
                    <?php if ($isOverdue): ?>
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-300">
                        <i data-feather="alert-triangle" class="w-3 h-3 mr-1"></i>
                        Overdue
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              
              <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 mb-3">
                <div class="flex items-center">
                  <i data-feather="calendar" class="w-3 h-3 mr-1 text-gray-500"></i>
                  <span>Borrowed: <?php echo date("M d, Y", strtotime($row['borrow_date'])); ?></span>
                </div>
                <div class="flex items-center">
                  <i data-feather="calendar" class="w-3 h-3 mr-1 text-gray-500"></i>
                  <span>Return: <?php echo date("M d, Y", strtotime($row['expected_return_date'])); ?></span>
                </div>
              </div>

              <?php if ($row['actual_pickup_date']): ?>
                <div class="text-xs text-gray-500 mb-2">
                  <i data-feather="check" class="w-3 h-3 inline mr-1"></i>
                  Delivered: <?php echo date("M d, Y", strtotime($row['actual_pickup_date'])); ?>
                </div>
              <?php endif; ?>

              <?php if ($row['actual_return_date']): ?>
                <div class="text-xs text-gray-500 mb-2">
                  <i data-feather="check-circle" class="w-3 h-3 inline mr-1"></i>
                  Returned: <?php echo date("M d, Y", strtotime($row['actual_return_date'])); ?>
                </div>
              <?php endif; ?>

              <?php if ($row['is_damaged'] == 1): ?>
                <div class="mt-2 p-2 bg-red-50 border border-red-200 rounded text-xs">
                  <div class="flex items-center text-red-700 font-semibold mb-1">
                    <i data-feather="alert-triangle" class="w-3 h-3 mr-1"></i>
                    Damage Detected
                  </div>
                  <div class="text-red-600">
                    <strong>Fee:</strong> ₱<?php echo number_format($row['damage_fee'], 2); ?>
                  </div>
                  <?php if ($row['damage_notes']): ?>
                    <div class="text-red-600 mt-1">
                      <strong>Notes:</strong> <?php echo htmlspecialchars($row['damage_notes']); ?>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>

              <?php if ($canRequestReturn): ?>
                <button onclick="openReturnModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['equipment']); ?>', <?php echo $row['qty']; ?>)" 
                        class="w-full mt-3 inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                  <i data-feather="rotate-ccw" class="mr-2 w-4 h-4"></i>
                  Request Return
                </button>
              <?php elseif ($row['return_approved'] == 1 && !$row['actual_return_date']): ?>
                <div class="mt-3 p-3 bg-purple-50 border border-purple-200 rounded-lg text-xs text-purple-700">
                  <i data-feather="info" class="w-4 h-4 inline mr-1"></i>
                  <strong>Return approved!</strong> Please prepare the item for pickup by staff.
                </div>
              <?php elseif ($row['return_requested'] == 1 && !$row['actual_return_date']): ?>
                <span class="text-xs text-gray-500 italic block mt-2">
                  <i data-feather="clock" class="w-3 h-3 inline mr-1"></i>
                  Return request pending staff approval
                </span>
              <?php endif; ?>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="px-6 py-12 text-center">
            <div class="flex flex-col items-center justify-center">
              <div class="bg-gray-100 rounded-full p-4 mb-4">
                <i data-feather="inbox" class="w-12 h-12 text-gray-400"></i>
              </div>
              <p class="text-gray-500 text-base font-medium">No borrowings found</p>
              <p class="text-gray-400 text-sm mt-1">Your borrowing history will appear here</p>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Desktop Table View -->
      <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gradient-to-r from-blue-900 to-blue-700">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Equipment</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Quantity</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Dates</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Status</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php $result->data_seek(0); ?>
            <?php if ($result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): 
                $status = getBorrowStatus($row);
                $canRequestReturn = $row['actual_pickup_date'] && !$row['actual_return_date'] && $row['return_requested'] == 0;
                $isOverdue = !$row['actual_return_date'] && strtotime($row['expected_return_date']) < time();
              ?>
                <tr class="hover:bg-blue-50 transition duration-150 ease-in-out">
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-12 w-12 rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                        <?php if (!empty($row['equipment_image'])): ?>
                          <img src="../photos/<?php echo htmlspecialchars($row['equipment_image']); ?>" 
                              alt="<?php echo htmlspecialchars($row['equipment']); ?>" 
                              class="h-full w-full object-cover"
                              onerror="this.parentElement.innerHTML='<div class=\'h-full w-full flex items-center justify-center bg-blue-100\'><i data-feather=\'package\' class=\'text-blue-600 w-6 h-6\'></i></div>'; feather.replace();">
                        <?php else: ?>
                          <div class="h-full w-full flex items-center justify-center bg-blue-100">
                            <i data-feather="package" class="text-blue-600 w-6 h-6"></i>
                          </div>
                        <?php endif; ?>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($row['equipment']); ?></div>
                        <?php if ($row['condition_notes']): ?>
                          <div class="text-xs text-gray-500 mt-1">Condition: <?php echo htmlspecialchars($row['condition_notes']); ?></div>
                        <?php endif; ?>
                        <?php if ($row['is_damaged'] == 1): ?>
                          <div class="text-xs text-red-600 font-semibold mt-1">
                            <i data-feather="alert-triangle" class="w-3 h-3 inline mr-1"></i>
                            Damage Fee: ₱<?php echo number_format($row['damage_fee'], 2); ?>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                      <?php echo $row['qty']; ?>
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <div class="space-y-1">
                      <div class="flex items-center text-gray-700">
                        <i data-feather="calendar" class="w-3 h-3 mr-2 text-gray-500"></i>
                        <strong>Borrowed:</strong>&nbsp;<?php echo date("M d, Y", strtotime($row['borrow_date'])); ?>
                      </div>
                      <?php if ($row['actual_pickup_date']): ?>
                        <div class="flex items-center text-gray-700">
                          <i data-feather="truck" class="w-3 h-3 mr-2 text-gray-500"></i>
                          <strong>Delivered:</strong>&nbsp;<?php echo date("M d, Y", strtotime($row['actual_pickup_date'])); ?>
                        </div>
                      <?php endif; ?>
                      <div class="flex items-center <?php echo $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-700'; ?>">
                        <i data-feather="calendar" class="w-3 h-3 mr-2"></i>
                        <strong>Due:</strong>&nbsp;<?php echo date("M d, Y", strtotime($row['expected_return_date'])); ?>
                        <?php if ($isOverdue): ?>
                          <span class="ml-2 text-xs">(Overdue)</span>
                        <?php endif; ?>
                      </div>
                      <?php if ($row['actual_return_date']): ?>
                        <div class="flex items-center text-green-700">
                          <i data-feather="check-circle" class="w-3 h-3 mr-2"></i>
                          <strong>Returned:</strong>&nbsp;<?php echo date("M d, Y", strtotime($row['actual_return_date'])); ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold border <?php echo $status['bg'] . ' ' . $status['text'] . ' ' . $status['border']; ?>">
                      <i data-feather="<?php echo $status['icon']; ?>" class="w-4 h-4 mr-1.5"></i>
                      <?php echo $status['label']; ?>
                    </span>
                    <?php if ($isOverdue && !$row['actual_return_date']): ?>
                      <div class="mt-1">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-300">
                          <i data-feather="alert-triangle" class="w-3 h-3 mr-1"></i>
                          Overdue
                        </span>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4">
                    <?php if ($canRequestReturn): ?>
                      <button onclick="openReturnModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['equipment']); ?>', <?php echo $row['qty']; ?>)" 
                              class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5">
                        <i data-feather="rotate-ccw" class="mr-2 w-4 h-4"></i>
                        Request Return
                      </button>
                    <?php elseif ($row['return_approved'] == 1 && !$row['actual_return_date']): ?>
                      <div class="text-sm">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-purple-100 text-purple-700 font-medium">
                          <i data-feather="package" class="w-4 h-4 mr-1.5"></i>
                          Prepare for Pickup
                        </span>
                      </div>
                    <?php elseif ($row['return_requested'] == 1 && !$row['actual_return_date']): ?>
                      <span class="text-sm text-gray-500 italic">
                        <i data-feather="clock" class="w-4 h-4 inline mr-1"></i>
                        Pending approval
                      </span>
                    <?php else: ?>
                      <span class="text-sm text-gray-400">-</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <div class="bg-gray-100 rounded-full p-4 mb-4">
                      <i data-feather="inbox" class="w-12 h-12 text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 text-lg font-medium">No borrowings found</p>
                    <p class="text-gray-400 text-sm mt-1">Your borrowing history will appear here</p>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
        <div class="bg-gray-50 px-4 sm:px-6 py-4 border-t border-gray-200">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-xs sm:text-sm text-gray-700 text-center sm:text-left">
              Showing 
              <span class="font-semibold text-blue-600"><?php echo $offset + 1; ?></span>
              to 
              <span class="font-semibold text-blue-600"><?php echo min($offset + $records_per_page, $total_records); ?></span>
              of 
              <span class="font-semibold text-blue-600"><?php echo $total_records; ?></span>
              results
            </div>

            <div class="flex gap-1 sm:gap-2 flex-wrap justify-center">
              <?php if ($current_page > 1): ?>
                <a href="?page=<?php echo $current_page - 1; ?>" 
                  class="inline-flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition duration-150">
                  <i data-feather="chevron-left" class="w-3 h-3 sm:w-4 sm:h-4 mr-1"></i>
                  Previous
                </a>
              <?php else: ?>
                <span class="inline-flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                  <i data-feather="chevron-left" class="w-3 h-3 sm:w-4 sm:h-4 mr-1"></i>
                  Previous
                </span>
              <?php endif; ?>

              <div class="flex gap-1">
                <?php
                $start_page = max(1, $current_page - 1);
                $end_page = min($total_pages, $current_page + 1);

                if ($start_page > 1): ?>
                  <a href="?page=1" class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition duration-150">1</a>
                  <?php if ($start_page > 2): ?>
                    <span class="px-2 py-2 text-xs sm:text-sm text-gray-500">...</span>
                  <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                  <?php if ($i == $current_page): ?>
                    <span class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold text-white bg-blue-600 border border-blue-600 rounded-lg shadow-sm">
                      <?php echo $i; ?>
                    </span>
                  <?php else: ?>
                    <a href="?page=<?php echo $i; ?>" 
                      class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition duration-150">
                      <?php echo $i; ?>
                    </a>
                  <?php endif; ?>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                  <?php if ($end_page < $total_pages - 1): ?>
                    <span class="px-2 py-2 text-xs sm:text-sm text-gray-500">...</span>
                  <?php endif; ?>
                  <a href="?page=<?php echo $total_pages; ?>" class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition duration-150"><?php echo $total_pages; ?></a>
                <?php endif; ?>
              </div>

              <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?php echo $current_page + 1; ?>" 
                  class="inline-flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition duration-150">
                  Next
                  <i data-feather="chevron-right" class="w-3 h-3 sm:w-4 sm:h-4 ml-1"></i>
                </a>
              <?php else: ?>
                <span class="inline-flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                  Next
                  <i data-feather="chevron-right" class="w-3 h-3 sm:w-4 sm:h-4 ml-1"></i>
                </span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <!-- Footer -->
  <footer class="ml-16 md:ml-64">
    <?php include "footer.php"; ?>
  </footer>

  <!-- Return Request Confirmation Modal -->
  <div id="returnModal" class="modal">
    <div class="modal-content bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 p-6">
      <div class="flex items-center justify-center w-16 h-16 mx-auto bg-blue-100 rounded-full mb-4">
        <i data-feather="rotate-ccw" class="w-8 h-8 text-blue-600"></i>
      </div>
      
      <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Request Early Return?</h3>
      <p class="text-gray-600 text-center mb-6">
        Are you sure you want to return this item early? Staff will be notified and will schedule a pickup.
      </p>
      
      <div class="bg-blue-50 rounded-lg p-4 mb-6 border border-blue-200">
        <div class="flex items-start">
          <i data-feather="info" class="w-5 h-5 text-blue-600 mr-3 mt-0.5 flex-shrink-0"></i>
          <div>
            <p class="text-sm font-semibold text-blue-900 mb-1" id="modalEquipmentName"></p>
            <p class="text-xs text-blue-700">Quantity: <span id="modalQuantity"></span></p>
          </div>
        </div>
      </div>

      <div class="bg-amber-50 rounded-lg p-3 mb-6 border border-amber-200">
        <div class="flex items-start">
          <i data-feather="alert-circle" class="w-4 h-4 text-amber-600 mr-2 mt-0.5 flex-shrink-0"></i>
          <p class="text-xs text-amber-800">
            After confirmation, you will receive an email notification. Please prepare the item for pickup by staff.
          </p>
        </div>
      </div>
      
      <form method="POST" id="returnForm">
        <input type="hidden" name="borrow_id" id="modalBorrowId">
        <div class="flex gap-3">
          <button type="button" onclick="closeReturnModal()" 
                  class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition duration-200">
            Cancel
          </button>
          <button type="submit" name="request_return" 
                  class="flex-1 px-4 py-2.5 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
            Confirm Return
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    feather.replace();

    function openReturnModal(borrowId, equipmentName, quantity) {
      document.getElementById('modalBorrowId').value = borrowId;
      document.getElementById('modalEquipmentName').textContent = equipmentName;
      document.getElementById('modalQuantity').textContent = quantity;
      document.getElementById('returnModal').classList.add('active');
      feather.replace();
    }

    function closeReturnModal() {
      document.getElementById('returnModal').classList.remove('active');
    }

    // Close modal when clicking outside
    document.getElementById('returnModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeReturnModal();
      }
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeReturnModal();
      }
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
      const alerts = document.querySelectorAll('.alert-animate');
      alerts.forEach(function(alert) {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(function() {
          alert.remove();
        }, 500);
      });
    }, 5000);
  </script>
</body>
</html>