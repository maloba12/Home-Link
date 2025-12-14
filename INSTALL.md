# HomeLink Installation Guide

## Quick Start

### Step 1: Database Setup

1. Open your MySQL client (phpMyAdmin, command line, or any MySQL GUI)

2. Create a new database named `homelink`

3. Import the SQL file:
   ```bash
   mysql -u root -p homelink < sql/homelink.sql
   ```
   
   Or use phpMyAdmin:
   - Select the `homelink` database
   - Go to Import tab
   - Choose `sql/homelink.sql`
   - Click Go

### Step 2: Configure Database Connection

Edit `includes/db_connect.php` and update your database credentials:

```php
$host = 'localhost';     // Your MySQL host
$dbname = 'homelink';    // Database name
$username = 'root';      // Your MySQL username
$password = '';          // Your MySQL password
```

### Step 3: Set File Permissions

Make the uploads directory writable:

```bash
chmod 755 assets/uploads
```

Or on Windows, ensure the directory is not read-only.

### Step 4: Start Your Web Server

**For Apache/PHP Built-in Server:**

```bash
# Navigate to project directory
cd /path/to/homelink

# Start PHP built-in server
php -S localhost:8000
```

**For XAMPP/WAMP/MAMP:**
- Place project in htdocs/www folder
- Start Apache and MySQL services
- Access via `http://localhost/homelink`

### Step 5: Access the Application

Open your browser and navigate to:

```
http://localhost:8000
```

Or if using XAMPP:

```
http://localhost/homelink
```

### Step 6: Login with Default Credentials

**Admin Account:**
- Username: `admin`
- Password: `admin123`

**Test User Accounts:**
- Buyer: `johnbuyer` / `password`
- Seller: `sallyseller` / `password`

## Troubleshooting

### Database Connection Issues

If you see "Connection failed" error:

1. Check MySQL service is running
2. Verify database credentials in `includes/db_connect.php`
3. Ensure database `homelink` exists

### File Upload Issues

If property image uploads fail:

1. Check `assets/uploads` directory exists
2. Set proper permissions: `chmod 755 assets/uploads`
3. Check PHP upload limits in `php.ini`:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   ```

### Image Display Issues

If images don't display:

1. Check image paths in database
2. Place sample images in `assets/images/` or `assets/uploads/`
3. Update image URLs in SQL file to match your server

## Sample Data

The SQL file includes:
- 1 Admin user
- 2 Sample users (buyer + seller)
- 3 Sample properties
- Sample amenities and images

## Production Deployment

For production environment:

1. **Secure the database credentials**
   - Use environment variables
   - Never commit credentials to version control

2. **Update upload directory**
   - Move uploads outside web root for security
   - Configure proper CORS if needed

3. **Enable error logging**
   - Disable `display_errors` in production
   - Enable error logging to file

4. **Set proper file permissions**
   ```bash
   chmod 644 *.php
   chmod 755 uploads/
   ```

5. **Configure HTTPS**
   - Use SSL certificate
   - Force HTTPS redirect

6. **Backup regularly**
   - Set up automated database backups
   - Keep uploads directory backed up

## Additional Configuration

### Email Setup (for future notifications)

To enable email features, configure PHP mailer in `includes/email_config.php`:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-password');
```

### Security Enhancements

1. **Change default passwords**
   ```sql
   UPDATE users SET password = '$2y$10$...' WHERE username = 'admin';
   ```

2. **Add CSRF protection**
   - Implement CSRF tokens for forms

3. **Rate limiting**
   - Add rate limiting for API endpoints

## Support

For issues or questions:
- Check README.md for general information
- Review code comments in PHP files
- Check browser console for JavaScript errors

## Version

**Current Version**: 1.0.0  
**Release Date**: 2024

