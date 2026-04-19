# Uploads Directory

This directory stores user-uploaded files for the Sapienta Test Platform.

## Subdirectories

### `/screenshots/`
- Test session screenshots captured during test attempts
- Used for anti-cheat monitoring
- Access controlled via PHP API endpoints
- Direct access blocked by `.htaccess`

## Security Notes

- All file access goes through PHP authentication
- Direct HTTP access is blocked
- Files are served via authenticated API endpoints
- Regular cleanup recommended for old files

## Permissions

Ensure the web server has write access:
- **Linux/Mac**: `chmod 755 screenshots/`
- **Windows**: Default XAMPP permissions usually work

## Storage Management

Monitor disk usage regularly:
```bash
# Check directory size
du -sh uploads/screenshots/

# Find large files
find uploads/screenshots/ -type f -size +1M
```

Recommended: Implement automatic cleanup for screenshots older than 90 days.
