-- ============================================
-- FEEDBACK ENHANCEMENTS - DATABASE SCHEMA
-- ============================================
-- This file documents the database changes for the enhanced feedback system
-- with subject dropdown and emoji mood rating features.
--
-- Changes:
-- 1. Added mood_rating column to feedbacks table
-- 2. Mood rating values: 1 (😢 Very Unhappy), 2 (😟 Unhappy), 3 (😐 Neutral), 4 (🙂 Happy), 5 (😄 Very Happy)
--
-- Note: The ALTER TABLE statement is automatically executed in api/feedbacks/create.php
-- to ensure the column exists before inserting data.
-- ============================================

-- Add mood_rating column to feedbacks table
ALTER TABLE feedbacks ADD COLUMN IF NOT EXISTS mood_rating INT DEFAULT NULL;

-- Verify the column was added
-- SELECT * FROM feedbacks LIMIT 1;
