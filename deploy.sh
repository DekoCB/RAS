#!/usr/bin/env bash
# Ejecutar por SSH en Hostinger, en la carpeta del proyecto, tras cada git pull.
set -e
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan storage:link 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 775 storage bootstrap/cache
