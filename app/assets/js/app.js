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

        // Group editing functionality
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-group-btn')) {
                const button = e.target.closest('.edit-group-btn');
                const groupId = button.getAttribute('data-group-id');
                const groupName = button.getAttribute('data-group-name');
                
                // Prompt for new group name
                const newGroupName = prompt('Enter new name for group "' + groupName + '":', groupName);
                
                if (newGroupName !== null && newGroupName.trim() !== '' && newGroupName !== groupName) {
                    // Send request to update group name
                    fetch('edit_tile.php?action=update_group_name', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ 
                            id: groupId,
                            name: newGroupName.trim()
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload the page to reflect changes
                            location.reload();
                        } else {
                            alert('Failed to update group name: ' + (data.error || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to update group name.');
                    });
                }
            }
            
            // Group deletion confirmation
            if (e.target.closest('.delete-group-btn')) {
                const button = e.target.closest('.delete-group-btn');
                const groupId = button.getAttribute('data-group-id');
                const groupName = button.getAttribute('data-group-name');
                
                if (confirm('Are you sure you want to delete the group "' + groupName + '"? All tiles in this group will be moved to another group.')) {
                    // Send request to delete group
                    fetch('edit_tile.php?action=delete_group', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ 
                            id: groupId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload the page to reflect changes
                            location.reload();
                        } else {
                            alert('Failed to delete group: ' + (data.error || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to delete group.');
                    });
                }
            }
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
            const showSettingsButtonToggle = document.getElementById('showSettingsButtonToggle');

            // Open settings overlay
            if (settingsButton) {
                settingsButton.addEventListener('click', function() {
                    settingsOverlay.classList.add('active');
                });
            }

            // Close settings overlay
            closeSettings.addEventListener('click', function() {
                settingsOverlay.classList.remove('active');
            });

            // Handle click outside to close
            document.addEventListener('click', function(event) {
                if (settingsOverlay && !settingsOverlay.contains(event.target) && 
                    settingsButton && !settingsButton.contains(event.target) && 
                    settingsOverlay.classList.contains('active')) {
                    settingsOverlay.classList.remove('active');
                }
            });

            // Toggle edit mode
            if (editModeToggle) {
                editModeToggle.addEventListener('change', function() {
                    if (this.checked) {
                        window.location.href = '?edit=true';
                    } else {
                        window.location.href = '?exit_edit=true';
                    }
                });
            }

            // Toggle settings button visibility
            if (showSettingsButtonToggle) {
                showSettingsButtonToggle.addEventListener('change', function() {
                    // Send request to update setting
                    fetch('edit_tile.php?action=update_setting', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ 
                            key: 'show_settings_button',
                            value: this.checked ? 'true' : 'false'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload the page to reflect changes
                            location.reload();
                        } else {
                            alert('Failed to update setting.');
                            // Revert the toggle if the update failed
                            this.checked = !this.checked;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to update setting.');
                        // Revert the toggle if the update failed
                        this.checked = !this.checked;
                    });
                });
            }
        });