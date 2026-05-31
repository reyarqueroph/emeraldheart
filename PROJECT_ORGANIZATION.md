# PRU Life eHeart System - Project Organization 📁

## Folder Structure

The project has been organized for better maintainability and clarity.

### 📚 Documentation Folder (`/documentation/`)
Contains all project documentation files (51 files):
- Feature implementation guides
- AI Chatbot documentation
- Setup and configuration guides
- Testing guides
- Troubleshooting documentation
- Developer notes

**Location:** `/documentation/`
**File Type:** `.md` (Markdown)
**Purpose:** Project documentation and guides

### 🗄️ SQL Scripts Folder (`/sql_scripts/`)
Contains all SQL script files for database updates (15 files):
- Database schema updates
- Feature-specific SQL scripts
- Bug fixes and patches
- Data migration scripts

**Location:** `/sql_scripts/`
**File Type:** `.sql` (SQL Scripts)
**Purpose:** Database updates and modifications

### 💾 Main Database File
The main database schema remains in the root directory:
- `pru_life_db.sql` - Complete database schema

**Location:** `/pru_life_db.sql` (Root directory)
**Purpose:** Main database schema for fresh installations

---

## Quick Access

### For Developers
- **Documentation:** See `/documentation/` folder
- **Database Scripts:** See `/sql_scripts/` folder
- **Main Schema:** See `pru_life_db.sql` in root

### For Setup
1. **Fresh Installation:** Import `pru_life_db.sql` from root
2. **Updates/Patches:** Use scripts from `/sql_scripts/` folder
3. **Feature Guides:** Check `/documentation/` folder

---

## File Counts

| Category | Count | Location |
|----------|-------|----------|
| Documentation Files | 51 | `/documentation/` |
| SQL Script Files | 15 | `/sql_scripts/` |
| Main Database Schema | 1 | `/pru_life_db.sql` (root) |

---

## Benefits of This Organization

✅ **Cleaner Root Directory** - Less clutter in the main folder
✅ **Easy Navigation** - Related files grouped together
✅ **Better Maintenance** - Easier to find and update files
✅ **Clear Separation** - Documentation vs Database scripts
✅ **Professional Structure** - Industry-standard organization

---

## Important Notes

### Documentation Folder
- All `.md` files have been moved here
- Includes README.md explaining contents
- Organized alphabetically

### SQL Scripts Folder
- All `.sql` files moved here (except main schema)
- Includes README.md with usage instructions
- **Always backup before running scripts!**

### Root Directory
- Kept clean with only essential files
- Main database schema (`pru_life_db.sql`) remains here
- Application source code folders remain unchanged

---

## Application Folders (Unchanged)

The following folders remain in their original locations:

- `/admin/` - Admin panel files
- `/agent/` - Agent portal files
- `/api/` - API endpoints
- `/assets/` - CSS, JS, images
- `/includes/` - Shared PHP includes
- `/uploads/` - User-uploaded files

---

## How to Use

### Reading Documentation
```bash
cd documentation
# Browse .md files
```

### Running SQL Scripts
```bash
cd sql_scripts
# Review and execute scripts as needed
```

### Fresh Database Setup
```bash
# Import from root directory
mysql -u username -p database_name < pru_life_db.sql
```

---

## Maintenance

### Adding New Documentation
Place new `.md` files in `/documentation/` folder

### Adding New SQL Scripts
Place new `.sql` files in `/sql_scripts/` folder

### Updating Main Schema
Update `pru_life_db.sql` in root directory

---

**Organization Date:** May 8, 2026
**Project:** PRU Life eHeart System
**Status:** ✅ Organized and Ready
