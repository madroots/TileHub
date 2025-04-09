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
    $group = htmlspecialchars($_POST['group']);

    // Handle icon upload or selection
    $iconPath = '';
    if (isset($_FILES['icon_upload']) && $_FILES['icon_upload']['error'] === UPLOAD_ERR_OK) {
        // Upload new icon
        $tmpName = $_FILES['icon_upload']['tmp_name'];
        $iconName = uniqid() . '_' . basename($_FILES['icon_upload']['name']);
        $uploadDir = __DIR__ . '/uploads/';  // Ensure the icon is uploaded to this directory
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

    // Determine position and group_position
    if ($id) { // Update existing tile
        $stmt = $pdo->prepare("SELECT position, group_position FROM tiles WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $tileData = $stmt->fetch(PDO::FETCH_ASSOC);
        $position = $tileData['position'];
        $group_position = $tileData['group_position'];
    } else { // Add new tile
        // Get max position in the group
        $stmt = $pdo->prepare("SELECT MAX(position) AS max_position FROM tiles WHERE group_name = :group");
        $stmt->execute(['group' => $group]);
        $maxPosition = $stmt->fetchColumn();
        $position = ($maxPosition !== false) ? $maxPosition + 1 : 1;

        // Get group_position for the group
        $stmt = $pdo->prepare("SELECT group_position FROM tiles WHERE group_name = :group LIMIT 1");
        $stmt->execute(['group' => $group]);
        $existingGroupPosition = $stmt->fetchColumn();

        if ($existingGroupPosition === false) {
            // If the group doesn't exist, assign a new group_position
            $stmt = $pdo->prepare("SELECT MAX(group_position) AS max_group_position FROM tiles");
            $stmt->execute();
            $maxGroupPosition = $stmt->fetchColumn();
            $group_position = ($maxGroupPosition !== false) ? $maxGroupPosition + 1 : 1;
        } else {
            // If the group already exists, use its existing group_position
            $group_position = $existingGroupPosition;
        }
    }

    if ($id) { // Update existing tile
        $stmt = $pdo->prepare("UPDATE tiles SET title = :title, url = :url, icon = :icon, group_name = :group WHERE id = :id");
        $stmt->execute([
            'title' => $title,
            'url' => $url,
            'icon' => $iconPath,
            'group' => $group,
            'id' => $id
        ]);
    } else { // Add new tile
        $stmt = $pdo->prepare("INSERT INTO tiles (title, url, icon, group_name, position, group_position) VALUES (:title, :url, :icon, :group, :position, :group_position)");
        $stmt->execute([
            'title' => $title,
            'url' => $url,
            'icon' => $iconPath,
            'group' => $group,
            'position' => $position,
            'group_position' => $group_position
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