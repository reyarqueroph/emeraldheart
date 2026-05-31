# Debug Mode Guide for Products Management

## Overview
The debug mode has been added to the admin/products.php page to help identify and resolve issues with products and product guides that are in the database but not showing up correctly in the system.

## How to Access Debug Mode

1. **Login as Admin**: Navigate to the admin panel and log in with admin credentials
2. **Go to Products Page**: Click on "Manage Products" in the admin sidebar
3. **Enable Debug Mode**: Click the "Debug Mode: OFF" button at the top of the page, or use the "Debug" button in the VUL & Traditional tab toolbar

## Debug Features

### 1. Database Statistics
Shows a real-time count of:
- Total products in database
- Products by category (VUL, Traditional, Stand-Alone, Product Guides)
- Unknown/miscategorized products
- Products with and without PDF files

### 2. Issue Detection
Automatically identifies:
- **Unknown Categories**: Products with invalid or non-standard category names
- **Whitespace Issues**: Categories with leading/trailing spaces
- **Missing PDFs**: Products without attached documentation
- **Empty Fields**: Products with missing required information

### 3. Raw Database View
Displays all products in a detailed table showing:
- Product ID and name
- Raw category data (exactly as stored in database)
- Category length and trimmed version
- Sub-category information
- Active/inactive status
- PDF attachment status

### 4. Smart Recommendations
Provides actionable suggestions such as:
- Running the "Fix Categories" function for known category variations
- Uploading missing PDF files
- Addressing specific data quality issues

## Common Issues and Solutions

### Issue: Products Not Showing in Correct Tabs
**Symptoms**: Products exist in database but don't appear in Stand-Alone or Product Guides tabs
**Solution**: 
1. Enable debug mode
2. Check the "Category Mapping Issues" section
3. Run "Fix Categories" to automatically correct known variations
4. Manually update categories for unknown variations

### Issue: Stand-Alone Products Missing
**Symptoms**: Stand-alone products like "PRUPersonal Accident" don't appear
**Cause**: Category might be stored as "personal accident" instead of "Stand-Alone Product"
**Solution**: Debug mode will detect this and "Fix Categories" will correct it

### Issue: Product Guides Not Visible
**Symptoms**: Product guides exist but don't show in the Product Guides tab
**Cause**: Category stored as "product guide" instead of "Product Guides"
**Solution**: Use debug mode to identify and fix category inconsistencies

## Debug Tools

### 1. Refresh Debug Data
- Updates all debug information with latest database state
- Useful after making changes or running fixes

### 2. Export Debug Data
- Downloads a CSV file with complete product information
- Useful for external analysis or backup purposes
- Includes all fields and metadata

### 3. Fix Categories
- Automatically maps known category variations to standard names
- Shows before/after comparison in browser console
- Safe to run multiple times

## Technical Details

### Valid Categories
The system recognizes these exact category names:
- `VUL`
- `Traditional Life Insurance`
- `Stand-Alone Product`
- `Product Guides`

### Category Mapping
Debug mode automatically suggests mappings for common variations:
- "personal accident" → "Stand-Alone Product"
- "stand-alone" → "Stand-Alone Product"
- "product guide" → "Product Guides"
- "traditional" → "Traditional Life Insurance"

### API Endpoints
Debug mode uses these endpoints:
- `/api/products/debug-enhanced.php` - Comprehensive analysis
- `/api/products/debug-categories.php` - Basic category information
- `/api/products/fix-categories.php` - Automatic category correction

## Best Practices

1. **Enable Debug Mode First**: Always check debug mode when investigating missing products
2. **Review Issues Section**: Look for specific problems before making changes
3. **Use Fix Categories**: Try automatic fixes before manual corrections
4. **Export Data**: Keep backups when making bulk changes
5. **Check Console**: Browser console shows detailed before/after information

## Troubleshooting

### Debug Mode Won't Load
- Check browser console for JavaScript errors
- Verify admin permissions
- Ensure database connection is working

### Fix Categories Doesn't Work
- Check that you have admin role
- Look for PHP errors in server logs
- Verify database write permissions

### Products Still Not Showing
- Check if products are marked as inactive (`is_active = 0`)
- Verify category names match exactly (case-sensitive)
- Look for special characters or encoding issues

## Security Notes

- Debug mode is only available to admin users
- All debug functions require admin authentication
- Exported data should be handled securely
- Debug mode shows sensitive database information

## Support

If debug mode reveals issues that can't be resolved automatically:
1. Export the debug data for analysis
2. Check the recommendations section for guidance
3. Review the raw database view for data inconsistencies
4. Contact system administrator with specific error details