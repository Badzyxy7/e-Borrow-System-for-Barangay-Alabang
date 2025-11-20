<?php
include "db.php";
session_start();

$success = false;

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

if (isset($_POST['register']) && time() >= $_SESSION['blocked_until']) {
    $firstName = trim($_POST['first_name']);
    $middleName = trim($_POST['middle_name']);
    $lastName = trim($_POST['last_name']);
    $fullName = $firstName . " " . $middleName . " " . $lastName;
    $email = trim($_POST['email']);
    $barangayId = trim($_POST['barangay_id']);
    $barangay = trim($_POST['barangay']);
    $street = trim($_POST['street']);
    $landmark = trim($_POST['landmark']);
    $password = md5($_POST['password']); 
    $confirmPassword = md5($_POST['confirm_password']);
    $role = "resident"; 

    // Password match check
    if ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    }
    // Gmail validation
    elseif (!preg_match("/@gmail\.com$/", $email)) {
        $error = "Only Gmail accounts are allowed.";
    } 
    else {
        // Validate Barangay ID from JSON
        $jsonFile = "barangay_ids.json";
        $validIds = json_decode(file_get_contents($jsonFile), true);

        if (!in_array($barangayId, $validIds)) {
            $_SESSION['register_attempts']++;

            if ($_SESSION['register_attempts'] >= 3) {
                $_SESSION['blocked_until'] = time() + (10 * 60); // block 10 minutes
                $error = "Too many failed attempts. You are blocked for 10 minutes.";
                $formDisabled = true;
            } else {
                $remaining = 3 - $_SESSION['register_attempts'];
                $error = "You are not a registered resident of Barangay Alabang. ($remaining attempts left)";
            }
       } else {
    // Check if name or email already exists
    $check = "SELECT * FROM users WHERE (name='$fullName' OR email='$email') LIMIT 1";
    $result = $conn->query($check);

    if ($result && $result->num_rows > 0) {
        $existing = $result->fetch_assoc();
        
        // Allow re-registration if unverified
        if ($existing['is_verified'] == 0 && $existing['email'] === $email) {
            // Generate new OTP
            $otp_code = sprintf("%06d", mt_rand(1, 999999));
            $otp_expiry = date('Y-m-d H:i:s', strtotime('+2 minutes'));
            
            // Update existing record with new info
            $sql = "UPDATE users SET 
                    name='$fullName', 
                    password='$password',
                    barangay_id='$barangayId',
                    barangay='$barangay',
                    street='$street',
                    landmark='$landmark',
                    otp_code='$otp_code',
                    otp_expiry='$otp_expiry'
                    WHERE email='$email'";
            
            if ($conn->query($sql) === TRUE) {
                require_once 'email_config.php';
                $emailSent = sendOTP($email, $fullName, $otp_code);
                
                if ($emailSent) {
                    $success = true;
                    $_SESSION['register_email'] = $email;
                    $_SESSION['register_attempts'] = 0;
                    $_SESSION['blocked_until'] = 0;
                } else {
                    $error = "Failed to send verification email. Please try again.";
                }
            } else {
                $error = "Error: " . $conn->error;
            }
        }
        // Block if verified user exists
        elseif ($existing['name'] === $fullName) {
            $error = "This name is already taken!";
        } elseif ($existing['email'] === $email) {
            $error = "This email is already registered!";
        }
    } else {
        // NEW USER - Generate OTP and insert
        $otp_code = sprintf("%06d", mt_rand(1, 999999));
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+2 minutes'));

        $sql = "INSERT INTO users (name, email, password, role, barangay_id, barangay, street, landmark, otp_code, otp_expiry, is_verified) 
                VALUES ('$fullName', '$email', '$password', '$role', '$barangayId', '$barangay', '$street', '$landmark', '$otp_code', '$otp_expiry', 0)";

        if ($conn->query($sql) === TRUE) {
            require_once 'email_config.php';
            $emailSent = sendOTP($email, $fullName, $otp_code);
            
            if ($emailSent) {
                $success = true;
                $_SESSION['register_email'] = $email;
                $_SESSION['register_attempts'] = 0;
                $_SESSION['blocked_until'] = 0;
            } else {
                $conn->query("DELETE FROM users WHERE email='$email'");
                $error = "Failed to send verification email. Please try again.";
            }
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <link rel="icon" type="image/jpeg" href="logo.jpg">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    
    @keyframes fadeSlide {
      0% {
        opacity: 0;
        transform: translateY(20px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes modalBounce {
    0% {
      opacity: 0;
      transform: scale(0.7);
    }
    50% {
      transform: scale(1.05);
    }
    100% {
      opacity: 1;
      transform: scale(1);
  }
}
    
    @keyframes checkmark {
      0% {
        transform: scale(0);
        opacity: 0;
      }
      50% {
        transform: scale(1.2);
      }
      100% {
        transform: scale(1);
        opacity: 1;
      }
    }
    
    .animate-fadeSlide {
      animation: fadeSlide 0.6s ease-out forwards;
    }
    
    .animate-modalBounce {
      animation: modalBounce 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }
    
    .animate-checkmark {
      animation: checkmark 0.6s ease-out 0.2s forwards;
      opacity: 0;
    }

    .street-item:hover {
      background-color: #dbeafe;
      transform: translateX(4px);
      transition: all 0.2s ease;
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-cover bg-center relative" 
      style="background-image: url('photos/niggapic.jpg');">
  <div class="absolute inset-0 bg-blue-900/75"></div>

  <!-- Main Container matching the photo layout -->
  <div class="relative z-10 w-full max-w-5xl bg-white/95 backdrop-blur-lg rounded-3xl shadow-2xl border border-gray-200 animate-fadeSlide overflow-hidden">
    
    <div class="grid grid-cols-1 lg:grid-cols-5 min-h-[600px]">
      
      <!-- Left Side - Logo and Name Fields (2 columns) -->
      <div class="lg:col-span-2 bg-white p-8 flex flex-col justify-center items-center border-r border-gray-200">
        
        <!-- Logo and Title -->
        <div class="text-center mb-8">
          <img src="photos/logo.png" alt="Barangay Logo" class="w-32 h-32 mx-auto rounded-full mb-4 shadow-lg">
          <h1 class="text-2xl font-bold text-blue-900">Welcome Batang Alabang</h1>
        </div>

        <!-- Left Column Fields -->
        <div class="w-full space-y-4">
          <input type="text" form="registerForm" name="first_name" placeholder="First Name" required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-gray-50">
          
          <input type="text" form="registerForm" name="middle_name" placeholder="Middle Name" required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-gray-50">
          
          <input type="text" form="registerForm" name="last_name" placeholder="Last Name" required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-gray-50">
        </div>
      </div>

      <!-- Right Side - Form Fields (3 columns) -->
      <div class="lg:col-span-3 bg-gradient-to-br from-gray-50 to-white p-8 flex flex-col justify-center">
        
        <h2 class="text-xl font-extrabold text-gray-700 text-center mb-6">Create your resident account</h2>

        <?php if (!empty($error)): ?>
          <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-center text-sm font-medium shadow-sm">
            <?= $error ?>
          </div>
        <?php endif; ?>

        <form id="registerForm" method="POST" class="space-y-4" <?= isset($formDisabled) && $formDisabled ? 'onsubmit="return false;"' : '' ?>>
          
          <!-- Email - Full Width -->
          <input type="email" name="email" placeholder="Email" required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-white">

          <!-- Barangay ID and Barangay - Two Columns -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <input type="text" id="barangay_id" name="barangay_id" 
                    placeholder="Barangay ID Number" 
                    maxlength="7" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-white">
              <p id="barangay_error" class="text-red-600 text-xs mt-1 hidden">
                Format: BA-1234
              </p>
            </div>
            
            <input type="text" name="barangay" value="Alabang" readonly
              class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-200 cursor-not-allowed shadow-sm">
          </div>

          <!-- Street and Landmark - Two Columns -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <input type="text" id="street_display" placeholder="Street" readonly required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-white cursor-pointer hover:bg-gray-50"
                onclick="openStreetModal()">
              <input type="hidden" id="street" name="street">
            </div>
            
            <input type="text" name="landmark" placeholder="Land mark" required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-white">
          </div>

          <!-- Password - Full Width -->
          <div class="relative">
            <input type="password" id="password" name="password" placeholder="Password" required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-white">
            <button type="button" id="toggle_password" 
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
              <svg id="password_icon_hide" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
              </svg>
              <svg id="password_icon_show" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
            </button>
          </div>

          <!-- Confirm Password - Full Width -->
          <div class="relative">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm bg-white">
            <button type="button" id="toggle_confirm_password" 
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
              <svg id="confirm_password_icon_hide" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
              </svg>
              <svg id="confirm_password_icon_show" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
            </button>
          </div>

          <p id="password_error" class="text-red-600 text-sm hidden">
            Passwords do not match.
          </p>

          <!-- Register Button -->
          <button type="submit" name="register"
            class="w-full py-3 bg-gradient-to-r from-blue-900 to-blue-700 text-white font-bold rounded-lg hover:bg-blue-800 transition shadow-md"
            <?= isset($formDisabled) && $formDisabled ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : '' ?>>
            Register
          </button>

          <!-- Login Link -->
          <div class="text-center mt-4">
            <span class="text-gray-600">Already have an account?</span>
            <a href="login.php" class="text-blue-900 hover:underline font-semibold ml-1">Login</a>
          </div>
        </form>
      </div>
    </div>
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
      
      <div id="streetList" class="overflow-y-auto flex-1 space-y-2">
        <!-- Streets will be populated by JavaScript -->
      </div>
    </div>
  </div>

  <!-- OTP Verification Modal -->
<?php if ($success): ?>
<div id="otpModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-md w-full animate-modalBounce">
        
        <!-- Icon -->
        <div class="flex justify-center mb-6">
            <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>
        
        <!-- Message -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Check Your Email!</h2>
            <p class="text-gray-600 mb-1">
                We've sent a 6-digit verification code to
            </p>
            <p class="text-blue-600 font-semibold"><?= $_POST['email'] ?? '' ?></p>
        </div>
        
        <!-- OTP Input -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2 text-center">Enter Verification Code</label>
            <input type="text" id="otp_input" maxlength="6" placeholder="000000"
                   class="w-full px-4 py-4 text-center text-2xl font-bold tracking-widest border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            <p id="otp_error" class="text-red-600 text-sm mt-2 text-center hidden"></p>
        </div>
        
        <!-- Timer -->
        <div class="text-center mb-4">
            <p class="text-sm text-gray-600">
                Code expires in: <span id="timer" class="font-bold text-red-600">2:00</span>
            </p>
        </div>
        
        <!-- Verify Button -->
        <button onclick="verifyOTP()" 
                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-3 px-6 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg mb-3">
            Verify Code
        </button>
        
        <!-- Resend Link -->
        <div class="text-center">
            <button id="resend_btn" onclick="resendOTP()" 
                    class="text-blue-600 hover:underline text-sm font-medium" disabled>
                Resend Code (<span id="resend_timer">60</span>s)
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Success Modal (shown after OTP verification) -->
<div id="successModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-md w-full animate-modalBounce text-center">
    
    <!-- Success Icon -->
    <div class="flex justify-center mb-6">
      <div class="w-24 h-24 bg-gradient-to-r from-green-400 to-green-600 rounded-full flex items-center justify-center animate-checkmark">
        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
        </svg>
      </div>
    </div>
    
    <!-- Success Message -->
    <h2 class="text-3xl font-bold text-gray-800 mb-3">Registration Complete!</h2>
    <p class="text-gray-600 mb-6">
      Your account has been successfully verified. Welcome to Barangay Alabang!
    </p>
    
    <!-- Button -->
    <button onclick="goToLogin()" 
            class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold py-3 px-6 rounded-xl hover:from-green-600 hover:to-green-700 transition-all shadow-lg">
      Continue to Login
    </button>
    
    <p class="text-sm text-gray-500 mt-4">Redirecting in <span id="redirect_timer">3</span> seconds...</p>
  </div>
</div>

<script>
let timeLeft = 120; // 2 minutes
let resendTimeLeft = 60; // 1 minute before can resend
let timerInterval;
let resendInterval;

// Start countdown timer
function startTimer() {
    timerInterval = setInterval(() => {
        timeLeft--;
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        document.getElementById('timer').textContent = 
            `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            document.getElementById('timer').textContent = 'EXPIRED';
            document.getElementById('otp_error').textContent = 'Code expired. Please request a new one.';
            document.getElementById('otp_error').classList.remove('hidden');
        }
    }, 1000);
}

// Start resend timer
function startResendTimer() {
    const resendBtn = document.getElementById('resend_btn');
    resendBtn.disabled = true;
    
    resendInterval = setInterval(() => {
        resendTimeLeft--;
        document.getElementById('resend_timer').textContent = resendTimeLeft;
        
        if (resendTimeLeft <= 0) {
            clearInterval(resendInterval);
            resendBtn.disabled = false;
            resendBtn.innerHTML = 'Resend Code';
        }
    }, 1000);
}

// Verify OTP
function verifyOTP() {
    const otpInput = document.getElementById('otp_input').value;
    const errorDiv = document.getElementById('otp_error');
    
    if (otpInput.length !== 6) {
        errorDiv.textContent = 'Please enter a 6-digit code.';
        errorDiv.classList.remove('hidden');
        return;
    }
    
    // Send verification request
    fetch('verify_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `otp=${otpInput}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            clearInterval(timerInterval);
            clearInterval(resendInterval);
            
            // Hide OTP modal and show success modal
            document.getElementById('otpModal').classList.add('hidden');
            showSuccessModal();
        } else {
            errorDiv.textContent = data.message;
            errorDiv.classList.remove('hidden');
        }
    })
    .catch(err => {
        errorDiv.textContent = 'Verification failed. Please try again.';
        errorDiv.classList.remove('hidden');
    });
}

// Show success modal with countdown
function showSuccessModal() {
    const successModal = document.getElementById('successModal');
    successModal.classList.remove('hidden');
    successModal.classList.add('flex');
    
    let countdown = 3;
    const timerSpan = document.getElementById('redirect_timer');
    
    const countdownInterval = setInterval(() => {
        countdown--;
        timerSpan.textContent = countdown;
        
        if (countdown <= 0) {
            clearInterval(countdownInterval);
            goToLogin();
        }
    }, 1000);
}

// Resend OTP
function resendOTP() {
    fetch('resend_otp.php', {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            timeLeft = 120;
            resendTimeLeft = 60;
            startTimer();
            startResendTimer();
            document.getElementById('otp_error').classList.add('hidden');
            document.getElementById('otp_input').value = '';
            alert('New code sent to your email!');
        } else {
            alert(data.message);
        }
    });
}

// Go to login page
function goToLogin() {
    window.location.href = 'login.php?verified=1';
}

<?php if ($success): ?>
// Start timers on load
startTimer();
startResendTimer();

// Allow Enter key to submit
document.getElementById('otp_input').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') verifyOTP();
});
<?php endif; ?>

// List of streets in Barangay Alabang
const streets = [
  "Acacia Avenue",
  "Alabang-Zapote Road",
  "Commerce Avenue",
  "Corporate Avenue",
  "Filinvest Avenue",
  "Madrigal Avenue",
  "Parkway Avenue",
  "Spectrum Midway",
  "Civic Drive",
  "Festival Drive",
  "Forbestown Road",
  "Investment Drive",
  "Northgate Avenue",
  "South Station Road",
  "Westgate Avenue",
  "Daang Hari Road",
  "Molino Road",
  "Zapote River",
  "Bayview Drive",
  "Garden Villas",
  "Palm Drive",
  "Redwood Lane",
  "Willow Street"
].sort();

const barangayInput = document.getElementById('barangay_id');
const barangayError = document.getElementById('barangay_error');
const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirm_password');
const passwordError = document.getElementById('password_error');
const togglePassword = document.getElementById('toggle_password');
const toggleConfirmPassword = document.getElementById('toggle_confirm_password');
const form = document.getElementById('registerForm');

// Populate street list
function populateStreets() {
  const streetList = document.getElementById('streetList');
  streetList.innerHTML = streets.map(street => `
    <div class="street-item px-4 py-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 transition-all" 
         onclick="selectStreet('${street}')">
      <p class="font-medium text-gray-700">${street}</p>
    </div>
  `).join('');
}

function openStreetModal() {
  document.getElementById('streetModal').classList.remove('hidden');
  document.getElementById('streetModal').classList.add('flex');
  populateStreets();
}

function closeStreetModal() {
  document.getElementById('streetModal').classList.add('hidden');
  document.getElementById('streetModal').classList.remove('flex');
}

function selectStreet(street) {
  document.getElementById('street_display').value = street;
  document.getElementById('street').value = street;
  closeStreetModal();
}

function filterStreets() {
  const searchValue = document.getElementById('streetSearch').value.toLowerCase();
  const filteredStreets = streets.filter(street => 
    street.toLowerCase().includes(searchValue)
  );
  
  const streetList = document.getElementById('streetList');
  streetList.innerHTML = filteredStreets.map(street => `
    <div class="street-item px-4 py-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 transition-all" 
         onclick="selectStreet('${street}')">
      <p class="font-medium text-gray-700">${street}</p>
    </div>
  `).join('');
}

function validateBarangayId() {
  const pattern = /^BA-\d{4}$/;
  if (!pattern.test(barangayInput.value)) {
    barangayInput.classList.remove('border-gray-300', 'focus:ring-blue-500');
    barangayInput.classList.add('border-red-500', 'focus:ring-red-500');
    barangayError.classList.remove('hidden');
    return false;
  } else {
    barangayInput.classList.remove('border-red-500', 'focus:ring-red-500');
    barangayInput.classList.add('border-gray-300', 'focus:ring-blue-500');
    barangayError.classList.add('hidden');
    return true;
  }
}

function validatePasswords() {
  if (confirmPasswordInput.value !== passwordInput.value || confirmPasswordInput.value === "") {
    confirmPasswordInput.classList.remove('border-gray-300', 'focus:ring-blue-500');
    confirmPasswordInput.classList.add('border-red-500', 'focus:ring-red-500');
    passwordError.classList.remove('hidden');
    return false;
  } else {
    confirmPasswordInput.classList.remove('border-red-500', 'focus:ring-red-500');
    confirmPasswordInput.classList.add('border-gray-300', 'focus:ring-blue-500');
    passwordError.classList.add('hidden');
    return true;
  }
}

// Toggle password visibility
togglePassword.addEventListener('click', () => {
  const type = passwordInput.type === 'password' ? 'text' : 'password';
  passwordInput.type = type;
  document.getElementById('password_icon_hide').classList.toggle('hidden');
  document.getElementById('password_icon_show').classList.toggle('hidden');
});

toggleConfirmPassword.addEventListener('click', () => {
  const type = confirmPasswordInput.type === 'password' ? 'text' : 'password';
  confirmPasswordInput.type = type;
  document.getElementById('confirm_password_icon_hide').classList.toggle('hidden');
  document.getElementById('confirm_password_icon_show').classList.toggle('hidden');
});

// Real-time validation
barangayInput.addEventListener('input', validateBarangayId);
passwordInput.addEventListener('input', validatePasswords);
confirmPasswordInput.addEventListener('input', validatePasswords);

// Block submission if invalid
form.addEventListener('submit', (e) => {
  const streetValue = document.getElementById('street').value;
  if (!streetValue) {
    e.preventDefault();
    alert('Please select a street from the list.');
    return;
  }
  if (!validateBarangayId() || !validatePasswords()) {
    e.preventDefault();
  }
});
</script>
</body>
</html>