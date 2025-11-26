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

// Pagination configuration
$records_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page);

// Calculate offset
$offset = ($current_page - 1) * $records_per_page;

// Get total number of records
$count_sql = "SELECT COUNT(*) as total FROM borrow_requests WHERE user_id=$user_id";
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get paginated records
$sql = "SELECT br.*, e.name AS equipment, e.image AS equipment_image
        FROM borrow_requests br
        JOIN equipment e ON br.equipment_id = e.id
        WHERE br.user_id=$user_id
        ORDER BY br.created_at DESC
        LIMIT $records_per_page OFFSET $offset";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>My Requests | E-Borrow System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen flex flex-col">

  <!-- Sidebar include -->
  <?php include "resident_sidebar.php"; ?>

  <!-- Header include -->
  <?php 
    $page_title = "My Requests"; 
    include "header.php"; 
  ?>

  <!-- Main Content - Responsive margins and padding -->
  <main class="flex-1 ml-16 md:ml-64 p-4 sm:p-6 lg:p-8 pt-24 md:pt-28 lg:pt-32">
    <!-- Page Header -->
    <div class="mb-4 sm:mb-6">
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">My Borrow Requests</h1>
      <p class="text-sm sm:text-base text-gray-600 mt-1">View and track all your equipment borrow requests</p>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
      
      <!-- Mobile Card View -->
      <div class="block md:hidden">
        <?php if ($result->num_rows > 0): ?>
          <?php $result->data_seek(0); ?>
          <?php while ($row = $result->fetch_assoc()): ?>
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
                  <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                      Qty: <?php echo $row['qty']; ?>
                    </span>
                    <?php
                    $status = strtolower($row['status']);
                    $statusConfig = [
                      'pending' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'border' => 'border-amber-300', 'icon' => 'clock'],
                      'approved' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'border' => 'border-emerald-300', 'icon' => 'check-circle'],
                      'rejected' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-700', 'border' => 'border-rose-300', 'icon' => 'x-circle'],
                      'declined' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'border' => 'border-red-300', 'icon' => 'slash'],
                      'returned' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'border' => 'border-blue-300', 'icon' => 'rotate-ccw']
                    ];
                    $config = $statusConfig[$status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-300', 'icon' => 'info'];
                    ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border <?php echo $config['bg'] . ' ' . $config['text'] . ' ' . $config['border']; ?>">
                      <i data-feather="<?php echo $config['icon']; ?>" class="w-3 h-3 mr-1"></i>
                      <?php echo ucfirst($status); ?>
                    </span>
                  </div>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-2 text-xs text-gray-600">
                <div class="flex items-center">
                  <i data-feather="calendar" class="w-3 h-3 mr-1 text-gray-500"></i>
                  <span>Borrow: <?php echo date("M d, Y", strtotime($row['borrow_date'])); ?></span>
                </div>
                <div class="flex items-center">
                  <i data-feather="calendar" class="w-3 h-3 mr-1 text-gray-500"></i>
                  <span>Return: <?php echo date("M d, Y", strtotime($row['return_date'])); ?></span>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="px-6 py-12 text-center">
            <div class="flex flex-col items-center justify-center">
              <div class="bg-gray-100 rounded-full p-4 mb-4">
                <i data-feather="inbox" class="w-12 h-12 text-gray-400"></i>
              </div>
              <p class="text-gray-500 text-base font-medium">No requests found</p>
              <p class="text-gray-400 text-sm mt-1">You haven't made any borrow requests yet</p>
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
              <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Borrow Date</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Return Date</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Status</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php $result->data_seek(0); ?>
            <?php if ($result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
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
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                      <?php echo $row['qty']; ?>
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center text-sm text-gray-700">
                      <i data-feather="calendar" class="w-4 h-4 mr-2 text-gray-500"></i>
                      <?php echo date("M d, Y", strtotime($row['borrow_date'])); ?>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center text-sm text-gray-700">
                      <i data-feather="calendar" class="w-4 h-4 mr-2 text-gray-500"></i>
                      <?php echo date("M d, Y", strtotime($row['return_date'])); ?>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <?php
                    $status = strtolower($row['status']);
                    $statusConfig = [
                      'pending' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'border' => 'border-amber-300', 'icon' => 'clock'],
                      'approved' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'border' => 'border-emerald-300', 'icon' => 'check-circle'],
                      'rejected' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-700', 'border' => 'border-rose-300', 'icon' => 'x-circle'],
                      'declined' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'border' => 'border-red-300', 'icon' => 'slash'],
                      'returned' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'border' => 'border-blue-300', 'icon' => 'rotate-ccw']
                    ];
                    $config = $statusConfig[$status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-300', 'icon' => 'info'];
                    ?>
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold border <?php echo $config['bg'] . ' ' . $config['text'] . ' ' . $config['border']; ?>">
                      <i data-feather="<?php echo $config['icon']; ?>" class="w-4 h-4 mr-1.5"></i>
                      <?php echo ucfirst($status); ?>
                    </span>
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
                    <p class="text-gray-500 text-lg font-medium">No requests found</p>
                    <p class="text-gray-400 text-sm mt-1">You haven't made any borrow requests yet</p>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination - Mobile Responsive -->
      <?php if ($total_pages > 1): ?>
        <div class="bg-gray-50 px-4 sm:px-6 py-4 border-t border-gray-200">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <!-- Results Info -->
            <div class="text-xs sm:text-sm text-gray-700 text-center sm:text-left">
              Showing 
              <span class="font-semibold text-blue-600"><?php echo $offset + 1; ?></span>
              to 
              <span class="font-semibold text-blue-600"><?php echo min($offset + $records_per_page, $total_records); ?></span>
              of 
              <span class="font-semibold text-blue-600"><?php echo $total_records; ?></span>
              results
            </div>

            <!-- Pagination buttons -->
            <div class="flex gap-1 sm:gap-2 flex-wrap justify-center">
              <!-- Previous button -->
              <?php if ($current_page > 1): ?>
                <a href="?page=<?php echo $current_page - 1; ?>" 
                   class="inline-flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition duration-150">
                  <i data-feather="chevron-left" class="w-3 h-3 sm:w-4 sm:h-4 mr-1"></i>
                  <span class="hidden sm:inline">Previous</span>
                  <span class="sm:hidden">Prev</span>
                </a>
              <?php else: ?>
                <span class="inline-flex items-center px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                  <i data-feather="chevron-left" class="w-3 h-3 sm:w-4 sm:h-4 mr-1"></i>
                  <span class="hidden sm:inline">Previous</span>
                  <span class="sm:hidden">Prev</span>
                </span>
              <?php endif; ?>

              <!-- Page numbers -->
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

              <!-- Next button -->
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

  <!-- Footer include -->
  <footer class="ml-16 md:ml-64">
    <?php include "footer.php"; ?>
  </footer>

  <script>
    feather.replace();
  </script>
</body>
</html>