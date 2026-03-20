# Certificates

## Production Certificate Plan

When Voidforge has a real domain, use a standard domain-validated TLS certificate.

Recommended certificate:
- `Let's Encrypt` DV certificate

Recommended covered hostnames:
- `yourdomain.com`
- `www.yourdomain.com`

Required files:
- `fullchain.pem`
- `privkey.pem`

Typical Let's Encrypt location on a server:
- `/etc/letsencrypt/live/yourdomain.com/fullchain.pem`
- `/etc/letsencrypt/live/yourdomain.com/privkey.pem`

## What To Add When The Domain Exists

1. Point DNS for the domain to the server.
2. Issue a certificate for:
   - `yourdomain.com`
   - `www.yourdomain.com`
3. Mount or copy the certificate files into the app container.
4. Set the certificate environment variables.
5. Set `APP_URL` to the real HTTPS domain.
6. Keep HTTP redirected to HTTPS.

## Environment Variables

These are already supported by the Docker startup flow:

- `CERT_MODE=provided`
- `CERT_DOMAIN=yourdomain.com`
- `CERT_SERVER_NAMES="yourdomain.com www.yourdomain.com"`
- `CERT_CRT_PATH=/etc/letsencrypt/live/yourdomain.com/fullchain.pem`
- `CERT_KEY_PATH=/etc/letsencrypt/live/yourdomain.com/privkey.pem`
- `APP_URL=https://yourdomain.com`

## Current Local Development Mode

Without a domain, Voidforge should stay on self-signed local certificates:

- `CERT_MODE=self-signed`
- `CERT_DOMAIN=localhost`
- `CERT_SERVER_NAMES="localhost 127.0.0.1"`
- `CERT_ALT_NAMES=DNS:localhost,IP:127.0.0.1`

This is only for local development and browser testing.

## Notes

- A real public certificate cannot be issued before you control a domain.
- Do not commit real private keys to version control.
- Prefer automatic renewal for production certificates.
- After the domain is ready, also consider adding:
  - HSTS
  - secure cookies
  - CAA DNS records
