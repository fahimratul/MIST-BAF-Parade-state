#!/usr/bin/env python3
"""
XAMPP Automatic Download and Installer (Python Version)
Automatically installs XAMPP if not present, or opens existing installation.
"""

import os
import sys
import subprocess
import urllib.request
import ctypes
import tempfile
import time
from pathlib import Path

# Configuration
XAMPP_VERSION = "8.2.12"
XAMPP_URL = f"https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/{XAMPP_VERSION}/xampp-windows-x64-{XAMPP_VERSION}-0-VS16-installer.exe/download"
INSTALL_PATH = Path("C:/xampp")
MIN_FILE_SIZE = 100000  # Minimum expected file size in bytes

def is_admin():
    """Check if script is running with administrator privileges."""
    try:
        return ctypes.windll.shell32.IsUserAnAdmin()
    except:
        return False

def print_header(title):
    """Print a formatted header."""
    print("=" * 40)
    print(title)
    print("=" * 40)
    print()

def download_xampp(download_path):
    """Download XAMPP installer."""
    print(f"Downloading XAMPP {XAMPP_VERSION}...")
    print("This may take several minutes depending on your internet connection.")
    print(f"Download URL: {XAMPP_URL}")
    print()
    
    try:
        # Set up headers to mimic a browser request
        request = urllib.request.Request(
            XAMPP_URL,
            headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'}
        )
        
        with urllib.request.urlopen(request) as response:
            with open(download_path, 'wb') as f:
                # Download with progress indication
                total_size = int(response.headers.get('Content-Length', 0))
                downloaded = 0
                chunk_size = 8192
                
                while True:
                    chunk = response.read(chunk_size)
                    if not chunk:
                        break
                    f.write(chunk)
                    downloaded += len(chunk)
                    
                    if total_size > 0:
                        progress = (downloaded / total_size) * 100
                        print(f"\rProgress: {progress:.1f}% ({downloaded}/{total_size} bytes)", end='', flush=True)
                
                print()  # New line after progress
                
    except Exception as e:
        raise Exception(f"Failed to download XAMPP installer: {str(e)}")

def install_xampp(installer_path):
    """Run XAMPP installer."""
    print()
    print("Starting XAMPP installation...")
    print()
    print("IMPORTANT NOTES:")
    print("- The installer will open in a separate window")
    print("- Choose your installation directory (default: C:\\xampp)")
    print("- Select components to install (Apache, MySQL, PHP, etc.)")
    print("- Wait for the installation to complete")
    print()
    
    try:
        # Run installer and wait for completion
        print("Running XAMPP installer...")
        result = subprocess.run([str(installer_path)], wait=True)
        
        if result.returncode != 0:
            print(f"Warning: Installer returned code {result.returncode}")
            
    except Exception as e:
        raise Exception(f"Failed to run installer: {str(e)}")

def start_xampp():
    """Start XAMPP Control Panel."""
    xampp_control = INSTALL_PATH / "xampp-control.exe"
    
    print()
    print_header("Starting XAMPP Control Panel...")
    print(f"XAMPP Location: {INSTALL_PATH}")
    print(f"Your web files should be placed in: {INSTALL_PATH / 'htdocs'}")
    print()
    
    try:
        print("Launching XAMPP Control Panel...")
        subprocess.Popen([str(xampp_control)], shell=True)
        
        print()
        print("XAMPP Control Panel has been started!")
        print()
        print("Please check the XAMPP Control Panel window to:")
        print("1. Start Apache service (if not already started)")
        print("2. Start MySQL service (if not already started)")
        print("3. Access your local server at: http://localhost")
        print()
        
    except Exception as e:
        raise Exception(f"Failed to start XAMPP Control Panel: {str(e)}")

def cleanup_temp_files(temp_file):
    """Clean up temporary installer file."""
    try:
        if temp_file and os.path.exists(temp_file):
            os.remove(temp_file)
            print("Temporary installer file deleted.")
    except Exception as e:
        print(f"Warning: Could not delete temporary file: {str(e)}")

def wait_for_error():
    """Wait for user input when there's an error."""
    input("Press Enter to continue...")

def main():
    """Main execution function."""
    temp_installer = None
    
    try:
        print_header("XAMPP Automatic Download and Installer")
        
        # Check administrator privileges
        if not is_admin():
            print("This script requires administrator privileges.")
            print("Please right-click the Python file and 'Run as administrator'")
            print("Or run from an elevated command prompt/PowerShell.")
            wait_for_error()
            sys.exit(1)
        
        print("Running with administrator privileges...")
        print()
        
        # Check if XAMPP is already installed
        print("Checking if XAMPP is already installed...")
        xampp_control = INSTALL_PATH / "xampp-control.exe"
        
        if xampp_control.exists():
            print(f"XAMPP appears to be already installed at {INSTALL_PATH}")
            print("Starting XAMPP Control Panel without user input...")
            start_xampp()
            
            print()
            print_header("Script completed successfully!")
            print("XAMPP should now be running.")
            print("Access your local server at: http://localhost")
            print(f"Place your PHP files in: {INSTALL_PATH / 'htdocs'}")
            
            # Auto-exit after 3 seconds
            print()
            print("Console will close automatically in 3 seconds...")
            time.sleep(3)
            return
        
        # Download XAMPP installer
        temp_installer = os.path.join(tempfile.gettempdir(), "xampp-installer.exe")
        download_xampp(temp_installer)
        
        # Verify download
        if not os.path.exists(temp_installer):
            raise Exception("Downloaded installer file not found.")
        
        file_size = os.path.getsize(temp_installer)
        print()
        print("Download completed successfully!")
        print(f"File saved to: {temp_installer}")
        print(f"Downloaded file size: {file_size} bytes")
        print()
        
        if file_size < MIN_FILE_SIZE:
            raise Exception("Downloaded file appears to be too small. Download may have failed.")
        
        # Install XAMPP
        install_xampp(temp_installer)
        
        # Check if installation was successful
        if xampp_control.exists():
            print()
            print_header("XAMPP Installation completed successfully!")
            start_xampp()
        else:
            raise Exception("Installation may have failed or was cancelled. XAMPP Control Panel not found.")
        
        # Success cleanup and exit
        print()
        print("Cleaning up temporary files...")
        cleanup_temp_files(temp_installer)
        
        print()
        print_header("Script completed successfully!")
        print("XAMPP should now be running.")
        print("Access your local server at: http://localhost")
        print(f"Place your PHP files in: {INSTALL_PATH / 'htdocs'}")
        
        # Auto-exit after 3 seconds
        print()
        print("Console will close automatically in 3 seconds...")
        time.sleep(3)
        
    except Exception as e:
        print(f"\nError: {str(e)}")
        print("Please check your internet connection and try again.")
        
        # Cleanup on error
        if temp_installer:
            print()
            print("Cleaning up temporary files...")
            cleanup_temp_files(temp_installer)
        
        print()
        print("Script encountered an error and will not exit automatically.")
        wait_for_error()
        sys.exit(1)

if __name__ == "__main__":
    main()