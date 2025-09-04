<?php
session_start();
include "../landingpage/db.php"; // adjust path

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../landingpage/login.php");
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
<title>Inventory</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="flex bg-gray-100 min-h-screen">
<?php include "admin_sidebar.php"; ?>

<main class="flex-1 ml-64 p-10">
<div class="flex justify-between items-center mb-6">
  <h1 class="text-3xl font-bold text-gray-800">Inventory Management</h1>
  <button id="openAddModal" class="bg-blue-900 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Add Equipment</button>
</div>

<?php if($msg): ?>
<p class="text-green-600 mb-4"><?php echo $msg; ?></p>
<?php endif; ?>

<!-- Search + Filter -->
<form method="get" class="flex flex-col md:flex-row gap-4 mb-6">
  <input type="text" name="search" placeholder="Search equipment..." value="<?php echo htmlspecialchars($search); ?>" class="flex-1 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
  <select name="status" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
    <option value="">All</option>
    <option value="available" <?php if($status_filter=="available") echo "selected"; ?>>Available</option>
    <option value="maintenance" <?php if($status_filter=="maintenance") echo "selected"; ?>>Under Maintenance</option>
  </select>
  <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Filter</button>
</form>

<!-- Equipment Table -->
<div class="bg-white rounded-2xl shadow overflow-x-auto">
  <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Image</th>
        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Description</th>
        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Qty</th>
        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Condition</th>
        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Borrowed</th>
        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
      </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-100">
      <?php if($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr class="hover:bg-gray-50 transition">
          <td class="px-6 py-4"><?php echo $row['image'] ? "<img src='../photos/".htmlspecialchars($row['image'])."' class='h-12 w-12 object-cover rounded'>" : "-"; ?></td>
          <td class="px-6 py-4"><?php echo htmlspecialchars($row['name']); ?></td>
          <td class="px-6 py-4"><?php echo htmlspecialchars($row['description']); ?></td>
          <td class="px-6 py-4"><?php echo $row['quantity']; ?></td>
          <td class="px-6 py-4"><?php echo htmlspecialchars($row['condition']); ?></td>
          <td class="px-6 py-4"><?php echo $row['borrowed_count']; ?></td>
          <td class="px-6 py-4"><?php echo ucfirst($row['status']); ?></td>
          <td class="px-6 py-4 flex gap-2">
            <button data-modal-target="editModal<?php echo $row['id']; ?>" class="text-yellow-500 hover:text-yellow-700"><i data-feather="edit"></i></button>
            <a href="inventory.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this equipment?')" class="text-red-500 hover:text-red-700"><i data-feather="trash-2"></i></a>
          </td>
        </tr>

        <!-- Edit Modal -->
        <div id="editModal<?php echo $row['id']; ?>" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
          <div class="bg-white p-6 rounded-xl shadow-lg w-96 relative">
            <h2 class="text-xl font-bold mb-4">Edit Equipment</h2>
            <form method="post" enctype="multipart/form-data" class="space-y-4">
              <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
              <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
              <input type="text" name="description" value="<?php echo htmlspecialchars($row['description']); ?>" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
              <input type="number" name="quantity" value="<?php echo $row['quantity']; ?>" min="1" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
              <input type="text" name="condition" value="<?php echo htmlspecialchars($row['condition']); ?>" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
              <select name="status" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="available" <?php if($row['status']=='available') echo 'selected'; ?>>Available</option>
                <option value="maintenance" <?php if($row['status']=='maintenance') echo 'selected'; ?>>Under Maintenance</option>
              </select>
              <input type="file" name="image" class="w-full">
              <div class="flex justify-end gap-2">
                <button type="button" data-close="editModal<?php echo $row['id']; ?>" class="px-4 py-2 rounded bg-gray-400 hover:bg-gray-500 text-white">Cancel</button>
                <button type="submit" name="update_equipment" class="px-4 py-2 rounded bg-blue-900 hover:bg-blue-700 text-white">Update</button>
              </div>
            </form>
          </div>
        </div>

        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="8" class="px-6 py-4 text-center text-gray-500">No equipment found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</main>

<!-- Add Equipment Modal -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
  <div class="bg-white p-6 rounded-xl shadow-lg w-96 relative">
    <h2 class="text-xl font-bold mb-4">Add New Equipment</h2>
    <form method="post" enctype="multipart/form-data" class="space-y-4">
      <input type="text" name="name" placeholder="Equipment Name" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
      <input type="text" name="description" placeholder="Description" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
      <input type="number" name="quantity" placeholder="Quantity" min="1" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
      <input type="text" name="condition" placeholder="Condition" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
      <select name="status" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="available">Available</option>
        <option value="maintenance">Under Maintenance</option>
      </select>
      <input type="file" name="image" class="w-full">
      <div class="flex justify-end gap-2">
        <button type="button" id="closeAddModal" class="px-4 py-2 rounded bg-gray-400 hover:bg-gray-500 text-white">Cancel</button>
        <button type="submit" name="add_equipment" class="px-4 py-2 rounded bg-blue-900 hover:bg-blue-700 text-white">Add</button>
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
</script>
</body>
</html>
