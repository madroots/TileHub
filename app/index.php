<?php
// Include database connection
require_once 'db.php';
// Initialize session
session_start();

// Handle deletion of tiles
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM tiles WHERE id = :id");
    $stmt->execute(['id' => $_GET['delete']]);
    
    // Redirect to clean URL after deletion
    header('Location: ./');
    exit;
}

// Check if in edit mode
if (isset($_GET['edit']) && $_GET['edit'] === 'true') {
    $_SESSION['edit_mode'] = true;
    header('Location: ./');
    exit;
} elseif (isset($_GET['exit_edit'])) {
    unset($_SESSION['edit_mode']);
    header('Location: ./');
    exit;
}

// Fetch dashboard title
$stmt = $pdo->prepare("SELECT value FROM settings WHERE key_name = 'dashboard_title'");
$stmt->execute();
$dashboardTitle = $stmt->fetchColumn() ?: 'TileHub Dashboard'; // Default to "TileHub Dashboard"

// Fetch settings button visibility
$stmt = $pdo->prepare("SELECT value FROM settings WHERE key_name = 'show_settings_button'");
$stmt->execute();
$showSettingsButton = $stmt->fetchColumn();
if ($showSettingsButton === false) {
    $showSettingsButton = 'true'; // Default to showing the button
}

// Fetch all groups
$stmt = $pdo->query("SELECT id, name FROM groups ORDER BY position ASC");
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all tiles ordered by group_position and position
$stmt = $pdo->query("SELECT t.id, t.title, t.url, t.icon, t.group_id, t.position, g.name AS group_name 
                     FROM tiles t 
                     JOIN groups g ON t.group_id = g.id 
                     ORDER BY g.position ASC, t.position ASC");
$tiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TileHub Dashboard</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h1 class="mb-4" id="dashboard-title">
            <?php if (isset($_SESSION['edit_mode'])) : ?>
                <!-- Editable Input Field -->
                <input type="text" class="form-control d-inline w-auto" id="editable-title" value="<?= htmlspecialchars($dashboardTitle) ?>" />
                <button class="btn btn-sm btn-primary ms-2" id="save-title">Save</button>
            <?php else : ?>
                <!-- Static Title -->
                <?= htmlspecialchars($dashboardTitle) ?>
            <?php endif; ?>
        </h1>
        <?php if (isset($_SESSION['edit_mode'])) : ?>
            <a href="?exit_edit=true" class="btn btn-danger mb-3">Exit Edit Mode</a>
            <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addTileModal">Add Tile</button>
            
            <!-- Settings Button Visibility Toggle -->
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="showSettingsButtonToggle" <?php echo $showSettingsButton === 'true' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="showSettingsButtonToggle">Show Settings Button</label>
            </div>
        <?php endif; ?>
        <div class="row" id="tile-container">
            <?php
            $currentGroupId = null;
            foreach ($tiles as $tile) : ?>
                <!-- Display Group Heading if Group Changes -->
                <?php if ($currentGroupId !== $tile['group_id']) : ?>
                    <?php $currentGroupId = $tile['group_id']; ?>
                    <div class="col-12 group-header">
                        <h2 class="text-primary"><?= htmlspecialchars($tile['group_name']) ?></h2>
                        <hr>
                    </div>
                <?php endif; ?>
                <div class="col-md-4 col-sm-6 tile-item" data-id="<?= $tile['id'] ?>">
                    <!-- Tile Link -->
                    <a href="<?= htmlspecialchars($tile['url']) ?>" target="_blank" class="tile d-flex align-items-center">
                        <img src="uploads/<?= htmlspecialchars($tile['icon']) ?>" alt="Icon">
                        <span><?= htmlspecialchars($tile['title']) ?></span>
                    </a>
                    <!-- Edit and Delete Buttons -->
                    <?php if (isset($_SESSION['edit_mode'])) : ?>
                        <div class="edit-buttons mt-2">
                            <button type="button" class="btn btn-sm btn-warning" 
                                data-bs-toggle="modal" data-bs-target="#editTileModal" 
                                data-id="<?= $tile['id'] ?>" 
                                data-title="<?= htmlspecialchars($tile['title']) ?>" 
                                data-url="<?= htmlspecialchars($tile['url']) ?>" 
                                data-icon="<?= htmlspecialchars($tile['icon']) ?>"
                                data-group-id="<?= $tile['group_id'] ?>">
                                Edit
                            </button>
                            <a href="?delete=<?= $tile['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- Add Tile Modal -->
    <div class="modal fade" id="addTileModal" tabindex="-1" aria-labelledby="addTileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTileModalLabel">Add Tile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="edit_tile.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="url" class="form-label">URL</label>
                            <input type="url" class="form-control" id="url" name="url" required>
                        </div>
                        <div class="mb-3">
                            <label for="icon" class="form-label">Icon</label>
                            <select class="form-select" id="icon" name="icon">
                                <option value="" selected disabled>-- Select Icon --</option>
                                <?php
                                $uploadDir = __DIR__ . '/uploads/';
                                if (is_dir($uploadDir)) {
                                    $icons = scandir($uploadDir);
                                    foreach ($icons as $icon) {
                                        if ($icon !== '.' && $icon !== '..') {
                                            echo '<option value="' . htmlspecialchars($icon) . '">' . htmlspecialchars($icon) . '</option>';
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="uploadIcon" class="form-label">Or Upload New Icon</label>
                            <input type="file" class="form-control" id="uploadIcon" name="icon_upload" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="group" class="form-label">Group</label>
                            <select class="form-select" id="group" name="group">
                                <option value="" selected disabled>-- Select Group --</option>
                                <?php foreach ($groups as $group) : ?>
                                    <option value="<?= htmlspecialchars($group['id']) ?>"><?= htmlspecialchars($group['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="mt-2">
                                <label for="newGroup" class="form-label">Or Create New Group</label>
                                <input type="text" class="form-control" id="newGroup" name="new_group" placeholder="Enter new group name">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Edit Tile Modal -->
    <div class="modal fade" id="editTileModal" tabindex="-1" aria-labelledby="editTileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTileModalLabel">Edit Tile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="edit_tile.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="id">
                        <div class="mb-3">
                            <label for="editTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="editTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editUrl" class="form-label">URL</label>
                            <input type="url" class="form-control" id="editUrl" name="url" required>
                        </div>
                        <div class="mb-3">
                            <label for="editIcon" class="form-label">Icon</label>
                            <select class="form-select" id="editIcon" name="icon">
                                <option value="" selected disabled>-- Select Icon --</option>
                                <?php
                                $uploadDir = __DIR__ . '/uploads/';
                                if (is_dir($uploadDir)) {
                                    $icons = scandir($uploadDir);
                                    foreach ($icons as $icon) {
                                        if ($icon !== '.' && $icon !== '..') {
                                            echo '<option value="' . htmlspecialchars($icon) . '">' . htmlspecialchars($icon) . '</option>';
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="uploadIcon" class="form-label">Or Upload New Icon</label>
                            <input type="file" class="form-control" id="uploadIcon" name="icon_upload" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="editGroup" class="form-label">Group</label>
                            <select class="form-select" id="editGroup" name="group">
                                <option value="" selected disabled>-- Select Group --</option>
                                <?php foreach ($groups as $group) : ?>
                                    <option value="<?= htmlspecialchars($group['id']) ?>"><?= htmlspecialchars($group['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="mt-2">
                                <label for="editNewGroup" class="form-label">Or Create New Group</label>
                                <input type="text" class="form-control" id="editNewGroup" name="new_group" placeholder="Enter new group name">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Local Scripts -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
    <!-- Settings Button (fixed position) -->
    <?php if ($showSettingsButton === 'true'): ?>
    <div class="settings-button" id="settingsButton">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"></circle>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
        </svg>
    </div>
    <?php endif; ?>

    <!-- Settings Overlay -->
    <div class="settings-overlay" id="settingsOverlay">
        <div class="settings-content">
            <div class="settings-header">
                <h3>Settings</h3>
                <button class="close-settings" id="closeSettings">&times;</button>
            </div>
            <div class="settings-body">
                <div class="setting-item">
                    <span>Edit Mode</span>
                    <div class="toggle-switch">
                        <input type="checkbox" id="editModeToggle" <?php echo isset($_SESSION['edit_mode']) ? 'checked' : ''; ?>>
                        <label for="editModeToggle"></label>
                    </div>
                </div>
                <!-- More settings here in the future -->
            </div>
        </div>
    </div>
</body>
</html>