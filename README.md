# Zerone Japan App

A CodeIgniter 3 application for marketplace integration and business management.

## Local Development with Docker

### Prerequisites
- Docker Desktop installed
- Git

### Quick Start

```bash
# 1. Copy environment file
cp .env.example .env

# 2. Start containers
docker-compose up -d

# 3. Wait ~30 seconds for MySQL to be ready, then access:
#    - App: http://localhost:8080
#    - phpMyAdmin: http://localhost:8081
```

### Docker Commands

```bash
# Start services in background
docker-compose up -d

# View application logs
docker-compose logs -f app

# Stop services
docker-compose down

# Rebuild after Dockerfile changes
docker-compose up -d --build

# Reset database (removes all data)
docker-compose down -v && docker-compose up -d
```

### Database Setup

1. Access phpMyAdmin at http://localhost:8081
   - User: `zerone`
   - Password: `zerone_password`
2. Import your database schema/dump
3. Or add SQL to `docker/init.sql` and restart with volume reset

### Services

| Service | URL | Description |
|---------|-----|-------------|
| App | http://localhost:8080 | PHP/Apache application |
| phpMyAdmin | http://localhost:8081 | Database management |
| MySQL | localhost:3306 | Database server |

---

## Credentials & Configuration

This section contains information about sensitive credentials and configuration files for the application.

## Files

### `.env` (Required)
Environment variables file containing all sensitive credentials. **This file is gitignored and should never be committed.**

To set up:
1. Copy `.env.example` to `.env`
2. Update all values with your actual credentials

### `.env.example` (Template)
Template file showing the required environment variables. This file is safe to commit and serves as documentation for other developers.

### `client_secret.json` (Legacy - Optional)
**Note:** This file is now deprecated. The application has been migrated to use environment variables from `.env` file instead.

Legacy Google OAuth configuration file. The values from this file have been moved to the `.env` file under:
- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_PROJECT_ID`
- etc.

## Environment Variables

### Database Configuration
- `DB_HOSTNAME` - Database host address
- `DB_USERNAME` - Database username
- `DB_PASSWORD` - Database password
- `DB_DATABASE` - Database name
- `DB_DRIVER` - Database driver (default: mysqli)

### Application
- `CI_ENV` - Environment mode (development/testing/production)
- `BASE_URL` - Application base URL (auto-detected if not set)

### Google OAuth
- `GOOGLE_CLIENT_ID` - Google OAuth client ID
- `GOOGLE_CLIENT_SECRET` - Google OAuth client secret
- `GOOGLE_PROJECT_ID` - Google Cloud project ID
- `GOOGLE_AUTH_URI` - Google OAuth authorization URI
- `GOOGLE_TOKEN_URI` - Google OAuth token URI
- `GOOGLE_AUTH_PROVIDER_CERT_URL` - Google auth provider certificate URL
- `GOOGLE_REDIRECT_URI_PROD` - Production OAuth redirect URI
- `GOOGLE_REDIRECT_URI_LOCAL` - Local development OAuth redirect URI

### SMTP Email
- `SMTP_HOST` - SMTP server hostname
- `SMTP_USER` - SMTP username (email address)
- `SMTP_PASS` - SMTP password
- `SMTP_PORT_SSL` - SMTP SSL port (default: 465)
- `SMTP_PORT_TLS` - SMTP TLS port (default: 587)

## Security Notes

1. **Never commit `.env` file** - It contains sensitive credentials
2. Always use `.env.example` as a template for new environments
3. Keep credentials secure and rotate them regularly
4. Use different credentials for development, staging, and production environments
5. The `.env` file is automatically loaded by the `env_helper.php` helper function

## Usage in Code

Load environment variables using the `env()` helper function:

```php
// Load the helper (done automatically in database.php and controllers)
$this->load->helper('env');

// Get environment variable with optional default value
$db_host = env('DB_HOSTNAME', 'localhost');
$client_id = env('GOOGLE_CLIENT_ID');
```

## Migration from client_secret.json

The application has been updated to use environment variables instead of `client_secret.json`. The following controllers have been updated:

1. `Googlemeet.php` - Now uses `env('GOOGLE_CLIENT_ID')` and `env('GOOGLE_CLIENT_SECRET')`
2. `Googlemou.php` - Now uses environment variables for both OAuth and SMTP configuration
3. `database.php` - Now uses environment variables for database configuration

The `client_secret.json` file can be kept as a backup reference but is no longer actively used by the application.
