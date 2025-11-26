<?php
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$sql = "SELECT br.*, e.name AS equipment_name, e.description AS equipment_desc, e.image AS equipment_photo,
        u.name AS user_name, u.email AS user_email, 
        CONCAT_WS(', ', u.street, u.landmark, u.barangay) AS address
        FROM borrow_requests br
        JOIN equipment e ON br.equipment_id = e.id
        JOIN users u ON br.user_id = u.id
        WHERE br.status = 'pending'";
if ($search) $sql .= " AND (e.name LIKE '%$search%' OR u.name LIKE '%$search%')";
$sql .= " ORDER BY br.created_at DESC";
$result = $conn->query($sql);
?>

<div class="p-4">
    <!-- Compact Header -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <i data-feather="clock" class="w-5 h-5 text-yellow-600"></i>
            Pending <span class="text-sm font-normal text-gray-500">(<?= $result->num_rows ?>)</span>
        </h3>
        <form method="get" class="flex gap-2">
            <input type="hidden" name="tab" value="pending">
            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>"
                   class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 outline-none w-48">
            <button type="submit" class="px-3 py-1.5 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                <i data-feather="search" class="w-4 h-4"></i>
            </button>
        </form>
    </div>

    <?php if ($result->num_rows > 0): ?>
    <!-- Compact Table View -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Equipment</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Borrower</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Dates</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Requested</th>
                    <th class="px-4 py-2 text-center font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50 transition">
                    <!-- Equipment Column -->
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <?php 
                            // Extract just the filename and build correct path
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
                            <div class="font-medium text-gray-900"><?= htmlspecialchars($row['user_name']) ?></div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars($row['user_email']) ?></div>
                            <div class="text-xs text-gray-400 truncate max-w-xs"><?= htmlspecialchars($row['address']) ?></div>
                        </div>
                    </td>

                    <!-- Dates Column -->
                    <td class="px-4 py-3">
                        <div class="text-xs">
                            <div class="flex items-center gap-1 text-gray-700">
                                <i data-feather="calendar" class="w-3 h-3"></i>
                                <span><?= date('M d', strtotime($row['borrow_date'])) ?></span>
                            </div>
                            <div class="flex items-center gap-1 text-gray-700 mt-1">
                                <i data-feather="calendar" class="w-3 h-3"></i>
                                <span><?= date('M d', strtotime($row['return_date'])) ?></span>
                            </div>
                            <div class="text-gray-500 mt-1">
                                <?php 
                                $days = (strtotime($row['return_date']) - strtotime($row['borrow_date'])) / 86400;
                                echo ceil($days) . ' days';
                                ?>
                            </div>
                        </div>
                    </td>

                    <!-- Requested Column -->
                    <td class="px-4 py-3">
                        <div class="text-xs text-gray-600">
                            <?= date('M d, Y', strtotime($row['created_at'])) ?>
                        </div>
                        <div class="text-xs text-gray-400">
                            <?= date('h:i A', strtotime($row['created_at'])) ?>
                        </div>
                    </td>

                    <!-- Actions Column -->
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick='openApprovalModal(<?= json_encode($row) ?>)'
                                    class="px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-1 text-xs shadow-sm"
                                    title="Approve Request">
                                <i data-feather="check" class="w-3 h-3"></i>
                                <span>Approve</span>
                            </button>
                            <button onclick="openRejectModal(<?= $row['id'] ?>)"
                                    class="px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center gap-1 text-xs shadow-sm"
                                    title="Reject Request">
                                <i data-feather="x" class="w-3 h-3"></i>
                                <span>Reject</span>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="text-center py-12 bg-white rounded-lg shadow">
        <i data-feather="inbox" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
        <p class="text-gray-500">No pending requests</p>
    </div>
    <?php endif; ?>
</div>

<style>
/* Ensure feather icons render properly */
table tbody tr:hover svg {
    color: inherit;
}

/* Compact scrollbar for table overflow */
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