## BFP Konekt Dashboard (index.php)

A PHP + MySQL dashboard for monitoring and managing fire-related incidents. The authenticated dashboard at `index.php` renders a Leaflet map with clustering, search/geocoding, incident controls, stats, and notifications.

### Quick start
- **Requirements**: XAMPP (PHP 8.x recommended), MySQL/MariaDB, Browser
- **Install**:
  1. Copy this project to `C:\xampp\htdocs\Bfp-Konekt`
  2. Create a database named `bfpkqrib_bfpkonekt` (or choose your own name)
  3. Update database credentials in `config/db_connect.php` (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`)
  4. Start Apache and MySQL in XAMPP
  5. Open `http://localhost/Bfp-Konekt/login.php` and sign in

### First admin login
- **Default admin ID**: `BFPK0001`
- **Default password**: `Kiko_195568`
- Change the password immediately after first login. You can also change it via `api/change-password.php`.

### Database initialization
- Tables are auto-created on first run by `config/database.php` when invoked by the connection layer.
- If you need to fix or (re)create structure, use the utilities in `api/`:
  - `api/run_database_update.php`
  - `api/check_database_structure.php` and `api/fix_database_structure.php`
  - `api/test-db.php`, `api/test_connection.php`

### What `index.php` does
- Enforces an authenticated session (`login.php` redirects unauthenticated users)
- Loads the dashboard UI and map, including:
  - Leaflet 1.9.x, Leaflet.draw, Leaflet Control Geocoder
  - Marker clustering for incidents
  - NLP/utility libs: compromise, natural, turf
  - QR code generation (qrcodejs)
  - Local scripts like `js/user-manager.js`, `js/notification.js`, `js/alert-system.js`

### Key folders
- `api/`: REST-like endpoints and maintenance scripts, including:
  - `incidents.php`, `get_incidents.php`, `insert_incident.php`, `update_incident_status.php`
  - Evidence and feedback: `upload_evidence.php`, `get_evidence.php`, `get_evidence_stats.php`, `submit_feedback.php`
  - Settings: `settings.php`, `get_settings.php`, `save_settings.php`
  - Users/auth: `auth.php`, `users.php`, `change-password.php`
  - Health/DB tools: `check_connection.php`, `test_connection.php`, `check_database_structure.php`, `fix_database_structure.php`, `run_database_update.php`
- `config/`: connection and service config (`db_connect.php`, `database.php`, `sms-config.php`)
- `js/`, `css/`: client scripts and styles used by the dashboard
- `uploads/`: media storage (e.g., slider images, evidence)

### Configuration
- Edit `config/db_connect.php` to match your MySQL credentials and database name.
- Optional integrations (e.g., SMS) live under `config/`; update as needed.
- In production, disable display of PHP errors (enable logging only).

### Run
- After configuration, browse: `http://localhost/Bfp-Konekt/`
- If redirected, log in at `http://localhost/Bfp-Konekt/login.php`.

### Troubleshooting
- Database connection issues: verify credentials in `config/db_connect.php` and that MySQL is running.
- Schema issues: open `api/check_database_structure.php` then `api/fix_database_structure.php`.
- General API health: open `api/test_connection.php` and `api/test-db.php`.

### Security notes
- Replace the default admin password on first use.
- Restrict direct access to maintenance scripts under `api/` in production (e.g., via web server rules).
- Do not deploy with `display_errors` enabled.

### License
Add your preferred license here.


