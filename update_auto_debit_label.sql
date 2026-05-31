-- Update Auto-Debit label to "Payment - Auto Debit & Credit Card"
-- This updates the services table where the section title is "Auto-Debit"

UPDATE services_sections 
SET title = 'Payment - Auto Debit & Credit Card'
WHERE title = 'Auto-Debit' OR section_key = 'auto-debit';

-- Verify the update
SELECT id, section_key, title, category 
FROM services_sections 
WHERE section_key = 'auto-debit' OR title LIKE '%Auto%Debit%';
