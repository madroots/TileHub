<?php
// Include database connection
require_once 'db.php';

// Initialize session
session_start();

// Check if in edit mode
if (isset($_GET['edit']) && $_GET['edit'] === 'true') {
    $_SESSION['edit_mode'] = true;
} elseif (isset($_GET['exit_edit'])) {
    unset($_SESSION['edit_mode']);
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
    </style>
</head>
<body class="p-4">
    <div class="container">
        <h1 class="mb-4">Welcome</h1>

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
    </script>
</body>
</html>