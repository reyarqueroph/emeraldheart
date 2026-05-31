# Performance Sync Investigation - Agent to Admin

## Problem Statement
Agent performance updates (Total APE, Prospects, Clients) entered in `agent/account.php` are not reflecting in `admin/dashboard.php`.

## Tools Created for Diagnosis

### 1. Debug API Endpoint
**File**: `api/agents/debug-performance.php`

**Purpose**: Provides detailed diagnostic information about:
- Database column existence
- Current user's performance data
- All agents' performance data
- Performance history records

**How to Use**:
1. Login as agent or admin
2. Visit: `/api/agents/debug-performance.php`
3. Review JSON output for issues

### 2. Test Page
**File**: `test-performance-sync.php`

**Purpose**: Interactive diagnostic tool with:
- Database structure verification
- Current user data display
- All agents data table
- Performance history viewer
- API endpoint testing buttons

**How to Use**:
1. Login to the system
2. Visit: `/test-performance-sync.php`
3. Review all sections
4. Click "Test Save API" to test saving
5. Click "Test Get API" to test retrieval
6. Click "Test Debug API" for detailed diagnostics

### 3. Enhanced Console Logging
**File**: `admin/dashboard.php`

**Added**: Console logging to track data flow:
```javascript
console.log('Admin Performance Data:', result);
console.log('Summary:', summary);
console.log('All Agents:', allAgents);
console.log('Top Performers:', topPerformers);
```

**How to Use**:
1. Login as admin
2. Open browser console (F12)
3. Go to dashboard
4. Check console for logged data

## Investigation Checklist

### ✅ Agent Side (Saving)
- [ ] Agent can access account page
- [ ] Performance form displays correctly
- [ ] Form submits without errors
- [ ] Success message appears after save
- [ ] Browser console shows success response
- [ ] Network tab shows 200 OK response

### ✅ Database Layer
- [ ] Column `total_ape` exists in users table
- [ ] Column `total_prospects` exists in users table
- [ ] Column `total_clients` exists in users table
- [ ] Column `last_sale_date` exists in users table
- [ ] Table `performance_history` exists
- [ ] Data appears in `performance_history` table after save
- [ ] Data updates in `users` table after save

### ✅ Admin Side (Loading)
- [ ] Admin can access dashboard
- [ ] Performance section displays
- [ ] Browser console shows API call
- [ ] Console logs show correct data
- [ ] API returns success response
- [ ] Data appears in UI elements

## Common Root Causes

### 1. Database Column Missing
**Symptom**: SQL error "Unknown column 'total_ape'"

**Check**:
```sql
SHOW COLUMNS FROM users LIKE 'total_ape';
```

**Fix**:
```sql
ALTER TABLE users ADD COLUMN total_ape DECIMAL(12,2) DEFAULT 0.00;
ALTER TABLE users ADD COLUMN total_prospects INT DEFAULT 0;
ALTER TABLE users ADD COLUMN total_clients INT DEFAULT 0;
ALTER TABLE users ADD COLUMN last_sale_date DATE DEFAULT NULL;
```

### 2. Old Column Name Still in Use
**Symptom**: Data saves but doesn't appear

**Check**:
```sql
SHOW COLUMNS FROM users LIKE 'monthly_sales';
```

**Fix**:
```sql
ALTER TABLE users CHANGE COLUMN monthly_sales total_ape DECIMAL(12,2) DEFAULT 0.00;
```

### 3. Session/Permission Issue
**Symptom**: "Unauthorized" error

**Check**:
- Is user logged in?
- Is `$_SESSION['user_role']` set correctly?
- Does admin have 'admin' role?

**Fix**:
- Logout and login again
- Clear browser cookies
- Check session configuration

### 4. Browser Caching
**Symptom**: Old data persists despite updates

**Fix**:
- Hard refresh: Ctrl+Shift+R
- Clear cache: Ctrl+Shift+Delete
- Try incognito mode
- Check Network tab "Disable cache"

### 5. API Not Updating Users Table
**Symptom**: History table has data, users table doesn't

**Check**: `api/agents/save-performance-history.php` line 70-80

**Verify**:
```php
UPDATE users 
SET total_ape = :total_ape,
    total_prospects = :total_prospects,
    total_clients = :total_clients,
    last_sale_date = :last_sale_date,
    profile_updated_at = CURRENT_TIMESTAMP
WHERE id = :user_id
```

### 6. Admin Reading Wrong Column
**Symptom**: Admin shows zeros despite agent having data

**Check**: `api/agents/get-performance.php` line 40-50

**Verify**:
```php
SELECT 
    id,
    full_name,
    agent_code,
    position,
    total_ape,  // Must be 'total_ape' not 'monthly_sales'
    total_prospects,
    total_clients,
    last_sale_date,
    status
FROM users 
WHERE user_role = 'agent'
```

## Step-by-Step Testing Procedure

### Test 1: Agent Save
1. Login as agent (e.g., user_id = 8)
2. Go to `/test-performance-sync.php`
3. Note current values in "Step 2"
4. Click "Test Save API" button
5. Check response shows `success: true`
6. Refresh page
7. Verify "Step 2" shows new values (99999.99, 99, 99)
8. Verify "Step 4" shows new history record

### Test 2: Admin Load
1. Login as admin
2. Go to `/test-performance-sync.php`
3. Check "Step 3" shows agent with updated values
4. Click "Test Get API" button
5. Verify response includes agent with correct data
6. Go to `/admin/dashboard.php`
7. Open browser console
8. Check console logs show correct data
9. Verify UI displays correct values

### Test 3: End-to-End
1. Login as agent
2. Go to Account page
3. Enter real performance data
4. Save successfully
5. Logout
6. Login as admin
7. Go to Dashboard
8. Click "Refresh Data" button
9. Verify agent's data appears correctly

## Expected Data at Each Stage

### After Agent Saves (Total APE: 50000, Prospects: 25, Clients: 10)

**performance_history table**:
```
id | user_id | total_ape | total_prospects | total_clients | notes | created_at
1  | 8       | 50000.00  | 25              | 10            | Test  | 2026-05-08 10:30:00
```

**users table**:
```
id | full_name | total_ape | total_prospects | total_clients | last_sale_date | profile_updated_at
8  | John Doe  | 50000.00  | 25              | 10            | 2026-05-08     | 2026-05-08 10:30:00
```

**Admin API Response**:
```json
{
  "success": true,
  "data": {
    "agents": [
      {
        "id": "8",
        "full_name": "John Doe",
        "total_ape": "50000.00",
        "total_prospects": "25",
        "total_clients": "10"
      }
    ],
    "summary": {
      "total_ape": 50000,
      "total_prospects": 25,
      "total_clients": 10
    }
  }
}
```

**Admin UI**:
- Summary card "Total APE": ₱50,000.00
- Summary card "Total Prospects": 25
- Summary card "Total Clients": 10
- Performance table shows agent with these values

## Files Modified

1. **admin/dashboard.php**
   - Added console logging
   - Added error display in catch block
   - Added cache-busting timestamp

2. **api/agents/debug-performance.php** (NEW)
   - Diagnostic endpoint

3. **test-performance-sync.php** (NEW)
   - Interactive test page

## Next Steps

1. **Run test-performance-sync.php** to identify the exact issue
2. **Check browser console** for errors
3. **Review database** using SQL queries
4. **Test APIs** individually using debug tools
5. **Verify data flow** at each stage

## Success Criteria

✅ Test page shows:
- All columns exist
- Agent data updates after save
- History records created
- Admin API returns correct data

✅ Admin dashboard shows:
- Console logs with correct data
- UI displays updated values
- Refresh button works
- No errors in console

## Documentation

- `TROUBLESHOOTING_PERFORMANCE_SYNC.md` - Detailed troubleshooting guide
- `PERFORMANCE_SYSTEM_FIXES.md` - Previous fixes documentation
- `TESTING_GUIDE.md` - Testing procedures
