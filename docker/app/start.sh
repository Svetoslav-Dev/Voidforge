#!/usr/bin/env sh
set -eu

mkdir -p storage/certs
mkdir -p /tmp/nginx

CERT_MODE="${CERT_MODE:-self-signed}"
CERT_DOMAIN="${CERT_DOMAIN:-localhost}"
CERT_SERVER_NAMES="${CERT_SERVER_NAMES:-localhost 127.0.0.1}"
CERT_ALT_NAMES="${CERT_ALT_NAMES:-DNS:localhost,IP:127.0.0.1}"
APP_HTTP_PORT="${APP_PORT:-8000}"
APP_HTTPS_PORT="${APP_HTTPS_PORT:-8443}"

if [ "${CERT_MODE}" = "provided" ]; then
    CERT_CRT_PATH="${CERT_CRT_PATH:-}"
    CERT_KEY_PATH="${CERT_KEY_PATH:-}"

    if [ -z "${CERT_CRT_PATH}" ] || [ -z "${CERT_KEY_PATH}" ]; then
        echo "Provided certificate mode requires CERT_CRT_PATH and CERT_KEY_PATH." >&2
        exit 1
    fi

    if [ ! -f "${CERT_CRT_PATH}" ] || [ ! -f "${CERT_KEY_PATH}" ]; then
        echo "Provided certificate files were not found." >&2
        exit 1
    fi
else
    CERT_CRT_PATH="storage/certs/${CERT_DOMAIN}.crt"
    CERT_KEY_PATH="storage/certs/${CERT_DOMAIN}.key"

    if [ ! -f "${CERT_CRT_PATH}" ] || [ ! -f "${CERT_KEY_PATH}" ]; then
        openssl req -x509 -nodes -newkey rsa:2048 \
            -keyout "${CERT_KEY_PATH}" \
            -out "${CERT_CRT_PATH}" \
            -days 3650 \
            -subj "/CN=${CERT_DOMAIN}" \
            -addext "subjectAltName=${CERT_ALT_NAMES}"
    fi
fi

case "${CERT_CRT_PATH}" in
    /*) NGINX_CERT_CRT_PATH="${CERT_CRT_PATH}" ;;
    *) NGINX_CERT_CRT_PATH="/var/www/html/${CERT_CRT_PATH}" ;;
esac

case "${CERT_KEY_PATH}" in
    /*) NGINX_CERT_KEY_PATH="${CERT_KEY_PATH}" ;;
    *) NGINX_CERT_KEY_PATH="/var/www/html/${CERT_KEY_PATH}" ;;
esac

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
        listen ${APP_HTTP_PORT};
        server_name ${CERT_SERVER_NAMES};

        location / {
            return 301 https://\$host:${APP_HTTPS_PORT}\$request_uri;
        }
    }

    server {
        listen ${APP_HTTPS_PORT} ssl;
        server_name ${CERT_SERVER_NAMES};

        ssl_certificate ${NGINX_CERT_CRT_PATH};
        ssl_certificate_key ${NGINX_CERT_KEY_PATH};
        ssl_protocols TLSv1.2 TLSv1.3;
        ssl_prefer_server_ciphers on;

        location / {
            proxy_pass http://127.0.0.1:9000;
            proxy_http_version 1.1;
            proxy_set_header Host \$host;
            proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto https;
            proxy_set_header X-Forwarded-Port ${APP_HTTPS_PORT};
        }
    }
}
EOF

php artisan serve --host=127.0.0.1 --port=9000 &
APP_SERVER_PID=$!

trap 'kill ${APP_SERVER_PID} >/dev/null 2>&1 || true' INT TERM EXIT

exec nginx -c /tmp/nginx/nginx.conf -g 'daemon off;'
