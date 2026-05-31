-- GCash Payment System for Agent Registration
-- Run this SQL in phpMyAdmin

-- Create payment_transactions table
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    transaction_id VARCHAR(100) UNIQUE NOT NULL,
    gcash_reference VARCHAR(100) NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('gcash', 'bank_transfer', 'cash') DEFAULT 'gcash',
    status ENUM('pending', 'paid', 'verified', 'rejected') DEFAULT 'pending',
    payment_proof TEXT NULL,
    admin_notes TEXT NULL,
    verified_by INT NULL,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    INDEX idx_transaction_id (transaction_id)
);

-- Add payment status to users table
ALTER TABLE users 
ADD COLUMN payment_status ENUM('unpaid', 'pending', 'paid', 'verified') DEFAULT 'unpaid' AFTER position,
ADD COLUMN subscription_expires DATE NULL AFTER payment_status,
ADD COLUMN registration_fee DECIMAL(10,2) DEFAULT 500.00 AFTER subscription_expires;

-- Create payment_settings table for admin configuration
CREATE TABLE IF NOT EXISTS payment_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    description TEXT NULL,
    updated_by INT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default payment settings
INSERT INTO payment_settings (setting_key, setting_value, description) VALUES
('registration_fee', '500.00', 'Registration fee for new agents'),
('gcash_number', '09123456789', 'GCash number for payments'),
('gcash_name', 'PRU LIFE UK', 'GCash account name'),
('payment_instructions', 'Send payment to the GCash number above and upload your payment receipt.', 'Payment instructions for agents'),
('auto_approve', '0', 'Auto-approve payments (1=yes, 0=no)');

-- Update existing users to have unpaid status
UPDATE users SET payment_status = 'unpaid' WHERE payment_status IS NULL;

-- Verify tables created
SHOW TABLES LIKE '%payment%';
DESCRIBE payment_transactions;
DESCRIBE payment_settings;