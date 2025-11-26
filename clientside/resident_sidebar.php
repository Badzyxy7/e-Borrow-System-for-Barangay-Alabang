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
  const loggingOutModal = document.getElementById('loggingOutModal');  // NEW: Reference to logging out modal
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

  // NEW: Confirm logout handler with animation
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