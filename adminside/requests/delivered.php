<?php
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$sql = "SELECT br.*, 
        e.name AS equipment_name, 
        e.description AS equipment_desc, 
        e.image AS equipment_photo,
        u.name AS user_name, u.email AS user_email, 
        CONCAT_WS(', ', u.street, u.landmark, u.barangay) AS address,
        bl.actual_pickup_date, bl.return_requested, bl.return_approved,
        approved_admin.name AS approved_by_name,
        br.approved_at,
        delivered_admin.name AS delivered_by_name,
        br.delivered_at
        FROM borrow_requests br
        JOIN equipment e ON br.equipment_id = e.id
        JOIN users u ON br.user_id = u.id
        LEFT JOIN borrow_logs bl ON br.id = bl.request_id
        LEFT JOIN users approved_admin ON br.approved_by = approved_admin.id
        LEFT JOIN users delivered_admin ON br.delivered_by = delivered_admin.id
        WHERE br.status = 'delivered'
        AND (bl.return_requested IS NULL OR bl.return_requested = 0)";

if ($search) $sql .= " AND (e.name LIKE '%$search%' OR u.name LIKE '%$search%')";
$sql .= " ORDER BY bl.actual_pickup_date DESC";
$result = $conn->query($sql);
?>

<div class="p-4">
    <!-- Compact Header -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <i data-feather="truck" class="w-5 h-5 text-blue-600"></i>
            Delivered <span class="text-sm font-normal text-gray-500">(<?= $result->num_rows ?>)</span>
        </h3>
        <form method="get" class="flex gap-2">
            <input type="hidden" name="tab" value="delivered">
            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>"
                   class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none w-48">
            <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i data-feather="search" class="w-4 h-4"></i>
            </button>
        </form>
    </div>

    <?php if ($result->num_rows > 0): ?>
    <!-- Compact Table View -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-blue-50 border-b border-blue-200">
                <tr>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Equipment</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Borrower</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Delivered</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Audit Trail</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Expected Return</th>
                    <th class="px-4 py-2 text-center font-semibold text-gray-700">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while ($row = $result->fetch_assoc()): 
                    $days_left = (strtotime($row['return_date']) - time()) / 86400;
                    $is_overdue = $days_left < 0;
                    $due_soon = !$is_overdue && $days_left <= 3;
                ?>
                <tr class="hover:bg-blue-50/30 transition <?= $is_overdue ? 'bg-red-50/30' : '' ?>">
                    <!-- Equipment Column -->
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <?php 
                            $imagePath = '../photos/' . basename($row['equipment_photo']);
                            ?>
                            <img src="<?= htmlspecialchars($imagePath) ?>" 
                                alt="Equipment" 
                                class="w-12 h-12 rounded object-cover"
                                onerror="this.src='../photos/placeholder.png'; this.onerror=null;">
                            <div>
                                <div class="font-medium text-gray-900"><?= htmlspecialchars($row['equipment_name']) ?></div>
                                <div class="text-xs text-gray-500 truncate max-w-xs"><?= htmlspecialchars($row['equipment_desc']) ?></div>
                            </div>
                        </div>
                    </td>

                    <!-- Borrower Column -->
                    <td class="px-4 py-3">
                        <div>
                            <div class="font-medium text-gray-900 flex items-center gap-1">
                                <i data-feather="user" class="w-3 h-3"></i>
                                <?= htmlspecialchars($row['user_name']) ?>
                            </div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars($row['user_email']) ?></div>
                        </div>
                    </td>

                    <!-- Delivered Column -->
                    <td class="px-4 py-3">
                        <div class="text-xs">
                            <div class="text-gray-700 font-medium">
                                <?= date('M d, Y', strtotime($row['actual_pickup_date'])) ?>
                            </div>
                            <div class="text-gray-500">
                                <?= date('h:i A', strtotime($row['actual_pickup_date'])) ?>
                            </div>
                            <?php if (!empty($row['delivery_photo'])): ?>
                            <button onclick="viewPhoto('<?= htmlspecialchars($row['delivery_photo']) ?>', 'Delivery Proof')"
                                    class="text-blue-600 hover:text-blue-700 flex items-center gap-1 mt-1">
                                <i data-feather="image" class="w-3 h-3"></i>
                                <span>View proof</span>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>

                    <!-- Audit Trail Column (NEW) -->
                    <td class="px-4 py-3">
                        <div class="text-xs space-y-1">
                            <?php if (!empty($row['approved_by_name'])): ?>
                            <div class="flex items-start gap-1">
                                <i data-feather="check-circle" class="w-3 h-3 text-green-600 mt-0.5 flex-shrink-0"></i>
                                <div>
                                    <div class="font-medium text-gray-700">Approved by:</div>
                                    <div class="text-gray-600"><?= htmlspecialchars($row['approved_by_name']) ?></div>
                                    <?php if (!empty($row['approved_at'])): ?>
                                    <div class="text-gray-400"><?= date('M d, g:i A', strtotime($row['approved_at'])) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($row['delivered_by_name'])): ?>
                            <div class="flex items-start gap-1 pt-1 border-t border-gray-200">
                                <i data-feather="truck" class="w-3 h-3 text-blue-600 mt-0.5 flex-shrink-0"></i>
                                <div>
                                    <div class="font-medium text-gray-700">Delivered by:</div>
                                    <div class="text-gray-600"><?= htmlspecialchars($row['delivered_by_name']) ?></div>
                                    <?php if (!empty($row['delivered_at'])): ?>
                                    <div class="text-gray-400"><?= date('M d, g:i A', strtotime($row['delivered_at'])) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </td>

                    <!-- Expected Return Column -->
                    <td class="px-4 py-3">
                        <div class="text-xs">
                            <div class="font-medium <?= $is_overdue ? 'text-red-600' : 'text-gray-700' ?>">
                                <?= date('M d, Y', strtotime($row['return_date'])) ?>
                            </div>
                            <?php if ($is_overdue): ?>
                            <div class="text-red-600 font-semibold flex items-center gap-1 mt-1">
                                <i data-feather="alert-circle" class="w-3 h-3"></i>
                                <?= abs(ceil($days_left)) ?> days overdue
                            </div>
                            <?php elseif ($due_soon): ?>
                            <div class="text-orange-600 font-medium flex items-center gap-1 mt-1">
                                <i data-feather="clock" class="w-3 h-3"></i>
                                <?= ceil($days_left) ?> days left
                            </div>
                            <?php else: ?>
                            <div class="text-gray-500">
                                <?= ceil($days_left) ?> days left
                            </div>
                            <?php endif; ?>
                        </div>
                    </td>

                    <!-- Status Column -->
                    <td class="px-4 py-3">
                        <div class="flex flex-col items-center gap-1">
                            <?php if ($is_overdue): ?>
                            <div class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                                <i data-feather="alert-triangle" class="w-3 h-3"></i>
                                <span>OVERDUE</span>
                            </div>
                            <?php elseif ($due_soon): ?>
                            <div class="inline-flex items-center gap-1 px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-medium">
                                <i data-feather="clock" class="w-3 h-3"></i>
                                <span>Due Soon</span>
                            </div>
                            <?php endif; ?>
                            <div class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                                <i data-feather="package" class="w-3 h-3"></i>
                                <span>With Borrower</span>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="text-center py-12 bg-white rounded-lg shadow">
        <i data-feather="truck" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
        <p class="text-gray-500">No items currently with borrowers</p>
    </div>
    <?php endif; ?>
</div>

<style>
table tbody tr:hover svg {
    color: inherit;
}

@keyframes pulse-bg {
    0%, 100% { background-color: rgba(254, 226, 226, 0.3); }
    50% { background-color: rgba(254, 226, 226, 0.5); }
}

tr.bg-red-50\/30 {
    animation: pulse-bg 2s ease-in-out infinite;
}

.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>