<?php

/**
 * Validate name fields - only letters, spaces, and apostrophes allowed
 * No special characters like -, #, etc.
 */
function validateName($name) {
    $name = trim($name);
    
    if (empty($name)) {
        return ['valid' => false, 'error' => 'Name cannot be empty'];
    }
    
    // Only allow letters (including accented), spaces, and apostrophes
    if (!preg_match("/^[a-zA-ZÀ-ÿ\s']+$/u", $name)) {
        return ['valid' => false, 'error' => 'Name can only contain letters, spaces, and apostrophes'];
    }
    
    return ['valid' => true];
}

/**
 * Validate password - at least 1 uppercase, 1 lowercase, 1 number
 * No special characters allowed, minimum 8 characters
 */
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'at least 8 characters';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = '1 uppercase letter';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = '1 lowercase letter';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = '1 number';
    }
    
    // Check for special characters (not allowed)
    if (preg_match('/[^a-zA-Z0-9]/', $password)) {
        return ['valid' => false, 'error' => 'Password cannot contain special characters'];
    }
    
    if (!empty($errors)) {
        return ['valid' => false, 'error' => 'Password must contain ' . implode(', ', $errors)];
    }
    
    return ['valid' => true];
}

/**
 * Validate Philippine phone number
 * Accepts formats: 09123456789 or +639123456789
 */
function validatePhoneNumber($phone) {
    $phone = trim($phone);
    
    // Remove spaces and dashes
    $phone = preg_replace('/[\s\-]/', '', $phone);
    
    // Check if it matches Philippine format
    if (preg_match('/^(09|\+639)\d{9}$/', $phone)) {
        // Normalize to 09 format
        if (strpos($phone, '+63') === 0) {
            $phone = '0' . substr($phone, 3);
        }
        return ['valid' => true, 'phone' => $phone];
    }
    
    return ['valid' => false, 'error' => 'Invalid Philippine phone number format'];
}

/**
 * Validate birthdate - must be at least 18 years old
 */
function validateBirthdate($birthdate) {
    if (empty($birthdate)) {
        return ['valid' => false, 'error' => 'Birthdate is required'];
    }
    
    $date = DateTime::createFromFormat('Y-m-d', $birthdate);
    
    if (!$date) {
        return ['valid' => false, 'error' => 'Invalid date format'];
    }
    
    $today = new DateTime();
    $age = $today->diff($date)->y;
    
    if ($age < 18) {
        return ['valid' => false, 'error' => 'You must be at least 18 years old to register'];
    }
    
    if ($age > 120) {
        return ['valid' => false, 'error' => 'Invalid birthdate'];
    }
    
    return ['valid' => true];
}

/**
 * Validate email - Gmail only
 */
function validateEmail($email) {
    $email = trim($email);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'error' => 'Invalid email format'];
    }
    
    if (!preg_match("/@gmail\.com$/i", $email)) {
        return ['valid' => false, 'error' => 'Only Gmail accounts are allowed'];
    }
    
    return ['valid' => true];
}

?>