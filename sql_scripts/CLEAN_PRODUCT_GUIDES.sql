-- Clean up Product Guides to remove age range, premium, and payment type display
-- Run this in phpMyAdmin to clean existing data

-- Update existing Product Guides to have empty age_range
UPDATE products 
SET age_range = '', 
    min_premium_monthly = 0.00
WHERE category = 'Product Guides';

-- Verify the changes
SELECT 
    id,
    product_name,
    category,
    age_range,
    min_premium_monthly,
    payment_type
FROM products 
WHERE category = 'Product Guides'
ORDER BY id;