#!/bin/bash
echo "Running food database seeder..."
php artisan db:seed --class=FoodSeeder
echo "Done! Foods have been added to the database."
