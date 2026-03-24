#!/usr/bin/env sh
set -eu

PORT="${PORT:-8080}"

mkdir -p /tmp/nginx
mkdir -p /tmp/nginx-client-body

# Generate APP_KEY if not set — this key will not persist across restarts.
# Set APP_KEY as a Railway environment variable to avoid this.
if [ -z "${APP_KEY:-}" ]; then
    echo "WARNING: APP_KEY is not set. Generating a temporary key." \
         "Set APP_KEY in Railway environment variables for a stable value." >&2
    php artisan key:generate --force
fi

# Wait for MariaDB to accept connections before running migrations
echo "Waiting for MariaDB at ${DB_HOST}:${DB_PORT:-3306}..." >&2
RETRIES=30
until mysqladmin ping -h"${DB_HOST}" -P"${DB_PORT:-3306}" \
      -u"${DB_USERNAME}" -p"${DB_PASSWORD}" --silent 2>/dev/null; do
    RETRIES=$((RETRIES - 1))
    if [ "${RETRIES}" -eq 0 ]; then
        echo "MariaDB did not become ready in time." >&2
        exit 1
    fi
    sleep 2
done

php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

cat > /tmp/nginx/nginx.conf <<EOF
worker_processes 1;
pid /tmp/nginx.pid;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    access_log /dev/stdout;
    error_log /dev/stderr warn;

    sendfile on;
    keepalive_timeout 65;

    client_body_temp_path /tmp/nginx-client-body;
    proxy_temp_path /tmp/nginx-proxy;
    fastcgi_temp_path /tmp/nginx-fastcgi;
    uwsgi_temp_path /tmp/nginx-uwsgi;
    scgi_temp_path /tmp/nginx-scgi;

    server {
        listen ${PORT};
        server_name _;

        location / {
            proxy_pass http://127.0.0.1:9000;
            proxy_http_version 1.1;
            proxy_set_header Host \$host;
            proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto \$http_x_forwarded_proto;
            proxy_set_header X-Forwarded-Port \$http_x_forwarded_port;
        }
    }
}
EOF

php artisan serve --host=127.0.0.1 --port=9000 &
APP_SERVER_PID=$!

trap 'kill ${APP_SERVER_PID} >/dev/null 2>&1 || true' INT TERM EXIT

# Wait for PHP serve to accept requests before exposing nginx to Railway's
# health check — prevents 502s during the /up probe on first boot
echo "Waiting for PHP server on port 9000..." >&2
until curl -sf http://127.0.0.1:9000/up >/dev/null 2>&1; do
    sleep 1
done

exec nginx -c /tmp/nginx/nginx.conf -g 'daemon off;'
