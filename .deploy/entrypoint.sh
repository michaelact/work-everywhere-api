#!/bin/bash

# Run database migrations and seeders
php artisan migrate --force
php artisan db:seed --class=AdminPermissionSeeder --force
php artisan db:seed --class=ProjectTaskSeeder --force

# Execute the CMD passed as arguments
exec "$@"
