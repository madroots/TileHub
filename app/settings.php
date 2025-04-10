<?php
// This file handles fetching all settings

// Include database connection
require_once 'db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Get all settings
if (isset($_GET['action']) && $_GET['action'] === 'get_settings') {
    // Fetch all settings from database
    $stmt = $pdo->query("SELECT key_name, value FROM settings");
    $settings = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['key_name']] = $row['value'];
    }
    
    echo json_encode($settings);
    exit;
}

// Return error for invalid requests
echo json_encode(['success' => false, 'message' => 'Invalid request']);