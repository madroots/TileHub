FROM nginx:1.25-alpine

# Copy nginx configuration
COPY custom-images/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy application files
COPY app/ /var/www/html/

# Copy entrypoint script
COPY custom-images/nginx/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set proper permissions to allow both containers to access
RUN mkdir -p /var/www/html/uploads && \
    chown -R nginx:nginx /var/cache/nginx && \
    chown -R nginx:nginx /var/log/nginx && \
    chown -R nginx:nginx /etc/nginx/conf.d && \
    chown -R 80:80 /var/www/html/uploads && \
    chmod -R 777 /var/www/html/uploads

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["nginx", "-g", "daemon off;"]