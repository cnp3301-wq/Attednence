# 🚀 Quick Deploy from GitHub to InfinityFree

## ⚡ 15-Minute Quick Start

### 1️⃣ Sign Up (2 minutes)
- Go to [infinityfree.net](https://infinityfree.net) → Sign up → Verify email

### 2️⃣ Create Account (3 minutes)
- Click "Create Account"
- Choose subdomain: `yourname.wuaze.com`
- Wait for activation

### 3️⃣ Setup Database (5 minutes)
1. Open cPanel → MySQL Databases
2. Create database: `kprcas_attendance`
3. Create user: `kprcas_admin` (strong password)
4. Assign user to database with ALL PRIVILEGES
5. **Note credentials:**
   ```
   Host: sql305.infinityfree.com
   DB Name: epiz_XXXXX_kprcas_attendance
   DB User: epiz_XXXXX_kprcas_admin
   DB Pass: [your password]
   ```

### 4️⃣ Download from GitHub (2 minutes)
- Go to: https://github.com/cnp3301-wq/Attednence
- Click "Code" → Download ZIP
- Extract to folder

### 5️⃣ Update Configuration (3 minutes)
Edit `login/config/database.php`:
```php
define('DB_HOST', 'sql305.infinityfree.com'); // Your actual host
define('DB_USER', 'epiz_XXXXX_kprcas_admin'); // Your username
define('DB_PASS', 'your_password');           // Your password
define('DB_NAME', 'epiz_XXXXX_kprcas_attendance'); // Your DB name
```

Edit `login/config/email_config.php`:
```php
$mail->Port = 465; // Change from 587 to 465 for InfinityFree
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use SSL
```

### 6️⃣ Upload Files (5 minutes)
**Option A: File Manager**
- cPanel → Online File Manager
- Go to `htdocs` folder
- Upload all files (or upload ZIP and extract)

**Option B: FTP**
- Use FileZilla
- Host: `ftpupload.net`
- Upload to `/htdocs` folder

### 7️⃣ Import Database (3 minutes)
- cPanel → phpMyAdmin
- Select your database
- Import → Choose `schema/database_schema_complete.sql`
- Click Go

### 8️⃣ Test! (2 minutes)
- Visit: `http://yourname.wuaze.com/login/login.php`
- Login: admin@kprcas.ac.in / admin123
- **Change password immediately!**

---

## 🎯 Total Time: ~15-20 minutes

---

## 🆘 Quick Troubleshooting

| Problem | Solution |
|---------|----------|
| **Database error** | Verify credentials in `database.php` |
| **Email not working** | Change port to 465 in `email_config.php` |
| **500 error** | Check file permissions (644/755) |
| **Login fails** | Clear browser cookies, check sessions |
| **Files missing** | Ensure all files in `htdocs` folder |

---

## 📋 Essential Credentials to Save

```
✅ InfinityFree Login:
   - Email: _______________
   - Password: _______________

✅ Website URL: 
   - http://_____________.wuaze.com

✅ FTP Details:
   - Host: ftpupload.net
   - User: epiz_XXXXX
   - Pass: _______________

✅ Database:
   - Host: sql305.infinityfree.com
   - Name: epiz_XXXXX_kprcas_attendance
   - User: epiz_XXXXX_kprcas_admin
   - Pass: _______________

✅ cPanel:
   - URL: https://cpanel.infinityfree.net
   - User: epiz_XXXXX
   - Pass: _______________
```

---

## 🔐 First Login (Change These!)

```
Admin:
Email: admin@kprcas.ac.in
Password: admin123 ← CHANGE THIS!

Teacher:
Email: rajesh.kumar@kprcas.ac.in
Password: Rajesh1234 ← CHANGE THIS!
```

---

## ✅ Post-Deployment Checklist

- [ ] Database connected successfully
- [ ] Admin login works
- [ ] Change admin password
- [ ] Test teacher login
- [ ] Change teacher passwords
- [ ] Send test OTP email
- [ ] Test QR code generation
- [ ] Test student attendance flow
- [ ] Test on mobile device
- [ ] Remove test/debug files
- [ ] Add .htaccess security
- [ ] Bookmark your live URL

---

## 📚 Full Documentation

For detailed instructions, see:
- **FREE-HOSTING-GUIDE.md** - Complete guide
- **deployment-guide.md** - All hosting options
- **COMPLETE_DOCUMENTATION.md** - System documentation

---

## 🎉 You're Live!

Share your attendance system:
- **Your URL:** `http://yourname.wuaze.com/login/login.php`
- **GitHub:** https://github.com/cnp3301-wq/Attednence

---

**Need help?** Check FREE-HOSTING-GUIDE.md for detailed troubleshooting!

