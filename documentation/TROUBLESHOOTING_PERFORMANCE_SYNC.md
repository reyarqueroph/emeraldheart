# Troubleshooting: Agent Performance Not Reflecting in Admin Dashboard

## Problem
When an agent updates their performance data in `agent/account.php`, the changes don't appear in `admin/dashboard.php`.

## Diagnostic Steps

### Step 1: Test Agent Side (Save Performance)

1. **Login as Agent**
2. **Go to Account Page** (`agent/account.php`)
3. **Open Browser Console** (F12 → Console tab)
4. **Fill Performance Form**:
   - Total APE: 100000
   - Total Prospects: 50
   - Total Clients: 20
   - Last Sale Date: Today
   - Notes: "Test Update"
5. **Click "Save Performance Update"**
6. **Check Console** for the API response

**Expected Console Output**:
```javascript
{success: true, message: "Performance updated successfully. History saved."}
```

**If you see an error**, note the error message and check:
- Network tab for failed requests
- Response tab for error details

### Step 2: Verify Database Update

**Option A: Use Debug Script**

1. After saving performance as agent, visit:
   ```
   /api/agents/debug-performance.php
   ```

2. Check the JSON response for:
   - `columns_exist.total_ape`: should be `true`
   - `current_user_data.total_ape`: should show your entered value
   - `recent_history_records`: should show your latest update

**Option B: Direct Database Query**

Run this SQL query:
```sql
-- Check if columns exist
SHOW COLUMNS FROM users LIKE 'total_ape';
SHOW COLUMNS FROM users LIKE 'total_prospects';
SHOW COLUMNS FROM users LIKE 'total_clients';

-- Check agent's data (replace 8 with actual agent ID)
SELECT id, full_name, total_ape, total_prospects, total_clients, last_sale_date, profile_updated_at
FROM users 
WHERE id = 8;

-- Check performance history
SELECT * FROM performance_history 
WHERE user_id = 8 
ORDER BY created_at DESC 
LIMIT 5;
```

### Step 3: Test Admin Side (Load Performance)

1. **Login as Admin**
2. **Go to Dashboard** (`admin/dashboard.php`)
3. **Open Browser Console** (F12 → Console tab)
4. **Look for these console logs**:
   ```
   Admin Performance Data: {success: true, data: {...}}
   Summary: {total_ape: ..., total_prospects: ..., ...}
   All Agents: [{id: 8, full_name: "...", total_ape: 100000, ...}]
   Top Performers: [...]
   ```

5. **Click "Refresh Data" button**
6. **Check if values update**

**If console shows errors**:
- Check Network tab for failed API calls
- Look for 401 (unauthorized) or 500 (server error) responses

### Step 4: Check API Endpoint Directly

**Test Get Performance API**:

1. Login as admin
2. Visit in browser:
   ```
   /api/agents/get-performance.php
   ```

3. You should see JSON like:
   ```json
   {
     "success": true,
     "data": {
       "agents": [
         {
           "id": "8",
           "full_name": "Agent Name",
           "agent_code": "AG001",
           "position": "Agent",
           "total_ape": "100000.00",
           "total_prospects": "50",
           "total_clients": "20",
           "last_sale_date": "2026-05-08",
           "status": "active"
         }
       ],
       "top_performers": [...],
       "summary": {
         "total_ape": 100000,
         "total_prospects": 50,
         "total_clients": 20,
         "active_agents": 1,
         "avg_ape": 100000,
         "avg_prospects": 50,
         "avg_clients": 20
       }
     }
   }
   ```

**If you see an error**, note the message.

## Common Issues & Solutions

### Issue 1: Column 'total_ape' doesn't exist

**Symptoms**:
- Error: "Unknown column 'total_ape' in 'field list'"
- Debug script shows `columns_exist.total_ape: false`

**Solution**:
Run this SQL to add the column:
```sql
ALTER TABLE users ADD COLUMN total_ape DECIMAL(12,2) DEFAULT 0.00;
ALTER TABLE users ADD COLUMN total_prospects INT DEFAULT 0;
ALTER TABLE users ADD COLUMN total_clients INT DEFAULT 0;
ALTER TABLE users ADD COLUMN last_sale_date DATE DEFAULT NULL;
```

Or rename if `monthly_sales` exists:
```sql
ALTER TABLE users CHANGE COLUMN monthly_sales total_ape DECIMAL(12,2) DEFAULT 0.00;
```

### Issue 2: Performance history table doesn't exist

**Symptoms**:
- Error: "Table 'performance_history' doesn't exist"
- Debug script shows `performance_history_table_exists: false`

**Solution**:
Run this SQL:
```sql
CREATE TABLE IF NOT EXISTS performance_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_ape DECIMAL(12,2) DEFAULT 0.00,
    total_prospects INT DEFAULT 0,
    total_clients INT DEFAULT 0,
    last_sale_date DATE DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);
```

### Issue 3: Session/Permission Issues

**Symptoms**:
- Error: "Unauthorized"
- API returns `success: false`

**Solution**:
1. Clear browser cookies
2. Logout and login again
3. Check `$_SESSION['user_role']` is set correctly

### Issue 4: Browser Caching

**Symptoms**:
- Old data still showing
- Refresh button doesn't work
- Console shows old values

**Solution**:
1. Hard refresh: `Ctrl + Shift + R` (Windows) or `Cmd + Shift + R` (Mac)
2. Clear browser cache: `Ctrl + Shift + Delete`
3. Try in incognito/private window
4. Check Network tab → Disable cache checkbox

### Issue 5: Data Not Updating in Users Table

**Symptoms**:
- Performance history has records
- Users table still shows old values
- Debug shows mismatch

**Solution**:
Check the UPDATE query in `save-performance-history.php`:
```php
UPDATE users 
SET total_ape = :total_ape,
    total_prospects = :total_prospects,
    total_clients = :total_clients,
    last_sale_date = :last_sale_date,
    profile_updated_at = CURRENT_TIMESTAMP
WHERE id = :user_id
```

Manually update to test:
```sql
UPDATE users 
SET total_ape = 100000,
    total_prospects = 50,
    total_clients = 20,
    last_sale_date = '2026-05-08'
WHERE id = 8;
```

### Issue 6: JavaScript Not Loading

**Symptoms**:
- No console logs appear
- Charts don't render
- Buttons don't work

**Solution**:
1. Check browser console for JavaScript errors
2. Verify Chart.js is loaded: `console.log(typeof Chart)`
3. Check if `loadAdminPerformance` function exists: `console.log(typeof loadAdminPerformance)`
4. Manually call: `loadAdminPerformance()`

## Manual Testing Commands

### Browser Console (Admin Dashboard)

```javascript
// Check if function exists
console.log(typeof loadAdminPerformance);

// Manually trigger refresh
loadAdminPerformance();

// Check what data was loaded
console.log(allAgentsPerformance);

// Test API directly
fetch('../api/agents/get-performance.php?t=' + Date.now())
  .then(r => r.json())
  .then(d => console.log('API Response:', d));
```

### SQL Queries

```sql
-- Check all agents' performance
SELECT id, full_name, agent_code, total_ape, total_prospects, total_clients, last_sale_date
FROM users 
WHERE user_role = 'agent'
ORDER BY total_ape DESC;

-- Check specific agent
SELECT * FROM users WHERE id = 8;

-- Check performance history
SELECT * FROM performance_history ORDER BY created_at DESC LIMIT 10;

-- Check if columns exist
SHOW COLUMNS FROM users;
```

## Files to Check

1. **Agent Side**:
   - `agent/account.php` - Performance form
   - `api/agents/save-performance-history.php` - Save API

2. **Admin Side**:
   - `admin/dashboard.php` - Display dashboard
   - `api/agents/get-performance.php` - Fetch API

3. **Debug**:
   - `api/agents/debug-performance.php` - Diagnostic tool

## Expected Data Flow

```
Agent fills form
    ↓
agent/account.php submits to save-performance-history.php
    ↓
save-performance-history.php:
  1. Inserts into performance_history table
  2. Updates users table (total_ape, total_prospects, total_clients, last_sale_date)
    ↓
Admin opens dashboard
    ↓
admin/dashboard.php calls get-performance.php
    ↓
get-performance.php:
  1. Reads from users table
  2. Returns all agents' data
    ↓
admin/dashboard.php displays data in:
  - Summary cards
  - Charts
  - Performance table
```

## Quick Fix Checklist

- [ ] Agent can save performance (success message appears)
- [ ] Database columns exist (total_ape, total_prospects, total_clients)
- [ ] Users table updates after agent saves
- [ ] Performance history table has records
- [ ] Admin API returns correct data
- [ ] Admin console shows correct values
- [ ] Admin UI displays updated values
- [ ] Refresh button works
- [ ] No JavaScript errors in console
- [ ] No PHP errors in Network tab

## Contact Points

If issue persists after all checks:

1. **Provide**:
   - Browser console screenshot
   - Network tab screenshot (showing API calls)
   - Debug script output
   - SQL query results

2. **Check**:
   - PHP error logs
   - Database error logs
   - Browser version
   - Server configuration
