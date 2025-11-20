<?php
session_start();
include "db.php";

// Initialize security tracking if not set
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = 0;
}

$lockout_time = 300; // 5 minutes (in seconds)
$error = "";

// Check lockout status
if ($_SESSION['login_attempts'] >= 5) {
    $remaining_lockout = time() - $_SESSION['last_attempt_time'];

    if ($remaining_lockout < $lockout_time) {
        $minutes_left = ceil(($lockout_time - $remaining_lockout) / 60);
        $error = "Too many failed login attempts. Please try again after {$minutes_left} minute(s).";
    } else {
        // Reset after lockout expires
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = 0;
    }
}

if (isset($_POST['login']) && empty($error)) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password']) || $row['password'] === md5($password)) {
            
            // ✅ CHECK EMAIL VERIFICATION FIRST
            if ($row['is_verified'] == 0) {
                $error = "Please verify your email before logging in. Check your inbox for the verification code.";
                // Don't count as failed attempt - just inform user
            } else {
                // ✅ Reset login attempts after successful login
                $_SESSION['login_attempts'] = 0;
                $_SESSION['last_attempt_time'] = 0;

                // Upgrade old md5 password to secure hash
                if ($row['password'] === md5($password)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt_update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                    $stmt_update->bind_param("si", $newHash, $row['id']);
                    $stmt_update->execute();
                }
                
                // Set session variables
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['avatar'] = !empty($row['avatar']) ? $row['avatar'] : null;
                $_SESSION['email'] = $row['email'];
                $_SESSION['barangay'] = $row['barangay'] ?? 'Alabang';
                $_SESSION['street'] = $row['street'] ?? 'N/A';
                $_SESSION['landmark'] = $row['landmark'] ?? 'N/A';

                // Redirect based on role
                if ($row['role'] === 'admin') {
                    header("Location: /equipment_lending/adminside/admin_dashboard.php");
                } elseif ($row['role'] === 'staff') {
                    header("Location: /equipment_lending/staffside/staff_dashboard.php");
                } else {
                    header("Location: /equipment_lending/clientside/resident_dashboard.php");
                }
                exit();
            }  
        } else {
            // ❌ Wrong password
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();
            $remaining = 5 - $_SESSION['login_attempts'];
            if ($remaining <= 0) {
                $error = "Too many failed attempts. Please wait 5 minutes before trying again.";
            } else {
                $error = "Invalid email or password. You have {$remaining} attempt(s) left.";
            }
        }
    } else {
        // ❌ No user found
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
        $remaining = 5 - $_SESSION['login_attempts'];
        if ($remaining <= 0) {
            $error = "Too many failed attempts. Please wait 5 minutes before trying again.";
        } else {
            $error = "Invalid email or password. You have {$remaining} attempt(s) left.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login</title>
  <link rel="icon" type="image/jpeg" href="logo.jpg">
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    @keyframes fadeSlide {
      0% { opacity: 0; transform: translateY(16px); }
      100% { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeSlide { animation: fadeSlide 0.55s ease-out both; }
    .icon-btn:focus { outline: 2px solid transparent; box-shadow: 0 0 0 3px rgba(59,130,246,0.25); border-radius: 9999px; }
  </style>
</head>

<body class="min-h-screen flex items-center justify-center bg-cover bg-center relative" style="background-image: url('photos/niggapic.jpg');">
  <div class="absolute inset-0 bg-blue-900/75"></div>

  <div class="relative z-10 w-full max-w-3xl bg-white rounded-2xl shadow-2xl overflow-hidden animate-fadeSlide">
    <div class="flex flex-col md:flex-row min-h-[500px]">
      <!-- Left Side - Logo Section -->
      <div class="md:w-2/5 bg-white flex flex-col items-center justify-center p-8 md:p-10">
        <div class="w-52 h-52 flex items-center justify-center mb-6">
          <!-- Barangay Logo -->
          <img src="photos/logo.png" alt="Barangay Logo" class="w-full h-full object-contain">
        </div>
        <h2 class="text-xl font-extrabold text-blue-900 text-center leading-tight">eBorrow System</h2>
        <h3 class="text-lg font-extrabold text-blue-900 text-center mt-1">for Barangay Alabang</h3>
      </div>

      <!-- Right Side - Login Form -->
      <div class="md:w-3/5 p-8 md:p-10 flex flex-col justify-center">
        <div class="max-w-sm mx-auto w-full">
          <div class="mb-6">
            <h2 class="text-2xl font-bold text-black-90 text-center ">Welcome back Batang Alabang</h2>
          
            <h1 class="text-base font-semibold text-gray-700 text-center ">Login to your account</h1>
          </div>

          <?php if (!empty($error)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm font-medium shadow-sm">
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <form id="login-form" method="POST" class="space-y-4">
            <div>
              <input id="email" type="email" name="email" placeholder="Email" required
                     class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50" />
            </div>

            <div class="relative">
              <input id="password" type="password" name="password" placeholder="Password" required
                     class="w-full px-4 py-2.5 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50" />
              <button type="button" id="togglePassword" aria-label="Show password" class="icon-btn absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                <svg id="eyeSlash" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-7 0-10-7-10-7a19.635 19.635 0 015.195-6.435M6.225 6.225A19.635 19.635 0 002 12s3 7 10 7c1.44 0 2.805-.27 4.05-.75M3 3l18 18"/>
                </svg>
                <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
              </button>
            </div>

            <button type="submit" name="login" 
                    class="w-full py-2.5 bg-gradient-to-r from-blue-900 to-blue-700 text-white font-bold rounded-lg hover:bg-blue-800 transition shadow-md">
              Login
            </button>
          </form>

          <div class="mt-4 text-center">
            <span class="text-gray-600 text-sm">Don't have an account? </span>
            <a href="register.php" class="text-blue-900 hover:underline font-medium text-sm">Sign in</a>
          </div>

          <div class="mt-4 text-center">
            <a href="index.php" class="inline-flex items-center text-gray-600 hover:text-gray-800 font-medium text-sm">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Back to Home
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    (function(){
      const pwd = document.getElementById('password');
      const toggle = document.getElementById('togglePassword');
      const eyeOpen = document.getElementById('eyeOpen');
      const eyeSlash = document.getElementById('eyeSlash');
      toggle.addEventListener('click', () => {
        const isHidden = pwd.type === 'password';
        pwd.type = isHidden ? 'text' : 'password';
        eyeOpen.classList.toggle('hidden');
        eyeSlash.classList.toggle('hidden');
        toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
      });
    })();
  </script>

  <div id="loading-overlay" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 flex flex-col items-center justify-center z-50">
    <div class="w-12 h-12 border-4 border-t-transparent border-blue-500 rounded-full animate-spin"></div>
    <p class="mt-4 text-white text-lg font-semibold animate-pulse">Logging in...</p>
  </div>

  <script>
    const form = document.getElementById("login-form");
    const overlay = document.getElementById("loading-overlay");
    form.addEventListener("submit", () => {
      overlay.classList.remove("hidden");
      setTimeout(() => {}, 10);
    });
  </script>
</body>
</html>