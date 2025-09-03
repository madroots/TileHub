# TileHub Permissions Guide

This document explains the proper permissions setup for TileHub to ensure security and functionality.

## Current Permission Setup

### Directory Permissions
- Uploads directory: `755` (readable by all, writable only by owner)
- Temporary directories: `755` (created during import/export operations)

### File Permissions
- Uploaded icons: `644` (readable by all, writable only by owner)
- Exported ZIP files: `644` (readable by all, writable only by owner)

### Ownership
- All files and directories are owned by UID/GID `80:80` (www-data in Alpine Linux)
- The app container runs as this user to ensure it can access the uploads directory
- The web container runs as root (nginx default) but sets proper ownership on the uploads directory

## Why This Setup Works

1. **Security**: No world-writable directories (no 777 permissions)
2. **Functionality**: Both containers can read/write files because:
   - The app container runs as UID/GID 80:80
   - The web container sets the uploads directory ownership to 80:80
   - Both containers can access files owned by 80:80
3. **Import/Export**: Works correctly because temporary files are created with proper permissions
4. **Icon Uploads**: Work correctly because the uploads directory is writable by the web server user

## Docker Configuration

The production and development docker-compose files specify:
```yaml
tilehub-app:
  user: "80:80"  # App container runs as www-data user
# tilehub-web does not specify user, runs as root (nginx default)
```

This ensures the app container can access the shared uploads volume while the web container can still perform its required operations as root.

## Testing Changes

To test these changes, use the development docker-compose file which builds images from source:

```bash
# Bring down any existing containers
docker compose -f docker-compose.dev.yml down

# Build and start containers with the new configuration
docker compose -f docker-compose.dev.yml up -d --build

# Check that containers are running
docker compose -f docker-compose.dev.yml ps

# Access the application at http://localhost:5201
```

The development setup uses port 5201 (vs 5200 for production) and mounts the local `./app` directory for easier development.

## Troubleshooting

If you encounter permission issues:

1. Check that the app container is running as UID/GID 80:80
2. Verify the uploads volume is properly mounted in both containers
3. Ensure the uploads directory has 755 permissions and is owned by 80:80
4. Check that individual files have 644 permissions

## Changes Made

This setup replaces the previous insecure 777 permissions with a more secure configuration that still allows all necessary functionality.