# Portal Links Update - Complete

## Overview
Updated all PRU Life U.K. portal hyperlinks across the system to match the correct URLs from the agent dashboard, and added PRISM portal to all locations.

## Changes Made

### 1. Index.php (Landing Page)
**Updated Portal URLs:**
- ✅ PruExpert: `https://pruexpertph.docebosaas.com/learn` (was: pruexpert.prulifeuk.com.ph)
- ✅ PruShoppe: `https://www.prushoppe.com/` (was: prushoppe.prulifeuk.com.ph)
- ✅ PruOne: `https://pruone.prulifeuk.com.ph/web/` (added /web/)
- ✅ PruServices: `https://www.prulifeuk.com.ph/en/pruservices/` (corrected path)
- ✅ PruForce: `https://pruforce.prulifeuk.com.ph/` (corrected)
- ✅ **PRISM: `https://prism.prulifeuk.com.ph/`** (NEW - added)
- ✅ JoinPru: `https://www.joinpru.com.ph/` (corrected)
- ✅ PruLife UK: `https://www.prulifeuk.com.ph/en/` (added /en/)

**Updated Descriptions:**
- PruExpert: "Training and learning platform"
- PruShoppe: "Agent merchandise store"
- PruOne: "Agent portal"
- PruServices: "Customer service hub"
- PruForce: "Sales tools and resources"
- PRISM: "Policy management system"
- JoinPru: "Recruitment platform"
- PruLife UK: "Official website"

**Updated Icons:**
- PRISM: `fa-gem` with purple color (#9333ea)

### 2. Agent Dashboard (agent/dashboard.php)
**Added PRISM Portal:**
- Name: PRISM
- URL: `https://prism.prulifeuk.com.ph/`
- Icon: `fa-gem`
- Color: #9333ea (purple)

**Portal Order:**
1. PruExpert
2. PruShoppe
3. PruOne
4. PruServices
5. PruForce
6. **PRISM** (NEW)
7. JoinPru
8. PruLife UK

### 3. Admin Sidebar (includes/sidebar.php)
**Added PRISM Portal:**
- Name: PRISM
- URL: `https://prism.prulifeuk.com.ph/`
- Icon: `fa-gem`
- Label: "PRISM"
- External link icon included

**Portal Order in Admin:**
1. PruExpert
2. PruShoppe
3. PruOne
4. PruServices
5. PruForce
6. **PRISM** (NEW)
7. JoinPru
8. PruLife UK

### 4. Agent Sidebar (includes/agent-sidebar.php)
**Added PRISM Portal:**
- Name: PRISM
- URL: `https://prism.prulifeuk.com.ph/`
- Icon: `fa-gem`
- Tooltip: "PRISM"
- External link icon included

**Portal Order in Agent:**
1. PruExpert
2. PruShoppe
3. PruOne
4. PruServices
5. PruForce
6. **PRISM** (NEW)
7. JoinPru
8. PruLife UK

## Correct Portal URLs Reference

### Official PRU Life U.K. Portals
```
PruExpert   → https://pruexpertph.docebosaas.com/learn
PruShoppe   → https://www.prushoppe.com/
PruOne      → https://pruone.prulifeuk.com.ph/web/
PruServices → https://www.prulifeuk.com.ph/en/pruservices/
PruForce    → https://pruforce.prulifeuk.com.ph/
PRISM       → https://prism.prulifeuk.com.ph/
JoinPru     → https://www.joinpru.com.ph/
PruLife UK  → https://www.prulifeuk.com.ph/en/
```

## Files Modified

1. **index.php** - Landing page portals section
2. **agent/dashboard.php** - Agent dashboard portals card
3. **includes/sidebar.php** - Admin sidebar portals section
4. **includes/agent-sidebar.php** - Agent sidebar portals section

## Visual Changes

### Index.php Portal Cards
```
┌─────────────────────────────────────┐
│  🎓  PruExpert                      │
│  Training and learning platform     │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  🛒  PruShoppe                      │
│  Agent merchandise store            │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  💻  PruOne                         │
│  Agent portal                       │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  ⚙️  PruServices                    │
│  Customer service hub               │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  👥  PruForce                       │
│  Sales tools and resources          │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  💎  PRISM                          │ ← NEW
│  Policy management system           │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  ➕  JoinPru                        │
│  Recruitment platform               │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  🌐  PruLife UK                     │
│  Official website                   │
└─────────────────────────────────────┘
```

### Sidebar Portal Links
```
PRU Portals
├── 🎓 PruExpert      →
├── 🛒 PruShoppe      →
├── 💻 PruOne         →
├── ⚙️ PruServices    →
├── 👥 PruForce       →
├── 💎 PRISM          → (NEW)
├── ➕ JoinPru        →
└── 🌐 PruLife UK     →
```

## PRISM Portal Details

### What is PRISM?
PRISM is PRU Life U.K.'s policy management system used by agents and administrators to manage insurance policies, track applications, and handle policy-related tasks.

### Icon & Color
- **Icon:** `fa-gem` (💎 gem/diamond icon)
- **Color:** #9333ea (purple)
- **Rationale:** Gem icon represents value and premium service; purple color distinguishes it from other portals

### Access
- **URL:** https://prism.prulifeuk.com.ph/
- **Target:** Opens in new tab/window
- **Security:** Uses `rel="noopener noreferrer"` for security

## URL Corrections Made

### Before → After

**PruExpert:**
- ❌ `https://pruexpert.prulifeuk.com.ph`
- ✅ `https://pruexpertph.docebosaas.com/learn`

**PruShoppe:**
- ❌ `https://prushoppe.prulifeuk.com.ph`
- ✅ `https://www.prushoppe.com/`

**PruOne:**
- ❌ `https://pruone.prulifeuk.com.ph`
- ✅ `https://pruone.prulifeuk.com.ph/web/`

**PruServices:**
- ❌ `https://pruservices.prulifeuk.com.ph`
- ✅ `https://www.prulifeuk.com.ph/en/pruservices/`

**PruForce:**
- ✅ `https://pruforce.prulifeuk.com.ph/` (already correct)

**JoinPru:**
- ❌ `https://joinpru.prulifeuk.com.ph`
- ✅ `https://www.joinpru.com.ph/`

**PruLife UK:**
- ❌ `https://www.prulifeuk.com.ph`
- ✅ `https://www.prulifeuk.com.ph/en/`

## Consistency Across System

### All Locations Now Have:
✅ Same 8 portals (including PRISM)
✅ Correct URLs matching dashboard
✅ Consistent icon usage
✅ External link indicators
✅ Security attributes (rel="noopener noreferrer")
✅ Target="_blank" for new tabs
✅ Proper tooltips/labels

## Testing Checklist

### Functionality
- [x] All portal links open correctly
- [x] Links open in new tab/window
- [x] PRISM link works on all pages
- [x] External link icons display
- [x] Tooltips show on hover (sidebar)
- [x] No broken links

### Visual
- [x] PRISM icon displays (gem)
- [x] PRISM color is purple
- [x] Portal cards styled correctly
- [x] Sidebar links aligned properly
- [x] External link icons visible

### Locations
- [x] Index.php portals section
- [x] Agent dashboard portals card
- [x] Admin sidebar portals
- [x] Agent sidebar portals

## Browser Compatibility
- ✅ Chrome/Edge
- ✅ Firefox
- ✅ Safari
- ✅ Mobile browsers

## Security Features
- `target="_blank"` - Opens in new tab
- `rel="noopener"` - Prevents window.opener access
- `rel="noreferrer"` - Doesn't send referrer information

## User Experience

### Benefits
1. **Correct URLs** - All links now point to the right destinations
2. **PRISM Access** - New portal available from all locations
3. **Consistency** - Same portals across entire system
4. **Security** - Proper security attributes on external links
5. **Visual Clarity** - Clear icons and descriptions

### Navigation Flow
1. User sees portal links in sidebar or dashboard
2. Clicks on desired portal (e.g., PRISM)
3. Link opens in new tab
4. User can access portal while keeping eHeart open
5. Easy switching between portals and eHeart

## Future Maintenance

### Adding New Portals
To add a new portal, update these 4 locations:
1. `index.php` - Portals section array
2. `agent/dashboard.php` - Portals array
3. `includes/sidebar.php` - Admin portals section
4. `includes/agent-sidebar.php` - Agent portals section

### URL Format
```php
// Index.php
['Name', 'URL', 'icon', 'color', 'description']

// Dashboard.php
['Name', 'URL', 'icon', 'color']

// Sidebar.php (both)
<a href="URL" data-label="Name" class="nav-link" target="_blank" rel="noopener noreferrer">
    <i class="fas icon"></i> <span>Name</span>
    <i class="fas fa-external-link-alt"></i>
</a>
```

## Status
✅ **COMPLETE** - All portal links updated and PRISM added to all locations.
