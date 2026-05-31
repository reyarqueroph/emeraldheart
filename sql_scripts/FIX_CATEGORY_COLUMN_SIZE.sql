-- Fix the category column size issue
-- Run this FIRST before fixing categories

-- Step 1: Check current column definition
SHOW COLUMNS FROM products LIKE 'category';

-- Step 2: Alter the column to ensure it's large enough
ALTER TABLE products 
MODIFY COLUMN category VARCHAR(100) NOT NULL;

-- Step 3: Verify the change
SHOW COLUMNS FROM products LIKE 'category';

-- Step 4: Now fix the categories
UPDATE products SET category = 'Stand-Alone Product' 
WHERE LOWER(TRIM(category)) LIKE '%accident%' 
   OR LOWER(TRIM(category)) LIKE '%stand%';

UPDATE products SET category = 'Product Guides' 
WHERE LOWER(TRIM(category)) LIKE '%guide%';

UPDATE products SET category = 'Traditional Life Insurance' 
WHERE LOWER(TRIM(category)) = 'traditional';

UPDATE products SET category = TRIM(category);

-- Step 5: Verify the fix worked
SELECT 
    category,
    LENGTH(category) as length,
    COUNT(*) as count
FROM products
GROUP BY category
ORDER BY category;