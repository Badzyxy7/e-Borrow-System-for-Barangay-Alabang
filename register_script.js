// Street List Data with Categories
const streets = [
  { category: "WEST SERVICE ROAD", name: "Rizal Village" },
  { category: "WEST SERVICE ROAD", name: "Crisostomo Ibarra" },
  { category: "WEST SERVICE ROAD", name: "Maria Clara" },
  { category: "WEST SERVICE ROAD", name: "Sisa" },
  { category: "WEST SERVICE ROAD", name: "Basilio" },
  { category: "WEST SERVICE ROAD", name: "Crispin" },
  { category: "WEST SERVICE ROAD", name: "Capitan Tiago" },
  { category: "WEST SERVICE ROAD", name: "Elias" },
  { category: "WEST SERVICE ROAD", name: "Padre Damaso" },
  { category: "WEST SERVICE ROAD", name: "JP Rizal" },
  { category: "WEST SERVICE ROAD", name: "Arevalo Compound" },
  { category: "WEST SERVICE ROAD", name: "CENA" },
  
  { category: "FILINVEST", name: "Dotcom Dr" },
  { category: "FILINVEST", name: "W Parc Drive (Studio City and West Parc)" },
  { category: "FILINVEST", name: "Filinvest Housing" },
  { category: "FILINVEST", name: "Sitio Masagana" },
  { category: "FILINVEST", name: "Alabang-Zapote Road" },
  { category: "FILINVEST", name: "Pacific Rim" },
  { category: "FILINVEST", name: "Filinvest Avenue" },
  { category: "FILINVEST", name: "E Asia Drive" },
  { category: "FILINVEST", name: "Laguna Heights Drive" },
  { category: "FILINVEST", name: "Research Drive" },
  { category: "FILINVEST", name: "Civic Drive" },
  { category: "FILINVEST", name: "Asean Drive" },
  { category: "FILINVEST", name: "Spectrum Midway" },
  { category: "FILINVEST", name: "Northgate Avenue" },
  { category: "FILINVEST", name: "Parkway Street" },
  { category: "FILINVEST", name: "Kuala Lumpur Lane" },
  
  { category: "FILINVEST - Palms", name: "Nipa Palm" },
  { category: "FILINVEST - Palms", name: "Bamboo Palm" },
  { category: "FILINVEST - Palms", name: "Royal Palm" },
  { category: "FILINVEST - Palms", name: "Blue Palm" },
  { category: "FILINVEST - Palms", name: "Red Palm" },
  { category: "FILINVEST - Palms", name: "Ivory Palm" },
  { category: "FILINVEST - Palms", name: "Alexander Palm" },
  { category: "FILINVEST - Palms", name: "Date Palm" },
  { category: "FILINVEST - Palms", name: "Manila Palm" },
  { category: "FILINVEST - Palms", name: "Champagne Palm" },
  { category: "FILINVEST - Palms", name: "Sugar Palm" },
  { category: "FILINVEST - Palms", name: "Fiji Palm" },
  { category: "FILINVEST - Palms", name: "Phoenix Palm" },
  
  { category: "EAST SERVICE ROAD", name: "Martinez Compound" },
  { category: "EAST SERVICE ROAD", name: "Sunflower Street" },
  { category: "EAST SERVICE ROAD", name: "Dahlia Street" },
  { category: "EAST SERVICE ROAD", name: "Belize Oasis Road" },
  
  { category: "ALABANG PROPER", name: "Barrio Bisaya" },
  { category: "ALABANG PROPER", name: "T. Molina Street" },
  { category: "ALABANG PROPER", name: "Montillano Street" },
  { category: "ALABANG PROPER", name: "Mendiola Street" },
  { category: "ALABANG PROPER", name: "Purok 4 Street" },
  { category: "ALABANG PROPER", name: "Wawa Street" },
  { category: "ALABANG PROPER", name: "Ilaya Street" },
  { category: "ALABANG PROPER", name: "Visayas Street" },
  { category: "ALABANG PROPER", name: "Luzon Street" },
  { category: "ALABANG PROPER", name: "UP Street" },
  { category: "ALABANG PROPER", name: "Mindanao Street" },
  
  { category: "ALABANG PROPER - L&B", name: "Main Street" },
  { category: "ALABANG PROPER - L&B", name: "1st Road" },
  { category: "ALABANG PROPER - L&B", name: "2nd Road" },
  { category: "ALABANG PROPER - L&B", name: "3rd Road" },
  { category: "ALABANG PROPER - L&B", name: "4th Road" },
  { category: "ALABANG PROPER - L&B", name: "5th Road" },
  { category: "ALABANG PROPER - L&B", name: "De Mesa Street" }
];

// Initialize Flatpickr for Birthdate
flatpickr("#birthdate", {
  dateFormat: "Y-m-d",
  maxDate: new Date().fp_incr(-6570), // 18 years ago
  minDate: new Date().fp_incr(-43800), // 120 years ago
  defaultDate: new Date().fp_incr(-7300), // 20 years ago
  yearSelectorType: "dropdown"
});

// Populate Street List with Categories
function populateStreets() {
  const streetList = document.getElementById('streetList');
  let currentCategory = '';
  let html = '';
  
  streets.forEach(street => {
    if (street.category !== currentCategory) {
      currentCategory = street.category;
      html += `<div class="font-bold text-blue-900 text-sm mt-3 mb-1 px-2">${currentCategory}</div>`;
    }
    html += `
      <div class="street-item px-4 py-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 transition-all" 
           onclick="selectStreet('${street.name}')">
        <p class="text-gray-700">${street.name}</p>
      </div>
    `;
  });
  
  streetList.innerHTML = html;
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
    street.name.toLowerCase().includes(searchValue) || 
    street.category.toLowerCase().includes(searchValue)
  );
  
  const streetList = document.getElementById('streetList');
  let currentCategory = '';
  let html = '';
  
  filteredStreets.forEach(street => {
    if (street.category !== currentCategory) {
      currentCategory = street.category;
      html += `<div class="font-bold text-blue-900 text-sm mt-3 mb-1 px-2">${currentCategory}</div>`;
    }
    html += `
      <div class="street-item px-4 py-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-blue-50 transition-all" 
           onclick="selectStreet('${street.name}')">
        <p class="text-gray-700">${street.name}</p>
      </div>
    `;
  });
  
  streetList.innerHTML = html || '<p class="text-gray-500 text-center py-4">No streets found</p>';
}

// Password Strength Indicator
const passwordInput = document.getElementById('password');
const strengthBar = document.getElementById('strength_bar');
const strengthText = document.getElementById('strength_text');

passwordInput.addEventListener('input', () => {
  const password = passwordInput.value;
  let strength = 0;
  let feedback = [];
  
  if (password.length >= 8) strength++;
  else feedback.push('8+ characters');
  
  if (/[A-Z]/.test(password)) strength++;
  else feedback.push('1 uppercase');
  
  if (/[a-z]/.test(password)) strength++;
  else feedback.push('1 lowercase');
  
  if (/[0-9]/.test(password)) strength++;
  else feedback.push('1 number');
  
  // Check for special characters (not allowed)
  if (/[^a-zA-Z0-9]/.test(password)) {
    strengthBar.className = 'strength-bar';
    strengthText.textContent = 'Special characters not allowed';
    strengthText.className = 'text-xs mt-1 text-red-600';
    return;
  }
  
  if (strength === 0) {
    strengthBar.className = 'strength-bar';
    strengthText.textContent = '';
  } else if (strength <= 2) {
    strengthBar.className = 'strength-bar strength-weak';
    strengthText.textContent = 'Weak - Need: ' + feedback.join(', ');
    strengthText.className = 'text-xs mt-1 text-red-600';
  } else if (strength === 3) {
    strengthBar.className = 'strength-bar strength-medium';
    strengthText.textContent = 'Medium - Need: ' + feedback.join(', ');
    strengthText.className = 'text-xs mt-1 text-yellow-600';
  } else {
    strengthBar.className = 'strength-bar strength-strong';
    strengthText.textContent = 'Strong password!';
    strengthText.className = 'text-xs mt-1 text-green-600';
  }
});

// Toggle Password Visibility (Single toggle for password field)
const togglePassword = document.getElementById('toggle_password');
const passwordIcon = document.getElementById('password_icon');

togglePassword.addEventListener('click', () => {
  const type = passwordInput.type === 'password' ? 'text' : 'password';
  passwordInput.type = type;
  
  if (type === 'text') {
    passwordIcon.innerHTML = `
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
    `;
  } else {
    passwordIcon.innerHTML = `
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
    `;
  }
});

// Name Validation - No special characters
function validateNameField(input) {
  const value = input.value;
  const regex = /^[a-zA-ZÀ-ÿ\s']+$/;
  
  if (value && !regex.test(value)) {
    input.classList.add('border-red-500');
    input.classList.remove('border-gray-300');
    return false;
  } else {
    input.classList.remove('border-red-500');
    input.classList.add('border-gray-300');
    return true;
  }
}

document.getElementById('first_name').addEventListener('input', function() {
  validateNameField(this);
});

document.getElementById('middle_name').addEventListener('input', function() {
  validateNameField(this);
});

document.getElementById('last_name').addEventListener('input', function() {
  validateNameField(this);
});

// Phone Number Validation
document.getElementById('phone_number').addEventListener('input', function() {
  let value = this.value.replace(/\D/g, ''); // Remove non-digits
  
  if (value.startsWith('63')) {
    value = '0' + value.substring(2);
  }
  
  if (value.length > 11) {
    value = value.substring(0, 11);
  }
  
  this.value = value;
});

// Form Submission with Loading Animation
const registerForm = document.getElementById('registerForm');
const registerBtn = document.getElementById('register_btn');
const btnText = document.getElementById('btn_text');
const btnSpinner = document.getElementById('btn_spinner');

registerForm.addEventListener('submit', function(e) {
  // Check if street is selected
  const streetValue = document.getElementById('street').value;
  if (!streetValue) {
    e.preventDefault();
    alert('Please select a street from the list.');
    return;
  }
  
  // Check password match
  const password = document.getElementById('password').value;
  const confirmPassword = document.getElementById('confirm_password').value;
  
  if (password !== confirmPassword) {
    e.preventDefault();
    document.getElementById('form_error').textContent = 'Passwords do not match';
    document.getElementById('form_error').classList.remove('hidden');
    return;
  }
  
  // Show loading animation
  btnText.textContent = 'Registering...';
  btnSpinner.classList.remove('hidden');
  registerBtn.disabled = true;
});

// OTP Modal Functions
let timeLeft = 120;
let resendTimeLeft = 60;
let timerInterval;
let resendInterval;

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

function verifyOTP() {
  const otpInput = document.getElementById('otp_input').value;
  const errorDiv = document.getElementById('otp_error');
  
  if (otpInput.length !== 6) {
    errorDiv.textContent = 'Please enter a 6-digit code.';
    errorDiv.classList.remove('hidden');
    return;
  }
  
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

function cancelOTP() {
  if (!confirm('Are you sure you want to cancel registration? Your data will be deleted.')) {
    return;
  }
  
  fetch('cancel_otp.php', {
    method: 'POST'
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      clearInterval(timerInterval);
      clearInterval(resendInterval);
      window.location.href = 'register.php';
    } else {
      alert('Error cancelling registration: ' + data.message);
    }
  })
  .catch(err => {
    alert('Error cancelling registration. Please try again.');
  });
}

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

function goToLogin() {
  window.location.href = 'login.php?verified=1';
}

// Auto-start timers if OTP modal is visible
if (document.getElementById('otpModal')) {
  startTimer();
  startResendTimer();
  
  document.getElementById('otp_input').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') verifyOTP();
  });
}