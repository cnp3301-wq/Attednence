<?php
/**
 * Email Configuration Template
 * Copy this file to email_config.php and update with your settings
 */

// ========================================
// GMAIL CONFIGURATION (Recommended)
// ========================================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'cmp3301@gmail.com');      // Change this!
define('SMTP_PASSWORD', 'pqzt turx tdbv nhba');        // App Password (16 chars)
define('SMTP_FROM_EMAIL', 'noreply@kprcas.ac.in');
define('SMTP_FROM_NAME', 'KPRCAS Attendance System');

// How to get Gmail App Password:
// 1. Go to: https://myaccount.google.com/security
// 2. Enable 2-Step Verification
// 3. Go to "App passwords"
// 4. Select "Mail" and your device
// 5. Click "Generate"
// 6. Copy the 16-character password

// ========================================
// OUTLOOK/HOTMAIL CONFIGURATION
// ========================================
/*
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@outlook.com');     // Change this!
define('SMTP_PASSWORD', 'your-password');               // Regular password
define('SMTP_FROM_EMAIL', 'noreply@kprcas.ac.in');
define('SMTP_FROM_NAME', 'KPRCAS Attendance System');
*/

// ========================================
// YAHOO CONFIGURATION
// ========================================
/*
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@yahoo.com');       // Change this!
define('SMTP_PASSWORD', 'app-password');                // App Password
define('SMTP_FROM_EMAIL', 'noreply@kprcas.ac.in');
define('SMTP_FROM_NAME', 'KPRCAS Attendance System');
*/

// ========================================
// CUSTOM SMTP SERVER
// ========================================
/*
define('SMTP_HOST', 'mail.yourdomain.com');
define('SMTP_PORT', 587);                              // or 465 for SSL
define('SMTP_USERNAME', 'noreply@yourdomain.com');
define('SMTP_PASSWORD', 'your-password');
define('SMTP_FROM_EMAIL', 'noreply@kprcas.ac.in');
define('SMTP_FROM_NAME', 'KPRCAS Attendance System');
*/

// ========================================
// OTP SETTINGS
// ========================================
define('OTP_LENGTH', 6);                               // OTP code length
define('OTP_EXPIRY_MINUTES', 10);                      // OTP validity in minutes

// ========================================
// IMPORTANT NOTES
// ========================================
// 1. Never commit this file with real credentials to version control
// 2. Use App Password for Gmail (not your regular password)
// 3. Test email functionality using: login/test_email.php
// 4. Check spam folder if emails not received
// 5. For production, consider using SendGrid, AWS SES, or Mailgun

// ========================================
// TESTING CONFIGURATION
// ========================================
// For local testing without sending real emails, use:
// - Mailtrap: https://mailtrap.io/
// - MailHog: https://github.com/mailhog/MailHog
// - XAMPP Mercury Mail Server

/*
// Mailtrap Configuration (for testing)
define('SMTP_HOST', 'smtp.mailtrap.io');
define('SMTP_PORT', 2525);
define('SMTP_USERNAME', 'your-mailtrap-username');
define('SMTP_PASSWORD', 'your-mailtrap-password');
define('SMTP_FROM_EMAIL', 'noreply@kprcas.ac.in');
define('SMTP_FROM_NAME', 'KPRCAS Attendance System');
*/
?>
