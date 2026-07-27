# Session Duration Configuration Feature - Testing Guide

## Overview
This feature allows administrators to configure the session lifetime (how long users can stay logged in without activity) through the admin panel instead of editing configuration files.

## Feature Components

### 1. Database Migration
- **File**: `database/migrations/2026_01_08_184121_add_session_lifetime_to_settings_table.php`
- **Purpose**: Adds session_lifetime setting to the settings table
- **Default Value**: 480 minutes (8 hours)

### 2. Backend Controller
- **File**: `app/Http/Controllers/SettingsController.php`
- **Method**: `updateSessionLifetime(Request $request)`
- **Validation**: Min 5 minutes, Max 43200 minutes (30 days)
- **Access**: Admin and Super Admin only

### 3. Service Provider
- **File**: `app/Providers/SessionConfigServiceProvider.php`
- **Purpose**: Loads session lifetime from database on application boot
- **Registered in**: `config/app.php`

### 4. Frontend UI
- **File**: `resources/views/settings/index.blade.php`
- **Location**: Settings → System Settings tab
- **Features**:
  - Input field with min/max validation
  - Real-time duration display (hours and minutes)
  - AJAX form submission
  - Success/error notifications

### 5. Route
- **Route**: `POST /settings/session-lifetime`
- **Name**: `settings.update-session-lifetime`
- **Middleware**: `can:access-settings`

## Testing Instructions

### Test 1: Verify Database Setup
```bash
php artisan tinker --execute="
\$setting = DB::table('settings')
    ->whereNull('clinic_id')
    ->where('key', 'session_lifetime')
    ->first();

if (\$setting) {
    echo 'Session lifetime: ' . \$setting->value . ' minutes' . PHP_EOL;
} else {
    echo 'Setting not found!' . PHP_EOL;
}
"
```

**Expected Result**: Should show "Session lifetime: 480 minutes"

### Test 2: Verify Config Loading
```bash
php artisan tinker --execute="
echo 'Session config: ' . config('session.lifetime') . ' minutes' . PHP_EOL;
"
```

**Expected Result**: Should show "Session config: 480 minutes"

### Test 3: Access Admin UI
1. Log in as an admin user
2. Navigate to Settings → System Settings tab
3. Scroll to "Session Settings" section

**Expected Result**: 
- Should see session lifetime input field
- Current value should be 480
- Display should show "8 hours 0 minutes"

### Test 4: Update Session Lifetime (Valid Values)
1. In the Session Settings form, change value to `120` (2 hours)
2. Click "Update Session Lifetime"

**Expected Result**:
- Success message: "Session lifetime updated successfully. New sessions will use 120 minutes."
- Display updates to "2 hours 0 minutes"

### Test 5: Update Session Lifetime (Edge Cases)

**Test 5a: Minimum Value**
- Enter `5` (5 minutes)
- Click update
- **Expected**: Success

**Test 5b: Maximum Value**
- Enter `43200` (30 days)
- Click update
- **Expected**: Success

**Test 5c: Below Minimum**
- Enter `4` (below minimum)
- Click update
- **Expected**: Validation error

**Test 5d: Above Maximum**
- Enter `50000` (above maximum)
- Click update
- **Expected**: Validation error

### Test 6: Verify Database Update
```bash
php artisan tinker --execute="
\$value = DB::table('settings')
    ->whereNull('clinic_id')
    ->where('key', 'session_lifetime')
    ->value('value');

echo 'Database value: ' . \$value . ' minutes' . PHP_EOL;
"
```

**Expected Result**: Should match the value you set in Test 4

### Test 7: Verify New Sessions Use Updated Value
1. Update session lifetime to 60 minutes
2. Log out
3. Log back in
4. Check session expiration time

**Expected Result**: New session should expire after 60 minutes of inactivity

### Test 8: Access Control
1. Log in as a non-admin user (doctor, nurse, etc.)
2. Navigate to Settings

**Expected Result**: 
- Should NOT see "Session Settings" section
- Only admins and super admins can access this feature

### Test 9: Real-time Display Update
1. In the Session Settings form, type different values
2. Watch the "Current" display

**Expected Result**:
- Display should update in real-time as you type
- Should show correct hours and minutes conversion
- Example: 90 → "1 hours 30 minutes"

### Test 10: Form Validation
1. Try to submit empty value
2. Try to submit non-numeric value
3. Try to submit negative value

**Expected Result**: 
- Browser validation should prevent submission
- Input field has `required`, `min="5"`, `max="43200"` attributes

## Manual Testing Checklist

- [ ] Migration ran successfully
- [ ] Default value (480 minutes) is set in database
- [ ] Config loads value from database on boot
- [ ] Admin can access Session Settings UI
- [ ] Non-admin cannot see Session Settings
- [ ] Form displays current value correctly
- [ ] Real-time display updates when typing
- [ ] Can update to valid values (5-43200)
- [ ] Validation prevents invalid values
- [ ] Success message appears after update
- [ ] Database value updates correctly
- [ ] New sessions use updated lifetime
- [ ] Config cache cleared after update

## Troubleshooting

### Issue: Session Settings not visible
**Solution**: 
- Ensure you're logged in as admin or super admin
- Check user role: `php artisan tinker --execute="echo auth()->user()->role;"`

### Issue: Update not working
**Solution**:
- Clear config cache: `php artisan config:clear`
- Clear application cache: `php artisan cache:clear`
- Check browser console for JavaScript errors

### Issue: Config not loading from database
**Solution**:
- Verify SessionConfigServiceProvider is registered in `config/app.php`
- Clear config cache: `php artisan config:clear`
- Restart server

## Reset to Default
```bash
php artisan tinker --execute="
DB::table('settings')->updateOrInsert(
    ['clinic_id' => null, 'key' => 'session_lifetime'],
    ['value' => '480', 'type' => 'integer', 'updated_at' => now()]
);
echo 'Reset to 480 minutes (8 hours)' . PHP_EOL;
"
```

## Feature Summary

✅ **Implemented**:
- Database migration with default value
- Backend controller with validation
- Service provider for config loading
- Admin UI with real-time updates
- Route with proper middleware
- Access control (admin only)
- Success/error notifications

✅ **Security**:
- Only admins can update
- Input validation (min/max)
- CSRF protection
- Middleware protection

✅ **User Experience**:
- Real-time display updates
- Clear success/error messages
- Helpful placeholder text
- Min/max constraints visible

