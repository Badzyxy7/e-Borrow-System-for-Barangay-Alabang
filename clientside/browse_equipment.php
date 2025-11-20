<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'resident') {
    header("Location: ../login.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = '';
$msg_type = 'success';

if (isset($_POST['request'])) {
    $equipment_id = intval($_POST['equipment_id']);
    $qty = intval($_POST['quantity']);
    $borrow_datetime = $_POST['borrow_datetime'];
    $return_datetime = $_POST['return_datetime'];
    $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';

    $now = date('Y-m-d H:i');
    
    if ($borrow_datetime < $now) {
        $msg = "Borrow date & time cannot be in the past.";
        $msg_type = 'error';
    } elseif ($return_datetime < $borrow_datetime) {
        $msg = "Return date & time cannot be before borrow date & time.";
        $msg_type = 'error';
    } else {
        $eq_result = $conn->query("SELECT quantity, name FROM equipment WHERE id = $equipment_id");
        $equipment = $eq_result->fetch_assoc();
        $total_qty = $equipment['quantity'];
        $equipment_name = $equipment['name'];
        
        $check_sql = "
            SELECT COALESCE(SUM(qty), 0) as total_borrowed
            FROM borrow_requests
            WHERE equipment_id = $equipment_id
              AND status IN ('pending', 'approved', 'picked_up')
              AND borrow_date <= '$return_datetime'
              AND return_date >= '$borrow_datetime'
        ";
        $check_result = $conn->query($check_sql);
        $row = $check_result->fetch_assoc();
        $total_borrowed = $row['total_borrowed'];
        
        $available_qty = $total_qty - $total_borrowed;
        
        if ($available_qty >= $qty) {
            $conn->query("INSERT INTO borrow_requests (user_id, equipment_id, qty, borrow_date, return_date, description, status, created_at)
                          VALUES ($user_id, $equipment_id, $qty, '$borrow_datetime', '$return_datetime', '$description', 'pending', NOW())");
            $msg = "Request submitted successfully!";
            $msg_type = 'success';
        } else {
            if ($available_qty > 0) {
                $msg = "Sorry! Only $available_qty unit(s) of '$equipment_name' are available for the selected dates. Please choose different dates or reduce quantity.";
            } else {
                $msg = "Sorry! '$equipment_name' is fully booked for the selected dates. Please choose different dates.";
            }
            $msg_type = 'error';
        }
    }
}

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

$items_per_page = 9;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $items_per_page;

$count_sql = "SELECT COUNT(*) as total FROM equipment WHERE 1=1";
if ($search) $count_sql .= " AND name LIKE '%$search%'";
if ($status_filter) $count_sql .= " AND status='$status_filter'";
$count_result = $conn->query($count_sql);
$total_items = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);

$sql = "SELECT * FROM equipment WHERE 1=1";
if ($search) $sql .= " AND name LIKE '%$search%'";
if ($status_filter) $sql .= " AND status='$status_filter'";
$sql .= " ORDER BY 
    CASE 
        WHEN status='available' AND available=1 THEN 1
        ELSE 2
    END,
    name ASC
    LIMIT $items_per_page OFFSET $offset";
$result = $conn->query($sql);

// Check if this is an AJAX request
$is_ajax = isset($_GET['ajax']) || 
           (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

// If AJAX, return only the results HTML
if ($is_ajax) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            ?>
            <div class="bg-white p-4 sm:p-6 rounded-xl shadow-md hover:shadow-lg transition flex flex-col">
              
              <?php if (!empty($row['image'])): ?>
                <img src="../photos/<?php echo htmlspecialchars($row['image']); ?>" 
                     class="w-full h-40 sm:h-48 object-cover rounded mb-3 cursor-pointer hover:scale-105 transition-transform"
                     alt="<?php echo htmlspecialchars($row['name']); ?>"
                     onclick="openImageModal('<?php echo htmlspecialchars($row['image']); ?>')">
              <?php endif; ?>
              
              <h2 class="text-base sm:text-lg font-semibold mb-1"><?php echo htmlspecialchars($row['name']); ?></h2>
              <p class="text-gray-500 mb-2 text-xs sm:text-sm line-clamp-2"><?php echo htmlspecialchars($row['description']); ?></p>
              <p class="mb-1 text-sm"><span class="font-bold">Quantity:</span> <?php echo $row['quantity']; ?></p>
              <p class="mb-4">
                <span class="px-2 py-1 rounded text-xs sm:text-sm
                  <?php echo $row['status']=="available"?"bg-green-100 text-green-700":"bg-yellow-100 text-yellow-700"; ?>">
                  <?php echo ucfirst($row['status']); ?>
                </span>
              </p>

              <?php if ($row['status']=="available" && $row['available']==1): ?>
                <button onclick="openRequestModal(<?php echo $row['id']; ?>)"
                        class="mt-auto w-full bg-blue-600 text-white py-2 text-sm sm:text-base rounded-lg hover:bg-blue-700 transition font-medium">
                  Request
                </button>
              <?php else: ?>
                <button class="mt-auto w-full bg-gray-400 text-white py-2 text-sm sm:text-base rounded-lg cursor-not-allowed" disabled>
                  Not Available
                </button>
              <?php endif; ?>
            </div>
            <?php
        }
    } else {
        echo '<div class="col-span-full text-center py-12">
          <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
          </svg>
          <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-2">No Equipment Found</h3>
          <p class="text-sm text-gray-500">Try adjusting your search or filters.</p>
        </div>';
    }
    
    // Pagination for AJAX
    if ($total_pages > 1) {
        echo '<div class="col-span-full mt-6 sm:mt-8 flex flex-wrap justify-center items-center gap-1 sm:gap-2">';
        
        if ($page > 1) {
            echo '<a href="#" onclick="goToPage(' . ($page - 1) . '); return false;" 
                   class="px-3 sm:px-4 py-2 text-xs sm:text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition text-gray-700 font-medium">
                    « Prev
                  </a>';
        }
        
        $start_page = max(1, $page - 1);
        $end_page = min($total_pages, $page + 1);
        
        if ($start_page > 1) {
            echo '<a href="#" onclick="goToPage(1); return false;" 
                   class="px-3 sm:px-4 py-2 text-xs sm:text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition text-gray-700">1</a>';
            if ($start_page > 2) {
                echo '<span class="px-2 text-gray-500 text-xs sm:text-sm">...</span>';
            }
        }
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $page) {
                echo '<span class="px-3 sm:px-4 py-2 text-xs sm:text-sm bg-blue-600 text-white rounded-lg font-bold">' . $i . '</span>';
            } else {
                echo '<a href="#" onclick="goToPage(' . $i . '); return false;" 
                       class="px-3 sm:px-4 py-2 text-xs sm:text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition text-gray-700">' . $i . '</a>';
            }
        }
        
        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) {
                echo '<span class="px-2 text-gray-500 text-xs sm:text-sm">...</span>';
            }
            echo '<a href="#" onclick="goToPage(' . $total_pages . '); return false;" 
                   class="px-3 sm:px-4 py-2 text-xs sm:text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition text-gray-700">' . $total_pages . '</a>';
        }
        
        if ($page < $total_pages) {
            echo '<a href="#" onclick="goToPage(' . ($page + 1) . '); return false;" 
                   class="px-3 sm:px-4 py-2 text-xs sm:text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition text-gray-700 font-medium">
                    Next »
                  </a>';
        }
        
        echo '</div>';
        
        echo '<div class="col-span-full mt-3 sm:mt-4 text-center text-xs sm:text-sm text-gray-600">
                Showing ' . ((($page - 1) * $items_per_page) + 1) . ' 
                to ' . min($page * $items_per_page, $total_items) . ' 
                of ' . $total_items . ' items
              </div>';
    }
    
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Browse Equipment | E-Borrow System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <style>
    .modal-enter {
      animation: modalFadeIn 0.3s ease-out;
    }
    @keyframes modalFadeIn {
      from {opacity: 0; transform: translateY(20px);}
      to {opacity: 1; transform: translateY(0);}
    }
    .backdrop-blur {
      backdrop-filter: blur(6px);
    }
    .line-clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">

  <?php include "resident_sidebar.php"; ?>
  <?php $page_title = "Browse Equipment"; include "header.php"; ?>

  <main class="flex-1 ml-16 md:ml-64 p-4 sm:p-6 lg:p-8 pt-20 md:pt-24 lg:pt-28">

    <?php if (!empty($msg)): ?>
      <div class="mb-4 sm:mb-6 p-4 rounded-lg <?php echo ($msg_type === 'success') ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>">
        <div class="flex items-center gap-2">
          <?php if ($msg_type === 'success'): ?>
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
          <?php else: ?>
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
          <?php endif; ?>
          <span class="font-semibold text-sm sm:text-base"><?php echo $msg; ?></span>
        </div>
      </div>
    <?php endif; ?>

    <!-- Search + Filter with Live Search -->
    <form method="get" id="searchForm" class="flex flex-col sm:flex-row gap-3 sm:gap-4 mb-6">
      <input type="text" 
             name="search" 
             id="searchInput"
             placeholder="Search equipment..." 
             value="<?php echo htmlspecialchars($search); ?>"
             class="flex-1 px-3 py-2 text-sm sm:text-base border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
      <select name="status" 
              id="statusFilter"
              class="px-3 py-2 text-sm sm:text-base border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="">All Status</option>
        <option value="available" <?php if($status_filter=="available") echo "selected"; ?>>Available</option>
        <option value="maintenance" <?php if($status_filter=="maintenance") echo "selected"; ?>>Under Maintenance</option>
      </select>
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 text-sm sm:text-base rounded-lg hover:bg-blue-700 transition font-medium">
        Filter
      </button>
    </form>

    <!-- Equipment Grid -->
    <div id="resultsContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <div class="bg-white p-4 sm:p-6 rounded-xl shadow-md hover:shadow-lg transition flex flex-col">
          
          <?php if (!empty($row['image'])): ?>
            <img src="../photos/<?php echo htmlspecialchars($row['image']); ?>" 
                 class="w-full h-40 sm:h-48 object-cover rounded mb-3 cursor-pointer hover:scale-105 transition-transform"
                 alt="<?php echo htmlspecialchars($row['name']); ?>"
                 onclick="openImageModal('<?php echo htmlspecialchars($row['image']); ?>')">
          <?php endif; ?>
          
          <h2 class="text-base sm:text-lg font-semibold mb-1"><?php echo htmlspecialchars($row['name']); ?></h2>
          <p class="text-gray-500 mb-2 text-xs sm:text-sm line-clamp-2"><?php echo htmlspecialchars($row['description']); ?></p>
          <p class="mb-1 text-sm"><span class="font-bold">Quantity:</span> <?php echo $row['quantity']; ?></p>
          <p class="mb-4">
            <span class="px-2 py-1 rounded text-xs sm:text-sm
              <?php echo $row['status']=="available"?"bg-green-100 text-green-700":"bg-yellow-100 text-yellow-700"; ?>">
              <?php echo ucfirst($row['status']); ?>
            </span>
          </p>

          <?php if ($row['status']=="available" && $row['available']==1): ?>
            <button onclick="openRequestModal(<?php echo $row['id']; ?>)"
                    class="mt-auto w-full bg-blue-600 text-white py-2 text-sm sm:text-base rounded-lg hover:bg-blue-700 transition font-medium">
              Request
            </button>

<!-- IMPROVED MOBILE-RESPONSIVE REQUEST MODAL -->
<div id="requestModal<?php echo $row['id']; ?>" 
     class="fixed inset-0 bg-black/40 backdrop-blur hidden items-center justify-center z-50 p-0 sm:p-4">
  <div class="bg-white w-full h-full sm:h-auto sm:rounded-2xl shadow-2xl sm:max-w-5xl sm:mx-auto modal-enter flex flex-col overflow-hidden"
       style="max-height: 100vh; sm:max-height: 90vh;">

    <!-- Mobile Header with Close -->
    <div class="flex items-center justify-between p-4 border-b sm:hidden bg-white sticky top-0 z-10">
      <h2 class="text-lg font-bold">Request Equipment</h2>
      <button onclick="closeRequestModal(<?php echo $row['id']; ?>)" 
              class="text-gray-500 text-2xl font-bold hover:text-gray-800">&times;</button>
    </div>

    <!-- Desktop Close Button -->
    <button onclick="closeRequestModal(<?php echo $row['id']; ?>)" 
            class="hidden sm:block absolute top-3 right-3 text-gray-500 text-3xl font-bold hover:text-gray-800 transition z-10">&times;</button>

    <div class="flex flex-col md:flex-row w-full overflow-y-auto flex-1">
      <input type="hidden" id="equipment_id_<?php echo $row['id']; ?>" value="<?php echo $row['id']; ?>">
      <input type="hidden" id="equipment_name_<?php echo $row['id']; ?>" value="<?php echo htmlspecialchars($row['name']); ?>">
      <input type="hidden" id="equipment_desc_<?php echo $row['id']; ?>" value="<?php echo htmlspecialchars($row['description']); ?>">
      <input type="hidden" id="equipment_image_<?php echo $row['id']; ?>" value="<?php echo htmlspecialchars($row['image']); ?>">
      <input type="hidden" id="equipment_total_qty_<?php echo $row['id']; ?>" value="<?php echo $row['quantity']; ?>">

      <!-- LEFT SIDE - Equipment Info -->
      <div class="md:w-1/2 bg-gray-50 flex flex-col p-4">
        <?php if (!empty($row['image'])): ?>
          <div class="w-full mb-3">
            <img src="../photos/<?php echo htmlspecialchars($row['image']); ?>" 
                 class="w-full h-48 sm:h-64 md:h-72 object-cover rounded-xl shadow-sm">
          </div>
        <?php endif; ?>

        <div class="flex-1">
          <h2 class="text-lg sm:text-xl font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($row['name']); ?></h2>
          <p class="text-gray-500 text-xs sm:text-sm mb-3"><?php echo htmlspecialchars($row['description']); ?></p>
          <p class="text-xs sm:text-sm mb-4"><span class="font-semibold">Total Available:</span> <?php echo $row['quantity']; ?></p>

          <!-- Availability Indicator -->
          <div id="availability_indicator_<?php echo $row['id']; ?>" class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs sm:text-sm text-blue-700 hidden">
            <span class="font-semibold">Checking availability...</span>
          </div>

          <!-- Quantity -->
          <label class="text-sm font-semibold block mb-1">Quantity</label>
          <input type="number" id="quantity_<?php echo $row['id']; ?>" min="1" max="<?php echo $row['quantity']; ?>" value="1"
                 onchange="checkAvailabilityRealtime(<?php echo $row['id']; ?>)"
                 class="w-full px-3 py-2.5 mb-3 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required>
        </div>
      </div>

      <!-- RIGHT SIDE - Form -->
      <div class="md:w-1/2 p-4 sm:p-6 flex flex-col gap-4 bg-white">

        <div>
          <label class="text-sm font-semibold block mb-1">Borrow Date & Time</label>
          <p class="text-gray-500 text-xs mb-2">When you'll pick up the equipment</p>
          <input type="text" id="borrow_datetime_<?php echo $row['id']; ?>"
                 onchange="checkAvailabilityRealtime(<?php echo $row['id']; ?>)"
                 class="w-full px-3 py-2.5 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" 
                 placeholder="Select date & time" required>
        </div>

        <div>
          <label class="text-sm font-semibold block mb-1">Return Date & Time</label>
          <p class="text-gray-500 text-xs mb-2">When you'll return the equipment</p>
          <input type="text" id="return_datetime_<?php echo $row['id']; ?>"
                 onchange="checkAvailabilityRealtime(<?php echo $row['id']; ?>)"
                 class="w-full px-3 py-2.5 text-sm border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" 
                 placeholder="Select date & time" required>
        </div>

        <div>
          <label class="text-sm font-semibold block mb-1">Description (Optional)</label>
          <textarea id="description_<?php echo $row['id']; ?>" rows="3" placeholder="Add any notes or special requests..."
                    class="w-full px-3 py-2.5 text-sm border rounded-lg resize-none focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
        </div>

        <label class="flex items-start gap-2 text-xs sm:text-sm cursor-pointer">
          <input type="checkbox" id="agreeCheckbox<?php echo $row['id']; ?>" onchange="toggleSubmitButton(<?php echo $row['id']; ?>)"
                 class="mt-0.5 flex-shrink-0">
          <span>I agree to return the item in good condition and on time.</span>
        </label>

        <button type="button"
                id="submitBtn<?php echo $row['id']; ?>"
                class="w-full bg-blue-400 text-white py-3 text-sm sm:text-base rounded-lg cursor-not-allowed transition font-semibold mt-auto"
                onclick="if(!this.disabled) openConfirmModal(<?php echo $row['id']; ?>)"
                disabled>
          Submit Request
        </button>

      </div>
    </div>
  </div>
</div>

<!-- IMPROVED MOBILE-RESPONSIVE CONFIRMATION MODAL -->
<div id="confirmModal<?php echo $row['id']; ?>" 
     class="fixed inset-0 bg-black/50 backdrop-blur hidden items-center justify-center z-[60] p-0 sm:p-4">
  <div class="bg-white w-full h-full sm:h-auto sm:rounded-2xl shadow-2xl sm:max-w-2xl sm:mx-auto modal-enter overflow-hidden flex flex-col" 
       style="max-height: 100vh; sm:max-height: 90vh;">
    
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-4 sm:p-6 text-white flex-shrink-0">
      <h2 class="text-xl sm:text-2xl font-bold">Confirm Your Request</h2>
      <p class="text-blue-100 text-xs sm:text-sm mt-1">Please review the details before submitting</p>
    </div>

    <!-- Content -->
    <div class="p-4 sm:p-6 overflow-y-auto flex-1">
      <div class="space-y-4">
        
        <div class="flex items-start gap-3 pb-4 border-b">
          <img id="confirm_image_<?php echo $row['id']; ?>" src="" class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-lg flex-shrink-0">
          <div class="flex-1 min-w-0">
            <h3 class="font-bold text-base sm:text-lg" id="confirm_name_<?php echo $row['id']; ?>"></h3>
            <p class="text-gray-500 text-xs sm:text-sm line-clamp-2" id="confirm_desc_<?php echo $row['id']; ?>"></p>
          </div>
        </div>

        <div>
          <p class="text-xs text-gray-500 font-semibold mb-0.5">Quantity</p>
          <p class="text-lg font-bold" id="confirm_quantity_<?php echo $row['id']; ?>"></p>
        </div>

        <div>
          <p class="text-xs text-gray-500 font-semibold mb-0.5">Borrow Date & Time</p>
          <p class="text-base font-bold" id="confirm_borrow_<?php echo $row['id']; ?>"></p>
        </div>

        <div>
          <p class="text-xs text-gray-500 font-semibold mb-0.5">Return Date & Time</p>
          <p class="text-base font-bold" id="confirm_return_<?php echo $row['id']; ?>"></p>
        </div>

        <div id="confirm_description_container_<?php echo $row['id']; ?>" style="display: none;">
          <p class="text-xs text-gray-500 font-semibold mb-0.5">Additional Notes</p>
          <p class="text-gray-700 text-sm" id="confirm_description_<?php echo $row['id']; ?>"></p>
        </div>

      </div>
    </div>

    <!-- Footer Buttons -->
    <div class="p-4 sm:p-6 bg-gray-50 flex gap-3 flex-shrink-0 border-t">
      <button onclick="closeConfirmModal(<?php echo $row['id']; ?>)"
              class="flex-1 bg-gray-200 text-gray-700 py-3 text-sm sm:text-base rounded-lg hover:bg-gray-300 transition font-semibold">
        Cancel
      </button>
      <button onclick="submitRequest(<?php echo $row['id']; ?>)"
              class="flex-1 bg-blue-600 text-white py-3 text-sm sm:text-base rounded-lg hover:bg-blue-700 transition font-semibold">
        Confirm Request
      </button>
    </div>

  </div>
</div>

          <?php else: ?>
            <button class="mt-auto w-full bg-gray-400 text-white py-2 text-sm sm:text-base rounded-lg cursor-not-allowed" disabled>
              Not Available
            </button>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
      <?php else: ?>
        <div class="col-span-full text-center py-12">
          <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
          </svg>
          <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-2">No Equipment Found</h3>
          <p class="text-sm text-gray-500">Try adjusting your search or filters.</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="mt-6 sm:mt-8 flex flex-wrap justify-center items-center gap-1 sm:gap-2">
      
      <?php if ($page > 1): ?>
        <a href="?page=<?php echo ($page - 1); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?>" 
           class="px-3 sm:px-4 py-2 text-xs sm:text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition text-gray-700 font-medium">
          « Prev
        </a>
      <?php else: ?>
        <span class="px-3 sm:px-4 py-2 text-xs sm:text-sm bg-gray-100 border border-gray-200 rounded-lg text-gray-400 font-medium cursor-not-allowed">
          « Prev
        </span>
      <?php endif; ?>

      <?php
      $start_page = max(1, $page - 1);
      $end_page = min($total_pages, $page + 1);
      
      if ($start_page > 1): ?>
        <a href="?page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?>" 
           class="px-3 sm:px-4 py-2 text-xs sm:text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition text-gray-700">
          1
        </a>
        <?php if ($start_page > 2): ?>
          <span class="px-2 text-gray-500 text-xs sm:text-sm">...</span>
        <?php endif; ?>
      <?php endif; ?>

      <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
        <?php if ($i == $page): ?>
          <span class="px-3 sm:px-4 py-2 text-xs sm:text-sm bg-blue-600 text-white rounded-lg font-bold">
            <?php echo $i; ?>
          </span>
        <?php else: ?>
          <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?>" 
             class="px-3 sm:px-4 py-2 text-xs sm:text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition text-gray-700">
            <?php echo $i; ?>
          </a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($end_page < $total_pages): ?>
        <?php if ($end_page < $total_pages - 1): ?>
          <span class="px-2 text-gray-500 text-xs sm:text-sm">...</span>
        <?php endif; ?>
        <a href="?page=<?php echo $total_pages; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?>" 
           class="px-3 sm:px-4 py-2 text-xs sm:text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition text-gray-700">
          <?php echo $total_pages; ?>
        </a>
      <?php endif; ?>

      <?php if ($page < $total_pages): ?>
        <a href="?page=<?php echo ($page + 1); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?>" 
           class="px-3 sm:px-4 py-2 text-xs sm:text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition text-gray-700 font-medium">
          Next »
        </a>
      <?php else: ?>
        <span class="px-3 sm:px-4 py-2 text-xs sm:text-sm bg-gray-100 border border-gray-200 rounded-lg text-gray-400 font-medium cursor-not-allowed">
          Next »
        </span>
      <?php endif; ?>

    </div>

    <div class="mt-3 sm:mt-4 text-center text-xs sm:text-sm text-gray-600">
      Showing <?php echo (($page - 1) * $items_per_page) + 1; ?> 
      to <?php echo min($page * $items_per_page, $total_items); ?> 
      of <?php echo $total_items; ?> items
    </div>
    <?php endif; ?>
  </main>

  <footer class="ml-16 md:ml-64">
    <?php include "footer.php"; ?>
  </footer>

  <!-- IMAGE MODAL -->
  <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="relative">
      <button onclick="closeImageModal()" class="absolute top-2 right-2 text-white text-2xl font-bold">&times;</button>
      <img id="modalImage" src="" class="max-h-[80vh] max-w-[90vw] rounded shadow-lg">
    </div>
  </div>

  <!-- SUCCESS MODAL -->
  <?php if ($msg === "Request submitted successfully!"): ?>
  <div id="successModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-[70] p-4">
    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-2xl w-full max-w-md text-center relative modal-enter">
      <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
      </div>
      <h2 class="text-xl sm:text-2xl font-bold mb-3 text-gray-800">Request Submitted!</h2>
      <p class="text-sm sm:text-base text-gray-600 mb-6">Your borrowing request has been successfully submitted. Please wait for staff approval.</p>
      <button onclick="closeSuccessModal()"
              class="w-full bg-blue-600 text-white py-2 sm:py-3 text-sm sm:text-base rounded-lg hover:bg-blue-700 transition font-semibold">
        OK
      </button>
    </div>
  </div>
  <script>function closeSuccessModal(){document.getElementById('successModal').remove();}</script>
  <?php endif; ?>

  <!-- ERROR MODAL -->
  <div id="errorModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[80] p-4">
    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-2xl w-full max-w-md text-center relative modal-enter">
      <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </div>
      <h2 class="text-xl sm:text-2xl font-bold mb-3 text-gray-800">Invalid Dates!</h2>
      <p class="text-sm sm:text-base text-gray-600 mb-6">Return date must be after borrow date. Please correct the dates.</p>
      <button onclick="closeErrorModal()"
              class="w-full bg-red-600 text-white py-2 sm:py-3 text-sm sm:text-base rounded-lg hover:bg-red-700 transition font-semibold">
        OK
      </button>
    </div>
  </div>

  <script>
    feather.replace();

    // Live search functionality
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    let searchTimeout;
    let currentPage = <?php echo $page; ?>;

    function performSearch(page = 1) {
      const searchValue = searchInput.value;
      const statusValue = statusFilter.value;
      
      currentPage = page;
      
      // Update URL without page reload
      const url = new URL(window.location);
      url.searchParams.set('search', searchValue);
      url.searchParams.set('status', statusValue);
      url.searchParams.set('page', page);
      window.history.pushState({}, '', url);
      
      // Fetch results via AJAX
      fetch(`${window.location.pathname}?search=${encodeURIComponent(searchValue)}&status=${encodeURIComponent(statusValue)}&page=${page}&ajax=1`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.text())
      .then(html => {
        document.getElementById('resultsContainer').innerHTML = html;
        // Scroll to top of results
        document.getElementById('resultsContainer').scrollIntoView({ behavior: 'smooth', block: 'start' });
      })
      .catch(error => console.error('Search error:', error));
    }

    // Debounced search on input (waits 300ms after user stops typing)
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => performSearch(1), 300);
    });

    // Immediate search on status change
    statusFilter.addEventListener('change', () => performSearch(1));

    // Prevent form submission
    document.getElementById('searchForm').addEventListener('submit', function(e) {
      e.preventDefault();
      performSearch(1);
    });

    // Pagination function
    function goToPage(page) {
      performSearch(page);
    }

    function closeErrorModal() {
      document.getElementById('errorModal').classList.add('hidden');
    }

    function openImageModal(src) {
      const modal = document.getElementById('imageModal');
      const modalImg = document.getElementById('modalImage');
      modalImg.src = '../photos/' + src;
      modal.classList.remove('hidden');
      modal.classList.add('flex');

      modal.addEventListener('click', function handleOutsideClick(e) {
        if (e.target === modal) {
          closeImageModal();
          modal.removeEventListener('click', handleOutsideClick);
        }
      });
    }

    function closeImageModal() {
      const modal = document.getElementById('imageModal');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }

    function openRequestModal(id) {
      const modal = document.getElementById('requestModal'+id);
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      
      // Prevent body scroll on mobile
      document.body.style.overflow = 'hidden';
      
      initializeDatePickers(id);
    }

    function initializeDatePickers(id) {
      const now = new Date();
      
      flatpickr("#borrow_datetime_" + id, {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        minDate: now,
        time_24hr: true
      });
      
      flatpickr("#return_datetime_" + id, {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        minDate: now,
        time_24hr: true
      });
    }
    
    function closeRequestModal(id) {
      const modal = document.getElementById('requestModal'+id);
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      
      // Re-enable body scroll
      document.body.style.overflow = '';
    }

    function toggleSubmitButton(id) {
      const checkbox = document.getElementById('agreeCheckbox' + id);
      const button = document.getElementById('submitBtn' + id);

      if (checkbox.checked) {
        button.disabled = false;
        button.classList.remove('bg-blue-400', 'cursor-not-allowed');
        button.classList.add('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
      } else {
        button.disabled = true;
        button.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
        button.classList.add('bg-blue-400', 'cursor-not-allowed');
      }
    }

    function checkAvailabilityRealtime(id) {
      const borrowDate = document.getElementById('borrow_datetime_' + id).value;
      const returnDate = document.getElementById('return_datetime_' + id).value;
      const qty = document.getElementById('quantity_' + id).value;
      const indicator = document.getElementById('availability_indicator_' + id);
      
      if (borrowDate && returnDate && qty) {
        indicator.classList.remove('hidden');
        indicator.innerHTML = '<span class="font-semibold">Checking availability...</span>';
        indicator.className = 'mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs sm:text-sm text-blue-700';
        
        fetch(`check_availability.php?equipment_id=${id}&borrow=${encodeURIComponent(borrowDate)}&return=${encodeURIComponent(returnDate)}&qty=${qty}`)
          .then(res => res.json())
          .then(data => {
            if (data.available) {
              indicator.innerHTML = `<span class="font-semibold">✓ Available!</span> ${data.available_qty} unit(s) available for selected dates.`;
              indicator.className = 'mb-3 p-3 bg-green-50 border border-green-200 rounded-lg text-xs sm:text-sm text-green-700';
            } else {
              indicator.innerHTML = `<span class="font-semibold">⚠ Limited availability!</span> Only ${data.available_qty} unit(s) available. Requested: ${data.requested_qty}`;
              indicator.className = 'mb-3 p-3 bg-orange-50 border border-orange-200 rounded-lg text-xs sm:text-sm text-orange-700';
            }
          })
          .catch(err => {
            console.error('Error checking availability:', err);
            indicator.classList.add('hidden');
          });
      }
    }

    function openConfirmModal(id) {
      const quantity = document.getElementById('quantity_' + id).value;
      const borrowDateStr = document.getElementById('borrow_datetime_' + id).value;
      const returnDateStr = document.getElementById('return_datetime_' + id).value;
      const description = document.getElementById('description_' + id).value;

      if (!borrowDateStr || !returnDateStr) {
        alert('Please select both borrow and return dates.');
        return;
      }

      const borrowDate = new Date(borrowDateStr);
      const returnDate = new Date(returnDateStr);

      if (returnDate <= borrowDate) {
        document.getElementById('errorModal').classList.remove('hidden');
        document.getElementById('errorModal').classList.add('flex');
        return;
      }

      const name = document.getElementById('equipment_name_' + id).value;
      const desc = document.getElementById('equipment_desc_' + id).value;
      const image = document.getElementById('equipment_image_' + id).value;

      document.getElementById('confirm_name_' + id).textContent = name;
      document.getElementById('confirm_desc_' + id).textContent = desc;
      document.getElementById('confirm_image_' + id).src = '../photos/' + image;
      document.getElementById('confirm_quantity_' + id).textContent = quantity;

      const borrowFormatted = borrowDate.toLocaleString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
      });
      const returnFormatted = returnDate.toLocaleString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
      });

      document.getElementById('confirm_borrow_' + id).textContent = borrowFormatted;
      document.getElementById('confirm_return_' + id).textContent = returnFormatted;

      if (description) {
        document.getElementById('confirm_description_' + id).textContent = description;
        document.getElementById('confirm_description_container_' + id).style.display = 'block';
      } else {
        document.getElementById('confirm_description_container_' + id).style.display = 'none';
      }

      const confirmModal = document.getElementById('confirmModal' + id);
      confirmModal.classList.remove('hidden');
      confirmModal.classList.add('flex');
    }

    function closeConfirmModal(id) {
      const modal = document.getElementById('confirmModal' + id);
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }

    function submitRequest(id) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '';

      const fields = {
        'equipment_id': document.getElementById('equipment_id_' + id).value,
        'quantity': document.getElementById('quantity_' + id).value,
        'borrow_datetime': document.getElementById('borrow_datetime_' + id).value,
        'return_datetime': document.getElementById('return_datetime_' + id).value,
        'description': document.getElementById('description_' + id).value,
        'request': '1'
      };

      for (const [name, value] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
      }

      document.body.appendChild(form);
      form.submit();
    }
  </script>

</body>
</html>