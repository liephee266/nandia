#!/bin/sh
set -e

JWT_DIR=/app/config/jwt
if [ ! -f "$JWT_DIR/private.pem" ] || [ ! -f "$JWT_DIR/public.pem" ]; then
  echo "[entrypoint] Clés JWT absentes — génération automatique..."
  mkdir -p "$JWT_DIR"
  php bin/console lexik:jwt:generate-keypair --skip-if-exists
  echo "[entrypoint] Clés JWT générées."
fi

exec php -S 0.0.0.0:80 -t /app/public /app/public/index.php
