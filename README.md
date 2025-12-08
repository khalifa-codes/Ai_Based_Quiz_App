# QuizAura - AI-Powered Quiz & Exam System

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-orange.svg)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-Proprietary-red.svg)](LICENSE)

A comprehensive, multi-tenant, AI-assisted secure quiz platform designed for high-stakes online examinations with real-time invigilation, AI-based subjective grading, and tenant-level analytics.

## 🚀 Features

### Core Features
- ✅ **AI-Powered Evaluation** - Automatic grading of subjective questions using advanced AI models (Gemini, Groq, Perplexity)
- ✅ **Multi-Tenant Architecture** - Isolated data and branding for each organization
- ✅ **Anti-Cheating System** - Comprehensive security measures including tab switching detection, IP tracking, and real-time monitoring
- ✅ **White-Label Branding** - Fully customizable themes, logos, and colors per organization
- ✅ **Real-Time Analytics** - Comprehensive dashboards with performance metrics
- ✅ **DOCX Quiz Upload** - Upload quizzes directly from Microsoft Word documents
- ✅ **Session Management** - Secure session handling with timeout and regeneration
- ✅ **Rate Limiting** - Protection against abuse and DDoS attacks

### Security Features
- Session timeout (15 minutes inactivity)
- IP address tracking and validation
- Audit logging for all actions
- CSRF protection
- SQL injection prevention
- XSS protection
- Rate limiting per user/IP
- Data retention policies

## 📋 Prerequisites

- **PHP**: 8.0 or higher
- **MySQL**: 8.0 or higher
- **Python**: 3.x (for DOCX processing scripts)
- **Web Server**: Apache/Nginx with mod_rewrite enabled
- **Extensions**: PDO, PDO_MySQL, JSON, cURL, mbstring

## 🔧 Installation

### 1. Clone Repository

```bash
git clone https://github.com/yourusername/quizaura.git
cd quizaura
```

### 2. Database Setup

#### Create Database
```bash
mysql -u root -p < database/schema.sql
```

Or manually:
```sql
CREATE DATABASE quiz_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Import Schema
```bash
mysql -u root -p quiz_system < database/schema.sql
```

### 3. Configuration

#### Database Configuration
```bash
# Copy example file
cp config/database_config.php.example config/database_config.php

# Edit with your database credentials
nano config/database_config.php
```

Update the following in `config/database_config.php`:
```php
return [
    'host' => 'localhost',
    'port' => 3307,  // Your MySQL port
    'dbname' => 'quiz_system',
    'username' => 'root',
    'password' => 'your_password',
    'charset' => 'utf8mb4',
    'show_errors' => false
];
```

#### AI Configuration
```bash
# Copy example file
cp config/ai_config.php.example config/ai_config.php

# Edit with your API keys
nano config/ai_config.php
```

Get API Keys:
- **Gemini**: https://makersuite.google.com/app/apikey (Free tier available)
- **Groq**: https://console.groq.com/keys (Free tier available)
- **Perplexity**: https://www.perplexity.ai/settings/api (Paid)

### 4. Storage Directories

Ensure storage directories exist and are writable:
```bash
mkdir -p storage/uploads storage/quiz_json storage/logs
chmod 755 storage/uploads storage/quiz_json storage/logs
```

### 5. Web Server Configuration

#### Apache (.htaccess)
Ensure `.htaccess` files are enabled:
```apache
<Directory /path/to/quizaura>
    AllowOverride All
    Require all granted
</Directory>
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

### 6. Python Dependencies (for DOCX processing)

```bash
pip install python-docx
```

## 🎯 Quick Start

### Initial Setup

1. **Configure Database**: Update `config/database_config.php`
2. **Configure AI**: Update `config/ai_config.php` with API keys
3. **Set Permissions**: Ensure storage directories are writable
4. **Access Application**: Navigate to `http://your-domain.com`

### Default Accounts

After database setup, create accounts through registration:
- **Admin**: Register at `/admin/login.php`
- **Organization**: Register at `/register.php`
- **Teacher**: Created by Organization
- **Student**: Created by Organization

## 📁 Project Structure

```
quizaura/
├── admin/                 # Admin module
│   ├── dashboard.php
│   ├── organizations/
│   ├── plans/
│   └── security/
├── api/                   # API endpoints
│   ├── admin/
│   ├── organization/
│   ├── student/
│   ├── teacher/
│   └── ai/
├── assets/                # Static assets
│   ├── css/
│   ├── js/
│   └── images/
├── config/                # Configuration files
│   ├── database.php
│   ├── database_config.php.example
│   └── ai_config.php.example
├── database/              # Database schema
│   └── schema.sql
├── includes/              # Shared includes
│   ├── ai_service.php
│   ├── branding_loader.php
│   └── security_helpers.php
├── organization/          # Organization module
│   ├── dashboard.php
│   ├── students/
│   ├── teachers/
│   └── settings.php
├── scripts/               # Utility scripts
│   └── process_quiz_docx.py
├── storage/               # Storage directories
│   ├── uploads/          # Uploaded files
│   ├── quiz_json/        # Processed quiz data
│   └── logs/             # Application logs
├── student/               # Student module
│   ├── dashboard.php
│   ├── quizzes/
│   └── results/
├── teacher/               # Teacher module
│   ├── dashboard.php
│   ├── quizzes/
│   └── results/
├── .gitignore
├── index.php              # Landing page
├── login.php              # Public login
├── register.php           # Public registration
└── README.md
```

## 🔐 Security Configuration

### Production Checklist

- [ ] Update `config/database_config.php` with production credentials
- [ ] Update `config/ai_config.php` with production API keys
- [ ] Set `show_errors` to `false` in database config
- [ ] Set `disable_ssl_verification` to `false` in AI config
- [ ] Ensure storage directories have proper permissions (755)
- [ ] Configure proper session settings in PHP
- [ ] Enable HTTPS/SSL
- [ ] Set secure cookie flags
- [ ] Configure rate limiting thresholds
- [ ] Review and update security headers

### Environment Variables (Optional)

You can use environment variables instead of config files:

```bash
export DB_HOST=localhost
export DB_PORT=3307
export DB_NAME=quiz_system
export DB_USER=root
export DB_PASS=your_password
export GEMINI_API_KEY=your_key
export GROQ_API_KEY=your_key
export PERPLEXITY_API_KEY=your_key
```

## 📊 Database Schema

### Core Tables
- `admins` - Super admin accounts
- `admin_credits` - Admin credits tracking
- `organizations` - Organization accounts
- `organization_branding` - Organization themes
- `plans` - Subscription plans
- `teachers` - Teacher accounts
- `students` - Student accounts
- `quizzes` - Quiz definitions
- `questions` - Quiz questions
- `question_options` - MCQ options
- `quiz_submissions` - Student submissions
- `student_answers` - Individual answers
- `ai_evaluations` - AI grading results
- `audit_logs` - Security audit trail
- `rate_limits` - Rate limiting

See `database/schema.sql` for complete schema.

## 🚀 Deployment

### Production Deployment Steps

1. **Clone Repository**
   ```bash
   git clone https://github.com/yourusername/quizaura.git
   cd quizaura
   ```

2. **Configure Environment**
   ```bash
   cp config/database_config.php.example config/database_config.php
   cp config/ai_config.php.example config/ai_config.php
   # Edit both files with production values
   ```

3. **Set Permissions**
   ```bash
   chmod 755 storage/uploads storage/quiz_json storage/logs
   chown www-data:www-data storage/uploads storage/quiz_json storage/logs
   ```

4. **Database Migration**
   ```bash
   mysql -u root -p quiz_system < database/schema.sql
   ```

5. **Verify Configuration**
   - Check database connection
   - Verify API keys
   - Test file uploads
   - Check storage permissions

### Docker Deployment (Optional)

```dockerfile
# Dockerfile example
FROM php:8.0-apache
RUN docker-php-ext-install pdo pdo_mysql
COPY . /var/www/html/
RUN chmod -R 755 /var/www/html/storage
```

## 🧪 Testing

### Manual Testing Checklist

See detailed testing guide in project documentation.

### Key Areas to Test
- [ ] User authentication (all roles)
- [ ] Quiz creation and upload
- [ ] Quiz taking interface
- [ ] AI evaluation
- [ ] Results display
- [ ] Branding customization
- [ ] Security features
- [ ] API endpoints

## 📝 API Documentation

### Student APIs
- `POST /api/student/quiz/start.php` - Start quiz
- `POST /api/student/quiz/submit-question.php` - Submit answer
- `POST /api/student/submit_quiz.php` - Submit complete quiz
- `GET /api/student/get_quiz_result.php` - Get quiz result

### Teacher APIs
- `POST /api/teacher/upload_quiz_docx.php` - Upload quiz file
- `POST /api/teacher/create_quiz.php` - Create quiz manually

### Organization APIs
- `POST /api/organization/register.php` - Register organization
- `POST /api/organization/branding_update.php` - Update branding
- `GET /api/organization/branding_get.php` - Get branding

### Admin APIs
- `POST /api/admin/login.php` - Admin login
- `GET /api/admin/dashboard_stats.php` - Dashboard statistics

## 🛠️ Development

### Code Structure
- **MVC Pattern**: Separation of concerns
- **Singleton Pattern**: Database connections
- **Helper Functions**: Reusable utilities
- **API Endpoints**: RESTful design

### Coding Standards
- PSR-12 coding style
- Meaningful variable names
- Comprehensive comments
- Error handling

## 📄 License

Proprietary - All rights reserved

## 👥 Contributing

This is a proprietary project. For contributions, please contact the development team.

## 🐛 Known Issues

- None currently reported

## 📞 Support

For support and questions:
- Email: support@quizaura.com
- Documentation: See project wiki
- Issues: Contact development team

## 🔄 Changelog

### Version 1.0 (December 2024)
- Initial release
- Multi-tenant architecture
- AI-powered evaluation
- White-label branding
- Security features
- Comprehensive analytics

## 🙏 Acknowledgments

- AI Providers: Google Gemini, Groq, Perplexity
- PHP Community
- Bootstrap for UI components
- All contributors

---

**Last Updated**: December 2024  
**Version**: 1.0  
**Status**: Production Ready
