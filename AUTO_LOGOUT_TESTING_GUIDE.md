# Auto-Logout Feature - Testing Guide

## Quick Test Instructions

### Prerequisites
1. Clear browser cache and localStorage
2. Have browser console open (F12) to see debug messages
3. Be logged in to the application

### Test 1: Verify Auto-Logout is Active (30 seconds)

**Steps:**
1. Log in to the application
2. Open browser console (F12)
3. Look for initialization message:
   ```
   Auto-logout initialized: {timeout: "10 minutes", warning: "2 minutes before"}
   ```
4. **Expected Result**: Message appears, confirming auto-logout is active

**Status**: ✅ PASS / ❌ FAIL

---

### Test 2: Activity Detection (1 minute)

**Steps:**
1. Stay on any page after login
2. Move mouse, click, type, scroll
3. Watch console for activity messages
4. **Expected Result**: Timer resets on each activity (no logout)

**Status**: ✅ PASS / ❌ FAIL

---

### Test 3: Warning Dialog (For Quick Testing - Modify Config)

**Quick Test Setup** (modify timeout to 2 minutes for testing):

1. Open browser console
2. Run this command to override timeout for testing:
   ```javascript
   if (window.autoLogout) {
       window.autoLogout.config.timeoutSeconds = 120;  // 2 minutes
       window.autoLogout.config.warningSeconds = 30;   // 30 seconds warning
       window.autoLogout.resetInactivityTimer();
   }
   ```

**Steps:**
1. After running the override command, don't touch anything
2. Wait 90 seconds (1.5 minutes)
3. Warning dialog should appear
4. **Expected Result**: 
   - Dialog appears with countdown
   - Countdown shows 30 seconds
   - Alert sound plays (optional)

**Status**: ✅ PASS / ❌ FAIL

---

### Test 4: "Stay Logged In" Button (2.5 minutes total)

**Steps:**
1. Continue from Test 3 (warning dialog is showing)
2. Click "Stay Logged In" button
3. **Expected Result**:
   - Dialog closes immediately
   - Timer resets
   - Session continues normally

**Status**: ✅ PASS / ❌ FAIL

---

### Test 5: Auto-Logout After Warning (3 minutes total)

**Steps:**
1. Trigger warning dialog again (wait 90 seconds)
2. Don't click anything
3. Wait for countdown to reach 0
4. **Expected Result**:
   - Automatic redirect to login page
   - Message: "You were automatically logged out due to inactivity"
   - Session is terminated

**Status**: ✅ PASS / ❌ FAIL

---

### Test 6: Keep-Alive Pings (2 minutes)

**Steps:**
1. Log in to the application
2. Open browser Network tab (F12 → Network)
3. Filter by "keep-alive"
4. Be active (move mouse, click)
5. **Expected Result**:
   - See POST requests to `/session/keep-alive` every 60 seconds
   - Requests return status 200
   - Response shows session is alive

**Status**: ✅ PASS / ❌ FAIL

---

### Test 7: Form Data Preservation (5 minutes)

**Steps:**
1. Find a form in the application
2. Add `data-preserve="true"` attribute to the form tag (use browser inspector)
3. Fill in some form fields (not passwords)
4. Wait for auto-logout (or trigger it manually)
5. Log back in
6. Return to the same page
7. **Expected Result**:
   - Form data is restored
   - Console shows: "Form data restored from previous session"

**Status**: ✅ PASS / ❌ FAIL

---

### Test 8: Multiple Tabs (3 minutes)

**Steps:**
1. Open application in Tab 1
2. Open same application in Tab 2
3. Be active in Tab 1 (move mouse, click)
4. Switch to Tab 2 (don't interact)
5. **Expected Result**:
   - Both tabs stay logged in (shared session)
   - Keep-alive from Tab 1 keeps both tabs alive

**Status**: ✅ PASS / ❌ FAIL

---

### Test 9: Page Visibility Detection (2 minutes)

**Steps:**
1. Log in to the application
2. Switch to another tab/window
3. Wait 1 minute
4. Switch back to application tab
5. **Expected Result**:
   - Console shows session status check
   - If session expired, auto-logout occurs
   - If session valid, continues normally

**Status**: ✅ PASS / ❌ FAIL

---

### Test 10: Audit Logging (2 minutes)

**Steps:**
1. Trigger auto-logout (wait for timeout)
2. Log in as admin
3. Go to Audit Logs (if available)
4. Or check database:
   ```sql
   SELECT * FROM audit_logs 
   WHERE action = 'auto_logout' 
   ORDER BY performed_at DESC 
   LIMIT 10;
   ```
5. **Expected Result**:
   - Auto-logout event is logged
   - Contains user info, reason, IP, timestamp

**Status**: ✅ PASS / ❌ FAIL

---

## Production Testing (Full Timeout)

For production testing with full 10-minute timeout:

### Test 11: Full Timeout Cycle (12 minutes)

**Steps:**
1. Log in to the application
2. Be active for 2 minutes (normal use)
3. Stop all activity
4. Wait 8 minutes (do something else)
5. At 8 minutes: Warning dialog appears
6. Wait 2 more minutes without clicking
7. At 10 minutes: Auto-logout occurs

**Expected Timeline:**
- 0:00 - Login
- 0:00-2:00 - Active use
- 2:00 - Stop activity
- 10:00 - Warning appears (8 min of inactivity)
- 12:00 - Auto-logout (10 min of inactivity)

**Status**: ✅ PASS / ❌ FAIL

---

## Browser Console Commands for Testing

### Check if Auto-Logout is Running
```javascript
console.log('Auto-logout active:', !!window.autoLogout);
console.log('Config:', window.autoLogout?.config);
```

### Manually Trigger Warning (for testing)
```javascript
if (window.autoLogout) {
    window.autoLogout.showWarning();
}
```

### Manually Trigger Logout (for testing)
```javascript
if (window.autoLogout) {
    window.autoLogout.performLogout('test');
}
```

### Check Last Activity Time
```javascript
if (window.autoLogout) {
    const elapsed = Date.now() - window.autoLogout.lastActivity;
    console.log('Seconds since last activity:', Math.floor(elapsed / 1000));
}
```

### Override Timeout for Quick Testing
```javascript
if (window.autoLogout) {
    // Set to 2 minutes timeout, 30 seconds warning
    window.autoLogout.config.timeoutSeconds = 120;
    window.autoLogout.config.warningSeconds = 30;
    window.autoLogout.resetInactivityTimer();
    console.log('Timeout overridden for testing');
}
```

---

## Common Issues and Solutions

### Issue: Auto-logout not initializing
**Check:**
- Browser console for errors
- Script is loaded: `<script src="/js/auto-logout.js"></script>`
- User is authenticated (`@auth` directive)

### Issue: Warning dialog not appearing
**Check:**
- Console for JavaScript errors
- Timeout configuration is correct
- Timer is actually running (check console logs)

### Issue: Keep-alive requests failing
**Check:**
- Network tab for 401/403 errors
- CSRF token is present
- Routes are registered correctly

### Issue: Form data not preserved
**Check:**
- Form has `data-preserve="true"` attribute
- localStorage is enabled in browser
- No errors in console during save

---

## Test Results Summary

| Test | Status | Notes |
|------|--------|-------|
| 1. Initialization | ⬜ | |
| 2. Activity Detection | ⬜ | |
| 3. Warning Dialog | ⬜ | |
| 4. Stay Logged In | ⬜ | |
| 5. Auto-Logout | ⬜ | |
| 6. Keep-Alive Pings | ⬜ | |
| 7. Form Preservation | ⬜ | |
| 8. Multiple Tabs | ⬜ | |
| 9. Page Visibility | ⬜ | |
| 10. Audit Logging | ⬜ | |
| 11. Full Timeout | ⬜ | |

**Overall Status**: ⬜ NOT TESTED / ✅ ALL PASS / ⚠️ SOME ISSUES / ❌ FAILED

---

## Notes

- For quick testing, use the console command to override timeout to 2 minutes
- Always test in a non-production environment first
- Clear browser cache between tests for accurate results
- Check both browser console and server logs for complete picture

