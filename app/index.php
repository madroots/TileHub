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
$dashboardTitle = $stmt->fetchColumn() ?: 'Dashboard'; // Default to "Dashboard" from test-branch

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
<body class="p-4 <?php echo isset($_SESSION['edit_mode']) ? 'edit-mode' : ''; ?>">
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
        
        <!-- Import/Export Messages -->
        <?php if (isset($_SESSION['import_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['import_success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['import_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['import_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['import_error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['import_error']); ?>
        <?php endif; ?>
        
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
            <?php foreach ($groups as $group): 
                // Filter tiles for this group
                $groupTiles = array_filter($tiles, function($tile) use ($group) {
                    return $tile['group_id'] == $group['id'];
                });
                ?>
                <div class="group-container col-12" data-group-id="<?= $group['id'] ?>">
                    <div class="group-header d-flex align-items-center mb-3">
                        <h2 class="text-primary mb-0"><?= htmlspecialchars($group['name']) ?></h2>
                        <?php if (isset($_SESSION['edit_mode'])) : ?>
                            <div class="group-actions ms-2">
                                <button type="button" class="btn btn-sm btn-warning edit-group-btn" 
                                    data-group-id="<?= $group['id'] ?>" 
                                    data-group-name="<?= htmlspecialchars($group['name']) ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-group-btn" 
                                    data-group-id="<?= $group['id'] ?>" 
                                    data-group-name="<?= htmlspecialchars($group['name']) ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                    </svg>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <hr class="mt-0 mb-4">
                    <div class="group-tiles row">
                        <?php foreach ($groupTiles as $tile): ?>
                            <div class="col-md-4 col-sm-6 tile-wrapper">
                                <div class="tile-item" data-id="<?= $tile['id'] ?>" data-group-id="<?= $group['id'] ?>">
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
                            </div>
                        <?php endforeach; ?>
                    </div>
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
    <script src="assets/js/interact.js"></script>
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
                
                <?php if (isset($_SESSION['edit_mode'])): ?>
                <!-- Import/Export Section -->
                <div class="setting-item">
                    <span>Export Data</span>
                    <button class="btn btn-sm btn-primary" id="exportDataBtn">Export</button>
                </div>
                <div class="setting-item">
                    <span>Import Data</span>
                    <button class="btn btn-sm btn-success" id="importDataBtn" data-bs-toggle="modal" data-bs-target="#importModal">Import</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Import Modal -->
    <?php if (isset($_SESSION['edit_mode'])): ?>
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="edit_tile.php?action=import" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="importFile" class="form-label">Select Export File</label>
                            <input type="file" class="form-control" id="importFile" name="import_file" accept=".zip" required>
                            <div class="form-text">Choose a TileHub export ZIP file to import.</div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="overwriteData" name="overwrite">
                            <label class="form-check-label" for="overwriteData">
                                Overwrite existing data
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Import Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>