#!/bin/sh
# Fix permissions for uploads directory
mkdir -p /var/www/html/uploads

# Set proper ownership (80:80 is www-data in Alpine) and permissions
# Ensure the directory is writable by both owner and group (775)
chown 80:80 /var/www/html/uploads
chmod 775 /var/www/html/uploads

# Clean up old temporary directories
find /var/www/html/uploads -name "tmp_export_*" -type d -mtime +1 -exec rm -rf {} + 2>/dev/null || true
find /var/www/html/uploads -name "tmp_extract_*" -type d -mtime +1 -exec rm -rf {} + 2>/dev/null || true

# Test write access
if ! touch /var/www/html/uploads/.entrypoint_test 2>/dev/null; then
    echo "ERROR: Cannot write to uploads directory!"
    ls -la /var/www/html/ | grep uploads
    echo "This will cause upload failures."
else
    rm /var/www/html/uploads/.entrypoint_test 2>/dev/null
fi

# Execute the default entrypoint
exec "$@"