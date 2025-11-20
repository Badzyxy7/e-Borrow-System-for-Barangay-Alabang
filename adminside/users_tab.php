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

// Handle user actions
$duplicate_email = false; // flag for modal
$user_added_successfully = false; // flag for success

if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_user':
                $name = trim($_POST['name']);
                $email = trim(strtolower($_POST['email'])); // Normalize email
                $password = $_POST['password'];
                $role = $_POST['role'];

                // Check if email already exists (case-insensitive)
                $stmt_check = $conn->prepare("SELECT id FROM users WHERE LOWER(email) = ?");
                $stmt_check->bind_param("s", $email);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();

                if ($result_check->num_rows > 0) {
                    $duplicate_email = true; // trigger modal
                    $stmt_check->close();
                } else {
                    $stmt_check->close();
                    
                    // Hash the password
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user
                    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $name, $email, $password_hash, $role);
                    
                    if ($stmt->execute()) {
                        $user_added_successfully = true;
                        $stmt->close();
                        // Redirect to prevent form resubmission
                        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
                        exit();
                    } else {
                        echo "<script>alert('Error adding user: " . $conn->error . "');</script>";
                        $stmt->close();
                    }
                }
                break;

            case 'delete_user':
                $user_id = (int)$_POST['user_id'];
                
                // Don't allow deleting your own account
                if ($user_id == $_SESSION['user_id']) {
                    echo "<script>alert('You cannot delete your own account!');</script>";
                    break;
                }
                
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);

                if ($stmt->execute()) {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?deleted=1");
                    exit();
                } else {
                    echo "<script>alert('Error deleting user.');</script>";
                }
                $stmt->close();
                break;

            case 'toggle_status':
                $user_id = (int)$_POST['user_id'];
                echo "<script>alert('Status toggle functionality needs status column in database');</script>";
                break;
        }
    }
}

// Check for success message
$success_message = '';
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = 'User added successfully!';
}
if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $success_message = 'User deleted successfully!';
}

// Get user statistics
$total_users_result = $conn->query("SELECT COUNT(*) as total FROM users");
$total_users = $total_users_result->fetch_assoc()['total'];

$admin_count_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
$admin_count = $admin_count_result->fetch_assoc()['count'];

$staff_count_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'staff'");
$staff_count = $staff_count_result->fetch_assoc()['count'];

$resident_count_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'resident'");
$resident_count = $resident_count_result->fetch_assoc()['count'];

// Get all users with search functionality - Include avatar field
$search = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';
$role_filter = isset($_GET['role']) ? $conn->real_escape_string($_GET['role']) : '';

$sql = "SELECT id, name, email, role, created_at, avatar FROM users WHERE 1=1";
if ($search) $sql .= " AND (name LIKE '%$search%' OR email LIKE '%$search%')";
if ($role_filter) $sql .= " AND role='$role_filter'";
$sql .= " ORDER BY created_at DESC";
$users_result = $conn->query($sql);
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
            <button onclick="openAddUserModal()" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-2xl hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-lg transform hover:-translate-y-1 flex items-center gap-2">
                <i data-feather="user-plus" class="w-5 h-5"></i>
                <span class="font-medium">Add User</span>
            </button>
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
                            <p class="text-sm font-medium text-gray-600 mb-1">Total Users</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $total_users; ?></p>
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
                            <p class="text-3xl font-bold text-gray-900"><?php echo $admin_count; ?></p>
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
                            <p class="text-3xl font-bold text-gray-900"><?php echo $staff_count; ?></p>
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
                            <p class="text-3xl font-bold text-gray-900"><?php echo $resident_count; ?></p>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-2xl shadow-lg">
                            <i data-feather="user" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <form method="get" class="flex flex-col md:flex-row gap-4 mb-6">
                <input type="text" name="search" placeholder="Search users by name or email..." 
                       value="<?php echo htmlspecialchars($search); ?>"
                       class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm">
                <select name="role" 
                        class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm">
                    <option value="">All Roles</option>
                    <option value="admin" <?php if($role_filter=="admin") echo "selected"; ?>>Admin</option>
                    <option value="staff" <?php if($role_filter=="staff") echo "selected"; ?>>Staff</option>
                    <option value="resident" <?php if($role_filter=="resident") echo "selected"; ?>>Resident</option>
                </select>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition-all duration-300 shadow-lg flex items-center gap-2">
                    <i data-feather="search" class="w-5 h-5"></i>
                    <span class="font-medium">Filter</span>
                </button>
            </form>

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
                        <tbody class="divide-y divide-gray-100">
                            <?php if($users_result->num_rows > 0): ?>
                                <?php while ($user = $users_result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <?php 
                                            // Check if avatar exists
                                            $has_avatar = false;
                                            $avatar_src = '';
                                            
                                            if (!empty($user['avatar'])) {
                                                // Assuming avatar path is stored as just filename or relative path
                                                // Build the correct path to photos/avatar/
                                                if (strpos($user['avatar'], 'photos/avatar/') !== false) {
                                                    // Already has full path
                                                    $avatar_src = '../' . $user['avatar'];
                                                } else {
                                                    // Just filename, prepend the path
                                                    $avatar_src = '../photos/avatars/' . basename($user['avatar']);
                                                }
                                                
                                                // Check if file exists
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
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium flex items-center gap-1 w-fit">
                                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-center gap-2">
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <button onclick="deactivateUser(<?php echo $user['id']; ?>)" 
                                                    class="p-2 bg-yellow-100 text-yellow-600 rounded-lg hover:bg-yellow-200 transition shadow-sm group relative"
                                                    title="Deactivate User">
                                                <i data-feather="user-x" class="w-4 h-4"></i>
                                                <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                                    Deactivate
                                                </span>
                                            </button>
                                            <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo addslashes($user['name']); ?>')" 
                                                    class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition shadow-sm group relative"
                                                    title="Delete User">
                                                <i data-feather="trash-2" class="w-4 h-4"></i>
                                                <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                                    Delete
                                                </span>
                                            </button>
                                            <?php else: ?>
                                            <span class="text-gray-400 text-xs italic px-3 py-1">Current User</span>
                                            <?php endif; ?>
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

    <!-- Add User Modal -->
    <div id="addUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i data-feather="user-plus" class="w-6 h-6 text-blue-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Add New User</h2>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_user">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                    <input type="text" name="name" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                    <input type="email" name="email" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                    <input type="password" name="password" required minlength="6" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                    <select name="role" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                        <option value="resident">Resident</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeAddUserModal()" 
                            class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-medium transition shadow-lg">
                        Add User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Duplicate Email Modal -->
    <div id="duplicateEmailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-red-100 rounded-full">
                    <i data-feather="alert-circle" class="w-6 h-6 text-red-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-red-600">Duplicate Email</h2>
            </div>
            <p class="mb-6 text-gray-600">The email address you entered already exists in the system. Please use a different email address.</p>
            <div class="flex justify-end">
                <button onclick="closeDuplicateEmailModal()" 
                        class="px-6 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-medium transition shadow-lg">
                    OK
                </button>
            </div>
        </div>
    </div>

    <script>
        feather.replace();

        function openAddUserModal() {
            document.getElementById('addUserModal').classList.remove('hidden');
            document.getElementById('addUserModal').classList.add('flex');
        }

        function closeAddUserModal() {
            document.getElementById('addUserModal').classList.add('hidden');
            document.getElementById('addUserModal').classList.remove('flex');
        }

        function deleteUser(userId, userName) {
            if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="action" value="delete_user"><input type="hidden" name="user_id" value="${userId}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deactivateUser(userId) {
            if (confirm('Are you sure you want to deactivate this user?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="action" value="toggle_status"><input type="hidden" name="user_id" value="${userId}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function closeDuplicateEmailModal() {
            document.getElementById('duplicateEmailModal').classList.add('hidden');
            document.getElementById('duplicateEmailModal').classList.remove('flex');
            // Re-open the add user modal so they can try again
            openAddUserModal();
        }

        // Close modals when clicking outside
        document.getElementById('addUserModal').addEventListener('click', function(e) {
            if (e.target === this) closeAddUserModal();
        });

        document.getElementById('duplicateEmailModal').addEventListener('click', function(e) {
            if (e.target === this) closeDuplicateEmailModal();
        });

        // Show duplicate email modal if needed
        <?php if ($duplicate_email): ?>
            document.getElementById('duplicateEmailModal').classList.remove('hidden');
            document.getElementById('duplicateEmailModal').classList.add('flex');
        <?php endif; ?>
    </script>

</body>
</html>