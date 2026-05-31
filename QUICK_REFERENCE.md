# Quick Reference Card 📋

## Project Organization

### 📚 Documentation
**Location:** `/documentation/`
**Files:** 52 markdown files
**Purpose:** All project documentation, guides, and notes

### 🗄️ SQL Scripts
**Location:** `/sql_scripts/`
**Files:** 16 SQL files
**Purpose:** Database updates, patches, and migrations

### 💾 Main Database
**Location:** `/pru_life_db.sql` (root)
**Purpose:** Complete database schema for fresh installations

---

## Quick Commands

### View Documentation
```bash
cd documentation
ls
```

### View SQL Scripts
```bash
cd sql_scripts
ls
```

### Import Main Database
```bash
mysql -u username -p database_name < pru_life_db.sql
```

### Run SQL Script
```bash
mysql -u username -p database_name < sql_scripts/SCRIPT_NAME.sql
```

---

## Folder Structure
```
pru_life_system/
├── 📚 documentation/          (52 files)
├── 🗄️ sql_scripts/            (16 files)
├── 💾 pru_life_db.sql         (Main schema)
├── 📄 PROJECT_ORGANIZATION.md (Guide)
├── 📄 ORGANIZATION_COMPLETE.md (Summary)
├── 📄 QUICK_REFERENCE.md      (This file)
│
├── admin/                     (Admin panel)
├── agent/                     (Agent portal)
├── api/                       (API endpoints)
├── assets/                    (CSS, JS, images)
├── includes/                  (Shared includes)
└── uploads/                   (User uploads)
```

---

## Key Files

### Documentation Highlights
- `CHATBOT_IMPLEMENTATION_SUMMARY.md` - Complete chatbot guide
- `ADMIN_PASSWORD_RESET_FEATURE.md` - Password reset system
- `PAYMENT_INTEGRATION_COMPLETE.md` - GCash payment guide
- `TESTING_GUIDE.md` - Testing instructions

### SQL Script Highlights
- `AGENT_PROFILE_SYSTEM.sql` - Agent profiles
- `GCASH_PAYMENT_SYSTEM.sql` - Payment system
- `PERFORMANCE_HISTORY_SYSTEM.sql` - Performance tracking
- `ENHANCED_CHATBOT_SCHEMA.sql` - Chatbot database

---

## Need Help?

### For Documentation
Read: `/documentation/README.md`

### For SQL Scripts
Read: `/sql_scripts/README.md`

### For Project Structure
Read: `PROJECT_ORGANIZATION.md`

---

**Last Updated:** May 8, 2026
**Project:** PRU Life eHeart System
