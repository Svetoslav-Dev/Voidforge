#!/usr/bin/env sh
set -eu

mkdir -p storage/certs

if [ ! -f storage/certs/localhost.crt ] || [ ! -f storage/certs/localhost.key ]; then
    openssl req -x509 -nodes -newkey rsa:2048 \
        -keyout storage/certs/localhost.key \
        -out storage/certs/localhost.crt \
        -days 3650 \
        -subj "/CN=localhost" \
        -addext "subjectAltName=DNS:localhost,IP:127.0.0.1"
fi

php artisan serve --host=127.0.0.1 --port=9000 &
APP_SERVER_PID=$!

trap 'kill ${APP_SERVER_PID} >/dev/null 2>&1 || true' INT TERM EXIT

exec nginx -c /etc/nginx/nginx.conf -g 'daemon off;'
