@echo off
title BAF Parade State Setup and Runner
echo ========================================
echo BAF Parade State Management System
echo Database Setup and Application Runner
echo ========================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% == 0 (
    echo Running with administrator privileges...
    echo.
) else (
    echo This script requires administrator privileges to ensure proper database setup.
    echo Please right-click and "Run as administrator"
    pause
    exit /b 1
)

REM Set variables
set PROJECT_PATH=C:\xampp\htdocs\paradestate
set XAMPP_PATH=C:\xampp
set MYSQL_PATH=%XAMPP_PATH%\mysql\bin
set PHP_PATH=%XAMPP_PATH%\php
set DATABASE_NAME=baf_parade_system
set MYSQL_USER=root
set MYSQL_PASSWORD=

echo Current working directory: %CD%
echo Project path: %PROJECT_PATH%
echo XAMPP path: %XAMPP_PATH%
echo.

REM Check if XAMPP is installed
if not exist "%XAMPP_PATH%\xampp-control.exe" (
    echo ERROR: XAMPP is not installed at %XAMPP_PATH%
    echo Please install XAMPP first or update the XAMPP_PATH variable.
    pause
    exit /b 1
)

REM Check if project files exist at target location
echo Checking if paradestate folder exists at %PROJECT_PATH%...
if not exist "%PROJECT_PATH%\config.php" (
    echo Paradestate folder not found at %PROJECT_PATH%
    
    REM Check if we're running from a paradestate folder with project files
    if exist "%CD%\config.php" (
        echo Found paradestate project in current directory: %CD%
        echo Copying paradestate folder to %PROJECT_PATH%...
        
        REM Create the htdocs directory if it doesn't exist
        if not exist "C:\xampp\htdocs" (
            mkdir "C:\xampp\htdocs"
            echo Created htdocs directory.
        )
        
        REM Create the target paradestate directory
        if not exist "%PROJECT_PATH%" (
            mkdir "%PROJECT_PATH%"
            echo Created paradestate directory at %PROJECT_PATH%
        )
        
        REM Copy all files and folders from current directory to target
        echo Copying all project files and folders...
        xcopy "%CD%" "%PROJECT_PATH%" /E /I /Y /Q
        
        if %errorLevel% == 0 (
            echo Paradestate folder copied successfully to %PROJECT_PATH%!
            echo Verifying copied files...
            if exist "%PROJECT_PATH%\config.php" (
                echo Files verified successfully.
            ) else (
                echo ERROR: Files may not have copied correctly.
                pause
                exit /b 1
            )
        ) else (
            echo ERROR: Failed to copy paradestate folder.
            echo Please manually copy the paradestate folder to C:\xampp\htdocs\
            pause
            exit /b 1
        )
    ) else (
        echo ERROR: No paradestate project found in current directory.
        echo Please ensure you either:
        echo 1. Have the paradestate folder at %PROJECT_PATH%, or
        echo 2. Run this script from inside the paradestate project folder
        echo Current directory: %CD%
        pause
        exit /b 1
    )
) else (
    echo Paradestate folder found at %PROJECT_PATH%
)

echo Step 1: Verifying XAMPP Services...
echo ========================================

echo Assuming XAMPP is already running...
echo Verifying Apache and MySQL services are accessible...

REM Quick check if Apache is responding
echo Testing Apache service...
curl -s -o nul -w "%%{http_code}" http://localhost/ >temp_result.txt 2>nul
set /p apache_status=<temp_result.txt
if exist temp_result.txt del temp_result.txt

if "%apache_status%"=="200" (
    echo Apache is running and accessible.
) else (
    echo WARNING: Apache may not be running properly.
    echo Please ensure Apache is started in XAMPP Control Panel.
)

echo.
echo Step 2: Checking Database Setup...
echo ========================================

REM Check if MySQL is accessible
echo Testing MySQL connection...
"%MYSQL_PATH%\mysql.exe" -u%MYSQL_USER% -e "SELECT 1;" >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Cannot connect to MySQL server.
    echo MySQL may not be running. Please check XAMPP Control Panel:
    echo 1. Open XAMPP Control Panel
    echo 2. Start MySQL service if it's not running
    echo 3. Ensure no other MySQL services are conflicting
    pause
    exit /b 1
)
echo MySQL connection successful.

REM Check if database exists
echo Checking if database '%DATABASE_NAME%' exists...
"%MYSQL_PATH%\mysql.exe" -u%MYSQL_USER% -e "USE %DATABASE_NAME%;" >nul 2>&1
if %errorLevel% neq 0 (
    echo Database '%DATABASE_NAME%' does not exist. Creating database...
    
    REM Create database using SQL file
    if exist "%PROJECT_PATH%\database_schema.sql" (
        echo Executing database schema from database_schema.sql...
        "%MYSQL_PATH%\mysql.exe" -u%MYSQL_USER% < "%PROJECT_PATH%\database_schema.sql"
        if %errorLevel% == 0 (
            echo Database and tables created successfully!
        ) else (
            echo ERROR: Failed to create database from schema file.
            pause
            exit /b 1
        )
    ) else (
        echo ERROR: database_schema.sql file not found in project directory.
        echo Please ensure the schema file exists.
        pause
        exit /b 1
    )
) else (
    echo Database '%DATABASE_NAME%' already exists.
    echo Checking if tables exist...
    
    REM Check if main tables exist
    "%MYSQL_PATH%\mysql.exe" -u%MYSQL_USER% -D%DATABASE_NAME% -e "DESCRIBE officers;" >nul 2>&1
    if %errorLevel% neq 0 (
        echo Tables do not exist. Re-creating database structure...
        "%MYSQL_PATH%\mysql.exe" -u%MYSQL_USER% < "%PROJECT_PATH%\database_schema.sql"
        if %errorLevel% == 0 (
            echo Database tables created successfully!
        ) else (
            echo ERROR: Failed to create database tables.
            pause
            exit /b 1
        )
    ) else (
        echo Database tables exist and are accessible.
    )
)

echo.
echo Step 3: Testing Configuration...
echo ========================================

REM Test PHP configuration
echo Testing PHP configuration...
cd /d "%PROJECT_PATH%"

REM Create a temporary PHP test script
echo ^<?php > test_config.php
echo require_once 'config.php'; >> test_config.php
echo echo "Database connection successful!"; >> test_config.php
echo ?^> >> test_config.php

REM Test the configuration
"%PHP_PATH%\php.exe" test_config.php >nul 2>&1
if %errorLevel% == 0 (
    echo PHP configuration test passed.
) else (
    echo ERROR: PHP configuration test failed.
    echo Please check config.php for database connection settings.
    pause
    exit /b 1
)

REM Clean up test file
if exist "test_config.php" del "test_config.php"

echo.
echo Step 4: Starting Application...
echo ========================================

REM Check if Apache is serving files
echo Testing web server accessibility...
curl -s -o nul -w "%%{http_code}" http://localhost/paradestate/ >nul 2>&1
if %errorLevel% == 0 (
    echo Web server is accessible.
) else (
    echo Note: curl not available, but proceeding with browser launch.
)

echo Opening BAF Parade State Management System...
echo Application URL: http://localhost/paradestate/

REM Open the application in default browser
start http://localhost/paradestate/

REM Wait a moment for browser to start
timeout /t 2 /nobreak >nul

REM Note about XAMPP Control Panel
echo.
echo Note: XAMPP Control Panel should already be running.
echo If you need to monitor services, check your XAMPP Control Panel.

echo.
echo ========================================
echo Setup and Launch Completed Successfully!
echo ========================================
echo.
echo Your BAF Parade State Management System is now running!
echo.
echo Access URLs:
echo - Main Application: http://localhost/paradestate/
echo - phpMyAdmin: http://localhost/phpmyadmin/
echo - XAMPP Dashboard: http://localhost/
echo.
echo Database Information:
echo - Database Name: %DATABASE_NAME%
echo - Username: %MYSQL_USER%
echo - Password: %MYSQL_PASSWORD%
echo.
echo Project Location: %PROJECT_PATH%
echo.
echo The application should now be open in your browser.
echo If not, manually navigate to: http://localhost/paradestate/
echo.

REM Check for any common issues
echo Troubleshooting Information:
echo - If page doesn't load: Ensure Apache is running in XAMPP Control Panel
echo - If database errors: Ensure MySQL is running in XAMPP Control Panel
echo - If connection errors: Verify config.php database settings
echo - XAMPP Control Panel should show Apache and MySQL as "Running" (green)
echo.
