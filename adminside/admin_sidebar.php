<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-64 bg-blue-900 text-white shadow-xl fixed h-full">
  <div class="p-6 font-bold text-xl border-b border-blue-700">Barangay Alabang</div>
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
   <a href="../landingpage/logout.php"
   class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-red-600 transition">
   <i data-feather="log-out"></i> Logout
</a>

  </nav>
</aside>
