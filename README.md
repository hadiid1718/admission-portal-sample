```

---

## 🎯 **Steps to Fix:**

1. **First, run create_admin.php once:**
```
   http://localhost/adv-web-project/university_admission/create_admin.php
```
   
   You should see: "✅ Admin created successfully!"

2. **Then, try logging in:**
```
   http://localhost/adv-web-project/university_admission/admin_login.php
```
   
   Use:
   - Username: `admin`
   - Password: `admin123`

3. **After admin is created, you can delete create_admin.php** (for security)

---

## ✅ **Your File Structure Should Be:**
```
university_admission/
├── config.php
├── student_register.php
├── student_login.php
├── student_dashboard.php
├── apply_admission.php
├── update_application.php
├── view_merit.php
├── admin_login.php        ← Fixed version
├── admin_dashboard.php
├── generate_merit.php
├── create_admin.php       ← Run once to create admin
└── logout.php