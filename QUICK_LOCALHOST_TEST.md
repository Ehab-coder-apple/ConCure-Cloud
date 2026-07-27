# Quick Localhost Testing - Single Session Enforcement

## Pre-Test Checklist
- [ ] Database: Run `php artisan migrate` (if user_sessions table not created)
- [ ] Artisan tinker created user_sessions table ✅
- [ ] App running on localhost
- [ ] Browser dev tools open (optional, for cookies inspection)

---

## Test 1: Basic Two-Browser Test (EASIEST)

### Setup
- Open **Tab 1**: `http://localhost:8000/login` (regular)
- Open **Tab 2**: `http://localhost:8000/login` (right-click → Incognito or Private)

### Steps
1. **Tab 1**: Login with any user (e.g., doctor@example.com)
   - Should see dashboard ✅
   
2. **Tab 2**: Login with SAME user (doctor@example.com)
   - Should see dashboard ✅
   
3. **Tab 1**: Refresh page (F5)
   - **EXPECTED**: Flash message appears → Redirects to login
   - Message: *"For your security, your session has been terminated..."*
   - **If this happens**: TEST PASSED ✅

4. **Tab 2**: Refresh page (F5)
   - Should remain logged in ✅

### Verification - Check Database
Open terminal and run:
```bash
php artisan tinker
```

Then in tinker:
```php
DB::table('user_sessions')->orderBy('created_at', 'desc')->limit(5)->get();
```

**Expected output**:
- 2 records for same user_id
- One with `terminated_at` = NULL (Tab 2 - active)
- One with `terminated_at` = current time (Tab 1 - terminated)
- `termination_reason` = 'new_login_elsewhere'

---

## Test 2: Check Audit Log

In tinker:
```php
DB::table('audit_logs')
  ->whereIn('action', ['session_created', 'session_terminated'])
  ->orderBy('performed_at', 'desc')
  ->limit(10)
  ->get();
```

**Expected**: Should see entries like:
- `action: session_created` (for both Tab 1 and Tab 2)
- `action: session_terminated` (for Tab 1)

---

## Test 3: Multi-Credential Test (Advanced)

If your test user has multiple roles:

### Setup
- Same person with 2 usernames:
  - `doctor@example.com` (doctor role)
  - `admin@example.com` (admin role)

### Steps
1. **Tab 1**: Login as `doctor@example.com`
2. **Tab 2**: Login as `admin@example.com`
3. Refresh both tabs

**EXPECTED**: 
- Both tabs stay logged in ✅ (different credentials = independent sessions)
- Audit log shows 2 `session_created` entries with different `credential_used`

---

## Test 4: Manual Logout Test

1. Login with any user
2. Click "Logout" button
3. Check database:

```php
DB::table('user_sessions')
  ->where('user_id', 8) // your user_id
  ->where('terminated_at', '<>', null)
  ->orderBy('terminated_at', 'desc')
  ->first();
```

**Expected**: 
- `termination_reason` = 'manual_logout'
- `terminated_at` = recent timestamp

---

## Troubleshooting

### Flash message not showing?
- Check `resources/views/auth/login.blade.php` was modified
- Check middleware is in `app/Http/Kernel.php`
- Check `session_terminated` session key in redirect

### user_sessions table not created?
```bash
php artisan tinker
Schema::create('user_sessions', function ($t) { ... }); // as before
```

### Not seeing audit logs?
- Check `AuditLog::create()` is not throwing exceptions
- Check logs: `tail storage/logs/laravel.log`
- Manually insert test entry: `DB::insert('INSERT INTO audit_logs...')`

---

## Quick Artisan Commands

### Check if middleware is registered
```bash
php artisan route:list | grep -i middleware
```

### Clear session files
```bash
rm -rf storage/framework/sessions/*
```

### Check database connection
```bash
php artisan tinker
DB::connection()->getPdo();
```

---

## What to Document After Testing

After completing tests, please share:
1. Did Tab 1 get redirected to login? (YES/NO)
2. Did Tab 1 show termination message? (YES/NO)
3. Did Tab 2 remain logged in? (YES/NO)
4. user_sessions table entries (copy output from tinker)
5. audit_logs entries for session events
6. Any errors in `storage/logs/laravel.log`

Then we can move to production deployment!
