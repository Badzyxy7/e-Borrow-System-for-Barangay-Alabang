<!-- Approval Modal -->
<div id="approvalModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
  <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-lg mx-4">
    <div class="flex items-center gap-3 mb-6">
      <div class="p-3 bg-green-100 rounded-full">
        <i data-feather="check-circle" class="w-6 h-6 text-green-600"></i>
      </div>
      <h2 class="text-2xl font-bold text-gray-800">Confirm Approval</h2>
    </div>
    
    <!-- Admin Preview Section (NEW) -->
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
      <h3 class="font-semibold text-gray-800">User Details:</h3>
      <p class="text-sm"><span class="font-medium">Name:</span> <span id="approval_user_name"></span></p>
      <p class="text-sm"><span class="font-medium">Email:</span> <span id="approval_user_email"></span></p>
      <p class="text-sm"><span class="font-medium">Address:</span> <span id="approval_user_address"></span></p>
      <hr class="my-4">
      <h3 class="font-semibold text-gray-800">Equipment Details:</h3>
      <p class="text-sm"><span class="font-medium">Equipment:</span> <span id="approval_equipment"></span></p>
      <p class="text-sm"><span class="font-medium">Quantity:</span> <span id="approval_qty"></span></p>
      <p class="text-sm"><span class="font-medium">Period:</span> <span id="approval_period"></span></p>
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
    
    <!-- Admin Preview Section (NEW) -->
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
    
    <!-- Admin Preview Section (NEW) -->
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
  <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-2xl mx-4">
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
      </div>
      
      <div class="mb-6">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" id="damageCheckbox" name="is_damaged" onchange="toggleDamageFields()" class="w-5 h-5 text-red-600 rounded">
          <span class="font-medium text-gray-700">Item is damaged</span>
        </label>
      </div>
      
      <div id="damageFields" class="hidden space-y-4 mb-6 p-4 bg-red-50 rounded-xl">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Damage Fee (₱) *</label>
          <input type="number" name="damage_fee" step="0.01" min="0" placeholder="Enter amount" class="w-full px-4 py-3 border border-gray-300 rounded-xl">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Damage Description *</label>
          <textarea name="damage_notes" placeholder="Describe the damage..." class="w-full px-4 py-3 border border-gray-300 rounded-xl h-24"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Payment Proof Photo *</label>
          <input type="file" name="payment_photo" accept="image/*" class="w-full px-4 py-3 border border-gray-300 rounded-xl">
          <div class="mt-3 p-3 bg-blue-50 rounded-lg">
            <p class="text-sm font-medium text-blue-800">Barangay GCash: <span class="text-lg font-bold">0917-123-4567</span></p>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-3">
        <button type="button" onclick="closeModal('returnModal')" class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 font-medium">Cancel</button>
        <button type="submit" class="px-6 py-3 rounded-xl bg-purple-600 text-white hover:bg-purple-700 font-medium shadow-lg">Confirm Return</button>
      </div>
    </form>
  </div>
</div>

<!-- Photo Viewer Modal -->
<div id="photoModal" class="fixed inset-0 bg-black/90 hidden items-center justify-center z-50">
  <div class="max-w-4xl w-full p-8 mx-4">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold text-white" id="photoTitle"></h2>
      <button onclick="closeModal('photoModal')" class="text-white hover:text-gray-300">
        <i data-feather="x" class="w-8 h-8"></i>
      </button>
    </div>
    <img id="photoImage" src="" alt="Photo" class="w-full rounded-xl shadow-2xl">
  </div>
</div>