# E-commerce Platform

**Bangla Puzzle Limited · Laravel technical assignment**

Candidate: **Zahid Hasan**

Repository: [zahid397/e-commerce-platform](https://github.com/zahid397/e-commerce-platform)

A Laravel catalogue with protected category, subcategory and product management. Visitors can browse products by subcategory, save favourites, manage a cart and place a demo order. Admins can review those orders and update their status.

## Run with Docker

Use Docker Desktop (Linux containers) or Docker Engine with Compose v2 or newer. Start Docker, then open a terminal in the project root: the folder containing both `compose.yaml` and `docker/generate-env.php`. The ZIP may contain two nested `Zahid_Hasan` folders. PHP, Composer, Node.js and MySQL are installed inside the containers or build stages.

Generate local credentials with the supplied script. It writes `.env.docker` directly, without printing secrets or depending on shell redirection. Re-running it validates an existing file and preserves its credentials. No host PHP installation is needed.

Keep the original `.env.docker` if this stack has been used before. If it is missing but `zahid-ecommerce_mysql_data` or `zahid-ecommerce_app_storage` already exists (`docker volume ls`), restore it before running initialization. New credentials would not match an existing MySQL volume, and a new application key would invalidate encrypted sessions.

**Windows PowerShell**

```powershell
if (-not (Test-Path -PathType Leaf .\compose.yaml) -or
    -not (Test-Path -PathType Leaf .\docker\generate-env.php)) {
    throw "Open the project root containing compose.yaml and docker/generate-env.php."
}
docker run --rm --mount "type=bind,source=$($PWD.Path),target=/workspace" --workdir /workspace php:8.3-cli-bookworm php docker/generate-env.php
if ($LASTEXITCODE -ne 0) { throw "Docker environment initialization failed; do not start Compose." }
```

**Linux, macOS or WSL**

```bash
test -f compose.yaml && test -f docker/generate-env.php || { echo 'Open the project root containing compose.yaml and docker/generate-env.php.' >&2; exit 1; }
docker run --rm --user "$(id -u):$(id -g)" --mount "type=bind,source=$(pwd),target=/workspace" --workdir /workspace php:8.3-cli-bookworm php docker/generate-env.php || exit 1
```

If PHP 8.3+ is already installed, `php docker/generate-env.php` also works. The generated file contains a 32-byte Laravel key, independent random 48-character database passwords and `APP_PORT=8000`. Compose supplies the remaining settings: MySQL at `db:3306`, database `ecommerce_platform`, user `ecommerce_user`, local application mode and disabled debug output. `.env.docker` is excluded from Git and Docker builds. Keep it private and back it up with this stack's data; Windows bind mounts use the host folder's access permissions.

If the script itself is missing from this folder, restore `docker/generate-env.php` from the complete project archive. Do not create a blank `.env.docker`. An existing empty or invalid file produces an error instead of being silently skipped. Repair it, or remove it and initialize again **only if this stack has never created its data volumes**. If a previous interrupted run left `.env.docker.tmp`, remove only that temporary file after confirming no initializer is running.

Validate configuration without displaying expanded secrets, then build and start the app and MySQL:

```bash
docker compose --env-file .env.docker config --quiet
docker compose --env-file .env.docker up -d --build --wait
docker compose --env-file .env.docker ps
docker compose --env-file .env.docker exec --user www-data app php artisan migrate:status
```

Run each command only after the previous command succeeds. `up --wait` waits for MySQL's authenticated `SELECT 1` and Laravel's `/up` HTTP health checks. The app runs pending migrations before Apache starts. Create your own admin account after both services are healthy:

```bash
docker compose --env-file .env.docker exec --user www-data app php artisan admin:create
```

Open [the storefront](http://127.0.0.1:8000/) or [admin sign-in](http://127.0.0.1:8000/login). If port 8000 is busy, edit `APP_PORT` in `.env.docker` and use that port. MySQL imports the included sample catalogue only when its data volume is first created. The app runs pending migrations before starting Apache. Database records, uploads and sessions persist in Docker volumes.

Stop the stack with `docker compose --env-file .env.docker down`. Keep `.env.docker` and the named volumes for future starts; do not use `down --volumes` or `docker volume prune` to troubleshoot credentials. To see startup errors, run `docker compose --env-file .env.docker logs app db` locally and redact secrets before sharing logs. Use `config --quiet` for validation: plain `config` expands and prints credentials. Shell environment variables override `--env-file` values, so clear any stale `APP_KEY`, `DB_PASSWORD`, `MYSQL_ROOT_PASSWORD` or `APP_PORT` overrides if the stack uses unexpected settings ([Docker interpolation documentation](https://docs.docker.com/compose/how-tos/environment-variables/variable-interpolation/)).

## Run locally without Docker

**Requirements:** 64-bit PHP 8.3 or newer supported by Laravel 13, Composer 2, and MySQL 8.0 or newer. Enable PHP PDO MySQL, Fileinfo, Mbstring, XML, OpenSSL and GD with JPEG, PNG and WebP support. For rebuilding assets, use Node 22.12+ or 24 and npm. SQLite PDO is needed for the default test suite.

1. Extract the ZIP and open a terminal inside **Zahid_Hasan**.
2. Create an empty database with your MySQL administrator:

   ```sql
   CREATE DATABASE ecommerce_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

   Use a MySQL account with access to that database. Use a separate account and password for any deployed installation.

3. Install the PHP dependencies and create your local configuration:

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

   On Windows PowerShell, use `Copy-Item .env.example .env` in place of `cp`. In `.env`, set the MySQL values for your machine:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ecommerce_platform
   DB_USERNAME=your_local_mysql_user
   DB_PASSWORD="your_local_mysql_password"
   ```

4. **Choose one database setup method.**

   **A. Migrate and load the demo catalogue:**

   ```bash
   php artisan migrate --seed
   ```

   Use `php artisan migrate` without `--seed` for a blank catalogue. You can later use `php artisan db:seed --class=DemoCatalogSeeder` in a local environment. The demo seeder creates missing records without updating existing ones.

   **B. Import the included SQL export into the empty database:**

   ```bash
   mysql -u your_local_mysql_user -p ecommerce_platform < database/ecommerce_platform.sql
   php artisan migrate:status
   ```

   You can also import the file in MySQL Workbench or phpMyAdmin. In the MySQL CLI on Windows, connect to the database from the project folder and run `source database/ecommerce_platform.sql`. The dump includes migration history and sample catalogue rows, with no users or orders. Do not run migrations first if you choose the SQL import.

5. Link public image storage, create an admin and start the application:

   ```bash
   php artisan storage:link
   php artisan admin:create
   php artisan serve
   ```

   The admin command prompts for your name, email and a hidden password. There is no shared/default administrator or public registration. If Windows disallows creating a symbolic link, enable Developer Mode or run the link command in an elevated terminal.

6. Open [the storefront](http://127.0.0.1:8000/) or [admin sign-in](http://127.0.0.1:8000/login). If you serve on a different port, update `APP_URL` to match.

The ZIP includes compiled Vite assets, so the application runs after PHP and MySQL setup without a frontend build. To change the UI, run `npm ci` followed by `npm run dev` or `npm run build`. Stop the dev server and remove a stale `public/hot` if switching back to compiled assets. Demo images are bundled local WebP illustrations.

## Review the application

| Area | Route | What to try |
| --- | --- | --- |
| Storefront | `/` | Featured products, categories, search and saved products |
| Catalogue | `/products` | Search, category/subcategory filters, per-group sorting and pagination |
| Product | `/products/wireless-studio-headphones` | Slug, product image, price, quantity and cart |
| Cart and checkout | `/cart` and `/checkout` | Update quantities; place an order with sample contact details |
| Dashboard | `/admin` | Live counts, recent products and quick actions |
| Catalogue management | `/admin/categories`, `/admin/subcategories`, `/admin/products` | Create, view, edit and delete records, subject to parent restrictions |
| Orders | `/admin/orders` | Find the saved order and move it to Processing or Cancelled |

After signing in, create a category and a subcategory. Add a product with a JPEG, PNG or WebP image and mark it Active. Confirm that it appears under its subcategory in the public catalogue. Place a demo order and find its reference in admin Orders. Checkout collects no payment and sends no email.

## Implementation notes

- **Framework:** Laravel 13, Blade, Eloquent, MySQL, Tailwind CSS 4 and Vite. Small JavaScript enhancements handle preview, dependent selects, quantities and confirmations. Laravel validates and saves every form on the server.
- **Structure:** `app/Http/Controllers` handles HTTP, `app/Http/Requests` validates inputs, `app/Services` handles image and order operations, `resources/views` contains shared layouts and page templates, and `database` holds migrations, factories, seeders and the SQL export.
- **Relationships:** A category has many subcategories and products. Each product has both a category and a subcategory. A composite foreign key on the pair prevents cross-category mismatches even outside the form. Category/subcategory deletion is restricted while dependent records exist. Move products before moving their subcategory.
- **Prices and URLs:** Money is stored as DECIMAL with two decimal places, calculated in integer poisha and displayed in BDT. Old price is optional and cannot be below the new price. Duplicate product names receive distinct slugs; edits retain the original URL. Drafts are hidden from storefront routes.
- **Images:** On creation, images are required. Validation checks content and extension, limits size to 3 MB and dimensions to 3,200 × 3,200, then re-encodes images as WebP. Replacement saves the new file first and removes the obsolete upload after the database update. Missing files show a local fallback.
- **Orders:** Session cart data contains product IDs and quantities. The server rechecks prices and availability, saves an order and price/name snapshots in one transaction, and deduplicates retries with a unique checkout token. Product deletion does not remove earlier order lines. Receipt links are limited to the placing session.
- **Access:** Admin routes require a signed-in account with the admin flag. Forms have CSRF protection, Blade escapes text, and login/checkout are throttled. Do not put real customer information in a demo. The SQL file contains synthetic catalogue data only.

## Tests

Install development dependencies with `composer install` before running PHPUnit or Pint. The runtime Docker image uses `--no-dev`; use a separate development/test environment for the test suite. Never run database-refreshing tests against the running demo database.

```bash
php artisan test
php vendor/bin/pint --test
npm ci
npm run build
```

The default PHP tests use SQLite in memory. For MySQL parity, create a dedicated empty `ecommerce_platform_test` database and run the following after `php artisan config:clear`:

```bash
APP_ENV=testing DB_CONNECTION=mysql DB_DATABASE=ecommerce_platform_test php artisan test
```

On PowerShell, set those values via `$env:APP_ENV`, `$env:DB_CONNECTION` and `$env:DB_DATABASE` before running the test. The test bootstrap rejects other database targets. Grant the local test account access to the dedicated test database.

The browser workflow is in `tests/e2e/smoke.mjs`. Install its browser via `npx playwright install chromium`. Use a **separate local MySQL database** named `ecommerce_platform_e2e` and load the SQL export there. Set `DB_DATABASE` to that database for the fixture and test server. Set `E2E_ADMIN_EMAIL` to an address under `example.test` and `E2E_ADMIN_PASSWORD` to a locally chosen test password. Run `php tests/e2e/create-admin.php`, start the app and run `npm run test:e2e` with the same environment. Set `E2E_BASE_URL` if the app uses a port other than 8000. The workflow writes a sample order to the dedicated test database.

### Verified results — 6 November 2026

These results were executed for final preparation using disposable PHP containers, SQLite in memory and a separate disposable MySQL 8.0.46 server. The existing Docker application database and named volumes were retained. Database fingerprints matched before and after the tests.

| Check | Command or method | Actual result |
| --- | --- | --- |
| Laravel Feature suite on SQLite | `php artisan test --testsuite=Feature --no-ansi` | 19 passed, 177 assertions |
| Unit suite on SQLite | `php artisan test --testsuite=Unit --no-ansi` | 1 passed, 4 assertions |
| Full suite on dedicated MySQL | `APP_ENV=testing DB_CONNECTION=mysql DB_DATABASE=ecommerce_platform_test php artisan test --no-ansi` | 20 passed, 181 assertions |
| Migrations on empty MySQL | `php artisan migrate --force --no-interaction --no-ansi` in the disposable test environment | All 8 migrations completed |
| PHP formatting | `php vendor/bin/pint --test` | Passed, 71 files |
| Composer manifest and lockfile | `composer validate --strict --no-check-publish --no-interaction` | Passed |
| Frontend assets | `docker build --target assets --tag zahid-submission-assets .` and byte comparison | Submitted manifest, CSS and JavaScript match the locked Docker asset build |
| Live public HTTP pages | `/up`, `/`, `/products`, `/products/wireless-studio-headphones`, `/login` | All returned HTTP 200 |

The Feature suite checks Category and Subcategory CRUD, parent restrictions, scoped duplicate names, Product CRUD, stable/unique slugs, category/subcategory consistency, database foreign keys, price validation, required/genuine/size-limited image uploads, image replacement/deletion and public product details. It also covers authentication, public filters, carts and order handling. Browser automation results in [QA.md](QA.md) are from the earlier application review; Playwright was not rerun during this submission preparation. No production deployment is claimed.

### SQL export verification

The submitted file is **`database/ecommerce_platform.sql`**. It was restored successfully into a fresh disposable MySQL 8.0.46 server. The restored schema contains 14 tables, five foreign key constraints and all required catalogue columns. The composite Product-to-Subcategory relationship enforces matching `category_id` and `subcategory_id` values. Its eight migration history records match the supplied migration filenames.

| Export content | Verified count |
| --- | ---: |
| Categories | 5 |
| Subcategories | 10 |
| Products | 11 |
| Users | 0 |
| Orders | 0 |
| Sessions | 0 |

The 14 tables are `cache`, `cache_locks`, `categories`, `failed_jobs`, `job_batches`, `jobs`, `migrations`, `order_items`, `orders`, `password_reset_tokens`, `products`, `sessions`, `subcategories` and `users`. The export contains synthetic catalogue data and no administrator credentials. It is the reviewed static export, not a dump of the running administrator's database. Import it only into an empty database using the setup instructions above; no SQL import or database reset was performed on the live Docker database during final preparation.

SQL SHA-256:

```text
9413703efcb01460917ad0a591da6c36fb73c50294924cd92e1ff525880a6104
```

Check the file locally with `Get-FileHash database/ecommerce_platform.sql -Algorithm SHA256` in PowerShell, `sha256sum database/ecommerce_platform.sql` on Linux/WSL, or `shasum -a 256 database/ecommerce_platform.sql` on macOS.

## GitHub setup and updates

The Git root is the directory containing `artisan`, `composer.json`, `compose.yaml` and this README. The repository stores these files directly at its root. The assignment ZIP adds a single `Zahid_Hasan/` folder around them.

To obtain the project from GitHub:

```bash
git clone https://github.com/zahid397/e-commerce-platform.git
cd e-commerce-platform
```

Then use either the Docker or local setup documented above. Each fresh installation generates its own local credentials; `.env.example` is the only tracked environment template.

For the first publication of a fresh extraction to an **empty** repository, run the following from the project root. Skip `git init` if Git already exists, and skip `git remote add` if the correct `origin` is already configured:

```bash
git init -b main
git remote add origin https://github.com/zahid397/e-commerce-platform.git
git add .
git diff --cached --check
git diff --cached --stat
git commit -m "Prepare verified Laravel assignment submission"
git push -u origin main
```

Stage the complete reviewed project, including `public/build`, migrations and the SQL export. Do not replace this README with a one-line repository title. Review staged files before committing. `.gitignore` excludes local environment files, credential/key files, `auth.json`, `.npmrc`, dependencies, runtime logs/cache/uploads, common volume/data directories and generated archives. The environment template, lockfiles, SQL export, source images and compiled Vite assets remain included. Check source content for credentials as well as checking filenames; ignore rules alone cannot detect a secret pasted into source code.

For later changes, review `git status --short`, stage the intended files, create a commit and run `git push origin main`. If the remote has moved, fetch and inspect its commits before integrating them. Do not force-push or overwrite unrelated history. Verify publication by comparing the SHA from `git rev-parse HEAD` with `git ls-remote origin refs/heads/main`; only a successful push and matching SHAs confirm publication. The final preparation report records the actual commit and push result.

## Final assignment ZIP

Create the archive from the reviewed commit, from inside the Git root:

```bash
git archive --format=zip --prefix=Zahid_Hasan/ --output=../Zahid_Hasan.zip HEAD
```

The output is one directory above the repository. On this workstation its path is `C:\Users\hp\Downloads\Zahid_Hasan\Zahid_Hasan.zip`. The archive contains the committed project with exactly one `Zahid_Hasan/` prefix, including hidden configuration templates and compiled assets. Its root layout is:

```text
Zahid_Hasan.zip
└── Zahid_Hasan/
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    │   ├── .gitignore
    │   ├── ecommerce_platform.sql
    │   ├── factories/
    │   ├── migrations/
    │   └── seeders/
    ├── docker/
    │   ├── apache.conf
    │   ├── entrypoint.sh
    │   ├── generate-env.php
    │   └── php-uploads.ini
    ├── public/
    │   ├── .htaccess
    │   ├── build/
    │   │   ├── assets/
    │   │   └── manifest.json
    │   ├── favicon.ico
    │   ├── images/
    │   ├── index.php
    │   └── robots.txt
    ├── resources/
    ├── routes/
    ├── storage/
    ├── tests/
    ├── .dockerignore
    ├── .editorconfig
    ├── .env.example
    ├── .gitattributes
    ├── .gitignore
    ├── artisan
    ├── compose.yaml
    ├── composer.json
    ├── composer.lock
    ├── Dockerfile
    ├── package-lock.json
    ├── package.json
    ├── phpunit.xml
    ├── QA.md
    ├── README.md
    └── vite.config.js
```

Directories contain their committed project files; list every ZIP entry with `python -m zipfile --list ../Zahid_Hasan.zip` or `unzip -l ../Zahid_Hasan.zip`. Verify CRC integrity with `python -m zipfile --test ../Zahid_Hasan.zip` or `unzip -t ../Zahid_Hasan.zip`. The required SQL entry is exactly `Zahid_Hasan/database/ecommerce_platform.sql`. There is no extra nested project root, Git history, `.env`, `.env.docker`, `vendor`, `node_modules`, database volume, log, session or private upload in the archive. `storage` and `bootstrap/cache` retain their committed directory placeholders so Laravel can create runtime files after installation.

## Submission checklist

- [ ] Confirm GitHub `main` matches the final local commit and the repository is accessible to the reviewer.
- [ ] Confirm `Zahid_Hasan.zip` passes its integrity check and contains the SQL export, all eight migrations, both lockfiles, Docker files, compiled assets and this README.
- [ ] Recheck that credentials, Git history, dependencies, runtime files and data volumes are excluded.
- [ ] Follow one database setup method in a fresh environment and create a private administrator using `php artisan admin:create`.
- [ ] Include the GitHub URL and the requested candidate details with the ZIP when submission is authorized.
- [ ] Send or email the assignment only after explicit final approval. No email or assignment submission was performed during preparation.

## Scope and deployment

This is a local assignment demo. It does not integrate payment, stock reservation, delivery services or transactional email. Favourites and carts use the current browser session. The demo seeder is restricted to local/testing environments.

For an optional VPS deployment, use PHP-FPM with the web root pointed at **public/**, TLS, production environment settings (including `APP_DEBUG=false` and secure session cookies), a restricted MySQL account and persistent writable storage. Deploy compiled assets, run migrations with a backup, link storage, and configure backups for both uploaded files and MySQL. Never expose the project root or the submitted SQL dump through the web server. The supplied verification is local; no VPS or live deployment is claimed.
