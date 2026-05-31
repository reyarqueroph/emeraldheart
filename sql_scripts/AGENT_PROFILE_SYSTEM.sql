-- ============================================
-- AGENT PROFILE SYSTEM - DATABASE SCHEMA
-- ============================================
-- This file contains the database changes for the agent profile system
-- with personal information tracking and birthday notifications.
--
-- Features:
-- 1. Personal information fields (birthday, contact, address)
-- 2. Performance tracking (monthly sales, prospects, clients)
-- 3. Birthday notifications in dashboard
-- ============================================

-- Add personal information columns to users table
ALTER TABLE users ADD COLUMN IF NOT EXISTS birthday DATE DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS phone_number VARCHAR(20) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS address TEXT DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS emergency_contact_name VARCHAR(100) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS emergency_contact_phone VARCHAR(20) DEFAULT NULL;

-- Add performance tracking columns
ALTER TABLE users ADD COLUMN IF NOT EXISTS monthly_sales DECIMAL(12,2) DEFAULT 0.00;
ALTER TABLE users ADD COLUMN IF NOT EXISTS total_prospects INT DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS total_clients INT DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_sale_date DATE DEFAULT NULL;

-- Add profile completion tracking
ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_completed BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Verify columns were added
-- SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'users';
