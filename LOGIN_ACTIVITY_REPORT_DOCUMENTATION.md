# Login/Logout Activity Report - Documentation

## Overview
The Login/Logout Activity Report is a comprehensive security and monitoring tool for ConCure Master administrators to track user login sessions across all clinics in the system.

## Features Implemented

### 1. **Data Display**
- ✅ User full name and role
- ✅ Clinic name
- ✅ Login timestamp (date and time)
- ✅ Logout timestamp (date and time)
- ✅ Session duration (calculated and formatted)
- ✅ IP address for security tracking
- ✅ Session status (Active/Completed)

### 2. **Filtering Options**
- ✅ **Date Range Filter:** From/To date picker for specific periods (default: last 30 days)
- ✅ **Clinic Filter:** Dropdown to select specific clinic(s) or "All Clinics"
- ✅ **User Filter:** Dropdown to select specific user(s) or "All Users"
- ✅ **Role Filter:** Filter by user roles (admin, doctor, nurse, etc.)

### 3. **Technical Implementation**
- ✅ Uses existing `audit_logs` table where `action` = 'login' and 'logout'
- ✅ Matches login/logout pairs by `user_id` and session timing
- ✅ Calculates duration by finding the next logout event after each login
- ✅ Handles cases where logout is missing (shows "Active Session")
- ✅ Efficient database queries with eager loading

### 4. **Display Features**
- ✅ Paginated table with 50 records per page
- ✅ Sortable columns by date (descending)
- ✅ Export functionality to CSV
- ✅ Summary statistics:
  - Total sessions
  - Unique users
  - Average session duration
  - Active sessions count
- ✅ Shows "Active Session" for logins without corresponding logout
- ✅ Visual charts:
  - Sessions by Role (Doughnut chart)
  - Top 10 Clinics by Sessions (Bar chart)

### 5. **Location**
- ✅ Added to Master Reports section (`app/Http/Controllers/Master/ReportController.php`)
- ✅ View created in `resources/views/master/reports/login-activity.blade.php`
- ✅ Navigation link added in master reports page header

### 6. **Security**
- ✅ Only master admin users can access (protected by `super.admin` middleware)
- ✅ Audit trail for who accessed the report and when
- ✅ Audit trail for CSV exports

## File Structure

### Controller Methods
**File:** `app/Http/Controllers/Master/ReportController.php`

1. **`loginActivity(Request $request)`**
   - Main method to display the report
   - Handles filtering and pagination
   - Logs access to the report

2. **`exportLoginActivity(Request $request)`**
   - Exports filtered data to CSV
   - Logs export action
   - Streams CSV file to browser

3. **`getLoginActivityStats($from, $to, $filters)`**
   - Private method to calculate summary statistics
   - Returns total sessions, unique users, average duration, etc.

4. **`formatDuration($minutes)`**
   - Private helper to format duration in human-readable format
   - Converts minutes to "Xh Ym" or "Xd Yh Zm" format

### Routes
**File:** `routes/master.php`

```php
Route::get('/reports/login-activity', [ReportController::class, 'loginActivity'])
    ->name('reports.login-activity');
    
Route::get('/reports/login-activity/export', [ReportController::class, 'exportLoginActivity'])
    ->name('reports.login-activity.export');
```

### View
**File:** `resources/views/master/reports/login-activity.blade.php`

- Extends master layout
- Displays summary statistics cards
- Filter form with date range, clinic, user, and role filters
- Paginated table of login sessions
- Charts for visual analytics
- Export button

## Usage

### Accessing the Report
1. Log in as a super admin or master admin
2. Navigate to **Master Dashboard** → **Reports**
3. Click on **"Login Activity Report"** button in the header
4. Or directly access: `/master/reports/login-activity`

### Filtering Data
1. **Date Range:** Select "From" and "To" dates to filter sessions
2. **Clinic:** Choose a specific clinic or leave as "All Clinics"
3. **User:** Select a specific user or leave as "All Users"
4. **Role:** Filter by user role (admin, doctor, nurse, etc.)
5. Click **"Apply Filters"** to update the report
6. Click **"Reset"** to clear all filters

### Exporting Data
1. Apply desired filters
2. Click **"Export CSV"** button in the top-right
3. CSV file will download with all filtered sessions
4. CSV includes: User Name, Role, Clinic, Login Date/Time, Logout Date/Time, Duration, IP Address, Status

### Understanding the Data

#### Session Status
- **Active Session:** User logged in but no logout event found (may still be logged in or session timed out)
- **Completed:** User logged in and logged out normally

#### Duration Format
- Less than 1 hour: "45 min"
- 1-24 hours: "2h 30m"
- More than 24 hours: "1d 5h 20m"

#### Summary Statistics
- **Total Sessions:** Count of all login events in the selected period
- **Unique Users:** Number of different users who logged in
- **Avg Session Duration:** Average time between login and logout (only completed sessions)
- **Active Sessions:** Sessions without a logout event

## Database Schema

The report uses the existing `audit_logs` table:

```sql
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    user_name VARCHAR(255) NULL,
    user_role VARCHAR(255) NULL,
    clinic_id BIGINT UNSIGNED NULL,
    action VARCHAR(255),  -- 'login' or 'logout'
    description TEXT,
    ip_address VARCHAR(255) NULL,
    user_agent VARCHAR(255) NULL,
    performed_at TIMESTAMP,
    -- ... other fields
);
```

## Security Considerations

1. **Access Control:** Only users with `super_admin` or `master_admin` roles can access
2. **Audit Trail:** Every access to the report is logged in `audit_logs`
3. **Export Tracking:** CSV exports are also logged for accountability
4. **IP Tracking:** IP addresses are stored for security analysis
5. **Data Privacy:** Sensitive user data is only accessible to authorized admins

## Performance Optimization

1. **Pagination:** Results are paginated (50 per page) to prevent memory issues
2. **Indexed Queries:** Uses database indexes on `action` and `performed_at` columns
3. **Eager Loading:** Loads related `user` and `clinic` data efficiently
4. **Streaming Export:** CSV export uses streaming to handle large datasets

## Future Enhancements (Optional)

- [ ] Add PDF export option
- [ ] Add email scheduling for periodic reports
- [ ] Add failed login attempts tracking
- [ ] Add geographic location mapping from IP addresses
- [ ] Add session timeout detection
- [ ] Add concurrent session detection
- [ ] Add user activity heatmap by time of day
- [ ] Add comparison with previous periods

## Troubleshooting

### No sessions showing
- Check if date range includes actual login events
- Verify filters are not too restrictive
- Ensure `audit_logs` table has login/logout entries

### Duration showing as "Active Session"
- This is normal for users currently logged in
- Also occurs if logout event was not recorded (browser closed, session timeout, etc.)

### Export not working
- Check file permissions on server
- Verify CSV headers are being sent correctly
- Check browser download settings

## Support
For issues or questions, contact the development team or refer to the main ConCure documentation.

