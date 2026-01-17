# Setup Instructions

## Option 1: Docker Compose (Recommended)

The easiest way to run the application locally is using Docker Compose.

### Prerequisites
- Docker Desktop installed
- Git

### Quick Start

1. **Clone and enter the project:**
   ```bash
   cd zerone-japan-app
   ```

2. **Copy environment file:**
   ```bash
   cp .env.example .env
   ```

3. **Start the containers:**
   ```bash
   docker-compose up -d
   ```

4. **Wait for services to be ready** (MySQL health check takes ~30 seconds)

5. **Access the application:**
   - **App**: http://localhost:8080
   - **phpMyAdmin**: http://localhost:8081

### Docker Commands

```bash
# Start services
docker-compose up -d

# View logs
docker-compose logs -f app

# Stop services
docker-compose down

# Rebuild after Dockerfile changes
docker-compose up -d --build

# Reset database (removes all data)
docker-compose down -v
docker-compose up -d
```

### Database Setup

1. Access phpMyAdmin at http://localhost:8081
2. Import your database schema/dump
3. Or add SQL to `docker/init.sql` and restart with `docker-compose down -v && docker-compose up -d`

---

## Option 2: XAMPP/MAMP (Traditional)

## Apache Configuration for Clean URLs

To enable clean URLs (removing `index.php` from URLs), follow these steps:

### 1. Enable mod_rewrite in Apache

Open `C:\xampp\apache\conf\httpd.conf` and find this line:

```apache
#LoadModule rewrite_module modules/mod_rewrite.so
```

Remove the `#` to uncomment it:

```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

### 2. Allow .htaccess Overrides

In the same `httpd.conf` file, find the section for your document root:

```apache
<Directory "C:/xampp/htdocs">
    Options Indexes FollowSymLinks Includes ExecCGI
    AllowOverride None
    Require all granted
</Directory>
```

Change `AllowOverride None` to `AllowOverride All`:

```apache
<Directory "C:/xampp/htdocs">
    Options Indexes FollowSymLinks Includes ExecCGI
    AllowOverride All
    Require all granted
</Directory>
```

### 3. Restart Apache

Restart Apache from the XAMPP Control Panel for changes to take effect.

### 4. Test Your Application

Now you should be able to access:
- `http://localhost/forbes-skin-app/auth/login` ✅
- `http://localhost/forbes-skin-app/dashboard` ✅

Instead of:
- `http://localhost/forbes-skin-app/index.php/auth/login` ❌

## Environment Variables Setup

1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Update `.env` with your actual credentials:
   - Database credentials
   - Google OAuth credentials
   - SMTP credentials

## Database Setup

1. Create a MySQL database
2. Import the database schema files:
   - `database_quest_module.sql`
   - `enhanced_side_quest_schema.sql`
   - `role_based_permissions.sql`
   - `clear_and_replace_modules.sql`
   - `final_permission_view.sql`

3. Update `.env` with your database credentials

## Troubleshooting

### Still getting 404 errors?

1. Check if `mod_rewrite` is enabled:
   ```bash
   httpd -M | grep rewrite
   ```
   You should see: `rewrite_module (shared)`

2. Verify `.htaccess` file exists in project root

3. Check Apache error logs at: `C:\xampp\apache\logs\error.log`

4. Make sure `AllowOverride All` is set in httpd.conf

### Alternative: Use index.php in URLs

If you can't enable mod_rewrite, you can still access the application using:
```
http://localhost/forbes-skin-app/index.php/auth/login
```

Just change `config.php`:
```php
$config['index_page'] = 'index.php';
```
