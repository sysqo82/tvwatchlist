FROM mongo:7

# Install cron
RUN apt-get update && apt-get install -y cron && rm -rf /var/lib/apt/lists/*

# Copy the backup script
COPY cron/backup-db-internal.sh /backup-db-internal.sh
RUN chmod +x /backup-db-internal.sh

# Create cron job
RUN echo '0 3 * * 1 /backup-db-internal.sh >> /var/log/cron.log 2>&1' > /etc/cron.d/database-backup \
    && chmod 0644 /etc/cron.d/database-backup \
    && crontab /etc/cron.d/database-backup \
    && touch /var/log/cron.log

# Create entrypoint script
RUN echo '#!/bin/bash\n\
service cron start\n\
exec "$@"' > /entrypoint-wrapper.sh \
    && chmod +x /entrypoint-wrapper.sh

ENTRYPOINT ["/entrypoint-wrapper.sh", "docker-entrypoint.sh"]
CMD ["mongod", "--auth", "--bind_ip_all"]
