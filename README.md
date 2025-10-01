# 🎖️ BAF Parade State Management System

## Complete Installation Guide

A comprehensive web-based system for managing Bangladesh Air Force MIST Student Officers' parade states, generating professional PDF reports, and tracking attendance.

---

## 📋 Table of Contents

- [System Requirements](#system-requirements)
- [Installation Steps](#installation-steps)
  - [Step 1: Install XAMPP](#step-1-install-xampp)
  - [Step 2: Download Project Files](#step-2-download-project-files)
  - [Step 3: Setup Database](#step-3-setup-database)
  - [Step 4: Test the Installation](#step-4-test-the-installation)
- [Usage Guide](#usage-guide)
- [Troubleshooting](#troubleshooting)
- [Features](#features)
- [Support](#support)

---

## 🖥️ System Requirements

### Minimum Requirements:
- **Operating System:** Windows 7/8/10/11
- **RAM:** 4GB minimum (8GB recommended)
- **Storage:** 500MB free space
- **Browser:** Chrome, Firefox, Edge, or Safari (latest versions)

### Software Requirements:
- **XAMPP** (includes Apache, MySQL, PHP)
- **Web Browser** (modern version)
- **Text Editor** (optional, for configuration)

---

## 🚀 Installation Steps
### Step 1: Download the zip file

1. **Download the ZIP File**
- Go to the [MIST BAF Parade State GitHub repository](https://github.com/fahimratul/MIST-BAF-Parade-state).
- Click the green **Code** button.
- Select **Download ZIP**.

 2. Unzip the Downloaded File
- Locate the downloaded ZIP file (usually in your Downloads folder).
- Right-click the ZIP file and select **Extract All**.
- Choose a destination folder and click **Extract**.

### Step 2: Install XAMPP

2. **Install XAMPP:**
   ```
   - Run the XAMMP-Installer.exe 
   - It will automatacally download your XAMPP app and run it
   
   ```

3. **Start XAMPP:**
   - Open XAMPP Control Panel (Start Menu → XAMPP → XAMPP Control Panel)
   - Click "Start" button next to **Apache**
   - Click "Start" button next to **MySQL**
   - You should see green background indicating they're running

   ![XAMPP Control Panel](https://i.imgur.com/xampp-control.png)

4. **Verify Installation:**
   - Open your browser
   - Visit: `http://localhost`
   - You should see the XAMPP welcome page

### Step 3: Setup Database
   - Run the **Paradestate-Copier.exe** 
   - It will setup your HD file
   - After this run **BAF-Parade-Setup.exe**
   - It will setup your database. Now your database is ready to run 


### Step 4: Test the Installation

#### 1. Access the System

Open your browser and visit:
```
http://localhost/paradestate/

```

You should see the **Dashboard** with:
- ✅ Welcome header with BAF branding
- ✅ Statistics cards showing total officers
- ✅ Navigation menu
- ✅ Quick action buttons

#### 2. Test Each Module

**a) Dashboard Test:**
URL:
```
 http://localhost/paradestate/index.php
```
Expected: Statistics, department breakdown, recent activity


**b) Officers Management:**
URL:
```
 http://localhost/paradestate/officers.php
```
Expected: List of sample officers with search/filter options

**c) Add New Officer:**
URL: 
```
 http://localhost/paradestate/add_officer.php 
```
Try adding:
- Name: Test Officer
- Rank: Flg Offr
- Department: CSE
- Level: II
- Mess Location: MIST Dhaka Mess
- Gender: Male
Click "Add Officer"
Expected: Success message


**d) Parade State Management:**
URL: 
```
http://localhost/paradestate/parade.php
```
Expected: List of all officers with attendance status dropdowns
Try:
- Mark a few officers as "Leave" or "CMH"
- Add remarks
- Click "Update Attendance"
Expected: Success message

**e) Generate PDF Report:**
URL: 
```
http://localhost/paradestate/generate_report.php?date=2025-01-15
```
Expected: PDF report opens in browser
Should show: Complete parade state report in BAF format


**f) Reports Dashboard:**
URL:
```
 http://localhost/paradestate/reports.php
```
Expected: List of generated reports with statistics

---

## 📚 Usage Guide

### Daily Operations

#### 1. Mark Daily Attendance

1. Go to: Parade State Management
2. Select today's date
3. Mark attendance for each officer:
   - Present (default)
   - Leave
   - CMH
   - Sick Leave
   - SIQ
   - Isolation
4. Add remarks if needed
5. Click "Update Attendance"

#### 2. Generate Daily Report
1. Go to: Reports Dashboard
2. Select date
3. Click "Generate PDF Report"
4. Print or save PDF

#### 3. Add New Officers
1. Go to: Officers Management
2. Click "Add New Officer"
3. Fill in details:
   - Full name
   - Rank
   - Department
   - Level
   - Mess Location
   - Gender
4. Click "Add Officer"

#### 4. View Statistics

1. Dashboard shows:
   - Total officers
   - Present today
   - Absent today
   - Female officers
2. Department breakdowns
3. Recent activity


---

## 🔧 Troubleshooting

### Common Issues and Solutions

#### Issue 1: "Cannot connect to database"

**Solution:**

1. Check if MySQL is running in XAMPP Control Panel
2. Verify config.php has correct credentials:
   - $host = 'localhost';
   - $username = 'root';
   - $password = '';  (empty by default)
3. Test database connection:
   - Visit: 
   ```
   http://localhost/phpmyadmin
   ```
   - Check if "baf_parade_system" database exists


#### Issue 2: "Page not found" or "404 Error"

**Solution:**
1. Verify XAMPP Apache is running (green in Control Panel)
2. Check folder location:
   - Should be: C:\xampp\htdocs\paradestate\
   - NOT: C:\xampp\htdocs\baf-parade-system-main\
3. Use correct URL: http://localhost/paradestate (not .com)

#### Issue 3: "Failed to load PDF document"

**Solution:**
1. Install mPDF:
   - Visit: 
   ```
   http://localhost/paradestate/install_composer.php
   ```
   - Click install button
   - Wait for completion

2. Verify installation:
   - Check folder: paradestate/vendor/
   - Should contain: mPDF.php file


#### Issue 4: "Access Denied" or Permission Issues

- Right-click folder: C:\xampp\htdocs\
- Properties → Security tab
- Make sure your user has "Full control"


#### Issue 5: "Blank white page"

**Solution:**
1. Enable error reporting:
   - Edit: C:\xampp\htdocs\baf_parade_system\index.php
   - Add at the very top:
     <?php
     error_reporting(E_ALL);
     ini_set('display_errors', 1);
     ?>

2. Check Apache error logs:
   - XAMPP Control Panel → Apache → Logs button
   - Check error.log for details

3. Verify PHP version:
   - Visit: http://localhost/dashboard/
   - Check PHP version (should be 7.4+)

#### Issue 6: "Table doesn't exist"

**Solution:**
1. Re-import database:
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Select "baf_parade_system" database
   - Click "Import" tab
   - Choose database_schema.sql file
   - Click "Go"

2. Verify tables exist:
   - Should see: officers, parade_states, reports

#### Issue 7: Slow Performance

**Solution:**
1. Increase PHP memory limit:
   - Edit: C:\xampp\php\php.ini (Windows)
   - Find: memory_limit = 128M
   - Change to: memory_limit = 256M
   - Restart Apache

2. Close unnecessary applications
3. Clear browser cache (Ctrl+Shift+Delete)

---

## ✨ Features

### Core Features:
- ✅ **Officer Management:** Add, edit, delete, and search officers
- ✅ **Daily Attendance:** Mark present/absent with reasons
- ✅ **PDF Reports:** Generate professional BAF-format reports
- ✅ **Statistics Dashboard:** Real-time attendance analytics
- ✅ **Search & Filter:** Advanced filtering by department, level, location
- ✅ **Historical Reports:** View and regenerate past reports
- ✅ **Responsive Design:** Works on desktop, tablet, and mobile
- ✅ **User-Friendly Interface:** Modern, intuitive design

### Report Features:
- 📊 Parade state table by department and level
- 📊 Grand totals and subtotals
- 📊 Level-wise officer counts
- 📊 Female officer statistics by location
- 📊 Detailed absent/off-parade listing
- 📊 Summary of absences by reason
- 📊 Professional PDF format

---

## 📞 Support

### Getting Help:

1. **Check Documentation:**
   - Re-read this README
   - Check troubleshooting section

2. **Common Resources:**
   - XAMPP Documentation: [https://www.apachefriends.org/docs/](https://www.apachefriends.org/docs/)
   - PHP Manual: [https://www.php.net/manual/](https://www.php.net/manual/)
   - MySQL Documentation: [https://dev.mysql.com/doc/](https://dev.mysql.com/doc/)

3. **System Information:**
   When reporting issues, include:
   - Operating System: Windows/macOS/Linux
   - XAMPP Version: (check Control Panel)
   - PHP Version: (visit http://localhost/dashboard/)
   - Error Messages: (exact text)
   - Steps to Reproduce: (what you did)
   - Screenshots: (if applicable)

---

## 🔄 Updating the System

### To update to a newer version:

1. **Backup Current Installation:**
   - Export database via phpMyAdmin
   - Copy paradestate folder to safe location

2. **Download New Version:**
   - Download latest ZIP from GitHub
   - Extract to temporary location

3. **Replace Files:**
   - Keep your config.php (don't overwrite)
   - Replace all other PHP files
   - Keep vendor/ folder if already installed

4. **Update Database:**
   - Check if new database_schema.sql includes updates
   - Import only new tables/columns if needed

---

## 📝 Configuration Reference

### config.php Settings:
```php
$host = 'localhost';           // Database host (usually localhost)
$dbname = 'baf_parade_system'; // Database name (don't change)
$username = 'root';            // MySQL username (default: root)
$password = '';                // MySQL password (default: empty)
```

### PHP Requirements:
- PHP Version: 7.4 or higher (8.0+ recommended)
- Extensions Required:
  - PDO
  - PDO_MySQL
  - mbstring
  - zip (for mPDF installation)

---

## 🎓 Training Resources

### For System Administrators:
1. Basic database backup procedures
2. User management (if implemented)
3. Regular maintenance tasks
4. Report generation and archiving

### For End Users:
1. Daily attendance marking
2. Adding new officers
3. Generating reports
4. Searching and filtering

---

## 📄 License

This system is developed for Bangladesh Air Force use. All rights reserved.

---

## 🙏 Acknowledgments

- Bangladesh Air Force
- Military Institute of Science & Technology (MIST)
- mPDF Library: [https://mPDF.org/](https://mPDF.org/)
- Bootstrap Framework: [https://getbootstrap.com/](https://getbootstrap.com/)

---

## 📌 Quick Reference Card

### Essential URLs:
Dashboard:          http://localhost/paradestate/
Officers:           http://localhost/paradestate/officers.php
Add Officer:        http://localhost/paradestate/add_officer.php
Parade State:       http://localhost/paradestate/parade.php
Reports:            http://localhost/paradestate/reports.php
Generate PDF:       http://localhost/paradestate/generate_report.php
Install mPDF:      http://localhost/paradestate/install_composer.php
phpMyAdmin:         http://localhost/phpmyadmin

### XAMPP Control:
Start Apache:       Click "Start" next to Apache
Start MySQL:        Click "Start" next to MySQL
Stop Services:      Click "Stop" buttons
View Logs:          Click "Logs" buttons
Config Files:       Click "Config" buttons


### File Locations:
Windows:
- Project: C:\xampp\htdocs\paradestate\
- Config: C:\xampp\php\php.ini
- Logs: C:\xampp\apache\logs\

---

**Installation Complete! 🎉**

Your BAF Parade State Management System is now ready to use.

For questions or issues, refer to the Troubleshooting section above.

**Version:** 1.0.0  
**Last Updated:** January 2025  
**Developed for:** Bangladesh Air Force