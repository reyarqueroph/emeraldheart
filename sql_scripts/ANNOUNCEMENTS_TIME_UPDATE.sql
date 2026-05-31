-- Add time fields to admin_announcements table
-- Run this SQL in phpMyAdmin

ALTER TABLE admin_announcements 
ADD COLUMN start_time TIME NULL AFTER start_date,
ADD COLUMN end_time TIME NULL AFTER end_date;

-- Update existing announcements with default times if needed
-- UPDATE admin_announcements SET start_time = '09:00:00' WHERE start_date IS NOT NULL AND start_time IS NULL;
-- UPDATE admin_announcements SET end_time = '17:00:00' WHERE end_date IS NOT NULL AND end_time IS NULL;

-- Verify the changes
DESCRIBE admin_announcements;