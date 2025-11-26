<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../db.php";
include "requests/functions.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Store admin name in session for modal display (ADD THIS SECTION)
if (!isset($_SESSION['user_name'])) {
    $admin_id = $_SESSION['user_id'];
    $result = $conn->query("SELECT name FROM users WHERE id=$admin_id");
    if ($result && $row = $result->fetch_assoc()) {
        $_SESSION['user_name'] = $row['name'];
    }
}

// Handle POST actions from any sub-tab
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    handleRequestAction($conn, $_POST);
}

// Determine active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'pending';
$valid_tabs = ['pending', 'approved', 'delivered', 'return_requests', 'returned', 'damaged'];
if (!in_array($active_tab, $valid_tabs)) $active_tab = 'pending';

// Get counts for badges
$counts = getRequestCounts($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>E-Borrow System | Borrow Requests</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <style>
    #tab-content {
      transition: opacity 0.2s ease-in-out;
    }
    #tab-content.loading {
      opacity: 0.5;
      pointer-events: none;
    }
  </style>
</head>
<body class="flex bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen text-sm">
  <?php include "admin_sidebar.php"; ?>
  <?php $page_title = "Borrow Requests"; include "header_admin.php"; ?>

  <main class="flex-1 ml-64 pt-16 flex flex-col min-h-screen">
    <div class="px-8 py-6 border-b border-gray-200">
      <h2 class="text-2xl font-semibold text-gray-800">Borrow Requests Management</h2>
    </div>

    <div class="flex-1 px-8 py-6">
      <!-- Tab Navigation -->
      <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200 pb-4">
        <?php
        $tabs = [
            'pending' => ['label' => 'Pending', 'icon' => 'clock', 'color' => 'yellow'],
            'approved' => ['label' => 'Approved', 'icon' => 'check-circle', 'color' => 'green'],
            'delivered' => ['label' => 'Delivered', 'icon' => 'truck', 'color' => 'blue'],
            'return_requests' => ['label' => 'Return Requests', 'icon' => 'corner-down-left', 'color' => 'orange'],
            'returned' => ['label' => 'Returned', 'icon' => 'check-square', 'color' => 'gray'],
            'damaged' => ['label' => 'Damaged', 'icon' => 'alert-triangle', 'color' => 'red']
        ];
        foreach ($tabs as $key => $tab):
            $is_active = ($active_tab === $key);
            $count = $counts[$key] ?? 0;
            $bg = $is_active ? "bg-{$tab['color']}-600 text-white" : "bg-white text-gray-700 hover:bg-gray-100";
        ?>
        <button 
           onclick="loadTab('<?= $key ?>')" 
           data-tab="<?= $key ?>"
           data-color="<?= $tab['color'] ?>"
           class="flex items-center gap-2 px-4 py-2 rounded-xl font-medium transition-all shadow-sm <?= $bg ?> tab-button">
          <i data-feather="<?= $tab['icon'] ?>" class="w-4 h-4"></i>
          <span><?= $tab['label'] ?></span>
          <?php if ($count > 0): ?>
          <span class="badge px-2 py-0.5 text-xs rounded-full <?= $is_active ? 'bg-white/20' : "bg-{$tab['color']}-100 text-{$tab['color']}-700" ?>">
            <?= $count ?>
          </span>
          <?php endif; ?>
        </button>
        <?php endforeach; ?>
      </div>

      <!-- Tab Content -->
      <div id="tab-content" class="bg-white rounded-3xl shadow-xl overflow-hidden">
        <?php include "requests/{$active_tab}.php"; ?>
      </div>
    </div>

    <?php include "footer_admin.php"; ?>
  </main>

  <?php include "requests/modals.php"; ?>

  <script>
    let currentTab = '<?= $active_tab ?>';

    function loadTab(tabName) {
      if (currentTab === tabName) return;
      
      const content = document.getElementById('tab-content');
      
      // Add loading state
      content.classList.add('loading');
      
      // Fetch new content
      fetch(`requests/load_tab.php?tab=${tabName}`)
        .then(response => {
          if (!response.ok) throw new Error('Network response was not ok');
          return response.text();
        })
        .then(html => {
          content.innerHTML = html;
          content.classList.remove('loading');
          
          // Update URL without reload
          history.pushState({tab: tabName}, '', `?tab=${tabName}`);
          currentTab = tabName;
          
          // Update active tab styling
          updateTabStyles(tabName);
          
          // Re-initialize feather icons
          feather.replace();
          
          // Re-run any scripts if needed
          <?php include "requests/scripts.js"; ?>
        })
        .catch(error => {
          console.error('Error loading tab:', error);
          content.classList.remove('loading');
          content.innerHTML = '<div class="p-8 text-center text-red-600">Error loading content. Please try again.</div>';
        });
    }

    function updateTabStyles(activeTab) {
      document.querySelectorAll('.tab-button').forEach(btn => {
        const tab = btn.dataset.tab;
        const color = btn.dataset.color;
        const isActive = tab === activeTab;
        
        // Remove all state classes
        btn.classList.remove('bg-white', 'text-gray-700', 'hover:bg-gray-100');
        btn.classList.remove(`bg-${color}-600`, 'text-white');
        
        // Add appropriate classes
        if (isActive) {
          btn.classList.add(`bg-${color}-600`, 'text-white');
          // Update badge style
          const badge = btn.querySelector('.badge');
          if (badge) {
            badge.className = 'badge px-2 py-0.5 text-xs rounded-full bg-white/20';
          }
        } else {
          btn.classList.add('bg-white', 'text-gray-700', 'hover:bg-gray-100');
          // Update badge style
          const badge = btn.querySelector('.badge');
          if (badge) {
            badge.className = `badge px-2 py-0.5 text-xs rounded-full bg-${color}-100 text-${color}-700`;
          }
        }
      });
      
      // Re-initialize feather icons for the buttons
      feather.replace();
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(event) {
      if (event.state && event.state.tab) {
        currentTab = ''; // Reset to allow reload
        loadTab(event.state.tab);
      }
    });

    // Initialize the first state
    history.replaceState({tab: currentTab}, '', `?tab=${currentTab}`);

    feather.replace();
  </script>
  <script src="requests/scripts.js"></script>

  <?php renderLoadingScript(); ?>
</body>
</html>