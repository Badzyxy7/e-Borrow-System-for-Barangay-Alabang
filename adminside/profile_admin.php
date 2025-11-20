<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// fallback avatar if none is set
$avatar = !empty($_SESSION['avatar']) ? "../photos/avatars/" . $_SESSION['avatar'] : "../photos/avatars/default.png";
$name   = $_SESSION['name']  ?? 'Admin';
$email  = $_SESSION['email'] ?? '';
?>

<!-- Header -->
<header class="ml-64 h-16 bg-white shadow flex items-center">
  <div class="max-w-7xl mx-auto w-full flex items-center justify-between px-6">
    <h1 class="text-xl font-bold text-gray-800">
      <?php echo isset($page_title) ? htmlspecialchars($page_title) : "Admin Dashboard"; ?>
    </h1>
    <div class="flex items-center gap-4">
      <!-- Profile Trigger -->
      <button id="profileBtn" class="flex items-center bg-gray-100 px-4 py-2 rounded-full shadow hover:shadow-md transition">
        <img id="avatarPreview"
             src="<?php echo htmlspecialchars($avatar); ?>" 
             alt="Profile" 
             class="w-10 h-10 rounded-full border-2 border-blue-600 object-cover">
        <div class="ml-3 text-left">
          <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($name); ?></p>
          <p class="text-xs text-gray-500"><?php echo htmlspecialchars($email); ?></p>
        </div>
      </button>
    </div>
  </div>
</header>

<!-- Profile Panel Overlay -->
<div id="profileOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40"></div>

<!-- Slide-out Profile Panel -->
<div id="profilePanel" class="fixed top-0 right-0 w-96 h-full bg-white shadow-lg transform translate-x-full transition-transform duration-300 z-50 overflow-y-auto">
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-xl font-semibold text-gray-700">My Profile</h2>
      <button id="closeProfile" class="text-gray-500 hover:text-gray-700">&times;</button>
    </div>

    <!-- Avatar Preview -->
    <div class="flex flex-col items-center mb-6">
      <img id="avatarPreviewPanel"
           src="<?php echo htmlspecialchars($avatar); ?>"
           alt="Profile"
           class="w-24 h-24 rounded-full border-4 border-blue-600 object-cover mb-3">
    </div>

    <!-- Avatar Form -->
    <form id="avatarForm" action="update_avatar.php" method="POST" enctype="multipart/form-data" class="space-y-4 mb-6">
      <label class="block text-gray-700 font-medium mb-1">Change Avatar</label>
      <div class="flex items-center gap-2">
        <input type="file" name="avatar" accept="image/*" class="hidden" id="avatarInput">
        <button type="button" 
                onclick="document.getElementById('avatarInput').click()" 
                class="bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
          Choose File
        </button>
        <button type="submit" 
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
          Save Avatar
        </button>
      </div>
      <div id="avatarMessage" class="mt-1 text-sm text-center"></div>
    </form>

    <!-- Profile Info Form -->
    <form id="profileForm" action="update_profile.php" method="POST" class="space-y-4">
      <div>
        <label class="block text-gray-700 font-medium mb-1">Name</label>
        <input type="text" name="name" 
               value="<?php echo htmlspecialchars($name); ?>" 
               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
      </div>

      <div>
        <label class="block text-gray-700 font-medium mb-1">Email</label>
        <input type="email" name="email" 
               value="<?php echo htmlspecialchars($email); ?>" 
               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
      </div>

      <div>
        <label class="block text-gray-700 font-medium mb-1">New Password</label>
        <input type="password" name="password" 
               placeholder="Enter new password to change" 
               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
      </div>

      <div>
        <label class="block text-gray-700 font-medium mb-1">Confirm Password</label>
        <input type="password" name="confirm_password" 
               placeholder="Confirm new password" 
               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
      </div>

      <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">
        Save Profile
      </button>
      <div id="profileMessage" class="mt-2 text-center text-sm"></div>
    </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const profileBtn = document.getElementById("profileBtn");
  const profileOverlay = document.getElementById("profileOverlay");
  const profilePanel = document.getElementById("profilePanel");
  const closeProfile = document.getElementById("closeProfile");

  // Open/close panel
  profileBtn.addEventListener("click", () => {
    profileOverlay.classList.remove("hidden");
    profilePanel.classList.remove("translate-x-full");
  });
  closeProfile.addEventListener("click", () => {
    profileOverlay.classList.add("hidden");
    profilePanel.classList.add("translate-x-full");
  });
  profileOverlay.addEventListener("click", () => {
    profileOverlay.classList.add("hidden");
    profilePanel.classList.add("translate-x-full");
  });

  // Avatar upload
  const avatarForm = document.getElementById("avatarForm");
  const avatarInput = document.getElementById("avatarInput");
  const avatarMessage = document.getElementById("avatarMessage");
  const avatarPreview = document.getElementById("avatarPreview");
  const avatarPreviewPanel = document.getElementById("avatarPreviewPanel");

  avatarForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (!avatarInput.files[0]) {
      avatarMessage.textContent = "Please choose an image.";
      avatarMessage.className = "text-red-600 text-sm text-center";
      return;
    }

    const formData = new FormData();
    formData.append("avatar", avatarInput.files[0]);

    avatarMessage.textContent = "Uploading...";
    avatarMessage.className = "text-blue-600 text-sm text-center";

    try {
      const res = await fetch("update_avatar.php", {
        method: "POST",
        body: formData,
      });
      const data = await res.json();

      if (data.success) {
        avatarMessage.textContent = data.message;
        avatarMessage.className = "text-green-600 text-sm text-center";
        avatarPreview.src = data.avatar_url + "?t=" + new Date().getTime();
        avatarPreviewPanel.src = data.avatar_url + "?t=" + new Date().getTime();
      } else {
        avatarMessage.textContent = data.message;
        avatarMessage.className = "text-red-600 text-sm text-center";
      }
    } catch (err) {
      avatarMessage.textContent = "Upload failed. Please try again.";
      avatarMessage.className = "text-red-600 text-sm text-center";
      console.error(err);
    }
  });
});

// Profile update
const profileForm = document.getElementById("profileForm");
const profileMessage = document.getElementById("profileMessage");

profileForm.addEventListener("submit", async (e) => {
  e.preventDefault();
  const formData = new FormData(profileForm);

  profileMessage.textContent = "Updating...";
  profileMessage.className = "mt-2 text-center text-blue-600 text-sm";

  try {
    const res = await fetch("update_profile.php", {
      method: "POST",
      body: formData,
    });

    const data = await res.json();

    if (data.success) {
      profileMessage.textContent = data.message;
      profileMessage.className = "mt-2 text-center text-green-600 text-sm";

      // Update header name/email
      if (formData.get("name")) {
        document.querySelector("#profileBtn p").textContent = formData.get("name");
      }
      if (formData.get("email")) {
        document.querySelector("#profileBtn p + p").textContent = formData.get("email");
      }
    } else {
      profileMessage.textContent = data.message;
      profileMessage.className = "mt-2 text-center text-red-600 text-sm";
    }
  } catch (err) {
    profileMessage.textContent = "Update failed. Please try again.";
    profileMessage.className = "mt-2 text-center text-red-600 text-sm";
    console.error(err);
  }
});
</script>
