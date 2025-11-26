<!-- Add User Modal -->
<div id="addUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center gap-3 mb-6">
            <div class="p-3 bg-blue-100 rounded-full">
                <i data-feather="user-plus" class="w-6 h-6 text-blue-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Add New User</h2>
        </div>
        <form id="addUserForm" method="POST" action="users_handler.php">
            <input type="hidden" name="action" value="add_user">
            
            <!-- Required Fields Section -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 pb-2 border-b">Required Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                        <input type="text" name="first_name" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                               placeholder="Juan">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                        <input type="text" name="middle_name" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                               placeholder="Santos">
                        <p class="text-xs text-gray-500 mt-1">Optional</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                        <input type="text" name="last_name" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                               placeholder="Dela Cruz">
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                        <input type="email" name="email" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                        <input type="password" name="password" required minlength="6" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                        <select name="role" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                            <option value="resident">Resident</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Optional Fields Section -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 pb-2 border-b">Additional Information (Optional)</h3>
                <p class="text-sm text-gray-500 mb-4">Note: Residents must complete address information before submitting requests.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" name="phone_number" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                               placeholder="09XX XXX XXXX">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Birthdate</label>
                        <input type="date" name="birthdate" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Barangay</label>
                        <input type="text" name="barangay" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                               placeholder="e.g., Alabang">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Street</label>
                        <input type="text" name="street" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                               placeholder="e.g., Acacia Avenue">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Landmark</label>
                        <input type="text" name="landmark" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                               placeholder="e.g., Near Alabang Town Center">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeAddUserModal()" 
                        class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-900 to-blue-700 text-white hover:bg-blue-700 font-medium transition shadow-lg">
                    Add User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- OTP Verification Modal -->
<div id="otpModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md">
        <div class="flex items-center gap-3 mb-6">
            <div class="p-3 bg-green-100 rounded-full">
                <i data-feather="mail" class="w-6 h-6 text-green-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Verify Email</h2>
        </div>
        
        <p class="text-gray-600 mb-4">A 6-digit verification code has been sent to:</p>
        <p class="text-blue-600 font-semibold mb-6" id="otpEmailDisplay"></p>
        
        <form id="otpForm" method="POST" action="users_handler.php">
            <input type="hidden" name="action" value="verify_otp">
            <input type="hidden" name="email" id="otpEmail">
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Enter OTP Code</label>
                <input type="text" name="otp_code" id="otpCode" required maxlength="6" pattern="\d{6}"
                       class="w-full px-4 py-4 border border-gray-300 rounded-xl text-center text-2xl font-bold tracking-widest focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                       placeholder="000000">
                <p class="text-xs text-gray-500 mt-2">Code expires in 2 minutes</p>
            </div>

            <div id="otpMessage" class="mb-4 hidden"></div>

            <div class="flex flex-col gap-3">
                <button type="submit" 
                        class="w-full px-6 py-3 rounded-xl bg-gradient-to-r from-blue-900 to-blue-700 text-white hover:bg-blue-700 font-medium transition shadow-lg">
                    Verify OTP
                </button>
                <button type="button" onclick="resendOTP()" 
                        class="w-full px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium transition">
                    Resend OTP
                </button>
            </div>
        </form>
    </div>
</div>

<!-- User Details Modal -->
<div id="userDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i data-feather="user" class="w-6 h-6 text-blue-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">User Details</h2>
            </div>
            <button onclick="closeUserDetailsModal()" class="text-gray-400 hover:text-gray-600">
                <i data-feather="x" class="w-6 h-6"></i>
            </button>
        </div>

        <div id="userDetailsContent">
            <!-- Content will be loaded here via JavaScript -->
        </div>
    </div>
</div>

<!-- Duplicate Email Modal -->
<div id="duplicateEmailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md">
        <div class="flex items-center gap-3 mb-6">
            <div class="p-3 bg-red-100 rounded-full">
                <i data-feather="alert-circle" class="w-6 h-6 text-red-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-red-600">Duplicate Email</h2>
        </div>
        <p class="mb-6 text-gray-600">The email address you entered already exists in the system. Please use a different email address.</p>
        <div class="flex justify-end">
            <button onclick="closeDuplicateEmailModal()" 
                    class="px-6 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-medium transition shadow-lg">
                OK
            </button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md">
        <div class="flex items-center gap-3 mb-6">
            <div class="p-3 bg-green-100 rounded-full">
                <i data-feather="check-circle" class="w-6 h-6 text-green-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-green-600">Success!</h2>
        </div>
        <p class="mb-6 text-gray-600" id="successMessage"></p>
        <div class="flex justify-end">
            <button onclick="closeSuccessModal()" 
                    class="px-6 py-3 rounded-xl bg-green-600 text-white hover:bg-green-700 font-medium transition shadow-lg">
                OK
            </button>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md">
        <div class="flex items-center gap-3 mb-6">
            <div class="p-3 bg-red-100 rounded-full">
                <i data-feather="x-circle" class="w-6 h-6 text-red-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-red-600">Error</h2>
        </div>
        <p class="mb-6 text-gray-600" id="errorMessage"></p>
        <div class="flex justify-end">
            <button onclick="closeErrorModal()" 
                    class="px-6 py-3 rounded-xl bg-red-600 text-white hover:bg-red-700 font-medium transition shadow-lg">
                OK
            </button>
        </div>
    </div>
</div>