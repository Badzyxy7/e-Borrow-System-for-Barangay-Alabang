<?php
session_start();
include "../db.php"; // adjust path

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle Add Equipment
$msg = '';
if (isset($_POST['add_equipment'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 1);
    $status = $_POST['status'] ?? 'available';
    $condition = $conn->real_escape_string($_POST['condition'] ?? '');

    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid() . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "../photos/" . $image_name);
    }

    $sql = "INSERT INTO equipment (name, description, quantity, status, `condition`, image, created_at)
            VALUES ('$name', '$description', $quantity, '$status', '$condition', '$image_name', NOW())";

    if ($conn->query($sql)) {
        $msg = "Equipment added successfully!";
    } else {
        $msg = "Error: " . $conn->error;
    }
}

// Handle Edit Equipment
if (isset($_POST['update_equipment'])) {
    $id = intval($_POST['id']);
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 1);
    $status = $_POST['status'] ?? 'available';
    $condition = $conn->real_escape_string($_POST['condition'] ?? '');

    // Get existing image
    $row = $conn->query("SELECT image FROM equipment WHERE id=$id")->fetch_assoc();
    $image_name = $row['image'];

    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid() . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "../photos/" . $image_name);
    }

    $sql = "UPDATE equipment SET 
                name='$name', 
                description='$description', 
                quantity=$quantity, 
                status='$status', 
                `condition`='$condition', 
                image='$image_name'
            WHERE id=$id";

    if ($conn->query($sql)) {
        $msg = "Equipment updated successfully!";
    } else {
        $msg = "Error: " . $conn->error;
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM equipment WHERE id=$id");
    header("Location: inventory.php");
    exit();
}

// Fetch equipment with borrowed count
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

$sql = "SELECT e.*, 
               (SELECT COUNT(*) FROM borrow_logs bl WHERE bl.equipment_id = e.id) AS borrowed_count
        FROM equipment e
        WHERE 1=1";

if ($search) $sql .= " AND name LIKE '%$search%'";
if ($status_filter) $sql .= " AND status='$status_filter'";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>E-Borrow System | Inventory Management</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
</head>

<body class="flex bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen text-sm">

  <!-- Sidebar -->
  <?php include "admin_sidebar.php"; ?>
  
  <!-- Header -->
  <?php 
   
    include "header_admin.php"; 
  ?>

  <!-- Main Content -->
  <main class="flex-1 ml-64 pt-16 flex flex-col min-h-screen">

    <!-- Page Title -->
    <div class="px-8 py-6 border-b border-gray-200 flex justify-between items-center">
      <h2 class="text-2xl font-semibold text-gray-800">Inventory Management</h2>
      <button id="openAddModal" class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-2xl hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-lg transform hover:-translate-y-1 flex items-center gap-2">
        <i data-feather="plus" class="w-5 h-5"></i>
        <span class="font-medium">Add Equipment</span>
      </button>
    </div>

    <!-- Page Content -->
    <div class="flex-1 px-8 py-6">

      <?php if($msg): ?>
      <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-2xl mb-6 shadow">
        <?php echo $msg; ?>
      </div>
      <?php endif; ?>

      <!-- Search + Filter -->
      <form method="get" class="flex flex-col md:flex-row gap-4 mb-6">
        <input type="text" name="search" placeholder="Search equipment..." 
               value="<?php echo htmlspecialchars($search); ?>" 
               class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm">
        <select name="status" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm">
          <option value="">All Status</option>
          <option value="available" <?php if($status_filter=="available") echo "selected"; ?>>Available</option>
          <option value="maintenance" <?php if($status_filter=="maintenance") echo "selected"; ?>>Under Maintenance</option>
        </select>
        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition-all duration-300 shadow-lg flex items-center gap-2">
          <i data-feather="search" class="w-5 h-5"></i>
          <span class="font-medium">Filter</span>
        </button>
      </form>

      <!-- Equipment Table -->
      <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Image</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Name</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Description</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Qty</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Condition</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Borrowed</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4">
                    <?php if($row['image']): ?>
                      <img src='../photos/<?php echo htmlspecialchars($row['image']); ?>' class='h-14 w-14 object-cover rounded-xl shadow'>
                    <?php else: ?>
                      <div class="h-14 w-14 bg-gray-200 rounded-xl flex items-center justify-center">
                        <i data-feather="image" class="w-6 h-6 text-gray-400"></i>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4 font-medium text-gray-800"><?php echo htmlspecialchars($row['name']); ?></td>
                  <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($row['description']); ?></td>
                  <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full font-medium">
                      <?php echo $row['quantity']; ?>
                    </span>
                  </td>
                  <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($row['condition']); ?></td>
                  <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full font-medium">
                      <?php echo $row['borrowed_count']; ?>
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <span class="px-3 py-1 rounded-full font-medium <?php echo $row['status']=='available' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                      <?php echo ucfirst($row['status']); ?>
                    </span>
                  </td>
                  <td class="px-6 py-4 flex gap-3">
                    <button data-modal-target="editModal<?php echo $row['id']; ?>" 
                            class="text-blue-600 hover:text-blue-800 transition" title="Edit">
                      <i data-feather="edit" class="w-5 h-5"></i>
                    </button>
                    <a href="inventory.php?delete=<?php echo $row['id']; ?>" 
                       onclick="return confirm('Delete this equipment?')" 
                       class="text-red-600 hover:text-red-800 transition" title="Delete">
                      <i data-feather="trash-2" class="w-5 h-5"></i>
                    </a>
                  </td>
                </tr>

                <!-- Edit Modal -->
                <div id="editModal<?php echo $row['id']; ?>" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                  <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md relative">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800">Edit Equipment</h2>
                    <form method="post" enctype="multipart/form-data" class="space-y-4">
                      <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                      
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Equipment Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" 
                               required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="3" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"><?php echo htmlspecialchars($row['description']); ?></textarea>
                      </div>

                      <div class="grid grid-cols-2 gap-4">
                        <div>
                          <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                          <input type="number" name="quantity" value="<?php echo $row['quantity']; ?>" 
                                 min="1" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>

                        <div>
                          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                          <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="available" <?php if($row['status']=='available') echo 'selected'; ?>>Available</option>
                            <option value="maintenance" <?php if($row['status']=='maintenance') echo 'selected'; ?>>Maintenance</option>
                          </select>
                        </div>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Condition</label>
                        <input type="text" name="condition" value="<?php echo htmlspecialchars($row['condition']); ?>" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Image</label>
                        <input type="file" name="image" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                      </div>

                      <div class="flex justify-end gap-3 pt-4">
                        <button type="button" data-close="editModal<?php echo $row['id']; ?>" 
                                class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium transition">
                          Cancel
                        </button>
                        <button type="submit" name="update_equipment" 
                                class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium transition shadow-lg">
                          Update
                        </button>
                      </div>
                    </form>
                  </div>
                </div>

                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                    <i data-feather="inbox" class="w-12 h-12 mx-auto mb-3 text-gray-400"></i>
                    <p class="text-lg">No equipment found.</p>
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

  <!-- Add Equipment Modal -->
  <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md relative">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Add New Equipment</h2>
      <form method="post" enctype="multipart/form-data" class="space-y-4">
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Equipment Name *</label>
          <input type="text" name="name" placeholder="Enter equipment name" 
                 required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
          <textarea name="description" placeholder="Enter description" rows="3" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
            <input type="number" name="quantity" placeholder="0" min="1" 
                   required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
              <option value="available">Available</option>
              <option value="maintenance">Maintenance</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Condition</label>
          <input type="text" name="condition" placeholder="e.g., Good, Fair, Excellent" 
                 class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Image</label>
          <input type="file" name="image" 
                 class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
        </div>

        <div class="flex justify-end gap-3 pt-4">
          <button type="button" id="closeAddModal" 
                  class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium transition">
            Cancel
          </button>
          <button type="submit" name="add_equipment" 
                  class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium transition shadow-lg">
            Add Equipment
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    feather.replace();

    // Add modal
    const addModal = document.getElementById('addModal');
    document.getElementById('openAddModal').addEventListener('click', () => addModal.classList.remove('hidden'));
    document.getElementById('closeAddModal').addEventListener('click', () => addModal.classList.add('hidden'));

    // Edit modals
    document.querySelectorAll('[data-modal-target]').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = document.getElementById(btn.getAttribute('data-modal-target'));
        target.classList.remove('hidden');
      });
    });
    document.querySelectorAll('[data-close]').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = document.getElementById(btn.getAttribute('data-close'));
        target.classList.add('hidden');
      });
    });

    // Close modals when clicking outside
    [addModal, ...document.querySelectorAll('[id^="editModal"]')].forEach(modal => {
      modal?.addEventListener('click', (e) => {
        if (e.target === modal) {
          modal.classList.add('hidden');
        }
      });
    });
  </script>

</body>
</html>