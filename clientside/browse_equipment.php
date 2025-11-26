<?php
// ============================================
// BROWSE EQUIPMENT - MAIN FILE (LOGIC + LAYOUT) - UPDATED
// ============================================

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../db.php";

// ============================================
// AUTHENTICATION CHECK
// ============================================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'resident') {
    header("Location: ../login.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = '';
$msg_type = 'success';

// ============================================
// FORM SUBMISSION HANDLING - UPDATED WITH PURPOSE & DEATH CERTIFICATE
// ============================================
if (isset($_POST['request'])) {
    $equipment_id = intval($_POST['equipment_id']);
    $qty = intval($_POST['quantity']);
    $borrow_datetime = $_POST['borrow_datetime'];
    $return_datetime = $_POST['return_datetime'];
    $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';
    $purpose = isset($_POST['purpose']) ? $conn->real_escape_string($_POST['purpose']) : '';
    $death_certificate = isset($_POST['death_certificate']) ? $conn->real_escape_string($_POST['death_certificate']) : '';

    $now = date('Y-m-d H:i');
    // ========== ADD QUANTITY VALIDATION ==========
    if ($qty <= 0 || !is_numeric($qty) || floor($qty) != $qty) {
        $msg = "Invalid quantity. Please enter a positive whole number.";
        $msg_type = 'error';
    }
    // ========== END QUANTITY VALIDATION ==========
    
    // Validate purpose
    if (empty($purpose)) {
        $msg = "Purpose is required.";
        $msg_type = 'error';
    } elseif ($purpose === 'Funeral/Lamay' && empty($death_certificate)) {
        $msg = "Death certificate is required for funeral/lamay purposes.";
        $msg_type = 'error';
    } elseif ($borrow_datetime < $now) {
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
            $conn->query("INSERT INTO borrow_requests (user_id, equipment_id, qty, borrow_date, return_date, description, purpose, death_certificate, status, created_at)
                          VALUES ($user_id, $equipment_id, $qty, '$borrow_datetime', '$return_datetime', '$description', '$purpose', '$death_certificate', 'pending', NOW())");
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

// ============================================
// SEARCH & FILTER PARAMETERS
// ============================================
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

// ============================================
// PAGINATION SETUP
// ============================================
$items_per_page = 9;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $items_per_page;

// ============================================
// COUNT TOTAL ITEMS
// ============================================
$count_sql = "SELECT COUNT(*) as total FROM equipment WHERE 1=1";
if ($search) $count_sql .= " AND name LIKE '%$search%'";
if ($status_filter) $count_sql .= " AND status='$status_filter'";
$count_result = $conn->query($count_sql);
$total_items = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);

// ============================================
// FETCH EQUIPMENT DATA
// ============================================
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

// ============================================
// AJAX REQUEST DETECTION
// ============================================
$is_ajax = isset($_GET['ajax']) || 
           (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

// ============================================
// AJAX RESPONSE (Cards + Pagination Only)
// ============================================
if ($is_ajax) {
    include "browse_equipment_cards.php";
    
    // AJAX Pagination
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
    
    exit(); // Stop execution for AJAX
}

// ============================================
// FULL PAGE HTML (NON-AJAX REQUESTS)
// ============================================
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
    .line-clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .equipment-card {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .equipment-card:hover {
      transform: translateY(-4px);
    }
    .equipment-image {
      transition: transform 0.4s ease;
      overflow: hidden;
    }
    .equipment-image img {
      transition: transform 0.4s ease;
    }
    .equipment-image:hover img {
      transform: scale(1.08);
    }
    .modal-backdrop {
      position: fixed;
      inset: 0;
      z-index: 9999;
      pointer-events: auto;
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">

  <?php include "resident_sidebar.php"; ?>
  <?php $page_title = "Browse Equipment"; include "header.php"; ?>

  <main class="flex-1 ml-16 md:ml-64 p-4 sm:p-6 lg:p-8 pt-20 md:pt-24 lg:pt-28">

    <!-- ALERT MESSAGES -->
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

    <!-- SEARCH & FILTER FORM -->
    <form method="get" id="searchForm" class="flex flex-col sm:flex-row gap-3 sm:gap-4 mb-6">
      <input type="text" 
             name="search" 
             id="searchInput"
             placeholder="Search equipment..." 
             value="<?php echo htmlspecialchars($search); ?>"
             class="flex-1 px-4 py-3 text-sm sm:text-base border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
      <select name="status" 
              id="statusFilter"
              class="px-4 py-3 text-sm sm:text-base border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
        <option value="">All Status</option>
        <option value="available" <?php if($status_filter=="available") echo "selected"; ?>>Available</option>
        <option value="maintenance" <?php if($status_filter=="maintenance") echo "selected"; ?>>Under Maintenance</option>
      </select>
      <button type="submit" class="bg-gradient-to-r from-blue-900 to-blue-700 text-white px-6 py-3 text-sm sm:text-base rounded-xl hover:bg-blue-700 transition font-semibold shadow-sm">
        Filter
      </button>
    </form>

    <!-- EQUIPMENT GRID -->
    <div id="resultsContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
      <?php include "browse_equipment_cards.php"; ?>
    </div>

    <!-- PAGINATION -->
    <?php if ($total_pages > 1): ?>
    <div class="mt-8 flex flex-wrap justify-center items-center gap-2">
      
      <?php if ($page > 1): ?>
        <a href="?page=<?php echo ($page - 1); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?>" 
           class="px-4 py-2.5 text-sm bg-white border-2 border-gray-200 rounded-xl hover:bg-gray-50 hover:border-blue-300 transition text-gray-700 font-semibold">
          « Prev
        </a>
      <?php else: ?>
        <span class="px-4 py-2.5 text-sm bg-gray-100 border-2 border-gray-200 rounded-xl text-gray-400 font-semibold cursor-not-allowed">
          « Prev
        </span>
      <?php endif; ?>

      <?php
      $start_page = max(1, $page - 1);
      $end_page = min($total_pages, $page + 1);
      
      if ($start_page > 1): ?>
        <a href="?page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?>" 
           class="px-4 py-2.5 text-sm bg-white border-2 border-gray-200 rounded-xl hover:bg-gray-50 hover:border-blue-300 transition text-gray-700 font-medium">
          1
        </a>
        <?php if ($start_page > 2): ?>
          <span class="px-2 text-gray-400 text-sm">...</span>
        <?php endif; ?>
      <?php endif; ?>

      <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
        <?php if ($i == $page): ?>
          <span class="px-4 py-2.5 text-sm bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-bold shadow-sm">
            <?php echo $i; ?>
          </span>
        <?php else: ?>
          <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?>" 
             class="px-4 py-2.5 text-sm bg-white border-2 border-gray-200 rounded-xl hover:bg-gray-50 hover:border-blue-300 transition text-gray-700 font-medium">
            <?php echo $i; ?>
          </a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($end_page < $total_pages): ?>
        <?php if ($end_page < $total_pages - 1): ?>
          <span class="px-2 text-gray-400 text-sm">...</span>
        <?php endif; ?>
        <a href="?page=<?php echo $total_pages; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?>" 
           class="px-4 py-2.5 text-sm bg-white border-2 border-gray-200 rounded-xl hover:bg-gray-50 hover:border-blue-300 transition text-gray-700 font-medium">
          <?php echo $total_pages; ?>
        </a>
      <?php endif; ?>

      <?php if ($page < $total_pages): ?>
        <a href="?page=<?php echo ($page + 1); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?>" 
           class="px-4 py-2.5 text-sm bg-white border-2 border-gray-200 rounded-xl hover:bg-gray-50 hover:border-blue-300 transition text-gray-700 font-semibold">
          Next »
        </a>
      <?php else: ?>
        <span class="px-4 py-2.5 text-sm bg-gray-100 border-2 border-gray-200 rounded-xl text-gray-400 font-semibold cursor-not-allowed">
          Next »
        </span>
      <?php endif; ?>

    </div>

    <div class="mt-4 text-center text-sm text-gray-600 font-medium">
      Showing <?php echo (($page - 1) * $items_per_page) + 1; ?> 
      to <?php echo min($page * $items_per_page, $total_items); ?> 
      of <?php echo $total_items; ?> items
    </div>
    <?php endif; ?>
  </main>

  <footer class="ml-16 md:ml-64">
    <?php include "footer.php"; ?>
  </footer>

  <!-- INCLUDE ALL MODALS -->
  <?php include "browse_equipment_modals.php"; ?>

  <!-- JAVASCRIPT -->
  <script src="js/equipment_browse.js"></script>
  <script>
    // Pass PHP variable to JavaScript
    currentPage = <?php echo $page; ?>;
  </script>

</body>
</html>