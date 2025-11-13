Developer notes — local workflow
================================

This project can be run in two common ways locally:

- XAMPP / Apache (recommended for a production-like environment)
- PHP built-in dev server via `php artisan serve` (convenient for quick tests)

Because running both at the same time can cause "Address already in use" errors,
use the helper scripts in `scripts/` to start/stop the artisan server safely.

Usage
-----

Start the artisan dev server (binds to 127.0.0.1:8000):

```bash
./scripts/dev-start.sh
```

Stop the server started by the script:

```bash
./scripts/dev-stop.sh
```

Notes
-----

- The start script writes a PID file to `storage/dev-server.pid` and logs to
  `storage/laravel-serve.log`.
- If you prefer XAMPP/Apache, make sure the artisan server is stopped before
  starting Apache vhosts that serve the same address/port.
- If you accidentally see "Address already in use", run `./scripts/dev-stop.sh`.

Keeping artisan
----------------
Per project preference, we keep the artisan dev server available. Use the
scripts above to manage it; do not run `php artisan serve` manually if you
already started it with `./scripts/dev-start.sh`.
