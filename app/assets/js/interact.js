/* drag and drop functionality using SortableJS */
document.addEventListener('DOMContentLoaded', function () {
    // Check if we're in edit mode
    const editMode = document.body.classList.contains('edit-mode');
    
    if (!editMode) {
        return; // Don't initialize drag and drop if not in edit mode
    }
    
    // Add edit mode indicator
    const editModeIndicator = document.createElement('div');
    editModeIndicator.className = 'edit-mode-indicator';
    editModeIndicator.textContent = 'Edit Mode';
    document.body.appendChild(editModeIndicator);
    
    // Import SortableJS
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js';
    script.onload = function() {
        initializeDragAndDrop();
    };
    document.head.appendChild(script);
});

function initializeDragAndDrop() {
    // Initialize Sortable for group containers (reordering groups)
    const tileContainer = document.getElementById('tile-container');
    if (tileContainer) {
        Sortable.create(tileContainer, {
            animation: 150,
            handle: '.group-header',
            draggable: '.group-container',
            ghostClass: 'drag-ghost',
            chosenClass: 'drag-chosen',
            dragClass: 'drag-item',
            onEnd: function (evt) {
                saveGroupPositions();
            }
        });
    }
    
    // Initialize Sortable for tiles within each group
    const groupContainers = document.querySelectorAll('.group-container');
    
    groupContainers.forEach(function(container) {
        const groupTiles = container.querySelector('.group-tiles');
        if (groupTiles) {
            Sortable.create(groupTiles, {
                group: 'tiles',
                animation: 150,
                handle: '.tile-item', // Handle is the entire tile
                ghostClass: 'drag-ghost',
                chosenClass: 'drag-chosen',
                dragClass: 'drag-item',
                onEnd: function (evt) {
                    saveTilePositions();
                },
                // Add empty insert targets
                forceFallback: false
            });
        }
    });
}

function saveTilePositions() {
    // Collect all groups and their tiles
    const groups = document.querySelectorAll('.group-container');
    const updates = [];
    
    groups.forEach(group => {
        const groupId = group.getAttribute('data-group-id');
        const tiles = group.querySelectorAll('.tile-item');
        
        tiles.forEach((tile, index) => {
            updates.push({
                id: tile.getAttribute('data-id'),
                group_id: groupId,
                position: index
            });
        });
    });
    
    // Send updates to server
    if (updates.length > 0) {
        fetch('edit_tile.php?action=update_tile_positions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ tiles: updates })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Failed to save tile positions');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function saveGroupPositions() {
    // Collect all groups and their positions
    const groups = document.querySelectorAll('.group-container');
    const positions = [];
    
    Array.from(groups).forEach((group, index) => {
        positions.push({
            id: group.getAttribute('data-group-id'),
            position: index
        });
    });
    
    // Send positions to server
    if (positions.length > 0) {
        fetch('edit_tile.php?action=update_group_positions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ groups: positions })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Failed to save group positions');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}