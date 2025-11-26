<?php
// ============================================
// ALL MODALS - REQUEST, CONFIRMATION, IMAGE, SUCCESS, ERROR, PROFILE INCOMPLETE
// Variables available: $result (mysqli_result), $msg
// ============================================

// Reset result pointer to loop again for modals
$result->data_seek(0);

while ($row = $result->fetch_assoc()):
?>

<!-- ============================================ -->
<!-- REQUEST MODAL FOR EQUIPMENT ID: <?php echo $row['id']; ?> -->
<!-- ============================================ -->
<div id="requestModal<?php echo $row['id']; ?>" 
     class="modal-backdrop fixed inset-0 bg-black/60 items-center justify-center p-0 sm:p-4" style="display: none;">
  <div class="bg-white w-full h-full sm:h-auto sm:rounded-2xl shadow-2xl sm:max-w-5xl sm:mx-auto modal-enter flex flex-col overflow-hidden"
       style="max-height: 100vh; sm:max-height: 90vh;">

    <!-- Mobile Header with Close -->
    <div class="flex items-center justify-between p-4 border-b sm:hidden bg-white sticky top-0 z-10">
      <h2 class="text-lg font-bold">Request Equipment</h2>
      <button onclick="closeRequestModal(<?php echo $row['id']; ?>)" 
              class="text-gray-400 hover:text-gray-600 text-2xl font-bold transition">&times;</button>
    </div>

    <!-- Desktop Close Button -->
    <button onclick="closeRequestModal(<?php echo $row['id']; ?>)" 
            class="hidden sm:block absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-3xl font-bold transition z-10">&times;</button>

    <div class="flex flex-col md:flex-row w-full overflow-y-auto flex-1">
      <input type="hidden" id="equipment_id_<?php echo $row['id']; ?>" value="<?php echo $row['id']; ?>">
      <input type="hidden" id="equipment_name_<?php echo $row['id']; ?>" value="<?php echo htmlspecialchars($row['name']); ?>">
      <input type="hidden" id="equipment_desc_<?php echo $row['id']; ?>" value="<?php echo htmlspecialchars($row['description']); ?>">
      <input type="hidden" id="equipment_image_<?php echo $row['id']; ?>" value="<?php echo htmlspecialchars($row['image']); ?>">
      <input type="hidden" id="equipment_total_qty_<?php echo $row['id']; ?>" value="<?php echo $row['quantity']; ?>">

      <!-- LEFT SIDE - Equipment Info -->
      <div class="md:w-1/2 bg-gradient-to-br from-gray-50 to-blue-50 flex flex-col p-6">
        <?php if (!empty($row['image'])): ?>
          <div class="w-full mb-4">
            <img src="../photos/<?php echo htmlspecialchars($row['image']); ?>" 
                 class="w-full h-52 sm:h-64 md:h-80 object-cover rounded-2xl shadow-md">
          </div>
        <?php endif; ?>

        <div class="flex-1 bg-white rounded-xl p-4 shadow-sm">
          <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($row['name']); ?></h2>
          <p class="text-gray-600 text-sm sm:text-base mb-4"><?php echo htmlspecialchars($row['description']); ?></p>
          <div class="flex items-center gap-2 text-sm sm:text-base">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <span class="font-semibold text-gray-700">Total Available:</span>
            <span class="font-bold text-gray-900"><?php echo $row['quantity']; ?></span>
          </div>

          <!-- Availability Indicator -->
          <div id="availability_indicator_<?php echo $row['id']; ?>" class="mt-4 p-3 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg text-sm text-blue-700 hidden">
            <span class="font-semibold">Checking availability...</span>
          </div>

          <!-- Quantity -->
          <div class="mt-4">
           <input type="number" 
       id="quantity_<?php echo $row['id']; ?>" 
       name="quantity" 
       min="1" 
       max="<?php echo $row['quantity']; ?>"
       step="1"
       value="1" 
       required
       oninput="this.value = this.value.replace(/[^0-9]/g, ''); checkAvailabilityRealtime(<?php echo $row['id']; ?>)"
       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"> </div>
        </div>
      </div>

      <!-- RIGHT SIDE - Form -->
      <div class="md:w-1/2 p-6 flex flex-col gap-5 bg-white">

        <!-- Purpose Dropdown -->
        <div>
          <label class="text-sm font-semibold block mb-2 text-gray-700">Purpose <span class="text-red-500">*</span></label>
          <select id="purpose_<?php echo $row['id']; ?>" 
                  onchange="toggleDeathCertificateField(<?php echo $row['id']; ?>)"
                  class="w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" required>
            <option value="">Select purpose...</option>
            <option value="Birthday Party">Birthday Party</option>
            <option value="Wedding">Wedding</option>
            <option value="Anniversary">Anniversary</option>
            <option value="Seminar">Seminar</option>
            <option value="Workshop">Workshop</option>
            <option value="Community Event">Community Event</option>
            <option value="Funeral/Lamay">Funeral/Lamay</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <!-- Death Certificate Upload (Hidden by default) -->
        <div id="death_certificate_container_<?php echo $row['id']; ?>" style="display: none;">
          <label class="text-sm font-semibold block mb-2 text-gray-700">Death Certificate <span class="text-red-500">*</span></label>
          <p class="text-gray-500 text-xs mb-2">Please upload or capture a photo of the death certificate as proof</p>
          
          <!-- File Input (Desktop) -->
          <input type="file" 
                 id="death_certificate_file_<?php echo $row['id']; ?>" 
                 accept="image/jpeg,image/jpg,image/png"
                 onchange="handleDeathCertificateUpload(<?php echo $row['id']; ?>, this)"
                 class="hidden">
          
          <!-- Camera Input (Mobile) -->
          <input type="file" 
                 id="death_certificate_camera_<?php echo $row['id']; ?>" 
                 accept="image/*" 
                 capture="environment"
                 onchange="handleDeathCertificateUpload(<?php echo $row['id']; ?>, this)"
                 class="hidden">
          
          <!-- Upload Buttons -->
          <div class="flex gap-2">
            <button type="button" 
                    onclick="document.getElementById('death_certificate_file_<?php echo $row['id']; ?>').click()"
                    class="flex-1 bg-white border-2 border-gray-300 text-gray-700 py-3 px-4 text-sm rounded-xl hover:bg-gray-50 transition font-semibold flex items-center justify-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
              </svg>
              Upload File
            </button>
            <button type="button" 
                    onclick="document.getElementById('death_certificate_camera_<?php echo $row['id']; ?>').click()"
                    class="flex-1 bg-white border-2 border-gray-300 text-gray-700 py-3 px-4 text-sm rounded-xl hover:bg-gray-50 transition font-semibold flex items-center justify-center gap-2 md:hidden">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Capture Photo
            </button>
          </div>
          
          <!-- Upload Status/Preview -->
          <div id="death_certificate_status_<?php echo $row['id']; ?>" class="mt-3 hidden">
            <div class="bg-green-50 border border-green-200 rounded-xl p-3 flex items-center gap-3">
              <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <span class="text-sm text-green-700 font-medium">Certificate uploaded successfully</span>
            </div>
          </div>
          
          <input type="hidden" id="death_certificate_filename_<?php echo $row['id']; ?>" value="">
        </div>

        <div>
          <label class="text-sm font-semibold block mb-2 text-gray-700">Borrow Date & Time</label>
          <p class="text-gray-500 text-xs mb-2">When you'll pick up the equipment</p>
          <input type="text" id="borrow_datetime_<?php echo $row['id']; ?>"
                 onchange="checkAvailabilityRealtime(<?php echo $row['id']; ?>)"
                 class="w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" 
                 placeholder="Select date & time" required>
        </div>

        <div>
          <label class="text-sm font-semibold block mb-2 text-gray-700">Return Date & Time</label>
          <p class="text-gray-500 text-xs mb-2">When you'll return the equipment</p>
          <input type="text" id="return_datetime_<?php echo $row['id']; ?>"
                 onchange="checkAvailabilityRealtime(<?php echo $row['id']; ?>)"
                 class="w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" 
                 placeholder="Select date & time" required>
        </div>

        <div>
          <label class="text-sm font-semibold block mb-2 text-gray-700">Description (Optional)</label>
          <textarea id="description_<?php echo $row['id']; ?>" rows="3" placeholder="Add any notes or special requests..."
                    class="w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-xl resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"></textarea>
        </div>

        <label class="flex items-start gap-3 text-sm cursor-pointer p-4 bg-gray-50 rounded-xl border-2 border-gray-200 hover:border-blue-300 transition">
          <input type="checkbox" id="agreeCheckbox<?php echo $row['id']; ?>" onchange="toggleSubmitButton(<?php echo $row['id']; ?>)"
                 class="mt-0.5 w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
          <span class="text-gray-700">I agree to return the item in good condition and on time.</span>
        </label>

        <button type="button"
                id="submitBtn<?php echo $row['id']; ?>"
                class="w-full bg-gray-300 text-gray-500 py-3.5 text-sm sm:text-base rounded-xl cursor-not-allowed transition font-semibold mt-auto"
                onclick="if(!this.disabled) openConfirmModal(<?php echo $row['id']; ?>)"
                disabled>
          Submit Request
        </button>

      </div>
    </div>
  </div>
</div>

<!-- ============================================ -->
<!-- CONFIRMATION MODAL FOR EQUIPMENT ID: <?php echo $row['id']; ?> -->
<!-- ============================================ -->
<div id="confirmModal<?php echo $row['id']; ?>" 
     class="modal-backdrop fixed inset-0 bg-black/60 items-center justify-center p-0 sm:p-4" style="display: none;">
  <div class="bg-white w-full h-full sm:h-auto sm:rounded-2xl shadow-2xl sm:max-w-2xl sm:mx-auto modal-enter overflow-hidden flex flex-col" 
       style="max-height: 100vh; sm:max-height: 90vh;">
    
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6 text-white flex-shrink-0">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <h2 class="text-2xl font-bold">Confirm Your Request</h2>
          <p class="text-blue-100 text-sm mt-1">Please review the details before submitting</p>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="p-6 overflow-y-auto flex-1">
      <div class="space-y-5">
        
        <div class="flex items-start gap-4 pb-5 border-b-2 border-gray-100">
          <img id="confirm_image_<?php echo $row['id']; ?>" src="" class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-xl shadow-sm flex-shrink-0">
          <div class="flex-1 min-w-0">
            <h3 class="font-bold text-lg sm:text-xl text-gray-900 mb-1" id="confirm_name_<?php echo $row['id']; ?>"></h3>
            <p class="text-gray-600 text-sm line-clamp-2" id="confirm_desc_<?php echo $row['id']; ?>"></p>
          </div>
        </div>

        <div class="bg-purple-50 rounded-xl p-4">
          <p class="text-xs text-purple-600 font-semibold mb-1 uppercase tracking-wide">Purpose</p>
          <p class="text-base font-bold text-gray-900" id="confirm_purpose_<?php echo $row['id']; ?>"></p>
        </div>

        <div class="bg-gray-50 rounded-xl p-4">
          <p class="text-xs text-gray-500 font-semibold mb-1 uppercase tracking-wide">Quantity</p>
          <p class="text-2xl font-bold text-gray-900" id="confirm_quantity_<?php echo $row['id']; ?>"></p>
        </div>

        <div class="bg-blue-50 rounded-xl p-4">
          <p class="text-xs text-blue-600 font-semibold mb-1 uppercase tracking-wide">Borrow Date & Time</p>
          <p class="text-base font-bold text-gray-900" id="confirm_borrow_<?php echo $row['id']; ?>"></p>
        </div>

        <div class="bg-green-50 rounded-xl p-4">
          <p class="text-xs text-green-600 font-semibold mb-1 uppercase tracking-wide">Return Date & Time</p>
          <p class="text-base font-bold text-gray-900" id="confirm_return_<?php echo $row['id']; ?>"></p>
        </div>

        <div id="confirm_description_container_<?php echo $row['id']; ?>" style="display: none;" class="bg-amber-50 rounded-xl p-4">
          <p class="text-xs text-amber-600 font-semibold mb-1 uppercase tracking-wide">Additional Notes</p>
          <p class="text-gray-700 text-sm" id="confirm_description_<?php echo $row['id']; ?>"></p>
        </div>

      </div>
    </div>

    <!-- Footer Buttons -->
    <div class="p-6 bg-gray-50 flex gap-3 flex-shrink-0 border-t-2 border-gray-100">
      <button onclick="closeConfirmModal(<?php echo $row['id']; ?>)"
              class="flex-1 bg-white border-2 border-gray-300 text-gray-700 py-3 text-sm sm:text-base rounded-xl hover:bg-gray-50 transition font-semibold">
        Cancel
      </button>
      <button onclick="submitRequest(<?php echo $row['id']; ?>)"
              class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 text-sm sm:text-base rounded-xl hover:from-blue-700 hover:to-blue-800 transition font-semibold shadow-sm">
        Confirm Request
      </button>
    </div>

  </div>
</div>

<?php endwhile; ?>

<!-- ============================================ -->
<!-- PROFILE INCOMPLETE MODAL (GLOBAL) -->
<!-- ============================================ -->
<div id="profileIncompleteModal" class="modal-backdrop fixed inset-0 bg-black/60 items-center justify-center p-4" style="display: none;">
  <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md text-center relative modal-enter">
    <div class="w-20 h-20 bg-gradient-to-br from-orange-400 to-orange-600 rounded-full flex items-center justify-center mx-auto mb-5 shadow-lg">
      <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
      </svg>
    </div>
    <h2 class="text-2xl sm:text-3xl font-bold mb-3 text-gray-900">Profile Incomplete</h2>
    <p class="text-base text-gray-600 mb-2 leading-relaxed">Please complete your profile before borrowing equipment.</p>
    <p class="text-sm text-gray-500 mb-6" id="missingFieldsText">Missing: <span id="missingFieldsList"></span></p>
    <button onclick="redirectToProfile()"
            class="w-full bg-gradient-to-r from-orange-500 to-orange-600 text-white py-3.5 text-base rounded-xl hover:from-orange-600 hover:to-orange-700 transition font-semibold shadow-sm">
      Update Profile
    </button>
    <button onclick="closeProfileIncompleteModal()"
            class="w-full mt-3 bg-white border-2 border-gray-300 text-gray-700 py-3 text-sm rounded-xl hover:bg-gray-50 transition font-semibold">
      Cancel
    </button>
  </div>
</div>

<!-- ============================================ -->
<!-- IMAGE MODAL (GLOBAL) -->
<!-- ============================================ -->
<div id="imageModal" class="modal-backdrop fixed inset-0 bg-black/70 items-center justify-center p-4" style="display: none;">
  <div class="relative max-w-5xl w-full">
    <button onclick="closeImageModal()" 
            class="absolute -top-12 right-0 text-white text-4xl font-bold hover:text-gray-300 transition">&times;</button>
    <img id="modalImage" src="" class="max-h-[85vh] w-full object-contain rounded-2xl shadow-2xl">
  </div>
</div>

<!-- ============================================ -->
<!-- SUCCESS MODAL -->
<!-- ============================================ -->
<?php if ($msg === "Request submitted successfully!"): ?>
<div id="successModal" class="modal-backdrop fixed inset-0 bg-black/60 items-center justify-center p-4" style="display: flex;">
  <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md text-center relative modal-enter">
    <div class="w-20 h-20 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mx-auto mb-5 shadow-lg">
      <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
      </svg>
    </div>
    <h2 class="text-2xl sm:text-3xl font-bold mb-3 text-gray-900">Request Submitted!</h2>
    <p class="text-base text-gray-600 mb-6 leading-relaxed">Your borrowing request has been successfully submitted. Please wait for staff approval.</p>
    <button onclick="closeSuccessModal()"
            class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white py-3.5 text-base rounded-xl hover:from-green-600 hover:to-green-700 transition font-semibold shadow-sm">
      Got it!
    </button>
  </div>
</div>



<?php endif; ?>

<!-- ============================================ -->
<!-- ERROR MODAL -->
<!-- ============================================ -->
<div id="errorModal" class="modal-backdrop fixed inset-0 bg-black/60 items-center justify-center p-4" style="display: none;">
  <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md text-center relative modal-enter">
    <div class="w-20 h-20 bg-gradient-to-br from-red-400 to-red-600 rounded-full flex items-center justify-center mx-auto mb-5 shadow-lg">
      <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </div>
    <h2 class="text-2xl sm:text-3xl font-bold mb-3 text-gray-900">Invalid Dates!</h2>
    <p class="text-base text-gray-600 mb-6 leading-relaxed">Return date must be after borrow date. Please correct the dates and try again.</p>
    <button onclick="closeErrorModal()"
            class="w-full bg-gradient-to-r from-red-500 to-red-600 text-white py-3.5 text-base rounded-xl hover:from-red-600 hover:to-red-700 transition font-semibold shadow-sm">
      OK, I understand
    </button>
  </div>
</div>
<!-- REUSABLE VALIDATION MODAL (GLOBAL) -->
<div id="validationModal" class="modal-backdrop fixed inset-0 bg-black/60 items-center justify-center p-4" style="display: none;">
  <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md text-center relative modal-enter">
    <div id="validationModalIcon" class="w-20 h-20 bg-gradient-to-br from-orange-400 to-orange-600 rounded-full flex items-center justify-center mx-auto mb-5 shadow-lg">
      <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
      </svg>
    </div>
    <h2 id="validationModalTitle" class="text-2xl sm:text-3xl font-bold mb-3 text-gray-900">Validation Error</h2>
    <p id="validationModalMessage" class="text-base text-gray-600 mb-6 leading-relaxed">Please fix the errors and try again.</p>
    <button id="validationModalButton" onclick="closeValidationModal()"
            class="w-full bg-gradient-to-r from-orange-500 to-orange-600 text-white py-3.5 text-base rounded-xl hover:from-orange-600 hover:to-orange-700 transition font-semibold shadow-sm">
      Got it!
    </button>
  </div>
</div>