# BlackJoe Photography - Portfolio Management System

A Laravel 8 backend API for managing a photography portfolio with admin authentication and media upload capabilities.

## Features

- 🔐 **Admin Authentication** - Session-based admin login with password management
- 📸 **Portfolio Management** - Upload, list, and delete portfolio items (images & videos)
- 🛡️ **Middleware Protection** - Custom admin authentication middleware
- 🎨 **RESTful API** - Clean API endpoints for frontend integration
- ✅ **Type Safety** - Full return type hints and PHPDoc annotations
- 🧪 **Tested** - PHPUnit test suite included

## Tech Stack

- **Framework**: Laravel 8.83.29
- **PHP**: 8.3.6
- **Database**: MySQL/MariaDB (configurable)
- **Testing**: PHPUnit 9.6.25

## Prerequisites

- PHP 8.3+ with extensions:
  - `dom`, `xml`, `xmlwriter`, `mbstring` (for PHPUnit)
  - Other standard Laravel requirements
- Composer
- MySQL/MariaDB or SQLite database

## Installation

1. **Clone the repository** (if not already cloned)
   ```bash
   cd /path/to/blackjoe-photography
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Set up environment**
   ```bash
   cp .env.example .env  # if .env doesn't exist
   php artisan key:generate
   ```

4. **Configure database** in `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=blackjoe_photography
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   
   # Optional: Set initial admin password (defaults to 'admin123')
   ADMIN_PASSWORD=your_secure_password
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```
   This creates the database tables and inserts a default admin user.

6. **Create storage symlink**
   ```bash
   php artisan storage:link
   ```
   This allows public access to uploaded portfolio files.

7. **Cache configuration** (optional but recommended for production)
   ```bash
   php artisan config:cache
   php artisan route:cache
   ```

## Running the Application

### Development Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

### Using XAMPP/Apache

See [README-XAMPP.md](README-XAMPP.md) for Apache/XAMPP setup instructions.

## API Endpoints

### Public Endpoints

- `GET /api/portfolio` - List all portfolio items
- `GET /portfolio` - List all portfolio items (web route)

### Admin Authentication

- `POST /api/admin/login` - Admin login
  ```json
  { "password": "your_password" }
  ```

- `POST /api/admin/logout` - Admin logout

- `GET /api/user` - Get current user info

### Protected Admin Endpoints

These require admin authentication (session cookie from login):

- `POST /api/admin/change-password` - Change admin password
  ```json
  {
    "current_password": "old_password",
    "new_password": "new_password"
  }
  ```

- `POST /api/portfolio` - Upload portfolio item (multipart/form-data)
  - `file` (required): image or video file (max 200MB)
  - `title` (optional): item title
  - `category` (optional): category name
  - `description` (optional): item description

- `DELETE /api/portfolio/{id}` - Delete portfolio item

## Testing

Run the test suite:

```bash
./vendor/bin/phpunit
```

Run with coverage (requires Xdebug):

```bash
./vendor/bin/phpunit --coverage-html coverage
```

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AdminController.php       # Admin auth & user management
│   │   ├── PortfolioController.php   # Portfolio CRUD operations
│   │   └── Controller.php            # Base controller
│   ├── Middleware/
│   │   └── EnsureAdminAuthenticated.php  # Custom admin auth middleware
│   └── Kernel.php                    # HTTP kernel with middleware registration
├── Models/
│   ├── AdminModel.php                # Admin user model
│   ├── PortFolio.php                 # Portfolio item model
│   └── User.php                      # Laravel default user model
routes/
├── api.php                           # API routes
├── web.php                           # Web routes
└── channels.php                      # Broadcast channels
database/
├── migrations/                       # Database migrations
└── seeders/                          # Database seeders
```

## Recent Fixes & Improvements

### Fixed Issues

1. ✅ **Removed broken UserController routes** - Cleaned up unused `loginUser`/`verifyUser` routes that had no implementation
2. ✅ **Added proper authentication middleware** - Created `EnsureAdminAuthenticated` middleware for cleaner route protection
3. ✅ **Added return type hints** - All controller methods now have proper `JsonResponse` return types
4. ✅ **Improved model documentation** - Added PHPDoc type hints for `$fillable`, `$casts`, and other properties
5. ✅ **Enhanced code quality** - Consistent formatting, better comments, and Laravel best practices
6. ✅ **Fixed middleware registration** - Properly registered custom middleware in `Kernel.php`
7. ✅ **Updated route organization** - Grouped protected routes under middleware for better clarity

### Code Quality Improvements

- ✅ All PHP syntax validated (zero errors)
- ✅ All tests passing (2/2 tests)
- ✅ Routes properly cached and optimized
- ✅ Storage symlink configured
- ✅ PHPDoc annotations for better IDE support
- ✅ Type safety with return type hints

## Configuration

### Environment Variables

Key `.env` variables:

```env
APP_NAME="BlackJoe Photography"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_DATABASE=blackjoe_photography

ADMIN_PASSWORD=your_secure_password

SESSION_DRIVER=file
SESSION_LIFETIME=120
```

## Security Notes

- Admin authentication uses Laravel sessions (cookies)
- Passwords are hashed using bcrypt
- CSRF protection enabled for web routes
- File uploads are validated and stored securely
- Middleware enforces authentication on protected routes

## Troubleshooting

### PHPUnit "dom" extension missing

Install PHP XML extensions:

```bash
sudo apt install php8.3-xml php8.3-mbstring  # Ubuntu/Debian
# or
sudo dnf install php-xml php-mbstring         # RHEL/CentOS/Fedora
```

### Storage files not accessible

Run the storage link command:

```bash
php artisan storage:link
```

### Routes not updating

Clear and rebuild caches:

```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan route:cache
php artisan config:cache
```

## Development

For developer notes and advanced setup, see [DEVELOPER.md](DEVELOPER.md).

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

**Project Status**: ✅ All systems operational | All tests passing | Production ready
