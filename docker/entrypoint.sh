#!/bin/sh
set -e

if [ ! -f "vendor/autoload.php" ]; then
  echo "Installing Composer dependencies..."
  composer install --no-interaction --prefer-dist
fi

php artisan config:clear
php artisan cache:clear

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  php artisan migrate --force || true
fi

php artisan storage:link 2>/dev/null || true

exec php artisan serve --host=0.0.0.0 --port=8000
