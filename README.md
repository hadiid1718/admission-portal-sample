# Student Admission Portal System

A comprehensive web-based admission management system with separate modules for students and administrators.

## Features

### Student Module
- ✅ **Create Account / Register** - Students can sign up with personal details
- ✅ **Login System** - Secure authentication with validation
- ✅ **Apply for Admission** - Submit application with academic details
- ✅ **Update Application** - Edit application details before final submission
- ✅ **View Merit List** - Check admission results and rankings

### Admin Module
- ✅ **Admin Login** - Secure admin authentication
- ✅ **View Applications** - Browse all submitted student applications
- ✅ **Generate Merit List** - Calculate merit using customizable formula
- ✅ **Publish Merit List** - Make results available to students
- ✅ **Dashboard Statistics** - Overview of applications and system status

## Technical Stack

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Server:** XAMPP / WAMP

## Installation Instructions

### Step 1: Install XAMPP/WAMP

1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP to `C:\xampp` (Windows) or `/Applications/XAMPP` (Mac)
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Setup Database

1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click on "SQL" tab
3. Copy and paste the entire SQL code from `database_schema.sql`
4. Click "Go" to execute

**Default Admin Credentials:**
- Username: `admin`
- Password: `admin123`

### Step 3: Configure Project Files

1. Create a folder named `admission_portal` in:
   - Windows: `C:\xampp\htdocs\admission_portal`
   - Mac: `/Applications/XAMPP/htdocs/admission_portal`

2. Place all PHP files in this folder:
   - config.php
   - student_register.php
   - student_login.php
   - student_dashboard.php
   - apply_admission.php
   - update_application.php
   - view_merit.php
   - admin_login.php
   - admin_dashboard.php
   - generate_merit.php
   - logout.php

3. Open `config.php` and verify database settings:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Default is empty for XAMPP
define('DB_NAME', 'admission_portal');
```

### Step 4: Access the System

1. **Student Portal:**
   - Registration: `http://localhost/admission_portal/student_register.php`
   - Login: `http://localhost/admission_portal/student_login.php`

2. **Admin Panel:**
   - Login: `http://localhost/admission_portal/admin_login.php`
   - Username: `admin`
   - Password: `admin123`

## File Structure

```
admission_portal/
│
├── config.php                 # Database configuration
├── student_register.php       # Student registration
├── student_login.php          # Student login
├── student_dashboard.php      # Student dashboard
├── apply_admission.php        # Admission application form
├── update_application.php     # Update application
├── view_merit.php            # View merit list (student)
├── admin_login.php           # Admin login
├── admin_dashboard.php       # Admin dashboard
├── generate_merit.php        # Generate & publish merit
└── logout.php                # Logout handler
```

## Database Tables

1. **students** - Student account information
2. **applications** - Admission applications
3. **admins** - Administrator accounts
4. **merit_list** - Published merit list

## Merit Calculation Formula

```
Merit Score = (50% × Matric Marks / 11) + (50% × Inter Marks / 11)
```

This formula normalizes marks out of 1100 and gives equal weightage to both examinations.

## Usage Guide

### For Students:

1. **Register:** Create account with personal details and CNIC
2. **Login:** Use registered email and password
3. **Apply:** Fill admission form with academic details
4. **Update:** Modify application before merit list publication
5. **Check Results:** View merit list when published by admin

### For Administrators:

1. **Login:** Use admin credentials
2. **View Applications:** Review all submitted applications
3. **Generate Merit:** Set cutoff score and generate merit list
4. **Publish:** Merit list becomes visible to students automatically
5. **Monitor:** Track statistics and application status

## Security Features

- ✅ Password hashing using PHP `password_hash()`
- ✅ SQL injection prevention using prepared statements
- ✅ Input validation and sanitization
- ✅ Session-based authentication
- ✅ CNIC format validation
- ✅ Email validation

## Troubleshooting

### Problem: "Connection failed" error
**Solution:** 
- Make sure MySQL is running in XAMPP
- Check database credentials in config.php
- Verify database name is correct

### Problem: "Page not found" error
**Solution:**
- Ensure files are in `htdocs/admission_portal` folder
- Check that Apache is running
- Use correct URL: `http://localhost/admission_portal/filename.php`

### Problem: "Cannot login" error
**Solution:**
- Verify database has been created and populated
- Check if admin credentials are correct
- Clear browser cache and try again

## Customization

### Change Admin Password:
```sql
UPDATE admins 
SET password = '$2y$10$YOUR_NEW_HASHED_PASSWORD' 
WHERE username = 'admin';
```

### Modify Merit Formula:
Edit line in `apply_admission.php` and `update_application.php`:
```php
$merit_score = (0.5 * $matric_marks / 11) + (0.5 * $inter_marks / 11);
```

### Add More Programs:
Edit the select dropdown in `apply_admission.php`:
```html
<option value="Your Program">Your Program</option>
```

## Browser Compatibility

- ✅ Google Chrome (Recommended)
- ✅ Mozilla Firefox
- ✅ Microsoft Edge
- ✅ Safari

## Support

For issues or questions:
1. Check the troubleshooting section
2. Verify all installation steps were followed
3. Check PHP error logs in XAMPP

## License

This project is created for educational purposes.

---

**Developed with PHP, MySQL, HTML, CSS & JavaScript**