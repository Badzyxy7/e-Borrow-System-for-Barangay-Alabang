<!-- Unified Details Modal -->
<div id="detailsModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 overflow-y-auto py-8">
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl mx-4 my-8">
    <!-- Modal Header -->
    <div class="flex items-center justify-between p-6 border-b border-gray-200">
      <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
        <i data-feather="file-text" class="w-6 h-6 text-blue-600"></i>
        Request Details
      </h2>
      <button onclick="closeModal('detailsModal')" class="p-2 hover:bg-gray-100 rounded-lg transition">
        <i data-feather="x" class="w-5 h-5 text-gray-600"></i>
      </button>
    </div>

    <!-- Modal Body -->
    <div class="p-6 max-h-[70vh] overflow-y-auto space-y-4">
      <!-- Equipment Details -->
      <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
          <i data-feather="package" class="w-4 h-4 text-blue-600"></i> Equipment Details
        </h4>
        <div class="flex gap-4">
          <img id="detail_equipment_photo" src="" alt="Equipment" 
               class="w-24 h-24 object-cover rounded-lg cursor-pointer border-2 border-blue-200"
               onclick="viewPhoto(this.src, document.getElementById('detail_equipment_name').textContent)">
          <div class="flex-1 text-sm space-y-2">
            <div>
              <span class="font-medium text-gray-700">Name:</span>
              <span id="detail_equipment_name" class="text-gray-900 font-semibold"></span>
            </div>
            <div>
              <span class="font-medium text-gray-700">Description:</span>
              <p id="detail_equipment_desc" class="text-gray-600 mt-1"></p>
            </div>
            <div>
              <span class="font-medium text-gray-700">Quantity:</span>
              <span id="detail_qty" class="text-gray-900"></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Borrower Information -->
      <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
          <i data-feather="user" class="w-4 h-4 text-gray-600"></i> Borrower Information
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
          <div>
            <span class="font-medium text-gray-700">Name:</span>
            <p id="detail_user_name" class="text-gray-900"></p>
          </div>
          <div>
            <span class="font-medium text-gray-700">Email:</span>
            <p id="detail_user_email" class="text-gray-600"></p>
          </div>
          <div class="col-span-2">
            <span class="font-medium text-gray-700">Address:</span>
            <p id="detail_address" class="text-gray-600"></p>
          </div>
        </div>
      </div>

      <!-- Purpose Section -->
      <div id="detail_purpose_section" class="bg-purple-50 rounded-xl p-4 border border-purple-100">
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
          <i data-feather="info" class="w-4 h-4 text-purple-600"></i> Purpose
        </h4>
        <div class="text-sm">
          <p id="detail_purpose" class="text-gray-900 font-medium mb-2"></p>
          <button id="detail_certificate_btn" onclick="" 
                  class="hidden px-3 py-1.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-2 text-xs">
            <i data-feather="file-text" class="w-3 h-3"></i>
            <span>View Death Certificate</span>
          </button>
        </div>
      </div>

      <!-- Timeline -->
      <div class="bg-green-50 rounded-xl p-4 border border-green-100">
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
          <i data-feather="calendar" class="w-4 h-4 text-green-600"></i> Timeline
        </h4>
        <div class="space-y-2 text-sm">
          <div class="flex items-center justify-between">
            <span class="text-gray-700 flex items-center gap-2">
              <i data-feather="clock" class="w-3 h-3"></i>
              Requested:
            </span>
            <span id="detail_created_at" class="font-medium text-gray-900"></span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-gray-700 flex items-center gap-2">
              <i data-feather="calendar" class="w-3 h-3"></i>
              Borrow Date:
            </span>
            <span id="detail_borrow_date" class="font-medium text-gray-900"></span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-gray-700 flex items-center gap-2">
              <i data-feather="calendar" class="w-3 h-3"></i>
              Return Date:
            </span>
            <span id="detail_return_date" class="font-medium text-gray-900"></span>
          </div>
          <div class="flex items-center justify-between pt-2 border-t border-green-200">
            <span class="text-gray-700 font-medium">Duration:</span>
            <span id="detail_duration" class="font-semibold text-green-700"></span>
          </div>
        </div>
      </div>

      <!-- Audit Trail -->
      <div id="detail_audit_section" class="bg-indigo-50 rounded-xl p-4 border border-indigo-100 hidden">
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
          <i data-feather="activity" class="w-4 h-4 text-indigo-600"></i> Audit Trail
        </h4>
        <div id="detail_audit_content" class="space-y-3 text-sm">
          <!-- Dynamic audit trail will be inserted here -->
        </div>
      </div>

      <!-- Photos Section (for delivered/returned tabs) -->
      <div id="detail_photos_section" class="hidden">
        <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-100">
          <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
            <i data-feather="image" class="w-4 h-4 text-yellow-600"></i> Proof Photos
          </h4>
          <div class="flex flex-wrap gap-3">
            <button id="detail_delivery_photo_btn" onclick="" 
                    class="hidden px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2 text-xs">
              <i data-feather="truck" class="w-3 h-3"></i>
              <span>Delivery Photo</span>
            </button>
            <button id="detail_return_photo_btn" onclick="" 
                    class="hidden px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2 text-xs">
              <i data-feather="check-square" class="w-3 h-3"></i>
              <span>Return Photo</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Footer with Actions -->
    <div id="detail_actions" class="p-6 border-t border-gray-200 bg-gray-50 rounded-b-3xl">
      <!-- Dynamic action buttons will be inserted here based on tab -->
    </div>
  </div>
</div>

<!-- Keep existing modals (approval, reject, delivery, etc.) -->
<!-- Approval Modal -->
<div id="approvalModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
  <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-lg mx-4">
    <div class="flex items-center gap-3 mb-6">
      <div class="p-3 bg-green-100 rounded-full">
        <i data-feather="check-circle" class="w-6 h-6 text-green-600"></i>
      </div>
      <h2 class="text-2xl font-bold text-gray-800">Confirm Approval</h2>
    </div>
    
    <div class="bg-indigo-50 rounded-xl p-4 mb-4 border-l-4 border-indigo-500">
      <div class="flex items-center gap-2 text-sm">
        <i data-feather="user-check" class="w-4 h-4 text-indigo-600"></i>
        <span class="font-medium text-gray-700">This will be approved by:</span>
        <span class="font-bold text-indigo-700" id="approval_admin_name">
          <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : getCurrentAdminName($conn); ?>
        </span>
      </div>
    </div>
    
    <div class="bg-gray-50 rounded-xl p-6 mb-6 space-y-3">
      <h3 class="font-semibold text-gray-800">Confirm approval for:</h3>
      <p class="text-sm"><span class="font-medium">Borrower:</span> <span id="approval_user_name"></span></p>
      <p class="text-sm"><span class="font-medium">Equipment:</span> <span id="approval_equipment"></span></p>
    </div>
    <form method="post">
      <input type="hidden" name="id" id="approvalId">
      <input type="hidden" name="action" value="approve">
      <div class="flex justify-end gap-3">
        <button type="button" onclick="closeModal('approvalModal')" class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 font-medium">Cancel</button>
        <button type="submit" class="px-6 py-3 rounded-xl bg-green-600 text-white hover:bg-green-700 font-medium shadow-lg">Confirm Approval</button>
      </div>
    </form>
  </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
  <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md mx-4">
    <div class="flex items-center gap-3 mb-6">
      <div class="p-3 bg-red-100 rounded-full">
        <i data-feather="x-circle" class="w-6 h-6 text-red-600"></i>
      </div>
      <h2 class="text-2xl font-bold text-gray-800">Reject Request</h2>
    </div>
    <form method="post">
      <input type="hidden" name="id" id="rejectId">
      <input type="hidden" name="action" value="decline">
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection *</label>
        <textarea name="reason" required placeholder="Please provide a reason..." class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 outline-none h-32"></textarea>
      </div>
      <div class="flex justify-end gap-3">
        <button type="button" onclick="closeModal('rejectModal')" class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 font-medium">Cancel</button>
        <button type="submit" class="px-6 py-3 rounded-xl bg-red-600 text-white hover:bg-red-700 font-medium shadow-lg">Reject</button>
      </div>
    </form>
  </div>
</div>

<!-- Delivery Modal -->
<div id="deliveryModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
  <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md mx-4">
    <div class="flex items-center gap-3 mb-6">
      <div class="p-3 bg-blue-100 rounded-full">
        <i data-feather="truck" class="w-6 h-6 text-blue-600"></i>
      </div>
      <h2 class="text-2xl font-bold text-gray-800">Mark as Delivered</h2>
    </div>
    
    <div class="bg-indigo-50 rounded-xl p-4 mb-4 border-l-4 border-indigo-500">
      <div class="flex items-center gap-2 text-sm">
        <i data-feather="truck" class="w-4 h-4 text-indigo-600"></i>
        <span class="font-medium text-gray-700">This will be delivered by:</span>
        <span class="font-bold text-indigo-700">
          <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : getCurrentAdminName($conn); ?>
        </span>
      </div>
    </div>
    
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="id" id="deliveryId">
      <input type="hidden" name="action" value="delivered">
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Photo *</label>
        <input type="file" name="delivery_photo" accept="image/*" required class="w-full px-4 py-3 border border-gray-300 rounded-xl">
        <p class="text-xs text-gray-500 mt-2">Take a photo as proof of delivery</p>
      </div>
      <div class="flex justify-end gap-3">
        <button type="button" onclick="closeModal('deliveryModal')" class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 font-medium">Cancel</button>
        <button type="submit" class="px-6 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-medium shadow-lg">Confirm Delivery</button>
      </div>
    </form>
  </div>
</div>

<!-- Return Approval Modal -->
<div id="returnApprovalModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
  <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md mx-4">
    <div class="flex items-center gap-3 mb-6">
      <div class="p-3 bg-orange-100 rounded-full">
        <i data-feather="corner-down-left" class="w-6 h-6 text-orange-600"></i>
      </div>
      <h2 class="text-2xl font-bold text-gray-800">Approve Return Request</h2>
    </div>
    
    <div class="bg-indigo-50 rounded-xl p-4 mb-4 border-l-4 border-indigo-500">
      <div class="flex items-center gap-2 text-sm">
        <i data-feather="user-check" class="w-4 h-4 text-indigo-600"></i>
        <span class="font-medium text-gray-700">Pickup will be handled by:</span>
        <span class="font-bold text-indigo-700">
          <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : getCurrentAdminName($conn); ?>
        </span>
      </div>
    </div>
    
    <p class="text-gray-600 mb-6">Approve this return request? The user will be notified to prepare the item for pickup.</p>
    <form method="post">
      <input type="hidden" name="id" id="returnApprovalId">
      <input type="hidden" name="action" value="approve_return">
      <div class="flex justify-end gap-3">
        <button type="button" onclick="closeModal('returnApprovalModal')" class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 font-medium">Cancel</button>
        <button type="submit" class="px-6 py-3 rounded-xl bg-orange-600 text-white hover:bg-orange-700 font-medium shadow-lg">Approve Return</button>
      </div>
    </form>
  </div>
</div>

<!-- Return Modal -->
<div id="returnModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 overflow-y-auto py-8">
  <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md mx-4">
    <div class="flex items-center gap-3 mb-6">
      <div class="p-3 bg-purple-100 rounded-full">
        <i data-feather="check-square" class="w-6 h-6 text-purple-600"></i>
      </div>
      <h2 class="text-2xl font-bold text-gray-800">Mark as Returned</h2>
    </div>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="id" id="returnId">
      <input type="hidden" name="action" value="returned">
      
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Photo of Returned Item *</label>
        <input type="file" name="return_photo" accept="image/*" required class="w-full px-4 py-3 border border-gray-300 rounded-xl">
        <p class="text-xs text-gray-500 mt-2">Take a photo as proof of return</p>
      </div>

      <div class="flex justify-end gap-3">
        <button type="button" onclick="closeModal('returnModal')" class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 font-medium">Cancel</button>
        <button type="submit" class="px-6 py-3 rounded-xl bg-purple-600 text-white hover:bg-purple-700 font-medium shadow-lg">Confirm Return</button>
      </div>
    </form>
  </div>
</div>

<!-- Photo Viewer Modal -->
<div id="photoModal" class="fixed inset-0 bg-black/90 hidden items-center justify-center z-[60]" onclick="if(event.target === this) closeModal('photoModal')">
  <div class="relative max-w-6xl max-h-[90vh] w-full mx-4">
    <!-- Close Button -->
    <button onclick="closeModal('photoModal')" 
            class="absolute -top-12 right-0 p-2 bg-white/10 hover:bg-white/20 rounded-lg transition text-white">
      <i data-feather="x" class="w-6 h-6"></i>
    </button>
    
    <!-- Photo Title -->
    <div class="absolute -top-12 left-0 text-white font-semibold text-lg flex items-center gap-2">
      <i data-feather="image" class="w-5 h-5"></i>
      <span id="photoTitle"></span>
    </div>
    
    <!-- Photo Container -->
    <div class="bg-white rounded-lg overflow-hidden shadow-2xl">
      <img id="photoImage" 
           src="" 
           alt="Photo" 
           class="w-full h-auto max-h-[85vh] object-contain">
    </div>
    
    <!-- Download/Actions Bar -->
    <div class="absolute -bottom-12 left-0 right-0 flex justify-center gap-3">
      <button onclick="window.open(document.getElementById('photoImage').src, '_blank')" 
              class="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg transition text-white flex items-center gap-2">
        <i data-feather="external-link" class="w-4 h-4"></i>
        <span>Open in New Tab</span>
      </button>
      <button onclick="closeModal('photoModal')" 
              class="px-4 py-2 bg-white hover:bg-gray-100 rounded-lg transition text-gray-800 flex items-center gap-2 font-medium">
        <i data-feather="x" class="w-4 h-4"></i>
        <span>Close</span>
      </button>
    </div>
  </div>
</div>

<!-- ============================================ -->
<!-- GROUP REQUEST MODALS -->
<!-- ============================================ -->

<!-- Group Details Modal -->
<div id="groupDetailsModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 overflow-y-auto py-8">
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl mx-4 my-8">
    <!-- Modal Header -->
    <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-700">
      <h2 class="text-2xl font-bold text-white flex items-center gap-2">
        <i data-feather="layers" class="w-6 h-6"></i>
        Group Request Details
        <span id="group_detail_count" class="ml-2 px-3 py-1 bg-white/20 rounded-full text-sm"></span>
      </h2>
      <button onclick="closeModal('groupDetailsModal')" class="p-2 hover:bg-white/10 rounded-lg transition">
        <i data-feather="x" class="w-5 h-5 text-white"></i>
      </button>
    </div>

    <!-- Modal Body -->
    <div class="p-6 max-h-[70vh] overflow-y-auto space-y-4">
      
      <!-- Priority Badge (if applicable) -->
      <div id="group_detail_priority_badge" class="hidden bg-purple-50 border-l-4 border-purple-500 rounded-xl p-4">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-purple-100 rounded-full">
            <i data-feather="alert-circle" class="w-5 h-5 text-purple-600"></i>
          </div>
          <div>
            <p class="font-bold text-purple-900">⚡ Priority Request</p>
            <p class="text-sm text-purple-700">This funeral request has been prioritized for review</p>
          </div>
        </div>
      </div>

      <!-- Borrower Information -->
      <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
          <i data-feather="user" class="w-4 h-4 text-gray-600"></i> Borrower Information
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
          <div>
            <span class="font-medium text-gray-700">Name:</span>
            <p id="group_detail_user_name" class="text-gray-900"></p>
          </div>
          <div>
            <span class="font-medium text-gray-700">Email:</span>
            <p id="group_detail_user_email" class="text-gray-600"></p>
          </div>
          <div class="col-span-2">
            <span class="font-medium text-gray-700">Address:</span>
            <p id="group_detail_address" class="text-gray-600"></p>
          </div>
        </div>
      </div>

      <!-- Purpose Section -->
      <div class="bg-purple-50 rounded-xl p-4 border border-purple-100">
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
          <i data-feather="info" class="w-4 h-4 text-purple-600"></i> Purpose
        </h4>
        <div class="text-sm">
          <p id="group_detail_purpose" class="text-gray-900 font-medium mb-2"></p>
          <button id="group_detail_certificate_btn" onclick="" 
                  class="hidden px-3 py-1.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-2 text-xs">
            <i data-feather="file-text" class="w-3 h-3"></i>
            <span>View Death Certificate</span>
          </button>
        </div>
      </div>

      <!-- Timeline -->
      <div class="bg-green-50 rounded-xl p-4 border border-green-100">
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
          <i data-feather="calendar" class="w-4 h-4 text-green-600"></i> Timeline
        </h4>
        <div class="space-y-2 text-sm">
          <div class="flex items-center justify-between">
            <span class="text-gray-700 flex items-center gap-2">
              <i data-feather="clock" class="w-3 h-3"></i>
              Requested:
            </span>
            <span id="group_detail_created_at" class="font-medium text-gray-900"></span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-gray-700 flex items-center gap-2">
              <i data-feather="calendar" class="w-3 h-3"></i>
              Borrow Date:
            </span>
            <span id="group_detail_borrow_date" class="font-medium text-gray-900"></span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-gray-700 flex items-center gap-2">
              <i data-feather="calendar" class="w-3 h-3"></i>
              Return Date:
            </span>
            <span id="group_detail_return_date" class="font-medium text-gray-900"></span>
          </div>
          <div class="flex items-center justify-between pt-2 border-t border-green-200">
            <span class="text-gray-700 font-medium">Duration:</span>
            <span id="group_detail_duration" class="font-semibold text-green-700"></span>
          </div>
        </div>
      </div>

      <!-- Equipment Items -->
      <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
          <i data-feather="package" class="w-4 h-4 text-blue-600"></i> 
          Equipment Items <span id="group_detail_items_count" class="text-blue-600"></span>
        </h4>
        <div id="group_detail_items_container" class="space-y-3">
          <!-- Items will be populated dynamically -->
        </div>
      </div>

      <!-- Description (if provided) -->
      <div id="group_detail_description_section" class="hidden bg-amber-50 rounded-xl p-4 border border-amber-100">
        <h4 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
          <i data-feather="message-square" class="w-4 h-4 text-amber-600"></i> Additional Notes
        </h4>
        <p id="group_detail_description" class="text-gray-700 text-sm"></p>
      </div>

    </div>

    <!-- Modal Footer with Actions -->
    <div class="p-6 border-t border-gray-200 bg-gray-50 rounded-b-3xl">
      <div class="flex justify-end gap-3">
        <button onclick="closeModal('groupDetailsModal')" 
                class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 font-medium">
          Close
        </button>
        <button id="group_detail_approve_btn" onclick="" 
                class="px-6 py-3 rounded-xl bg-green-600 text-white hover:bg-green-700 font-medium flex items-center gap-2">
          <i data-feather="check" class="w-4 h-4"></i>
          <span>Approve All</span>
        </button>
        <button id="group_detail_reject_btn" onclick="" 
                class="px-6 py-3 rounded-xl bg-red-600 text-white hover:bg-red-700 font-medium flex items-center gap-2">
          <i data-feather="x" class="w-4 h-4"></i>
          <span>Reject All</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Group Approval Modal -->
<div id="groupApprovalModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
  <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-2xl mx-4">
    <div class="flex items-center gap-3 mb-6">
      <div class="p-3 bg-green-100 rounded-full">
        <i data-feather="check-circle" class="w-6 h-6 text-green-600"></i>
      </div>
      <h2 class="text-2xl font-bold text-gray-800">Confirm Group Approval</h2>
    </div>
    
    <div class="bg-indigo-50 rounded-xl p-4 mb-4 border-l-4 border-indigo-500">
      <div class="flex items-center gap-2 text-sm">
        <i data-feather="user-check" class="w-4 h-4 text-indigo-600"></i>
        <span class="font-medium text-gray-700">This will be approved by:</span>
        <span class="font-bold text-indigo-700">
          <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : getCurrentAdminName($conn); ?>
        </span>
      </div>
    </div>
    
    <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-xl p-4 mb-4">
      <div class="flex gap-3">
        <i data-feather="alert-triangle" class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5"></i>
        <div>
          <p class="font-semibold text-yellow-900 mb-1">Approving Multiple Items</p>
          <p class="text-sm text-yellow-800">You are about to approve <span id="group_approval_count" class="font-bold"></span> equipment items in this group request. All items will be marked as approved.</p>
        </div>
      </div>
    </div>
    
    <div class="bg-gray-50 rounded-xl p-4 mb-6 max-h-60 overflow-y-auto">
      <h3 class="font-semibold text-gray-800 mb-3">Equipment Items:</h3>
      <div id="group_approval_items" class="space-y-2">
        <!-- Items will be populated dynamically -->
      </div>
    </div>
    
    <form method="post" id="groupApprovalForm">
      <input type="hidden" name="action" value="approve_group">
      <input type="hidden" name="group_request_id" id="groupApprovalGroupId">
      <input type="hidden" name="request_ids" id="groupApprovalRequestIds">
      
      <div class="flex justify-end gap-3">
        <button type="button" onclick="closeModal('groupApprovalModal')" 
                class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 font-medium">
          Cancel
        </button>
        <button type="submit" 
                class="px-6 py-3 rounded-xl bg-green-600 text-white hover:bg-green-700 font-medium shadow-lg flex items-center gap-2">
          <i data-feather="check-circle" class="w-4 h-4"></i>
          <span>Confirm Approval</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Group Reject Modal -->
<div id="groupRejectModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
  <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-2xl mx-4">
    <div class="flex items-center gap-3 mb-6">
      <div class="p-3 bg-red-100 rounded-full">
        <i data-feather="x-circle" class="w-6 h-6 text-red-600"></i>
      </div>
      <h2 class="text-2xl font-bold text-gray-800">Reject Group Request</h2>
    </div>
    
    <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4 mb-4">
      <div class="flex gap-3">
        <i data-feather="alert-triangle" class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5"></i>
        <div>
          <p class="font-semibold text-red-900 mb-1">Rejecting Multiple Items</p>
          <p class="text-sm text-red-800">You are about to reject <span id="group_reject_count" class="font-bold"></span> equipment items. All items in this group will be declined.</p>
        </div>
      </div>
    </div>
    
    <form method="post" id="groupRejectForm">
      <input type="hidden" name="action" value="reject_group">
      <input type="hidden" name="group_request_id" id="groupRejectGroupId">
      <input type="hidden" name="request_ids" id="groupRejectRequestIds">
      
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection *</label>
        <textarea name="reason" required placeholder="Please provide a reason for rejecting this group request..." 
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 outline-none h-32"></textarea>
        <p class="text-xs text-gray-500 mt-2">This reason will be sent to the borrower for all items.</p>
      </div>
      
      <div class="flex justify-end gap-3">
        <button type="button" onclick="closeModal('groupRejectModal')" 
                class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 font-medium">
          Cancel
        </button>
        <button type="submit" 
                class="px-6 py-3 rounded-xl bg-red-600 text-white hover:bg-red-700 font-medium shadow-lg flex items-center gap-2">
          <i data-feather="x-circle" class="w-4 h-4"></i>
          <span>Confirm Rejection</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Group Delivery Modal -->
<div id="groupDeliveryModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 overflow-y-auto py-8">
  <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-2xl mx-4">
    <div class="flex items-center gap-3 mb-6">
      <div class="p-3 bg-blue-100 rounded-full">
        <i data-feather="truck" class="w-6 h-6 text-blue-600"></i>
      </div>
      <h2 class="text-2xl font-bold text-gray-800">Mark Group as Delivered</h2>
    </div>
    
    <div class="bg-indigo-50 rounded-xl p-4 mb-4 border-l-4 border-indigo-500">
      <div class="flex items-center gap-2 text-sm">
        <i data-feather="truck" class="w-4 h-4 text-indigo-600"></i>
        <span class="font-medium text-gray-700">This will be delivered by:</span>
        <span class="font-bold text-indigo-700">
          <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : getCurrentAdminName($conn); ?>
        </span>
      </div>
    </div>
    
    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-xl p-4 mb-4">
      <div class="flex gap-3">
        <i data-feather="package" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
        <div>
          <p class="font-semibold text-blue-900 mb-1">Delivering Multiple Items</p>
          <p class="text-sm text-blue-800">You are about to mark <span id="group_delivery_count" class="font-bold"></span> equipment items as delivered.</p>
        </div>
      </div>
    </div>
    
    <div class="bg-gray-50 rounded-xl p-4 mb-6 max-h-48 overflow-y-auto">
      <h3 class="font-semibold text-gray-800 mb-3">Equipment Items:</h3>
      <div id="group_delivery_items" class="space-y-2">
        <!-- Items will be populated dynamically -->
      </div>
    </div>
    
    <form method="post" enctype="multipart/form-data" id="groupDeliveryForm">
      <input type="hidden" name="action" value="deliver_group">
      <input type="hidden" name="group_request_id" id="groupDeliveryGroupId">
      <input type="hidden" name="request_ids" id="groupDeliveryRequestIds">
      
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Photo *</label>
        <input type="file" name="delivery_photo" accept="image/*" required 
               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
        <p class="text-xs text-gray-500 mt-2">Take a photo showing all delivered items</p>
      </div>
      
      <div class="flex justify-end gap-3">
        <button type="button" onclick="closeModal('groupDeliveryModal')" 
                class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 font-medium">
          Cancel
        </button>
        <button type="submit" 
                class="px-6 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-medium shadow-lg flex items-center gap-2">
          <i data-feather="truck" class="w-4 h-4"></i>
          <span>Confirm Delivery</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Add these modals to your modals file -->

<!-- Group Return Approval Modal -->
<div id="groupReturnApprovalModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
  <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-2xl mx-4">
    <div class="flex items-center gap-3 mb-6">
      <div class="p-3 bg-orange-100 rounded-full">
        <i data-feather="check-circle" class="w-6 h-6 text-orange-600"></i>
      </div>
      <h2 class="text-2xl font-bold text-gray-800">Approve Group Return Request</h2>
    </div>
    
    <div class="bg-indigo-50 rounded-xl p-4 mb-4 border-l-4 border-indigo-500">
      <div class="flex items-center gap-2 text-sm">
        <i data-feather="user-check" class="w-4 h-4 text-indigo-600"></i>
        <span class="font-medium text-gray-700">Pickup will be handled by:</span>
        <span class="font-bold text-indigo-700">
          <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : getCurrentAdminName($conn); ?>
        </span>
      </div>
    </div>
    
    <div class="bg-orange-50 border-l-4 border-orange-500 rounded-xl p-4 mb-4">
      <div class="flex gap-3">
        <i data-feather="package" class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5"></i>
        <div>
          <p class="font-semibold text-orange-900 mb-1">Approving Group Return</p>
          <p class="text-sm text-orange-800">You are about to approve the return of <span id="group_return_approval_count" class="font-bold"></span> equipment items. The user will be notified to prepare all items for pickup.</p>
        </div>
      </div>
    </div>
    
    <form method="post" id="groupReturnApprovalForm">
      <input type="hidden" name="action" value="approve_group_return">
      <input type="hidden" name="group_request_id" id="groupReturnApprovalGroupId">
      
      <div class="flex justify-end gap-3">
        <button type="button" onclick="closeModal('groupReturnApprovalModal')" 
                class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 font-medium">
          Cancel
        </button>
        <button type="submit" 
                class="px-6 py-3 rounded-xl bg-orange-600 text-white hover:bg-orange-700 font-medium shadow-lg flex items-center gap-2">
          <i data-feather="check" class="w-4 h-4"></i>
          <span>Approve Group Return</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Group Return Mark Modal -->
<div id="groupReturnMarkModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
  <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-2xl mx-4">
    <div class="flex items-center gap-3 mb-6">
      <div class="p-3 bg-purple-100 rounded-full">
        <i data-feather="check-square" class="w-6 h-6 text-purple-600"></i>
      </div>
      <h2 class="text-2xl font-bold text-gray-800">Mark Group as Returned</h2>
    </div>
    
    <div class="bg-indigo-50 rounded-xl p-4 mb-4 border-l-4 border-indigo-500">
      <div class="flex items-center gap-2 text-sm">
        <i data-feather="user-check" class="w-4 h-4 text-indigo-600"></i>
        <span class="font-medium text-gray-700">This will be received by:</span>
        <span class="font-bold text-indigo-700">
          <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : getCurrentAdminName($conn); ?>
        </span>
      </div>
    </div>
    
    <div class="bg-purple-50 border-l-4 border-purple-500 rounded-xl p-4 mb-4">
      <div class="flex gap-3">
        <i data-feather="package" class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5"></i>
        <div>
          <p class="font-semibold text-purple-900 mb-1">Processing Group Return</p>
          <p class="text-sm text-purple-800">You are about to mark <span id="group_return_mark_count" class="font-bold"></span> equipment items as returned. All items will be made available again.</p>
        </div>
      </div>
    </div>
    
    <form method="post" enctype="multipart/form-data" id="groupReturnMarkForm">
      <input type="hidden" name="action" value="mark_group_returned">
      <input type="hidden" name="group_request_id" id="groupReturnMarkGroupId">
      
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Photo of Returned Items *</label>
        <input type="file" name="return_photo" accept="image/*" required 
               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none">
        <p class="text-xs text-gray-500 mt-2">Take a photo showing all returned items</p>
      </div>
      
      <div class="flex justify-end gap-3">
        <button type="button" onclick="closeModal('groupReturnMarkModal')" 
                class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 font-medium">
          Cancel
        </button>
        <button type="submit" 
                class="px-6 py-3 rounded-xl bg-purple-600 text-white hover:bg-purple-700 font-medium shadow-lg flex items-center gap-2">
          <i data-feather="check-square" class="w-4 h-4"></i>
          <span>Confirm Group Return</span>
        </button>
      </div>
    </form>
  </div>
</div>