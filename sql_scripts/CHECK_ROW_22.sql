-- Check what's in row 22 that's causing the issue
SELECT 
    id,
    product_name,
    CONCAT('"', category, '"') as current_category,
    LENGTH(category) as category_length,
    CHAR_LENGTH(category) as category_char_length
FROM products
WHERE id = 22;

-- Check all products to see which ones have long or problematic categories
SELECT 
    id,
    product_name,
    CONCAT('"', category, '"') as current_category,
    LENGTH(category) as length
FROM products
ORDER BY LENGTH(category) DESC
LIMIT 20;