<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-64 bg-blue-900 text-white shadow-xl fixed h-full">
  <div class="p-6 font-bold text-xl border-b border-blue-700 flex items-center gap-3">
    <!-- Circle logo -->
    <img src="../photos/logo.png" alt="Logo" class="w-10 h-10 rounded-full object-cover">
    <!-- Title -->
    <span>Barangay Alabang</span>
  </div>
  <nav class="p-4 space-y-2">
    <a href="admin_dashboard.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg transition <?php echo $current_page=='admin_dashboard.php' ? 'bg-white text-blue-900 font-semibold' : 'hover:bg-blue-800'; ?>">
       <i data-feather="home"></i> Dashboard
    </a>
    <a href="inventory.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg transition <?php echo $current_page=='inventory.php' ? 'bg-white text-blue-900 font-semibold' : 'hover:bg-blue-800'; ?>">
       <i data-feather="box"></i> Inventory
    </a>
    <a href="requests_tab.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg transition <?php echo $current_page=='requests_tab.php' ? 'bg-white text-blue-900 font-semibold' : 'hover:bg-blue-800'; ?>">
       <i data-feather="file-text"></i> Requests
    </a>
    <a href="users_tab.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg transition <?php echo $current_page=='users_tab.php' ? 'bg-white text-blue-900 font-semibold' : 'hover:bg-blue-800'; ?>">
       <i data-feather="users"></i> Users
    </a>
    <a href="schedule_tab.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg transition <?php echo $current_page=='schedule_tab.php' ? 'bg-white text-blue-900 font-semibold' : 'hover:bg-blue-800'; ?>">
       <i data-feather="calendar"></i> Schedule
    </a>
    <a href="reports_tab.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg transition <?php echo $current_page=='reports_tab.php' ? 'bg-white text-blue-900 font-semibold' : 'hover:bg-blue-800'; ?>">
       <i data-feather="bar-chart-2"></i> Reports
    </a>
    <a href="settings_tab.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg transition <?php echo $current_page=='settings_tab.php' ? 'bg-white text-blue-900 font-semibold' : 'hover:bg-blue-800'; ?>">
       <i data-feather="settings"></i> Settings
    </a>
    <a href="#" id="logoutBtn"
       class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-red-600 transition">
       <i data-feather="log-out"></i> Logout
    </a>
  </nav>
</aside>


<!-- Logout Confirmation Modal -->
  <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-80 text-center">
      <h2 class="text-lg font-semibold mb-4">Confirm Logout</h2>
      <p class="text-gray-600 mb-6">Are you sure you want to log out?</p>
      <div class="flex justify-center gap-4">
        <button id="cancelLogout" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
        <a href="../logout.php" id="confirmLogout"
           class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Logout</a>
      </div>
    </div>
  </div>
<script>
  // Logout modal
    const logoutBtn = document.getElementById('logoutBtn');
    const logoutModal = document.getElementById('logoutModal');
    const cancelLogout = document.getElementById('cancelLogout');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', e => {
        e.preventDefault();
        logoutModal.classList.remove('hidden');
        logoutModal.classList.add('flex');
      });
    }
    cancelLogout.addEventListener('click', () => {
      logoutModal.classList.add('hidden');
      logoutModal.classList.remove('flex');
    });
</script>
