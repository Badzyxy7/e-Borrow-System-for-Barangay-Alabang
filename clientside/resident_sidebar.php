<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar: Full width on desktop, Icon-only on mobile -->
<aside class="w-16 md:w-64 bg-blue-900 text-white shadow-xl fixed h-full z-30 transition-all duration-300">
  <!-- Logo Section -->
  <div class="p-4 md:p-6 font-bold text-xl border-b border-blue-700 flex items-center md:gap-3 justify-center md:justify-start">
    <!-- Circle logo -->
    <img src="../photos/logo.png" alt="Logo" class="w-10 h-10 rounded-full object-cover">
    <!-- Title - Hidden on mobile, visible on desktop -->
    <span class="hidden md:inline">Barangay Alabang</span>
  </div>

  <!-- Navigation -->
  <nav class="p-2 md:p-4 space-y-2">
    <!-- Dashboard -->
    <a href="resident_dashboard.php"
       class="flex items-center justify-center md:justify-start md:gap-3 px-2 md:px-4 py-3 md:py-2 rounded-lg transition group relative
       <?php echo $current_page=='resident_dashboard.php' ? 'bg-white text-blue-900 font-semibold' : 'hover:bg-blue-800'; ?>"
       title="Dashboard">
       <i data-feather="home" class="w-5 h-5 md:w-5 md:h-5"></i>
       <span class="hidden md:inline">Dashboard</span>
       <!-- Tooltip for mobile -->
       <span class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 md:opacity-0 pointer-events-none whitespace-nowrap z-50 transition-opacity">
         Dashboard
       </span>
    </a>

    <!-- Browse Equipment -->
    <a href="browse_equipment.php"
       class="flex items-center justify-center md:justify-start md:gap-3 px-2 md:px-4 py-3 md:py-2 rounded-lg transition group relative
       <?php echo $current_page=='browse_equipment.php' ? 'bg-white text-blue-900 font-semibold' : 'hover:bg-blue-800'; ?>"
       title="Browse Equipment">
       <i data-feather="box" class="w-5 h-5 md:w-5 md:h-5"></i>
       <span class="hidden md:inline">Browse Equipment</span>
       <!-- Tooltip for mobile -->
       <span class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 md:opacity-0 pointer-events-none whitespace-nowrap z-50 transition-opacity">
         Browse Equipment
       </span>
    </a>

    <!-- My Requests -->
    <a href="my_requests.php"
       class="flex items-center justify-center md:justify-start md:gap-3 px-2 md:px-4 py-3 md:py-2 rounded-lg transition group relative
       <?php echo $current_page=='my_requests.php' ? 'bg-white text-blue-900 font-semibold' : 'hover:bg-blue-800'; ?>"
       title="My Requests">
       <i data-feather="file-text" class="w-5 h-5 md:w-5 md:h-5"></i>
       <span class="hidden md:inline">My Requests</span>
       <!-- Tooltip for mobile -->
       <span class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 md:opacity-0 pointer-events-none whitespace-nowrap z-50 transition-opacity">
         My Requests
       </span>
    </a>

    <!-- My Borrowings -->
    <a href="my_borrowings.php"
       class="flex items-center justify-center md:justify-start md:gap-3 px-2 md:px-4 py-3 md:py-2 rounded-lg transition group relative
       <?php echo $current_page=='my_borrowings.php' ? 'bg-white text-blue-900 font-semibold' : 'hover:bg-blue-800'; ?>"
       title="My Borrowings">
       <i data-feather="book" class="w-5 h-5 md:w-5 md:h-5"></i>
       <span class="hidden md:inline">My Borrowings</span>
       <!-- Tooltip for mobile -->
       <span class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 md:opacity-0 pointer-events-none whitespace-nowrap z-50 transition-opacity">
         My Borrowings
       </span>
    </a>

    <!-- Logout -->
    <a href="#" id="logoutBtn"
       class="flex items-center justify-center md:justify-start md:gap-3 px-2 md:px-4 py-3 md:py-2 rounded-lg hover:bg-red-600 transition group relative"
       title="Logout">
       <i data-feather="log-out" class="w-5 h-5 md:w-5 md:h-5"></i>
       <span class="hidden md:inline">Logout</span>
       <!-- Tooltip for mobile -->
       <span class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 md:opacity-0 pointer-events-none whitespace-nowrap z-50 transition-opacity">
         Logout
       </span>
    </a>
  </nav>
</aside>

<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-lg p-6 w-80 mx-4 text-center">
    <h2 class="text-lg font-semibold mb-4">Confirm Logout</h2>
    <p class="text-gray-600 mb-6">Are you sure you want to log out?</p>
    <div class="flex justify-center gap-4">
      <button id="cancelLogout" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
      <a href="../logout.php" id="confirmLogout"
         class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Logout</a>
    </div>
  </div>
</div>

<!-- Feather Icons + Script -->
<script src="https://unpkg.com/feather-icons"></script>
<script>
  feather.replace();

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