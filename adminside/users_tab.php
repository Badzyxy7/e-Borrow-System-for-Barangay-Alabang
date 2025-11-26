<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once "users_functions.php";

// Check admin access
checkAdminAccess();

// Get user statistics
$stats = getUserStatistics($conn);

// Get search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? trim($_GET['role']) : '';

// Get all users
$users_result = searchUsers($conn, $search, $role_filter);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Borrow System | User Management</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
</head>

<body class="flex bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen text-sm">

    <!-- Sidebar -->
    <?php include "admin_sidebar.php"; ?>
    
    <!-- Header -->
    <?php 
        $page_title = "User Management"; 
        include "header_admin.php"; 
    ?>

    <!-- Main Content -->
    <main class="flex-1 ml-64 pt-16 flex flex-col min-h-screen">

        <!-- Page Title -->
        <div class="px-8 py-6 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-800">User Management</h2>
            <button onclick="openAddUserModal()" class="bg-gradient-to-r from-blue-900 to-blue-700 text-white px-6 py-3 rounded-2xl hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-lg transform hover:-translate-y-1 flex items-center gap-2">
                <i data-feather="user-plus" class="w-5 h-5"></i>
                <span class="font-medium">Add User</span>
            </button>
        </div>

        <!-- Page Content -->
        <div class="flex-1 px-8 py-6">

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Total Users</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['total']; ?></p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl shadow-lg">
                            <i data-feather="users" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Admins</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['admin']; ?></p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-red-500 to-red-600 text-white rounded-2xl shadow-lg">
                            <i data-feather="shield" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Staff</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['staff']; ?></p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl shadow-lg">
                            <i data-feather="user-check" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white/70 backdrop-blur-sm border border-white/20 p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Residents</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['resident']; ?></p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl shadow-lg">
                            <i data-feather="user" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="flex flex-col md:flex-row gap-4 mb-6">
                <input type="text" id="searchInput" placeholder="Search users by name, email, phone, or address..." 
                       value="<?php echo htmlspecialchars($search); ?>"
                       class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm">
                <select id="roleFilter" 
                        class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm">
                    <option value="">All Roles</option>
                    <option value="admin" <?php if($role_filter=="admin") echo "selected"; ?>>Admin</option>
                    <option value="staff" <?php if($role_filter=="staff") echo "selected"; ?>>Staff</option>
                    <option value="resident" <?php if($role_filter=="resident") echo "selected"; ?>>Resident</option>
                </select>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-700">User Info</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-700">Role</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-700">Status</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-700">Join Date</th>
                                <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody" class="divide-y divide-gray-100">
                            <?php if($users_result->num_rows > 0): ?>
                                <?php while ($user = $users_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <?php 
                                            $has_avatar = false;
                                            $avatar_src = '';
                                            
                                            if (!empty($user['avatar'])) {
                                                if (strpos($user['avatar'], 'photos/avatar/') !== false) {
                                                    $avatar_src = '../' . $user['avatar'];
                                                } else {
                                                    $avatar_src = '../photos/avatars/' . basename($user['avatar']);
                                                }
                                                
                                                if (file_exists($avatar_src)) {
                                                    $has_avatar = true;
                                                }
                                            }
                                            ?>
                                            
                                            <?php if ($has_avatar): ?>
                                                <img src="<?php echo htmlspecialchars($avatar_src); ?>" 
                                                     alt="<?php echo htmlspecialchars($user['name']); ?>"
                                                     class="w-10 h-10 rounded-full object-cover border-2 border-gray-200"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full items-center justify-center text-white font-semibold hidden">
                                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        if ($user['role'] == "admin") {
                                            echo '<span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">Admin</span>';
                                        } elseif ($user['role'] == "staff") {
                                            echo '<span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">Staff</span>';
                                        } elseif ($user['role'] == "resident") {
                                            echo '<span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">Resident</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($user['is_verified'] == 1): ?>
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium flex items-center gap-1 w-fit">
                                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                            Verified
                                        </span>
                                        <?php else: ?>
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium flex items-center gap-1 w-fit">
                                            <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                            Pending
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-center gap-2">
                                            <button onclick="openUserDetailsModal(<?php echo $user['id']; ?>)" 
                                                    class="p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition shadow-sm group relative"
                                                    title="View Details">
                                                <i data-feather="eye" class="w-4 h-4"></i>
                                                <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                                    View Details
                                                </span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <i data-feather="users" class="w-12 h-12 mx-auto mb-3 text-gray-400"></i>
                                        <p class="text-lg">No users found.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <?php include "footer_admin.php"; ?>

    </main>

    <!-- Include Modals -->
    <?php include "users_modals.php"; ?>

    <script src="users_scripts.js"></script>
    <script>
        feather.replace();
    </script>

</body>
</html>