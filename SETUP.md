# Quick Setup Guide

## For New Developers

### 1. Clone & Install
```bash
git clone https://github.com/yourusername/quizaura.git
cd quizaura
```

### 2. Configure Database
```bash
# Copy example config
cp config/database_config.php.example config/database_config.php

# Edit with your credentials
nano config/database_config.php
```

### 3. Configure AI
```bash
# Copy example config
cp config/ai_config.php.example config/ai_config.php

# Add your API keys
nano config/ai_config.php
```

### 4. Setup Database
```bash
# Create database
mysql -u root -p
CREATE DATABASE quiz_system;
exit

# Import schema
mysql -u root -p quiz_system < database/schema.sql
```

### 5. Set Permissions
```bash
chmod 755 storage/uploads storage/quiz_json storage/logs
```

### 6. Access Application
Open browser: `http://localhost/quizaura`

## File Structure Overview

```
quizaura/
├── .gitignore          # Git ignore rules
├── .gitattributes      # Git attributes
├── .editorconfig      # Editor configuration
├── .htaccess          # Apache configuration
├── README.md          # Main documentation
├── DEPLOYMENT.md      # Deployment guide
├── SETUP.md           # This file
├── CHANGELOG.md       # Version history
├── LICENSE            # License file
├── requirements.txt   # Python dependencies
├── robots.txt         # SEO configuration
├── index.php          # Landing page (DO NOT MODIFY)
├── config/            # Configuration files
│   ├── *.example      # Example configs (safe to commit)
│   └── *.php          # Actual configs (in .gitignore)
├── storage/           # Storage directories
│   ├── uploads/       # User uploads
│   ├── quiz_json/     # Processed quiz data
│   └── logs/          # Application logs
└── [modules]/         # Application modules
```

## Important Notes

1. **Never commit** `config/database_config.php` or `config/ai_config.php`
2. **Never commit** files in `storage/uploads/`, `storage/quiz_json/`, or `storage/logs/`
3. **Never modify** `index.php` (landing page) without explicit permission
4. Always use example files as templates
5. Test changes thoroughly before committing

## Common Commands

```bash
# Check what will be committed
git status

# See ignored files
git status --ignored

# Add all changes
git add .

# Commit changes
git commit -m "Your commit message"

# Push to GitHub
git push origin main
```

