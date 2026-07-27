# Auto-Logout Integration with Session Duration Setting - Fix

## Problem
The session lifetime setting in the admin panel (Settings → System Settings → Session Settings) was not connected to the auto-logout feature. Users could set the session lifetime to 5 minutes, but the auto-logout would still use the hardcoded 10-minute timeout from the config file.

## Root Cause
The `SessionActivityController::getConfig()` method was reading the timeout from `config/concure.php` instead of the database setting that admins configure through the UI.

## Solution
Updated the auto-logout system to read the session lifetime from the database (admin-configurable) instead of the hardcoded config value.

## Changes Made

### 1. Updated SessionActivityController
**File**: `app/Http/Controllers/SessionActivityController.php`

**Changes**:
- Modified `getConfig()` method to query the database for session_lifetime setting
- Database value takes precedence over config file value
- Warning time is auto-calculated as 20% of timeout or 2 minutes (whichever is smaller)

**Before**:
```php
public function getConfig()
{
    $config = config('concure.auto_logout', []);
    
    return response()->json([
        'enabled' => $config['enabled'] ?? true,
        'timeout_minutes' => $config['timeout_minutes'] ?? 10, // Hardcoded!
        'warning_minutes' => $config['warning_minutes'] ?? 2,
        // ...
    ]);
}
```

**After**:
```php
public function getConfig()
{
    $config = config('concure.auto_logout', []);
    
    // Get session lifetime from database (admin-configurable)
    $sessionLifetime = \DB::table('settings')
        ->whereNull('clinic_id')
        ->where('key', 'session_lifetime')
        ->value('value');
    
    // Use database value if available, otherwise fall back to config
    $timeoutMinutes = $sessionLifetime ? (int) $sessionLifetime : ($config['timeout_minutes'] ?? 10);
    
    // Warning should be 2 minutes before timeout, or 20% of timeout (whichever is smaller)
    $warningMinutes = min(2, (int) ($timeoutMinutes * 0.2));
    
    return response()->json([
        'enabled' => $config['enabled'] ?? true,
        'timeoutMinutes' => $timeoutMinutes,
        'warningMinutes' => $warningMinutes,
        'keepaliveInterval' => $config['keepalive_interval'] ?? 60,
        'timeoutSeconds' => $timeoutMinutes * 60,
        'warningSeconds' => $warningMinutes * 60,
    ]);
}
```

### 2. Updated Config Documentation
**File**: `config/concure.php`

**Changes**:
- Added clear documentation that timeout is now configurable via Admin Panel
- Marked config value as "FALLBACK ONLY"
- Added instructions to use Settings → System Settings → Session Settings

### 3. Enabled Auto-Logout
**File**: `.env`

**Changes**:
- Changed `CONCURE_AUTO_LOGOUT_ENABLED=false` to `CONCURE_AUTO_LOGOUT_ENABLED=true`

## How It Works Now

### Admin Workflow:
1. Admin navigates to Settings → System Settings → Session Settings
2. Admin sets session lifetime to desired value (e.g., 5 minutes)
3. Clicks "Update Session Lifetime"
4. Database is updated with new value
5. All users immediately get the new timeout on their next page load

### Auto-Logout Behavior:
1. When user logs in, JavaScript loads config from `/session/config`
2. Server queries database for `session_lifetime` setting
3. Returns timeout in minutes and seconds
4. JavaScript starts inactivity timer with database value
5. After inactivity period, warning dialog appears
6. If no activity, user is automatically logged out

### Warning Time Calculation:
- For timeouts ≥ 10 minutes: Warning shows 2 minutes before logout
- For timeouts < 10 minutes: Warning shows at 20% of timeout
- Examples:
  - 5 min timeout → 1 min warning (at 4 min mark)
  - 10 min timeout → 2 min warning (at 8 min mark)
  - 30 min timeout → 2 min warning (at 28 min mark)

## Testing

### Test 1: Verify Config Endpoint
```bash
curl -s http://127.0.0.1:8001/session/config | python3 -m json.tool
```

**Expected Output** (with 5-minute setting):
```json
{
    "enabled": true,
    "timeoutMinutes": 5,
    "warningMinutes": 1,
    "keepaliveInterval": 60,
    "timeoutSeconds": 300,
    "warningSeconds": 60
}
```

### Test 2: Verify Auto-Logout Works
1. Set session lifetime to 5 minutes in admin panel
2. Log in to the application
3. Open browser console (F12)
4. Look for auto-logout initialization messages:
   ```
   🔒 Auto-logout script loaded
   🔒 Auto-logout: Starting initialization...
   📡 Loading auto-logout config from server...
   📥 Server config received: {enabled: true, timeoutMinutes: 5, ...}
   ✅ Auto-logout initialized successfully: {timeout: "5 minutes (300 seconds)", ...}
   ```
5. Wait 4 minutes without any activity (don't move mouse, don't type)
6. At 4 minutes, warning dialog should appear: "Your session will expire in 1 minute"
7. Wait 1 more minute without clicking "Stay Logged In"
8. At 5 minutes, you should be automatically logged out

### Test 3: Verify Warning Dialog
1. Set session lifetime to 10 minutes
2. Wait 8 minutes without activity
3. Warning dialog should appear: "Your session will expire in 2 minutes"
4. Click "Stay Logged In"
5. Timer should reset, and you stay logged in

### Test 4: Verify Activity Tracking
1. Set session lifetime to 5 minutes
2. Every 2-3 minutes, move your mouse or type something
3. You should NOT be logged out (activity resets the timer)
4. Check console for: "🔄 Activity detected, resetting inactivity timer"

## Configuration Priority

The system now uses this priority order:

1. **Database Setting** (Highest Priority)
   - Set via: Settings → System Settings → Session Settings
   - Stored in: `settings` table, `session_lifetime` key
   - Applies to: Auto-logout timeout

2. **Config File** (Fallback)
   - File: `config/concure.php`
   - Used only if database setting doesn't exist
   - Applies to: Auto-logout timeout

3. **Environment Variable** (.env)
   - Used for: Enabling/disabling auto-logout feature
   - `CONCURE_AUTO_LOGOUT_ENABLED=true`

## Benefits

✅ **Single Source of Truth**: Session lifetime is configured in one place (admin panel)
✅ **No Code Changes**: Admins can adjust timeout without editing files
✅ **Immediate Effect**: Changes apply to all users on next page load
✅ **Smart Warnings**: Warning time auto-adjusts based on timeout
✅ **Consistent Behavior**: Auto-logout matches session lifetime setting

## Troubleshooting

### Issue: Auto-logout not working
**Solution**:
1. Check if enabled: `grep CONCURE_AUTO_LOGOUT_ENABLED .env`
2. Should be: `CONCURE_AUTO_LOGOUT_ENABLED=true`
3. Clear cache: `php artisan config:clear`
4. Refresh browser page

### Issue: Wrong timeout value
**Solution**:
1. Check database: `php artisan tinker --execute="echo DB::table('settings')->whereNull('clinic_id')->where('key', 'session_lifetime')->value('value');"`
2. Check config endpoint: `curl http://127.0.0.1:8001/session/config`
3. Clear cache: `php artisan config:clear && php artisan cache:clear`
4. Hard refresh browser: Ctrl+Shift+R (or Cmd+Shift+R on Mac)

### Issue: Warning appears too early/late
**Solution**:
- Warning time is auto-calculated
- For 5 min timeout: Warning at 4 min (1 min before)
- For 10+ min timeout: Warning at timeout-2 min (2 min before)
- This is by design and cannot be changed without code modification

## Summary

The auto-logout feature is now fully integrated with the admin-configurable session duration setting. When admins change the session lifetime in the Settings page, the auto-logout timeout automatically updates to match. This provides a consistent, user-friendly experience where session management is controlled from a single location.

