#!/bin/bash

# ConCure Final Fix Script
echo "🔧 ConCure Final Fix - Bypassing All Errors"
echo "==========================================="

# Step 1: Clear everything and start fresh
echo "🧹 Clearing all caches and locks..."
rm -rf vendor/
rm -f composer.lock
rm -rf bootstrap/cache/*.php
composer clear-cache

# Step 2: Install without scripts to avoid errors
echo "📦 Installing dependencies without scripts..."
composer install --no-scripts --no-interaction --prefer-dist

# Step 3: Generate application key manually
echo "🔑 Generating application key..."
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate a random 32-character key
APP_KEY="base64:$(openssl rand -base64 32)"
if [[ "$OSTYPE" == "darwin"* ]]; then
    sed -i '' "s/APP_KEY=.*/APP_KEY=${APP_KEY}/" .env
else
    sed -i "s/APP_KEY=.*/APP_KEY=${APP_KEY}/" .env
fi
echo "✅ Application key generated: $APP_KEY"

# Step 4: Setup database
echo "🗄️ Setting up database..."
mkdir -p database
touch database/concure.sqlite
chmod 664 database/concure.sqlite

# Update database path
CURRENT_DIR=$(pwd)
if [[ "$OSTYPE" == "darwin"* ]]; then
    sed -i '' "s|DB_DATABASE=.*|DB_DATABASE=${CURRENT_DIR}/database/concure.sqlite|" .env
else
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=${CURRENT_DIR}/database/concure.sqlite|" .env
fi

# Step 5: Create missing middleware files
echo "🛡️ Creating missing middleware..."

# Create Authenticate middleware
cat > app/Http/Middleware/Authenticate.php << 'EOF'
<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
EOF

# Create RedirectIfAuthenticated middleware
cat > app/Http/Middleware/RedirectIfAuthenticated.php << 'EOF'
<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
EOF

# Create ValidateSignature middleware
cat > app/Http/Middleware/ValidateSignature.php << 'EOF'
<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ValidateSignature as Middleware;

class ValidateSignature extends Middleware
{
    protected $except = [
        // 'fbclid',
        // 'utm_campaign',
        // 'utm_content',
        // 'utm_medium',
        // 'utm_source',
        // 'utm_term',
    ];
}
EOF

# Step 6: Create missing controllers
echo "🎮 Creating missing controllers..."

# Create SettingsController
cat > app/Http/Controllers/SettingsController.php << 'EOF'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings.index');
    }
}
EOF

# Step 7: Manually run autoload without scripts
echo "🔄 Generating autoload files..."
composer dump-autoload --no-scripts

# Step 8: Create storage directories
echo "📁 Creating storage directories..."
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Step 9: Set permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache

# Step 10: Create a simple migration runner
echo "🌱 Setting up database schema..."
cat > setup-db.php << 'EOF'
<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Run migrations
echo "Running migrations...\n";
$kernel->call('migrate', ['--force' => true, '--seed' => true]);

echo "Database setup complete!\n";
EOF

php setup-db.php
rm setup-db.php

echo ""
echo "🎉 ConCure is now ready!"
echo ""
echo "🚀 Starting development server..."
echo "📱 ConCure will be available at: http://localhost:8000"
echo ""
echo "🔑 Login credentials:"
echo "   Program Owner: program_owner / ConCure2024!"
echo "   Admin: admin / admin123"
echo "   Doctor: doctor / doctor123"
echo ""

# Start the server
php artisan serve
