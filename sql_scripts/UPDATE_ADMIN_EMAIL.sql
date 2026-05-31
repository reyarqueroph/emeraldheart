-- Update admin email addresses to reyarqueroofficial25@gmail.com

-- Update eheart_admin email
UPDATE users 
SET email = 'reyarqueroofficial25@gmail.com' 
WHERE username = 'eheart_admin' AND role = 'admin';

-- Update admin email
UPDATE users 
SET email = 'reyarqueroofficial25@gmail.com' 
WHERE username = 'admin' AND role = 'admin';

-- Verify the updates
SELECT id, username, email, role 
FROM users 
WHERE role = 'admin';
