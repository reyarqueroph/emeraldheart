-- ============================================
-- FIX PERFORMANCE COLUMNS - Run this SQL script
-- ============================================
-- This script will add/rename the necessary columns
-- for the performance tracking system to work
-- ============================================

-- Step 1: Check if monthly_sales column exists and rename it to total_ape
-- If it doesn't exist, create total_ape column
ALTER TABLE users 
MODIFY COLUMN monthly_sales DECIMAL(12,2) DEFAULT 0.00;

ALTER TABLE users 
CHANGE COLUMN monthly_sales total_ape DECIMAL(12,2) DEFAULT 0.00;

-- If the above fails (column doesn't exist), run this:
-- ALTER TABLE users ADD COLUMN total_ape DECIMAL(12,2) DEFAULT 0.00;

-- Step 2: Add other performance columns if they don't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS total_prospects INT DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS total_clients INT DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_sale_date DATE DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Step 3: Create performance_history table
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
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 4: Verify columns were added
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    COLUMN_DEFAULT, 
    IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'users' 
  AND COLUMN_NAME IN ('total_ape', 'total_prospects', 'total_clients', 'last_sale_date', 'profile_updated_at')
ORDER BY COLUMN_NAME;

-- Step 5: Check current data
SELECT 
    id,
    full_name,
    agent_code,
    user_role,
    total_ape,
    total_prospects,
    total_clients,
    last_sale_date,
    status
FROM users 
WHERE user_role = 'agent'
ORDER BY id;

-- ============================================
-- NOTES:
-- ============================================
-- After running this script:
-- 1. All agents should have total_ape, total_prospects, total_clients columns
-- 2. Default values will be 0 or NULL
-- 3. Agents can now update their performance in agent/account.php
-- 4. Admin will see the updates in admin/dashboard.php
-- ============================================
