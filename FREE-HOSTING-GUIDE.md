# 🆓 Free Hosting Guide - KPRCAS Attendance System

## 📋 Best Free Hosting Options

### ✅ Recommended: InfinityFree (Best Free Option)
- ✅ **Unlimited storage & bandwidth**
- ✅ **PHP 8.x support**
- ✅ **MySQL databases**
- ✅ **cPanel access**
- ✅ **No ads**
- ✅ **Free subdomain or custom domain**
- ❌ Daily hits limit (50,000 - sufficient for testing)

### Alternative Options:
- **000WebHost** - Easy but has ads
- **AwardSpace** - Limited features
- **FreeHosting.com** - Basic features

---

## 🚀 Step-by-Step: Deploy from GitHub to InfinityFree

### Step 1: Sign Up for InfinityFree

1. Go to [infinityfree.net](https://infinityfree.net)
2. Click **"Sign Up"**
3. Fill in your details:
   - Email address
   - Password
4. Verify your email
5. Login to iFastNet Client Area

---

### Step 2: Create Free Hosting Account

1. Click **"Create Account"** button
2. Choose a domain option:
   - **Option A:** Use free subdomain (e.g., `kprcas.wuaze.com`)
   - **Option B:** Use your own domain (if you have one)
3. Enter your desired subdomain name: `kprcas` or `attendance`
4. Click **"Create Account"**
5. Wait 2-5 minutes for account activation
6. Note down your credentials:
   ```
   Website URL: http://kprcas.wuaze.com
   Control Panel: https://cpanel.infinityfree.net
   FTP Hostname: ftpupload.net
   FTP Username: epiz_XXXXXXXX
   FTP Password: [your password]
   ```

---

### Step 3: Access Control Panel (cPanel)

1. Go to **Control Panel** from your client area
2. Or visit: `https://cpanel.infinityfree.net`
3. Login with your credentials

---

### Step 4: Create MySQL Database

1. In cPanel, find **"MySQL Databases"**
2. **Create New Database:**
   - Database Name: `kprcas_attendance`
   - Click "Create Database"
   - Note the full database name: `epiz_XXXXX_kprcas_attendance`

3. **Create Database User:**
   - Username: `kprcas_admin`
   - Password: Generate strong password
   - Click "Create User"
   - Note: `epiz_XXXXX_kprcas_admin`

4. **Assign User to Database:**
   - Select user: `epiz_XXXXX_kprcas_admin`
   - Select database: `epiz_XXXXX_kprcas_attendance`
   - Grant **ALL PRIVILEGES**
   - Click "Add"

5. **Note these credentials** (you'll need them):
   ```
   DB Host: sql305.infinityfree.com (or as shown)
   DB Name: epiz_XXXXX_kprcas_attendance
   DB User: epiz_XXXXX_kprcas_admin
   DB Pass: [your password]
   ```

---

### Step 5: Download Your Project from GitHub

**Option A: Download ZIP from GitHub**
1. Go to your repository: `https://github.com/cnp3301-wq/Attednence`
2. Click green **"Code"** button
3. Click **"Download ZIP"**
4. Extract the ZIP file to a folder

**Option B: Clone via Git (if you want to work on it)**
```powershell
cd "e:\KPRCAS\Free-Hosting"
git clone https://github.com/cnp3301-wq/Attednence.git
cd Attednence
```

---

### Step 6: Update Configuration Files

Before uploading, update your database configuration:

**1. Edit `login/config/database.php`:**
```php
<?php
// Database configuration for InfinityFree
define('DB_HOST', 'sql305.infinityfree.com'); // Check your cPanel for exact host
define('DB_USER', 'epiz_XXXXX_kprcas_admin'); // Your actual username
define('DB_PASS', 'your_database_password');
define('DB_NAME', 'epiz_XXXXX_kprcas_attendance'); // Your actual database name

// Create database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}
?>
```

**2. Update Email Configuration (if needed):**
Edit `login/config/email_config.php` with your Gmail credentials.

**Important:** InfinityFree blocks port 587 for SMTP. You need to:
- Use port 465 (SSL) instead of 587 (TLS)
- Or use their SMTP server (check documentation)
- Or keep Gmail but test thoroughly

Update in `email_config.php`:
```php
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use 'ssl'
$mail->Port       = 465; // Change from 587 to 465
```

---

### Step 7: Upload Files to Server

**Option A: Using File Manager (Easiest)**

1. In cPanel, open **"Online File Manager"**
2. Navigate to **`htdocs`** folder (this is your web root)
3. Delete default files (index.html, etc.)
4. Click **"Upload"** button
5. Select all your project files
6. Wait for upload to complete
7. If you uploaded a ZIP:
   - Right-click the ZIP file
   - Select **"Extract"**
   - Delete the ZIP after extraction

**Option B: Using FTP (FileZilla)**

1. Download FileZilla: [filezilla-project.org](https://filezilla-project.org)
2. Install and open FileZilla
3. Enter connection details:
   - **Host:** `ftpupload.net`
   - **Username:** `epiz_XXXXXXXX` (from cPanel)
   - **Password:** Your FTP password
   - **Port:** 21
4. Click **"Quickconnect"**
5. Navigate to `/htdocs` folder on remote side
6. Drag and drop all your project files
7. Wait for upload to complete

**File Structure Should Look Like:**
```
htdocs/
├── admin/
├── assets/
├── login/
├── student/
├── teacher/
├── vendor/
├── schema/
├── .gitignore
├── README.md
├── composer.json
└── deployment-guide.md
```

---

### Step 8: Import Database

1. In cPanel, open **"phpMyAdmin"**
2. Click on your database: `epiz_XXXXX_kprcas_attendance`
3. Click **"Import"** tab
4. Click **"Choose File"**
5. Select `schema/database_schema_complete.sql` from your computer
6. Scroll down and click **"Go"**
7. Wait for import to complete (you should see green success message)
8. Verify tables were created:
   - Click on database name in left sidebar
   - You should see 11 tables:
     * users
     * classes
     * students
     * subjects
     * teacher_subjects
     * attendance_sessions
     * attendance
     * otp_verification
     * qr_email_logs
     * login_logs
     * system_settings

---

### Step 9: Install Composer Dependencies

**InfinityFree doesn't have SSH access**, so you have two options:

**Option A: Upload vendor folder**
If you already have the `vendor` folder locally:
1. Upload the entire `vendor` folder via FTP or File Manager
2. This includes PHPMailer and all dependencies

**Option B: Install locally and upload**
```powershell
# On your local machine
cd "e:\KPRCAS\New folder\attendance"
composer install
# Then upload the generated vendor folder
```

---

### Step 10: Test Your Website

1. **Visit your website:**
   - `http://kprcas.wuaze.com/login/login.php`
   - Or whatever your subdomain is

2. **Test Admin Login:**
   - Email: `admin@kprcas.ac.in`
   - Password: `admin123`

3. **If you see errors:**
   - Check database connection
   - Verify database credentials in `database.php`
   - Check phpMyAdmin to ensure tables exist
   - Enable error reporting temporarily

4. **Test Teacher Login:**
   - Email: `rajesh.kumar@kprcas.ac.in`
   - Password: `Rajesh1234`

---

### Step 11: Enable Error Logging (For Debugging)

If something doesn't work, enable error logging:

Create a `.user.ini` file in your `htdocs` folder:
```ini
display_errors = On
error_reporting = E_ALL
log_errors = On
error_log = error.log
```

Or add to the top of `login/login.php`:
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// ... rest of code
?>
```

**Remember to disable this in production!**

---

### Step 12: Security Hardening

1. **Change Default Passwords Immediately:**
   - Login as admin
   - Change password from `admin123`
   - Update teacher passwords

2. **Create `.htaccess` for Security:**

Create/edit `.htaccess` in `htdocs` folder:
```apache
# Disable directory browsing
Options -Indexes

# Prevent access to sensitive files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect config files
<FilesMatch "(database\.php|email_config\.php)">
    Order allow,deny
    Deny from all
</FilesMatch>

# Enable error pages
ErrorDocument 404 /404.html
ErrorDocument 500 /500.html

# Force HTTPS (if SSL is available)
# RewriteEngine On
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

3. **Restrict Access to Admin Panel:**

Add to `admin/.htaccess`:
```apache
# Basic authentication for admin panel (optional)
AuthType Basic
AuthName "Admin Area"
AuthUserFile /path/to/.htpasswd
Require valid-user

# Or restrict by IP (if you have static IP)
# Order Deny,Allow
# Deny from all
# Allow from YOUR_IP_ADDRESS
```

4. **Remove Test/Debug Files:**
   - Delete files in `/checkings/` folder
   - Delete `/login/test_*.php` files
   - Delete `/login/*_debug.php` files

---

## 🔧 Troubleshooting Common Issues

### ❌ Database Connection Error

**Problem:** "Connection failed" or "Access denied"

**Solution:**
1. Verify credentials in `database.php`
2. Check database host in cPanel (might be `sql305`, `sql306`, etc.)
3. Ensure database user has ALL PRIVILEGES
4. Test connection with this script:

Create `test_db.php` in `htdocs`:
```php
<?php
$host = 'sql305.infinityfree.com';
$user = 'epiz_XXXXX_kprcas_admin';
$pass = 'your_password';
$db = 'epiz_XXXXX_kprcas_attendance';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected successfully!";
    echo "<br>Database: " . $db;
}
?>
```

Visit: `http://yourdomain.com/test_db.php`
**Delete this file after testing!**

---

### ❌ Email Not Sending

**Problem:** OTP emails not arriving

**Solution:**
1. **Change SMTP port from 587 to 465**
2. Update `email_config.php`:
   ```php
   $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
   $mail->Port = 465;
   ```
3. Verify Gmail "App Password" is correct (not regular password)
4. Check InfinityFree's email sending limits
5. Test with simple script:

Create `test_mail.php`:
```php
<?php
$to = "your-email@gmail.com";
$subject = "Test Email";
$message = "This is a test from InfinityFree";
$headers = "From: noreply@yoursite.com";

if(mail($to, $subject, $message, $headers)) {
    echo "Email sent!";
} else {
    echo "Email failed!";
}
?>
```

---

### ❌ 500 Internal Server Error

**Problem:** White page or 500 error

**Solution:**
1. Check `.htaccess` syntax
2. Enable error display (see Step 11)
3. Check PHP version compatibility
4. Verify all files uploaded correctly
5. Check file permissions (usually 644 for files, 755 for folders)

---

### ❌ Session Issues / Cannot Login

**Problem:** Login doesn't work, redirects back

**Solution:**
1. Check `session_start()` is called
2. Verify session save path is writable
3. Clear browser cache and cookies
4. Check if cookies are blocked
5. Add to top of login files:
   ```php
   <?php
   ini_set('session.use_cookies', 1);
   ini_set('session.cookie_httponly', 1);
   session_start();
   ?>
   ```

---

### ❌ Files Not Found / 404 Errors

**Problem:** CSS, JS, or PHP files not loading

**Solution:**
1. Check file paths are correct
2. Use relative paths: `../assets/css/style.css`
3. Or absolute paths: `/assets/css/style.css`
4. Verify files are in correct folders
5. Check file names are case-sensitive on Linux servers

---

### ❌ Upload Size Limit

**Problem:** Cannot upload large files (database SQL)

**Solution:**
1. Split large SQL file into smaller parts
2. Or import tables one by one
3. Or increase limits in `.user.ini`:
   ```ini
   upload_max_filesize = 20M
   post_max_size = 20M
   max_execution_time = 300
   ```

---

## 📊 InfinityFree Limitations

Be aware of these free hosting limitations:

| Feature | Limit |
|---------|-------|
| Storage | Unlimited* |
| Bandwidth | Unlimited* |
| Daily Hits | 50,000 |
| File Size | 10 MB per file |
| MySQL Databases | 400 |
| Email Sending | Limited (use external SMTP) |
| Execution Time | 60 seconds |
| Cron Jobs | ❌ Not available |
| SSH Access | ❌ Not available |

*Fair usage policy applies

---

## 🚀 Performance Tips

### 1. Enable Browser Caching
Add to `.htaccess`:
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### 2. Optimize Images
- Compress images before uploading
- Use appropriate formats (JPEG for photos, PNG for graphics)

### 3. Minimize Database Queries
- Use prepared statements
- Cache frequently accessed data
- Clean up old OTP records regularly

---

## 📱 Testing Checklist

After deployment, test these features:

- [ ] Admin login works
- [ ] Teacher login works
- [ ] Add new class
- [ ] Add new student
- [ ] Add new teacher
- [ ] Assign subject to teacher
- [ ] Teacher: Generate QR code
- [ ] Teacher: Send QR via email (test email delivery)
- [ ] Student: Receive QR email
- [ ] Student: Click QR link
- [ ] Student: Receive OTP email
- [ ] Student: Verify OTP
- [ ] Student: Attendance marked
- [ ] Teacher: View attendance report
- [ ] Mobile responsive design
- [ ] All pages load without errors

---

## 🔄 Updating Your Site

When you make changes to your GitHub repository:

**Method 1: Manual Update**
1. Download latest code from GitHub
2. Upload changed files via FTP/File Manager
3. Update database if schema changed

**Method 2: Re-deploy**
1. Delete all files in `htdocs` (except database config)
2. Download fresh copy from GitHub
3. Upload all files
4. Update database.php with credentials
5. Test

---

## 📈 Upgrade to Paid Hosting

When you outgrow free hosting, consider upgrading:

**InfinityFree Premium:**
- Remove daily hit limits
- Better support
- More resources

**Or migrate to:**
- Hostinger ($2.99/mo)
- SiteGround ($3.99/mo)
- DigitalOcean ($4/mo)

---

## 🎓 Additional Resources

- **InfinityFree Docs:** [forum.infinityfree.net](https://forum.infinityfree.net)
- **PHP Documentation:** [php.net](https://php.net)
- **PHPMailer Guide:** [github.com/PHPMailer/PHPMailer](https://github.com/PHPMailer/PHPMailer)
- **Your Project Docs:** `COMPLETE_DOCUMENTATION.md`

---

## 🆘 Need Help?

If you encounter issues:

1. Check error logs in cPanel
2. Search InfinityFree forum
3. Review `COMPLETE_DOCUMENTATION.md`
4. Check GitHub issues
5. Test locally first to isolate the problem

---

## ✅ Quick Start Checklist

- [ ] Sign up for InfinityFree
- [ ] Create hosting account
- [ ] Create MySQL database and user
- [ ] Download project from GitHub
- [ ] Update database.php with new credentials
- [ ] Update email_config.php (port 465)
- [ ] Upload all files to htdocs
- [ ] Import database via phpMyAdmin
- [ ] Upload vendor folder (PHPMailer)
- [ ] Test website URL
- [ ] Test admin login
- [ ] Change default passwords
- [ ] Test email functionality
- [ ] Secure with .htaccess
- [ ] Remove test files
- [ ] Test all features
- [ ] Share your live URL! 🎉

---

**Your Free Live Site:** `http://your-subdomain.wuaze.com/login/login.php`

**Good luck! 🚀**

---

**Created:** November 3, 2025  
**For:** KPRCAS Attendance System  
**GitHub:** https://github.com/cnp3301-wq/Attednence

