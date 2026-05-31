# Quick Fix Instructions - Performance Not Showing in Admin

## The Problem
Agent performance updates are not appearing in the admin dashboard because the database columns don't exist yet.

## The Solution (3 Easy Steps)

### Step 1: Run the Database Fix
1. **Login as Admin**
2. **Visit this URL in your browser:**
   ```
   /fix-performance-database.php
   ```
3. **Click the "Run Database Fix" button**
4. **Wait for success message** (should see green checkmarks)

### Step 2: Test as Agent
1. **Logout and login as an Agent**
2. **Go to "My Account"** page
3. **Scroll to "Performance Tracking" section**
4. **Enter test data:**
   - Total APE: 100000
   - Total Prospects: 50
   - Total Clients: 20
   - Last Sale Date: Today
   - Notes: "Test Update"
5. **Click "Save Performance Update"**
6. **Wait for success message**

### Step 3: Verify in Admin
1. **Logout and login as Admin**
2. **Go to Dashboard**
3. **Click "Refresh Data" button** (top right of Performance Overview section)
4. **Check if data appears:**
   - Summary cards should show totals
   - Performance table should show agent with data
   - Charts should display

## What the Fix Does

The `fix-performance-database.php` script will:
- ✅ Add `total_ape` column to users table
- ✅ Add `total_prospects` column to users table
- ✅ Add `total_clients` column to users table
- ✅ Add `last_sale_date` column to users table
- ✅ Add `profile_updated_at` column to users table
- ✅ Create `performance_history` table
- ✅ Rename old `monthly_sales` column if it exists

## Expected Results

### Before Fix:
- Admin dashboard shows: ₱0.00, 0, 0
- Performance table shows: "Loading..."
- Charts are empty

### After Fix:
- Agent can save performance data
- Admin dashboard shows: Real numbers
- Performance table shows: Agent data with values
- Charts display: Bar charts and pie charts with data

## Troubleshooting

### If fix-performance-database.php shows errors:
1. Check if you're logged in as admin
2. Check database connection in `api/config/database.php`
3. Check MySQL user has ALTER TABLE permissions

### If data still doesn't show after fix:
1. Visit `/test-performance-sync.php` to diagnose
2. Check browser console (F12) for JavaScript errors
3. Clear browser cache (Ctrl+Shift+Delete)
4. Try in incognito/private window

### If "Refresh Data" button doesn't work:
1. Open browser console (F12)
2. Look for error messages
3. Check Network tab for failed API calls
4. Manually refresh page (F5)

## Files Created

1. **fix-performance-database.php** - Automatic database fixer (USE THIS!)
2. **test-performance-sync.php** - Diagnostic tool
3. **api/agents/debug-performance.php** - Debug API endpoint
4. **FIX_PERFORMANCE_COLUMNS.sql** - Manual SQL script (if needed)

## Quick Test Commands

### Check if columns exist (SQL):
```sql
SHOW COLUMNS FROM users LIKE 'total_ape';
SHOW COLUMNS FROM users LIKE 'total_prospects';
SHOW COLUMNS FROM users LIKE 'total_clients';
SHOW TABLES LIKE 'performance_history';
```

### Check agent data (SQL):
```sql
SELECT id, full_name, total_ape, total_prospects, total_clients 
FROM users 
WHERE user_role = 'agent';
```

### Test API (Browser Console):
```javascript
fetch('/api/agents/get-performance.php?t=' + Date.now())
  .then(r => r.json())
  .then(d => console.log(d));
```

## Success Checklist

- [ ] Ran fix-performance-database.php successfully
- [ ] All columns show green checkmarks
- [ ] Agent can save performance data
- [ ] Agent sees success message after save
- [ ] Admin dashboard shows non-zero values
- [ ] Performance table shows agent data
- [ ] Charts display correctly
- [ ] Refresh button works

## Need Help?

If the issue persists:
1. Take screenshot of fix-performance-database.php results
2. Take screenshot of test-performance-sync.php
3. Take screenshot of browser console (F12) on admin dashboard
4. Check if MySQL user has proper permissions

## Important Notes

- **Backup First**: The fix script modifies database structure
- **Admin Only**: Must be logged in as admin to run fix
- **One Time**: Only need to run the fix once
- **Safe**: Script uses "IF NOT EXISTS" so it won't break existing data
- **Reversible**: Columns can be removed if needed (but don't!)

## After Successful Fix

Once everything works:
1. All agents can update their performance anytime
2. Admin sees updates immediately after clicking "Refresh Data"
3. Performance history is preserved (never deleted)
4. Charts update automatically every 15 minutes
5. Manual refresh button always available

## Summary

**Just do this:**
1. Visit `/fix-performance-database.php` as admin
2. Click "Run Database Fix"
3. Test as agent (save performance)
4. Check as admin (click refresh)
5. Done! ✓
