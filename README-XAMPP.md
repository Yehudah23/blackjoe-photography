Using this Laravel project with XAMPP (Apache + MySQL)

Goal: serve the application only via XAMPP's Apache server and use XAMPP's MySQL.

Quick steps

1) Place the project inside XAMPP's htdocs or create an Apache virtual host
   - Recommended: move or symlink the project into /opt/lampp/htdocs/blackjoe-photography
     or configure an Apache virtual host that points its DocumentRoot to the project's
     `public/` directory.

2) Ensure XAMPP's MySQL is running
   - Start XAMPP's control panel or run: sudo /opt/lampp/lampp start
   - Confirm MySQL is listening on 127.0.0.1:3306 (the project uses TCP by default)

3) Update `.env` (already configured)
   - DB_CONNECTION=mysql
   - DB_HOST=127.0.0.1
   - DB_PORT=3306
   - DB_DATABASE=laravel
   - DB_USERNAME=laravel
   - DB_PASSWORD=secret
   - MIX_BACKEND_URL=http://localhost
   - FRONTEND_URL=http://localhost

4) Ensure PHP extensions are enabled in XAMPP
   - XAMPP bundles PHP with pdo_mysql and mysqli; confirm by checking `phpinfo()` or
     running `/opt/lampp/bin/php -m | grep -E "pdo|mysql"`.

5) Run migrations using the same PHP used by XAMPP (optional but recommended)
   - Use XAMPP's PHP binary to run artisan so the PHP CLI environment matches the server:
     /opt/lampp/bin/php artisan migrate

6) Permissions
   - Ensure `storage/` and `bootstrap/cache` are writable by the Apache process:
     sudo chown -R www-data:www-data storage bootstrap/cache
     (on some systems the Apache user is `www-data`; on XAMPP it may be `daemon` or `nobody`)

7) Access the app
   - Open http://localhost (or your vhost) in the browser. The app will be served by Apache.

Notes & rationale
- Using XAMPP's Apache and MySQL ensures the app is served and talks to the database via the
  same environment that your Apache/PHP will use in production-like scenarios.
- Avoid running `php artisan serve` or separate dev servers when you want XAMPP to be the only
  server; the project is now configured to assume XAMPP/Apache is the host origin (see MIX_BACKEND_URL).
- For front-end development, use the browser against http://localhost. If you need hot reload or a dev
  server, set up a proxy in your frontend project to forward /api to http://localhost so cookies/session
  behaviour remains same-origin.

If you want, I can:
- Create an Apache vhost file for `/etc/apache2/sites-available/` and enable it (I will need sudo privileges).
- Generate SQL to drop the `admins` table if you want to remove the admin scaffolding I added earlier.
- Confirm ownership and permission commands for your distribution and XAMPP setup.
