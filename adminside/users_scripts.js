// Modal Functions
function openAddUserModal() {
    document.getElementById('addUserModal').classList.remove('hidden');
    document.getElementById('addUserModal').classList.add('flex');
}

function closeAddUserModal() {
    document.getElementById('addUserModal').classList.add('hidden');
    document.getElementById('addUserModal').classList.remove('flex');
    document.getElementById('addUserForm').reset();
}

function openOTPModal(email) {
    document.getElementById('otpEmail').value = email;
    document.getElementById('otpEmailDisplay').textContent = email;
    document.getElementById('otpCode').value = '';
    document.getElementById('otpMessage').classList.add('hidden');
    
    closeAddUserModal();
    document.getElementById('otpModal').classList.remove('hidden');
    document.getElementById('otpModal').classList.add('flex');
}

function closeOTPModal() {
    document.getElementById('otpModal').classList.add('hidden');
    document.getElementById('otpModal').classList.remove('flex');
}

function openUserDetailsModal(userId) {
    document.getElementById('userDetailsModal').classList.remove('hidden');
    document.getElementById('userDetailsModal').classList.add('flex');
    loadUserDetails(userId);
}

function closeUserDetailsModal() {
    document.getElementById('userDetailsModal').classList.add('hidden');
    document.getElementById('userDetailsModal').classList.remove('flex');
}

function closeDuplicateEmailModal() {
    document.getElementById('duplicateEmailModal').classList.add('hidden');
    document.getElementById('duplicateEmailModal').classList.remove('flex');
    openAddUserModal();
}

function showSuccessModal(message) {
    document.getElementById('successMessage').textContent = message;
    document.getElementById('successModal').classList.remove('hidden');
    document.getElementById('successModal').classList.add('flex');
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
    document.getElementById('successModal').classList.remove('flex');
    location.reload();
}

function showErrorModal(message) {
    document.getElementById('errorMessage').textContent = message;
    document.getElementById('errorModal').classList.remove('hidden');
    document.getElementById('errorModal').classList.add('flex');
}

function closeErrorModal() {
    document.getElementById('errorModal').classList.add('hidden');
    document.getElementById('errorModal').classList.remove('flex');
}

// Add User Form Submit
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Combine first, middle, and last name into single 'name' field
    const firstName = formData.get('first_name').trim();
    const middleName = formData.get('middle_name').trim();
    const lastName = formData.get('last_name').trim();
    
    // Create full name (include middle name only if provided)
    let fullName = firstName;
    if (middleName) {
        fullName += ' ' + middleName;
    }
    fullName += ' ' + lastName;
    
    // Remove the separate name fields and add combined name
    formData.delete('first_name');
    formData.delete('middle_name');
    formData.delete('last_name');
    formData.append('name', fullName);
    
    fetch('users_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            openOTPModal(data.email);
        } else {
            if (data.message.includes('already exists')) {
                closeAddUserModal();
                document.getElementById('duplicateEmailModal').classList.remove('hidden');
                document.getElementById('duplicateEmailModal').classList.add('flex');
            } else {
                showErrorModal(data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorModal('An error occurred. Please try again.');
    });
});

// OTP Form Submit
document.getElementById('otpForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('users_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('otpMessage');
        messageDiv.classList.remove('hidden');
        
        if (data.success) {
            messageDiv.className = 'mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-xl';
            messageDiv.textContent = data.message;
            
            setTimeout(() => {
                closeOTPModal();
                showSuccessModal('User has been successfully added and verified!');
            }, 1500);
        } else {
            messageDiv.className = 'mb-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-xl';
            messageDiv.textContent = data.message;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const messageDiv = document.getElementById('otpMessage');
        messageDiv.classList.remove('hidden');
        messageDiv.className = 'mb-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-xl';
        messageDiv.textContent = 'An error occurred. Please try again.';
    });
});

// Resend OTP
function resendOTP() {
    const email = document.getElementById('otpEmail').value;
    const formData = new FormData();
    formData.append('action', 'resend_otp');
    formData.append('email', email);
    
    fetch('users_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('otpMessage');
        messageDiv.classList.remove('hidden');
        
        if (data.success) {
            messageDiv.className = 'mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-xl';
            messageDiv.textContent = data.message;
        } else {
            messageDiv.className = 'mb-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-xl';
            messageDiv.textContent = data.message;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Load User Details
function loadUserDetails(userId) {
    const formData = new FormData();
    formData.append('action', 'get_user_details');
    formData.append('user_id', userId);
    
    fetch('users_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayUserDetails(data.user);
        } else {
            showErrorModal(data.message);
            closeUserDetailsModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorModal('Failed to load user details.');
        closeUserDetailsModal();
    });
}

// Display User Details
function displayUserDetails(user) {
    const hasAvatar = user.avatar && user.avatar.trim() !== '';
    let avatarSrc = '';
    
    if (hasAvatar) {
        if (user.avatar.includes('photos/avatar/')) {
            avatarSrc = '../' + user.avatar;
        } else {
            avatarSrc = '../photos/avatars/' + user.avatar.split('/').pop();
        }
    }
    
    const roleColors = {
        'admin': 'bg-red-100 text-red-700',
        'staff': 'bg-green-100 text-green-700',
        'resident': 'bg-blue-100 text-blue-700'
    };
    
    const statusColors = {
        'active': 'bg-green-100 text-green-700',
        'inactive': 'bg-gray-100 text-gray-700'
    };
    
    const verifiedBadge = user.is_verified == 1 
        ? '<span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium flex items-center gap-1 w-fit"><i data-feather="check-circle" class="w-4 h-4"></i>Verified</span>'
        : '<span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium flex items-center gap-1 w-fit"><i data-feather="alert-circle" class="w-4 h-4"></i>Not Verified</span>';
    
    const content = `
        <div class="space-y-6">
            <!-- Profile Section -->
            <div class="flex items-center gap-6 p-6 bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl">
                ${hasAvatar 
                    ? `<img src="${avatarSrc}" alt="${user.name}" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                       <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full hidden items-center justify-center text-white text-3xl font-bold shadow-lg">${user.name.charAt(0).toUpperCase()}</div>`
                    : `<div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-3xl font-bold shadow-lg">${user.name.charAt(0).toUpperCase()}</div>`
                }
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">${user.name}</h3>
                    <p class="text-gray-600 mb-2">${user.email}</p>
                    <div class="flex gap-2">
                        <span class="px-3 py-1 ${roleColors[user.role]} rounded-full text-sm font-medium capitalize">${user.role}</span>
                        <span class="px-3 py-1 ${statusColors[user.status]} rounded-full text-sm font-medium capitalize">${user.status}</span>
                        ${verifiedBadge}
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-feather="user" class="w-5 h-5 text-blue-600"></i>
                    Personal Information
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-500">Phone Number</label>
                        <p class="text-gray-900 font-medium">${user.phone_number || 'Not provided'}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Birthdate</label>
                        <p class="text-gray-900 font-medium">${user.birthdate ? new Date(user.birthdate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'Not provided'}</p>
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-feather="map-pin" class="w-5 h-5 text-blue-600"></i>
                    Address Information
                </h4>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm text-gray-500">Barangay</label>
                        <p class="text-gray-900 font-medium">${user.barangay || 'Not provided'}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Street</label>
                        <p class="text-gray-900 font-medium">${user.street || 'Not provided'}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Landmark</label>
                        <p class="text-gray-900 font-medium">${user.landmark || 'Not provided'}</p>
                    </div>
                </div>
                ${user.role === 'resident' && (!user.barangay || !user.street) 
                    ? '<div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg"><p class="text-sm text-yellow-800 flex items-center gap-2"><i data-feather="alert-triangle" class="w-4 h-4"></i>This user cannot submit requests until address information is completed.</p></div>'
                    : ''
                }
            </div>

            <!-- Account Information -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-feather="info" class="w-5 h-5 text-blue-600"></i>
                    Account Information
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-500">Account Created</label>
                        <p class="text-gray-900 font-medium">${new Date(user.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Last Login</label>
                        <p class="text-gray-900 font-medium">${user.last_login ? new Date(user.last_login).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Never'}</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('userDetailsContent').innerHTML = content;
    feather.replace();
}

// Live Search
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        performSearch();
    }, 500);
});

document.getElementById('roleFilter').addEventListener('change', function() {
    performSearch();
});

function performSearch() {
    const search = document.getElementById('searchInput').value;
    const roleFilter = document.getElementById('roleFilter').value;
    
    const formData = new FormData();
    formData.append('action', 'search_users');
    formData.append('search', search);
    formData.append('role_filter', roleFilter);
    
    fetch('users_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateUserTable(data.users);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function updateUserTable(users) {
    const tbody = document.getElementById('usersTableBody');
    
    if (users.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                    <i data-feather="users" class="w-12 h-12 mx-auto mb-3 text-gray-400"></i>
                    <p class="text-lg">No users found.</p>
                </td>
            </tr>
        `;
        feather.replace();
        return;
    }
    
    let html = '';
    users.forEach(user => {
        const hasAvatar = user.avatar && user.avatar.trim() !== '';
        let avatarSrc = '';
        
        if (hasAvatar) {
            if (user.avatar.includes('photos/avatar/')) {
                avatarSrc = '../' + user.avatar;
            } else {
                avatarSrc = '../photos/avatars/' + user.avatar.split('/').pop();
            }
        }
        
        const roleClass = user.role === 'admin' ? 'bg-red-100 text-red-700' :
                         user.role === 'staff' ? 'bg-green-100 text-green-700' :
                         'bg-blue-100 text-blue-700';
        
        const verifiedBadge = user.is_verified == 1 
            ? '<div class="w-2 h-2 bg-green-500 rounded-full"></div>Verified'
            : '<div class="w-2 h-2 bg-yellow-500 rounded-full"></div>Pending';
        
        html += `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        ${hasAvatar 
                            ? `<img src="${avatarSrc}" alt="${user.name}" class="w-10 h-10 rounded-full object-cover border-2 border-gray-200" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                               <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full hidden items-center justify-center text-white font-semibold">${user.name.charAt(0).toUpperCase()}</div>`
                            : `<div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">${user.name.charAt(0).toUpperCase()}</div>`
                        }
                        <div>
                            <div class="font-medium text-gray-900">${user.name}</div>
                            <div class="text-sm text-gray-500">${user.email}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 ${roleClass} rounded-full text-sm font-medium capitalize">${user.role}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium flex items-center gap-1 w-fit">
                        ${verifiedBadge}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-600">
                    ${new Date(user.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}
                </td>
                <td class="px-6 py-4">
                    <div class="flex justify-center gap-2">
                        <button onclick="openUserDetailsModal(${user.id})" 
                                class="p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition shadow-sm group relative"
                                title="View Details">
                            <i data-feather="eye" class="w-4 h-4"></i>
                            <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                View Details
                            </span>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    feather.replace();
}

// Close modals when clicking outside
document.getElementById('addUserModal').addEventListener('click', function(e) {
    if (e.target === this) closeAddUserModal();
});

document.getElementById('otpModal').addEventListener('click', function(e) {
    if (e.target === this) closeOTPModal();
});

document.getElementById('userDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) closeUserDetailsModal();
});

document.getElementById('duplicateEmailModal').addEventListener('click', function(e) {
    if (e.target === this) closeDuplicateEmailModal();
});

// OTP input auto-format
document.getElementById('otpCode').addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').slice(0, 6);
});