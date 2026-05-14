#!/bin/sh

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan tenants:migrate --force
php artisan db:seed --class=DemoTenantSeeder --force

# Start PHP-FPM in background
php-fpm -D

# Start nginx
nginx -g 'daemon off;'