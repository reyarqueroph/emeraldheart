-- Enhanced AI Chatbot Database Schema

-- 1. Add PDF content storage to products table
ALTER TABLE products 
ADD COLUMN IF NOT EXISTS primer_content LONGTEXT AFTER primer_file,
ADD COLUMN IF NOT EXISTS primer_extracted_at DATETIME AFTER primer_content,
ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1 AFTER primer_extracted_at;

-- 2. Create product features table for structured content
CREATE TABLE IF NOT EXISTS product_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    feature_type ENUM('benefit', 'coverage', 'exclusion', 'requirement', 'feature', 'highlight') NOT NULL,
    feature_text TEXT NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id),
    INDEX idx_feature_type (feature_type),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create chatbot conversation history
CREATE TABLE IF NOT EXISTS chatbot_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(64) NOT NULL,
    message TEXT NOT NULL,
    response TEXT NOT NULL,
    context_data JSON,
    products_recommended JSON,
    intent VARCHAR(50),
    confidence_score DECIMAL(3,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_created_at (created_at),
    INDEX idx_intent (intent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Create product keywords table for better matching
CREATE TABLE IF NOT EXISTS product_keywords (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    keyword VARCHAR(100) NOT NULL,
    keyword_type ENUM('goal', 'feature', 'benefit', 'category', 'general') NOT NULL,
    weight INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id),
    INDEX idx_keyword (keyword),
    INDEX idx_keyword_type (keyword_type),
    UNIQUE KEY unique_product_keyword (product_id, keyword)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Insert sample product features (you can customize these)
-- Example for VUL products
INSERT INTO product_features (product_id, feature_type, feature_text, display_order) 
SELECT id, 'benefit', 'Life insurance protection with investment component', 1
FROM products WHERE category = 'VUL' AND NOT EXISTS (
    SELECT 1 FROM product_features WHERE product_id = products.id
) LIMIT 1;

INSERT INTO product_features (product_id, feature_type, feature_text, display_order)
SELECT id, 'benefit', 'Flexible premium payments', 2
FROM products WHERE category = 'VUL' AND NOT EXISTS (
    SELECT 1 FROM product_features WHERE product_id = products.id AND feature_text = 'Flexible premium payments'
) LIMIT 1;

INSERT INTO product_features (product_id, feature_type, feature_text, display_order)
SELECT id, 'benefit', 'Market-linked investment returns', 3
FROM products WHERE category = 'VUL' LIMIT 1;

-- Example for Traditional Life Insurance
INSERT INTO product_features (product_id, feature_type, feature_text, display_order)
SELECT id, 'benefit', 'Guaranteed life insurance coverage', 1
FROM products WHERE category = 'Traditional Life Insurance' LIMIT 1;

INSERT INTO product_features (product_id, feature_type, feature_text, display_order)
SELECT id, 'benefit', 'Fixed premium payments', 2
FROM products WHERE category = 'Traditional Life Insurance' LIMIT 1;

INSERT INTO product_features (product_id, feature_type, feature_text, display_order)
SELECT id, 'benefit', 'Guaranteed cash value accumulation', 3
FROM products WHERE category = 'Traditional Life Insurance' LIMIT 1;

-- 6. Insert sample keywords for better matching
-- VUL keywords
INSERT IGNORE INTO product_keywords (product_id, keyword, keyword_type, weight)
SELECT id, 'investment', 'goal', 3 FROM products WHERE category = 'VUL';

INSERT IGNORE INTO product_keywords (product_id, keyword, keyword_type, weight)
SELECT id, 'wealth', 'goal', 3 FROM products WHERE category = 'VUL';

INSERT IGNORE INTO product_keywords (product_id, keyword, keyword_type, weight)
SELECT id, 'savings', 'goal', 2 FROM products WHERE category = 'VUL';

INSERT IGNORE INTO product_keywords (product_id, keyword, keyword_type, weight)
SELECT id, 'growth', 'feature', 2 FROM products WHERE category = 'VUL';

-- Traditional keywords
INSERT IGNORE INTO product_keywords (product_id, keyword, keyword_type, weight)
SELECT id, 'protection', 'goal', 3 FROM products WHERE category = 'Traditional Life Insurance';

INSERT IGNORE INTO product_keywords (product_id, keyword, keyword_type, weight)
SELECT id, 'security', 'goal', 2 FROM products WHERE category = 'Traditional Life Insurance';

INSERT IGNORE INTO product_keywords (product_id, keyword, keyword_type, weight)
SELECT id, 'guaranteed', 'feature', 2 FROM products WHERE category = 'Traditional Life Insurance';

-- Stand-Alone keywords
INSERT IGNORE INTO product_keywords (product_id, keyword, keyword_type, weight)
SELECT id, 'accident', 'goal', 3 FROM products WHERE category = 'Stand-Alone Product';

INSERT IGNORE INTO product_keywords (product_id, keyword, keyword_type, weight)
SELECT id, 'health', 'goal', 3 FROM products WHERE category = 'Stand-Alone Product';

-- Verify the schema
SELECT 'Products table updated' AS status;
SELECT 'Product features table created' AS status;
SELECT 'Chatbot conversations table created' AS status;
SELECT 'Product keywords table created' AS status;
SELECT 'Sample data inserted' AS status;
