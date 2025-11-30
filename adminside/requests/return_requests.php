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

// UPDATED QUERY: Added group_request_id and priority
$sql = "SELECT br.*, 
        e.name AS equipment_name, 
        e.description AS equipment_desc, 
        e.image AS equipment_photo,
        u.name AS user_name, 
        u.email AS user_email, 
        CONCAT_WS(', ', u.street, u.landmark, u.barangay) AS address,
        bl.actual_pickup_date, bl.return_requested, bl.return_approved, bl.return_requested_at,
        approved_admin.name AS approved_by_name,
        br.approved_at,
        delivered_admin.name AS delivered_by_name,
        br.delivered_at,
        br.group_request_id,
        br.priority,
        br.purpose,
        br.death_certificate
        FROM borrow_requests br
        JOIN equipment e ON br.equipment_id = e.id
        JOIN users u ON br.user_id = u.id
        JOIN borrow_logs bl ON br.id = bl.request_id
        LEFT JOIN users approved_admin ON br.approved_by = approved_admin.id
        LEFT JOIN users delivered_admin ON br.delivered_by = delivered_admin.id
        WHERE br.status = 'delivered'
        AND bl.return_requested = 1";

if ($search) $sql .= " AND (e.name LIKE '%$search%' OR u.name LIKE '%$search%')";
$sql .= " ORDER BY bl.return_approved ASC, br.priority DESC, bl.return_requested_at DESC LIMIT 10";
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

// Re-sort to maintain priority order (unapproved first, then by priority)
usort($display_requests, function($a, $b) {
    $aApproved = ($a['type'] === 'group') ? $a['data']['main']['return_approved'] : $a['data']['return_approved'];
    $bApproved = ($b['type'] === 'group') ? $b['data']['main']['return_approved'] : $b['data']['return_approved'];
    
    // Unapproved returns should come first
    if ($aApproved != $bApproved) {
        return $aApproved - $bApproved;
    }
    
    // Then sort by priority
    $aPriority = ($a['type'] === 'group') ? $a['data']['main']['priority'] : $a['data']['priority'];
    $bPriority = ($b['type'] === 'group') ? $b['data']['main']['priority'] : $b['data']['priority'];
    
    if ($aPriority != $bPriority) {
        return $bPriority - $aPriority;
    }
    
    // Finally by request date
    $aDate = ($a['type'] === 'group') ? $a['data']['main']['return_requested_at'] : $a['data']['return_requested_at'];
    $bDate = ($b['type'] === 'group') ? $b['data']['main']['return_requested_at'] : $b['data']['return_requested_at'];
    
    $aTime = !empty($aDate) ? strtotime($aDate) : 0;
$bTime = !empty($bDate) ? strtotime($bDate) : 0;
return $bTime - $aTime;
});

$total_count = count($display_requests);

// Get actual total count from database
$actual_total = $conn->query("SELECT COUNT(*) as count FROM borrow_requests br 
                             JOIN borrow_logs bl ON br.id = bl.request_id 
                             WHERE br.status = 'delivered' 
                             AND bl.return_requested = 1")->fetch_assoc()['count'] ?? 0;
?>

<div class="p-4">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i data-feather="corner-down-left" class="w-5 h-5 text-orange-600"></i>
                Return Requests <span class="text-sm font-normal text-gray-500">(Showing <?= $total_count ?> most recent)</span>
            </h3>
            <p class="text-xs text-gray-500 mt-1">Total: <?= $actual_total ?> records</p>
        </div>
        <form method="get" class="flex gap-2">
            <input type="hidden" name="tab" value="return_requests">
            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>"
                   class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none w-48">
            <button type="submit" class="px-3 py-1.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
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
                        $isPriority = ($mainData['priority'] == 1);
                        $is_approved = $mainData['return_approved'] == 1;
                        ?>
                        
                        <!-- GROUP REQUEST ROW -->
                        <tr class="hover:bg-gray-50 transition <?= $isPriority ? 'bg-purple-50' : '' ?> <?= $is_approved ? 'bg-purple-50/20' : '' ?> border-l-4 border-orange-500">
                            <!-- Equipment Column -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="w-10 h-10 bg-orange-100 rounded flex items-center justify-center">
                                            <i data-feather="layers" class="w-5 h-5 text-orange-600"></i>
                                        </div>
                                        <span class="absolute -top-1 -right-1 bg-orange-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center"><?= $itemCount ?></span>
                                    </div>
                                    <div>
                                        <div class="font-bold text-orange-700 flex items-center gap-2">
                                            Group Request
                                            <span class="px-2 py-0.5 bg-orange-100 text-orange-700 text-xs rounded-full"><?= $itemCount ?> items</span>
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

                            <!-- Timeline Column -->
                            <td class="px-4 py-3">
                                <div class="text-xs">
                                    <div class="text-gray-600">
                                        Delivered: <?= formatDate($mainData['actual_pickup_date']) ?>
                                    </div>
                                    <div class="text-gray-600 mt-1">
                                        Expected: <?= formatDate($mainData['return_date']) ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Status Column -->
                            <td class="px-4 py-3">
                                <?php if ($is_approved): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">
                                    <i data-feather="check-circle" class="w-3 h-3"></i>
                                    Return Approved
                                </span>
                                <?php else: ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-medium">
                                    <i data-feather="bell" class="w-3 h-3"></i>
                                    Pending Approval
                                </span>
                                <?php endif; ?>
                            </td>

                            <!-- Actions Column -->
                           <!-- Actions Column for GROUP requests -->
<td class="px-4 py-3">
    <div class="flex items-center justify-center gap-2">
        <button onclick='openGroupDetailsModal(<?= json_encode($group) ?>, "return_requests")'
                class="px-3 py-1.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition flex items-center gap-1 text-xs"
                title="View Group Details">
            <i data-feather="eye" class="w-3 h-3"></i>
            <span>Details</span>
        </button>
        <?php if ($is_approved): ?>
        <button onclick="openGroupReturnModal('<?= $group_id ?>', <?= $itemCount ?>)"
                class="px-3 py-1.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-1 text-xs"
                title="Mark Group as Returned">
            <i data-feather="check-square" class="w-3 h-3"></i>
            <span>Mark Returned</span>
        </button>
        <?php else: ?>
        <button onclick="openGroupReturnApprovalModal('<?= $group_id ?>', <?= $itemCount ?>)"
                class="px-3 py-1.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition flex items-center gap-1 text-xs"
                title="Approve Group Return">
            <i data-feather="check" class="w-3 h-3"></i>
            <span>Approve Return</span>
        </button>
        <?php endif; ?>
    </div>
</td>
                        </tr>
                        
                    <?php else: ?>
                        <?php 
                        $row = $request['data'];
                        $is_approved = $row['return_approved'] == 1;
                        ?>
                        
                        <!-- INDIVIDUAL REQUEST ROW -->
                        <tr class="hover:bg-gray-50 transition <?= $row['priority'] == 1 ? 'bg-purple-50' : '' ?> <?= $is_approved ? 'bg-purple-50/20' : '' ?>">
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
                                        <?php if ($row['priority'] == 1): ?>
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
                                <div class="font-medium text-gray-900"><?= htmlspecialchars($row['user_name']) ?></div>
                            </td>

                            <!-- Timeline Column -->
                            <td class="px-4 py-3">
                                <div class="text-xs">
                                    <div class="text-gray-600">
                                        Delivered: <?= formatDate($row['actual_pickup_date']) ?>
                                    </div>
                                    <div class="text-gray-600 mt-1">
                                        Expected: <?= formatDate($row['return_date']) ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Status Column -->
                            <td class="px-4 py-3">
                                <?php if ($is_approved): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">
                                    <i data-feather="check-circle" class="w-3 h-3"></i>
                                    Return Approved
                                </span>
                                <?php else: ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-medium">
                                    <i data-feather="bell" class="w-3 h-3"></i>
                                    Pending Approval
                                </span>
                                <?php endif; ?>
                            </td>

                            <!-- Actions Column -->
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick='openDetailsModal(<?= json_encode($row) ?>, "return_requests")'
                                            class="px-3 py-1.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition flex items-center gap-1 text-xs"
                                            title="View Details">
                                        <i data-feather="eye" class="w-3 h-3"></i>
                                        <span>Details</span>
                                    </button>
                                    <?php if ($is_approved): ?>
                                    <button onclick="openReturnModal(<?= $row['id'] ?>)"
                                            class="px-3 py-1.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-1 text-xs"
                                            title="Mark as Returned">
                                        <i data-feather="check-square" class="w-3 h-3"></i>
                                        <span>Returned</span>
                                    </button>
                                    <?php else: ?>
                                    <button onclick="openReturnApprovalModal(<?= $row['id'] ?>)"
                                            class="px-3 py-1.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition flex items-center gap-1 text-xs"
                                            title="Approve Return">
                                        <i data-feather="check" class="w-3 h-3"></i>
                                        <span>Approve</span>
                                    </button>
                                    <?php endif; ?>
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
        <i data-feather="corner-down-left" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
        <p class="text-gray-500">No pending return requests</p>
    </div>
    <?php endif; ?>
</div>

<style>
/* Subtle animation for priority rows */
@keyframes priorityPulse {
    0%, 100% { background-color: rgb(250, 245, 255); }
    50% { background-color: rgb(243, 232, 255); }
}

.bg-purple-50 {
    animation: priorityPulse 3s ease-in-out infinite;
}

/* Approved return requests background */
tr.bg-purple-50\/20 {
    background-color: rgba(243, 232, 255, 0.2);
}

tr.bg-purple-50\/20:hover {
    background-color: rgba(243, 232, 255, 0.4) !important;
}

/* Group request highlight */
.border-l-4.border-orange-500 {
    background: linear-gradient(90deg, rgba(249, 115, 22, 0.05) 0%, transparent 100%);
}
</style>