<?php
// Simple test script to verify permissions are set correctly

echo "Testing uploads directory permissions...\n";

$uploadDir = __DIR__ . '/app/uploads';

// Check if uploads directory exists
if (!is_dir($uploadDir)) {
    echo "Uploads directory does not exist\n";
    exit(1);
}

// Check directory permissions
$perms = fileperms($uploadDir);
if (($perms & 0777) == 0755) {
    echo "Directory permissions are correct (755)\n";
} else {
    echo "Directory permissions are incorrect: " . decoct($perms & 0777) . "\n";
}

// Check directory ownership
if (function_exists('posix_getpwuid')) {
    $owner = posix_getpwuid(fileowner($uploadDir));
    $group = posix_getgrgid(filegroup($uploadDir));
    echo "Directory owner: " . $owner['name'] . " (" . $owner['uid'] . ")\n";
    echo "Directory group: " . $group['name'] . " (" . $group['gid'] . ")\n";
}

// Test creating a file
$testFile = $uploadDir . '/permission_test.txt';
if (file_put_contents($testFile, "Test content") !== false) {
    echo "Successfully created test file\n";
    
    // Check file permissions
    $filePerms = fileperms($testFile);
    if (($filePerms & 0777) == 0644) {
        echo "File permissions are correct (644)\n";
    } else {
        echo "File permissions are incorrect: " . decoct($filePerms & 0777) . "\n";
    }
    
    // Clean up
    unlink($testFile);
    echo "Cleaned up test file\n";
} else {
    echo "Failed to create test file - permission issue\n";
}

echo "Test completed\n";
?>