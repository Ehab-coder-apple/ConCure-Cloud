# Session Grouping Logic - Explanation

## Problem Identified

The original implementation showed **every login event as a separate session**, which created issues:

1. ❌ Users who never explicitly log out had multiple "Active Session" entries
2. ❌ Same user logging in multiple times in a short period showed as separate sessions
3. ❌ No way to distinguish between actual different sessions vs. page refreshes
4. ❌ Inflated session counts that didn't reflect actual usage patterns

### Example of the Problem:
```
User: Asia hamid
- Jan 05, 2026 11:24 AM → Active Session
- Jan 05, 2026 11:24 AM → Active Session  (duplicate!)
- Jan 04, 2026 12:28 PM → Active Session
- Jan 04, 2026 12:28 PM → Active Session  (duplicate!)
- Jan 03, 2026 11:24 AM → Active Session
- Jan 03, 2026 11:24 AM → Active Session  (duplicate!)
```

## Solution: Session Grouping Algorithm

### Core Concept
Group consecutive login events from the same user into **logical sessions** based on activity patterns.

### Algorithm Logic

#### 1. **Session Timeout Rule**
- If more than **30 minutes** pass between consecutive logins → **New Session**
- If less than 30 minutes → **Same Session** (user is still active)

#### 2. **Session Grouping Process**

```php
For each login event (ordered by user_id and time):
    
    IF (first login ever) OR (different user) OR (30+ min since last login):
        → Start NEW SESSION
        → Save previous session if exists
    ELSE:
        → Update CURRENT SESSION
        → Track last activity time
        → Increment login count
```

#### 3. **Session Finalization**

When a session ends, we determine:

**A. End Time:**
- If explicit logout exists → Use logout time
- Otherwise → Use last activity time (last login in the group)

**B. Duration:**
- Calculate: `session_start` to `session_end`

**C. Status:**
- **"Completed"** → Explicit logout found
- **"Active Session"** → Last activity within 60 minutes (user likely still active)
- **"Timed Out"** → Last activity more than 60 minutes ago (session expired)

### Visual Example

#### Before (Old Logic):
```
Login Events:
- 9:00 AM → Session 1 (Active)
- 9:15 AM → Session 2 (Active)  ← Same session!
- 9:30 AM → Session 3 (Active)  ← Same session!
- 2:00 PM → Session 4 (Active)  ← Different session (30+ min gap)
```

#### After (New Logic):
```
Grouped Sessions:
- Session 1: 9:00 AM - 9:30 AM (30 min duration, 3 logins, Timed Out)
- Session 2: 2:00 PM - 2:00 PM (0 min duration, 1 login, Active)
```

## Implementation Details

### Key Functions

#### 1. `groupLoginsIntoSessions($logins)`
- Takes all login events
- Groups them into logical sessions
- Returns array of session objects

#### 2. `finalizeSession($session)`
- Calculates session duration
- Determines session status
- Checks for explicit logout
- Returns complete session object

### Session Object Structure

```php
{
    user_id: 6,
    user_name: "Asia hamid",
    user_role: "assistant",
    clinic_id: 1,
    clinic_name: "Nutricare Clinic",
    login_at: Carbon("2026-01-05 11:24:00"),      // First login
    last_activity: Carbon("2026-01-05 11:30:00"), // Last login in group
    logout_at: null,                               // Explicit logout (if exists)
    duration_minutes: 6,                           // Calculated duration
    duration_formatted: "6 min",                   // Human-readable
    ip_address: "138.193.203.111",
    status: "Timed Out",                           // Active/Completed/Timed Out
    login_count: 3                                 // Number of logins in session
}
```

## Benefits of This Approach

### ✅ Accurate Session Tracking
- One session per actual user activity period
- No duplicate entries for the same session

### ✅ Realistic Duration Calculation
- Measures actual time user was active
- Accounts for multiple logins during same session

### ✅ Better Status Detection
- **Active**: Currently using the system (< 60 min ago)
- **Timed Out**: Session expired due to inactivity
- **Completed**: User explicitly logged out

### ✅ Activity Metrics
- `login_count` shows how many times user logged in during session
- Helps identify users who frequently refresh or re-login

### ✅ Improved Analytics
- More accurate session counts
- Better average duration calculations
- Clearer understanding of usage patterns

## Configuration

### Adjustable Parameters

```php
// In groupLoginsIntoSessions() method
$sessionTimeoutMinutes = 30;  // Gap between logins to consider new session

// In finalizeSession() method
$activeThresholdMinutes = 60; // How recent to consider "Active Session"
```

### Customization Options

You can adjust these values based on your needs:

- **Shorter timeout (15 min)**: More granular session tracking
- **Longer timeout (60 min)**: Group longer work periods together
- **Active threshold**: Define what "currently active" means

## Example Scenarios

### Scenario 1: Normal Work Day
```
Logins:
- 9:00 AM (start work)
- 9:15 AM (page refresh)
- 9:45 AM (after coffee break)
- 12:00 PM (after lunch - 2h gap)

Result:
- Session 1: 9:00 AM - 9:45 AM (45 min, 3 logins, Timed Out)
- Session 2: 12:00 PM - 12:00 PM (0 min, 1 login, Active/Timed Out)
```

### Scenario 2: With Explicit Logout
```
Logins:
- 9:00 AM
- 9:30 AM
Logout:
- 5:00 PM

Result:
- Session 1: 9:00 AM - 5:00 PM (8h, 2 logins, Completed)
```

### Scenario 3: Multiple Short Sessions
```
Logins:
- 9:00 AM
- 11:00 AM (2h gap - new session)
- 2:00 PM (3h gap - new session)

Result:
- Session 1: 9:00 AM - 9:00 AM (0 min, 1 login, Timed Out)
- Session 2: 11:00 AM - 11:00 AM (0 min, 1 login, Timed Out)
- Session 3: 2:00 PM - 2:00 PM (0 min, 1 login, Active)
```

## Database Impact

### Query Optimization
- Single query to fetch all logins
- In-memory grouping (no additional DB queries per session)
- Efficient for large datasets

### Performance Considerations
- Grouping happens in PHP (fast for reasonable data volumes)
- Pagination applied after grouping
- Export streams data to handle large exports

## Future Enhancements

Potential improvements:

1. **Configurable timeout per role** (doctors vs. assistants)
2. **IP-based session tracking** (different IP = different session)
3. **Device fingerprinting** (track sessions per device)
4. **Session activity details** (what actions during session)
5. **Anomaly detection** (unusual session patterns)

## Testing Recommendations

Test these scenarios:

1. ✅ Single login, no logout
2. ✅ Multiple logins within 30 minutes
3. ✅ Multiple logins with 30+ minute gaps
4. ✅ Login with explicit logout
5. ✅ Different users interleaved
6. ✅ Same user, different days
7. ✅ Edge case: login exactly at 30-minute mark

## Summary

The session grouping logic transforms raw login events into meaningful session data by:

1. **Grouping** consecutive logins within 30 minutes
2. **Calculating** accurate session durations
3. **Detecting** session status (Active/Timed Out/Completed)
4. **Tracking** activity metrics (login count)
5. **Providing** realistic usage analytics

This gives administrators a much clearer picture of actual system usage patterns rather than just raw login event counts.

