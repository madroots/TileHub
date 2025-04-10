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
            // Wallpaper functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const searchWallpaperBtn = document.getElementById('searchWallpaperBtn');
            const wallpaperModal = new bootstrap.Modal(document.getElementById('wallpaperModal'));
            const wallpaperSearchBtn = document.getElementById('wallpaperSearchBtn');
            const wallpaperSearchInput = document.getElementById('wallpaperSearchInput');
            const wallpaperResults = document.getElementById('wallpaperResults');
            const wallpaperLoading = document.getElementById('wallpaperLoading');
            const overlayDarknessSlider = document.getElementById('overlayDarknessSlider');
            const darknessValue = document.getElementById('darknessValue');
            
            // Load saved settings on page load
            loadSavedWallpaper();
            
            // Open wallpaper search modal
            if (searchWallpaperBtn) {
                searchWallpaperBtn.addEventListener('click', function() {
                    wallpaperModal.show();
                });
            }
            
            // Search wallpapers
            if (wallpaperSearchBtn) {
                wallpaperSearchBtn.addEventListener('click', function() {
                    searchWallpapers();
                });
            }
            
            // Allow searching with Enter key
            if (wallpaperSearchInput) {
                wallpaperSearchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        searchWallpapers();
                    }
                });
            }
            
            // Overlay darkness slider
            if (overlayDarknessSlider) {
                // Update display value when slider moves
                overlayDarknessSlider.addEventListener('input', function() {
                    darknessValue.textContent = this.value + '%';
                    
                    // Update overlay darkness in real-time
                    document.documentElement.style.setProperty(
                        '--overlay-opacity', 
                        this.value / 100
                    );
                });
                
                // Save value when slider is released
                overlayDarknessSlider.addEventListener('change', function() {
                    saveDarkness(this.value);
                });
                
                // Load saved darkness value
                loadSavedDarkness();
            }
            
            // Function to search wallpapers
            function searchWallpapers() {
                const query = wallpaperSearchInput.value.trim();
                if (query === '') return;
                
                // Show loading spinner
                wallpaperResults.innerHTML = '';
                wallpaperLoading.classList.remove('d-none');
                
                // Make API request
                fetch(`wallpaper.php?action=search&query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        wallpaperLoading.classList.add('d-none');
                        displayWallpapers(data);
                    })
                    .catch(error => {
                        wallpaperLoading.classList.add('d-none');
                        console.error('Error:', error);
                        wallpaperResults.innerHTML = '<div class="col-12 text-center">Error fetching wallpapers. Please try again.</div>';
                    });
            }
            
            // Function to display wallpaper results
            function displayWallpapers(data) {
                wallpaperResults.innerHTML = '';
                
                if (!data.data || data.data.length === 0) {
                    wallpaperResults.innerHTML = '<div class="col-12 text-center">No wallpapers found. Try a different search term.</div>';
                    return;
                }
                
                data.data.forEach(wallpaper => {
                    const col = document.createElement('div');
                    col.className = 'col-md-4 col-sm-6 mb-3';
                    
                    col.innerHTML = `
                        <img src="${wallpaper.thumbs.large}" 
                            data-original="${wallpaper.path}"
                            class="wallpaper-thumbnail" 
                            alt="Wallpaper">
                    `;
                    
                    // Add click event to select wallpaper
                    col.querySelector('img').addEventListener('click', function() {
                        const wallpaperUrl = this.getAttribute('data-original');
                        setWallpaper(wallpaperUrl);
                        wallpaperModal.hide();
                    });
                    
                    wallpaperResults.appendChild(col);
                });
            }
            
            // Function to set wallpaper
            function setWallpaper(wallpaperUrl) {
                document.body.style.backgroundImage = `url('${wallpaperUrl}')`;
                
                // Save to database
                fetch('wallpaper.php?action=save_wallpaper', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ wallpaper_url: wallpaperUrl })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Failed to save wallpaper');
                    }
                })
                .catch(error => console.error('Error:', error));
            }
            
            // Function to save darkness value
            function saveDarkness(value) {
                fetch('wallpaper.php?action=save_darkness', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ darkness: value })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Failed to save darkness setting');
                    }
                })
                .catch(error => console.error('Error:', error));
            }
            
            // Function to load saved wallpaper
            function loadSavedWallpaper() {
                fetch('settings.php?action=get_settings')
                    .then(response => response.json())
                    .then(data => {
                        if (data.wallpaper_url) {
                            document.body.style.backgroundImage = `url('${data.wallpaper_url}')`;
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
            
            // Function to load saved darkness
            function loadSavedDarkness() {
                fetch('settings.php?action=get_settings')
                    .then(response => response.json())
                    .then(data => {
                        if (data.overlay_darkness) {
                            const darkness = parseInt(data.overlay_darkness);
                            overlayDarknessSlider.value = darkness;
                            darknessValue.textContent = darkness + '%';
                            document.documentElement.style.setProperty(
                                '--overlay-opacity', 
                                darkness / 100
                            );
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        });