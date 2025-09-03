<?php
// Include database connection
require_once 'db.php';

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if ZipArchive is available
if (!class_exists('ZipArchive')) {
    die('ZipArchive class is not available. Please install/enable the php-zip extension.');
}

// Handle export action
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    exportData($pdo);
    exit;
}

// Handle import action
if (isset($_GET['action']) && $_GET['action'] === 'import') {
    importData($pdo);
    exit;
}

// Handle form submission for adding/editing tiles
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_GET['action']) && $_GET['action'] === 'update_title') {
        // Get the new title from the request body
        $data = json_decode(file_get_contents('php://input'), true);
        $newTitle = htmlspecialchars($data['title']);

        // Update the dashboard title in the database
        $stmt = $pdo->prepare("INSERT INTO settings (key_name, value) VALUES ('dashboard_title', :title) 
                               ON DUPLICATE KEY UPDATE value = :title");
        $success = $stmt->execute(['title' => $newTitle]);

        // Return a JSON response
        echo json_encode(['success' => $success]);
        exit;
    }
    
    if (isset($_GET['action']) && $_GET['action'] === 'update_setting') {
        // Get the setting key and value from the request body
        $data = json_decode(file_get_contents('php://input'), true);
        $key = htmlspecialchars($data['key']);
        $value = htmlspecialchars($data['value']);

        // Update the setting in the database
        $stmt = $pdo->prepare("INSERT INTO settings (key_name, value) VALUES (:key, :value) 
                               ON DUPLICATE KEY UPDATE value = :value");
        $success = $stmt->execute(['key' => $key, 'value' => $value]);

        // Return a JSON response
        echo json_encode(['success' => $success]);
        exit;
    }
    
    if (isset($_GET['action']) && $_GET['action'] === 'update_tile_positions') {
        // Get the tile positions from the request body
        $data = json_decode(file_get_contents('php://input'), true);
        $tiles = $data['tiles'];
        
        try {
            // Begin transaction
            $pdo->beginTransaction();
            
            // Update each tile's position and group
            foreach ($tiles as $tile) {
                $stmt = $pdo->prepare("UPDATE tiles SET position = :position, group_id = :group_id WHERE id = :id");
                $stmt->execute([
                    'position' => (int)$tile['position'],
                    'group_id' => (int)$tile['group_id'],
                    'id' => (int)$tile['id']
                ]);
            }
            
            // Commit transaction
            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollback();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        
        exit;
    }
    
    if (isset($_GET['action']) && $_GET['action'] === 'update_group_positions') {
        // Get the group positions from the request body
        $data = json_decode(file_get_contents('php://input'), true);
        $groups = $data['groups'];
        
        try {
            // Begin transaction
            $pdo->beginTransaction();
            
            // Update each group's position
            foreach ($groups as $group) {
                $stmt = $pdo->prepare("UPDATE groups SET position = :position WHERE id = :id");
                $stmt->execute([
                    'position' => (int)$group['position'],
                    'id' => (int)$group['id']
                ]);
            }
            
            // Commit transaction
            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollback();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        
        exit;
    }
    
    if (isset($_GET['action']) && $_GET['action'] === 'update_group_name') {
        // Get the group data from the request body
        $data = json_decode(file_get_contents('php://input'), true);
        $groupId = (int)$data['id'];
        $newName = htmlspecialchars($data['name']);
        
        try {
            // Update the group name in the database
            $stmt = $pdo->prepare("UPDATE groups SET name = :name WHERE id = :id");
            $success = $stmt->execute([
                'name' => $newName,
                'id' => $groupId
            ]);
            
            // Return a JSON response
            echo json_encode(['success' => $success]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        
        exit;
    }
    
    if (isset($_GET['action']) && $_GET['action'] === 'delete_group') {
        // Get the group data from the request body
        $data = json_decode(file_get_contents('php://input'), true);
        $groupId = (int)$data['id'];
        
        try {
            // Begin transaction
            $pdo->beginTransaction();
            
            // Check if this is the last group
            $stmt = $pdo->query("SELECT COUNT(*) FROM groups");
            $groupCount = $stmt->fetchColumn();
            
            if ($groupCount <= 1) {
                throw new Exception("Cannot delete the last group. At least one group must exist.");
            }
            
            // Get the first available group to move tiles to
            $stmt = $pdo->prepare("SELECT id FROM groups WHERE id != :id ORDER BY position ASC LIMIT 1");
            $stmt->execute(['id' => $groupId]);
            $newGroupId = $stmt->fetchColumn();
            
            if (!$newGroupId) {
                throw new Exception("No other groups available to move tiles to.");
            }
            
            // Move all tiles from the group being deleted to the new group
            $stmt = $pdo->prepare("UPDATE tiles SET group_id = :new_group_id WHERE group_id = :old_group_id");
            $stmt->execute([
                'new_group_id' => $newGroupId,
                'old_group_id' => $groupId
            ]);
            
            // Delete the group
            $stmt = $pdo->prepare("DELETE FROM groups WHERE id = :id");
            $success = $stmt->execute(['id' => $groupId]);
            
            // Commit transaction
            $pdo->commit();
            
            // Return a JSON response
            echo json_encode(['success' => $success]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollback();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        
        exit;
    }

    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $title = htmlspecialchars($_POST['title']);
    $url = htmlspecialchars($_POST['url']);
    $groupId = isset($_POST['group']) ? (int)$_POST['group'] : null;
    $newGroupName = htmlspecialchars($_POST['new_group']);

    // Determine the group_id
    if (!empty($newGroupName)) {
        // Get the maximum position from the groups table
        $stmt = $pdo->query("SELECT IFNULL(MAX(position), 0) AS max_position FROM groups");
        $maxPosition = $stmt->fetchColumn();
        $newGroupPosition = $maxPosition + 1;

        // Insert the new group
        $stmt = $pdo->prepare("INSERT INTO groups (name, position) VALUES (:name, :position)");
        $stmt->execute(['name' => $newGroupName, 'position' => $newGroupPosition]);

        // Get the new group's ID
        $groupId = $pdo->lastInsertId();
    } elseif ($groupId === null) {
        // Default to the first available group
        $stmt = $pdo->query("SELECT id FROM groups ORDER BY position ASC LIMIT 1");
        $groupData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($groupData) {
            $groupId = $groupData['id'];
        } else {
            // If no groups exist, create a default one
            $stmt = $pdo->query("SELECT IFNULL(MAX(position), 0) AS max_position FROM groups");
            $maxPosition = $stmt->fetchColumn();
            $newGroupPosition = $maxPosition + 1;

            $stmt = $pdo->prepare("INSERT INTO groups (name, position) VALUES ('Default', :position)");
            $stmt->execute(['position' => $newGroupPosition]);

            $groupId = $pdo->lastInsertId();
        }
    }

    // Handle icon upload or selection
    $iconPath = '';
    if (isset($_FILES['icon_upload']) && $_FILES['icon_upload']['error'] === UPLOAD_ERR_OK) {
        // Upload new icon
        $tmpName = $_FILES['icon_upload']['tmp_name'];
        $iconName = uniqid() . '_' . basename($_FILES['icon_upload']['name']);
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $iconPath = $uploadDir . $iconName;
        move_uploaded_file($tmpName, $iconPath);
        $iconPath = basename($iconPath); // Store only the relative path for the icon
    } elseif (isset($_POST['icon']) && !empty($_POST['icon'])) {
        // Reuse existing icon
        $iconPath = htmlspecialchars($_POST['icon']);
    }

    // Determine position within the group
    if ($id) { // Update existing tile
        $stmt = $pdo->prepare("SELECT position FROM tiles WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $tileData = $stmt->fetch(PDO::FETCH_ASSOC);
        $position = $tileData['position'];
    } else { // Add new tile
        // Get max position in the group
        $stmt = $pdo->prepare("SELECT MAX(position) AS max_position FROM tiles WHERE group_id = :group_id");
        $stmt->execute(['group_id' => $groupId]);
        $maxPosition = $stmt->fetchColumn();
        $position = ($maxPosition !== false) ? $maxPosition + 1 : 1;
    }

    if ($id) { // Update existing tile
        $stmt = $pdo->prepare("UPDATE tiles SET title = :title, url = :url, icon = :icon, group_id = :group_id, position = :position WHERE id = :id");
        $stmt->execute([
            'title' => $title,
            'url' => $url,
            'icon' => $iconPath,
            'group_id' => $groupId,
            'position' => $position,
            'id' => $id
        ]);
    } else { // Add new tile
        $stmt = $pdo->prepare("INSERT INTO tiles (title, url, icon, group_id, position) VALUES (:title, :url, :icon, :group_id, :position)");
        $stmt->execute([
            'title' => $title,
            'url' => $url,
            'icon' => $iconPath,
            'group_id' => $groupId,
            'position' => $position
        ]);
    }
}

header('Location: index.php');
exit;

<?php
}

function exportData($pdo) {
    // Create temporary directory for export
    $exportDir = __DIR__ . '/tmp_export_' . uniqid();
    if (!mkdir($exportDir, 0777, true)) {
        die("Failed to create temporary directory");
    }
    
    try {
        // Create database directory
        $dbDir = $exportDir . '/database';
        mkdir($dbDir, 0777, true);
        
        // Export groups table
        exportTable($pdo, 'groups', $dbDir . '/groups.sql');
        
        // Export tiles table
        exportTable($pdo, 'tiles', $dbDir . '/tiles.sql');
        
        // Export settings table
        exportTable($pdo, 'settings', $dbDir . '/settings.sql');
        
        // Create icons directory
        $iconsDir = $exportDir . '/icons';
        mkdir($iconsDir, 0777, true);
        
        // Copy icon files
        $uploadDir = __DIR__ . '/uploads';
        if (is_dir($uploadDir)) {
            $icons = scandir($uploadDir);
            foreach ($icons as $icon) {
                if ($icon !== '.' && $icon !== '..') {
                    copy($uploadDir . '/' . $icon, $iconsDir . '/' . $icon);
                }
            }
        }
        
        // Create manifest file
        $manifest = [
            'version' => '1.0',
            'export_date' => date('Y-m-d H:i:s'),
            'tilehub_version' => '1.0'
        ];
        file_put_contents($exportDir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
        
        // Create ZIP archive
        $zipFile = __DIR__ . '/tilehub_export_' . date('Y-m-d') . '.zip';
        createZip($exportDir, $zipFile);
        
        // Clean up temporary directory
        removeDirectory($exportDir);
        
        // Send ZIP file to user
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="tilehub_export_' . date('Y-m-d') . '.zip"');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);
        
        // Delete temporary ZIP file
        unlink($zipFile);
        
    } catch (Exception $e) {
        // Clean up on error
        if (is_dir($exportDir)) {
            removeDirectory($exportDir);
        }
        die("Export failed: " . $e->getMessage());
    }
}

function exportTable($pdo, $table, $filename) {
    $stmt = $pdo->prepare("SELECT * FROM `$table`");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $sql = "/* Export of $table table */\n";
    $sql .= "/* Exported on " . date('Y-m-d H:i:s') . " */\n\n";
    
    foreach ($rows as $row) {
        $columns = array_keys($row);
        $values = array_map(function($value) use ($pdo) {
            return $value === null ? 'NULL' : $pdo->quote($value);
        }, $row);
        
        $sql .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
    }
    
    file_put_contents($filename, $sql);
}

function createZip($source, $destination) {
    $zip = new ZipArchive();
    if (!$zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
        throw new Exception("Could not create ZIP archive");
    }
    
    $source = str_replace('\\', '/', realpath($source));
    
    if (is_dir($source) === true) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($files as $file) {
            $file = str_replace('\\', '/', $file);
            
            // Ignore "." and ".." folders
            if (in_array(substr($file, strrpos($file, '/') + 1), ['.', '..'])) {
                continue;
            }
            
            $file = realpath($file);
            
            if (is_dir($file) === true) {
                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            } else if (is_file($file) === true) {
                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
            }
        }
    }
    
    return $zip->close();
}

function removeDirectory($dir) {
    if (!is_dir($dir)) {
        return;
    }
    
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? removeDirectory("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

function importData($pdo) {
    // TODO: Implement import functionality
    // This will be implemented in the next step
    header('Location: index.php');
    exit;
}
?>