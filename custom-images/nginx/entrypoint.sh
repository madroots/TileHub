#!/bin/sh
# Fix permissions for uploads directory
chmod -R 777 /var/www/html/uploads
chown -R 80:80 /var/www/html/uploads

# Execute the default entrypoint
exec "$@"