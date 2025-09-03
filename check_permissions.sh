#!/bin/bash
# TileHub Permission Check Script
# This script helps verify that the uploads directory has the correct permissions

echo "=== TileHub Permission Check ==="

# Check if we're in the TileHub directory
if [ ! -f "docker-compose.yml" ] && [ ! -f "docker-compose.dev.yml" ]; then
    echo "Error: This script must be run from the TileHub root directory"
    echo "Please navigate to your TileHub directory and run this script again"
    exit 1
fi

echo "Checking uploads directory permissions..."

# Check if uploads directory exists
if [ ! -d "app/uploads" ]; then
    echo "Creating uploads directory..."
    mkdir -p app/uploads
fi

# Check current permissions
UPLOADS_PERMS=$(stat -c "%a" app/uploads 2>/dev/null || stat -f "%OLp" app/uploads 2>/dev/null)
UPLOADS_OWNER=$(stat -c "%u:%g" app/uploads 2>/dev/null || stat -f "%u:%g" app/uploads 2>/dev/null)

echo "Uploads directory permissions: $UPLOADS_PERMS"
echo "Uploads directory owner: $UPLOADS_OWNER"

# Check if permissions are adequate
if [ "$UPLOADS_PERMS" = "755" ] || [ "$UPLOADS_PERMS" = "775" ] || [ "$UPLOADS_PERMS" = "777" ]; then
    echo "✓ Permissions look OK for TileHub"
else
    echo "⚠ Permissions might need adjustment for TileHub"
    echo "  Recommended: chmod 755 app/uploads or chmod 775 app/uploads"
fi

# Try to create a test file
echo "Testing write access..."
if touch app/uploads/tilehub_permission_test.tmp 2>/dev/null; then
    echo "✓ Write access OK"
    rm -f app/uploads/tilehub_permission_test.tmp
else
    echo "✗ Write access FAILED"
    echo "  You may need to adjust permissions:"
    echo "  On Linux/macOS: sudo chown -R 80:80 app/uploads && chmod 775 app/uploads"
    echo "  As a last resort: chmod -R 777 app/uploads (less secure)"
fi

echo ""
echo "=== Recommendations ==="
echo "For production use: chmod 755 app/uploads"
echo "For development use: chmod 775 app/uploads"
echo "If permission issues persist: chmod 777 app/uploads (not recommended for production)"
echo ""
echo "Note: The app container runs as user 80:80 (www-data in Alpine)"