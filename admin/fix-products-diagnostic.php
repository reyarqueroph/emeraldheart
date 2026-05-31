<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Diagnostic - Pru Life UK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #d50032; margin-bottom: 20px; }
        .btn-pru { background: #d50032; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .btn-pru:hover { background: #a00025; }
        .result-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; margin: 15px 0; }
        .category-vul { background: #e3f2fd; padding: 2px 8px; border-radius: 3px; }
        .category-traditional { background: #e8f5e9; padding: 2px 8px; border-radius: 3px; }
        .category-standalone { background: #fff3e0; padding: 2px 8px; border-radius: 3px; }
        .category-guides { background: #f3e5f5; padding: 2px 8px; border-radius: 3px; }
        .category-unknown { background: #ffebee; padding: 2px 8px; border-radius: 3px; color: #c62828; }
        table { width: 100%; margin-top: 15px; }
        th { background: #d50032; color: white; padding: 10px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        .status-active { color: green; }
        .status-inactive { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-tools"></i> Products Database Diagnostic</h1>
        <p>This tool checks what products are actually in the database and helps fix category issues.</p>
        
        <div style="margin: 20px 0;">
            <button class="btn-pru" onclick="checkDatabase()">
                <i class="fas fa-search"></i> Check Database
            </button>
            <button class="btn-pru" onclick="fixCategories()" style="background: #ff9800;">
                <i class="fas fa-wrench"></i> Fix Categories
            </button>
            <button class="btn-pru" onclick="location.href='products.php'" style="background: #6c757d;">
                <i class="fas fa-arrow-left"></i> Back to Products
            </button>
        </div>

        <div id="results"></div>
    </div>

    <script>
        async function checkDatabase() {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '<div class="result-box"><i class="fas fa-spinner fa-spin"></i> Checking database...</div>';
            
            try {
                const response = await fetch('../api/products/check-database.php');
                const data = await response.json();
                
                if (data.success) {
                    displayResults(data);
                } else {
                    resultsDiv.innerHTML = `<div class="result-box" style="background:#ffebee;"><strong>Error:</strong> ${data.message}</div>`;
                }
            } catch (error) {
                resultsDiv.innerHTML = `<div class="result-box" style="background:#ffebee;"><strong>Error:</strong> ${error.message}</div>`;
            }
        }

        function displayResults(data) {
            const resultsDiv = document.getElementById('results');
            
            let html = `
                <div class="result-box">
                    <h3>Database Summary</h3>
                    <p><strong>Total Products:</strong> ${data.total_products}</p>
                    <h4>Products by Category:</h4>
                    <ul>
            `;
            
            for (const [category, count] of Object.entries(data.category_counts)) {
                const categoryClass = getCategoryClass(category);
                html += `<li><span class="${categoryClass}">${category}</span>: ${count} product(s)</li>`;
            }
            
            html += `
                    </ul>
                </div>
                
                <div class="result-box">
                    <h3>All Products in Database</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Category Length</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.products.forEach(product => {
                const categoryClass = getCategoryClass(product.category_trimmed);
                const statusClass = product.is_active == 1 ? 'status-active' : 'status-inactive';
                const statusText = product.is_active == 1 ? 'Active' : 'Inactive';
                
                html += `
                    <tr>
                        <td>${product.id}</td>
                        <td>${escapeHtml(product.product_name)}</td>
                        <td><span class="${categoryClass}">"${escapeHtml(product.category)}"</span></td>
                        <td>${product.category_length}</td>
                        <td class="${statusClass}">${statusText}</td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            resultsDiv.innerHTML = html;
        }

        function getCategoryClass(category) {
            const cat = (category || '').trim();
            switch (cat) {
                case 'VUL': return 'category-vul';
                case 'Traditional Life Insurance': return 'category-traditional';
                case 'Stand-Alone Product': return 'category-standalone';
                case 'Product Guides': return 'category-guides';
                default: return 'category-unknown';
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        async function fixCategories() {
            if (!confirm('This will attempt to fix category names in the database. Continue?')) {
                return;
            }
            
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '<div class="result-box"><i class="fas fa-spinner fa-spin"></i> Fixing categories...</div>';
            
            try {
                const response = await fetch('../api/products/fix-categories.php');
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    checkDatabase(); // Reload to show results
                } else {
                    resultsDiv.innerHTML = `<div class="result-box" style="background:#ffebee;"><strong>Error:</strong> ${data.message}</div>`;
                }
            } catch (error) {
                resultsDiv.innerHTML = `<div class="result-box" style="background:#ffebee;"><strong>Error:</strong> ${error.message}</div>`;
            }
        }

        // Auto-check on page load
        window.onload = checkDatabase;
    </script>
</body>
</html>