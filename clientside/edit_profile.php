<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$page_title = "Edit Profile";

// Get user data from session
$name = $_SESSION['name'] ?? '';
$email = $_SESSION['email'] ?? '';
$barangay = $_SESSION['barangay'] ?? 'Alabang';
$street = $_SESSION['street'] ?? '';
$landmark = $_SESSION['landmark'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Barangay System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="ml-64 p-8">
        <div class="max-w-3xl mx-auto">
            <!-- Back Button -->
            <div class="mb-6">
                <button onclick="window.history.back()" class="flex items-center text-gray-600 hover:text-gray-800 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back
                </button>
            </div>

            <!-- Edit Profile Card -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Edit Profile</h2>
                </div>

                <!-- Profile Update Form -->
                <form id="editProfileForm" class="space-y-6">
                    <!-- Personal Information Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-4 pb-2 border-b">Personal Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 font-medium mb-2">Full Name *</label>
                                <input type="text" 
                                       name="name" 
                                       value="<?php echo htmlspecialchars($name); ?>" 
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-gray-700 font-medium mb-2">Email Address *</label>
                                <input type="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($email); ?>" 
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Address Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-4 pb-2 border-b">Address</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 font-medium mb-2">Barangay *</label>
                                <input type="text" 
                                       name="barangay" 
                                       value="<?php echo htmlspecialchars($barangay); ?>" 
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-gray-700 font-medium mb-2">Street</label>
                                <input type="text" 
                                       name="street" 
                                       value="<?php echo htmlspecialchars($street); ?>" 
                                       placeholder="e.g., 123 Main Street"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-gray-700 font-medium mb-2">Landmark</label>
                                <input type="text" 
                                       name="landmark" 
                                       value="<?php echo htmlspecialchars($landmark); ?>" 
                                       placeholder="e.g., Near City Hall"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Password Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-4 pb-2 border-b">Change Password (Optional)</h3>
                        <p class="text-sm text-gray-600 mb-4">Leave blank if you don't want to change your password</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 font-medium mb-2">New Password</label>
                                <input type="password" 
                                       name="password" 
                                       placeholder="Enter new password"
                                       minlength="6"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-gray-700 font-medium mb-2">Confirm New Password</label>
                                <input type="password" 
                                       name="confirm_password" 
                                       placeholder="Confirm new password"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Message Display -->
                    <div id="updateMessage" class="hidden rounded-lg p-4 text-center"></div>

                    <!-- Action Buttons -->
                    <div class="flex gap-4 pt-4">
                        <button type="submit" 
                                class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition font-medium">
                            Save Changes
                        </button>
                        <button type="button" 
                                onclick="window.history.back()"
                                class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-lg hover:bg-gray-300 transition font-medium">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const editProfileForm = document.getElementById('editProfileForm');
        const updateMessage = document.getElementById('updateMessage');

        editProfileForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(editProfileForm);
            const password = formData.get('password');
            const confirmPassword = formData.get('confirm_password');

            // Validate passwords match if provided
            if (password && password !== confirmPassword) {
                showMessage('Passwords do not match!', 'error');
                return;
            }

            // Show loading state
            showMessage('Updating profile...', 'loading');

            try {
                const response = await fetch('update_profile.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showMessage(data.message, 'success');
                    
                    // Update session data display after 1 second
                    setTimeout(() => {
                        // Optionally redirect back or reload
                        window.location.href = 'resident_dashboard.php'; // or wherever your main page is
                    }, 1500);
                } else {
                    showMessage(data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('Failed to update profile. Please try again.', 'error');
            }
        });

        function showMessage(message, type) {
            updateMessage.classList.remove('hidden', 'bg-blue-100', 'text-blue-800', 'bg-green-100', 'text-green-800', 'bg-red-100', 'text-red-800');
            
            if (type === 'loading') {
                updateMessage.classList.add('bg-blue-100', 'text-blue-800');
            } else if (type === 'success') {
                updateMessage.classList.add('bg-green-100', 'text-green-800');
            } else if (type === 'error') {
                updateMessage.classList.add('bg-red-100', 'text-red-800');
            }
            
            updateMessage.textContent = message;
        }
    </script>
</body>
</html>