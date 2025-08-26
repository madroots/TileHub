FROM mariadb:10.11

# Copy initialization script
COPY init.sql /docker-entrypoint-initdb.d/

# Set proper permissions
RUN chown -R mysql:mysql /var/lib/mysql