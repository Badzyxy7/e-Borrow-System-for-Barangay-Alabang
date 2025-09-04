<?php
session_start();
include "../landingpage/db.php"; // adjust path

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../landingpage/login.php");
    exit();
}

// Get equipment ID
if (!isset($_GET['id'])) {
    header("Location: inventory.php");
    exit();
}

$id = intval($_GET['id']);
$msg = '';

// Fetch current equipment data
$sql = "SELECT * FROM equipment WHERE id=$id";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    header("Location: inventory.php");
    exit();
}
$equipment = $result->fetch_assoc();

// Handle form submission
if (isset($_POST['update_equipment'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $quantity = intval($_POST['quantity']);
    $status = $_POST['status'];
    $condition = $conn->real_escape_string($_POST['condition']);

    // Handle photo update
    $image_name = $equipment['image']; // keep old image by default
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
                condition='$condition', 
                image='$image_name'
            WHERE id=$id";

    if ($conn->query($sql)) {
        $msg = "Equipment updated successfully!";
        // Refresh the equipment data
        $equipment = $conn->query("SELECT * FROM equipment WHERE id=$id")->fetch_assoc();
    } else {
        $msg = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Equipment</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="bg-white p-8 rounded-xl shadow-lg w-96">
    <h2 class="text-2xl font-bold mb-6">Edit Equipment</h2>

    <?php if($msg): ?>
        <p class="text-green-600 mb-4"><?php echo $msg; ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="space-y-4">
        <input type="text" name="name" placeholder="Equipment Name" value="<?php echo htmlspecialchars($equipment['name']); ?>" required class="w-full px-3 py-2 border rounded-lg">
        <textarea name="description" placeholder="Description" class="w-full px-3 py-2 border rounded-lg"><?php echo htmlspecialchars($equipment['description']); ?></textarea>
        <input type="number" name="quantity" placeholder="Quantity" min="1" value="<?php echo $equipment['quantity']; ?>" required class="w-full px-3 py-2 border rounded-lg">
        <input type="text" name="condition" placeholder="Condition" value="<?php echo htmlspecialchars($equipment['condition']); ?>" class="w-full px-3 py-2 border rounded-lg">
        <select name="status" class="w-full px-3 py-2 border rounded-lg">
            <option value="available" <?php if($equipment['status']=='available') echo 'selected'; ?>>Available</option>
            <option value="maintenance" <?php if($equipment['status']=='maintenance') echo 'selected'; ?>>Under Maintenance</option>
        </select>
        <div>
            <label class="block mb-1">Current Image:</label>
            <?php if($equipment['image']): ?>
                <img src="../photos/<?php echo htmlspecialchars($equipment['image']); ?>" class="h-24 w-24 object-cover rounded mb-2">
            <?php else: ?>
                <p class="text-gray-500 mb-2">No image</p>
            <?php endif; ?>
            <input type="file" name="image">
        </div>
        <div class="flex justify-between">
            <a href="inventory.php" class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">Cancel</a>
            <button type="submit" name="update_equipment" class="px-4 py-2 bg-blue-900 text-white rounded hover:bg-blue-700">Update</button>
        </div>
    </form>
</div>

</body>
</html>
