// ============================================
// EQUIPMENT BROWSE JAVASCRIPT - FIXED VERSION
// ============================================

// SEARCH & FILTER FUNCTIONS
let searchTimeout;
let currentPage = 1;

/**
 * Performs search with filters and pagination
 * @param {number} page - Page number to load
 */
function performSearch(page = 1) {
  const searchInput = document.getElementById('searchInput');
  const statusFilter = document.getElementById('statusFilter');
  const searchValue = searchInput.value;
  const statusValue = statusFilter.value;
  
  currentPage = page;
  
  // Update URL without page reload
  const url = new URL(window.location);
  url.searchParams.set('search', searchValue);
  url.searchParams.set('status', statusValue);
  url.searchParams.set('page', page);
  window.history.pushState({}, '', url);
  
  // Fetch results via AJAX
  fetch(`${window.location.pathname}?search=${encodeURIComponent(searchValue)}&status=${encodeURIComponent(statusValue)}&page=${page}&ajax=1`, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.text())
  .then(html => {
    document.getElementById('resultsContainer').innerHTML = html;
    // Only scroll when changing pages, not during search
    if (page > 1) {
      document.getElementById('resultsContainer').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  })
  .catch(error => console.error('Search error:', error));
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

/**
 * Toggle death certificate field based on purpose selection
 * @param {number} id - Equipment ID
 */
function toggleDeathCertificateField(id) {
  const purposeSelect = document.getElementById('purpose_' + id);
  const deathCertContainer = document.getElementById('death_certificate_container_' + id);
  
  if (!purposeSelect || !deathCertContainer) return;
  
  const selectedPurpose = purposeSelect.value;
  
  if (selectedPurpose === 'Funeral/Lamay') {
    deathCertContainer.style.display = 'block';
  } else {
    deathCertContainer.style.display = 'none';
    // Clear the uploaded file if purpose changes
    clearDeathCertificate(id);
  }
}

/**
 * Handle death certificate file upload
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
        <span class="text-sm text-blue-700 font-medium">Uploading...</span>
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
      
      // Show success message
      if (statusDiv) {
        statusDiv.innerHTML = `
          <div class="bg-green-50 border border-green-200 rounded-xl p-3 flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="text-sm text-green-700 font-medium">Certificate uploaded successfully</span>
          </div>
        `;
      }
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
  if (statusDiv) statusDiv.classList.add('hidden');
}

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

  const fields = {
    'equipment_id': document.getElementById('equipment_id_' + id).value,
    'quantity': document.getElementById('quantity_' + id).value,
    'borrow_datetime': document.getElementById('borrow_datetime_' + id).value,
    'return_datetime': document.getElementById('return_datetime_' + id).value,
    'description': document.getElementById('description_' + id).value,
    'purpose': document.getElementById('purpose_' + id).value,
    'death_certificate': document.getElementById('death_certificate_filename_' + id).value,
    'request': '1'
  };

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

// ============================================
// MODAL BACKDROP CLICK HANDLERS
// ============================================

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


// ============================================
// INITIALIZATION
// ============================================

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