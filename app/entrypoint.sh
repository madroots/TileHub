#!/bin/sh
# Fix permissions for uploads directory
mkdir -p /var/www/html/uploads
# Set proper ownership (80:80 is www-data in Alpine) and permissions
chown -R 80:80 /var/www/html/uploads
chmod -R 755 /var/www/html/uploads
# Ensure the web server can create files in the uploads directory
chmod 775 /var/www/html/uploads

# Clean up old temporary directories
find /var/www/html/uploads -name "tmp_export_*" -type d -mtime +1 -exec rm -rf {} + 2>/dev/null || true
find /var/www/html/uploads -name "tmp_extract_*" -type d -mtime +1 -exec rm -rf {} + 2>/dev/null || true

# Execute the default entrypoint
exec "$@"