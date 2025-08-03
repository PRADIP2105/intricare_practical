# Project Setup Guide

This document provides step-by-step instructions for setting up and running this Laravel project.

## System Requirements

- PHP >= 8.2
- Composer >= 2.0
- Node.js >= 16.0
- NPM >= 8.0
- SQLite (default database) OR MySQL/MariaDB/PostgreSQL/SQL Server

## Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/PRADIP2105/intricare_practical.git
cd intricare_practical
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Environment Configuration

Copy the example environment file and generate application key:

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Database Setup

#### Option A: SQLite (Default - Recommended for quick setup)

1. Create the SQLite database file:
   ```bash
   touch database/database.sqlite
   ```

2. Update your `.env` file:
   ```env
   DB_CONNECTION=sqlite
   # Remove or comment out DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
   ```

#### Option B: MySQL/MariaDB

1. Create a database in your MySQL/MariaDB server.

2. Update your `.env` file:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

### 6. Run Database Migrations

```bash
php artisan migrate
```

### 7. Run Database Seeders (Optional)

To populate the database with sample data:

```bash
php artisan db:seed
```

### 8. Compile Assets

```bash
npm run dev
```

### 9. Start the Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Development Commands

- **Run development server**: `php artisan serve`
- **Watch for asset changes**: `npm run dev`
- **Run development server with all services**: `composer run dev`
- **Run tests**: `php artisan test` or `composer test`

## Project Features

This Laravel application includes:

1. User Authentication (Registration, Login, Password Reset)
2. Contact Management System with:
   - Create, Read, Update, Delete operations
   - Custom fields for contacts
   - Contact merging functionality
   - Soft deletes for contacts
   - File uploads (profile images, additional files)
3. Profile Management
4. Responsive UI using Tailwind CSS

## Default User Credentials (if seeded)

If you ran the seeders, you can use these credentials to log in:
- Email: `admin@example.com`
- Password: `password`

## Troubleshooting

1. **Permission issues**: Make sure the `storage` and `bootstrap/cache` directories are writable:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

2. **Asset compilation issues**: Clear the cache and recompile:
   ```bash
   npm run build
   ```

3. **Database connection issues**: Verify your database credentials in the `.env` file.

4. **Migration issues**: Reset and re-run migrations:
   ```bash
   php artisan migrate:fresh
   ```

## Additional Information

- The application uses SQLite as the default database for easy setup
- Tailwind CSS is used for styling
- Laravel Breeze provides the authentication scaffolding
- The contact management system supports custom fields and file uploads
