FROM nginx:1.25-alpine

# Copy nginx configuration
COPY custom-images/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy application files
COPY app/ /var/www/html/

# Copy entrypoint script
COPY custom-images/nginx/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set proper permissions for nginx directories
RUN chown -R nginx:nginx /var/cache/nginx && \
    chown -R nginx:nginx /var/log/nginx && \
    chown -R nginx:nginx /etc/nginx/conf.d

# Create uploads directory with proper permissions
RUN mkdir -p /var/www/html/uploads && \
    chown -R 80:80 /var/www/html/uploads && \
    chmod -R 755 /var/www/html/uploads

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["nginx", "-g", "daemon off;"]