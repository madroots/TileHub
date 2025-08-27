FROM nginx:1.25-alpine

# Copy nginx configuration
COPY custom-images/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy application files
COPY app/ /var/www/html/

# Create uploads directory and set proper permissions
RUN mkdir -p /var/www/html/uploads && \
    chown -R nginx:nginx /var/cache/nginx && \
    chown -R nginx:nginx /var/log/nginx && \
    chown -R nginx:nginx /etc/nginx/conf.d && \
    chown -R nginx:nginx /var/www/html/