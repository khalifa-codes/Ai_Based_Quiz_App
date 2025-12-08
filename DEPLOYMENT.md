# QuizAura Deployment Guide

## Pre-Deployment Checklist

### 1. Configuration Files
- [ ] Copy `config/database_config.php.example` to `config/database_config.php`
- [ ] Copy `config/ai_config.php.example` to `config/ai_config.php`
- [ ] Update database credentials in `database_config.php`
- [ ] Add API keys in `ai_config.php`
- [ ] Set `show_errors` to `false` in production
- [ ] Set `disable_ssl_verification` to `false` in production

### 2. File Permissions
```bash
chmod 755 storage/uploads storage/quiz_json storage/logs
chown www-data:www-data storage/uploads storage/quiz_json storage/logs
```

### 3. Database
- [ ] Create database
- [ ] Import schema: `mysql -u root -p quiz_system < database/schema.sql`
- [ ] Verify all tables created
- [ ] Test database connection

### 4. Security
- [ ] Enable HTTPS/SSL
- [ ] Review `.htaccess` files
- [ ] Check PHP security settings
- [ ] Verify session configuration
- [ ] Test CSRF protection

## Server Requirements

### Minimum Requirements
- PHP 8.0+
- MySQL 8.0+
- Apache/Nginx
- 512MB RAM
- 1GB disk space

### Recommended
- PHP 8.1+
- MySQL 8.0+
- Nginx with PHP-FPM
- 2GB+ RAM
- 10GB+ disk space

## Deployment Steps

### Step 1: Upload Files
```bash
# Via Git
git clone https://github.com/yourusername/quizaura.git
cd quizaura

# Or via FTP/SFTP
# Upload all files except config files
```

### Step 2: Configure Database
```bash
# Create database
mysql -u root -p
CREATE DATABASE quiz_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit

# Import schema
mysql -u root -p quiz_system < database/schema.sql
```

### Step 3: Configure Application
```bash
# Copy config files
cp config/database_config.php.example config/database_config.php
cp config/ai_config.php.example config/ai_config.php

# Edit config files
nano config/database_config.php
nano config/ai_config.php
```

### Step 4: Set Permissions
```bash
# Storage directories
chmod 755 storage/uploads storage/quiz_json storage/logs
chown www-data:www-data storage/uploads storage/quiz_json storage/logs

# Config files (read-only)
chmod 644 config/database_config.php config/ai_config.php
```

### Step 5: Web Server Configuration

#### Apache
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/quizaura
    
    <Directory /path/to/quizaura>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/quizaura;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### Step 6: Verify Installation
1. Access `http://your-domain.com`
2. Test registration
3. Test login
4. Verify database connection
5. Test file uploads

## Post-Deployment

### Security Hardening
1. Remove example config files
2. Set proper file permissions
3. Enable HTTPS
4. Configure firewall
5. Set up backups

### Monitoring
1. Check error logs regularly
2. Monitor storage usage
3. Review audit logs
4. Monitor API usage

## Troubleshooting

### Common Issues

**Database Connection Error**
- Check database credentials
- Verify MySQL is running
- Check port number

**File Upload Fails**
- Check storage directory permissions
- Verify PHP upload settings
- Check file size limits

**AI Evaluation Not Working**
- Verify API keys
- Check API quotas
- Review error logs

## Backup Strategy

### Database Backup
```bash
mysqldump -u root -p quiz_system > backup_$(date +%Y%m%d).sql
```

### File Backup
```bash
tar -czf backup_files_$(date +%Y%m%d).tar.gz storage/uploads storage/quiz_json
```

### Automated Backups
Set up cron jobs for regular backups.

