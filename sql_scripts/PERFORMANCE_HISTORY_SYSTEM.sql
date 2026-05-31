-- ============================================
-- PERFORMANCE HISTORY TRACKING SYSTEM
-- ============================================
-- This creates a separate table to track performance updates over time
-- allowing agents to update multiple times without losing previous data.
-- ============================================

-- Create performance_history table
CREATE TABLE IF NOT EXISTS performance_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_ape DECIMAL(12,2) DEFAULT 0.00,
    total_prospects INT DEFAULT 0,
    total_clients INT DEFAULT 0,
    last_sale_date DATE DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- Update users table column name from monthly_sales to total_ape
ALTER TABLE users CHANGE COLUMN monthly_sales total_ape DECIMAL(12,2) DEFAULT 0.00;

-- If the column doesn't exist yet, add it
ALTER TABLE users ADD COLUMN IF NOT EXISTS total_ape DECIMAL(12,2) DEFAULT 0.00;

-- Verify tables
-- SELECT * FROM performance_history LIMIT 5;
-- SELECT id, full_name, total_ape, total_prospects, total_clients FROM users LIMIT 5;
