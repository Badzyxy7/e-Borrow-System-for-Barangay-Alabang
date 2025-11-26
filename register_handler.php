<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add this at the very start of handleRegistration function
if (empty($_POST)) {
    error_log("POST is empty!");
    return ['success' => false, 'error' => 'Form data was not submitted'];
}

error_log("Received POST data: " . print_r($_POST, true));
require_once 'validation.php';
require_once 'barangay_validation.php';

function handleRegistration($conn) {
    $errors = [];
    
    // Get and sanitize inputs
    $firstName = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $middleName = isset($_POST['middle_name']) ? trim($_POST['middle_name']) : '';
    $lastName = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $fullName = $firstName . " " . $middleName . " " . $lastName;
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phoneNumber = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
    $birthdate = isset($_POST['birthdate']) ? trim($_POST['birthdate']) : '';
    $barangayId = isset($_POST['barangay_id']) ? trim($_POST['barangay_id']) : '';
    $barangay = isset($_POST['barangay']) ? trim($_POST['barangay']) : '';
    $street = isset($_POST['street']) ? trim($_POST['street']) : '';
    $landmark = isset($_POST['landmark']) ? trim($_POST['landmark']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $role = "resident";
    
    // Check if any field is empty
    if (empty($firstName)) {
        $errors[] = "First name is required";
    }
    
    if (empty($middleName)) {
        $errors[] = "Middle name is required";
    }
    
    if (empty($lastName)) {
        $errors[] = "Last name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    if (empty($phoneNumber)) {
        $errors[] = "Phone number is required";
    }
    
    if (empty($birthdate)) {
        $errors[] = "Birthdate is required";
    }
    
    if (empty($barangayId)) {
        $errors[] = "Barangay ID is required";
    }
    
    if (empty($street)) {
        $errors[] = "Street is required";
    }
    
    if (empty($landmark)) {
        $errors[] = "Landmark is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    if (empty($confirmPassword)) {
        $errors[] = "Confirm password is required";
    }
    
    // If basic fields are missing, return early
    if (!empty($errors)) {
        return ['success' => false, 'error' => implode('. ', $errors)];
    }
    
    // Validate first name
    if (!empty($firstName)) {
        $firstNameValidation = validateName($firstName);
        if (!$firstNameValidation['valid']) {
            $errors[] = "First name: " . $firstNameValidation['error'];
        }
    }
    
    // Validate middle name
    if (!empty($middleName)) {
        $middleNameValidation = validateName($middleName);
        if (!$middleNameValidation['valid']) {
            $errors[] = "Middle name: " . $middleNameValidation['error'];
        }
    }
    
    // Validate last name
    if (!empty($lastName)) {
        $lastNameValidation = validateName($lastName);
        if (!$lastNameValidation['valid']) {
            $errors[] = "Last name: " . $lastNameValidation['error'];
        }
    }
    
    // Validate email
    if (!empty($email)) {
        $emailValidation = validateEmail($email);
        if (!$emailValidation['valid']) {
            $errors[] = $emailValidation['error'];
        }
    }
    
    // Validate phone number
    if (!empty($phoneNumber)) {
        $phoneValidation = validatePhoneNumber($phoneNumber);
        if (!$phoneValidation['valid']) {
            $errors[] = $phoneValidation['error'];
        } else {
            $phoneNumber = $phoneValidation['phone']; // Normalized format
        }
    }
    
    // Validate birthdate
    if (!empty($birthdate)) {
        $birthdateValidation = validateBirthdate($birthdate);
        if (!$birthdateValidation['valid']) {
            $errors[] = $birthdateValidation['error'];
        }
    }
    
    // Validate password
    if (!empty($password) && !empty($confirmPassword)) {
        if ($password !== $confirmPassword) {
            $errors[] = "Passwords do not match";
        } else {
            $passwordValidation = validatePassword($password);
            if (!$passwordValidation['valid']) {
                $errors[] = $passwordValidation['error'];
            }
        }
    }
    
    // Validate Barangay ID format
    if (!empty($barangayId)) {
        $formatValidation = validateBarangayIdFormat($barangayId);
        if (!$formatValidation['valid']) {
            $errors[] = $formatValidation['error'];
        }
    }
    
    // Return all validation errors if any exist before checking barangay records
    if (!empty($errors)) {
        return ['success' => false, 'error' => implode('. ', $errors)];
    }
    
    // If basic validations pass, check barangay records
    $barangayValidation = validateBarangayResident($barangayId, $fullName, $birthdate);
    if (!$barangayValidation['valid']) {
        $_SESSION['register_attempts']++;
        
        if ($_SESSION['register_attempts'] >= 3) {
            $_SESSION['blocked_until'] = time() + (10 * 60);
            return [
                'success' => false, 
                'error' => 'Too many failed attempts. You are blocked for 10 minutes.',
                'blocked' => true
            ];
        }
        
        $remaining = 3 - $_SESSION['register_attempts'];
        return [
            'success' => false,
            'error' => $barangayValidation['error'] . " ($remaining attempts left)"
        ];
    }
    
    // Hash password
    $hashedPassword = md5($password);
    
    // Escape strings for SQL
    $fullName = mysqli_real_escape_string($conn, $fullName);
    $email = mysqli_real_escape_string($conn, $email);
    $phoneNumber = mysqli_real_escape_string($conn, $phoneNumber);
    $birthdate = mysqli_real_escape_string($conn, $birthdate);
    $barangayId = mysqli_real_escape_string($conn, $barangayId);
    $barangay = mysqli_real_escape_string($conn, $barangay);
    $street = mysqli_real_escape_string($conn, $street);
    $landmark = mysqli_real_escape_string($conn, $landmark);
    
    // Check if user already exists
    $check = "SELECT * FROM users WHERE (name='$fullName' OR email='$email') LIMIT 1";
    $result = $conn->query($check);
    
    if ($result && $result->num_rows > 0) {
        $existing = $result->fetch_assoc();
        
        // Allow re-registration if unverified
        if ($existing['is_verified'] == 0 && $existing['email'] === $email) {
            return updateUnverifiedUser($conn, $email, $fullName, $hashedPassword, $phoneNumber, $birthdate, $barangayId, $barangay, $street, $landmark);
        }
        
        // Block if verified user exists
        if ($existing['name'] === $fullName) {
            return ['success' => false, 'error' => 'This name is already taken'];
        }
        if ($existing['email'] === $email) {
            return ['success' => false, 'error' => 'This email is already registered'];
        }
    }
    
    // Insert new user
    return insertNewUser($conn, $fullName, $email, $hashedPassword, $phoneNumber, $birthdate, $role, $barangayId, $barangay, $street, $landmark);
}

function updateUnverifiedUser($conn, $email, $fullName, $password, $phoneNumber, $birthdate, $barangayId, $barangay, $street, $landmark) {
    $otp_code = sprintf("%06d", mt_rand(1, 999999));
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+2 minutes'));
    
    // Escape strings
    $email = mysqli_real_escape_string($conn, $email);
    $fullName = mysqli_real_escape_string($conn, $fullName);
    $phoneNumber = mysqli_real_escape_string($conn, $phoneNumber);
    $birthdate = mysqli_real_escape_string($conn, $birthdate);
    $barangayId = mysqli_real_escape_string($conn, $barangayId);
    $barangay = mysqli_real_escape_string($conn, $barangay);
    $street = mysqli_real_escape_string($conn, $street);
    $landmark = mysqli_real_escape_string($conn, $landmark);
    
    $sql = "UPDATE users SET 
            name='$fullName', 
            password='$password',
            phone_number='$phoneNumber',
            birthdate='$birthdate',
            barangay_id='$barangayId',
            barangay='$barangay',
            street='$street',
            landmark='$landmark',
            otp_code='$otp_code',
            otp_expiry='$otp_expiry'
            WHERE email='$email'";
    
    if ($conn->query($sql) === TRUE) {
        require_once 'email_config.php';
        $emailSent = sendOTP($email, $fullName, $otp_code);
        
        if ($emailSent) {
            $_SESSION['register_email'] = $email;
            $_SESSION['register_attempts'] = 0;
            $_SESSION['blocked_until'] = 0;
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => 'Failed to send verification email. Please try again.'];
        }
    }
    
    return ['success' => false, 'error' => 'Database error: ' . $conn->error];
}

function insertNewUser($conn, $fullName, $email, $password, $phoneNumber, $birthdate, $role, $barangayId, $barangay, $street, $landmark) {
    $otp_code = sprintf("%06d", mt_rand(1, 999999));
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+2 minutes'));
    
    // Escape strings
    $fullName = mysqli_real_escape_string($conn, $fullName);
    $email = mysqli_real_escape_string($conn, $email);
    $phoneNumber = mysqli_real_escape_string($conn, $phoneNumber);
    $birthdate = mysqli_real_escape_string($conn, $birthdate);
    $role = mysqli_real_escape_string($conn, $role);
    $barangayId = mysqli_real_escape_string($conn, $barangayId);
    $barangay = mysqli_real_escape_string($conn, $barangay);
    $street = mysqli_real_escape_string($conn, $street);
    $landmark = mysqli_real_escape_string($conn, $landmark);
    
    $sql = "INSERT INTO users (name, email, phone_number, birthdate, password, role, barangay_id, barangay, street, landmark, otp_code, otp_expiry, is_verified) 
            VALUES ('$fullName', '$email', '$phoneNumber', '$birthdate', '$password', '$role', '$barangayId', '$barangay', '$street', '$landmark', '$otp_code', '$otp_expiry', 0)";
    
    if ($conn->query($sql) === TRUE) {
        require_once 'email_config.php';
        $emailSent = sendOTP($email, $fullName, $otp_code);
        
        if ($emailSent) {
            $_SESSION['register_email'] = $email;
            $_SESSION['register_attempts'] = 0;
            $_SESSION['blocked_until'] = 0;
            return ['success' => true];
        } else {
            $conn->query("DELETE FROM users WHERE email='$email'");
            return ['success' => false, 'error' => 'Failed to send verification email. Please try again.'];
        }
    }
    
    return ['success' => false, 'error' => 'Database error: ' . $conn->error];
}

?>