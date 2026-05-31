# SQL Scripts Folder 🗄️

This folder contains all SQL script files for database updates, fixes, and enhancements.

## Contents

This folder includes SQL scripts for:

### Admin Features
- `ADMIN_ANNOUNCEMENTS_UPDATE.sql` - Admin announcements system updates
- `UPDATE_ADMIN_EMAIL.sql` - Admin email configuration updates

### Agent Features
- `AGENT_PROFILE_SYSTEM.sql` - Agent profile system schema
- `PERFORMANCE_HISTORY_SYSTEM.sql` - Performance tracking history tables

### Announcements
- `ANNOUNCEMENTS_TIME_UPDATE.sql` - Announcement time handling updates

### Product Management
- `CHECK_ROW_22.sql` - Product data verification
- `CLEAN_PRODUCT_GUIDES.sql` - Product guides cleanup
- `EMERGENCY_FIX_CATEGORIES.sql` - Product category emergency fixes
- `FIX_CATEGORY_COLUMN_SIZE.sql` - Category column size adjustments
- `fix_product_categories.sql` - Product category fixes

### Chatbot
- `ENHANCED_CHATBOT_SCHEMA.sql` - Enhanced chatbot database schema

### Feedback System
- `FEEDBACK_ENHANCEMENTS.sql` - Feedback system enhancements

### Payment System
- `GCASH_PAYMENT_SYSTEM.sql` - GCash payment integration schema

### Performance Tracking
- `FIX_PERFORMANCE_COLUMNS.sql` - Performance tracking column fixes

### General Fixes
- `COMPLETE_FIX_STEP_BY_STEP.sql` - Comprehensive database fixes

## File Count
Total SQL script files: 15

## Important Notes

⚠️ **WARNING:** Always backup your database before running any SQL scripts!

### Execution Order
Some scripts may depend on others. Review the script contents before execution.

### Main Database File
The main database schema file `pru_life_db.sql` is located in the **root directory**, not in this folder.

## Usage

1. **Backup First:** Always create a database backup before running scripts
2. **Review Script:** Read the script contents to understand what it does
3. **Test Environment:** Test scripts in a development environment first
4. **Execute:** Run the script using phpMyAdmin, MySQL Workbench, or command line
5. **Verify:** Check that the changes were applied correctly

### Example Execution (Command Line)
```bash
mysql -u username -p database_name < sql_scripts/SCRIPT_NAME.sql
```

### Example Execution (phpMyAdmin)
1. Open phpMyAdmin
2. Select your database
3. Go to "Import" tab
4. Choose the SQL file
5. Click "Go"

## Organization
All files are organized alphabetically by filename for easy reference.

---

**Last Updated:** May 8, 2026
**Project:** PRU Life eHeart System
**Database:** pru_life_db
