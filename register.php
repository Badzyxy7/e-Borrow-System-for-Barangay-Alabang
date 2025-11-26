<?php
include "db.php";
require_once "register_handler.php";
session_start();

$success = false;
$error = '';
$formDisabled = false;

// Preserve form data
$formData = [
    'first_name' => '',
    'middle_name' => '',
    'last_name' => '',
    'email' => '',
    'phone_number' => '',
    'birthdate' => '',
    'barangay_id' => '',
    'street' => '',
    'landmark' => ''
];

// Initialize session variables
if (!isset($_SESSION['register_attempts'])) {
    $_SESSION['register_attempts'] = 0;
}
if (!isset($_SESSION['blocked_until'])) {
    $_SESSION['blocked_until'] = 0;
}

// Check if currently blocked
if (time() < $_SESSION['blocked_until']) {
    $remaining = ceil(($_SESSION['blocked_until'] - time()) / 60);
    $error = "You have been temporarily blocked from registering. Please try again in $remaining minute(s).";
    $formDisabled = true;
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && time() >= $_SESSION['blocked_until']) {
    // Preserve form data
    $formData['first_name'] = isset($_POST['first_name']) ? htmlspecialchars(trim($_POST['first_name'])) : '';
    $formData['middle_name'] = isset($_POST['middle_name']) ? htmlspecialchars(trim($_POST['middle_name'])) : '';
    $formData['last_name'] = isset($_POST['last_name']) ? htmlspecialchars(trim($_POST['last_name'])) : '';
    $formData['email'] = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $formData['phone_number'] = isset($_POST['phone_number']) ? htmlspecialchars(trim($_POST['phone_number'])) : '';
    $formData['birthdate'] = isset($_POST['birthdate']) ? htmlspecialchars(trim($_POST['birthdate'])) : '';
    $formData['barangay_id'] = isset($_POST['barangay_id']) ? htmlspecialchars(trim($_POST['barangay_id'])) : '';
    $formData['street'] = isset($_POST['street']) ? htmlspecialchars(trim($_POST['street'])) : '';
    $formData['landmark'] = isset($_POST['landmark']) ? htmlspecialchars(trim($_POST['landmark'])) : '';
    
    $result = handleRegistration($conn);
    
    if ($result['success']) {
        $success = true;
    } else {
        $error = $result['error'];
        if (isset($result['blocked']) && $result['blocked']) {
            $formDisabled = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Barangay Alabang</title>
  <link rel="icon" type="image/jpeg" href="logo.jpg">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <style>
    @keyframes fadeSlide {
      0% { opacity: 0; transform: translateY(20px); }
      100% { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes modalBounce {
      0% { opacity: 0; transform: scale(0.7); }
      50% { transform: scale(1.05); }
      100% { opacity: 1; transform: scale(1); }
    }
    
    @keyframes checkmark {
      0% { transform: scale(0); opacity: 0; }
      50% { transform: scale(1.2); }
      100% { transform: scale(1); opacity: 1; }
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
      20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    
    .animate-fadeSlide { animation: fadeSlide 0.6s ease-out forwards; }
    .animate-modalBounce { animation: modalBounce 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
    .animate-checkmark { animation: checkmark 0.6s ease-out 0.2s forwards; opacity: 0; }
    .animate-spin { animation: spin 1s linear infinite; }
    .animate-shake { animation: shake 0.5s ease-in-out; }
    
    .street-item:hover {
      background-color: #dbeafe;
      transform: translateX(4px);
      transition: all 0.2s ease;
    }
    
    /* Password strength indicator */
    .strength-bar {
      height: 4px;
      border-radius: 2px;
      transition: all 0.3s ease;
    }
    
    .strength-weak { width: 33%; background-color: #ef4444; }
    .strength-medium { width: 66%; background-color: #f59e0b; }
    .strength-strong { width: 100%; background-color: #10b981; }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-cover bg-center relative" 
      style="background-image: url('photos/niggapic.jpg');">
  <div class="absolute inset-0 bg-blue-900/75"></div>

  <div class="relative z-10 w-full max-w-5xl bg-white/95 backdrop-blur-lg rounded-3xl shadow-2xl border border-gray-200 animate-fadeSlide overflow-hidden">
    
    <!-- SINGLE FORM wrapping everything -->
    <form id="registerForm" method="POST" action="" class="grid grid-cols-1 lg:grid-cols-5 min-h-[600px]" <?= $formDisabled ? 'onsubmit="return false;"' : '' ?>>
      
      <!-- Left Side - Logo and Name Fields -->
      <div class="lg:col-span-2 bg-white p-8 flex flex-col justify-center items-center border-r border-gray-200">
        <div class="text-center mb-8">
          <img src="photos/logo.png" alt="Barangay Logo" class="w-32 h-32 mx-auto rounded-full mb-4 shadow-lg">
          <h1 class="text-2xl font-bold text-blue-900">Welcome Batang Alabang</h1>
        </div>

        <div class="w-full space-y-4">
          <input type="text" id="first_name" name="first_name" placeholder="First Name" required
            value="<?= htmlspecialchars($formData['first_name']) ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-gray-50">
          
          <input type="text" id="middle_name" name="middle_name" placeholder="Middle Name" required
            value="<?= htmlspecialchars($formData['middle_name']) ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-gray-50">
          
          <input type="text" id="last_name" name="last_name" placeholder="Last Name" required
            value="<?= htmlspecialchars($formData['last_name']) ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-gray-50">
        </div>
      </div>

      <!-- Right Side - Form Fields -->
      <div class="lg:col-span-3 bg-gradient-to-br from-gray-50 to-white p-8 flex flex-col justify-center">
        
        <h2 class="text-xl font-extrabold text-gray-700 text-center mb-6">Create your resident account</h2>

        <?php if (!empty($error)): ?>
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm font-medium shadow-sm animate-shake">
            <div class="flex items-start">
              <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
              </svg>
              <span><?= htmlspecialchars($error) ?></span>
            </div>
          </div>
        <?php endif; ?>

        <div class="space-y-4">
          
          <!-- Email -->
          <input type="email" name="email" id="email" placeholder="Email (Gmail only)" required
            value="<?= htmlspecialchars($formData['email']) ?>"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-white">

          <!-- Phone Number and Birthdate -->
          <div class="grid grid-cols-2 gap-3">
            <input type="tel" name="phone_number" id="phone_number" placeholder="Phone (09XXXXXXXXX)" required
              value="<?= htmlspecialchars($formData['phone_number']) ?>"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-white">
            
            <input type="text" name="birthdate" id="birthdate" placeholder="Birthdate" required readonly
              value="<?= htmlspecialchars($formData['birthdate']) ?>"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-white cursor-pointer">
          </div>

          <!-- Barangay ID and Barangay -->
          <div class="grid grid-cols-2 gap-3">
            <input type="text" id="barangay_id" name="barangay_id" placeholder="Barangay ID (BA-1234)" maxlength="7" required
              value="<?= htmlspecialchars($formData['barangay_id']) ?>"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-white">
            
            <input type="text" name="barangay" value="Alabang" readonly
              class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-200 cursor-not-allowed shadow-sm">
          </div>

          <!-- Street and Landmark -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <input type="text" id="street_display" placeholder="Select Street" readonly required
                value="<?= htmlspecialchars($formData['street']) ?>"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-white cursor-pointer hover:bg-gray-50"
                onclick="openStreetModal()">
              <input type="hidden" id="street" name="street" value="<?= htmlspecialchars($formData['street']) ?>">
            </div>
            
            <input type="text" name="landmark" id="landmark" placeholder="Landmark" required
              value="<?= htmlspecialchars($formData['landmark']) ?>"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-white">
          </div>

          <!-- Password with Strength Indicator -->
          <div>
            <div class="relative">
              <input type="password" id="password" name="password" placeholder="Password (min 8 characters)" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-white">
              <button type="button" id="toggle_password" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                <svg id="password_icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                </svg>
              </button>
            </div>
            <div class="mt-2">
              <div class="w-full bg-gray-200 rounded-full h-1 overflow-hidden">
                <div id="strength_bar" class="strength-bar"></div>
              </div>
              <p id="strength_text" class="text-xs mt-1 text-gray-600"></p>
            </div>
          </div>

          <!-- Confirm Password -->
          <div class="relative">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-white">
          </div>

          <!-- Register Button -->
          <button type="submit" name="register" id="register_btn"
            class="w-full py-3 bg-gradient-to-r from-blue-900 to-blue-700 text-white font-bold rounded-lg hover:bg-blue-800 transition shadow-md flex items-center justify-center"
            <?= $formDisabled ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : '' ?>>
            <span id="btn_text">Register</span>
            <svg id="btn_spinner" class="hidden w-5 h-5 ml-2 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </button>

          <div class="text-center mt-4">
            <span class="text-gray-600">Already have an account?</span>
            <a href="login.php" class="text-blue-900 hover:underline font-semibold ml-1">Login</a>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Street Selection Modal -->
  <div id="streetModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-md w-full mx-4 max-h-[80vh] flex flex-col">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold text-blue-900">Select Your Street</h3>
        <button onclick="closeStreetModal()" class="text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      
      <input type="text" id="streetSearch" placeholder="Search streets..." 
        class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500"
        onkeyup="filterStreets()">
      
      <div id="streetList" class="overflow-y-auto flex-1 space-y-1"></div>
    </div>
  </div>

  <!-- OTP Verification Modal -->
  <?php if ($success): ?>
  <div id="otpModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-md w-full animate-modalBounce relative">
      
      <!-- Close Button -->
      <button onclick="cancelOTP()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
      
      <div class="flex justify-center mb-6">
        <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
          </svg>
        </div>
      </div>
      
      <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Check Your Email!</h2>
        <p class="text-gray-600 mb-1">We've sent a 6-digit verification code to</p>
        <p class="text-blue-600 font-semibold"><?= htmlspecialchars($_POST['email'] ?? '') ?></p>
      </div>
      
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2 text-center">Enter Verification Code</label>
        <input type="text" id="otp_input" maxlength="6" placeholder="000000"
               class="w-full px-4 py-4 text-center text-2xl font-bold tracking-widest border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
               oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        <p id="otp_error" class="text-red-600 text-sm mt-2 text-center hidden"></p>
      </div>
      
      <div class="text-center mb-4">
        <p class="text-sm text-gray-600">
          Code expires in: <span id="timer" class="font-bold text-red-600">2:00</span>
        </p>
      </div>
      
      <button onclick="verifyOTP()" 
              class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-3 px-6 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg mb-3">
        Verify Code
      </button>
      
      <div class="text-center">
        <button id="resend_btn" onclick="resendOTP()" 
                class="text-blue-600 hover:underline text-sm font-medium" disabled>
          Resend Code (<span id="resend_timer">60</span>s)
        </button>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Success Modal -->
  <div id="successModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-md w-full animate-modalBounce text-center">
      
      <div class="flex justify-center mb-6">
        <div class="w-24 h-24 bg-gradient-to-r from-green-400 to-green-600 rounded-full flex items-center justify-center animate-checkmark">
          <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
      </div>
      
      <h2 class="text-3xl font-bold text-gray-800 mb-3">Registration Complete!</h2>
      <p class="text-gray-600 mb-6">
        Your account has been successfully verified. Welcome to Barangay Alabang!
      </p>
      
      <button onclick="goToLogin()" 
              class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold py-3 px-6 rounded-xl hover:from-green-600 hover:to-green-700 transition-all shadow-lg">
        Continue to Login
      </button>
      
      <p class="text-sm text-gray-500 mt-4">Redirecting in <span id="redirect_timer">3</span> seconds...</p>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    // Set initial birthdate value if exists
    <?php if (!empty($formData['birthdate'])): ?>
    document.addEventListener('DOMContentLoaded', function() {
      const birthdateInput = document.getElementById('birthdate');
      birthdateInput.value = '<?= $formData['birthdate'] ?>';
    });
    <?php endif; ?>
  </script>
  <script src="register_script.js"></script>
</body>
</html>