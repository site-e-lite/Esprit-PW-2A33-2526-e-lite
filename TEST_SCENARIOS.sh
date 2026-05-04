#!/bin/bash
# Password Reset System - Test Scenarios
# Use these test cases to verify the implementation

cat << 'EOF'
╔═══════════════════════════════════════════════════════════════════════════╗
║                   PASSWORD RESET SYSTEM - TEST SCENARIOS                 ║
╚═══════════════════════════════════════════════════════════════════════════╝

PREREQUISITE:
=============
- Create password_resets table in database
- Have a test user account with known email
- Configure email or use logging (MAIL_PASSWORD empty)


TEST 1: Happy Path - Complete Reset
===================================
Steps:
  1. Navigate to http://localhost:8000/forgot
  2. Enter a valid user email address
  3. System should show: "Un code de vérification a été envoyé"
  4. Check password_resets.log or email for 6-digit code
  5. Navigate to http://localhost:8000/verify-code
  6. Enter the 6-digit code
  7. Should redirect to /reset-password
  8. Enter new password: "NewSecure123"
  9. Confirm password: "NewSecure123"
  10. Click "Réinitialiser le mot de passe"
  11. Should show success page
  12. Should be able to login with new password

Expected Results:
  ✓ Code received within seconds
  ✓ Code verified successfully
  ✓ Password updated in database
  ✓ Can login with new password


TEST 2: Invalid Code - Wrong Digits
====================================
Steps:
  1. Go to /forgot, enter email, get code (don't use it)
  2. Go to /verify-code
  3. Enter wrong code: "000000" (when code is different)
  4. Should show error: "Code invalide ou expiré"
  5. Repeat 4 more times
  6. On 5th attempt, should show: "Trop de tentatives"

Expected Results:
  ✓ Error message appears on invalid code
  ✓ Counter shows remaining attempts (5, 4, 3, 2, 1)
  ✓ After 5 failures, prevented from further attempts
  ✓ Must restart from /forgot


TEST 3: Expired Code
====================
Steps:
  1. Go to /forgot, enter email, get code
  2. Wait 15 minutes
  3. Go to /verify-code, enter code
  4. Should show error: "Code invalide ou expiré"

Expected Results:
  ✓ Code expires after exactly 15 minutes
  ✓ Database shows expires_at time is in past


TEST 4: Code Reuse Prevention
=============================
Steps:
  1. Go through complete password reset (TEST 1)
  2. Successfully change password
  3. Go to /forgot with same email
  4. Try to use the OLD code from before
  5. Should reject with error

Expected Results:
  ✓ First use: code works, password changed, code marked as used=1
  ✓ Second use: code fails, shows "Code invalide ou expiré"


TEST 5: Password Validation - Too Short
========================================
Steps:
  1. Complete code verification
  2. On /reset-password, enter password: "Short1"
  3. Confirm: "Short1"
  4. "Submit" button should be DISABLED
  5. Live feedback should show "Au moins 8 caractères" as ✗

Expected Results:
  ✓ Submit button disabled (grayed out)
  ✓ Requirement indicator shows red X
  ✓ Cannot submit form


TEST 6: Password Validation - Missing Uppercase
================================================
Steps:
  1. Complete code verification
  2. Enter password: "nouppercase123"
  3. Confirm: "nouppercase123"
  4. Submit button should be DISABLED
  5. Feedback shows "Au moins une lettre majuscule" as ✗

Expected Results:
  ✓ Submit button disabled
  ✓ Cannot proceed until uppercase added


TEST 7: Password Validation - Missing Lowercase
================================================
Steps:
  1. Complete code verification
  2. Enter password: "NOLOWERCASE123"
  3. Confirm: "NOLOWERCASE123"
  4. Submit button should be DISABLED
  5. Feedback shows "Au moins une lettre minuscule" as ✗

Expected Results:
  ✓ Submit button disabled
  ✓ Cannot proceed until lowercase added


TEST 8: Password Validation - Missing Digit
============================================
Steps:
  1. Complete code verification
  2. Enter password: "NoDigitHere"
  3. Confirm: "NoDigitHere"
  4. Submit button should be DISABLED
  5. Feedback shows "Au moins un chiffre" as ✗

Expected Results:
  ✓ Submit button disabled
  ✓ Cannot proceed until digit added


TEST 9: Password Validation - Mismatch
=======================================
Steps:
  1. Complete code verification
  2. Enter password: "Correct123"
  3. Confirm password: "Different456"
  4. Submit button should be DISABLED
  5. Feedback shows "Les mots de passe correspondent" as ✗

Expected Results:
  ✓ Submit button disabled
  ✓ Real-time feedback updates as user types
  ✓ Button enables when passwords match


TEST 10: Password Validation - Valid Password
==============================================
Steps:
  1. Complete code verification
  2. Enter password: "SecurePass123"
  3. Confirm: "SecurePass123"
  4. All requirements should show ✓ (green)
  5. Submit button should be ENABLED
  6. Click submit
  7. Should show success page

Expected Results:
  ✓ All 5 requirements met (length, lowercase, uppercase, digit, match)
  ✓ Submit button enabled (clickable)
  ✓ Password successfully stored
  ✓ Old password no longer works


TEST 11: Unregistered Email
============================
Steps:
  1. Go to /forgot
  2. Enter non-existent email: "notarealuser@example.com"
  3. Should show generic message (security)

Expected Results:
  ✓ System doesn't reveal if email is registered or not
  ✓ No actual code sent
  ✓ User can proceed to /verify-code (will fail there)


TEST 12: Session Verification
==============================
Steps:
  1. Go to /forgot, get code
  2. Don't verify code, try to manually navigate to /reset-password
  3. Should redirect back to /forgot

Expected Results:
  ✓ Cannot skip code verification step
  ✓ Session checks prevent unauthorized password changes


TEST 13: Multiple Users
=======================
Steps:
  1. Use USER A: Get code through /forgot
  2. Use USER B: Get code through /forgot (different code)
  3. Verify CODE A works for USER A
  4. Verify CODE B doesn't work for USER A
  5. Verify CODE B works for USER B

Expected Results:
  ✓ Each user gets unique code
  ✓ Codes are not interchangeable
  ✓ Each code is tied to specific email


TEST 14: Database Verification
===============================
Steps:
  1. After generating code, check password_resets table:
     SELECT * FROM password_resets WHERE email = 'test@example.com';
  2. Verify columns:
     - email: matches submitted email
     - code: 6 digits
     - expires_at: ~15 minutes in future
     - used: 0 (unused)
  3. After successful reset:
     - Check same code now has used: 1
     - Check user.motDePasse was updated (hash changed)

Expected Results:
  ✓ Table properly structured
  ✓ Code format correct (6 digits)
  ✓ Expiration time logical (15 min ahead)
  ✓ Single-use enforcement via used flag


PERFORMANCE TESTS
=================

TEST 15: Log File Check
=======================
Steps:
  1. With MAIL_PASSWORD empty, generate 5 codes
  2. Check password_resets.log file exists
  3. Verify log contains:
     - [YYYY-MM-DD HH:MM:SS] timestamp
     - Email address
     - 6-digit code
     - "Valid for 15 minutes" note

Expected Results:
  ✓ Log file created automatically
  ✓ Entries properly timestamped
  ✓ Code visible in log for testing


CLEANUP TASKS
=============

After testing:

1. Clear test codes from database:
   DELETE FROM password_resets WHERE expires_at < NOW();

2. Reset test user password:
   UPDATE user SET motDePasse = PASSWORD_HASH('TestPass123', PASSWORD_DEFAULT) 
   WHERE email = 'test@example.com';

3. Archive test logs:
   mv password_resets.log password_resets.log.backup

EOF
