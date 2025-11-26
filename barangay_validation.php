<?php

/**
 * Validate Barangay ID against JSON records
 * Must match name and birthdate exactly
 */
function validateBarangayResident($barangayId, $fullName, $birthdate) {
    $jsonFile = "barangay_ids.json";
    
    if (!file_exists($jsonFile)) {
        return ['valid' => false, 'error' => 'Barangay records not found'];
    }
    
    $records = json_decode(file_get_contents($jsonFile), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['valid' => false, 'error' => 'Error reading barangay records'];
    }
    
    // Check if barangay ID exists
    if (!isset($records[$barangayId])) {
        return ['valid' => false, 'error' => 'Barangay ID not found in our records'];
    }
    
    $record = $records[$barangayId];
    
    // Normalize names for comparison (case-insensitive, trim spaces)
    $recordName = strtolower(trim($record['name']));
    $inputName = strtolower(trim($fullName));
    
    // Check if name matches
    if ($recordName !== $inputName) {
        return ['valid' => false, 'error' => 'Name does not match our records for this Barangay ID'];
    }
    
    // Check if birthdate matches
    if ($record['birthdate'] !== $birthdate) {
        return ['valid' => false, 'error' => 'Birthdate does not match our records for this Barangay ID'];
    }
    
    return ['valid' => true];
}

/**
 * Check if Barangay ID format is correct (BA-1234)
 */
function validateBarangayIdFormat($barangayId) {
    if (!preg_match('/^BA-\d{4}$/', $barangayId)) {
        return ['valid' => false, 'error' => 'Invalid Barangay ID format. Use BA-1234 format'];
    }
    return ['valid' => true];
}

?>