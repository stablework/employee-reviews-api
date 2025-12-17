# Employee Reviews API

A REST API service for managing employee reviews across an organizational hierarchy. Built with **Laravel** and **MySQL**.

## Overview

This API enables employees to review projects, team members, and managers with comprehensive role-based access control. The system supports multiple organizational teams, cross-team collaboration on projects, and temporary advisory roles.

## Key Features

- **Role-Based Access Control**: Executives, Managers, Associates, and Internal Advisors with granular permissions
- **Organizational Hierarchy**: Teams with managers and associates
- **Multi-Team Projects**: Teams can collaborate on shared projects
- **Flexible Reviews**: Project reviews and peer-to-peer reviews
- **Internal Advisory Roles**: Temporary cross-team advisory assignments
- **Anonymous Reviewers**: Reviewer names hidden from non-executives
- **RESTful API**: JSON responses with comprehensive endpoint coverage

## Prerequisites

- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **MySQL**: 5.7 or higher (or MariaDB 10.3+)
- **Node.js**: 16.x or higher (for asset compilation)
- **npm**: Latest version

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd employee-reviews-api
```

### 2. Quick Setup

Run the automated setup script:

```bash
composer run setup
```

This will:
- Install PHP dependencies
- Copy `.env.example` to `.env` (if not exists)
- Generate application key
- Run database migrations
- Install Node dependencies
- Build frontend assets

### 3. Manual Setup (if needed)

If you prefer to set up manually:

```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env (see Configuration section)

# Run migrations
php artisan migrate

# Install Node dependencies
npm install

# Build assets
npm run build
```

## Configuration

### Database Setup

Edit `.env` and configure your MySQL connection:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=employee_reviews
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create the database:

```bash
mysql -u root -p -e "CREATE DATABASE employee_reviews;"
```

### Environment Variables

Key environment variables in `.env`:

- `APP_NAME`: Application name
- `APP_URL`: Application URL (default: http://localhost:8000)
- `APP_DEBUG`: Debug mode (set to false in production)
- `APP_KEY`: Encryption key (auto-generated)
- `DB_*`: Database credentials
- `API_TOKEN_LENGTH`: Length of API tokens (default: 80)

## Running the Application

### Development Server

Start the development server with live reloading:

```bash
composer run dev
```

This starts:
- Laravel development server on `http://localhost:8000`
- Queue listener
- Real-time logs (pail)
- Vite asset compilation watcher

### Production Server

```bash
php artisan serve --host 0.0.0.0 --port 8000
```

## Database Management

### Run Migrations

```bash
php artisan migrate
```

### Seed Sample Data

```bash
php artisan db:seed
```

### Fresh Migration (warning: destructive)

```bash
php artisan migrate:fresh --seed
```

### Rollback Migrations

```bash
php artisan migrate:rollback
```

## Testing

Run the test suite:

```bash
composer run test
```

Run specific test:

```bash
php artisan test tests/Feature/ReviewControllerTest.php
```

Run with code coverage:

```bash
php artisan test --coverage
```

## Code Quality

### Code Formatting

Format code with Laravel Pint:

```bash
./vendor/bin/pint
```

Check formatting without changes:

```bash
./vendor/bin/pint --test
```

## Project Structure

```
employee-reviews-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/    # API controllers
│   │   └── Middleware/     # HTTP middleware
│   ├── Models/             # Eloquent models
│   └── Services/           # Business logic services
├── database/
│   ├── migrations/         # Database schema migrations
│   ├── seeders/            # Database seeders
│   └── factories/          # Model factories for testing
├── routes/
│   └── api.php             # API route definitions
├── tests/
│   ├── Feature/            # Feature/integration tests
│   └── Unit/               # Unit tests
├── config/                 # Application configuration
├── resources/              # Views and assets
├── INSTRUCTIONS.md         # Original assessment requirements
├── SCHEMA_DESIGN.md        # Detailed database design
├── .env.example            # Environment template
└── composer.json           # PHP dependencies
```

## API Endpoints

### Authentication

All requests require an API token in the header:

```
Authorization: Bearer <api_token>
```

### Core Endpoints

**Users**
- `GET /api/users` - List users
- `GET /api/users/{id}` - Get user details
- `POST /api/users` - Create user (Executives only)
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user (Executives only)

**Teams**
- `GET /api/teams` - List teams
- `GET /api/teams/{id}` - Get team details
- `POST /api/teams` - Create team (Executives only)
- `PUT /api/teams/{id}` - Update team
- `DELETE /api/teams/{id}` - Delete team

**Projects**
- `GET /api/projects` - List projects
- `GET /api/projects/{id}` - Get project details
- `POST /api/projects` - Create project (Executives only)
- `PUT /api/projects/{id}` - Update project
- `DELETE /api/projects/{id}` - Delete project

**Reviews**
- `GET /api/reviews` - List reviews (filtered by role)
- `GET /api/reviews/{id}` - Get review details
- `POST /api/reviews` - Create review
- `PUT /api/reviews/{id}` - Update own review
- `DELETE /api/reviews/{id}` - Delete own review

**Internal Advisors**
- `GET /api/internal-advisors` - List advisory assignments
- `POST /api/internal-advisors` - Create advisory assignment (Managers/Associates)
- `DELETE /api/internal-advisors/{id}` - Remove advisory assignment

For complete API documentation, see the endpoint tests in `tests/Feature/`.

## Database Schema

The system uses the following core entities:

- **users**: System users with authentication
- **roles**: Role definitions (Executive, Manager, Associate, Internal Advisor)
- **role_user**: Many-to-many user-role relationships
- **teams**: Organizational teams with a manager
- **team_user**: Team membership for associates
- **projects**: Company projects
- **project_team**: Many-to-many project-team relationships
- **reviews**: Project and peer reviews
- **internal_advisors**: Advisory role assignments

See `SCHEMA_DESIGN.md` for the complete database design documentation.

## Access Control

### Executive
- Full system access
- Can manage users, teams, projects
- Can see all reviews with reviewer names
- Can delete any review (but not edit)

### Manager
- Can view their team's projects and members
- Can create and manage reviews
- Can see review names only for their own reviews

### Associate
- Can view their team's projects
- Can create reviews on team projects and peer reviews
- Can see review names only for their own reviews

### Internal Advisor
- Can review assigned projects temporarily
- Can create and manage their own reviews on assigned projects

## Troubleshooting

### Database Connection Error

Ensure MySQL is running and `.env` database credentials are correct:

```bash
mysql -u root -p -e "SHOW DATABASES;"
```

### Clear Application Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Rebuild Autoloader

```bash
composer dump-autoload
```

### Check Application Logs

```bash
tail -f storage/logs/laravel.log
```

## Development Workflow

1. **Create a new migration**:
   ```bash
   php artisan make:migration create_example_table
   ```

2. **Create a model**:
   ```bash
   php artisan make:model Example
   ```

3. **Create a controller**:
   ```bash
   php artisan make:controller Api/ExampleController
   ```

4. **Run tests**:
   ```bash
   composer run test
   ```

5. **Format code**:
   ```bash
   ./vendor/bin/pint
   ```

## Environment

- **Framework**: Laravel 12.x
- **Language**: PHP 8.2+
- **Database**: MySQL 5.7+
- **Frontend**: Vite
- **Testing**: PHPUnit 11.x
- **Code Quality**: Laravel Pint

## Support

For issues or questions, refer to:
- `INSTRUCTIONS.md` - Assessment requirements
- `SCHEMA_DESIGN.md` - Database design details
- `tests/` - Example usage in test files

## License

MIT
