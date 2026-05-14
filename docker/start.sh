#!/bin/sh

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force
php artisan tenants:migrate --force
php artisan db:seed --class=DemoTenantSeeder --force

# Start PHP-FPM in background
php-fpm -D

# Start nginx
nginx -g 'daemon off;'