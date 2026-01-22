# Quick Start Guide

## Backend Setup

The backend has been successfully configured to run with PHP 8.3 and all required dependencies have been installed.

## Required Configuration

### 1. Database Configuration

Before the application can fully function, you need to configure the database connection:

1. Edit the file `/opt/tacacsgui/web/api/config.php` (or `web/api/config.php` relative to the repository root)
2. Update the following database settings:

```php
define('DB_PASSWORD', '<your_database_password_here>');
```

The default configuration expects:
- **Database Name**: `tgui`
- **Logging Database**: `tgui_log`
- **Database User**: `tgui_user`
- **Database Host**: `localhost`

### 2. Starting the Application

#### Development Mode (Testing)

For testing purposes, you can use PHP's built-in server:

```bash
cd /opt/tacacsgui/web
php -S localhost:8080 -t .
```

Then access the application at: `http://localhost:8080`

#### Production Mode

For production deployment, configure your web server (Apache/Nginx) to serve the `/opt/tacacsgui/web` directory.

Example Nginx configuration:
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /opt/tacacsgui/web;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location /api {
        try_files $uri /api/index.php$is_args$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### 3. Database Setup

The application requires MySQL/MariaDB databases to be created:

```sql
CREATE DATABASE tgui CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE DATABASE tgui_log CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE USER 'tgui_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON tgui.* TO 'tgui_user'@'localhost';
GRANT ALL PRIVILEGES ON tgui_log.* TO 'tgui_user'@'localhost';
FLUSH PRIVILEGES;
```

## Troubleshooting

### Backend Not Starting

If the backend fails to start:

1. **Check if config.php exists**: 
   ```bash
   ls -la /opt/tacacsgui/web/api/config.php
   ```
   If missing, copy from config_example.php:
   ```bash
   cp /opt/tacacsgui/web/api/config_example.php /opt/tacacsgui/web/api/config.php
   ```

2. **Check if composer dependencies are installed**:
   ```bash
   ls -la /opt/tacacsgui/web/api/vendor/
   ```
   If missing, install them:
   ```bash
   cd /opt/tacacsgui/web/api
   composer install --no-dev --optimize-autoloader
   ```

3. **Check PHP version**:
   ```bash
   php -v
   ```
   Must be PHP 8.0.0 or higher (tested with PHP 8.3.6)

4. **Check bootstrap loading**:
   ```bash
   cd /opt/tacacsgui/web/api
   php -r "require 'bootstrap/app.php'; echo 'OK';"
   ```

### Common Errors

- **"Configuration file is missing"**: Run step 1 above
- **"Class not found"**: Run step 2 above to install composer dependencies
- **Database connection errors**: Configure the database settings in config.php

## What Was Fixed

The following issues were resolved:

1. ✅ **Missing config.php file** - Created from config_example.php template
2. ✅ **Missing composer dependencies** - Installed all required PHP packages
3. ✅ **Backend startup** - The backend now starts successfully
4. ✅ **Interface accessibility** - The web interface is now accessible

## Next Steps

1. Configure the database connection in `config.php`
2. Set up the MySQL/MariaDB databases
3. Configure your web server for production deployment
4. Follow the full installation guide for additional features

For more information, visit: https://tacacsgui.com/
