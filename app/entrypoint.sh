#!/bin/sh
# Fix permissions for uploads directory
mkdir -p /var/www/html/uploads

# Try to set proper ownership and permissions
# We might not be able to change ownership if we're not root, but we can try to set group permissions
chown -R 80:80 /var/www/html/uploads 2>/dev/null || true
chmod -R 755 /var/www/html/uploads 2>/dev/null || true

# Ensure the web server can create files in the uploads directory
# If we can't change ownership, at least make it group-writable
chmod 775 /var/www/html/uploads 2>/dev/null || chmod 777 /var/www/html/uploads 2>/dev/null || true

# Make sure we can write to the directory by testing it
touch /var/www/html/uploads/.entrypoint_test 2>/dev/null && rm /var/www/html/uploads/.entrypoint_test 2>/dev/null || {
    echo "Warning: Unable to write to uploads directory. This may cause issues with icon uploads."
    echo "Uploads directory: $(ls -la /var/www/html/ | grep uploads)"
}

# Clean up old temporary directories
find /var/www/html/uploads -name "tmp_export_*" -type d -mtime +1 -exec rm -rf {} + 2>/dev/null || true
find /var/www/html/uploads -name "tmp_extract_*" -type d -mtime +1 -exec rm -rf {} + 2>/dev/null || true

# Execute the default entrypoint
exec "$@"