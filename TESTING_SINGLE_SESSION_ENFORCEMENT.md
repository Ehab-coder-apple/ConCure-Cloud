# Testing Single Active Session Enforcement

## Feature Overview
This feature ensures only ONE active session per user credential (username/password) at a time. When a user logs in on a new device, the old session is automatically terminated with an audit trail.

## Implementation Components
1. **UserSession Model** - Tracks active sessions
2. **DeviceFingerprintService** - Identifies devices (browser + OS + IP)
3. **SessionManagementService** - Manages session lifecycle
4. **CheckSessionTermination Middleware** - Detects terminated sessions
5. **LoginController Changes** - Creates session records on login
6. **Audit Trail** - Logs all session events

## Testing Scenarios

### Scenario 1: Basic Single Session Enforcement
**Goal**: Verify that logging in on a new browser terminates the old session

**Setup**:
- Open two browser tabs/profiles of the SAME browser OR one regular + one incognito

**Steps**:
1. Login with user (e.g., doctor@clinic.com) in Tab 1
2. In Tab 2, login with the SAME user (doctor@clinic.com)
3. Go back to Tab 1
4. Try to navigate to any authenticated page (Dashboard, Patients, etc.)

**Expected Result**:
- Tab 1 should be redirected to login page
- Show message: *"For your security, your session has been terminated. You were logged out because you signed in from another device."*
- Tab 2 should remain logged in
- Check AuditLog table: Should see 2 entries: `session_created` for Tab 2, `session_terminated` for Tab 1

**Check Database**:
```sql
SELECT * FROM user_sessions WHERE user_id = ? ORDER BY created_at DESC;
```
Should show:
- Tab 1 session: `terminated_at` NOT NULL, `termination_reason = 'new_login_elsewhere'`
- Tab 2 session: `terminated_at` IS NULL (active)

---

### Scenario 2: Different Credentials = Independent Sessions
**Goal**: Verify that a person with 2 different usernames can have simultaneous sessions

**Setup**:
- Same user (person) has two accounts:
  - doctor_username (Doctor role)
  - admin_username (Admin role)

**Steps**:
1. Login as `doctor_username` in Tab 1 (Dashboard shows Doctor view)
2. Login as `admin_username` in Tab 2 (Dashboard shows Admin view)
3. Refresh Tab 1
4. Refresh Tab 2

**Expected Result**:
- Both tabs remain logged in independently
- No session termination
- Tab 1 shows doctor interface
- Tab 2 shows admin interface
- Check user_sessions table: 2 records with different `credential_used` values

---

### Scenario 3: Multi-Device Testing
**Goal**: Verify session enforcement works across different actual devices

**Setup**:
- Device A: Laptop (Chrome)
- Device B: Phone (Safari)
- Use same network or real internet

**Steps**:
1. Login on Device A (Laptop) with user credentials
2. Login on Device B (Phone) with same user credentials
3. On Device A, try to navigate to any page

**Expected Result**:
- Device A session terminates
- Device A is redirected to login with security message
- Device B remains logged in
- Check user_sessions: Both have different `device_fingerprint`, `browser`, `os`, `ip_address`

---

### Scenario 4: Manual Logout Audit Trail
**Goal**: Verify that manual logout is properly recorded

**Steps**:
1. Login with a user
2. Click "Logout" button
3. Check audit log

**Expected Result**:
- User is logged out
- AuditLog shows: `action = 'logout'`, `description = 'User logged out'`
- UserSession shows: `terminated_at` NOT NULL, `termination_reason = 'manual_logout'`

---

### Scenario 5: Simultaneous Login Attempts
**Goal**: Verify that rapid successive logins handle correctly

**Steps**:
1. Open 3 browser tabs with same login credentials
2. Login simultaneously (Ctrl+Click on submit button in multiple tabs)
3. Check which session survives

**Expected Result**:
- Only ONE session should be active
- Other 2 should be terminated
- Audit log should show all 3 session_created events + 2 session_terminated events

---

## Database Audit Checks

### View All Active Sessions
```sql
SELECT us.*, u.email, u.full_name 
FROM user_sessions us
JOIN users u ON us.user_id = u.id
WHERE us.terminated_at IS NULL
ORDER BY us.created_at DESC;
```

### View Terminated Sessions
```sql
SELECT us.*, u.email 
FROM user_sessions us
JOIN users u ON us.user_id = u.id
WHERE us.terminated_at IS NOT NULL
ORDER BY us.terminated_at DESC
LIMIT 20;
```

### Check Session Trail for Specific User
```sql
SELECT * FROM user_sessions 
WHERE user_id = ? 
ORDER BY created_at DESC;
```

### Verify Audit Log Entries
```sql
SELECT * FROM audit_logs
WHERE action IN ('session_created', 'session_terminated')
ORDER BY performed_at DESC
LIMIT 50;
```

---

## Troubleshooting

### Sessions Not Being Terminated
- Check if `user_sessions` table exists
- Run: `php artisan migrate` (if needed)
- Check if middleware `CheckSessionTermination` is registered in `app/Http/Kernel.php`
- Check logs for errors: `storage/logs/laravel.log`

### Device Fingerprint Not Matching
- Device fingerprint is SHA256 hash of user agent + IP
- Same browser on same device = same fingerprint
- Different IP address = different fingerprint (even same browser)

### Not Seeing Audit Log Entries
- Check if `AuditLog` model exists
- Ensure `audit_logs` table is created
- Check if audit log creation is failing silently (see logs)

---

## Performance Notes
- Each login creates ONE new user_sessions record
- Each old session termination updates ONE record
- Middleware check runs on every request (lightweight query with indexed fields)
- No significant performance impact expected

## Future Enhancements
- Add "Active Sessions" dashboard showing all logged-in devices
- Add "Log out all other sessions" button
- Add real-time notification when session is terminated
- Add geolocation tracking for sessions
