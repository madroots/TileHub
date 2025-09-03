<?php
// Simple test script to verify permissions are set correctly

echo "Testing uploads directory permissions...\n";

$uploadDir = __DIR__ . '/app/uploads';

// Check if uploads directory exists
if (!is_dir($uploadDir)) {
    echo "Uploads directory does not exist, creating it...\n";
    if (!mkdir($uploadDir, 0755, true)) {
        echo "Failed to create uploads directory\n";
        exit(1);
    }
}

// Check directory permissions
$perms = fileperms($uploadDir);
echo "Directory permissions: " . decoct($perms & 0777) . "\n";

if (($perms & 0777) == 0755 || ($perms & 0777) == 0775) {
    echo "Directory permissions are correct (755 or 775)\n";
} else {
    echo "Directory permissions are not optimal: " . decoct($perms & 0777) . "\n";
    echo "Recommended: chmod 755 or chmod 775\n";
}

// Check directory ownership
if (function_exists('posix_getpwuid') && function_exists('posix_getgrgid')) {
    $owner = posix_getpwuid(fileowner($uploadDir));
    $group = posix_getgrgid(filegroup($uploadDir));
    echo "Directory owner: " . ($owner ? $owner['name'] : 'unknown') . " (" . fileowner($uploadDir) . ")\n";
    echo "Directory group: " . ($group ? $group['name'] : 'unknown') . " (" . filegroup($uploadDir) . ")\n";
}

// Test creating a file
$testFile = $uploadDir . '/permission_test.txt';
echo "Testing file creation...\n";

if (file_put_contents($testFile, "Test content") !== false) {
    echo "Successfully created test file\n";
    
    // Check file permissions
    $filePerms = fileperms($testFile);
    echo "File permissions: " . decoct($filePerms & 0777) . "\n";
    
    if (($filePerms & 0777) == 0644) {
        echo "File permissions are correct (644)\n";
    } else {
        echo "File permissions are not optimal: " . decoct($filePerms & 0777) . "\n";
        echo "Recommended: chmod 644\n";
    }
    
    // Clean up
    unlink($testFile);
    echo "Cleaned up test file\n";
} else {
    echo "Failed to create test file - permission issue\n";
    echo "Try running: chmod 775 " . $uploadDir . "\n";
}

echo "Test completed\n";
?>