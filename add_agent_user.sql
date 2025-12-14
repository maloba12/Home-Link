-- Add agent role user for testing
-- Password: password (hashed with bcrypt)

INSERT INTO users (username, email, password, role, created_at) 
VALUES (
    'agentjohn',
    'agent@homelink.zm',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'agent',
    NOW()
);

-- Note: The password hash above is for 'password'
-- To create a new hash, use: password_hash('your_password', PASSWORD_DEFAULT) in PHP
