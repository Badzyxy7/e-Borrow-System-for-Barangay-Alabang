<?php
function formatDate($date, $format = 'M d, Y') {
    if (empty($date) || $date == '0000-00-00 00:00:00') {
        return 'N/A';
    }
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return 'N/A';
    }
    return date($format, $timestamp);
}

function formatDateTime($date) {
    if (empty($date) || $date == '0000-00-00 00:00:00') {
        return 'N/A';
    }
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return 'N/A';
    }
    return date('M d, Y g:i A', $timestamp);
}
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Query for COMPLETED RETURNS (actual_return_date IS NOT NULL)
$sql = "SELECT br.*, 
        e.name AS equipment_name, 
        e.description AS equipment_desc, 
        e.image AS equipment_photo,
        u.name AS user_name, 
        u.email AS user_email, 
        CONCAT_WS(', ', u.street, u.landmark, u.barangay) AS address,
        bl.actual_pickup_date, 
        bl.actual_return_date,
        bl.return_photo,
        bl.is_damaged,
        bl.damage_fee,
        bl.damage_notes,
        bl.payment_photo,
        approved_admin.name AS approved_by_name,
        br.approved_at,
        delivered_admin.name AS delivered_by_name,
        br.delivered_at,
        returned_admin.name AS returned_by_name,
        br.returned_at,
        br.group_request_id,
        br.priority,
        br.purpose,
        br.death_certificate
        FROM borrow_logs bl
        JOIN borrow_requests br ON bl.request_id = br.id
        JOIN equipment e ON br.equipment_id = e.id
        JOIN users u ON br.user_id = u.id
        LEFT JOIN users approved_admin ON br.approved_by = approved_admin.id
        LEFT JOIN users delivered_admin ON br.delivered_by = delivered_admin.id
        LEFT JOIN users returned_admin ON br.returned_by = returned_admin.id
        WHERE bl.actual_return_date IS NOT NULL
        AND br.status = 'returned'";

if ($search) $sql .= " AND (e.name LIKE '%$search%' OR u.name LIKE '%$search%')";
$sql .= " ORDER BY bl.actual_return_date DESC LIMIT 50";
$result = $conn->query($sql);

// Group requests by group_request_id
$grouped_requests = [];
$individual_requests = [];

while ($row = $result->fetch_assoc()) {
    if (!empty($row['group_request_id'])) {
        if (!isset($grouped_requests[$row['group_request_id']])) {
            $grouped_requests[$row['group_request_id']] = [
                'main' => $row,
                'items' => []
            ];
        }
        $grouped_requests[$row['group_request_id']]['items'][] = $row;
    } else {
        $individual_requests[] = $row;
    }
}

// Merge for display
$display_requests = [];

foreach ($grouped_requests as $group_id => $group) {
    $display_requests[] = [
        'type' => 'group',
        'group_id' => $group_id,
        'data' => $group
    ];
}

foreach ($individual_requests as $req) {
    $display_requests[] = [
        'type' => 'individual',
        'data' => $req
    ];
}

// Sort by return date (most recent first)
usort($display_requests, function($a, $b) {
    $aDate = ($a['type'] === 'group') ? $a['data']['main']['actual_return_date'] : $a['data']['actual_return_date'];
    $bDate = ($b['type'] === 'group') ? $b['data']['main']['actual_return_date'] : $b['data']['actual_return_date'];
    
    return strtotime($bDate) - strtotime($aDate);
});

$total_count = count($display_requests);

// Get actual total count from database
$actual_total = $conn->query("SELECT COUNT(*) as count FROM borrow_logs bl
                             JOIN borrow_requests br ON bl.request_id = br.id
                             WHERE bl.actual_return_date IS NOT NULL
                             AND br.status = 'returned'")->fetch_assoc()['count'] ?? 0;
?>

<div class="p-4">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i data-feather="check-square" class="w-5 h-5 text-gray-600"></i>
                Completed Returns <span class="text-sm font-normal text-gray-500">(Showing <?= $total_count ?> most recent)</span>
            </h3>
            <p class="text-xs text-gray-500 mt-1">Total: <?= $actual_total ?> records</p>
        </div>
        <form method="get" class="flex gap-2">
            <input type="hidden" name="tab" value="returned">
            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>"
                   class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 outline-none w-48">
            <button type="submit" class="px-3 py-1.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                <i data-feather="search" class="w-4 h-4"></i>
            </button>
        </form>
    </div>

    <?php if ($total_count > 0): ?>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Equipment</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Borrower</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Timeline</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($display_requests as $request): ?>
                    <?php if ($request['type'] === 'group'): ?>
                        <?php 
                        $group = $request['data'];
                        $mainData = $group['main'];
                        $items = $group['items'];
                        $itemCount = count($items);
                        $hasDamage = false;
                        foreach ($items as $item) {
                            if ($item['is_damaged'] == 1) {
                                $hasDamage = true;
                                break;
                            }
                        }
                        ?>
                        
                        <!-- GROUP REQUEST ROW -->
                        <tr class="hover:bg-gray-50 transition border-l-4 border-gray-400 bg-gray-50/20">
                            <!-- Equipment Column -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center">
                                            <i data-feather="layers" class="w-5 h-5 text-gray-600"></i>
                                        </div>
                                        <span class="absolute -top-1 -right-1 bg-gray-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center"><?= $itemCount ?></span>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-700 flex items-center gap-2">
                                            Group Return
                                            <span class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded-full"><?= $itemCount ?> items</span>
                                        </div>
                                        <div class="text-xs text-gray-600 mt-0.5">
                                            <?php 
                                            $equipmentNames = array_slice(array_map(fn($item) => $item['equipment_name'], $items), 0, 2);
                                            echo htmlspecialchars(implode(', ', $equipmentNames));
                                            if ($itemCount > 2) echo ' +' . ($itemCount - 2) . ' more';
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Borrower Column -->
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900"><?= htmlspecialchars($mainData['user_name']) ?></div>
                            </td>

                            <!-- Timeline Column -->
                            <td class="px-4 py-3">
                                <div class="text-xs space-y-1">
                                    <div class="text-gray-600">
                                        <span class="font-medium">Borrowed:</span> <?= formatDate($mainData['actual_pickup_date']) ?>
                                    </div>
                                    <div class="text-gray-700 font-medium">
                                        <span class="font-semibold">Returned:</span> <?= formatDateTime($mainData['actual_return_date']) ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Status Column -->
                            <td class="px-4 py-3">
                                <?php if ($hasDamage): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                                    <i data-feather="alert-triangle" class="w-3 h-3"></i>
                                    Has Damage
                                </span>
                                <?php else: ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">
                                    <i data-feather="check-circle" class="w-3 h-3"></i>
                                    Completed
                                </span>
                                <?php endif; ?>
                            </td>

                            <!-- Actions Column -->
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick='openGroupDetailsModal(<?= json_encode($group) ?>, "returned")'
                                            class="px-3 py-1.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition flex items-center gap-1 text-xs"
                                            title="View Group Details">
                                        <i data-feather="eye" class="w-3 h-3"></i>
                                        <span>View Details</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                    <?php else: ?>
                        <?php 
                        $row = $request['data'];
                        $isDamaged = ($row['is_damaged'] == 1);
                        ?>
                        
                        <!-- INDIVIDUAL REQUEST ROW -->
                        <tr class="hover:bg-gray-50 transition <?= $isDamaged ? 'bg-red-50/30' : 'bg-gray-50/20' ?>">
                            <!-- Equipment Column -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <?php 
                                    $imagePath = '../photos/' . basename($row['equipment_photo']);
                                    ?>
                                    <img src="<?= htmlspecialchars($imagePath) ?>" 
                                        alt="Equipment" 
                                        class="w-10 h-10 rounded object-cover"
                                        onerror="this.src='../photos/placeholder.png'; this.onerror=null;">
                                    <div>
                                        <div class="font-medium text-gray-900"><?= htmlspecialchars($row['equipment_name']) ?></div>
                                        <div class="text-xs text-gray-500">Qty: <?= $row['qty'] ?></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Borrower Column -->
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900"><?= htmlspecialchars($row['user_name']) ?></div>
                            </td>

                            <!-- Timeline Column -->
                            <td class="px-4 py-3">
                                <div class="text-xs space-y-1">
                                    <div class="text-gray-600">
                                        <span class="font-medium">Borrowed:</span> <?= formatDate($row['actual_pickup_date']) ?>
                                    </div>
                                    <div class="text-gray-700 font-medium">
                                        <span class="font-semibold">Returned:</span> <?= formatDateTime($row['actual_return_date']) ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Status Column -->
                            <td class="px-4 py-3">
                                <?php if ($isDamaged): ?>
                                <div class="space-y-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                                        <i data-feather="alert-triangle" class="w-3 h-3"></i>
                                        Damaged
                                    </span>
                                    <?php if (!empty($row['damage_fee'])): ?>
                                    <div class="text-xs text-red-700 font-medium">
                                        Fee: ₱<?= number_format($row['damage_fee'], 2) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">
                                    <i data-feather="check-circle" class="w-3 h-3"></i>
                                    Good Condition
                                </span>
                                <?php endif; ?>
                            </td>

                            <!-- Actions Column -->
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick='openDetailsModal(<?= json_encode($row) ?>, "returned")'
                                            class="px-3 py-1.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition flex items-center gap-1 text-xs"
                                            title="View Details">
                                        <i data-feather="eye" class="w-3 h-3"></i>
                                        <span>View Details</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="text-center py-12 bg-white rounded-lg shadow">
        <i data-feather="check-square" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
        <p class="text-gray-500">No completed returns yet</p>
    </div>
    <?php endif; ?>
</div>

<style>
/* Group request highlight */
.border-l-4.border-gray-400 {
    background: linear-gradient(90deg, rgba(156, 163, 175, 0.05) 0%, transparent 100%);
}

/* Completed returns background */
tr.bg-gray-50\/20 {
    background-color: rgba(249, 250, 251, 0.3);
}

tr.bg-gray-50\/20:hover {
    background-color: rgba(243, 244, 246, 0.5) !important;
}

/* Damaged items highlight */
tr.bg-red-50\/30 {
    background-color: rgba(254, 242, 242, 0.4);
}

tr.bg-red-50\/30:hover {
    background-color: rgba(254, 226, 226, 0.5) !important;
}
</style>