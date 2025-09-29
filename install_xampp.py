import os
import sys
import subprocess
import requests
from pathlib import Path
import time
import ctypes
import tempfile

def is_admin():
    """Check if the script is running with administrator privileges"""
    try:
        return ctypes.windll.shell32.IsUserAnAdmin()
    except:
        return False

def run_as_admin():
    """Restart the script with administrator privileges"""
    if is_admin():
        return True
    else:
        print("This script requires administrator privileges.")
        print("Attempting to restart with administrator privileges...")
        try:
            # Re-run the program with admin rights
            if getattr(sys, 'frozen', False):
                # Running as executable
                ctypes.windll.shell32.ShellExecuteW(
                    None, "runas", sys.executable, "", None, 1
                )
            else:
                # Running as script
                ctypes.windll.shell32.ShellExecuteW(
                    None, "runas", sys.executable, " ".join(sys.argv), None, 1
                )
            return False
        except Exception as e:
            print(f"Failed to restart with admin privileges: {e}")
            input("Press Enter to exit...")
            return False

def download_xampp(url, download_path):
    """Download XAMPP installer with progress indicator"""
    print("Downloading XAMPP installer...")
    print("This may take several minutes depending on your internet connection.")
    print(f"Download URL: {url}")
    print()
    
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    }
    
    try:
        with requests.get(url, headers=headers, stream=True) as response:
            response.raise_for_status()
            total_size = int(response.headers.get('content-length', 0))
            
            with open(download_path, 'wb') as file:
                downloaded = 0
                for chunk in response.iter_content(chunk_size=8192):
                    if chunk:
                        file.write(chunk)
                        downloaded += len(chunk)
                        if total_size > 0:
                            percent = (downloaded / total_size) * 100
                            print(f"\rProgress: {percent:.1f}% ({downloaded:,} / {total_size:,} bytes)", end='')
        
        print(f"\n\nDownload completed successfully!")
        print(f"File saved to: {download_path}")
        
        # Verify file size
        file_size = os.path.getsize(download_path)
        print(f"Downloaded file size: {file_size:,} bytes")
        
        if file_size < 100000:  # 100KB threshold
            print("Error: Downloaded file appears to be too small. Download may have failed.")
            return False
            
        return True
        
    except requests.exceptions.RequestException as e:
        print(f"Error downloading XAMPP: {e}")
        return False

def install_xampp():
    """Main XAMPP installation function"""
    print("=" * 50)
    print("XAMPP Automatic Download and Installer (Python)")
    print("=" * 50)
    print()
    
    # Check administrator privileges
    if not is_admin():
        if not run_as_admin():
            return False
    
    print("Running with administrator privileges...")
    print()
    
    # Set variables
    XAMPP_VERSION = "8.2.12"
    XAMPP_URL = f"https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/{XAMPP_VERSION}/xampp-windows-x64-{XAMPP_VERSION}-0-VS16-installer.exe/download"
    DOWNLOAD_PATH = os.path.join(tempfile.gettempdir(), "xampp-installer.exe")
    INSTALL_PATH = Path("C:/xampp")
    
    # Check if XAMPP is already installed
    print("Checking if XAMPP is already installed...")
    xampp_control = INSTALL_PATH / "xampp-control.exe"
    
    if xampp_control.exists():
        print(f"✅ XAMPP is already installed at {INSTALL_PATH}")
        print("Starting XAMPP Control Panel...")
        start_xampp(INSTALL_PATH)
        return True
    
    print()
    
    # Download XAMPP
    if not download_xampp(XAMPP_URL, DOWNLOAD_PATH):
        return False
    
    print()
    print("Starting XAMPP installation...")
    print()
    print("IMPORTANT NOTES:")
    print("- The installer will open in a separate window")
    print("- Choose your installation directory (default: C:\\xampp)")
    print("- Select components to install (Apache, MySQL, PHP, etc.)")
    print("- Wait for the installation to complete")
    print()
    print("Auto-starting installation...")
    
    # Run the installer
    print("Running XAMPP installer...")
    try:
        subprocess.run([DOWNLOAD_PATH], check=True)
    except subprocess.CalledProcessError:
        print("Installation may have been cancelled by user.")
    except Exception as e:
        print(f"Error running installer: {e}")
    
    # Check if installation was successful
    if xampp_control.exists():
        print()
        print("=" * 50)
        print("XAMPP Installation completed successfully!")
        print("=" * 50)
        print()
        print(f"XAMPP has been installed to: {INSTALL_PATH}")
        print()
        start_xampp(INSTALL_PATH)
    else:
        print()
        print("Installation may have failed or was cancelled.")
        print("Please check if XAMPP was installed correctly.")
    
    # Cleanup
    cleanup(DOWNLOAD_PATH)
    return True

def start_xampp(install_path):
    """Start XAMPP Control Panel"""
    print()
    print("=" * 50)
    print("Starting XAMPP Control Panel...")
    print("=" * 50)
    print()
    print(f"XAMPP Location: {install_path}")
    print(f"Your web files should be placed in: {install_path}/htdocs")
    print()
    
    xampp_control = install_path / "xampp-control.exe"
    
    if xampp_control.exists():
        print("Launching XAMPP Control Panel...")
        try:
            subprocess.Popen([str(xampp_control)])
            print()
            print("XAMPP Control Panel has been started!")
            print()
            print("Please check the XAMPP Control Panel window to:")
            print("1. Start Apache service (if not already started)")
            print("2. Start MySQL service (if not already started)")
            print("3. Access your local server at: http://localhost")
            print()
        except Exception as e:
            print(f"Error starting XAMPP Control Panel: {e}")
    else:
        print(f"Error: XAMPP Control Panel not found at {xampp_control}")

def cleanup(download_path):
    """Clean up temporary files"""
    print()
    print("Cleaning up temporary files...")
    try:
        if os.path.exists(download_path):
            os.remove(download_path)
            print("Temporary installer file deleted.")
    except Exception as e:
        print(f"Could not delete temporary file: {e}")

def main():
    """Main function"""
    try:
        success = install_xampp()
        
        print()
        print("=" * 50)
        print("Script completed!")
        print("=" * 50)
        if success:
            print("XAMPP should now be running.")
            print("Access your local server at: http://localhost")
            print("Place your PHP files in: C:/xampp/htdocs")
        else:
            print("Installation failed. Please check the error messages above.")
            input("Press Enter to exit...")
        print()
        
        # Auto-exit on success (no input required)
        if success:
            print("Auto-closing in 3 seconds...")
            time.sleep(3)
        
    except KeyboardInterrupt:
        print("\n\nOperation cancelled by user.")
        input("Press Enter to exit...")
    except Exception as e:
        print(f"\nUnexpected error: {e}")
        input("Press Enter to exit...")

if __name__ == "__main__":
    main()