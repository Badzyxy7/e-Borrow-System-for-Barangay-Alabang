<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../db.php"; // Adjust path

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle Export
if (isset($_GET['export'])) {
    $period = isset($_GET['period']) ? $_GET['period'] : '7';
    $start_date = date('Y-m-d H:i:s', strtotime("-$period days"));
    $export_date = date('F d, Y');
    $export_time = date('h:i A');
    
    // Get all data
    $total_requests = $conn->query("SELECT COUNT(*) as total FROM borrow_requests WHERE created_at >= '$start_date'")->fetch_assoc()['total'];
    $approved_count = $conn->query("SELECT COUNT(*) as total FROM borrow_requests WHERE status='approved' AND created_at >= '$start_date'")->fetch_assoc()['total'];
    $approval_rate = $total_requests > 0 ? round(($approved_count / $total_requests) * 100, 2) : 0;
    $total_equipment = $conn->query("SELECT COUNT(*) as total FROM equipment")->fetch_assoc()['total'];
    
    $most_borrowed = $conn->query("
        SELECT e.name, COUNT(*) as count
        FROM borrow_logs bl
        JOIN equipment e ON bl.equipment_id = e.id
        WHERE bl.log_created_at >= '$start_date'
        GROUP BY e.id
        ORDER BY count DESC
        LIMIT 10
    ")->fetch_all(MYSQLI_ASSOC);
    
    $status_counts = $conn->query("
        SELECT status, COUNT(*) as count
        FROM borrow_requests
        WHERE created_at >= '$start_date'
        GROUP BY status
    ")->fetch_all(MYSQLI_ASSOC);
    
    $detailed_logs = $conn->query("
        SELECT 
            u.name as borrower,
            e.name as equipment,
            bl.qty,
            bl.borrow_date,
            bl.expected_return_date,
            bl.actual_return_date,
            CASE 
                WHEN bl.actual_return_date IS NULL THEN 'Active'
                ELSE 'Returned'
            END as status
        FROM borrow_logs bl
        JOIN users u ON bl.user_id = u.id
        JOIN equipment e ON bl.equipment_id = e.id
        WHERE bl.log_created_at >= '$start_date'
        ORDER BY bl.borrow_date DESC
    ")->fetch_all(MYSQLI_ASSOC);
    
    // Set headers for download
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="Borrow_System_Report_' . date('Y-m-d') . '.html"');
    
    // Generate HTML Report
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>E-Borrow System Report</title>
        <style>
            body { 
                font-family: 'Arial', sans-serif; 
                margin: 40px; 
                color: #333;
                line-height: 1.6;
            }
            .header { 
                text-align: center; 
                margin-bottom: 40px; 
                border-bottom: 3px solid #2563eb;
                padding-bottom: 20px;
            }
            .header h1 { 
                color: #1e40af; 
                margin: 0;
                font-size: 32px;
            }
            .header p { 
                color: #6b7280; 
                margin: 5px 0;
                font-size: 14px;
            }
            .section { 
                margin-bottom: 30px; 
                page-break-inside: avoid;
            }
            .section-title { 
                font-size: 20px; 
                font-weight: bold; 
                color: #1e40af; 
                margin-bottom: 15px; 
                padding-bottom: 10px;
                border-bottom: 2px solid #ddd;
            }
            .stats-grid { 
                display: grid; 
                grid-template-columns: repeat(4, 1fr); 
                gap: 20px; 
                margin-bottom: 30px;
            }
            .stat-card { 
                background: #f8fafc; 
                padding: 20px; 
                border-radius: 8px; 
                border-left: 4px solid #2563eb;
            }
            .stat-card .label { 
                font-size: 12px; 
                color: #6b7280; 
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .stat-card .value { 
                font-size: 28px; 
                font-weight: bold; 
                color: #1e40af; 
                margin-top: 5px;
            }
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-top: 15px;
                background: white;
            }
            th { 
                background: #2563eb; 
                color: white; 
                padding: 12px; 
                text-align: left;
                font-size: 13px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            td { 
                padding: 10px 12px; 
                border-bottom: 1px solid #e5e7eb;
                font-size: 14px;
            }
            tr:hover { 
                background: #f9fafb; 
            }
            .bar-container {
                background: #e5e7eb;
                height: 24px;
                border-radius: 4px;
                overflow: hidden;
                position: relative;
            }
            .bar {
                height: 100%;
                background: #2563eb;
                display: flex;
                align-items: center;
                padding-left: 8px;
                color: white;
                font-size: 12px;
                font-weight: bold;
            }
            .footer {
                margin-top: 50px;
                padding-top: 20px;
                border-top: 2px solid #ddd;
                text-align: center;
                color: #6b7280;
                font-size: 12px;
            }
            .status-badge {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: 600;
            }
            .status-active { background: #dbeafe; color: #1e40af; }
            .status-returned { background: #d1fae5; color: #065f46; }
            @media print {
                body { margin: 20px; }
                .section { page-break-inside: avoid; }
            }
        </style>
    </head>
    <body>
        <!-- Header -->
        <div class="header">
            <h1>E-BORROW SYSTEM</h1>
            <h2 style="color: #6b7280; margin: 10px 0;">Analytics & Reports</h2>
            <p><strong>Report Period:</strong> Last <?php echo $period; ?> Days</p>
            <p><strong>Generated:</strong> <?php echo $export_date; ?> at <?php echo $export_time; ?></p>
            <p><strong>Generated By:</strong> <?php echo htmlspecialchars($_SESSION['name']); ?></p>
        </div>

        <!-- Summary Statistics -->
        <div class="section">
            <div class="section-title">Executive Summary</div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="label">Total Requests</div>
                    <div class="value"><?php echo $total_requests; ?></div>
                </div>
                <div class="stat-card">
                    <div class="label">Approval Rate</div>
                    <div class="value"><?php echo $approval_rate; ?>%</div>
                </div>
                <div class="stat-card">
                    <div class="label">Equipment Items</div>
                    <div class="value"><?php echo $total_equipment; ?></div>
                </div>
                <div class="stat-card">
                    <div class="label">Active Borrows</div>
                    <div class="value"><?php echo $conn->query("SELECT COUNT(*) as c FROM borrow_logs WHERE actual_return_date IS NULL")->fetch_assoc()['c']; ?></div>
                </div>
            </div>
        </div>

        <!-- Most Borrowed Equipment -->
        <div class="section">
            <div class="section-title">Top 10 Most Borrowed Equipment</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">Rank</th>
                        <th>Equipment Name</th>
                        <th style="width: 100px;">Times Borrowed</th>
                        <th style="width: 300px;">Usage Chart</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    $max_count = $most_borrowed[0]['count'] ?? 1;
                    foreach($most_borrowed as $item): 
                        $percentage = round(($item['count'] / $max_count) * 100);
                    ?>
                        <tr>
                            <td style="text-align: center; font-weight: bold;"><?php echo $rank++; ?></td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td style="text-align: center; font-weight: bold;"><?php echo $item['count']; ?></td>
                            <td>
                                <div class="bar-container">
                                    <div class="bar" style="width: <?php echo $percentage; ?>%">
                                        <?php echo $percentage; ?>%
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Status Distribution -->
        <div class="section">
            <div class="section-title">Request Status Distribution</div>
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th style="width: 100px;">Count</th>
                        <th style="width: 100px;">Percentage</th>
                        <th style="width: 300px;">Distribution</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_status = array_sum(array_column($status_counts, 'count'));
                    foreach($status_counts as $s): 
                        $percent = $total_status > 0 ? round(($s['count'] / $total_status) * 100, 2) : 0;
                    ?>
                        <tr>
                            <td style="text-transform: capitalize; font-weight: 600;"><?php echo str_replace('_', ' ', $s['status']); ?></td>
                            <td style="text-align: center; font-weight: bold;"><?php echo $s['count']; ?></td>
                            <td style="text-align: center;"><?php echo $percent; ?>%</td>
                            <td>
                                <div class="bar-container">
                                    <div class="bar" style="width: <?php echo $percent; ?>%">
                                        <?php echo $percent; ?>%
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Detailed Borrow Logs -->
        <div class="section">
            <div class="section-title">Detailed Borrow Records</div>
            <table>
                <thead>
                    <tr>
                        <th>Borrower</th>
                        <th>Equipment</th>
                        <th style="width: 80px;">Qty</th>
                        <th>Borrow Date</th>
                        <th>Expected Return</th>
                        <th>Actual Return</th>
                        <th style="width: 100px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($detailed_logs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['borrower']); ?></td>
                            <td><?php echo htmlspecialchars($log['equipment']); ?></td>
                            <td style="text-align: center;"><?php echo $log['qty']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($log['borrow_date'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($log['expected_return_date'])); ?></td>
                            <td><?php echo $log['actual_return_date'] ? date('M d, Y', strtotime($log['actual_return_date'])) : '-'; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($log['status']); ?>">
                                    <?php echo $log['status']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>E-Borrow System - Barangay Alabang</strong></p>
            <p>This is an automatically generated report. For inquiries, please contact the administrator.</p>
            <p>&copy; <?php echo date('Y'); ?> Barangay Alabang. All rights reserved.</p>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Date filter
$period = isset($_GET['period']) ? $_GET['period'] : '7';
$start_date = date('Y-m-d H:i:s', strtotime("-$period days"));

// Total requests
$total_requests = $conn->query("SELECT COUNT(*) as total FROM borrow_requests WHERE created_at >= '$start_date'")->fetch_assoc()['total'];

// Approval rate
$approved_count = $conn->query("SELECT COUNT(*) as total FROM borrow_requests WHERE status='approved' AND created_at >= '$start_date'")->fetch_assoc()['total'];
$approval_rate = $total_requests > 0 ? round(($approved_count / $total_requests) * 100, 2) : 0;

// Total equipment items
$total_equipment = $conn->query("SELECT COUNT(*) as total FROM equipment")->fetch_assoc()['total'];

// Average processing time (from request creation to pickup)
$avg_processing = $conn->query("
    SELECT AVG(TIMESTAMPDIFF(HOUR, br.created_at, bl.actual_pickup_date)) as avg_hours
    FROM borrow_requests br
    JOIN borrow_logs bl ON br.id=bl.request_id
    WHERE br.created_at >= '$start_date' AND bl.actual_pickup_date IS NOT NULL
")->fetch_assoc()['avg_hours'];
$avg_processing = $avg_processing ? round($avg_processing,2) : 0;

// Most borrowed equipment
$most_borrowed = $conn->query("
    SELECT e.name, COUNT(*) as count
    FROM borrow_logs bl
    JOIN equipment e ON bl.equipment_id = e.id
    WHERE bl.log_created_at >= '$start_date'
    GROUP BY e.id
    ORDER BY count DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Request status distribution
$status_counts = $conn->query("
    SELECT status, COUNT(*) as count
    FROM borrow_requests
    WHERE created_at >= '$start_date'
    GROUP BY status
")->fetch_all(MYSQLI_ASSOC);

$total_status = array_sum(array_column($status_counts,'count'));

// Recent activity (last 10)
$recent_activity = $conn->query("
    SELECT u.name as user_name, e.name as equipment_name, br.status, br.created_at
    FROM borrow_requests br
    JOIN users u ON br.user_id = u.id
    JOIN equipment e ON br.equipment_id = e.id
    WHERE br.created_at >= '$start_date'
    ORDER BY br.created_at DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Borrow System | Reports & Analytics</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
</head>

<body class="flex bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen text-sm">

    <!-- Sidebar -->
    <?php include "admin_sidebar.php"; ?>
    
    <!-- Header -->
    <?php 
        $page_title = "Reports & Analytics"; 
        include "header_admin.php"; 
    ?>

    <!-- Main Content -->
    <main class="flex-1 ml-64 pt-16 flex flex-col min-h-screen">

        <!-- Page Title -->
        <div class="px-8 py-6 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-800">Reports & Analytics</h2>
            <div class="flex gap-3">
                <form method="get" class="flex gap-2">
                    <select name="period" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm">
                        <option value="7" <?php if($period=="7") echo "selected"; ?>>Last 7 Days</option>
                        <option value="30" <?php if($period=="30") echo "selected"; ?>>Last 30 Days</option>
                        <option value="90" <?php if($period=="90") echo "selected"; ?>>Last 90 Days</option>
                        <option value="365" <?php if($period=="365") echo "selected"; ?>>Last Year</option>
                    </select>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition shadow-lg flex items-center gap-2">
                        <i data-feather="filter" class="w-5 h-5"></i>
                        Apply
                    </button>
                </form>
                <a href="?period=<?php echo $period; ?>&export=1" class="bg-green-600 text-white px-6 py-3 rounded-xl hover:bg-green-700 transition shadow-lg flex items-center gap-2">
                    <i data-feather="download" class="w-5 h-5"></i>
                    Export Report
                </a>
            </div>
        </div>

        <!-- Page Content -->
        <div class="flex-1 px-8 py-6">

            <!-- Top Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Total Requests</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $total_requests; ?></p>
                            <p class="text-sm text-blue-600 font-medium mt-1">Last <?php echo $period; ?> days</p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl shadow-lg">
                            <i data-feather="file-text" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Approval Rate</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $approval_rate; ?>%</p>
                            <p class="text-sm text-green-600 font-medium mt-1">Performance metric</p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl shadow-lg">
                            <i data-feather="check-circle" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Equipment Items</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $total_equipment; ?></p>
                            <p class="text-sm text-purple-600 font-medium mt-1">Total inventory</p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl shadow-lg">
                            <i data-feather="package" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Avg. Processing</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $avg_processing; ?>h</p>
                            <p class="text-sm text-orange-600 font-medium mt-1">Request to pickup</p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-2xl shadow-lg">
                            <i data-feather="clock" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Most Borrowed Equipment -->
                <div class="bg-white rounded-3xl shadow-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i data-feather="trending-up" class="w-5 h-5 text-blue-600"></i>
                        Most Borrowed Equipment
                    </h3>
                    <div class="space-y-4">
                        <?php 
                        $max_count = $most_borrowed[0]['count'] ?? 1;
                        foreach($most_borrowed as $item):
                            $percentage = round(($item['count'] / $max_count) * 100);
                        ?>
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="font-medium text-gray-700"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="text-gray-600 font-semibold"><?php echo $item['count']; ?> times</span>
                                </div>
                                <div class="w-full bg-gray-200 h-3 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500" 
                                         style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Status Distribution -->
                <div class="bg-white rounded-3xl shadow-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i data-feather="pie-chart" class="w-5 h-5 text-green-600"></i>
                        Request Status Distribution
                    </h3>
                    <div class="space-y-4">
                        <?php
                        $status_colors = [
                            'pending' => ['bg' => 'from-yellow-500 to-yellow-600', 'text' => 'text-yellow-700'],
                            'approved' => ['bg' => 'from-green-500 to-green-600', 'text' => 'text-green-700'],
                            'picked_up' => ['bg' => 'from-blue-500 to-blue-600', 'text' => 'text-blue-700'],
                            'returned' => ['bg' => 'from-gray-500 to-gray-600', 'text' => 'text-gray-700'],
                            'declined' => ['bg' => 'from-red-500 to-red-600', 'text' => 'text-red-700']
                        ];
                        foreach($status_counts as $s):
                            $percent = $total_status > 0 ? round(($s['count'] / $total_status) * 100, 2) : 0;
                            $color = $status_colors[$s['status']] ?? ['bg' => 'from-gray-500 to-gray-600', 'text' => 'text-gray-700'];
                        ?>
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="font-medium text-gray-700 capitalize"><?php echo str_replace('_', ' ', $s['status']); ?></span>
                                    <span class="<?php echo $color['text']; ?> font-semibold"><?php echo $s['count']; ?> (<?php echo $percent; ?>%)</span>
                                </div>
                                <div class="w-full bg-gray-200 h-3 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r <?php echo $color['bg']; ?> h-3 rounded-full transition-all duration-500" 
                                         style="width: <?php echo $percent; ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i data-feather="activity" class="w-5 h-5 text-purple-600"></i>
                        Recent Activity
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Resident</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Equipment</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Date & Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach($recent_activity as $ra): 
                                $status_badge = [
                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                    'approved' => 'bg-green-100 text-green-700',
                                    'picked_up' => 'bg-blue-100 text-blue-700',
                                    'returned' => 'bg-gray-100 text-gray-700',
                                    'declined' => 'bg-red-100 text-red-700'
                                ];
                                $badge_class = $status_badge[$ra['status']] ?? 'bg-gray-100 text-gray-700';
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-medium text-gray-800">
                                    <?php echo htmlspecialchars($ra['user_name']); ?>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    <?php echo htmlspecialchars($ra['equipment_name']); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo $badge_class; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $ra['status'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    <?php echo date('M d, Y - h:i A', strtotime($ra['created_at'])); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <?php include "footer_admin.php"; ?>

    </main>

    <script>
        feather.replace();
    </script>

</body>
</html>