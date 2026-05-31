-- COMPLETE FIX FOR CATEGORY ISSUES
-- Run each step one at a time in phpMyAdmin

-- ============================================
-- STEP 1: Check current state
-- ============================================
SELECT 
    id,
    product_name,
    CONCAT('"', category, '"') as category,
    LENGTH(category) as length
FROM products
ORDER BY id;

-- ============================================
-- STEP 2: Check the column definition
-- ============================================
SHOW COLUMNS FROM products LIKE 'category';

-- ============================================
-- STEP 3: Ensure column is large enough
-- ============================================
ALTER TABLE products 
MODIFY COLUMN category VARCHAR(150) NOT NULL;

-- ============================================
-- STEP 4: Fix categories ONE BY ONE to avoid truncation
-- ============================================

-- Fix anything with "accident" to Stand-Alone Product
UPDATE products 
SET category = 'Stand-Alone Product' 
WHERE LOWER(category) LIKE '%accident%';

-- Fix anything with "stand" to Stand-Alone Product
UPDATE products 
SET category = 'Stand-Alone Product' 
WHERE LOWER(category) LIKE '%stand%'
AND category != 'Stand-Alone Product';

-- Fix anything with "guide" to Product Guides
UPDATE products 
SET category = 'Product Guides' 
WHERE LOWER(category) LIKE '%guide%'
AND category != 'Product Guides';

-- Fix "traditional" to Traditional Life Insurance
UPDATE products 
SET category = 'Traditional Life Insurance' 
WHERE LOWER(TRIM(category)) = 'traditional';

-- Fix "vul" variations
UPDATE products 
SET category = 'VUL' 
WHERE LOWER(TRIM(category)) IN ('vul', 'variable unit-linked', 'variable unit linked');

-- Remove whitespace
UPDATE products 
SET category = TRIM(category);

-- ============================================
-- STEP 5: Verify the fix
-- ============================================
SELECT 
    category,
    COUNT(*) as count,
    GROUP_CONCAT(id ORDER BY id) as product_ids
FROM products
GROUP BY category
ORDER BY category;

-- ============================================
-- STEP 6: Check for any remaining issues
-- ============================================
SELECT 
    id,
    product_name,
    category
FROM products
WHERE category NOT IN ('VUL', 'Traditional Life Insurance', 'Stand-Alone Product', 'Product Guides')
ORDER BY id;

-- ============================================
-- EXPECTED RESULT:
-- ============================================
-- You should see exactly 4 categories:
-- 1. VUL
-- 2. Traditional Life Insurance  
-- 3. Stand-Alone Product
-- 4. Product Guides
--
-- Step 6 should return 0 rows (no unknown categories)