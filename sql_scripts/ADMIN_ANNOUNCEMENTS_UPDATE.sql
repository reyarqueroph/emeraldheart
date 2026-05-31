-- Admin Announcements/Calendar Feature - Database Updates
-- Run these SQL commands in phpMyAdmin

-- Step 1: Create admin_announcements table
CREATE TABLE IF NOT EXISTS admin_announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    announcement_type ENUM('general', 'urgent', 'reminder', 'event') DEFAULT 'general',
    start_date DATE NULL,
    end_date DATE NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_active (is_active),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_type (announcement_type),
    INDEX idx_created_at (created_at)
);

-- Step 2: Insert sample announcements for testing
INSERT INTO admin_announcements (title, message, announcement_type, start_date, end_date, created_by) VALUES
('System Maintenance', 'Scheduled system maintenance on Sunday 2AM-4AM. Please save your work before this time.', 'urgent', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1),
('New Product Launch', 'Exciting new VUL product launching next month. Training sessions will be announced soon.', 'general', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 1),
('Monthly Sales Meeting', 'All agents are required to attend the monthly sales meeting on the 15th.', 'event', DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 1);

-- Step 3: Verify the table was created
DESCRIBE admin_announcements;

-- Step 4: Check sample data
SELECT * FROM admin_announcements WHERE is_active = 1 ORDER BY created_at DESC;