// Generic modal functions
function openModal(id) {
  const modal = document.getElementById(id);
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  document.body.style.overflow = 'hidden'; // Prevent background scroll
  setTimeout(() => feather.replace(), 10); // Small delay to ensure DOM is ready
}

function closeModal(id) {
  const modal = document.getElementById(id);
  modal.classList.add('hidden');
  modal.classList.remove('flex');
  document.body.style.overflow = ''; // Restore scroll
}

// Photo Viewer with enhanced functionality
function viewPhoto(path, title) {
  document.getElementById('photoTitle').textContent = title || 'Photo';
  document.getElementById('photoImage').src = path;
  openModal('photoModal');
}

// Unified Details Modal Function
function openDetailsModal(data, tabType) {
  // Populate Equipment Details
  const imagePath = '../photos/' + data.equipment_photo.split('/').pop();
  document.getElementById('detail_equipment_photo').src = imagePath;
  document.getElementById('detail_equipment_name').textContent = data.equipment_name;
  document.getElementById('detail_equipment_desc').textContent = data.equipment_desc || 'No description';
  document.getElementById('detail_qty').textContent = data.qty;

  // Populate Borrower Information
  document.getElementById('detail_user_name').textContent = data.user_name;
  document.getElementById('detail_user_email').textContent = data.user_email;
  document.getElementById('detail_address').textContent = data.address || 'Not provided';

  // Populate Purpose Section
  const purposeSection = document.getElementById('detail_purpose_section');
  const purposeText = document.getElementById('detail_purpose');
  const certificateBtn = document.getElementById('detail_certificate_btn');
  
  if (data.purpose) {
    purposeText.textContent = data.purpose;
    purposeSection.classList.remove('hidden');
    
    // Show death certificate button if applicable
    if (data.purpose.toLowerCase() === 'funeral/lamay' && data.death_certificate) {
      certificateBtn.classList.remove('hidden');
      certificateBtn.onclick = () => viewPhoto('../death_certificates/' + data.death_certificate, 'Death Certificate');
    } else {
      certificateBtn.classList.add('hidden');
    }
  } else {
    purposeSection.classList.add('hidden');
  }

  // Populate Timeline
  document.getElementById('detail_created_at').textContent = formatDateTime(data.created_at);
  document.getElementById('detail_borrow_date').textContent = formatDate(data.borrow_date);
  document.getElementById('detail_return_date').textContent = formatDate(data.return_date);
  
  const days = Math.ceil((new Date(data.return_date) - new Date(data.borrow_date)) / (1000 * 60 * 60 * 24));
  document.getElementById('detail_duration').textContent = days + ' days';

  // Populate Audit Trail
  const auditSection = document.getElementById('detail_audit_section');
  const auditContent = document.getElementById('detail_audit_content');
  let auditHtml = '';

  if (data.approved_by_name) {
    auditHtml += `
      <div class="flex items-start gap-2">
        <i data-feather="check-circle" class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0"></i>
        <div>
          <div class="font-medium text-gray-700">Approved by:</div>
          <div class="text-gray-900">${escapeHtml(data.approved_by_name)}</div>
          ${data.approved_at ? `<div class="text-xs text-gray-500">${formatDateTime(data.approved_at)}</div>` : ''}
        </div>
      </div>`;
  }

  if (data.delivered_by_name) {
    auditHtml += `
      <div class="flex items-start gap-2 pt-2 border-t border-indigo-200">
        <i data-feather="truck" class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0"></i>
        <div>
          <div class="font-medium text-gray-700">Delivered by:</div>
          <div class="text-gray-900">${escapeHtml(data.delivered_by_name)}</div>
          ${data.delivered_at ? `<div class="text-xs text-gray-500">${formatDateTime(data.delivered_at)}</div>` : ''}
        </div>
      </div>`;
  }

  if (data.returned_by_name) {
    auditHtml += `
      <div class="flex items-start gap-2 pt-2 border-t border-indigo-200">
        <i data-feather="corner-down-left" class="w-4 h-4 text-purple-600 mt-0.5 flex-shrink-0"></i>
        <div>
          <div class="font-medium text-gray-700">Returned by:</div>
          <div class="text-gray-900">${escapeHtml(data.returned_by_name)}</div>
          ${data.returned_at ? `<div class="text-xs text-gray-500">${formatDateTime(data.returned_at)}</div>` : ''}
        </div>
      </div>`;
  }

  if (auditHtml) {
    auditContent.innerHTML = auditHtml;
    auditSection.classList.remove('hidden');
  } else {
    auditSection.classList.add('hidden');
  }

  // Handle Photos Section
  const photosSection = document.getElementById('detail_photos_section');
  const deliveryPhotoBtn = document.getElementById('detail_delivery_photo_btn');
  const returnPhotoBtn = document.getElementById('detail_return_photo_btn');
  
  let hasPhotos = false;
  
  if (data.delivery_photo) {
    deliveryPhotoBtn.classList.remove('hidden');
    deliveryPhotoBtn.onclick = () => viewPhoto(data.delivery_photo, 'Delivery Proof');
    hasPhotos = true;
  } else {
    deliveryPhotoBtn.classList.add('hidden');
  }
  
  if (data.return_photo) {
    returnPhotoBtn.classList.remove('hidden');
    returnPhotoBtn.onclick = () => viewPhoto(data.return_photo, 'Return Proof');
    hasPhotos = true;
  } else {
    returnPhotoBtn.classList.add('hidden');
  }
  
  if (hasPhotos) {
    photosSection.classList.remove('hidden');
  } else {
    photosSection.classList.add('hidden');
  }

  // Generate Action Buttons based on tab type
  const actionsDiv = document.getElementById('detail_actions');
  let actionsHtml = '<div class="flex justify-end gap-3">';
  
  switch(tabType) {
    case 'pending':
      actionsHtml += `
        <button onclick="closeModal('detailsModal')" 
                class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 font-medium">Close</button>
        <button onclick='closeModal("detailsModal"); openApprovalModal(${JSON.stringify(data)})' 
                class="px-6 py-3 rounded-xl bg-green-600 text-white hover:bg-green-700 font-medium flex items-center gap-2">
          <i data-feather="check" class="w-4 h-4"></i>
          <span>Approve Request</span>
        </button>
        <button onclick='closeModal("detailsModal"); openRejectModal(${data.id})' 
                class="px-6 py-3 rounded-xl bg-red-600 text-white hover:bg-red-700 font-medium flex items-center gap-2">
          <i data-feather="x" class="w-4 h-4"></i>
          <span>Reject Request</span>
        </button>`;
      break;
      
    case 'approved':
      actionsHtml += `
        <button onclick="closeModal('detailsModal')" 
                class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 font-medium">Close</button>
        <button onclick='closeModal("detailsModal"); openDeliveryModal(${data.id})' 
                class="px-6 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-medium flex items-center gap-2">
          <i data-feather="truck" class="w-4 h-4"></i>
          <span>Mark as Delivered</span>
        </button>`;
      break;
      
    case 'delivered':
      actionsHtml += `
        <button onclick="closeModal('detailsModal')" 
                class="px-6 py-3 rounded-xl bg-gray-600 hover:bg-gray-700 text-white font-medium">Close</button>`;
      break;
      
    case 'return_requests':
      if (data.return_approved == 1) {
        actionsHtml += `
          <button onclick="closeModal('detailsModal')" 
                  class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 font-medium">Close</button>
          <button onclick='closeModal("detailsModal"); openReturnModal(${data.id})' 
                  class="px-6 py-3 rounded-xl bg-purple-600 text-white hover:bg-purple-700 font-medium flex items-center gap-2">
            <i data-feather="check-square" class="w-4 h-4"></i>
            <span>Mark as Returned</span>
          </button>`;
      } else {
        actionsHtml += `
          <button onclick="closeModal('detailsModal')" 
                  class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 font-medium">Close</button>
          <button onclick='closeModal("detailsModal"); openReturnApprovalModal(${data.id})' 
                  class="px-6 py-3 rounded-xl bg-orange-600 text-white hover:bg-orange-700 font-medium flex items-center gap-2">
            <i data-feather="check" class="w-4 h-4"></i>
            <span>Approve Return</span>
          </button>`;
      }
      break;
      
    case 'returned':
      actionsHtml += `
        <button onclick="closeModal('detailsModal')" 
                class="px-6 py-3 rounded-xl bg-green-600 hover:bg-green-700 text-white font-medium">Close</button>`;
      break;
  }
  
  actionsHtml += '</div>';
  actionsDiv.innerHTML = actionsHtml;

  openModal('detailsModal');
}

// Existing modal functions
function openApprovalModal(data) {
  document.getElementById('approvalId').value = data.id;
  document.getElementById('approval_user_name').textContent = data.user_name;
  document.getElementById('approval_equipment').textContent = data.equipment_name;
  openModal('approvalModal');
}

function openRejectModal(id) {
  document.getElementById('rejectId').value = id;
  openModal('rejectModal');
}

function openDeliveryModal(id) {
  document.getElementById('deliveryId').value = id;
  openModal('deliveryModal');
}

function openReturnApprovalModal(id) {
  document.getElementById('returnApprovalId').value = id;
  openModal('returnApprovalModal');
}

function openReturnModal(id) {
  document.getElementById('returnId').value = id;
  openModal('returnModal');
}

// Helper functions
function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatDateTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + 
         ' at ' + date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Close modal on outside click
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('fixed') && e.target.classList.contains('bg-black')) {
    const modalId = e.target.id;
    if (modalId) closeModal(modalId);
  }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.fixed.flex').forEach(modal => {
      closeModal(modal.id);
    });
  }
});

// ============================================
// GROUP REQUEST FUNCTIONS
// ============================================

/**
 * Open Group Details Modal
 * @param {Object} groupData - Object containing main data and items array
 * @param {string} tabType - Current tab (e.g., 'pending', 'approved')
 */
function openGroupDetailsModal(groupData, tabType) {
  const mainData = groupData.main;
  const items = groupData.items;
  const itemCount = items.length;
  
  // Set header count
  document.getElementById('group_detail_count').textContent = itemCount + ' items';
  
  // Show priority badge if applicable
  const priorityBadge = document.getElementById('group_detail_priority_badge');
  if (mainData.priority == 1) {
    priorityBadge.classList.remove('hidden');
  } else {
    priorityBadge.classList.add('hidden');
  }
  
  // Populate borrower info
  document.getElementById('group_detail_user_name').textContent = mainData.user_name;
  document.getElementById('group_detail_user_email').textContent = mainData.user_email;
  document.getElementById('group_detail_address').textContent = mainData.address || 'Not provided';
  
  // Populate purpose
  document.getElementById('group_detail_purpose').textContent = mainData.purpose || 'Not specified';
  
  // Show death certificate button if applicable
  const certificateBtn = document.getElementById('group_detail_certificate_btn');
  if (mainData.purpose && mainData.purpose.toLowerCase() === 'funeral/lamay' && mainData.death_certificate) {
    certificateBtn.classList.remove('hidden');
    certificateBtn.onclick = () => viewPhoto('../death_certificates/' + mainData.death_certificate, 'Death Certificate');
  } else {
    certificateBtn.classList.add('hidden');
  }
  
  // Populate timeline
  document.getElementById('group_detail_created_at').textContent = formatDateTime(mainData.created_at);
  document.getElementById('group_detail_borrow_date').textContent = formatDate(mainData.borrow_date);
  document.getElementById('group_detail_return_date').textContent = formatDate(mainData.return_date);
  
  const days = Math.ceil((new Date(mainData.return_date) - new Date(mainData.borrow_date)) / (1000 * 60 * 60 * 24));
  document.getElementById('group_detail_duration').textContent = days + ' days';
  
  // Populate equipment items
  document.getElementById('group_detail_items_count').textContent = '(' + itemCount + ')';
  const itemsContainer = document.getElementById('group_detail_items_container');
  itemsContainer.innerHTML = '';
  
  items.forEach((item, index) => {
    const imagePath = '../photos/' + item.equipment_photo.split('/').pop();
    const itemHtml = `
      <div class="flex items-center gap-4 p-3 bg-white rounded-lg border-2 border-blue-100 hover:border-blue-300 transition">
        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center font-bold text-blue-700 text-sm">
          ${index + 1}
        </div>
        <img src="${escapeHtml(imagePath)}" 
             alt="Equipment" 
             class="w-16 h-16 object-cover rounded-lg cursor-pointer border-2 border-gray-200"
             onclick="viewPhoto('${escapeHtml(imagePath)}', '${escapeHtml(item.equipment_name)}')"
             onerror="this.src='../photos/placeholder.png'">
        <div class="flex-1">
          <h5 class="font-semibold text-gray-900">${escapeHtml(item.equipment_name)}</h5>
          <p class="text-xs text-gray-600 mt-0.5">${escapeHtml(item.equipment_desc || 'No description')}</p>
          <div class="flex items-center gap-4 mt-2 text-xs">
            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded font-medium">
              Qty: ${item.qty}
            </span>
          </div>
        </div>
      </div>
    `;
    itemsContainer.insertAdjacentHTML('beforeend', itemHtml);
  });
  
  // Show description if provided
  const descSection = document.getElementById('group_detail_description_section');
  const descText = document.getElementById('group_detail_description');
  if (mainData.description && mainData.description.trim()) {
    descText.textContent = mainData.description;
    descSection.classList.remove('hidden');
  } else {
    descSection.classList.add('hidden');
  }
  
  // Set up action buttons based on tab type
  const approveBtn = document.getElementById('group_detail_approve_btn');
  const rejectBtn = document.getElementById('group_detail_reject_btn');
  
  if (tabType === 'pending') {
    approveBtn.onclick = () => {
      closeModal('groupDetailsModal');
      openGroupApprovalModal(items, mainData.group_request_id);
    };
    rejectBtn.onclick = () => {
      closeModal('groupDetailsModal');
      openGroupRejectModal(mainData.group_request_id, items.map(i => i.id));
    };
    approveBtn.classList.remove('hidden');
    rejectBtn.classList.remove('hidden');
  } else {
    approveBtn.classList.add('hidden');
    rejectBtn.classList.add('hidden');
  }
  
  openModal('groupDetailsModal');
}

/**
 * Open Group Approval Modal
 * @param {Array} items - Array of request items
 * @param {string} groupId - Group request ID
 */
function openGroupApprovalModal(items, groupId) {
  const itemCount = items.length;
  
  document.getElementById('group_approval_count').textContent = itemCount;
  document.getElementById('groupApprovalGroupId').value = groupId;
  document.getElementById('groupApprovalRequestIds').value = JSON.stringify(items.map(i => i.id));
  
  // Populate items list
  const itemsContainer = document.getElementById('group_approval_items');
  itemsContainer.innerHTML = '';
  
  items.forEach((item, index) => {
    const itemHtml = `
      <div class="flex items-center gap-3 text-sm p-2 bg-white rounded border border-gray-200">
        <span class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center font-semibold text-green-700 text-xs">
          ${index + 1}
        </span>
        <div class="flex-1">
          <span class="font-medium text-gray-900">${escapeHtml(item.equipment_name)}</span>
          <span class="text-gray-500 ml-2">× ${item.qty}</span>
        </div>
      </div>
    `;
    itemsContainer.insertAdjacentHTML('beforeend', itemHtml);
  });
  
  openModal('groupApprovalModal');
}

/**
 * Open Group Reject Modal
 * @param {string} groupId - Group request ID
 * @param {Array} requestIds - Array of request IDs to reject
 */
function openGroupRejectModal(groupId, requestIds) {
  document.getElementById('group_reject_count').textContent = requestIds.length;
  document.getElementById('groupRejectGroupId').value = groupId;
  document.getElementById('groupRejectRequestIds').value = JSON.stringify(requestIds);
  
  openModal('groupRejectModal');
}

/**
 * Open Group Delivery Modal
 * @param {string} groupId - Group request ID
 * @param {Array} items - Array of items to deliver
 */
function openGroupDeliveryModal(groupId, items) {
  const itemCount = items.length;
  
  document.getElementById('group_delivery_count').textContent = itemCount;
  document.getElementById('groupDeliveryGroupId').value = groupId;
  document.getElementById('groupDeliveryRequestIds').value = JSON.stringify(items.map(i => i.id));
  
  // Populate items list
  const itemsContainer = document.getElementById('group_delivery_items');
  itemsContainer.innerHTML = '';
  
  items.forEach((item, index) => {
    const itemHtml = `
      <div class="flex items-center gap-3 text-sm p-2 bg-white rounded border border-gray-200">
        <span class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center font-semibold text-blue-700 text-xs">
          ${index + 1}
        </span>
        <div class="flex-1">
          <span class="font-medium text-gray-900">${escapeHtml(item.equipment_name)}</span>
          <span class="text-gray-500 ml-2">× ${item.qty}</span>
        </div>
      </div>
    `;
    itemsContainer.insertAdjacentHTML('beforeend', itemHtml);
  });
  
  openModal('groupDeliveryModal');
}

// ============================================
// GROUP RETURN REQUEST FUNCTIONS
// Add these to your existing JavaScript file
// ============================================

/**
 * Open Group Return Approval Modal
 * @param {string} groupId - Group request ID
 * @param {number} itemCount - Number of items in group
 */
function openGroupReturnApprovalModal(groupId, itemCount) {
  document.getElementById('group_return_approval_count').textContent = itemCount;
  document.getElementById('groupReturnApprovalGroupId').value = groupId;
  openModal('groupReturnApprovalModal');
}

/**
 * Open Group Return Marking Modal (Mark all as returned)
 * @param {string} groupId - Group request ID
 * @param {number} itemCount - Number of items in group
 */
function openGroupReturnModal(groupId, itemCount) {
  document.getElementById('group_return_mark_count').textContent = itemCount;
  document.getElementById('groupReturnMarkGroupId').value = groupId;
  openModal('groupReturnMarkModal');
}
