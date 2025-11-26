<?php
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$show_history = isset($_GET['history']) && $_GET['history'] == '1';

// Base query
$sql = "SELECT br.*, e.name AS equipment_name, e.description AS equipment_desc, e.image AS equipment_photo,
        u.name AS user_name, u.email AS user_email, 
        CONCAT_WS(', ', u.street, u.landmark, u.barangay) AS address,
        bl.actual_pickup_date, bl.actual_return_date, bl.return_photo, bl.is_damaged,
        approved_admin.name AS approved_by_name,
        br.approved_at,
        delivered_admin.name AS delivered_by_name,
        br.delivered_at,
        returned_admin.name AS returned_by_name,
        br.returned_at
        FROM borrow_requests br
        JOIN equipment e ON br.equipment_id = e.id
        JOIN users u ON br.user_id = u.id
        JOIN borrow_logs bl ON br.id = bl.request_id
        LEFT JOIN users approved_admin ON br.approved_by = approved_admin.id
        LEFT JOIN users delivered_admin ON br.delivered_by = delivered_admin.id
        LEFT JOIN users returned_admin ON br.returned_by = returned_admin.id
        WHERE br.status = 'returned'
        AND (bl.is_damaged IS NULL OR bl.is_damaged = 0)";

if ($search) $sql .= " AND (e.name LIKE '%$search%' OR u.name LIKE '%$search%')";
$sql .= " ORDER BY bl.actual_return_date DESC";

// Add limit for main view
if (!$show_history) $sql .= " LIMIT 10";

$result = $conn->query($sql);

// Get total count
$total_count = $conn->query("SELECT COUNT(*) as count FROM borrow_requests br 
                             JOIN borrow_logs bl ON br.id = bl.request_id 
                             WHERE br.status = 'returned' 
                             AND (bl.is_damaged IS NULL OR bl.is_damaged = 0)")->fetch_assoc()['count'] ?? 0;
?>

<div class="p-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i data-feather="check-square" class="w-5 h-5 text-green-600"></i>
                <?= $show_history ? 'Returned Items - Full History' : 'Returned Items - Recent' ?>
            </h3>
            <div class="flex flex-wrap items-center gap-3 mt-2 text-sm">
                <span class="text-gray-500">
                    Total Records: <span class="font-bold text-gray-700"><?= $total_count ?></span>
                </span>
                <?php if (!$show_history): ?>
                <span class="text-gray-300">•</span>
                <span class="text-blue-600 text-xs">(Showing 10 most recent)</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="flex flex-wrap gap-2">
            <!-- History Toggle Button -->
            <?php if ($show_history): ?>
            <a href="?tab=returned" 
               class="px-4 py-2 bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition flex items-center gap-2">
                <i data-feather="arrow-left" class="w-4 h-4"></i>
                <span>Back to Recent</span>
            </a>
            <?php else: ?>
            <a href="?tab=returned&history=1" 
               class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition flex items-center gap-2">
                <i data-feather="clock" class="w-4 h-4"></i>
                <span>View History</span>
            </a>
            <?php endif; ?>
            
            <!-- Search Form -->
            <form method="get" class="flex gap-2">
                <input type="hidden" name="tab" value="returned">
                <?php if ($show_history): ?>
                <input type="hidden" name="history" value="1">
                <?php endif; ?>
                <input type="text" name="search" placeholder="Search equipment or user..." 
                       value="<?= htmlspecialchars($search) ?>"
                       class="px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 outline-none w-64">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition">
                    <i data-feather="search" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>

    <?php if ($result->num_rows > 0): ?>
    <!-- Table Header -->
    <div class="bg-gray-50 rounded-t-xl border border-gray-200 border-b-0">
        <div class="grid grid-cols-12 gap-4 p-4 text-sm font-semibold text-gray-700">
            <div class="col-span-2">Equipment</div>
            <div class="col-span-2">Borrower</div>
            <div class="col-span-2">Timeline</div>
            <div class="col-span-3">Audit Trail</div>
            <div class="col-span-3 text-center">Action</div>
        </div>
    </div>

    <!-- Table Body -->
    <div class="border border-gray-200 rounded-b-xl bg-white divide-y divide-gray-200">
        <?php while ($row = $result->fetch_assoc()): ?>
        <div class="grid grid-cols-12 gap-4 p-4 items-center hover:bg-green-50/30 transition">
           <!-- Equipment Column -->
            <div class="col-span-2 flex gap-3 items-center">
                <?php 
                $imagePath = '../photos/' . basename($row['equipment_photo']);
                ?>
                <img src="<?= htmlspecialchars($imagePath) ?>" 
                     alt="Equipment" 
                     class="w-12 h-12 rounded-lg object-cover border border-gray-200 flex-shrink-0"
                     onerror="this.src='../photos/placeholder.png'; this.onerror=null;">
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-800 text-sm truncate"><?= htmlspecialchars($row['equipment_name']) ?></h4>
                    <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($row['equipment_desc']) ?></p>
                </div>
            </div>

            <!-- Borrower Column -->
            <div class="col-span-2 flex items-center gap-2">
                <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-green-600 rounded-lg flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                    <?= strtoupper(substr($row['user_name'], 0, 2)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm truncate">
                        <i data-feather="user" class="w-3 h-3 inline"></i>
                        <?= htmlspecialchars($row['user_name']) ?>
                    </p>
                    <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($row['user_email']) ?></p>
                </div>
            </div>

            <!-- Timeline Column -->
            <div class="col-span-2">
                <div class="bg-green-50 rounded-lg p-3 border border-green-100">
                    <div class="space-y-1 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 flex items-center gap-1">
                                <i data-feather="calendar" class="w-3 h-3"></i>
                                Borrowed:
                            </span>
                            <span class="font-medium"><?= date('M d, Y', strtotime($row['borrow_date'])) ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 flex items-center gap-1">
                                <i data-feather="truck" class="w-3 h-3"></i>
                                Delivered:
                            </span>
                            <span class="font-medium"><?= date('M d, Y', strtotime($row['actual_pickup_date'])) ?></span>
                        </div>
                        <div class="flex items-center justify-between pt-1 border-t border-green-200">
                            <span class="text-green-700 font-medium flex items-center gap-1">
                                <i data-feather="check-circle" class="w-3 h-3"></i>
                                Returned:
                            </span>
                            <span class="font-semibold text-green-700"><?= date('M d, Y', strtotime($row['actual_return_date'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Trail Column (NEW) -->
            <div class="col-span-3">
                <div class="bg-indigo-50 rounded-lg p-3 border border-indigo-100">
                    <div class="space-y-2 text-xs">
                        <?php if (!empty($row['approved_by_name'])): ?>
                        <div class="flex items-start gap-1">
                            <i data-feather="check-circle" class="w-3 h-3 text-green-600 mt-0.5 flex-shrink-0"></i>
                            <div class="flex-1">
                                <div class="font-medium text-gray-700">Approved by:</div>
                                <div class="text-gray-600"><?= htmlspecialchars($row['approved_by_name']) ?></div>
                                <?php if (!empty($row['approved_at'])): ?>
                                <div class="text-gray-400"><?= date('M d, g:i A', strtotime($row['approved_at'])) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($row['delivered_by_name'])): ?>
                        <div class="flex items-start gap-1 pt-1 border-t border-indigo-200">
                            <i data-feather="truck" class="w-3 h-3 text-blue-600 mt-0.5 flex-shrink-0"></i>
                            <div class="flex-1">
                                <div class="font-medium text-gray-700">Delivered by:</div>
                                <div class="text-gray-600"><?= htmlspecialchars($row['delivered_by_name']) ?></div>
                                <?php if (!empty($row['delivered_at'])): ?>
                                <div class="text-gray-400"><?= date('M d, g:i A', strtotime($row['delivered_at'])) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($row['returned_by_name'])): ?>
                        <div class="flex items-start gap-1 pt-1 border-t border-indigo-200">
                            <i data-feather="corner-down-left" class="w-3 h-3 text-purple-600 mt-0.5 flex-shrink-0"></i>
                            <div class="flex-1">
                                <div class="font-medium text-gray-700">Returned by:</div>
                                <div class="text-gray-600"><?= htmlspecialchars($row['returned_by_name']) ?></div>
                                <?php if (!empty($row['returned_at'])): ?>
                                <div class="text-gray-400"><?= date('M d, g:i A', strtotime($row['returned_at'])) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Action Column -->
            <div class="col-span-3 flex flex-col gap-1.5">
                <div class="flex items-center justify-center mb-1">
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-semibold">
                        ✓ Completed
                    </span>
                </div>
                <?php if (!empty($row['delivery_photo'])): ?>
                <button onclick="viewPhoto('<?= htmlspecialchars($row['delivery_photo']) ?>', 'Delivery Proof')"
                        class="w-full px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition text-xs flex items-center justify-center gap-1.5">
                    <i data-feather="package" class="w-3 h-3"></i>
                    <span>Delivery Photo</span>
                </button>
                <?php endif; ?>
                <?php if (!empty($row['return_photo'])): ?>
                <button onclick="viewPhoto('<?= htmlspecialchars($row['return_photo']) ?>', 'Return Proof')"
                        class="w-full px-3 py-1.5 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition text-xs flex items-center justify-center gap-1.5">
                    <i data-feather="check-square" class="w-3 h-3"></i>
                    <span>Return Photo</span>
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <?php if (!$show_history && $total_count > 10): ?>
    <div class="mt-6 text-center">
        <a href="?tab=returned&history=1" 
           class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition">
            <i data-feather="archive" class="w-4 h-4"></i>
            <span>View All <?= $total_count ?> Returned Records</span>
            <i data-feather="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="text-center py-16 bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl border border-green-100">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
            <i data-feather="check-square" class="w-10 h-10 text-green-600"></i>
        </div>
        <p class="text-gray-700 text-lg font-semibold mb-2">
            <?= $search ? 'No results found' : 'No returned items yet' ?>
        </p>
        <p class="text-gray-500 text-sm">
            <?= $search ? 'Try adjusting your search terms' : 'Returned items will appear here' ?>
        </p>
    </div>
    <?php endif; ?>
</div>

<style>
[data-feather] {
    display: inline-block;
    vertical-align: middle;
}

.transition {
    transition: all 0.2s ease-in-out;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>