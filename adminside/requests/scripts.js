// Generic modal functions
function openModal(id) {
  document.getElementById(id).classList.remove('hidden');
  document.getElementById(id).classList.add('flex');
}

function closeModal(id) {
  document.getElementById(id).classList.add('hidden');
  document.getElementById(id).classList.remove('flex');
}

// Approval Modal
function openApprovalModal(data) {
  document.getElementById('approvalId').value = data.id;
  document.getElementById('approval_user_name').textContent = data.user_name;
  document.getElementById('approval_user_email').textContent = data.user_email;
  document.getElementById('approval_user_address').textContent = data.address || 'Not provided';
  document.getElementById('approval_equipment').textContent = data.equipment_name;
  document.getElementById('approval_qty').textContent = data.qty;
  
  const borrow = new Date(data.borrow_date).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'});
  const ret = new Date(data.return_date).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'});
  document.getElementById('approval_period').textContent = borrow + ' to ' + ret;
  
  openModal('approvalModal');
}

// Reject Modal
function openRejectModal(id) {
  document.getElementById('rejectId').value = id;
  openModal('rejectModal');
}

// Delivery Modal
function openDeliveryModal(id) {
  document.getElementById('deliveryId').value = id;
  openModal('deliveryModal');
}

// Return Approval Modal
function openReturnApprovalModal(id) {
  document.getElementById('returnApprovalId').value = id;
  openModal('returnApprovalModal');
}

// Return Modal
function openReturnModal(id) {
  document.getElementById('returnId').value = id;
  document.getElementById('damageCheckbox').checked = false;
  document.getElementById('damageFields').classList.add('hidden');
  openModal('returnModal');
}

// Toggle damage fields
function toggleDamageFields() {
  const checkbox = document.getElementById('damageCheckbox');
  const fields = document.getElementById('damageFields');
  if (checkbox.checked) {
    fields.classList.remove('hidden');
  } else {
    fields.classList.add('hidden');
  }
}

// Photo Viewer
function viewPhoto(path, title) {
  document.getElementById('photoTitle').textContent = title;
  document.getElementById('photoImage').src = path;
  openModal('photoModal');
  feather.replace();
}

// Close modal on outside click
document.querySelectorAll('.fixed').forEach(modal => {
  modal.addEventListener('click', function(e) {
    if (e.target === this) closeModal(this.id);
  });
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.fixed.flex').forEach(modal => {
      closeModal(modal.id);
    });
  }
});