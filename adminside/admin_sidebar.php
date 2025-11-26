<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-64 bg-gradient-to-r from-blue-900 to-blue-900 text-white shadow-xl fixed h-full">
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
<div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50">
  <div id="logoutModalContent" class="bg-white rounded-2xl shadow-2xl p-8 w-96 mx-4 text-center transform transition-all">
    <!-- Icon -->
    <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
      <i data-feather="log-out" class="w-8 h-8 text-red-600"></i>
    </div>
    
    <!-- Title -->
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Confirm Logout</h2>
    
    <!-- Message -->
    <p class="text-gray-600 mb-8">Are you sure you want to log out of your account?</p>
    
    <!-- Buttons -->
    <div class="flex gap-3">
      <button id="cancelLogout" class="flex-1 px-6 py-3 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium transition-colors duration-200">
        Cancel
      </button>
      <button id="confirmLogout"
         class="flex-1 px-6 py-3 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition-colors duration-200 shadow-lg hover:shadow-xl">
        Logout
      </button>
    </div>
  </div>
</div>

<!-- Logging Out Animation Modal -->
<div id="loggingOutModal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50">
  <div class="bg-white rounded-2xl shadow-2xl p-8 w-96 mx-4 text-center">
    <!-- Spinner -->
    <div class="mx-auto w-16 h-16 mb-4">
      <svg class="animate-spin text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>
    
    <!-- Message -->
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Logging Out...</h2>
    <p class="text-gray-600">Please wait while we log you out</p>
  </div>
</div>

<!-- Feather Icons + Script -->
<script src="https://unpkg.com/feather-icons"></script>
<script>
  feather.replace();

  // Logout modal
  const logoutBtn = document.getElementById('logoutBtn');
  const logoutModal = document.getElementById('logoutModal');
  const loggingOutModal = document.getElementById('loggingOutModal');
  const cancelLogout = document.getElementById('cancelLogout');
  const confirmLogout = document.getElementById('confirmLogout');
  
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

  // Confirm logout handler with animation
  confirmLogout.addEventListener('click', () => {
    // Hide confirmation modal
    logoutModal.classList.add('hidden');
    logoutModal.classList.remove('flex');
    
    // Show logging out animation
    loggingOutModal.classList.remove('hidden');
    loggingOutModal.classList.add('flex');
    
    // Redirect after a short delay
    setTimeout(() => {
      window.location.href = '../logout.php';
    }, 1000);
  });
</script>