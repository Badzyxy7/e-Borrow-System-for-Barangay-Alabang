<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-64 bg-blue-900 text-white shadow-xl fixed h-full">
  <div class="p-6 font-bold text-xl border-b border-blue-700">Barangay Alabang</div>
  <nav class="p-4 space-y-2">
    <a href="resident_dashboard.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg transition <?php echo $current_page=='resident_dashboard.php' ? 'bg-white text-blue-900 font-semibold' : 'hover:bg-blue-800'; ?>">
       <i data-feather="home"></i> Dashboard
    </a>
    <a href="browse_equipment.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg transition <?php echo $current_page=='browse_equipment.php' ? 'bg-white text-blue-900 font-semibold' : 'hover:bg-blue-800'; ?>">
       <i data-feather="box"></i> Browse Equipment
    </a>
    <a href="my_requests.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg transition <?php echo $current_page=='my_requests.php' ? 'bg-white text-blue-900 font-semibold' : 'hover:bg-blue-800'; ?>">
       <i data-feather="file-text"></i> My Requests
    </a>
    <a href="my_borrowings.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg transition <?php echo $current_page=='my_borrowings.php' ? 'bg-white text-blue-900 font-semibold' : 'hover:bg-blue-800'; ?>">
       <i data-feather="book"></i> My Borrowings
    </a>
   <a href="../landingpage/logout.php"
   class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-red-600 transition">
   <i data-feather="log-out"></i> Logout
</a>

  </nav>
</aside>
