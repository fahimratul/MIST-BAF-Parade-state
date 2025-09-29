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
  - [Step 4: Configure the System](#step-4-configure-the-system)
  - [Step 5: Install TCPDF for PDF Generation](#step-5-install-tcpdf-for-pdf-generation)
  - [Step 6: Test the Installation](#step-6-test-the-installation)
- [Usage Guide](#usage-guide)
- [Troubleshooting](#troubleshooting)
- [Features](#features)
- [Support](#support)

---

## 🖥️ System Requirements

### Minimum Requirements:
- **Operating System:** Windows 7/8/10/11, macOS 10.13+, or Linux
- **RAM:** 4GB minimum (8GB recommended)
- **Storage:** 500MB free space
- **Browser:** Chrome, Firefox, Edge, or Safari (latest versions)

### Software Requirements:
- **XAMPP** (includes Apache, MySQL, PHP)
- **Web Browser** (modern version)
- **Text Editor** (optional, for configuration)

---

## 🚀 Installation Steps

### Step 1: Install XAMPP

#### For Windows:

1. **Download XAMPP:**
   - Visit: [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Download XAMPP for Windows (PHP 8.0 or higher recommended)
   - File size: ~150MB

2. **Install XAMPP:**
   ```
   - Run the downloaded installer (xampp-windows-x64-8.x.x-installer.exe)
   - Choose installation directory (default: C:\xampp)
   - Select components: Apache, MySQL, PHP, phpMyAdmin
   - Click "Next" through the installation wizard
   - Uncheck "Learn more about Bitnami" option
   - Click "Finish" when installation completes
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

#### For macOS:

1. **Download XAMPP:**
   - Visit: [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Download XAMPP for macOS

2. **Install XAMPP:**
   ```bash
   # Open the downloaded DMG file
   # Drag XAMPP icon to Applications folder
   # Open XAMPP from Applications
   ```

3. **Start Services:**
   - Open XAMPP Manager
   - Click "Start" for Apache and MySQL
   - Enter your macOS password if prompted

#### For Linux:

```bash
# Download XAMPP
wget https://www.apachefriends.org/xampp-files/8.2.12/xampp-linux-x64-8.2.12-0-installer.run

# Make it executable
chmod +x xampp-linux-x64-8.2.12-0-installer.run

# Install (requires sudo)
sudo ./xampp-linux-x64-8.2.12-0-installer.run

# Start XAMPP
sudo /opt/lampp/lampp start
```

---

### Step 2: Download Project Files

#### Method 1: Download ZIP from GitHub

1. **Download the Project:**
   - Go to the GitHub repository
   - Click the green **"Code"** button
   - Select **"Download ZIP"**
   - Save the file (e.g., `baf-parade-system-main.zip`)

2. **Extract Files:**

   **Windows:**
   ```
   - Right-click the downloaded ZIP file
   - Select "Extract All..."
   - Choose destination: C:\xampp\htdocs\
   - The folder will be extracted (e.g., baf-parade-system-main)
   - Rename folder to: baf_parade_system
   ```

   **macOS/Linux:**
   ```bash
   # Navigate to downloads
   cd ~/Downloads
   
   # Extract ZIP
   unzip baf-parade-system-main.zip
   
   # Move to XAMPP htdocs
   # For macOS:
   sudo mv baf-parade-system-main /Applications/XAMPP/htdocs/baf_parade_system
   
   # For Linux:
   sudo mv baf-parade-system-main /opt/lampp/htdocs/baf_parade_system
   
   # Set permissions
   sudo chmod -R 755 /path/to/htdocs/baf_parade_system
   ```

#### Method 2: Clone with Git (Alternative)

```bash
# If you have Git installed
cd C:\xampp\htdocs  # Windows
cd /Applications/XAMPP/htdocs  # macOS
cd /opt/lampp/htdocs  # Linux

# Clone repository
git clone https://github.com/yourusername/baf-parade-system.git baf_parade_system
```

3. **Verify File Structure:**
   ```
   C:\xampp\htdocs\baf_parade_system\  (Windows)
   /Applications/XAMPP/htdocs/baf_parade_system/  (macOS)
   /opt/lampp/htdocs/baf_parade_system/  (Linux)
   
   Should contain:
   ├── index.php
   ├── config.php
   ├── functions.php
   ├── officers.php
   ├── add_officer.php
   ├── parade.php
   ├── reports.php
   ├── generate_report.php
   ├── install_tcpdf.php
   ├── composer.json
   ├── database_schema.sql
   └── README.md
   ```

---

### Step 3: Setup Database

#### Option 1: Using phpMyAdmin (Recommended for Beginners)

1. **Open phpMyAdmin:**
   - Open your browser
   - Visit: `http://localhost/phpmyadmin`
   - You should see the phpMyAdmin interface

2. **Create Database:**
   ```
   - Click on "New" in the left sidebar
   - Database name: baf_parade_system
   - Collation: utf8mb4_general_ci
   - Click "Create"
   ```

3. **Import Database Schema:**
   ```
   - Select the "baf_parade_system" database from left sidebar
   - Click on "Import" tab at the top
   - Click "Choose File" button
   - Navigate to: C:\xampp\htdocs\baf_parade_system\database_schema.sql
   - Select the file and click "Open"
   - Scroll down and click "Go" button
   - You should see "Import has been successfully finished"
   ```

4. **Verify Database Tables:**
   ```
   - Click on "baf_parade_system" database
   - You should see 3 tables:
     ✓ officers
     ✓ parade_states
     ✓ reports
   - Click on "officers" to see sample data
   ```

#### Option 2: Using MySQL Command Line

```bash
# Windows (open Command Prompt as Administrator)
cd C:\xampp\mysql\bin
mysql -u root -p

# macOS/Linux (open Terminal)
/Applications/XAMPP/bin/mysql -u root -p  # macOS
/opt/lampp/bin/mysql -u root -p  # Linux

# Then run these commands:
CREATE DATABASE baf_parade_system CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE baf_parade_system;
SOURCE C:/xampp/htdocs/baf_parade_system/database_schema.sql;  # Windows
SOURCE /Applications/XAMPP/htdocs/baf_parade_system/database_schema.sql;  # macOS
SOURCE /opt/lampp/htdocs/baf_parade_system/database_schema.sql;  # Linux

# Verify tables
SHOW TABLES;
SELECT COUNT(*) FROM officers;

# Exit MySQL
EXIT;
```

---

### Step 4: Configure the System

1. **Edit Database Configuration:**

   **Open the config.php file:**
   - Navigate to: `C:\xampp\htdocs\baf_parade_system\config.php`
   - Open with Notepad, VS Code, or any text editor

   **Default configuration:**
   ```php
   <?php
   $host = 'localhost';
   $dbname = 'baf_parade_system';
   $username = 'root';
   $password = '';  // Leave empty for default XAMPP
   ```

   **If you set a MySQL password:**
   ```php
   $password = 'your_password_here';
   ```

2. **Save the file** (Ctrl+S or Cmd+S)

---

### Step 5: Install TCPDF for PDF Generation

PDF generation requires the TCPDF library. Choose one method:

#### Method 1: Auto-Installer (Easiest)

1. **Run the Auto-Installer:**
   - Open browser
   - Visit: `http://localhost/baf_parade_system/install_tcpdf.php`
   - Click the install button
   - Wait for installation to complete (30-60 seconds)
   - You'll see: "✅ TCPDF installation completed successfully!"

#### Method 2: Manual Installation

1. **Download TCPDF:**
   - Visit: [https://github.com/tecnickcom/TCPDF/archive/refs/heads/main.zip](https://github.com/tecnickcom/TCPDF/archive/refs/heads/main.zip)
   - Download the ZIP file

2. **Extract and Install:**
   ```
   Windows:
   - Extract the downloaded ZIP
   - Rename folder from "TCPDF-main" to "tcpdf"
   - Move to: C:\xampp\htdocs\baf_parade_system\tcpdf\
   
   macOS/Linux:
   unzip TCPDF-main.zip
   mv TCPDF-main tcpdf
   mv tcpdf /path/to/htdocs/baf_parade_system/
   ```

#### Method 3: Composer (For Advanced Users)

```bash
# Install Composer first (if not installed)
# Visit: https://getcomposer.org/download/

# Navigate to project directory
cd C:\xampp\htdocs\baf_parade_system  # Windows
cd /Applications/XAMPP/htdocs/baf_parade_system  # macOS
cd /opt/lampp/htdocs/baf_parade_system  # Linux

# Install dependencies
composer install
```

3. **Verify TCPDF Installation:**
   - Check if folder exists: `baf_parade_system/tcpdf/tcpdf.php`
   - OR: `baf_parade_system/vendor/tecnickcom/tcpdf/tcpdf.php`

---

### Step 6: Test the Installation

#### 1. Access the System

Open your browser and visit:
```
http://localhost/baf_parade_system
```

You should see the **Dashboard** with:
- ✅ Welcome header with BAF branding
- ✅ Statistics cards showing total officers
- ✅ Navigation menu
- ✅ Quick action buttons

#### 2. Test Each Module

**a) Dashboard Test:**
```
URL: http://localhost/baf_parade_system/index.php
Expected: Statistics, department breakdown, recent activity
```

**b) Officers Management:**
```
URL: http://localhost/baf_parade_system/officers.php
Expected: List of sample officers with search/filter options
```

**c) Add New Officer:**
```
URL: http://localhost/baf_parade_system/add_officer.php
Try adding:
- Name: Test Officer
- Rank: Flg Offr
- Department: CSE
- Level: II
- Mess Location: MIST Dhaka Mess
- Gender: Male
Click "Add Officer"
Expected: Success message
```

**d) Parade State Management:**
```
URL: http://localhost/baf_parade_system/parade.php
Expected: List of all officers with attendance status dropdowns
Try:
- Mark a few officers as "Leave" or "CMH"
- Add remarks
- Click "Update Attendance"
Expected: Success message
```

**e) Generate PDF Report:**
```
URL: http://localhost/baf_parade_system/generate_report.php?date=2025-01-15
Expected: PDF report opens in browser
Should show: Complete parade state report in BAF format
```

**f) Reports Dashboard:**
```
URL: http://localhost/baf_parade_system/reports.php
Expected: List of generated reports with statistics
```

---

## 📚 Usage Guide

### Daily Operations

#### 1. Mark Daily Attendance
```
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
```

#### 2. Generate Daily Report
```
1. Go to: Reports Dashboard
2. Select date
3. Click "Generate PDF Report"
4. Print or save PDF
```

#### 3. Add New Officers
```
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
```

#### 4. View Statistics
```
1. Dashboard shows:
   - Total officers
   - Present today
   - Absent today
   - Female officers
2. Department breakdowns
3. Recent activity
```

---

## 🔧 Troubleshooting

### Common Issues and Solutions

#### Issue 1: "Cannot connect to database"

**Solution:**
```
1. Check if MySQL is running in XAMPP Control Panel
2. Verify config.php has correct credentials:
   - $host = 'localhost';
   - $username = 'root';
   - $password = '';  (empty by default)
3. Test database connection:
   - Visit: http://localhost/phpmyadmin
   - Check if "baf_parade_system" database exists
```

#### Issue 2: "Page not found" or "404 Error"

**Solution:**
```
1. Verify XAMPP Apache is running (green in Control Panel)
2. Check folder location:
   - Should be: C:\xampp\htdocs\baf_parade_system\
   - NOT: C:\xampp\htdocs\baf-parade-system-main\
3. Use correct URL: http://localhost/baf_parade_system (not .com)
```

#### Issue 3: "Failed to load PDF document"

**Solution:**
```
1. Install TCPDF:
   - Visit: http://localhost/baf_parade_system/install_tcpdf.php
   - Click install button
   - Wait for completion

2. Verify installation:
   - Check folder: baf_parade_system/tcpdf/
   - Should contain: tcpdf.php file

3. Test PDF generation:
   - Visit: http://localhost/baf_parade_system/generate_report.php?date=2025-01-15
```

#### Issue 4: "Access Denied" or Permission Issues

**Windows:**
```
- Right-click folder: C:\xampp\htdocs\baf_parade_system
- Properties → Security tab
- Make sure your user has "Full control"
```

**macOS/Linux:**
```bash
sudo chmod -R 755 /path/to/htdocs/baf_parade_system
sudo chown -R your_username:your_group /path/to/htdocs/baf_parade_system
```

#### Issue 5: "Blank white page"

**Solution:**
```
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
```

#### Issue 6: "Table doesn't exist"

**Solution:**
```
1. Re-import database:
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Select "baf_parade_system" database
   - Click "Import" tab
   - Choose database_schema.sql file
   - Click "Go"

2. Verify tables exist:
   - Should see: officers, parade_states, reports
```

#### Issue 7: Slow Performance

**Solution:**
```
1. Increase PHP memory limit:
   - Edit: C:\xampp\php\php.ini (Windows)
   - Find: memory_limit = 128M
   - Change to: memory_limit = 256M
   - Restart Apache

2. Close unnecessary applications
3. Clear browser cache (Ctrl+Shift+Delete)
```

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
   ```
   - Operating System: Windows/macOS/Linux
   - XAMPP Version: (check Control Panel)
   - PHP Version: (visit http://localhost/dashboard/)
   - Error Messages: (exact text)
   - Steps to Reproduce: (what you did)
   - Screenshots: (if applicable)
   ```

---

## 🔄 Updating the System

### To update to a newer version:

1. **Backup Current Installation:**
   ```
   - Export database via phpMyAdmin
   - Copy baf_parade_system folder to safe location
   ```

2. **Download New Version:**
   ```
   - Download latest ZIP from GitHub
   - Extract to temporary location
   ```

3. **Replace Files:**
   ```
   - Keep your config.php (don't overwrite)
   - Replace all other PHP files
   - Keep tcpdf/ folder if already installed
   ```

4. **Update Database:**
   ```
   - Check if new database_schema.sql includes updates
   - Import only new tables/columns if needed
   ```

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
  - zip (for TCPDF installation)

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
- TCPDF Library: [https://tcpdf.org/](https://tcpdf.org/)
- Bootstrap Framework: [https://getbootstrap.com/](https://getbootstrap.com/)

---

## 📌 Quick Reference Card

### Essential URLs:
```
Dashboard:          http://localhost/baf_parade_system/
Officers:           http://localhost/baf_parade_system/officers.php
Add Officer:        http://localhost/baf_parade_system/add_officer.php
Parade State:       http://localhost/baf_parade_system/parade.php
Reports:            http://localhost/baf_parade_system/reports.php
Generate PDF:       http://localhost/baf_parade_system/generate_report.php
Install TCPDF:      http://localhost/baf_parade_system/install_tcpdf.php
phpMyAdmin:         http://localhost/phpmyadmin
```

### XAMPP Control:
```
Start Apache:       Click "Start" next to Apache
Start MySQL:        Click "Start" next to MySQL
Stop Services:      Click "Stop" buttons
View Logs:          Click "Logs" buttons
Config Files:       Click "Config" buttons
```

### File Locations:
```
Windows:
- Project: C:\xampp\htdocs\baf_parade_system\
- Config: C:\xampp\php\php.ini
- Logs: C:\xampp\apache\logs\

macOS:
- Project: /Applications/XAMPP/htdocs/baf_parade_system/
- Config: /Applications/XAMPP/etc/php.ini
- Logs: /Applications/XAMPP/logs/

Linux:
- Project: /opt/lampp/htdocs/baf_parade_system/
- Config: /opt/lampp/etc/php.ini
- Logs: /opt/lampp/logs/
```

---

**Installation Complete! 🎉**

Your BAF Parade State Management System is now ready to use.

For questions or issues, refer to the Troubleshooting section above.

**Version:** 1.0.0  
**Last Updated:** January 2025  
**Developed for:** Bangladesh Air Force