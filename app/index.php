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
    <title>Dashboard</title>
    <!-- Bootstrap CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #fff;
        }
        .tile {
            background-color: #212121;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
        }
        .tile:hover {
            background-color: #333;
        }
        .tile img {
            width: 24px;
            height: 24px;
            margin-right: 10px;
        }
        .form-label {
            color: #000; /* Dark text for form labels */
        }
        .group-header {
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .edit-buttons {
            display: flex;
            gap: 10px;
        }
        .group-header h2 {
            color: #ff5722 !important; /* Custom group title color */
            font-size: 1.5rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        /* Settings Button */
        .settings-button {
            position: fixed;
            right: 20px;
            top: 20px;
            background-color: rgba(51, 51, 51, 0.3); /* Semi-transparent background */
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            opacity: 0.2; /* Start with low opacity */
        }

        .settings-button:hover {
            background-color: #444;
            transform: rotate(30deg);
            opacity: 1; /* Full opacity on hover */
        }

        .settings-button svg {
            color: #ff5722;
        }

        /* Settings Overlay */
        .settings-overlay {
            position: fixed;
            top: 0;
            right: -300px; /* Start off-screen */
            width: 300px;
            height: 100%;
            background-color: #212121;
            z-index: 1001;
            transition: right 0.3s ease;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.5);
        }

        .settings-overlay.active {
            right: 0;
        }

        .settings-content {
            padding: 20px;
            color: #fff;
        }

        .settings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #444;
            padding-bottom: 10px;
        }

        .settings-header h3 {
            margin: 0;
            color: #ff5722;
        }

        .close-settings {
            background: none;
            border: none;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
        }

        /* Settings Items */
        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #333;
        }

        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            width: 60px;
            height: 30px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-switch label {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #444;
            border-radius: 34px;
            cursor: pointer;
            transition: .4s;
        }

        .toggle-switch label:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            border-radius: 50%;
            transition: .4s;
        }

        .toggle-switch input:checked + label {
            background-color: #ff5722;
        }

        .toggle-switch input:checked + label:before {
            transform: translateX(29px);
        }
    </style>
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
    <!-- Bootstrap JS and Popper.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Populate edit modal fields
        document.querySelectorAll('[data-bs-target="#editTileModal"]').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                const url = this.getAttribute('data-url');
                const icon = this.getAttribute('data-icon');
                const groupId = this.getAttribute('data-group-id');
                document.getElementById('editId').value = id;
                document.getElementById('editTitle').value = title;
                document.getElementById('editUrl').value = url;
                document.getElementById('editIcon').value = icon; // Pre-select the icon
                document.getElementById('editGroup').value = groupId; // Pre-fill the group name
            });
        });

        // Save Dashboard Title
        document.addEventListener('DOMContentLoaded', function () {
            const editableTitle = document.getElementById('editable-title');
            const saveButton = document.getElementById('save-title');

            if (editableTitle && saveButton) {
                saveButton.addEventListener('click', function () {
                    const newTitle = editableTitle.value;

                    // Send the updated title to the server
                    fetch('edit_tile.php?action=update_title', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ title: newTitle })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Title updated successfully!');
                            location.reload(); // Reload the page to reflect changes
                        } else {
                            alert('Failed to update title.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            }
        });
        // Settings functionality
        document.addEventListener('DOMContentLoaded', function() {
            const settingsButton = document.getElementById('settingsButton');
            const settingsOverlay = document.getElementById('settingsOverlay');
            const closeSettings = document.getElementById('closeSettings');
            const editModeToggle = document.getElementById('editModeToggle');

            // Open settings overlay
            settingsButton.addEventListener('click', function() {
                settingsOverlay.classList.add('active');
            });

            // Close settings overlay
            closeSettings.addEventListener('click', function() {
                settingsOverlay.classList.remove('active');
            });

            // Handle click outside to close
            document.addEventListener('click', function(event) {
                if (!settingsOverlay.contains(event.target) && 
                    !settingsButton.contains(event.target) && 
                    settingsOverlay.classList.contains('active')) {
                    settingsOverlay.classList.remove('active');
                }
            });

            // Toggle edit mode
            editModeToggle.addEventListener('change', function() {
                if (this.checked) {
                    window.location.href = '?edit=true';
                } else {
                    window.location.href = '?exit_edit=true';
                }
            });
        });
    </script>
    <!-- Settings Button (fixed position) -->
    <div class="settings-button" id="settingsButton">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"></circle>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
        </svg>
    </div>

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
                <!-- Add more settings here in the future -->
            </div>
        </div>
    </div>
</body>
</html>