# Fix Missing Stand-Alone Products and Product Guides

## Problem
Stand-alone products and product guides exist in the database but don't show up in the admin interface or debug mode.

## Root Cause
The products likely have incorrect or inconsistent category names in the database. For example:
- "Personal Accident" instead of "Stand-Alone Product"
- "Product Guide" instead of "Product Guides"
- Extra whitespace in category names
- Case sensitivity issues

## Solution Options

### Option 1: Use the Diagnostic Tool (Easiest)

1. **Navigate to the diagnostic page:**
   ```
   http://localhost/pru_life_system/admin/fix-products-diagnostic.php
   ```

2. **The page will automatically show:**
   - Total products in database
   - Products grouped by category
   - All products with their exact category names
   - Any category mismatches highlighted in red

3. **Click "Fix Categories" button**
   - This will automatically correct common category variations
   - Shows how many records were fixed

4. **Click "Check Database" again to verify**
   - All products should now have correct categories

5. **Go back to the main products page**
   - Stand-alone products and product guides should now appear

### Option 2: Use the Products Page Fix Button

1. **Go to:** `admin/products.php`

2. **In the VUL & Traditional tab, click "Fix Categories" button**
   - This runs the same fix as the diagnostic tool
   - Check browser console for before/after comparison

3. **Switch to Stand-Alone Products and Product Guides tabs**
   - Products should now appear

### Option 3: Run SQL Directly (Advanced)

1. **Open phpMyAdmin**
   - Navigate to your database: `pru_life_db`

2. **Go to SQL tab**

3. **First, check what categories exist:**
   ```sql
   SELECT category, COUNT(*) as count 
   FROM products 
   GROUP BY category;
   ```

4. **Run the fix script:**
   ```sql
   -- Fix Stand-Alone Products
   UPDATE products 
   SET category = 'Stand-Alone Product' 
   WHERE LOWER(TRIM(category)) IN ('personal accident', 'stand-alone', 'stand alone');

   -- Fix Product Guides
   UPDATE products 
   SET category = 'Product Guides' 
   WHERE LOWER(TRIM(category)) = 'product guide';

   -- Fix Traditional
   UPDATE products 
   SET category = 'Traditional Life Insurance' 
   WHERE LOWER(TRIM(category)) = 'traditional';

   -- Remove whitespace
   UPDATE products 
   SET category = TRIM(category);
   ```

5. **Verify the fix:**
   ```sql
   SELECT category, COUNT(*) as count 
   FROM products 
   GROUP BY category;
   ```

   You should see exactly 4 categories:
   - VUL
   - Traditional Life Insurance
   - Stand-Alone Product
   - Product Guides

### Option 4: Use Debug Mode (For Analysis)

1. **Go to:** `admin/products.php`

2. **Click "Debug Mode: OFF"** to enable it

3. **Click "Refresh Debug Data"**
   - This loads detailed analysis
   - Shows all products with their exact categories
   - Highlights issues in yellow

4. **Review the "Category Mapping Issues" section**
   - Shows which products have incorrect categories
   - Provides recommendations

5. **Click "Fix Categories"** to auto-correct

## Valid Category Names

The system recognizes ONLY these exact category names (case-sensitive):

1. **VUL** - Variable Unit-Linked products
2. **Traditional Life Insurance** - Traditional life insurance products
3. **Stand-Alone Product** - Stand-alone products (accident, etc.)
4. **Product Guides** - Product guides and documentation

## Common Issues and Solutions

### Issue: Products show in database but not in tabs

**Cause:** Category name doesn't match exactly

**Solution:** Use any of the fix options above

### Issue: Debug mode shows "Unknown category"

**Cause:** Category name is not one of the 4 valid names

**Solution:** Run "Fix Categories" or update manually

### Issue: Category has extra spaces

**Cause:** Whitespace before/after category name

**Solution:** The fix script automatically trims whitespace

### Issue: Products are inactive

**Cause:** `is_active` field is set to 0

**Solution:** 
```sql
UPDATE products SET is_active = 1 WHERE id = [product_id];
```

## Verification Steps

After running any fix:

1. **Check the diagnostic page:**
   - Should show all 4 categories
   - No "unknown" categories

2. **Check the main products page:**
   - VUL & Traditional tab: Shows VUL and Traditional products
   - Stand-Alone Products tab: Shows stand-alone products
   - Product Guides tab: Shows product guides

3. **Check browser console:**
   - Should show "Products loaded in XXms"
   - No errors

4. **Test search:**
   - Search should work across all tabs

## Expected Results

After fixing:

### VUL & Traditional Tab
- PRULink Assurance Account
- PRULink Exact Protector
- PRULife Your Term
- PRUHealth
- PRULife Endowment

### Stand-Alone Products Tab
- PRUPersonal Accident
- (Any other stand-alone products)

### Product Guides Tab
- Product Placemat – Choosing the Pru Life UK Product Solution
- PRULink Product Specification Guide (PSG) – August 2025
- Accelerated Total and Permanent Disability (ATPD) – Benefits and Limitations

## Files Created for This Fix

1. **admin/fix-products-diagnostic.php** - Visual diagnostic tool
2. **api/products/check-database.php** - API to check database
3. **fix_product_categories.sql** - SQL script for manual fixes
4. **This guide** - Step-by-step instructions

## Troubleshooting

### If products still don't show after fix:

1. **Clear browser cache** (Ctrl+Shift+Delete)
2. **Hard refresh** (Ctrl+F5)
3. **Check if products are active:**
   ```sql
   SELECT id, product_name, category, is_active FROM products;
   ```
4. **Verify database connection** is working
5. **Check PHP error logs** for server-side issues

### If fix button doesn't work:

1. Use the diagnostic tool instead
2. Or run SQL directly in phpMyAdmin
3. Check browser console for JavaScript errors

## Prevention

To prevent this issue in the future:

1. **Always use exact category names** when adding products
2. **Use the dropdown** in the add/edit modal (don't type manually)
3. **Run diagnostic check** periodically
4. **Use the fix button** if you notice missing products

## Support

If issues persist after trying all options:
1. Check the diagnostic page for specific errors
2. Review browser console for JavaScript errors
3. Check PHP error logs for server errors
4. Verify database connection settings