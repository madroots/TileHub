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

function exportData($pdo) {
    // Create temporary directory for export in uploads folder
    $exportDir = __DIR__ . '/uploads/tmp_export_' . uniqid();
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
                    $sourceFile = $uploadDir . '/' . $icon;
                    $destFile = $iconsDir . '/' . $icon;
                    // Only copy files, not directories
                    if (is_file($sourceFile)) {
                        copy($sourceFile, $destFile);
                    }
                }
            }
        }
        
        // Create manifest file with schema information
        $schemaInfo = getSchemaInfo($pdo);
        
        $manifest = [
            'version' => '1.0',
            'export_date' => date('Y-m-d H:i:s'),
            'tilehub_version' => '1.0',
            'schema' => $schemaInfo
        ];
        file_put_contents($exportDir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
        
        // Create ZIP archive in uploads directory
        $zipFile = __DIR__ . '/uploads/tilehub_export_' . date('Y-m-d') . '.zip';
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

function getSchemaInfo($pdo) {
    $schema = [];
    
    // Get information about groups table
    $stmt = $pdo->query("DESCRIBE groups");
    $schema['groups'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get information about tiles table
    $stmt = $pdo->query("DESCRIBE tiles");
    $schema['tiles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get information about settings table
    $stmt = $pdo->query("DESCRIBE settings");
    $schema['settings'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $schema;
}

function importData($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php');
        exit;
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['import_error'] = 'No file uploaded or upload error occurred.';
        header('Location: index.php');
        exit;
    }
    
    // Check if file is a ZIP file
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $_FILES['import_file']['tmp_name']);
    finfo_close($fileInfo);
    
    if ($mimeType !== 'application/zip' && $mimeType !== 'application/x-zip-compressed') {
        $_SESSION['import_error'] = 'Invalid file type. Please upload a ZIP file.';
        header('Location: index.php');
        exit;
    }
    
    // Create temporary directory for extraction in uploads folder
    $extractDir = __DIR__ . '/uploads/tmp_extract_' . uniqid();
    if (!mkdir($extractDir, 0777, true)) {
        $_SESSION['import_error'] = 'Failed to create temporary directory.';
        header('Location: index.php');
        exit;
    }
    
    try {
        // Extract ZIP file
        $zip = new ZipArchive();
        if ($zip->open($_FILES['import_file']['tmp_name']) !== TRUE) {
            throw new Exception('Failed to open ZIP file.');
        }
        $zip->extractTo($extractDir);
        $zip->close();
        
        // Check for required files
        $manifestFile = $extractDir . '/manifest.json';
        $dbDir = $extractDir . '/database';
        
        if (!file_exists($manifestFile) || !is_dir($dbDir)) {
            throw new Exception('Invalid export file structure.');
        }
        
        // Read manifest
        $manifest = json_decode(file_get_contents($manifestFile), true);
        if (!$manifest) {
            throw new Exception('Invalid manifest file.');
        }
        
        // Check version compatibility and resolve conflicts
        $compatibility = checkVersionCompatibility($pdo, $manifest);
        if (!$compatibility['compatible']) {
            throw new Exception('Version incompatibility: ' . $compatibility['message']);
        }
        
        // Check if we should overwrite existing data
        $overwrite = isset($_POST['overwrite']) && $_POST['overwrite'] === 'on';
        
        // Begin transaction
        $pdo->beginTransaction();
        
        // Clear existing data if overwrite is selected
        if ($overwrite) {
            $pdo->exec("DELETE FROM tiles");
            $pdo->exec("DELETE FROM groups");
            $pdo->exec("DELETE FROM settings");
        }
        
        // Import database tables with conflict resolution
        importTableWithConflictResolution($pdo, $dbDir . '/groups.sql', 'groups', $overwrite, $manifest);
        importTableWithConflictResolution($pdo, $dbDir . '/tiles.sql', 'tiles', $overwrite, $manifest);
        importTableWithConflictResolution($pdo, $dbDir . '/settings.sql', 'settings', $overwrite, $manifest);
        
        // Copy icon files
        $iconsDir = $extractDir . '/icons';
        if (is_dir($iconsDir)) {
            $uploadDir = __DIR__ . '/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $icons = scandir($iconsDir);
            foreach ($icons as $icon) {
                if ($icon !== '.' && $icon !== '..') {
                    // Skip if file already exists to avoid overwriting
                    if (!file_exists($uploadDir . '/' . $icon)) {
                        copy($iconsDir . '/' . $icon, $uploadDir . '/' . $icon);
                    }
                }
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Clean up
        removeDirectory($extractDir);
        
        $_SESSION['import_success'] = 'Data imported successfully.';
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction
        $pdo->rollback();
        
        // Clean up
        if (is_dir($extractDir)) {
            removeDirectory($extractDir);
        }
        
        $_SESSION['import_error'] = 'Import failed: ' . $e->getMessage();
        header('Location: index.php');
        exit;
    }
}

function importTableWithConflictResolution($pdo, $sqlFile, $tableName, $overwrite, $manifest) {
    if (!file_exists($sqlFile)) {
        return; // Skip if file doesn't exist
    }
    
    $sql = file_get_contents($sqlFile);
    if (empty($sql)) {
        return;
    }
    
    // Get current table schema
    $currentSchema = [];
    try {
        $stmt = $pdo->query("DESCRIBE `$tableName`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            $currentSchema[$column['Field']] = $column;
        }
    } catch (Exception $e) {
        // Table doesn't exist, will be created by import
    }
    
    // Split SQL into individual statements
    $statements = explode(";
", $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            // Handle INSERT statements with conflict resolution
            if (preg_match('/INSERT INTO\s+`?' . $tableName . '`?/i', $statement)) {
                // Parse the INSERT statement to extract columns and values
                $resolvedStatement = resolveInsertConflicts($pdo, $statement, $tableName, $currentSchema, $manifest);
                if ($resolvedStatement) {
                    try {
                        $pdo->exec($resolvedStatement);
                    } catch (Exception $e) {
                        // Log error but continue with other statements
                        error_log("Failed to execute statement: " . $e->getMessage());
                    }
                }
            } else {
                // Execute other statements (CREATE TABLE, etc.) directly
                try {
                    $pdo->exec($statement);
                } catch (Exception $e) {
                    // Log error but continue
                    error_log("Failed to execute statement: " . $e->getMessage());
                }
            }
        }
    }
}

function resolveInsertConflicts($pdo, $statement, $tableName, $currentSchema, $manifest) {
    // Extract columns and values from INSERT statement
    if (!preg_match('/INSERT INTO\s+`?' . $tableName . '`?\s*\(([^)]+)\)\s*VALUES\s*\(([^)]+)\)/i', $statement, $matches)) {
        return $statement; // Could not parse, return as is
    }
    
    $columns = array_map('trim', explode(',', $matches[1]));
    $values = array_map('trim', explode(',', $matches[2]));
    
    // Remove backticks from column names
    $columns = array_map(function($col) {
        return trim($col, '` ');
    }, $columns);
    
    // Create associative array of column => value
    $data = [];
    for ($i = 0; $i < count($columns); $i++) {
        if (isset($values[$i])) {
            $data[$columns[$i]] = trim($values[$i], "' ");
        }
    }
    
    // Check for missing columns in current schema
    $missingColumns = [];
    foreach ($columns as $column) {
        if (!isset($currentSchema[$column])) {
            $missingColumns[] = $column;
        }
    }
    
    // If there are missing columns, we need to handle them
    if (!empty($missingColumns)) {
        // Remove missing columns from the insert
        foreach ($missingColumns as $missingColumn) {
            unset($data[$missingColumn]);
        }
    }
    
    // Check for missing required columns in the data
    $missingDataColumns = [];
    foreach ($currentSchema as $column => $info) {
        // Skip auto increment columns
        if ($info['Extra'] === 'auto_increment') {
            continue;
        }
        
        // Check if column is required and missing from data
        if (!isset($data[$column]) && $info['Null'] === 'NO' && $info['Default'] === null) {
            $missingDataColumns[] = $column;
        }
    }
    
    // If there are missing required columns with no defaults, we can't insert this row
    if (!empty($missingDataColumns)) {
        // For simplicity, we'll skip rows with missing required data
        // In a more sophisticated system, we might prompt the user or use defaults
        return null;
    }
    
    // Rebuild the INSERT statement with resolved columns
    $newColumns = array_keys($data);
    $newValues = array_values($data);
    
    // Escape values properly
    $newValues = array_map(function($value) use ($pdo) {
        return $value === 'NULL' ? 'NULL' : $pdo->quote($value);
    }, $newValues);
    
    if (empty($newColumns)) {
        return null; // Nothing to insert
    }
    
    return "INSERT INTO `$tableName` (`" . implode('`, `', $newColumns) . "`) VALUES (" . implode(', ', $newValues) . ")";
}

function checkVersionCompatibility($pdo, $manifest) {
    // For now, we'll assume compatibility for same major versions
    // In a more complex system, we would check schema compatibility
    
    $currentSchema = getSchemaInfo($pdo);
    $exportedSchema = $manifest['schema'] ?? [];
    
    // Basic check: if exported schema is missing, assume compatibility
    if (empty($exportedSchema)) {
        return ['compatible' => true, 'message' => 'No schema info in export, assuming compatibility'];
    }
    
    // More detailed check could be implemented here
    // For now, we'll just return compatible
    return ['compatible' => true, 'message' => 'Compatible'];
}
?>