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