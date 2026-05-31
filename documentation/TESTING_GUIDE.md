# Testing Guide - Performance System Fixes

## Quick Test Steps

### Test 1: Agent Dashboard Chart Y-Axis
**Expected Result**: Chart should show "0 to 1,000" on the left side

1. Login as an agent
2. Go to Dashboard
3. Scroll to "My Performance" section
4. Look at the "Performance Overview" chart
5. **Verify**: Left side (Y-axis) shows "0 to 1,000" (not "0 - 1,000,000")

### Test 2: Agent Performance Updates Reflecting in Admin
**Expected Result**: Admin sees agent updates immediately after refresh

1. **Agent Side**:
   - Login as agent
   - Go to "My Account"
   - Scroll to "Performance Tracking" section
   - Enter test values:
     - Total APE: 50000
     - Total Prospects: 25
     - Total Clients: 10
     - Last Sale Date: Today's date
     - Notes: "Test Update"
   - Click "Save Performance Update"
   - Wait for success message

2. **Admin Side**:
   - Login as admin
   - Go to Dashboard
   - Click "Refresh Data" button in "Agent Performance Overview" section
   - **Verify**:
     - Summary cards show updated totals
     - Agent appears in performance table with new values
     - Top 10 Performers chart updates (if agent is in top 10)
     - Conversion Rate shows 40% (10/25 * 100)

### Test 3: Birthday Calendar Integration
**Expected Result**: Birthday appears in calendar after saving

1. **Agent Side - Set Birthday**:
   - Login as agent
   - Go to "My Account"
   - Scroll to "Personal Information" section
   - Enter birthday (e.g., May 15, 1990)
   - Click "Save Personal Info"
   - Wait for success message

2. **Agent Side - View Calendar**:
   - Go to Dashboard
   - Scroll to "Admin Updates & Calendar" section
   - Click "Calendar" tab
   - Navigate to the month of your birthday
   - **Verify**: Your birthday appears with pink color
   - Hover over the event to see your name

3. **Other Agents**:
   - Login as different agent
   - Go to Dashboard → Calendar tab
   - Navigate to the birthday month
   - **Verify**: First agent's birthday is visible

## Detailed Verification

### Agent Dashboard Chart Details
- **Y-axis label**: "0 to 1,000"
- **Y-axis max**: 1000
- **Data scaling**: 
  - Total APE divided by 1000 (e.g., ₱50,000 shows as 50)
  - Prospects show actual value (e.g., 25 shows as 25)
  - Clients show actual value (e.g., 10 shows as 10)
- **Tooltip**: Shows correct full values when hovering
  - Total APE: ₱50,000.00
  - Prospects: 25
  - Clients: 10

### Admin Dashboard Performance Table
Columns should show:
1. **Agent**: Full name + agent code
2. **Position**: Agent/OM/UM/BM badge
3. **Total APE**: ₱50,000.00 (formatted with commas)
4. **Prospects**: 25
5. **Clients**: 10
6. **Conversion Rate**: 40.00% (green badge if ≥50%, yellow if <50%)
7. **Last Sale**: May 08, 2026 (formatted date)
8. **Status**: Active (green badge)

### Birthday Calendar Events
- **Color**: Pink background (`#e83e8c`)
- **Title**: "[Agent Name]'s Birthday"
- **Date**: Correct month and day
- **Tooltip**: Shows full name and agent code
- **All Day Event**: Yes (no specific time)

## Common Issues & Solutions

### Issue 1: Admin doesn't see agent updates
**Solution**: 
- Click "Refresh Data" button
- Check browser console for errors
- Verify agent saved successfully (check success message)
- Clear browser cache (Ctrl+Shift+Delete)

### Issue 2: Chart shows wrong scale
**Solution**:
- Hard refresh page (Ctrl+F5)
- Check browser console for JavaScript errors
- Verify Chart.js is loaded (check Network tab)

### Issue 3: Birthday not appearing in calendar
**Solution**:
- Verify birthday was saved (check "My Account" page)
- Refresh calendar tab (switch to Announcements then back to Calendar)
- Check browser console for API errors
- Verify agent status is "active"

## API Endpoints to Test

### 1. Save Performance
```
POST /api/agents/save-performance-history.php
Body: {
  "total_ape": 50000,
  "total_prospects": 25,
  "total_clients": 10,
  "last_sale_date": "2026-05-08",
  "notes": "Test Update"
}
Expected: {"success": true, "message": "Performance updated successfully. History saved."}
```

### 2. Get Performance (Agent)
```
GET /api/agents/get-performance.php
Expected: {
  "success": true,
  "data": {
    "agent": {...},
    "metrics": {
      "total_ape": 50000,
      "total_prospects": 25,
      "total_clients": 10,
      "conversion_rate": 40
    }
  }
}
```

### 3. Get Performance (Admin)
```
GET /api/agents/get-performance.php?t=1715155200000
Expected: {
  "success": true,
  "data": {
    "agents": [...],
    "top_performers": [...],
    "summary": {
      "total_ape": ...,
      "total_prospects": ...,
      "total_clients": ...,
      "active_agents": ...,
      "avg_ape": ...,
      "avg_prospects": ...,
      "avg_clients": ...
    }
  }
}
```

### 4. Get All Birthdays
```
GET /api/agents/get-all-birthdays.php
Expected: {
  "success": true,
  "data": [
    {
      "id": "birthday-8",
      "title": "John Doe's Birthday",
      "start": "2026-05-15",
      "allDay": true,
      "event_type": "birthday",
      "description": "John Doe (AG001) - Birthday",
      "user_id": 8,
      "user_role": "agent"
    }
  ]
}
```

## Browser Console Commands

### Check if Chart.js is loaded:
```javascript
console.log(typeof Chart);
// Should output: "function"
```

### Check if FullCalendar is loaded:
```javascript
console.log(typeof FullCalendar);
// Should output: "object"
```

### Manually trigger admin performance refresh:
```javascript
loadAdminPerformance();
```

### Check calendar events:
```javascript
agentCalendar.getEvents();
// Should show array of events including birthdays
```

## Success Criteria

✅ **All tests pass if**:
1. Agent dashboard chart shows "0 to 1,000" label
2. Agent performance updates appear in admin dashboard after refresh
3. Birthdays appear in calendar with pink color
4. All tooltips show correct values
5. No JavaScript errors in browser console
6. All API calls return success responses

## Rollback Plan

If issues occur, revert these files:
1. `agent/dashboard.php` - Chart configuration
2. `admin/dashboard.php` - Refresh button and cache-busting

Original values:
- Y-axis max: `1000000`
- Y-axis text: `"0 - 1,000,000"`
- Data scaling: `metrics.total_ape` (no division)
- No timestamp parameter in fetch call
