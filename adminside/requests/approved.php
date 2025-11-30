<?php
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$sql = "SELECT br.*, 
        e.name AS equipment_name, 
        e.description AS equipment_desc, 
        e.image AS equipment_photo,
        u.name AS user_name, 
        u.email AS user_email,
        CONCAT_WS(', ', u.street, u.landmark, u.barangay) AS address,
        approved_admin.name AS approved_by_name,
        br.approved_at,
        br.group_request_id,
        br.priority,
        br.purpose
        FROM borrow_requests br
        JOIN equipment e ON br.equipment_id = e.id
        JOIN users u ON br.user_id = u.id
        LEFT JOIN users approved_admin ON br.approved_by = approved_admin.id
        WHERE br.status = 'approved'";
if ($search) $sql .= " AND (e.name LIKE '%$search%' OR u.name LIKE '%$search%')";
$sql .= " ORDER BY br.priority DESC, br.created_at DESC";
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

// Re-sort to maintain priority order
usort($display_requests, function($a, $b) {
    $aPriority = ($a['type'] === 'group') ? $a['data']['main']['priority'] : $a['data']['priority'];
    $bPriority = ($b['type'] === 'group') ? $b['data']['main']['priority'] : $b['data']['priority'];
    
    if ($aPriority != $bPriority) {
        return $bPriority - $aPriority;
    }
    
    $aDate = ($a['type'] === 'group') ? $a['data']['main']['created_at'] : $a['data']['created_at'];
    $bDate = ($b['type'] === 'group') ? $b['data']['main']['created_at'] : $b['data']['created_at'];
    
    return strtotime($bDate) - strtotime($aDate);
});

$total_count = count($display_requests);
?>

<div class="p-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <i data-feather="check-circle" class="w-5 h-5 text-green-600"></i>
            Approved <span class="text-sm font-normal text-gray-500">(<?= $total_count ?>)</span>
        </h3>
        <form method="get" class="flex gap-2">
            <input type="hidden" name="tab" value="approved">
            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>"
                   class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none w-48">
            <button type="submit" class="px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700">
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
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Dates</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Approved</th>
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
                        $isPriority = ($mainData['priority'] == 1);
                        ?>
                        
                        <!-- GROUP REQUEST ROW -->
                        <tr class="hover:bg-gray-50 transition <?= $isPriority ? 'bg-purple-50' : '' ?> border-l-4 border-green-500">
                            <!-- Equipment Column -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="w-10 h-10 bg-green-100 rounded flex items-center justify-center">
                                            <i data-feather="layers" class="w-5 h-5 text-green-600"></i>
                                        </div>
                                        <span class="absolute -top-1 -right-1 bg-green-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center"><?= $itemCount ?></span>
                                    </div>
                                    <div>
                                        <div class="font-bold text-green-700 flex items-center gap-2">
                                            Group Request
                                            <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full"><?= $itemCount ?> items</span>
                                        </div>
                                        <div class="text-xs text-gray-600 mt-0.5">
                                            <?php 
                                            $equipmentNames = array_slice(array_map(fn($item) => $item['equipment_name'], $items), 0, 2);
                                            echo htmlspecialchars(implode(', ', $equipmentNames));
                                            if ($itemCount > 2) echo ' +' . ($itemCount - 2) . ' more';
                                            ?>
                                        </div>
                                        <?php if ($isPriority): ?>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-200 text-purple-800 text-xs font-semibold rounded-full mt-1">
                                                <i data-feather="alert-circle" class="w-3 h-3"></i>
                                                Priority
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Borrower Column -->
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900"><?= htmlspecialchars($mainData['user_name']) ?></div>
                            </td>

                            <!-- Dates Column -->
                            <td class="px-4 py-3">
                                <div class="text-xs text-gray-600">
                                    <?= date('M d', strtotime($mainData['borrow_date'])) ?> - <?= date('M d, Y', strtotime($mainData['return_date'])) ?>
                                </div>
                            </td>

                            <!-- Approved Column -->
                            <td class="px-4 py-3">
                                <div class="text-xs text-gray-600">
                                    <?= date('M d, Y', strtotime($mainData['approved_at'])) ?>
                                </div>
                                <?php if (!empty($mainData['approved_by_name'])): ?>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    by <?= htmlspecialchars($mainData['approved_by_name']) ?>
                                </div>
                                <?php endif; ?>
                            </td>

                            <!-- Actions Column -->
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick='openGroupDetailsModal(<?= json_encode($group) ?>, "approved")'
                                            class="px-3 py-1.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition flex items-center gap-1 text-xs"
                                            title="View Group Details">
                                        <i data-feather="eye" class="w-3 h-3"></i>
                                        <span>Details</span>
                                    </button>
                                    <button onclick='openGroupDeliveryModal("<?= $request['group_id'] ?>", <?= json_encode($items) ?>)'
                                            class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-1 text-xs"
                                            title="Mark All as Delivered">
                                        <i data-feather="truck" class="w-3 h-3"></i>
                                        <span>Deliver All</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                    <?php else: ?>
                        <?php $row = $request['data']; ?>
                        
                        <!-- INDIVIDUAL REQUEST ROW -->
                        <tr class="hover:bg-gray-50 transition <?= $row['priority'] == 1 ? 'bg-purple-50' : '' ?>">
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
                                        <?php if ($row['priority'] == 1): ?>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-200 text-purple-800 text-xs font-semibold rounded-full mt-1">
                                                <i data-feather="alert-circle" class="w-3 h-3"></i>
                                                Priority
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900"><?= htmlspecialchars($row['user_name']) ?></div>
                            </td>

                            <td class="px-4 py-3">
                                <div class="text-xs text-gray-600">
                                    <?= date('M d', strtotime($row['borrow_date'])) ?> - <?= date('M d, Y', strtotime($row['return_date'])) ?>
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <div class="text-xs text-gray-600">
                                    <?= date('M d, Y', strtotime($row['approved_at'])) ?>
                                </div>
                                <?php if (!empty($row['approved_by_name'])): ?>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    by <?= htmlspecialchars($row['approved_by_name']) ?>
                                </div>
                                <?php endif; ?>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick='openDetailsModal(<?= json_encode($row) ?>, "approved")'
                                            class="px-3 py-1.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition flex items-center gap-1 text-xs"
                                            title="View Details">
                                        <i data-feather="eye" class="w-3 h-3"></i>
                                        <span>Details</span>
                                    </button>
                                    <button onclick="openDeliveryModal(<?= $row['id'] ?>)"
                                            class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-1 text-xs"
                                            title="Mark as Delivered">
                                        <i data-feather="truck" class="w-3 h-3"></i>
                                        <span>Deliver</span>
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
        <i data-feather="check-circle" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
        <p class="text-gray-500">No approved requests awaiting delivery</p>
    </div>
    <?php endif; ?>
</div>

<style>
/* Group request highlight */
.border-l-4.border-green-500 {
    background: linear-gradient(90deg, rgba(34, 197, 94, 0.05) 0%, transparent 100%);
}

/* Priority rows */
.bg-purple-50 {
    animation: priorityPulse 3s ease-in-out infinite;
}

@keyframes priorityPulse {
    0%, 100% { background-color: rgb(250, 245, 255); }
    50% { background-color: rgb(243, 232, 255); }
}
</style>