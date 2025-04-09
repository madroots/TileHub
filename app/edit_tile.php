<?php
// Include database connection
require_once 'db.php';

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Handle form submission for adding/editing tiles
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $title = htmlspecialchars($_POST['title']);
    $url = htmlspecialchars($_POST['url']);
    $groupName = htmlspecialchars($_POST['group']);
    $newGroupName = htmlspecialchars($_POST['new_group']);

    // Determine the group_id
    $groupId = null;
    if (!empty($newGroupName)) {
        // Create a new group
        $stmt = $pdo->prepare("INSERT INTO groups (name, position) VALUES (:name, (SELECT IFNULL(MAX(position) + 1, 1) FROM groups)) ON DUPLICATE KEY UPDATE name = name");
        $stmt->execute(['name' => $newGroupName]);
        $groupId = $pdo->lastInsertId();
    } else {
        // Get the group_id for the selected group
        $stmt = $pdo->prepare("SELECT id FROM groups WHERE name = :name");
        $stmt->execute(['name' => $groupName]);
        $groupData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($groupData) {
            $groupId = $groupData['id'];
        } else {
            // If the group doesn't exist, create it
            $stmt = $pdo->prepare("INSERT INTO groups (name, position) VALUES (:name, (SELECT IFNULL(MAX(position) + 1, 1) FROM groups))");
            $stmt->execute(['name' => $groupName]);
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

// Handle deletion of tiles
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM tiles WHERE id = :id");
    $stmt->execute(['id' => $_GET['delete']]);
}

header('Location: index.php');
exit;
?>