#!/bin/bash
# Password Reset System - Setup Guide
# Run this to set up the password reset feature

echo "=========================================="
echo "Password Reset System - Setup Guide"
echo "=========================================="
echo ""

# Step 1: Database Setup
echo "[1/4] Setting up database table..."
echo ""
echo "Execute this SQL in your database (e_lite):"
echo ""
cat << 'EOF'
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    code VARCHAR(6) NOT NULL UNIQUE,
    token VARCHAR(128) DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_code (code),
    INDEX idx_expires_at (expires_at)
);
EOF
echo ""
echo "If using MySQL CLI: mysql -u root e_lite < migrations/001_create_password_resets_table.sql"
echo ""

# Step 2: Check file permissions
echo "[2/4] Checking file permissions..."
echo ""
echo "Making sure log directory is writable for email logs..."
touch password_resets.log 2>/dev/null && echo "✓ password_resets.log is writable" || echo "✗ password_resets.log not writable"
echo ""

# Step 3: Email Configuration
echo "[3/4] Email Configuration Setup..."
echo ""
echo "Edit config.php and set your email credentials:"
echo ""
cat << 'EOF'
Example for Gmail:
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your_email@gmail.com');
define('MAIL_PASSWORD', 'your_app_password');  // Not your Gmail password!
define('MAIL_FROM', 'your_email@gmail.com');
define('MAIL_FROM_NAME', 'e-lite Security');

To get Gmail App Password:
1. Enable 2-Factor Authentication
2. Go to https://myaccount.google.com/apppasswords
3. Select "Mail" and "Windows Computer" (or your device)
4. Copy the generated password and use it above

For Testing Without Email:
Leave MAIL_PASSWORD empty to log codes to password_resets.log
EOF
echo ""

# Step 4: Verify routes
echo "[4/4] Verifying routes..."
echo ""
echo "The following routes should be available:"
echo "  - GET/POST  /forgot           → Forgot password form"
echo "  - GET/POST  /verify-code      → Code verification form"
echo "  - GET/POST  /reset-password   → New password form"
echo "  - GET       /reset-success    → Success page"
echo ""

echo "=========================================="
echo "Setup Complete!"
echo "=========================================="
echo ""
echo "Next Steps:"
echo "1. Run the SQL migration to create password_resets table"
echo "2. Configure email settings in config.php"
echo "3. Test the flow: /forgot → /verify-code → /reset-password → /reset-success"
echo ""
echo "For testing without email:"
echo "  - Leave MAIL_PASSWORD empty in config.php"
echo "  - Codes will be logged to password_resets.log"
echo "  - Extract the 6-digit code from the log file"
echo ""
