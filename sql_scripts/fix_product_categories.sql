-- SQL Script to Check and Fix Product Categories
-- Run this in phpMyAdmin or MySQL command line

-- 1. Check current categories in database
SELECT 
    category,
    COUNT(*) as count,
    GROUP_CONCAT(product_name SEPARATOR ', ') as products
FROM products
GROUP BY category
ORDER BY category;

-- 2. Show all products with their exact categories
SELECT 
    id,
    product_name,
    CONCAT('"', category, '"') as category_with_quotes,
    LENGTH(category) as category_length,
    is_active
FROM products
ORDER BY id;

-- 3. Fix any category variations (run these one by one)

-- Fix "Personal Accident" to "Stand-Alone Product"
UPDATE products 
SET category = 'Stand-Alone Product' 
WHERE LOWER(TRIM(category)) IN ('personal accident', 'stand-alone', 'stand alone');

-- Fix "Product Guide" to "Product Guides"
UPDATE products 
SET category = 'Product Guides' 
WHERE LOWER(TRIM(category)) = 'product guide';

-- Fix "Traditional" to "Traditional Life Insurance"
UPDATE products 
SET category = 'Traditional Life Insurance' 
WHERE LOWER(TRIM(category)) = 'traditional';

-- Fix any whitespace issues
UPDATE products 
SET category = TRIM(category);

-- 4. Verify the fix - should only show these 4 categories:
-- VUL, Traditional Life Insurance, Stand-Alone Product, Product Guides
SELECT 
    category,
    COUNT(*) as count
FROM products
GROUP BY category
ORDER BY category;

-- 5. Show products that might still have issues
SELECT 
    id,
    product_name,
    category
FROM products
WHERE category NOT IN ('VUL', 'Traditional Life Insurance', 'Stand-Alone Product', 'Product Guides')
ORDER BY id;