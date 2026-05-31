-- EMERGENCY FIX: Correct all product categories
-- Run this in phpMyAdmin SQL tab

-- Step 1: See what categories currently exist
SELECT 
    CONCAT('"', category, '"') as category_with_quotes,
    COUNT(*) as count,
    GROUP_CONCAT(product_name SEPARATOR ' | ') as products
FROM products
GROUP BY category
ORDER BY category;

-- Step 2: Fix Stand-Alone Products (all variations)
UPDATE products 
SET category = 'Stand-Alone Product' 
WHERE LOWER(TRIM(category)) IN (
    'personal accident',
    'stand-alone',
    'stand alone',
    'stand-alone product',
    'stand alone product',
    'standalone',
    'standalone product'
)
OR LOWER(TRIM(category)) LIKE '%accident%';

-- Step 3: Fix Product Guides (all variations)
UPDATE products 
SET category = 'Product Guides' 
WHERE LOWER(TRIM(category)) IN (
    'product guide',
    'product guides',
    'guide',
    'guides'
)
OR LOWER(TRIM(category)) LIKE '%guide%';

-- Step 4: Fix Traditional Life Insurance (all variations)
UPDATE products 
SET category = 'Traditional Life Insurance' 
WHERE LOWER(TRIM(category)) IN (
    'traditional',
    'traditional life',
    'traditional insurance',
    'traditional life insurance'
);

-- Step 5: Fix VUL (all variations)
UPDATE products 
SET category = 'VUL' 
WHERE LOWER(TRIM(category)) IN (
    'vul',
    'variable unit-linked',
    'variable unit linked'
);

-- Step 6: Remove any whitespace
UPDATE products 
SET category = TRIM(category);

-- Step 7: Verify - should show exactly 4 categories
SELECT 
    category,
    COUNT(*) as count,
    GROUP_CONCAT(product_name SEPARATOR ' | ') as products
FROM products
GROUP BY category
ORDER BY category;

-- Step 8: Check for any remaining unknown categories
SELECT 
    id,
    product_name,
    CONCAT('"', category, '"') as category
FROM products
WHERE category NOT IN ('VUL', 'Traditional Life Insurance', 'Stand-Alone Product', 'Product Guides')
ORDER BY id;

-- If Step 8 shows any products, manually update them:
-- UPDATE products SET category = 'Stand-Alone Product' WHERE id = X;
-- UPDATE products SET category = 'Product Guides' WHERE id = Y;