<?php
// Include database connection
require_once 'db.php';

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verify CSRF Token
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Handle form submission for adding/editing tiles
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF Token
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }

    if (isset($_GET['action']) && $_GET['action'] === 'update_title') {
        // Get the new title from the request body
        $data = json_decode(file_get_contents('php://input'), true);
        $newTitle = htmlspecialchars($data['title']);
        $csrfTokenFromRequest = $data['csrf_token'];

        // Verify CSRF Token
        if (!verifyCsrfToken($csrfTokenFromRequest)) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }

        // Update the dashboard title in the database
        $stmt = $pdo->prepare("INSERT INTO settings (key_name, value) VALUES ('dashboard_title', :title) 
                               ON DUPLICATE KEY UPDATE value = :title");
        $success = $stmt->execute(['title' => $newTitle]);

        // Return a JSON response
        echo json_encode(['success' => $success]);
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
        // Default to the "Uncategorized" group if no group is selected
        $stmt = $pdo->prepare("SELECT id FROM groups WHERE name = 'Uncategorized'");
        $stmt->execute();
        $groupData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($groupData) {
            $groupId = $groupData['id'];
        } else {
            // If "Uncategorized" group doesn't exist, create it
            $stmt = $pdo->query("SELECT IFNULL(MAX(position), 0) AS max_position FROM groups");
            $maxPosition = $stmt->fetchColumn();
            $newGroupPosition = $maxPosition + 1;

            $stmt = $pdo->prepare("INSERT INTO groups (name, position) VALUES ('Uncategorized', :position)");
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
        $success = $stmt->execute([
            'title' => $title,
            'url' => $url,
            'icon' => $iconPath,
            'group_id' => $groupId,
            'position' => $position,
            'id' => $id
        ]);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Tile updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update tile.']);
        }
        exit;
    } else { // Add new tile
        $stmt = $pdo->prepare("INSERT INTO tiles (title, url, icon, group_id, position) VALUES (:title, :url, :icon, :group_id, :position)");
        $success = $stmt->execute([
            'title' => $title,
            'url' => $url,
            'icon' => $iconPath,
            'group_id' => $groupId,
            'position' => $position
        ]);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Tile added successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add tile.']);
        }
        exit;
    }
}

// Handle deletion of tiles
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    // Verify CSRF Token
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }

    $tileId = (int)$_POST['delete'];
    $stmt = $pdo->prepare("DELETE FROM tiles WHERE id = :id");
    $success = $stmt->execute(['id' => $tileId]);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Tile deleted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete tile.']);
    }
    exit;
}

header('Location: index.php');
exit;
?>