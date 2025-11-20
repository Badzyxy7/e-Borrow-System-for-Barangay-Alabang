<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../db.php";

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle booking actions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_booking':
                $title = $conn->real_escape_string($_POST['title']);
                $description = $conn->real_escape_string($_POST['description']);
                $user_id = intval($_POST['user_id']);
                $equipment_id = intval($_POST['equipment_id']);
                $start_date = $_POST['start_date'];
                $end_date = $_POST['end_date'];
                $status = 'pending';
                
                $sql = "INSERT INTO bookings (title, description, user_id, equipment_id, start_date, end_date, status) 
                        VALUES ('$title', '$description', $user_id, $equipment_id, '$start_date', '$end_date', '$status')";
                
                if ($conn->query($sql)) {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?success=added");
                    exit();
                } else {
                    echo "<script>alert('Error adding booking!');</script>";
                }
                break;
                
            case 'update_status':
                $booking_id = intval($_POST['booking_id']);
                $new_status = $conn->real_escape_string($_POST['status']);
                
                $sql = "UPDATE bookings SET status='$new_status' WHERE id=$booking_id";
                
                if ($conn->query($sql)) {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?success=updated");
                    exit();
                } else {
                    echo "<script>alert('Error updating booking!');</script>";
                }
                break;
                
            case 'delete_booking':
                $booking_id = intval($_POST['booking_id']);
                
                $sql = "DELETE FROM bookings WHERE id=$booking_id";
                
                if ($conn->query($sql)) {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?success=deleted");
                    exit();
                } else {
                    echo "<script>alert('Error deleting booking!');</script>";
                }
                break;
        }
    }
}

// Success message
$success_message = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'added': $success_message = 'Booking added successfully!'; break;
        case 'updated': $success_message = 'Booking status updated!'; break;
        case 'deleted': $success_message = 'Booking deleted successfully!'; break;
    }
}

// Get statistics
$today = date('Y-m-d');
$next_week = date('Y-m-d', strtotime('+7 days'));

// Today's events
$todays_events = $conn->query("SELECT COUNT(*) as count FROM borrow_logs WHERE DATE(borrow_date) = '$today'")->fetch_assoc()['count'];

// Upcoming events (next 7 days)
$upcoming_events = $conn->query("SELECT COUNT(*) as count FROM borrow_logs WHERE DATE(expected_return_date) BETWEEN '$today' AND '$next_week' AND actual_return_date IS NULL")->fetch_assoc()['count'];

// Active bookings (currently borrowed)
$active_bookings = $conn->query("SELECT COUNT(*) as count FROM borrow_logs WHERE actual_return_date IS NULL")->fetch_assoc()['count'];

// Utilization (simple calculation: booked days / total days this month * 100)
$current_month_start = date('Y-m-01');
$current_month_end = date('Y-m-t');
$booked_days = $conn->query("SELECT COUNT(DISTINCT DATE(borrow_date)) as count FROM borrow_logs WHERE DATE(borrow_date) BETWEEN '$current_month_start' AND '$current_month_end'")->fetch_assoc()['count'];
$total_days = date('t');
$utilization = $total_days > 0 ? round(($booked_days / $total_days) * 100) : 0;

// Get all borrow logs with user and equipment info
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

$sql = "SELECT bl.*, u.name as user_name, e.name as equipment_name, e.id as equipment_id
        FROM borrow_logs bl 
        LEFT JOIN users u ON bl.user_id = u.id 
        LEFT JOIN equipment e ON bl.equipment_id = e.id 
        WHERE 1=1";

if ($search) $sql .= " AND (u.name LIKE '%$search%' OR e.name LIKE '%$search%')";
if ($status_filter) {
    if ($status_filter == 'active') {
        $sql .= " AND bl.actual_return_date IS NULL";
    } elseif ($status_filter == 'returned') {
        $sql .= " AND bl.actual_return_date IS NOT NULL";
    }
}

$sql .= " ORDER BY bl.borrow_date DESC";
$bookings_result = $conn->query($sql);

// Get users and equipment for dropdowns
$users = $conn->query("SELECT id, name FROM users WHERE role = 'resident'");
$equipment = $conn->query("SELECT id, name FROM equipment");

// Generate color palette for equipment
$equipment_colors = [];
$colors = [
    ['bg' => 'bg-blue-500', 'text' => 'text-blue-700', 'border' => 'border-blue-600'],
    ['bg' => 'bg-green-500', 'text' => 'text-green-700', 'border' => 'border-green-600'],
    ['bg' => 'bg-purple-500', 'text' => 'text-purple-700', 'border' => 'border-purple-600'],
    ['bg' => 'bg-pink-500', 'text' => 'text-pink-700', 'border' => 'border-pink-600'],
    ['bg' => 'bg-yellow-500', 'text' => 'text-yellow-700', 'border' => 'border-yellow-600'],
    ['bg' => 'bg-indigo-500', 'text' => 'text-indigo-700', 'border' => 'border-indigo-600'],
    ['bg' => 'bg-red-500', 'text' => 'text-red-700', 'border' => 'border-red-600'],
    ['bg' => 'bg-teal-500', 'text' => 'text-teal-700', 'border' => 'border-teal-600'],
];

$equipment_list = $conn->query("SELECT id, name FROM equipment");
$color_index = 0;
while ($eq = $equipment_list->fetch_assoc()) {
    $equipment_colors[$eq['id']] = $colors[$color_index % count($colors)];
    $color_index++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Borrow System | Schedule & Booking</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
            background-color: #e5e7eb;
            border: 1px solid #e5e7eb;
        }
        .calendar-cell {
            background: white;
            min-height: 100px;
            padding: 8px;
            position: relative;
            overflow: hidden;
        }
        .calendar-event {
            font-size: 0.75rem;
            padding: 2px 4px;
            margin-bottom: 2px;
            border-radius: 4px;
            border-left: 3px solid;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .calendar-event:hover {
            transform: translateX(2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 10;
        }
        .tooltip {
            visibility: hidden;
            position: absolute;
            z-index: 50;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            min-width: 250px;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .calendar-event:hover .tooltip {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>

<body class="flex bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen text-sm">

    <!-- Sidebar -->
    <?php include "admin_sidebar.php"; ?>
    
    <!-- Header -->
    <?php 
        $page_title = "Schedule & Booking"; 
        include "header_admin.php"; 
    ?>

    <!-- Main Content -->
    <main class="flex-1 ml-64 pt-16 flex flex-col min-h-screen">

        <!-- Page Title -->
        <div class="px-8 py-6 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-800">Schedule & Booking Calendar</h2>
        </div>

        <!-- Page Content -->
        <div class="flex-1 px-8 py-6">

            <!-- Success Message -->
            <?php if ($success_message): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-2xl shadow">
                <div class="flex items-center gap-2">
                    <i data-feather="check-circle" class="w-5 h-5"></i>
                    <span><?php echo htmlspecialchars($success_message); ?></span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Today's Pickups</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $todays_events; ?></p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl shadow-lg">
                            <i data-feather="calendar" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Due Soon (7 days)</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $upcoming_events; ?></p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl shadow-lg">
                            <i data-feather="clock" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Currently Borrowed</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $active_bookings; ?></p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-2xl shadow-lg">
                            <i data-feather="activity" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Utilization</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $utilization; ?>%</p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl shadow-lg">
                            <i data-feather="trending-up" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View Toggle -->
            <div class="mb-6">
                <div class="bg-white rounded-xl p-1 inline-flex shadow-md">
                    <button id="calendarViewBtn" onclick="showCalendarView()" class="px-6 py-2 rounded-lg bg-blue-600 text-white transition font-medium">
                        <i data-feather="calendar" class="w-4 h-4 inline mr-2"></i>Calendar
                    </button>
                    <button id="listViewBtn" onclick="showListView()" class="px-6 py-2 rounded-lg text-gray-600 hover:text-gray-800 transition font-medium">
                        <i data-feather="list" class="w-4 h-4 inline mr-2"></i>List
                    </button>
                </div>
            </div>

            <!-- Calendar View -->
            <div id="calendarView" class="bg-white rounded-3xl shadow-xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold"><?php echo date('F Y'); ?></h2>
                    <div class="flex gap-4 text-sm flex-wrap">
                        <?php
                        $eq_legend = $conn->query("SELECT id, name FROM equipment LIMIT 8");
                        while ($eq = $eq_legend->fetch_assoc()):
                            $color = $equipment_colors[$eq['id']];
                        ?>
                        <span class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded <?php echo $color['bg']; ?> border-2 <?php echo $color['border']; ?>"></div>
                            <span class="text-gray-700"><?php echo htmlspecialchars($eq['name']); ?></span>
                        </span>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Calendar Grid -->
                <div class="calendar-grid">
                    <!-- Day headers -->
                    <?php
                    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    foreach ($days as $day): ?>
                        <div class="calendar-cell font-semibold text-center bg-gray-50 text-gray-700 py-3">
                            <?php echo $day; ?>
                        </div>
                    <?php endforeach; ?>

                    <!-- Calendar days -->
                    <?php
                    $first_day = date('w', strtotime($current_month_start));
                    $days_in_month = date('t');
                    
                    // Empty cells for days before month starts
                    for ($i = 0; $i < $first_day; $i++): ?>
                        <div class="calendar-cell bg-gray-50"></div>
                    <?php endfor;

                    // Days of the month
                    for ($day = 1; $day <= $days_in_month; $day++):
                        $current_date = date('Y-m-d', strtotime("$current_month_start +".($day-1)." days"));
                        $is_today = $current_date == $today ? 'ring-2 ring-blue-500' : '';
                    ?>
                        <div class="calendar-cell <?php echo $is_today; ?>">
                            <div class="text-sm font-semibold mb-2 <?php echo $current_date == $today ? 'text-blue-600' : 'text-gray-600'; ?>">
                                <?php echo $day; ?>
                            </div>
                            <div class="space-y-1">
                                <?php
                                // Get events that span this date
                                $daily_events = $conn->query("
                                    SELECT bl.*, u.name as user_name, e.name as equipment_name, e.id as equipment_id
                                    FROM borrow_logs bl 
                                    LEFT JOIN users u ON bl.user_id = u.id 
                                    LEFT JOIN equipment e ON bl.equipment_id = e.id 
                                    WHERE '$current_date' BETWEEN DATE(borrow_date) AND DATE(expected_return_date)
                                    AND bl.actual_return_date IS NULL
                                    ORDER BY borrow_date
                                ");
                                
                                while ($event = $daily_events->fetch_assoc()):
                                    $color = $equipment_colors[$event['equipment_id']] ?? $colors[0];
                                    $start_date = date('M d', strtotime($event['borrow_date']));
                                    $end_date = date('M d', strtotime($event['expected_return_date']));
                                ?>
                                    <div class="calendar-event bg-<?php echo str_replace('bg-', '', $color['bg']); ?>-100 border-<?php echo str_replace('border-', '', $color['border']); ?> <?php echo $color['text']; ?> relative">
                                        <?php echo htmlspecialchars(substr($event['equipment_name'], 0, 12)); ?>
                                        <?php if(strlen($event['equipment_name']) > 12) echo '...'; ?>
                                        
                                        <!-- Tooltip -->
                                        <div class="tooltip">
                                            <div class="font-semibold text-gray-800 mb-2 text-base">
                                                <?php echo htmlspecialchars($event['equipment_name']); ?>
                                            </div>
                                            <div class="space-y-1 text-sm text-gray-600">
                                                <div><strong>Borrower:</strong> <?php echo htmlspecialchars($event['user_name']); ?></div>
                                                <div><strong>Quantity:</strong> <?php echo $event['qty']; ?></div>
                                                <div><strong>Period:</strong> <?php echo $start_date; ?> → <?php echo $end_date; ?></div>
                                                <div><strong>Pickup:</strong> <?php echo $event['actual_pickup_date'] ? date('M d, Y', strtotime($event['actual_pickup_date'])) : 'Pending'; ?></div>
                                                <?php
                                                $days_remaining = floor((strtotime($event['expected_return_date']) - time()) / 86400);
                                                if ($days_remaining < 0): ?>
                                                    <div class="text-red-600 font-semibold">Overdue by <?php echo abs($days_remaining); ?> days</div>
                                                <?php elseif ($days_remaining <= 3): ?>
                                                    <div class="text-yellow-600 font-semibold">Due in <?php echo $days_remaining; ?> days</div>
                                                <?php else: ?>
                                                    <div class="text-green-600">Due in <?php echo $days_remaining; ?> days</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- List View -->
            <div id="listView" class="hidden">
                <!-- Search and Filter -->
                <form method="get" class="flex flex-col md:flex-row gap-4 mb-6">
                    <input type="text" name="search" placeholder="Search by borrower or equipment..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm">
                    <select name="status" 
                            class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm">
                        <option value="">All Status</option>
                        <option value="active" <?php if($status_filter=="active") echo "selected"; ?>>Currently Borrowed</option>
                        <option value="returned" <?php if($status_filter=="returned") echo "selected"; ?>>Returned</option>
                    </select>
                    <button type="submit" 
                            class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition-all duration-300 shadow-lg flex items-center gap-2">
                        <i data-feather="search" class="w-5 h-5"></i>
                        <span class="font-medium">Filter</span>
                    </button>
                </form>

                <!-- Bookings Table -->
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Borrower</th>
                                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Equipment</th>
                                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Quantity</th>
                                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Borrow Period</th>
                                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if($bookings_result->num_rows > 0): ?>
                                    <?php while ($booking = $bookings_result->fetch_assoc()): 
                                        $color = $equipment_colors[$booking['equipment_id']] ?? $colors[0];
                                    ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-gray-800">
                                            <?php echo htmlspecialchars($booking['user_name']); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <div class="w-3 h-3 rounded-full <?php echo $color['bg']; ?>"></div>
                                                <span class="text-gray-700"><?php echo htmlspecialchars($booking['equipment_name']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full font-medium">
                                                <?php echo $booking['qty']; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 text-sm">
                                            <div class="flex items-center gap-2">
                                                <span><?php echo date('M d, Y', strtotime($booking['borrow_date'])); ?></span>
                                                <i data-feather="arrow-right" class="w-4 h-4"></i>
                                                <span><?php echo date('M d, Y', strtotime($booking['expected_return_date'])); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if ($booking['actual_return_date']): ?>
                                                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">Returned</span>
                                            <?php else: 
                                                $days_remaining = floor((strtotime($booking['expected_return_date']) - time()) / 86400);
                                                if ($days_remaining < 0): ?>
                                                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">Overdue</span>
                                                <?php elseif ($days_remaining <= 3): ?>
                                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">Due Soon</span>
                                                <?php else: ?>
                                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">Active</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                            <i data-feather="calendar" class="w-12 h-12 mx-auto mb-3 text-gray-400"></i>
                                            <p class="text-lg">No bookings found.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <?php include "footer_admin.php"; ?>

    </main>

    <script>
        feather.replace();
        
        function showCalendarView() {
            document.getElementById('calendarView').classList.remove('hidden');
            document.getElementById('listView').classList.add('hidden');
            document.getElementById('calendarViewBtn').classList.add('bg-blue-600', 'text-white');
            document.getElementById('calendarViewBtn').classList.remove('text-gray-600');
            document.getElementById('listViewBtn').classList.remove('bg-blue-600', 'text-white');
            document.getElementById('listViewBtn').classList.add('text-gray-600');
            feather.replace();
        }
        
        function showListView() {
            document.getElementById('listView').classList.remove('hidden');
            document.getElementById('calendarView').classList.add('hidden');
            document.getElementById('listViewBtn').classList.add('bg-blue-600', 'text-white');
            document.getElementById('listViewBtn').classList.remove('text-gray-600');
            document.getElementById('calendarViewBtn').classList.remove('bg-blue-600', 'text-white');
            document.getElementById('calendarViewBtn').classList.add('text-gray-600');
            feather.replace();
        }
    </script>

</body>
</html>