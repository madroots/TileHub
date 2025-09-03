#!/bin/sh
# Fix permissions for uploads directory
# Set proper ownership (80:80 is www-data in Alpine) and permissions
chown -R 80:80 /var/www/html/uploads
chmod -R 755 /var/www/html/uploads
# Ensure the web server can create files in the uploads directory
chmod 775 /var/www/html/uploads

# Execute the default entrypoint
exec "$@"