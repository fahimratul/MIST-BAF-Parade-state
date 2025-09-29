import os
import shutil
import sys
from pathlib import Path

def copy_paradestate_setup():
    """
    Copy all files from paradestate_setup folder to C:/xampp/htdocs/paradestate
    Creates the destination directory if it doesn't exist.
    """
    
    # Source directory (paradestate_setup folder)
    # Try multiple locations for the source directory
    possible_locations = [
        Path(__file__).parent / "paradestate_setup",  # When running as script
        Path(sys.executable).parent / "paradestate_setup",  # When running as exe, try exe location
        Path.cwd() / "paradestate_setup",  # Current working directory
        Path("C:/Users/User/Downloads/paradestate/paradestate_setup"),  # Absolute path
    ]
    
    source_dir = None
    for location in possible_locations:
        if location.exists():
            source_dir = location
            break
    
    # Destination directory
    destination_dir = Path("C:/xampp/htdocs/paradestate")
    
    try:
        # Check if source directory exists
        if source_dir is None:
            print("Error: Could not find 'paradestate_setup' directory in any of these locations:")
            for location in possible_locations:
                print(f"  - {location}")
            print("\nPlease ensure the 'paradestate_setup' folder is in the same directory as this executable.")
            return False
        
        # Create destination directory if it doesn't exist
        destination_dir.mkdir(parents=True, exist_ok=True)
        print(f"Destination directory created/verified: {destination_dir}")
        
        # Copy all files and subdirectories
        for item in source_dir.iterdir():
            src_path = item
            dst_path = destination_dir / item.name
            
            if item.is_file():
                # Copy file
                shutil.copy2(src_path, dst_path)
                print(f"Copied file: {item.name}")
            elif item.is_dir():
                # Copy directory and all its contents
                if dst_path.exists():
                    shutil.rmtree(dst_path)
                shutil.copytree(src_path, dst_path)
                print(f"Copied directory: {item.name}")
        
        print(f"\n✅ Successfully copied all files from '{source_dir}' to '{destination_dir}'")
        return True
        
    except PermissionError as e:
        print(f"❌ Permission error: {e}")
        print("Try running the script as administrator or check if XAMPP is installed.")
        return False
    except Exception as e:
        print(f"❌ An error occurred: {e}")
        return False

def main():
    """Main function to execute the copy operation"""
    print("ParadeState Setup File Copy Utility")
    print("=" * 40)
    
    # Confirm operation with user
    
    success = copy_paradestate_setup()
    if success:
        print("\n🎉 Operation completed successfully!")
    else:
        print("\n💥 Operation failed!")
        sys.exit(1)

if __name__ == "__main__":
    main()