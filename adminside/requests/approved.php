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
        br.approved_at
        FROM borrow_requests br
        JOIN equipment e ON br.equipment_id = e.id
        JOIN users u ON br.user_id = u.id
        LEFT JOIN users approved_admin ON br.approved_by = approved_admin.id
        WHERE br.status = 'approved'";
if ($search) $sql .= " AND (e.name LIKE '%$search%' OR u.name LIKE '%$search%')";
$sql .= " ORDER BY br.created_at DESC";
$result = $conn->query($sql);
?>

<div class="p-4">
    <!-- Compact Header -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <i data-feather="check-circle" class="w-5 h-5 text-green-600"></i>
            Approved <span class="text-sm font-normal text-gray-500">(<?= $result->num_rows ?>)</span>
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

    <?php if ($result->num_rows > 0): ?>
    <!-- Compact Table View -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-green-50 border-b border-green-200">
                <tr>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Equipment</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Borrower & Address</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Schedule</th>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Approved By</th>
                    <th class="px-4 py-2 text-center font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-green-50/30 transition">
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

                    <!-- Borrower & Address Column -->
                    <td class="px-4 py-3">
                        <div>
                            <div class="font-medium text-gray-900 flex items-center gap-1">
                                <i data-feather="user" class="w-3 h-3"></i>
                                <?= htmlspecialchars($row['user_name']) ?>
                            </div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars($row['user_email']) ?></div>
                            <div class="text-xs text-blue-600 flex items-start gap-1 mt-1">
                                <i data-feather="map-pin" class="w-3 h-3 mt-0.5 flex-shrink-0"></i>
                                <span class="max-w-xs"><?= htmlspecialchars($row['address']) ?></span>
                            </div>
                        </div>
                    </td>

                    <!-- Schedule Column -->
                    <td class="px-4 py-3">
                        <div class="text-xs space-y-1">
                            <div class="flex items-center gap-1 text-gray-700">
                                <i data-feather="calendar" class="w-3 h-3"></i>
                                <span class="font-medium">Borrow:</span>
                                <span><?= date('M d, Y', strtotime($row['borrow_date'])) ?></span>
                            </div>
                            <div class="flex items-center gap-1 text-gray-700">
                                <i data-feather="calendar" class="w-3 h-3"></i>
                                <span class="font-medium">Return:</span>
                                <span><?= date('M d, Y', strtotime($row['return_date'])) ?></span>
                            </div>
                            <div class="text-gray-500">
                                <?php 
                                $days = (strtotime($row['return_date']) - strtotime($row['borrow_date'])) / 86400;
                                echo ceil($days) . ' days';
                                ?>
                            </div>
                        </div>
                    </td>

                    <!-- Approved By Column (NEW) -->
                    <td class="px-4 py-3">
                        <div class="text-xs">
                            <?php if (!empty($row['approved_by_name'])): ?>
                                <div class="flex items-center gap-1 text-green-700 font-medium">
                                    <i data-feather="user-check" class="w-3 h-3"></i>
                                    <span><?= htmlspecialchars($row['approved_by_name']) ?></span>
                                </div>
                                <?php if (!empty($row['approved_at'])): ?>
                                <div class="text-gray-500 mt-1">
                                    <?= date('M d, Y', strtotime($row['approved_at'])) ?>
                                </div>
                                <div class="text-gray-400">
                                    <?= date('g:i A', strtotime($row['approved_at'])) ?>
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-gray-400 italic">No data</span>
                            <?php endif; ?>
                        </div>
                    </td>

                    <!-- Action Column -->
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center">
                            <button onclick="openDeliveryModal(<?= $row['id'] ?>)"
                                    class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-1 text-xs shadow-sm"
                                    title="Mark as Delivered">
                                <i data-feather="truck" class="w-3 h-3"></i>
                                <span>Delivered</span>
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
        <i data-feather="check-circle" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
        <p class="text-gray-500">No approved requests awaiting delivery</p>
    </div>
    <?php endif; ?>
</div>

<style>
table tbody tr:hover svg {
    color: inherit;
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