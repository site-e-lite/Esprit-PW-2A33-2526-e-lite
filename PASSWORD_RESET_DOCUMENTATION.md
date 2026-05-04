# Password Reset System - Complete Implementation

## Overview
This implementation provides a secure, multi-step password reset workflow for the e-lite application. The system follows security best practices including:

- **Verification Codes**: 6-digit codes that expire after 15 minutes
- **Single-use Codes**: Each code can only be used once
- **Brute Force Protection**: Maximum 5 verification attempts
- **Strong Password Requirements**: 
  - Minimum 8 characters
  - At least one uppercase letter
  - At least one lowercase letter
  - At least one digit
- **Database Tracking**: All reset requests are logged with expiration timestamps

## Workflow

### Step 1: Forgot Password (/forgot)
**File**: `View/auth/forgot.php`
- User enters their email address
- System checks if email exists (security: doesn't reveal if email is registered)
- Generates 6-digit verification code
- Sends code via email (logs to `password_resets.log` if no email configured)
- Redirects to code verification page

### Step 2: Code Verification (/verify-code)
**File**: `View/auth/verify-code.php`
- User enters 6-digit code received by email
- Code validated against database with expiration check
- Brute force protection: max 5 attempts
- If valid: marks as verified and redirects to password reset
- If invalid: shows error message "Code invalide ou expiré"

### Step 3: Reset Password (/reset-password)
**File**: `View/auth/reset-password.php`
- User enters new password and confirmation
- Real-time validation shows:
  - ✓ Minimum 8 characters
  - ✓ Uppercase letter (A-Z)
  - ✓ Lowercase letter (a-z)
  - ✓ Digit (0-9)
  - ✓ Passwords match
- Submit button enabled only when all requirements met
- On success: updates password hash, marks code as used, redirects to success page

### Step 4: Success (/reset-success)
**File**: `View/auth/reset-success.php`
- Displays success message
- Shows security tips
- Auto-redirects to login after 5 seconds
- User can manually click "Go to Login" button

## Security Features

### 1. Database Table
```sql
CREATE TABLE password_resets (
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
```

### 2. Code Expiration
- Codes expire after 15 minutes (900 seconds)
- Automatic cleanup via `User::cleanupExpiredCodes()`
- Database query includes: `WHERE expires_at > NOW()`

### 3. Single-Use Enforcement
- Code marked as `used = 1` after successful password reset
- Verification checks: `WHERE used = 0`
- Cannot reuse the same code

### 4. Brute Force Protection
- Maximum 5 verification attempts
- Counter reset when user restarts from forgot password
- Displays remaining attempts to user

### 5. Strong Password Requirements
**Client-side** (visual feedback):
- Real-time validation while typing
- Shows which requirements are met
- Submit button disabled until all met

**Server-side** (enforced):
- Minimum 8 characters
- At least one lowercase: `/[a-z]/`
- At least one uppercase: `/[A-Z]/`
- At least one digit: `/[0-9]/`
- Passwords match

### 6. Session Management
- Code verification stored in session
- Session cleared after successful reset
- Prevents unauthorized password changes
- Code can only be used if session verification passes

## API Reference

### User Model Methods

#### `User::generateResetCode(string $email): string|false`
Generates and stores a new 6-digit code for email.
- Invalidates previous codes for that email
- Expires after 15 minutes
- Returns the generated code or false

#### `User::verifyResetCode(string $email, string $code): bool`
Verifies if code is valid and not expired.
- Checks email, code, expiration, and used status
- Returns true if valid, false otherwise

#### `User::updatePasswordAfterReset(string $email, string $code, string $newPassword): bool`
Updates password after code verification.
- Verifies code validity
- Updates password hash
- Marks code as used (single-use enforcement)
- Uses transaction for atomicity
- Returns true on success

#### `User::cleanupExpiredCodes(): bool`
Removes expired codes from database.
- Can be called periodically (e.g., via cron)
- Deletes codes where `expires_at < NOW()`

### Controller Methods

#### `UserController::forgotPassword()`
Handles GET/POST for `/forgot` route
- Validates email format
- Generates and emails code
- Redirects to verification page

#### `UserController::verifyCode()`
Handles GET/POST for `/verify-code` route
- Validates 6-digit format
- Checks brute force attempts
- Verifies code against database
- Sets session flag for next step

#### `UserController::resetPassword()`
Handles GET/POST for `/reset-password` route
- Enforces session verification
- Validates password requirements
- Updates password in database
- Clears session data

#### `UserController::resetSuccess()`
Displays success page with auto-redirect

### Mailer Methods

#### `Mailer::sendPasswordResetCode($email, $code)`
Sends password reset code email.
- Falls back to file logging if no SMTP configured
- Sends to `password_resets.log` for testing
- Includes 15-minute expiration notice
- HTML formatted with security tips

## Testing the System

### Setup
1. Create password_resets table using SQL migration
2. Configure email (or use log file for testing)
3. Ensure database connection configured in `config.php`

### Test Scenarios

**1. Valid Password Reset**
```
1. Go to /forgot
2. Enter registered user email
3. Check email or password_resets.log for code
4. Go to /verify-code, enter code
5. Go to /reset-password, enter new password meeting requirements
6. Verify success page displays
7. Login with new password should work
```

**2. Invalid Code**
```
1. Go to /forgot → Enter email
2. Go to /verify-code → Enter wrong code
3. Error: "Code invalide ou expiré"
4. Repeat until 5 attempts reached
5. Verify: "Trop de tentatives" message
```

**3. Expired Code**
```
1. Go to /forgot → Generate code
2. Wait 15+ minutes
3. Go to /verify-code → Enter code
4. Error: "Code invalide ou expiré"
```

**4. Code Reuse Prevention**
```
1. Go through full reset with code
2. Try using same code again
3. Should fail (marked as used)
```

**5. Weak Password**
```
1. Complete code verification
2. Try password: "weak"
3. Error: "Le mot de passe doit contenir au moins 8 caractères"
4. Try: "Weak1234" (no uppercase/lowercase/digit detected properly)
5. Eventually try: "StrongPass123"
6. Should succeed
```

## File Locations

### Database
- Migration: `/migrations/001_create_password_resets_table.sql`

### Models
- `Model/User.php` - Password reset methods
- `Model/Mailer.php` - Email sending

### Controllers
- `Controller/UserController.php` - All workflow methods

### Views
- `View/auth/forgot.php` - Email entry form
- `View/auth/verify-code.php` - Code verification form
- `View/auth/reset-password.php` - Password reset form
- `View/auth/reset-success.php` - Success page

### Logs (when no email configured)
- `password_resets.log` - Password reset codes for testing
- `login_alerts.log` - Login alerts (existing)

## Configuration

### Email Configuration (`config.php`)
```php
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your_email@gmail.com');
define('MAIL_PASSWORD', '');  // Empty = logging only
define('MAIL_FROM', 'your_email@gmail.com');
define('MAIL_FROM_NAME', 'e-lite Security');
```

When `MAIL_PASSWORD` is empty, codes are logged to `password_resets.log` for testing.

## Environment Variables

### For Gmail (recommended)
1. Enable 2-Factor Authentication in Google Account
2. Generate App Password: https://myaccount.google.com/apppasswords
3. Use App Password in `MAIL_PASSWORD`

### For Other SMTP Providers
Update `MAIL_HOST` and `MAIL_PORT` accordingly

## Security Best Practices Implemented

✓ Codes expire after 15 minutes (not permanent)
✓ Single-use codes (cannot be reused)
✓ Brute force protection (max 5 attempts)
✓ Strong password requirements enforced server-side
✓ Session-based verification (prevents bypassing steps)
✓ Passwords hashed with PHP's PASSWORD_DEFAULT
✓ SQL injection protected (prepared statements)
✓ XSS protected (htmlspecialchars, filter_var)
✓ CSRF tokens recommended (not in scope)
✓ Automatic cleanup of expired codes possible
✓ Database indexes for performance

## Future Enhancements

- [ ] CSRF token validation
- [ ] Rate limiting per IP address
- [ ] Password history (prevent reusing old passwords)
- [ ] SMS code delivery option
- [ ] Two-factor authentication integration
- [ ] Email verification step before reset
- [ ] Reset password notifications to registered email
- [ ] Admin ability to revoke password resets
