<?php
// TEST VALIDATION FILE - Use this to debug
require_once 'validation.php';
require_once 'barangay_validation.php';

echo "<h1>Testing Validation Functions</h1>";

// Test 1: Name with special characters
echo "<h2>Test 1: Name Validation</h2>";
$result = validateName("-Hector");
echo "Testing '-Hector': ";
if (!$result['valid']) {
    echo "<span style='color: red;'>ERROR: " . $result['error'] . "</span><br>";
} else {
    echo "<span style='color: green;'>PASSED</span><br>";
}

$result = validateName("John");
echo "Testing 'John': ";
if ($result['valid']) {
    echo "<span style='color: green;'>PASSED</span><br>";
} else {
    echo "<span style='color: red;'>ERROR: " . $result['error'] . "</span><br>";
}

// Test 2: Password validation
echo "<h2>Test 2: Password Validation</h2>";
$result = validatePassword("dasdasdas");
echo "Testing 'dasdasdas': ";
if (!$result['valid']) {
    echo "<span style='color: red;'>ERROR: " . $result['error'] . "</span><br>";
} else {
    echo "<span style='color: green;'>PASSED</span><br>";
}

$result = validatePassword("Pass123!");
echo "Testing 'Pass123!': ";
if (!$result['valid']) {
    echo "<span style='color: red;'>ERROR: " . $result['error'] . "</span><br>";
} else {
    echo "<span style='color: green;'>PASSED</span><br>";
}

$result = validatePassword("Pass1234");
echo "Testing 'Pass1234': ";
if ($result['valid']) {
    echo "<span style='color: green;'>PASSED</span><br>";
} else {
    echo "<span style='color: red;'>ERROR: " . $result['error'] . "</span><br>";
}

// Test 3: Phone validation
echo "<h2>Test 3: Phone Validation</h2>";
$result = validatePhoneNumber("09300349940");
echo "Testing '09300349940': ";
if ($result['valid']) {
    echo "<span style='color: green;'>PASSED - Normalized: " . $result['phone'] . "</span><br>";
} else {
    echo "<span style='color: red;'>ERROR: " . $result['error'] . "</span><br>";
}

$result = validatePhoneNumber("12345");
echo "Testing '12345': ";
if (!$result['valid']) {
    echo "<span style='color: red;'>ERROR: " . $result['error'] . "</span><br>";
} else {
    echo "<span style='color: green;'>PASSED</span><br>";
}

// Test 4: Barangay ID validation
echo "<h2>Test 4: Barangay ID Format</h2>";
$result = validateBarangayIdFormat("12313");
echo "Testing '12313': ";
if (!$result['valid']) {
    echo "<span style='color: red;'>ERROR: " . $result['error'] . "</span><br>";
} else {
    echo "<span style='color: green;'>PASSED</span><br>";
}

$result = validateBarangayIdFormat("BA-1001");
echo "Testing 'BA-1001': ";
if ($result['valid']) {
    echo "<span style='color: green;'>PASSED</span><br>";
} else {
    echo "<span style='color: red;'>ERROR: " . $result['error'] . "</span><br>";
}

// Test 5: Barangay Resident validation
echo "<h2>Test 5: Barangay Resident Validation</h2>";
$result = validateBarangayResident("BA-1001", "Juan Dela Cruz", "1990-01-15");
echo "Testing BA-1001 with correct details: ";
if ($result['valid']) {
    echo "<span style='color: green;'>PASSED</span><br>";
} else {
    echo "<span style='color: red;'>ERROR: " . $result['error'] . "</span><br>";
}

$result = validateBarangayResident("BA-1001", "Wrong Name", "1990-01-15");
echo "Testing BA-1001 with wrong name: ";
if (!$result['valid']) {
    echo "<span style='color: red;'>ERROR: " . $result['error'] . "</span><br>";
} else {
    echo "<span style='color: green;'>PASSED</span><br>";
}

// Test 6: Email validation
echo "<h2>Test 6: Email Validation</h2>";
$result = validateEmail("test@yahoo.com");
echo "Testing 'test@yahoo.com': ";
if (!$result['valid']) {
    echo "<span style='color: red;'>ERROR: " . $result['error'] . "</span><br>";
} else {
    echo "<span style='color: green;'>PASSED</span><br>";
}

$result = validateEmail("test@gmail.com");
echo "Testing 'test@gmail.com': ";
if ($result['valid']) {
    echo "<span style='color: green;'>PASSED</span><br>";
} else {
    echo "<span style='color: red;'>ERROR: " . $result['error'] . "</span><br>";
}

echo "<hr>";
echo "<h2>If all tests show expected results, validation is working!</h2>";
?>