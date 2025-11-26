<?php
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$sql = "SELECT br.*, e.name AS equipment_name, e.description AS equipment_desc, e.image AS equipment_photo,
        u.name AS user_name, u.email AS user_email, 
        CONCAT_WS(', ', u.street, u.landmark, u.barangay) AS address,
        bl.actual_pickup_date, bl.return_requested, bl.return_approved, bl.return_requested_at,
        approved_admin.name AS approved_by_name,
        br.approved_at,
        delivered_admin.name AS delivered_by_name,
        br.delivered_at
        FROM borrow_requests br
        JOIN equipment e ON br.equipment_id = e.id
        JOIN users u ON br.user_id = u.id
        JOIN borrow_logs bl ON br.id = bl.request_id
        LEFT JOIN users approved_admin ON br.approved_by = approved_admin.id
        LEFT JOIN users delivered_admin ON br.delivered_by = delivered_admin.id
        WHERE br.status = 'delivered'
        AND bl.return_requested = 1";
if ($search) $sql .= " AND (e.name LIKE '%$search%' OR u.name LIKE '%$search%')";
$sql .= " ORDER BY bl.return_approved ASC, bl.return_requested_at DESC";
$result = $conn->query($sql);
?>

<div class="p-4">
    <!-- Compact Header -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <i data-feather="corner-down-left" class="w-5 h-5 text-orange-600"></i>
            Return Requests <span class="text-sm font-normal text-gray-500">(<?= $result->num_rows ?>)</span>
        </h3>
        <form method="get" class="flex gap-2">
            <input type="hidden" name="tab" value="return_requests">
            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>"
                   class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none w-48">
            <button type="submit" class="px-3 py-1.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                <i data-feather="search" class="w-4 h-4"></i>
            </button>
        </form>
    </div>

    <?php if ($result->num_rows > 0): ?>
    <!-- Compact Table View -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-orange-50 border-b border-orange-200">
                <tr>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Equipment</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Borrower & Pickup Address</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Timeline</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Audit Trail</th>
                    <th class="px-4 py-2 text-center font-semibold text-gray-700">Status</th>
                    <th class="px-4 py-2 text-center font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while ($row = $result->fetch_assoc()): 
                    $is_approved = $row['return_approved'] == 1;
                ?>
                <tr class="hover:bg-orange-50/30 transition <?= $is_approved ? 'bg-purple-50/20' : '' ?>">
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

                    <!-- Borrower & Pickup Address Column -->
                    <td class="px-4 py-3">
                        <div>
                            <div class="font-medium text-gray-900 flex items-center gap-1">
                                <i data-feather="user" class="w-3 h-3"></i>
                                <?= htmlspecialchars($row['user_name']) ?>
                            </div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars($row['user_email']) ?></div>
                            <div class="text-xs text-orange-600 flex items-start gap-1 mt-1">
                                <i data-feather="map-pin" class="w-3 h-3 mt-0.5 flex-shrink-0"></i>
                                <span class="max-w-xs"><?= htmlspecialchars($row['address']) ?></span>
                            </div>
                        </div>
                    </td>

                    <!-- Timeline Column -->
                    <td class="px-4 py-3">
                        <div class="text-xs space-y-1">
                            <div class="flex items-center gap-1 text-gray-600">
                                <i data-feather="truck" class="w-3 h-3"></i>
                                <span class="font-medium">Delivered:</span>
                                <span><?= date('M d, Y', strtotime($row['actual_pickup_date'])) ?></span>
                            </div>
                            <div class="flex items-center gap-1 text-gray-600">
                                <i data-feather="calendar" class="w-3 h-3"></i>
                                <span class="font-medium">Expected:</span>
                                <span><?= date('M d, Y', strtotime($row['return_date'])) ?></span>
                            </div>
                            <div class="flex items-center gap-1 text-orange-600 font-medium">
                                <i data-feather="corner-down-left" class="w-3 h-3"></i>
                                <span>Requested:</span>
                                <span><?= $row['return_requested_at'] ? date('M d, Y', strtotime($row['return_requested_at'])) : 'N/A' ?></span>
                            </div>
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

                    <!-- Status Column -->
                    <td class="px-4 py-3">
                        <div class="flex flex-col items-center gap-1">
                            <?php if ($is_approved): ?>
                            <div class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">
                                <i data-feather="check-circle" class="w-3 h-3"></i>
                                <span>Return Approved</span>
                            </div>
                            <div class="text-xs text-gray-600 mt-1">Ready for Pickup</div>
                            <?php else: ?>
                            <div class="inline-flex items-center gap-1 px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-medium">
                                <i data-feather="bell" class="w-3 h-3"></i>
                                <span>Pending Approval</span>
                            </div>
                            <div class="text-xs text-gray-600 mt-1">New Request</div>
                            <?php endif; ?>
                        </div>
                    </td>

                    <!-- Action Column -->
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center">
                            <?php if ($is_approved): ?>
                            <button onclick="openReturnModal(<?= $row['id'] ?>)"
                                    class="px-3 py-1.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-1 text-xs shadow-sm"
                                    title="Mark as Returned">
                                <i data-feather="check-square" class="w-3 h-3"></i>
                                <span>Returned</span>
                            </button>
                            <?php else: ?>
                            <button onclick="openReturnApprovalModal(<?= $row['id'] ?>)"
                                    class="px-3 py-1.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition flex items-center gap-1 text-xs shadow-sm whitespace-nowrap"
                                    title="Approve Return">
                                <i data-feather="check" class="w-3 h-3"></i>
                                <span>Approve Return</span>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
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
table tbody tr:hover svg {
    color: inherit;
}

tr.bg-purple-50\/20 {
    background-color: rgba(243, 232, 255, 0.2);
}

tr.bg-purple-50\/20:hover {
    background-color: rgba(251, 207, 232, 0.3) !important;
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