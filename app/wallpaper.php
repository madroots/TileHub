<?php
// This file handles wallpaper-related operations

// Include database connection for saving settings
require_once 'db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Search wallpapers
if (isset($_GET['action']) && $_GET['action'] === 'search') {
    $query = urlencode($_GET['query']);
    $apiUrl = "https://wallhaven.cc/api/v1/search?q={$query}&categories=110&purity=100&sorting=relevance";
    
    $response = file_get_contents($apiUrl);
    if ($response === false) {
        echo json_encode(['success' => false, 'message' => 'Failed to connect to Wallhaven API']);
        exit;
    }
    
    echo $response;
    exit;
}

// Save wallpaper choice
if (isset($_GET['action']) && $_GET['action'] === 'save_wallpaper') {
    $data = json_decode(file_get_contents('php://input'), true);
    $wallpaperUrl = $data['wallpaper_url'];
    
    // Update the wallpaper URL in the database
    $stmt = $pdo->prepare("INSERT INTO settings (key_name, value) VALUES ('wallpaper_url', :url) 
                           ON DUPLICATE KEY UPDATE value = :url");
    $success = $stmt->execute(['url' => $wallpaperUrl]);
    
    echo json_encode(['success' => $success]);
    exit;
}

// Save overlay darkness
if (isset($_GET['action']) && $_GET['action'] === 'save_darkness') {
    $data = json_decode(file_get_contents('php://input'), true);
    $darkness = intval($data['darkness']);
    
    // Ensure value is between 0 and 90
    $darkness = max(0, min(90, $darkness));
    
    // Update the overlay darkness in the database
    $stmt = $pdo->prepare("INSERT INTO settings (key_name, value) VALUES ('overlay_darkness', :darkness) 
                           ON DUPLICATE KEY UPDATE value = :darkness");
    $success = $stmt->execute(['darkness' => $darkness]);
    
    echo json_encode(['success' => $success]);
    exit;
}

// Return error for invalid requests
echo json_encode(['success' => false, 'message' => 'Invalid request']);