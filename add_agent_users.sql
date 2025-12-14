-- Add Agent Users to HomeLink System
-- Real Estate Agent Accounts

USE homelink;

-- Insert agent users
INSERT INTO users (username, email, password, role, full_name, phone) VALUES
('johnagent', 'john@realestate.co.zm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agent', 'John Banda', '+260977123456'),
('maryagent', 'mary@properties.co.zm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agent', 'Mary Mulenga', '+260976654321'),
('davidagent', 'david@homes.co.zm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agent', 'David Phiri', '+260975987654');

-- Note: Password is 'password' for all agent accounts (hashed above)
