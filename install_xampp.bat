@echo off
title XAMPP Installer
echo ========================================
echo XAMPP Automatic Download and Installer
echo ========================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% == 0 (
    echo Running with administrator privileges...
    echo.
) else (
    echo This script requires administrator privileges.
    echo Please right-click and "Run as administrator"
    pause
    exit /b 1
)

REM Set variables
set XAMPP_VERSION=8.2.12
set XAMPP_URL=https://sourceforge.net/projects/xampp/files/XAMPP%%20Windows/%XAMPP_VERSION%/xampp-windows-x64-%XAMPP_VERSION%-0-VS16-installer.exe/download
set DOWNLOAD_PATH=%TEMP%\xampp-installer.exe
set INSTALL_PATH=C:\xampp

echo Checking if XAMPP is already installed...
if exist "%INSTALL_PATH%\xampp-control.exe" (
    echo XAMPP appears to be already installed at %INSTALL_PATH%
    echo Do you want to reinstall? (Y/N)
    set /p choice="Enter your choice: "
    if /i not "%choice%"=="Y" (
        echo Skipping installation. Starting XAMPP Control Panel...
        goto :start_xampp
    )
)

echo.
echo Downloading XAMPP %XAMPP_VERSION%...
echo This may take several minutes depending on your internet connection.
echo Download URL: %XAMPP_URL%
echo.

REM Download XAMPP using PowerShell
powershell -Command "& { [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -Uri '%XAMPP_URL%' -OutFile '%DOWNLOAD_PATH%' -UserAgent 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36' }"

if not exist "%DOWNLOAD_PATH%" (
    echo Error: Failed to download XAMPP installer.
    echo Please check your internet connection and try again.
    pause
    exit /b 1
)

echo.
echo Download completed successfully!
echo File saved to: %DOWNLOAD_PATH%
echo.

REM Get file size for verification
for %%A in ("%DOWNLOAD_PATH%") do set SIZE=%%~zA
echo Downloaded file size: %SIZE% bytes
echo.

if %SIZE% LSS 100000 (
    echo Error: Downloaded file appears to be too small. Download may have failed.
    echo Please check your internet connection and try again.
    pause
    exit /b 1
)

echo Starting XAMPP installation...
echo.
echo IMPORTANT NOTES:
echo - The installer will open in a separate window
echo - Choose your installation directory (default: C:\xampp)
echo - Select components to install (Apache, MySQL, PHP, etc.)
echo - Wait for the installation to complete
echo.
echo Press any key to start the installation...
pause >nul

REM Run the installer
echo Running XAMPP installer...
start /wait "" "%DOWNLOAD_PATH%"

REM Check if installation was successful
if exist "%INSTALL_PATH%\xampp-control.exe" (
    echo.
    echo ========================================
    echo XAMPP Installation completed successfully!
    echo ========================================
    echo.
    echo XAMPP has been installed to: %INSTALL_PATH%
    echo.
    goto :start_xampp
) else (
    echo.
    echo Installation may have failed or was cancelled.
    echo Please check if XAMPP was installed correctly.
    goto :cleanup
)

:start_xampp
echo.
echo ========================================
echo Starting XAMPP Control Panel...
echo ========================================
echo.
echo XAMPP Location: %INSTALL_PATH%
echo Your web files should be placed in: %INSTALL_PATH%\htdocs
echo.

REM Start XAMPP Control Panel
echo Launching XAMPP Control Panel...
start "" "%INSTALL_PATH%\xampp-control.exe"

REM Wait a moment for XAMPP to start
timeout /t 3 /nobreak >nul

REM Try to start Apache and MySQL services automatically
echo.
echo Attempting to start Apache and MySQL services automatically...
echo (This may take a few moments...)

REM Use XAMPP shell commands to start services
cd /d "%INSTALL_PATH%"
if exist "apache_start.bat" (
    echo Starting Apache...
    call apache_start.bat >nul 2>&1
)
if exist "mysql_start.bat" (
    echo Starting MySQL...
    call mysql_start.bat >nul 2>&1
)

REM Alternative method using net start (if services are installed)
net start Apache2.4 >nul 2>&1
net start MySQL >nul 2>&1

echo.
echo XAMPP Control Panel has been started!
echo.
echo Please check the XAMPP Control Panel window to:
echo 1. Start Apache service (if not already started)
echo 2. Start MySQL service (if not already started)
echo 3. Access your local server at: http://localhost
echo.

REM Ask if user wants to open localhost in browser
echo Do you want to open http://localhost in your browser? (Y/N)
set /p openbrowser="Enter your choice: "
if /i "%openbrowser%"=="Y" (
    echo Opening localhost in default browser...
    start http://localhost
)

REM Ask if user wants to open htdocs folder
echo.
echo Do you want to open the htdocs folder? (Y/N)
set /p openhtdocs="Enter your choice: "
if /i "%openhtdocs%"=="Y" (
    explorer "%INSTALL_PATH%\htdocs"
)

:cleanup
REM Clean up downloaded installer
echo.
echo Cleaning up temporary files...
if exist "%DOWNLOAD_PATH%" (
    del "%DOWNLOAD_PATH%"
    echo Temporary installer file deleted.
)

echo.
echo ========================================
echo Script completed successfully!
echo ========================================
echo XAMPP should now be running.
echo Access your local server at: http://localhost
echo Place your PHP files in: %INSTALL_PATH%\htdocs
echo.
echo Press any key to exit...
pause >nul