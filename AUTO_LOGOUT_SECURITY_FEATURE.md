# Auto-Logout Security Feature

## Overview

The Auto-Logout Security Feature automatically logs out users after a period of inactivity to protect sensitive medical data. This is a critical security measure for healthcare applications where unattended workstations could expose patient information.

## Features Implemented

### ✅ 1. Client-Side Activity Tracking
- **Monitored Events**: Mouse movements, clicks, keyboard input, scrolling, touch events
- **Throttled Detection**: Activity events are throttled to once per second to avoid performance issues
- **Page Visibility Tracking**: Detects when browser tab becomes inactive/hidden
- **Smart Activity Detection**: Only tracks meaningful user interactions

### ✅ 2. Inactivity Timer
- **Default Timeout**: 10 minutes of inactivity
- **Configurable Duration**: Easily adjustable via configuration
- **Automatic Reset**: Timer resets on any user activity
- **Warning Period**: 2-minute warning before logout

### ✅ 3. Warning Notification
- **Visual Dialog**: Beautiful modal dialog with countdown timer
- **Countdown Display**: Shows remaining seconds before logout
- **Color-Coded Warning**: Changes color as time runs out (blue → yellow → red)
- **Audio Alert**: Optional beep sound to get user's attention
- **User Actions**:
  - **Stay Logged In**: Resets timer and continues session
  - **Logout Now**: Immediately logs out the user

### ✅ 4. Server-Side Session Validation
- **Session Keep-Alive**: Regular pings to server during active use
- **Session Status Check**: Validates session when page becomes visible
- **Server-Side Timeout**: Matches client-side timeout for consistency
- **Audit Logging**: All auto-logout events are logged with reason

### ✅ 5. Graceful Logout Process
- **Session Cleanup**: Properly invalidates session on both client and server
- **CSRF Token Regeneration**: Security best practice
- **Redirect to Login**: Smooth redirect with informative message
- **Logout Reason Display**: Shows why user was logged out
- **Form Data Preservation**: Saves unsaved form data to localStorage

### ✅ 6. Form Data Preservation
- **Automatic Saving**: Saves form data every 30 seconds
- **Smart Restoration**: Restores data when user logs back in on same page
- **Privacy Protection**: Excludes password fields from preservation
- **Time-Limited**: Only restores data less than 1 hour old
- **Opt-In**: Forms must have `data-preserve="true"` attribute

## Configuration

### Environment Variables

Add to `.env` file:

```env
# Auto-Logout Configuration
CONCURE_AUTO_LOGOUT_ENABLED=true
CONCURE_AUTO_LOGOUT_TIMEOUT=10        # Minutes of inactivity before logout
CONCURE_AUTO_LOGOUT_WARNING=2         # Minutes before logout to show warning
CONCURE_AUTO_LOGOUT_KEEPALIVE=60      # Seconds between keep-alive pings
```

### Configuration File

Located in `config/concure.php`:

```php
'auto_logout' => [
    // Enable/disable auto-logout feature
    'enabled' => env('CONCURE_AUTO_LOGOUT_ENABLED', true),
    
    // Inactivity timeout in minutes (default: 10 minutes)
    'timeout_minutes' => env('CONCURE_AUTO_LOGOUT_TIMEOUT', 10),
    
    // Warning time before logout in minutes (default: 2 minutes)
    'warning_minutes' => env('CONCURE_AUTO_LOGOUT_WARNING', 2),
    
    // Keep-alive ping interval in seconds (default: 60 seconds)
    'keepalive_interval' => env('CONCURE_AUTO_LOGOUT_KEEPALIVE', 60),
    
    // Activities that reset the inactivity timer
    'tracked_events' => [
        'mousemove',
        'mousedown',
        'keypress',
        'scroll',
        'touchstart',
        'click',
    ],
],
```

## How It Works

### Timeline Example (10-minute timeout, 2-minute warning)

```
Time 0:00 - User logs in
          ↓
Time 0:00-8:00 - User is active (timer keeps resetting)
          ↓
Time 8:00 - User stops activity
          ↓
Time 8:00-10:00 - Inactivity timer counting down
          ↓
Time 10:00 - Warning dialog appears (2 minutes before logout)
          ↓
          ├─→ User clicks "Stay Logged In" → Timer resets, session continues
          │
          └─→ User does nothing → Auto-logout at 12:00
```

### Activity Detection Flow

```
User Activity (mouse, keyboard, etc.)
          ↓
Throttled Handler (max once per second)
          ↓
Reset Inactivity Timer
          ↓
Hide Warning Dialog (if shown)
          ↓
Send Keep-Alive Ping (if interval elapsed)
```

### Warning Dialog Flow

```
8 Minutes of Inactivity
          ↓
Show Warning Dialog
          ↓
Start 2-Minute Countdown
          ↓
Play Alert Sound
          ↓
User Action?
  ├─→ "Stay Logged In" → Reset timer, hide dialog
  ├─→ "Logout Now" → Immediate logout
  └─→ No action → Auto-logout after 2 minutes
```

## Implementation Details

### Files Created/Modified

#### New Files:
1. **`app/Http/Controllers/SessionActivityController.php`**
   - Handles session keep-alive pings
   - Checks session status
   - Performs auto-logout with audit logging
   - Provides configuration to JavaScript

2. **`public/js/auto-logout.js`**
   - Client-side activity tracking
   - Inactivity timer management
   - Warning dialog display
   - Keep-alive ping system
   - Form data preservation

3. **`AUTO_LOGOUT_SECURITY_FEATURE.md`**
   - This documentation file

#### Modified Files:
1. **`config/concure.php`**
   - Added auto-logout configuration section

2. **`routes/web.php`**
   - Added session activity routes

3. **`resources/views/layouts/app.blade.php`**
   - Integrated auto-logout script

4. **`resources/views/master/layouts/app.blade.php`**
   - Integrated auto-logout script

5. **`resources/views/auth/login.blade.php`**
   - Added auto-logout reason display

### API Endpoints

#### POST `/session/keep-alive`
- **Purpose**: Keep session alive during active use
- **Auth**: Required
- **Response**: Session status and timestamp

#### GET `/session/status`
- **Purpose**: Check if session is still valid
- **Auth**: Required
- **Response**: Session status and remaining time

#### POST `/session/auto-logout`
- **Purpose**: Perform auto-logout with reason tracking
- **Auth**: Required
- **Body**: `{ "reason": "inactivity" }`
- **Response**: Logout confirmation and redirect URL

#### GET `/session/config`
- **Purpose**: Get auto-logout configuration for JavaScript
- **Auth**: Not required
- **Response**: Configuration object

## Security Benefits

### 🔒 1. Prevents Unauthorized Access
- Automatically logs out users who leave workstations unattended
- Protects against "walk-by" attacks in clinic environments
- Ensures sessions don't remain active indefinitely

### 🔒 2. Compliance with Healthcare Regulations
- Meets HIPAA requirements for automatic session termination
- Demonstrates due diligence in protecting patient data
- Provides audit trail of all logout events

### 🔒 3. Configurable Security Levels
- Adjust timeout based on clinic's security policy
- Shorter timeouts for high-security areas
- Longer timeouts for less sensitive operations

### 🔒 4. User-Friendly Security
- Warning dialog gives users chance to stay logged in
- Preserves form data to prevent work loss
- Clear messaging about why logout occurred

## User Experience

### For Active Users
- **Transparent**: No interruption during normal use
- **Responsive**: Activity immediately resets timer
- **Informative**: Clear warning before logout

### For Inactive Users
- **Fair Warning**: 2-minute notice before logout
- **Easy Recovery**: One-click to stay logged in
- **Data Protection**: Form data preserved when possible

### For Returning Users
- **Clear Messaging**: Explains why they were logged out
- **Quick Re-login**: Standard login process
- **Data Restoration**: Unsaved form data may be restored

## Testing Scenarios

### Test 1: Normal Activity
1. Log in to the system
2. Use the application normally (clicking, typing, etc.)
3. **Expected**: No logout, timer keeps resetting

### Test 2: Warning Dialog
1. Log in to the system
2. Wait 8 minutes without any activity
3. **Expected**: Warning dialog appears with 2-minute countdown

### Test 3: Stay Logged In
1. Trigger warning dialog (wait 8 minutes)
2. Click "Stay Logged In" button
3. **Expected**: Dialog closes, timer resets, session continues

### Test 4: Auto-Logout
1. Trigger warning dialog (wait 8 minutes)
2. Don't interact with the dialog
3. Wait 2 more minutes
4. **Expected**: Automatic logout, redirect to login page with message

### Test 5: Form Data Preservation
1. Start filling out a form with `data-preserve="true"`
2. Wait for auto-logout
3. Log back in and return to same page
4. **Expected**: Form data is restored

### Test 6: Multiple Tabs
1. Open application in multiple tabs
2. Be active in one tab
3. **Expected**: All tabs stay logged in (shared session)

## Customization Examples

### Shorter Timeout (5 minutes)
```env
CONCURE_AUTO_LOGOUT_TIMEOUT=5
CONCURE_AUTO_LOGOUT_WARNING=1
```

### Longer Timeout (30 minutes)
```env
CONCURE_AUTO_LOGOUT_TIMEOUT=30
CONCURE_AUTO_LOGOUT_WARNING=5
```

### Disable Auto-Logout (Not Recommended)
```env
CONCURE_AUTO_LOGOUT_ENABLED=false
```

### More Frequent Keep-Alive
```env
CONCURE_AUTO_LOGOUT_KEEPALIVE=30  # Every 30 seconds
```

## Troubleshooting

### Issue: Users complain about frequent logouts
**Solution**: Increase timeout duration in configuration

### Issue: Warning dialog doesn't appear
**Solution**: Check browser console for JavaScript errors, ensure script is loaded

### Issue: Keep-alive pings failing
**Solution**: Check server logs, verify routes are registered, check CSRF token

### Issue: Form data not preserved
**Solution**: Ensure form has `data-preserve="true"` attribute

### Issue: Auto-logout not working
**Solution**: 
1. Check `CONCURE_AUTO_LOGOUT_ENABLED=true` in `.env`
2. Verify JavaScript file is loaded
3. Check browser console for errors
4. Ensure user is authenticated

## Best Practices

### ✅ DO:
- Keep timeout reasonable (10-15 minutes for healthcare)
- Test thoroughly in production-like environment
- Monitor audit logs for auto-logout patterns
- Educate users about the feature
- Preserve important form data

### ❌ DON'T:
- Set timeout too short (< 5 minutes) - frustrates users
- Set timeout too long (> 30 minutes) - security risk
- Disable feature in production
- Ignore user feedback about timeout duration
- Preserve sensitive data like passwords

## Audit Logging

All auto-logout events are logged in the `audit_logs` table with:
- **User ID**: Who was logged out
- **Action**: `auto_logout`
- **Description**: Reason for logout (inactivity, session_expired, manual)
- **IP Address**: Where the logout occurred
- **Timestamp**: When it happened

Query example:
```sql
SELECT * FROM audit_logs 
WHERE action = 'auto_logout' 
ORDER BY performed_at DESC 
LIMIT 100;
```

## Future Enhancements

Potential improvements for future versions:

1. **Role-Based Timeouts**: Different timeouts for different user roles
2. **Location-Based Timeouts**: Shorter timeouts for remote access
3. **Activity Heatmap**: Visual display of user activity patterns
4. **Smart Timeout**: AI-based timeout adjustment based on user behavior
5. **Multi-Device Sync**: Coordinate timeouts across multiple devices
6. **Idle Detection**: Detect system idle state (screensaver, lock screen)

## Summary

The Auto-Logout Security Feature provides:

✅ **Enhanced Security**: Automatic session termination after inactivity  
✅ **User-Friendly**: Warning dialog with option to stay logged in  
✅ **Configurable**: Easy to adjust timeout and warning periods  
✅ **Auditable**: Complete logging of all auto-logout events  
✅ **Data Protection**: Form data preservation to prevent work loss  
✅ **Healthcare Compliant**: Meets HIPAA and other regulatory requirements  

This feature is essential for protecting sensitive medical data in clinic environments where workstations may be left unattended.

