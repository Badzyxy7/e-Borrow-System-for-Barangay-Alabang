// ============================================
// EQUIPMENT BROWSE JAVASCRIPT - FIXED VERSION
// ============================================

// SEARCH & FILTER FUNCTIONS

let searchTimeout;
let currentPage = 1;
let activeRequest = null; // Track active AJAX request

// GROUP REQUEST VARIABLES
let isSelectMode = false;
let selectedItems = new Map();


/**
 * Performs search with filters and pagination
 * @param {number} page - Page number to load
 */

function performSearch(page = 1) {
  const searchInput = document.getElementById('searchInput');
  const statusFilter = document.getElementById('statusFilter');
  const searchValue = searchInput.value;
  const statusValue = statusFilter.value;
  const resultsContainer = document.getElementById('resultsContainer');
  
  if (!resultsContainer) {
    console.error('Results container not found');
    return;
  }
  
  // Cancel previous request if still pending
  if (activeRequest) {
    activeRequest.abort();
  }
  
  currentPage = page;
  
  // Update URL without page reload
  const url = new URL(window.location);
  url.searchParams.set('search', searchValue);
  url.searchParams.set('status', statusValue);
  url.searchParams.set('page', page);
  window.history.pushState({}, '', url);
  
  // Show loading state
  resultsContainer.style.opacity = '0.5';
  
  // Create AbortController for this request
  const controller = new AbortController();
  activeRequest = controller;
  
  // Fetch results via AJAX
  fetch(`${window.location.pathname}?search=${encodeURIComponent(searchValue)}&status=${encodeURIComponent(statusValue)}&page=${page}&ajax=1`, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    },
    signal: controller.signal
  })
  .then(response => response.text())
  .then(html => {
    // Clear and replace content completely
    resultsContainer.innerHTML = '';
    resultsContainer.innerHTML = html;
    resultsContainer.style.opacity = '1';
    
    // Re-initialize Feather icons for new content
    if (typeof feather !== 'undefined') {
      feather.replace();
    }
    
    // Only scroll when changing pages, not during search
    if (page > 1) {
      resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
    activeRequest = null;
  })
  .catch(error => {
    if (error.name !== 'AbortError') {
      console.error('Search error:', error);
      resultsContainer.style.opacity = '1';
    }
    activeRequest = null;
  });
}

/**
 * Navigate to specific page
 * @param {number} page - Page number
 */
function goToPage(page) {
  performSearch(page);
}

/**
 * Initialize search and filter event listeners
 */
function initializeSearchFilters() {
  const searchInput = document.getElementById('searchInput');
  const statusFilter = document.getElementById('statusFilter');
  const searchForm = document.getElementById('searchForm');
  
  if (!searchInput || !statusFilter || !searchForm) return;
  
  // Debounced search on input (waits 300ms after user stops typing)
  searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => performSearch(1), 300);
  });

  // Immediate search on status change
  statusFilter.addEventListener('change', () => performSearch(1));

  // Prevent form submission
  searchForm.addEventListener('submit', function(e) {
    e.preventDefault();
    performSearch(1);
  });
}

// ============================================
// VALIDATION MODAL FUNCTIONS (REUSABLE)
// ============================================

/**
 * Show validation error modal with custom message
 * @param {string} title - Modal title
 * @param {string} message - Error message to display
 * @param {string} iconType - Icon type: 'error', 'warning', 'info'
 */
function showValidationModal(title, message, iconType = 'warning') {
  const modal = document.getElementById('validationModal');
  const modalTitle = document.getElementById('validationModalTitle');
  const modalMessage = document.getElementById('validationModalMessage');
  const modalIcon = document.getElementById('validationModalIcon');
  const modalButton = document.getElementById('validationModalButton');
  
  if (!modal) {
    console.error('Validation modal not found!');
    return;
  }
  
  // Set content
  modalTitle.textContent = title;
  modalMessage.textContent = message;
  
  // Set icon and colors based on type
  if (iconType === 'error') {
    modalIcon.innerHTML = `
      <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    `;
    modalIcon.className = 'w-20 h-20 bg-gradient-to-br from-red-400 to-red-600 rounded-full flex items-center justify-center mx-auto mb-5 shadow-lg';
    modalButton.className = 'w-full bg-gradient-to-r from-red-500 to-red-600 text-white py-3.5 text-base rounded-xl hover:from-red-600 hover:to-red-700 transition font-semibold shadow-sm';
  } else if (iconType === 'warning') {
    modalIcon.innerHTML = `
      <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
      </svg>
    `;
    modalIcon.className = 'w-20 h-20 bg-gradient-to-br from-orange-400 to-orange-600 rounded-full flex items-center justify-center mx-auto mb-5 shadow-lg';
    modalButton.className = 'w-full bg-gradient-to-r from-orange-500 to-orange-600 text-white py-3.5 text-base rounded-xl hover:from-orange-600 hover:to-orange-700 transition font-semibold shadow-sm';
  } else {
    modalIcon.innerHTML = `
      <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    `;
    modalIcon.className = 'w-20 h-20 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-5 shadow-lg';
    modalButton.className = 'w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3.5 text-base rounded-xl hover:from-blue-600 hover:to-blue-700 transition font-semibold shadow-sm';
  }
  
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

/**
 * Close validation modal
 */
function closeValidationModal() {
  const modal = document.getElementById('validationModal');
  if (!modal) return;
  
  modal.style.display = 'none';
  document.body.style.overflow = '';
}

// ============================================
// PROFILE COMPLETENESS CHECK
// ============================================

/**
 * Check if user profile is complete before opening request modal
 * @param {number} id - Equipment ID
 */
function checkProfileAndOpenModal(id) {
  console.log('Checking profile completeness for equipment ID:', id);
  
  fetch('check_profile_completeness.php')
    .then(response => response.json())
    .then(data => {
      console.log('Profile check response:', data);
      
      if (data.success && data.complete) {
        // Profile is complete, open request modal
        console.log('Profile complete, opening modal');
        openRequestModal(id);
      } else {
        // Profile incomplete, show warning modal
        console.log('Profile incomplete, showing warning');
        showProfileIncompleteModal(data.missing_fields || []);
      }
    })
    .catch(error => {
      console.error('Error checking profile:', error);
      // On error, still allow opening modal (fail-safe)
      console.log('Opening modal anyway due to error');
      openRequestModal(id);
    });
}

/**
 * Show profile incomplete modal with missing fields
 * @param {array} missingFields - Array of missing field names
 */
function showProfileIncompleteModal(missingFields) {
  console.log('showProfileIncompleteModal called with:', missingFields);
  
  const modal = document.getElementById('profileIncompleteModal');
  const fieldsList = document.getElementById('missingFieldsList');
  
  if (!modal) {
    console.error('Profile incomplete modal not found!');
    return;
  }
  
  if (!fieldsList) {
    console.error('Missing fields list element not found!');
    return;
  }
  
  // Display missing fields
  if (missingFields.length > 0) {
    fieldsList.textContent = missingFields.join(', ');
  } else {
    fieldsList.textContent = 'Please complete all required fields';
  }
  
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
  console.log('Profile incomplete modal displayed');
}

/**
 * Close profile incomplete modal
 */
function closeProfileIncompleteModal() {
  const modal = document.getElementById('profileIncompleteModal');
  if (!modal) return;
  
  modal.style.display = 'none';
  document.body.style.overflow = '';
}

/**
 * Redirect user to edit profile page
 */
function redirectToProfile() {
  window.location.href = 'edit_profile.php';
}

// ============================================
// IMAGE MODAL FUNCTIONS
// ============================================

/**
 * Opens image modal to view equipment image
 * @param {string} src - Image source filename
 */
function openImageModal(src) {
  const modal = document.getElementById('imageModal');
  const modalImg = document.getElementById('modalImage');
  if (!modal || !modalImg) return;
  
  modalImg.src = '../photos/' + src;
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

/**
 * Closes image modal
 */
function closeImageModal() {
  const modal = document.getElementById('imageModal');
  if (!modal) return;
  
  modal.style.display = 'none';
  document.body.style.overflow = '';
}

// ============================================
// REQUEST MODAL FUNCTIONS
// ============================================

/**
 * Opens request modal for equipment (called from card button)
 * @param {number} id - Equipment ID
 */
function openRequestModal(id) {
  console.log('openRequestModal called for ID:', id);
  
  const modal = document.getElementById('requestModal' + id);
  if (!modal) {
    console.error('Request modal not found for ID:', id);
    return;
  }
  
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
  
  // Initialize date pickers when modal opens
  initializeDatePickers(id);
  console.log('Request modal opened');
}

/**
 * Closes request modal
 * @param {number} id - Equipment ID
 */
function closeRequestModal(id) {
  const modal = document.getElementById('requestModal' + id);
  if (!modal) return;
  
  modal.style.display = 'none';
  document.body.style.overflow = '';
}

/**
 * Initialize Flatpickr date pickers for borrow/return dates
 * @param {number} id - Equipment ID
 */
function initializeDatePickers(id) {
  const borrowInput = document.getElementById('borrow_datetime_' + id);
  const returnInput = document.getElementById('return_datetime_' + id);
  
  if (!borrowInput || !returnInput) return;
  
  // Check if already initialized
  if (borrowInput._flatpickr) {
    return; // Already initialized
  }
  
  const now = new Date();
  
  flatpickr("#borrow_datetime_" + id, {
    enableTime: true,
    dateFormat: "Y-m-d H:i",
    time_24hr: true,
    minDate: now,
    minuteIncrement: 30,
    altInput: true,
    altFormat: "F j, Y at h:i K",
    onChange: function(selectedDates, dateStr, instance) {
      checkAvailabilityRealtime(id);
    }
  });
  
  flatpickr("#return_datetime_" + id, {
    enableTime: true,
    dateFormat: "Y-m-d H:i",
    time_24hr: true,
    minDate: now,
    minuteIncrement: 30,
    altInput: true,
    altFormat: "F j, Y at h:i K",
    onChange: function(selectedDates, dateStr, instance) {
      checkAvailabilityRealtime(id);
    }
  });
}

/**
 * Toggles submit button based on agreement checkbox
 * @param {number} id - Equipment ID
 */
function toggleSubmitButton(id) {
  const checkbox = document.getElementById('agreeCheckbox' + id);
  const button = document.getElementById('submitBtn' + id);

  if (!checkbox || !button) return;

  if (checkbox.checked) {
    button.disabled = false;
    button.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
    button.classList.add('bg-gradient-to-r', 'from-blue-600', 'to-blue-700', 'text-white', 'hover:from-blue-700', 'hover:to-blue-800', 'shadow-sm');
  } else {
    button.disabled = true;
    button.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
    button.classList.remove('bg-gradient-to-r', 'from-blue-600', 'to-blue-700', 'text-white', 'hover:from-blue-700', 'hover:to-blue-800', 'shadow-sm');
  }
}

// ============================================
// PURPOSE & DEATH CERTIFICATE FUNCTIONS
// ============================================

// ============================================
// UPDATED: toggleDeathCertificateField with Priority Notification
// Replace your existing toggleDeathCertificateField function with this
// ============================================

/**
 * Toggle death certificate field AND priority notification based on purpose selection
 * @param {number} id - Equipment ID
 */
function toggleDeathCertificateField(id) {
  const purposeSelect = document.getElementById('purpose_' + id);
  const deathCertContainer = document.getElementById('death_certificate_container_' + id);
  
  if (!purposeSelect || !deathCertContainer) return;
  
  const selectedPurpose = purposeSelect.value;
  
  if (selectedPurpose === 'Funeral/Lamay') {
    // Show death certificate field
    deathCertContainer.style.display = 'block';
    
    // Add smooth slide-in animation
    deathCertContainer.style.opacity = '0';
    deathCertContainer.style.transform = 'translateY(-10px)';
    
    setTimeout(() => {
      deathCertContainer.style.transition = 'all 0.3s ease-out';
      deathCertContainer.style.opacity = '1';
      deathCertContainer.style.transform = 'translateY(0)';
    }, 10);
    
  } else {
    // Hide death certificate field
    deathCertContainer.style.display = 'none';
    
    // Clear the uploaded file if purpose changes
    clearDeathCertificate(id);
  }
}

/**
 * Handle death certificate file upload with priority notification
 * @param {number} id - Equipment ID
 * @param {HTMLInputElement} input - File input element
 */
function handleDeathCertificateUpload(id, input) {
  const file = input.files[0];
  
  if (!file) return;
  
  // Validate file size (20MB max)
  const maxSize = 20 * 1024 * 1024; // 20MB
  if (file.size > maxSize) {
    showValidationModal(
      'File Too Large',
      'File size exceeds 20MB limit. Please choose a smaller file.',
      'error'
    );
    input.value = '';
    return;
  }
  
  // Validate file type
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
  if (!allowedTypes.includes(file.type)) {
    showValidationModal(
      'Invalid File Type',
      'Invalid file type. Only JPG, JPEG, and PNG files are allowed.',
      'error'
    );
    input.value = '';
    return;
  }
  
  // Show uploading indicator
  const statusDiv = document.getElementById('death_certificate_status_' + id);
  if (statusDiv) {
    statusDiv.classList.remove('hidden');
    statusDiv.innerHTML = `
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 flex items-center gap-3">
        <svg class="animate-spin w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-sm text-blue-700 font-medium">Uploading certificate...</span>
      </div>
    `;
  }
  
  // Create FormData and upload
  const formData = new FormData();
  formData.append('death_certificate', file);
  
  fetch('upload_death_certificate.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Store filename in hidden input
      const filenameInput = document.getElementById('death_certificate_filename_' + id);
      if (filenameInput) {
        filenameInput.value = data.filename;
      }
      
      // Show success message with priority notification
      if (statusDiv) {
        statusDiv.innerHTML = `
          <div class="bg-green-50 border border-green-200 rounded-xl p-3 flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <div class="flex-1">
              <span class="text-sm text-green-700 font-medium">Certificate uploaded successfully</span>
              <p class="text-xs text-green-600 mt-1">✓ Your request will be prioritized</p>
            </div>
          </div>
        `;
      }
      
      // Add a subtle success animation
      if (statusDiv) {
        statusDiv.style.animation = 'successFadeIn 0.5s ease-out';
      }
      
      console.log('✓ Death certificate uploaded - Priority flag will be set');
      
    } else {
      // Show error message
      showValidationModal(
        'Upload Failed',
        'Upload failed: ' + data.message,
        'error'
      );
      if (statusDiv) {
        statusDiv.classList.add('hidden');
      }
      input.value = '';
    }
  })
  .catch(error => {
    console.error('Upload error:', error);
    showValidationModal(
      'Upload Error',
      'An error occurred during upload. Please try again.',
      'error'
    );
    if (statusDiv) {
      statusDiv.classList.add('hidden');
    }
    input.value = '';
  });
}

/**
 * Clear death certificate upload
 * @param {number} id - Equipment ID
 */
function clearDeathCertificate(id) {
  const filenameInput = document.getElementById('death_certificate_filename_' + id);
  const fileInput = document.getElementById('death_certificate_file_' + id);
  const cameraInput = document.getElementById('death_certificate_camera_' + id);
  const statusDiv = document.getElementById('death_certificate_status_' + id);
  
  if (filenameInput) filenameInput.value = '';
  if (fileInput) fileInput.value = '';
  if (cameraInput) cameraInput.value = '';
  if (statusDiv) {
    statusDiv.classList.add('hidden');
    statusDiv.innerHTML = '';
  }
  
  console.log('Death certificate cleared - Priority flag will not be set');
}

// ============================================
// ADD THIS CSS FOR ANIMATIONS (if not already present)
// ============================================
const style = document.createElement('style');
style.textContent = `
  @keyframes successFadeIn {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  @keyframes priorityGlow {
    0%, 100% { 
      box-shadow: 0 0 0 0 rgba(147, 51, 234, 0.4);
    }
    50% { 
      box-shadow: 0 0 0 10px rgba(147, 51, 234, 0);
    }
  }
  
  .priority-notification {
    animation: priorityGlow 2s ease-out;
  }
`;
document.head.appendChild(style);

// ============================================
// AVAILABILITY CHECK FUNCTIONS
// ============================================

/**
 * Checks equipment availability in real-time
 * @param {number} id - Equipment ID
 */
function checkAvailabilityRealtime(id) {
  const borrowDate = document.getElementById('borrow_datetime_' + id).value;
  const returnDate = document.getElementById('return_datetime_' + id).value;
  const qty = document.getElementById('quantity_' + id).value;
  const indicator = document.getElementById('availability_indicator_' + id);
  
  if (!indicator) return;
  
  if (borrowDate && returnDate && qty) {
    indicator.classList.remove('hidden');
    indicator.innerHTML = '<span class="font-semibold">Checking availability...</span>';
    indicator.className = 'mt-4 p-3 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg text-sm text-blue-700';
    
    fetch(`check_availability.php?equipment_id=${id}&borrow=${encodeURIComponent(borrowDate)}&return=${encodeURIComponent(returnDate)}&qty=${qty}`)
      .then(res => res.json())
      .then(data => {
        if (data.available) {
          indicator.innerHTML = `<span class="font-semibold">✓ Available!</span> ${data.available_qty} unit(s) available for selected dates.`;
          indicator.className = 'mt-4 p-3 bg-green-50 border-l-4 border-green-500 rounded-r-lg text-sm text-green-700';
        } else {
          indicator.innerHTML = `<span class="font-semibold">⚠ Limited availability!</span> Only ${data.available_qty} unit(s) available. Requested: ${data.requested_qty}`;
          indicator.className = 'mt-4 p-3 bg-orange-50 border-l-4 border-orange-500 rounded-r-lg text-sm text-orange-700';
        }
      })
      .catch(err => {
        console.error('Error checking availability:', err);
        indicator.classList.add('hidden');
      });
  }
}

// ============================================
// CONFIRMATION MODAL FUNCTIONS
// ============================================

/**
 * Opens confirmation modal before submitting request
 * @param {number} id - Equipment ID
 */
function openConfirmModal(id) {
  const quantity = document.getElementById('quantity_' + id).value;
  const borrowDateStr = document.getElementById('borrow_datetime_' + id).value;
  const returnDateStr = document.getElementById('return_datetime_' + id).value;
  const description = document.getElementById('description_' + id).value;
  const purpose = document.getElementById('purpose_' + id).value;
  
  // Validate quantity
  if (!quantity || quantity <= 0 || !Number.isInteger(Number(quantity))) {
    showValidationModal(
      'Invalid Quantity',
      'Please enter a valid quantity (must be a positive whole number).',
      'warning'
    );
    return;
  }

  // Validate dates are selected
  if (!borrowDateStr || !returnDateStr) {
    showValidationModal(
      'Missing Dates',
      'Please select both borrow and return dates.',
      'warning'
    );
    return;
  }
  
  // Validate purpose
  if (!purpose) {
    showValidationModal(
      'Purpose Required',
      'Please select a purpose for borrowing.',
      'warning'
    );
    return;
  }

  // Validate death certificate if funeral is selected
  if (purpose === 'Funeral/Lamay') {
    const deathCertFilename = document.getElementById('death_certificate_filename_' + id).value;
    if (!deathCertFilename) {
      showValidationModal(
        'Certificate Required',
        'Please upload the death certificate for funeral/lamay purposes.',
        'warning'
      );
      return;
    }
  }

  const borrowDate = new Date(borrowDateStr);
  const returnDate = new Date(returnDateStr);

  if (returnDate <= borrowDate) {
    const errorModal = document.getElementById('errorModal');
    if (errorModal) {
      errorModal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    }
    return;
  }

  const name = document.getElementById('equipment_name_' + id).value;
  const desc = document.getElementById('equipment_desc_' + id).value;
  const image = document.getElementById('equipment_image_' + id).value;

  document.getElementById('confirm_name_' + id).textContent = name;
  document.getElementById('confirm_desc_' + id).textContent = desc;
  document.getElementById('confirm_image_' + id).src = '../photos/' + image;
  document.getElementById('confirm_quantity_' + id).textContent = quantity;
  document.getElementById('confirm_purpose_' + id).textContent = purpose;

  const borrowFormatted = borrowDate.toLocaleString('en-US', {
    month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
  });
  const returnFormatted = returnDate.toLocaleString('en-US', {
    month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
  });

  document.getElementById('confirm_borrow_' + id).textContent = borrowFormatted;
  document.getElementById('confirm_return_' + id).textContent = returnFormatted;

  if (description) {
    document.getElementById('confirm_description_' + id).textContent = description;
    document.getElementById('confirm_description_container_' + id).style.display = 'block';
  } else {
    document.getElementById('confirm_description_container_' + id).style.display = 'none';
  }

  const confirmModal = document.getElementById('confirmModal' + id);
  if (confirmModal) {
    confirmModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
}

/**
 * Closes confirmation modal
 * @param {number} id - Equipment ID
 */
function closeConfirmModal(id) {
  const modal = document.getElementById('confirmModal' + id);
  if (!modal) return;
  
  modal.style.display = 'none';
  document.body.style.overflow = '';
}

// ============================================
// FORM SUBMISSION FUNCTIONS
// ============================================

/**
 * Submits borrow request form
 * @param {number} id - Equipment ID
 */
function submitRequest(id) {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '';

  const purposeValue = document.getElementById('purpose_' + id).value;
  const deathCertValue = document.getElementById('death_certificate_filename_' + id).value;

  // DEBUG: Log values before submission
  console.log('=== FORM SUBMISSION DEBUG ===');
  console.log('Equipment ID:', id);
  console.log('Purpose:', purposeValue);
  console.log('Death Certificate:', deathCertValue);
  console.log('Purpose Element:', document.getElementById('purpose_' + id));
  console.log('Death Cert Element:', document.getElementById('death_certificate_filename_' + id));

  const fields = {
    'equipment_id': document.getElementById('equipment_id_' + id).value,
    'quantity': document.getElementById('quantity_' + id).value,
    'borrow_datetime': document.getElementById('borrow_datetime_' + id).value,
    'return_datetime': document.getElementById('return_datetime_' + id).value,
    'description': document.getElementById('description_' + id).value,
    'purpose': purposeValue,
    'death_certificate': deathCertValue,
    'request': '1'
  };

  console.log('All fields:', fields);

  for (const [name, value] of Object.entries(fields)) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    form.appendChild(input);
  }

  document.body.appendChild(form);
  form.submit();
}
// ============================================
// ERROR & SUCCESS MODAL FUNCTIONS
// ============================================

/**
 * Closes error modal
 */
function closeErrorModal() {
  const modal = document.getElementById('errorModal');
  if (!modal) return;
  
  modal.style.display = 'none';
  document.body.style.overflow = '';
}

/**
 * Closes success modal
 */
function closeSuccessModal() {
  const modal = document.getElementById('successModal');
  if (!modal) return;
  
  modal.style.display = 'none';
  document.body.style.overflow = '';
}
// MODAL BACKDROP CLICK HANDLERS
/**
 * Close modals when clicking outside
 */
function initializeModalBackdropHandlers() {
  document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal-backdrop')) {
      event.target.style.display = 'none';
      document.body.style.overflow = '';
    }
  });

  // Prevent modal content clicks from closing modal
  document.querySelectorAll('.modal-backdrop > div').forEach(function(modalContent) {
    modalContent.addEventListener('click', function(event) {
      event.stopPropagation();
    });
  });
}
// INITIALIZATION
/**
 * Initialize all event listeners and components
 */
function initializeEquipmentBrowse() {
  console.log('Initializing Equipment Browse...');
  
  // Initialize Feather icons
  if (typeof feather !== 'undefined') {
    feather.replace();
  }
  
  // Initialize search filters
  initializeSearchFilters();
  
  // Initialize modal backdrop handlers
  initializeModalBackdropHandlers();
  
  // Set current page from PHP
  const pageParam = new URLSearchParams(window.location.search).get('page');
  currentPage = pageParam ? parseInt(pageParam) : 1;
  
  console.log('Equipment Browse initialized successfully');
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeEquipmentBrowse);
} else {
  initializeEquipmentBrowse();
}

// ============================================
// GROUP REQUEST FUNCTIONALITY
// ============================================

// IMPORTANT: Add these lines at the TOP of your equipment_browse.js file
// (right after the existing let declarations)


/**
 * Toggle select mode on/off
 */
function toggleSelectMode() {
  isSelectMode = !isSelectMode;
  const selectBtn = document.getElementById('selectModeBtn');
  const selectIcon = document.getElementById('selectModeIcon');
  const selectTooltip = document.getElementById('selectModeTooltip');
  const floatingCard = document.getElementById('floatingActionCard');
  const cards = document.querySelectorAll('.equipment-card');
  
  if (isSelectMode) {
    // Enter select mode
    selectIcon.innerHTML = `
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    `;
    selectTooltip.textContent = 'Cancel Selection';
    selectBtn.classList.remove('from-blue-900', 'to-blue-700');
    selectBtn.classList.add('from-gray-600', 'to-gray-700');
    
    // Show checkboxes on available equipment cards only
    cards.forEach(card => {
      const checkboxContainer = card.querySelector('.equipment-checkbox-container');
      const requestBtn = card.querySelector('.individual-request-btn');
      const availableBadge = card.querySelector('.equipment-badge-available');
      const isAvailable = requestBtn && !requestBtn.disabled;
      
      if (checkboxContainer && isAvailable) {
        // Only show checkbox for available items
        checkboxContainer.classList.remove('hidden');
        
        // Hide available badge when in select mode
        if (availableBadge) {
          availableBadge.classList.add('hidden');
        }
      }
      
      // Hide individual request button in select mode
      if (requestBtn) {
        requestBtn.style.display = 'none';
      }
    });
    
    // Show floating action card if items selected
    if (selectedItems.size > 0) {
      floatingCard.classList.remove('hidden');
    }
  } else {
    // Exit select mode
    selectIcon.innerHTML = `
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
    `;
    selectTooltip.textContent = 'Select Equipment';
    selectBtn.classList.add('from-blue-900', 'to-blue-700');
    selectBtn.classList.remove('from-gray-600', 'to-gray-700');
    
    // Hide checkboxes and restore buttons/badges
    cards.forEach(card => {
      const checkboxContainer = card.querySelector('.equipment-checkbox-container');
      const checkbox = card.querySelector('.equipment-checkbox');
      const requestBtn = card.querySelector('.individual-request-btn');
      const availableBadge = card.querySelector('.equipment-badge-available');
      
      if (checkboxContainer) {
        checkboxContainer.classList.add('hidden');
      }
      
      if (checkbox) {
        checkbox.checked = false;
      }
      
      if (requestBtn) {
        requestBtn.style.display = 'block';
      }
      
      // Show available badge again
      if (availableBadge) {
        availableBadge.classList.remove('hidden');
      }
      
      // Remove selection styling
      card.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50');
    });
    
    // Clear selections and hide floating card
    selectedItems.clear();
    floatingCard.classList.add('hidden');
    updateFloatingCard();
  }
}

/**
 * Handle equipment selection
 * @param {number} equipmentId - Equipment ID
 * @param {HTMLInputElement} checkbox - Checkbox element
 */
function toggleEquipmentSelection(equipmentId, checkbox) {
  const card = checkbox.closest('.equipment-card');
  
  if (checkbox.checked) {
    // Add to selection
    const equipmentData = {
      id: equipmentId,
      name: card.querySelector('[data-equipment-name]').textContent,
      description: card.querySelector('[data-equipment-desc]').textContent,
      image: card.querySelector('[data-equipment-image]').src,
      total_qty: parseInt(card.querySelector('[data-equipment-qty]').textContent),
      quantity: 1 // Default quantity
    };
    
    selectedItems.set(equipmentId, equipmentData);
    card.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50');
  } else {
    // Remove from selection
    selectedItems.delete(equipmentId);
    card.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50');
  }
  
  updateFloatingCard();
}

/**
 * Update floating action card
 */
function updateFloatingCard() {
  const floatingCard = document.getElementById('floatingActionCard');
  const countSpan = document.getElementById('selectedCount');
  
  if (selectedItems.size > 0) {
    floatingCard.classList.remove('hidden');
    countSpan.textContent = selectedItems.size;
  } else {
    floatingCard.classList.add('hidden');
  }
}

/**
 * Clear all selections
 */
function clearAllSelections() {
  selectedItems.clear();
  
  document.querySelectorAll('.equipment-checkbox').forEach(checkbox => {
    checkbox.checked = false;
    const card = checkbox.closest('.equipment-card');
    card.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50');
  });
  
  updateFloatingCard();
}

/**
 * Open quantity confirmation modal
 */
function openQuantityModal() {
  if (selectedItems.size === 0) {
    showValidationModal('No Items Selected', 'Please select at least one equipment item.', 'warning');
    return;
  }
  
  const modal = document.getElementById('quantityConfirmModal');
  const itemsContainer = document.getElementById('quantityModalItems');
  
  // Clear previous content
  itemsContainer.innerHTML = '';
  
  // Populate items
  selectedItems.forEach((item, id) => {
    const itemHtml = `
      <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl">
        <img src="${item.image}" class="w-20 h-20 object-cover rounded-lg flex-shrink-0">
        <div class="flex-1 min-w-0">
          <h4 class="font-bold text-gray-900 mb-1">${item.name}</h4>
          <p class="text-sm text-gray-600 mb-2">Available: ${item.total_qty}</p>
          <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-700">Quantity:</label>
            <input type="number" 
                   id="qty_${id}" 
                   value="${item.quantity}"
                   min="1" 
                   max="${item.total_qty}"
                   onchange="updateItemQuantity(${id}, this.value)"
                   class="w-20 px-3 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>
        </div>
      </div>
    `;
    itemsContainer.insertAdjacentHTML('beforeend', itemHtml);
  });
  
  modal.classList.remove('hidden');
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

/**
 * Close quantity modal
 */
function closeQuantityModal() {
  const modal = document.getElementById('quantityConfirmModal');
  modal.classList.add('hidden');
  modal.style.display = 'none';
  document.body.style.overflow = '';
}

/**
 * Update item quantity
 * @param {number} equipmentId - Equipment ID
 * @param {number} quantity - New quantity
 */
function updateItemQuantity(equipmentId, quantity) {
  const item = selectedItems.get(equipmentId);
  if (item) {
    item.quantity = parseInt(quantity);
  }
}

/**
 * Proceed to date selection
 */
function proceedToDateSelection() {
  // Validate all quantities
  let isValid = true;
  selectedItems.forEach((item, id) => {
    const qtyInput = document.getElementById(`qty_${id}`);
    if (!qtyInput || qtyInput.value <= 0 || qtyInput.value > item.total_qty) {
      isValid = false;
    }
  });
  
  if (!isValid) {
    showValidationModal('Invalid Quantity', 'Please enter valid quantities for all items.', 'warning');
    return;
  }
  
  // Close quantity modal
  closeQuantityModal();
  
  // Open date selection modal
  openGroupRequestModal();
}

/**
 * Open group request modal with date selection
 */
function openGroupRequestModal() {
  const modal = document.getElementById('groupRequestModal');
  const reviewContainer = document.getElementById('groupRequestReview');
  
  // Clear and populate review
  reviewContainer.innerHTML = '';
  
  selectedItems.forEach((item, id) => {
    const itemHtml = `
      <div class="flex items-center gap-4 p-4 bg-white rounded-xl border-2 border-gray-100">
        <img src="${item.image}" class="w-16 h-16 object-cover rounded-lg">
        <div class="flex-1">
          <h4 class="font-bold text-gray-900">${item.name}</h4>
          <p class="text-sm text-gray-600">Quantity: ${item.quantity}</p>
        </div>
      </div>
    `;
    reviewContainer.insertAdjacentHTML('beforeend', itemHtml);
  });
  
  // Initialize date pickers
  initializeGroupDatePickers();
  
  modal.classList.remove('hidden');
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

/**
 * Close group request modal
 */
function closeGroupRequestModal() {
  const modal = document.getElementById('groupRequestModal');
  modal.classList.add('hidden');
  modal.style.display = 'none';
  document.body.style.overflow = '';
}

/**
 * Initialize date pickers for group request
 */
function initializeGroupDatePickers() {
  const borrowInput = document.getElementById('group_borrow_datetime');
  const returnInput = document.getElementById('group_return_datetime');
  
  if (!borrowInput || !returnInput) return;
  
  // Destroy existing instances if any
  if (borrowInput._flatpickr) borrowInput._flatpickr.destroy();
  if (returnInput._flatpickr) returnInput._flatpickr.destroy();
  
  const now = new Date();
  
  flatpickr("#group_borrow_datetime", {
    enableTime: true,
    dateFormat: "Y-m-d H:i",
    time_24hr: true,
    minDate: now,
    minuteIncrement: 30,
    altInput: true,
    altFormat: "F j, Y at h:i K"
  });
  
  flatpickr("#group_return_datetime", {
    enableTime: true,
    dateFormat: "Y-m-d H:i",
    time_24hr: true,
    minDate: now,
    minuteIncrement: 30,
    altInput: true,
    altFormat: "F j, Y at h:i K"
  });
}

/**
 * Submit group request
 */
function submitGroupRequest() {
  const purpose = document.getElementById('group_purpose').value;
  const borrowDate = document.getElementById('group_borrow_datetime').value;
  const returnDate = document.getElementById('group_return_datetime').value;
  const description = document.getElementById('group_description').value;
  const deathCertificate = document.getElementById('group_death_certificate_filename').value;
  
  // Validation
  if (!purpose) {
    showValidationModal('Purpose Required', 'Please select a purpose for borrowing.', 'warning');
    return;
  }
  
  // Validate death certificate for funeral
  if (purpose === 'Funeral/Lamay' && !deathCertificate) {
    showValidationModal('Certificate Required', 'Please upload the death certificate for funeral/lamay purposes.', 'warning');
    return;
  }
  
  if (!borrowDate || !returnDate) {
    showValidationModal('Dates Required', 'Please select both borrow and return dates.', 'warning');
    return;
  }
  
  const borrow = new Date(borrowDate);
  const returnD = new Date(returnDate);
  
  if (returnD <= borrow) {
    showValidationModal('Invalid Dates', 'Return date must be after borrow date.', 'error');
    return;
  }
  
  // Prepare data with priority flag
  const requestData = {
    items: Array.from(selectedItems.values()),
    borrow_date: borrowDate,
    return_date: returnDate,
    purpose: purpose,
    description: description,
    death_certificate: deathCertificate,
    priority: (purpose === 'Funeral/Lamay' && deathCertificate) ? 1 : 0
  };
  
  // Submit via POST (submits to the same page)
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = ''; // Empty action submits to current page
  
  const input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'group_request_data';
  input.value = JSON.stringify(requestData);
  form.appendChild(input);
  
  document.body.appendChild(form);
  form.submit();
}

/**
 * Toggle death certificate field for group request
 */
function toggleGroupDeathCertificateField() {
  const purposeSelect = document.getElementById('group_purpose');
  const deathCertContainer = document.getElementById('group_death_certificate_container');
  
  if (!purposeSelect || !deathCertContainer) return;
  
  const selectedPurpose = purposeSelect.value;
  
  if (selectedPurpose === 'Funeral/Lamay') {
    deathCertContainer.style.display = 'block';
    
    // Add smooth slide-in animation
    deathCertContainer.style.opacity = '0';
    deathCertContainer.style.transform = 'translateY(-10px)';
    
    setTimeout(() => {
      deathCertContainer.style.transition = 'all 0.3s ease-out';
      deathCertContainer.style.opacity = '1';
      deathCertContainer.style.transform = 'translateY(0)';
    }, 10);
  } else {
    deathCertContainer.style.display = 'none';
    clearGroupDeathCertificate();
  }
}

/**
 * Handle group death certificate upload
 */
function handleGroupDeathCertificateUpload(input) {
  const file = input.files[0];
  
  if (!file) return;
  
  // Validate file size (20MB max)
  const maxSize = 20 * 1024 * 1024;
  if (file.size > maxSize) {
    showValidationModal(
      'File Too Large',
      'File size exceeds 20MB limit. Please choose a smaller file.',
      'error'
    );
    input.value = '';
    return;
  }
  
  // Validate file type
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
  if (!allowedTypes.includes(file.type)) {
    showValidationModal(
      'Invalid File Type',
      'Invalid file type. Only JPG, JPEG, and PNG files are allowed.',
      'error'
    );
    input.value = '';
    return;
  }
  
  // Show uploading indicator
  const statusDiv = document.getElementById('group_death_certificate_status');
  if (statusDiv) {
    statusDiv.classList.remove('hidden');
    statusDiv.innerHTML = `
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 flex items-center gap-3">
        <svg class="animate-spin w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-sm text-blue-700 font-medium">Uploading certificate...</span>
      </div>
    `;
  }
  
  // Create FormData and upload
  const formData = new FormData();
  formData.append('death_certificate', file);
  
  fetch('upload_death_certificate.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const filenameInput = document.getElementById('group_death_certificate_filename');
      if (filenameInput) {
        filenameInput.value = data.filename;
      }
      
      if (statusDiv) {
        statusDiv.innerHTML = `
          <div class="bg-green-50 border border-green-200 rounded-xl p-3 flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <div class="flex-1">
              <span class="text-sm text-green-700 font-medium">Certificate uploaded successfully</span>
              <p class="text-xs text-green-600 mt-1">✓ Your request will be prioritized</p>
            </div>
          </div>
        `;
      }
      
      console.log('✓ Group death certificate uploaded - Priority flag will be set');
    } else {
      showValidationModal(
        'Upload Failed',
        'Upload failed: ' + data.message,
        'error'
      );
      if (statusDiv) statusDiv.classList.add('hidden');
      input.value = '';
    }
  })
  .catch(error => {
    console.error('Upload error:', error);
    showValidationModal(
      'Upload Error',
      'An error occurred during upload. Please try again.',
      'error'
    );
    if (statusDiv) statusDiv.classList.add('hidden');
    input.value = '';
  });
}

/**
 * Clear group death certificate
 */
function clearGroupDeathCertificate() {
  const filenameInput = document.getElementById('group_death_certificate_filename');
  const fileInput = document.getElementById('group_death_certificate_file');
  const cameraInput = document.getElementById('group_death_certificate_camera');
  const statusDiv = document.getElementById('group_death_certificate_status');
  
  if (filenameInput) filenameInput.value = '';
  if (fileInput) fileInput.value = '';
  if (cameraInput) cameraInput.value = '';
  if (statusDiv) {
    statusDiv.classList.add('hidden');
    statusDiv.innerHTML = '';
  }
}

// Add to existing initializeEquipmentBrowse function
function initializeGroupRequestFeature() {
  console.log('Initializing Group Request Feature...');
  
  // Add event listener for select mode button
  const selectBtn = document.getElementById('selectModeBtn');
  if (selectBtn) {
    selectBtn.addEventListener('click', toggleSelectMode);
  }
}

// Call in main initialization
// Add this line to initializeEquipmentBrowse():
// initializeGroupRequestFeature();