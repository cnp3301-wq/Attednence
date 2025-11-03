# 🚀 Deployment Guide - KPRCAS Attendance System

## Prerequisites
- Web hosting with PHP 8.0+ and MySQL
- Domain name (optional)
- Gmail account for email functionality
- FTP client (FileZilla) or cPanel access

---

## 📦 Method 1: Shared Hosting (Recommended for Beginners)

### Step 1: Choose a Hosting Provider

**Recommended:**
- **Hostinger** - $2.99/month (Best value)
- **SiteGround** - $3.99/month (Best performance)
- **Bluehost** - $3.95/month (Beginner-friendly)

### Step 2: Prepare Your Files

1. **Update Database Configuration**
   
   Edit `login/config/database.php`:
   ```php
   define('DB_HOST', 'localhost'); // or hosting provider's DB host
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_password');
   define('DB_NAME', 'kprcas_attendance');
   ```

2. **Update Email Configuration**
   
   Edit `login/config/email_config.php`:
   ```php
   $mail->Host       = 'smtp.gmail.com';
   $mail->SMTPAuth   = true;
   $mail->Username   = 'your-production-email@gmail.com';
   $mail->Password   = 'your-app-specific-password';
   ```

3. **Update Base URLs** (if needed in any files)
   - Search for `localhost` references
   - Replace with your domain: `https://yourdomain.com`

### Step 3: Upload Files

**Option A: Using cPanel File Manager**
1. Login to cPanel
2. Go to File Manager
3. Navigate to `public_html`
4. Upload all files (or upload ZIP and extract)
5. Delete installation files after setup

**Option B: Using FTP (FileZilla)**
1. Download FileZilla from filezilla-project.org
2. Connect using credentials from hosting provider:
   - Host: ftp.yourdomain.com
   - Username: your FTP username
   - Password: your FTP password
   - Port: 21
3. Upload all files to `public_html` or `www` folder

### Step 4: Create MySQL Database

1. **In cPanel:**
   - Go to "MySQL Databases"
   - Create new database: `kprcas_attendance`
   - Create new user with strong password
   - Assign user to database with ALL PRIVILEGES

2. **Note these credentials:**
   ```
   Database Name: cpanel_username_kprcas
   Database User: cpanel_username_admin
   Database Password: [your strong password]
   Database Host: localhost (or as provided)
   ```

### Step 5: Import Database

1. **In cPanel phpMyAdmin:**
   - Select your database
   - Click "Import" tab
   - Choose `database_schema_complete.sql`
   - Click "Go"
   - Wait for success message

2. **Verify tables created:**
   - Should see 11 tables
   - Check if admin user exists in `users` table

### Step 6: Update Configuration Files

Update `login/config/database.php` with actual credentials:
```php
define('DB_HOST', 'localhost'); // Check with hosting provider
define('DB_USER', 'cpanel_user_admin');
define('DB_PASS', 'your_strong_password');
define('DB_NAME', 'cpanel_user_kprcas');
```

### Step 7: Install Composer Dependencies

**Option A: Via SSH (if available)**
```bash
cd public_html
php composer.phar install
# or
composer install
```

**Option B: Upload vendor folder**
- If no SSH access, upload the entire `vendor` folder via FTP

### Step 8: Set Permissions

Set correct file permissions:
- Folders: 755
- PHP files: 644
- Writable folders (if any): 777 (temporarily, change to 755 after testing)

### Step 9: Configure SSL Certificate

1. In cPanel, go to "SSL/TLS"
2. Install free Let's Encrypt certificate
3. Force HTTPS redirect (add to .htaccess):
   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

### Step 10: Test Your Application

1. Visit: `https://yourdomain.com/login/login.php`
2. Test admin login:
   - Email: admin@kprcas.ac.in
   - Password: admin123
3. **Change default password immediately!**
4. Test teacher and student flows
5. Send test email to verify PHPMailer

---

## 📦 Method 2: Cloud Hosting (AWS/DigitalOcean)

### DigitalOcean Droplet Setup

#### Step 1: Create Droplet
1. Sign up at digitalocean.com
2. Create new Droplet:
   - **Image:** Ubuntu 22.04 LTS
   - **Plan:** Basic ($4-6/month)
   - **Datacenter:** Choose closest to users
   - Add SSH key

#### Step 2: Connect via SSH
```bash
ssh root@your_droplet_ip
```

#### Step 3: Install LAMP Stack
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache
sudo apt install apache2 -y

# Install MySQL
sudo apt install mysql-server -y

# Install PHP 8.1
sudo apt install php8.1 php8.1-cli php8.1-common php8.1-mysql php8.1-xml php8.1-curl php8.1-mbstring php8.1-zip -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Restart Apache
sudo systemctl restart apache2
```

#### Step 4: Configure MySQL
```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database
sudo mysql -u root -p
```

```sql
CREATE DATABASE kprcas_attendance;
CREATE USER 'kprcas_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON kprcas_attendance.* TO 'kprcas_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Step 5: Upload Files
```bash
# Using SCP from your local machine (Windows PowerShell)
scp -r "e:\KPRCAS\New folder\attendance\*" root@your_droplet_ip:/var/www/html/
```

Or use FileZilla with SFTP protocol.

#### Step 6: Set Permissions
```bash
cd /var/www/html
sudo chown -R www-data:www-data *
sudo chmod -R 755 *
```

#### Step 7: Install Dependencies
```bash
cd /var/www/html
composer install
```

#### Step 8: Import Database
```bash
mysql -u kprcas_user -p kprcas_attendance < database_schema_complete.sql
```

#### Step 9: Configure Apache
```bash
sudo nano /etc/apache2/sites-available/attendance.conf
```

Add:
```apache
<VirtualHost *:80>
    ServerAdmin admin@yourdomain.com
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

Enable site and rewrite module:
```bash
sudo a2ensite attendance.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Step 10: Install SSL (Let's Encrypt)
```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

---

## 📦 Method 3: AWS EC2 (Scalable)

### Step 1: Launch EC2 Instance
1. Go to AWS Console
2. Launch EC2 instance:
   - **AMI:** Ubuntu Server 22.04 LTS
   - **Instance Type:** t2.micro (free tier)
   - **Security Group:** Allow HTTP (80), HTTPS (443), SSH (22)
   - Download key pair (.pem file)

### Step 2: Connect to Instance
```powershell
# Windows PowerShell (convert .pem to .ppk for PuTTY or use WSL)
ssh -i "your-key.pem" ubuntu@your-ec2-public-ip
```

### Step 3: Follow Same Steps as DigitalOcean
- Install LAMP stack
- Configure MySQL
- Upload files
- Set permissions
- Install SSL

### Step 4: Use RDS for Database (Optional)
1. Create RDS MySQL instance
2. Update DB_HOST in database.php to RDS endpoint
3. More reliable than EC2-hosted database

---

## 🔧 Post-Deployment Checklist

### Security
- [ ] Change all default passwords
- [ ] Enable HTTPS/SSL
- [ ] Set secure file permissions (755/644)
- [ ] Remove or restrict access to installation/testing files
- [ ] Configure firewall (UFW on Ubuntu)
- [ ] Enable PHP security settings
- [ ] Backup database regularly

### Configuration
- [ ] Update database credentials
- [ ] Configure email settings (Gmail App Password)
- [ ] Test email delivery
- [ ] Set up cron jobs (if needed for cleanup)
- [ ] Configure timezone in PHP
- [ ] Update base URLs in code

### Testing
- [ ] Test admin login
- [ ] Test teacher login
- [ ] Test student attendance flow
- [ ] Test QR code generation
- [ ] Test OTP email delivery
- [ ] Test password reset
- [ ] Test on mobile devices
- [ ] Check error logs

### Monitoring
- [ ] Set up error logging
- [ ] Monitor server resources
- [ ] Check email delivery logs
- [ ] Review login logs
- [ ] Set up uptime monitoring (uptimerobot.com)

### Backup Strategy
- [ ] Daily database backups
- [ ] Weekly full backups
- [ ] Test restore procedure
- [ ] Store backups off-site

---

## 🐛 Troubleshooting Common Issues

### Database Connection Error
```
Solution:
1. Check DB credentials in database.php
2. Verify database exists: mysql -u user -p
3. Check MySQL is running: sudo systemctl status mysql
4. Verify user permissions: SHOW GRANTS FOR 'user'@'localhost';
```

### PHPMailer Errors
```
Solution:
1. Enable "Less secure app access" in Gmail (or use App Password)
2. Check email_config.php settings
3. Verify port 587 is not blocked
4. Test with phpmailer_smtp_test.php
```

### Permission Denied Errors
```
Solution:
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
```

### 500 Internal Server Error
```
Solution:
1. Check Apache error logs: sudo tail -f /var/log/apache2/error.log
2. Verify PHP version compatibility
3. Check .htaccess rules
4. Ensure all PHP extensions installed
```

### Session Errors
```
Solution:
1. Check session save path is writable
2. Verify session.cookie_secure in php.ini
3. Clear browser cookies
```

---

## 📊 Performance Optimization

### Enable PHP OPcache
```ini
; In php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

### Enable Gzip Compression
```apache
# In .htaccess
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

### Browser Caching
```apache
# In .htaccess
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

## 📞 Support

- **Technical Issues:** Check COMPLETE_DOCUMENTATION.md
- **Hosting Support:** Contact your hosting provider
- **Email Issues:** Verify Gmail settings and app passwords

---

**Good luck with your deployment! 🚀**

