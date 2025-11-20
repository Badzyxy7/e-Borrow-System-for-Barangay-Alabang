<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// fallback avatar if none is set
$avatar = !empty($_SESSION['avatar']) ? "../photos/avatars/" . $_SESSION['avatar'] : "../photos/avatars/default.png";
$name   = $_SESSION['name']  ?? 'Guest';
$email  = $_SESSION['email'] ?? '';
$barangay = $_SESSION['barangay'] ?? 'Alabang';
$street = $_SESSION['street'] ?? 'N/A';
$landmark = $_SESSION['landmark'] ?? 'N/A';
?>
<!-- Header - Responsive margin for sidebar -->
<header class="fixed top-0 left-16 md:left-64 right-0 h-16 bg-white shadow flex items-center z-20">
  <div class="w-full flex items-center justify-between px-4 md:px-6">
    <!-- Page Title -->
    <h1 class="text-base md:text-xl font-bold text-gray-800 truncate">
      <?php echo isset($page_title) ? htmlspecialchars($page_title) : "Barangay System"; ?>
    </h1>

    <!-- Profile Trigger -->
    <div class="flex items-center gap-2 md:gap-4">
      <button id="profileBtn" class="flex items-center bg-gray-100 px-2 md:px-4 py-2 rounded-full shadow hover:shadow-md transition">
        <img id="avatarPreview"
             src="<?php echo htmlspecialchars($avatar); ?>" 
             alt="Profile" 
             class="w-8 h-8 md:w-10 md:h-10 rounded-full border-2 border-blue-600 object-cover">
        <!-- Name/Email - Hidden on small mobile, visible on larger screens -->
        <div class="ml-2 md:ml-3 text-left hidden sm:block">
          <p class="text-xs md:text-sm font-semibold text-gray-800 truncate max-w-[120px] md:max-w-none">
            <?php echo htmlspecialchars($name); ?>
          </p>
          <p class="text-xs text-gray-500 truncate max-w-[120px] md:max-w-none">
            <?php echo htmlspecialchars($email); ?>
          </p>
        </div>
      </button>
    </div>
  </div>
</header>


<!-- Profile Panel Overlay -->
<div id="profileOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40"></div>

<!-- Slide-out Profile Panel - Responsive width -->
<div id="profilePanel" class="fixed top-0 right-0 w-full sm:w-96 h-full bg-white shadow-lg transform translate-x-full transition-transform duration-300 z-50 overflow-y-auto">
  <div class="p-4 sm:p-6">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-lg sm:text-xl font-semibold text-gray-700">My Profile</h2>
      <button id="closeProfile" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
    </div>

    <!-- Avatar Preview -->
    <div class="flex flex-col items-center mb-6">
      <img id="avatarPreviewPanel"
           src="<?php echo htmlspecialchars($avatar); ?>"
           alt="Profile"
           class="w-20 h-20 sm:w-24 sm:h-24 rounded-full border-4 border-blue-600 object-cover mb-3">
    </div>

    <!-- Avatar Form -->
    <form id="avatarForm" action="update_avatar.php" method="POST" enctype="multipart/form-data" class="space-y-4 mb-6 pb-6 border-b">
      <label class="block text-gray-700 font-medium mb-1 text-sm sm:text-base">Change Avatar</label>
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
        <input type="file" name="avatar" accept="image/*" class="hidden" id="avatarInput">
        <button type="button" 
                onclick="document.getElementById('avatarInput').click()" 
                class="bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300 transition text-sm sm:text-base">
          Choose File
        </button>
        <button type="submit" 
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm sm:text-base">
          Save Avatar
        </button>
      </div>
      <div id="avatarMessage" class="mt-1 text-sm text-center"></div>
    </form>

    <!-- Profile Information (Read-Only) -->
    <div class="space-y-4 mb-6">
      <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-4">Profile Information</h3>
      
      <div>
        <label class="block text-gray-600 text-sm font-medium mb-1">Name</label>
        <div class="w-full px-3 sm:px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-800 text-sm sm:text-base break-words">
          <?php echo htmlspecialchars($name); ?>
        </div>
      </div>

      <div>
        <label class="block text-gray-600 text-sm font-medium mb-1">Email</label>
        <div class="w-full px-3 sm:px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-800 text-sm sm:text-base break-all">
          <?php echo htmlspecialchars($email); ?>
        </div>
      </div>

      <div>
        <label class="block text-gray-600 text-sm font-medium mb-1">Barangay</label>
        <div class="w-full px-3 sm:px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-800 text-sm sm:text-base">
          <?php echo htmlspecialchars($barangay); ?>
        </div>
      </div>

      <div>
        <label class="block text-gray-600 text-sm font-medium mb-1">Street</label>
        <div class="w-full px-3 sm:px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-800 text-sm sm:text-base">
          <?php echo htmlspecialchars($street); ?>
        </div>
      </div>

      <div>
        <label class="block text-gray-600 text-sm font-medium mb-1">Landmark</label>
        <div class="w-full px-3 sm:px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-800 text-sm sm:text-base">
          <?php echo htmlspecialchars($landmark); ?>
        </div>
      </div>

      <a href="edit_profile.php" class="block w-full bg-green-600 text-white py-2 sm:py-3 rounded-lg hover:bg-green-700 transition text-center font-medium text-sm sm:text-base">
        Edit Profile
      </a>
    </div>
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
        // Update both avatar previews instantly
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
</script>