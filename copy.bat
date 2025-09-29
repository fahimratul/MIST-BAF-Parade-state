@echo off
echo Checking if C:\xampp\htdocs\paradestate directory exists...

REM Check if the destination directory exists, if not create it
if not exist "C:\xampp\htdocs\paradestate" (
    echo Directory does not exist. Creating C:\xampp\htdocs\paradestate...
    mkdir "C:\xampp\htdocs\paradestate"
    if exist "C:\xampp\htdocs\paradestate" (
        echo Directory created successfully!
    ) else (
        echo Error: Failed to create directory!
        pause
        exit /b 1
    )
) else (
    echo Directory already exists.
)

echo.
echo Copying files from paradestate_setup to C:\xampp\htdocs\paradestate...

REM Copy all files from paradestate_setup folder to the destination directory
xcopy "paradestate_setup\*" "C:\xampp\htdocs\paradestate\" /Y /E /H /R

echo.
echo Copy operation completed!
echo All files have been copied from paradestate_setup to C:\xampp\htdocs\paradestate
pause