# Deployment Guide

This document covers server requirements, installation, configuration, and scheduling for the PHP Twitter Multi-Bot Management System.

## Server requirements

### PHP

- **Minimum:** PHP 5.3 (original target).
- **Recommended for testing:** PHP 7.4 with deprecation notices monitored.
- **Extensions:**
  - `mbstring` — required by `TwitterBot.class.php`
  - `mysql` / `mysqli` or PDO — MDB2 uses the mysql driver
  - `session` — admin authentication
  - `simplexml` — Twitter search feed parsing in `TwitterBot::searchFor()`

The codebase includes a `get_magic_quotes_gpc()` workaround in `config.php`. Magic quotes were removed in PHP 5.4; the check is harmless on modern PHP.

### MySQL

- MySQL 5.x or compatible (MariaDB generally works).
- Create a dedicated database user with `SELECT`, `INSERT`, `UPDATE`, `DELETE` on the application database.
- Import the schema from [`twitter-min.sql`](../twitter-min.sql) at the repository root.

### Web server

- Document root should point at the project root (where `index.php` and `config.php` live).
- `admin/` must be reachable as `/admin/` relative to `SITE_DOMAIN`.
- Optional: the included [`admin/.htaccess`](../admin/.htaccess) shows Apache basic-auth wiring; update `AuthUserFile` to a valid htpasswd path if used.

## Installation steps

### 1. Upload files

Copy the entire repository to the host. Preserve directory structure—especially `PEAR/`, `classes/`, `admin/`, and `vendor/`.

### 2. Create the database

```sql
CREATE DATABASE twitter CHARACTER SET latin1;
CREATE USER 'twitter'@'localhost' IDENTIFIED BY 'your-password';
GRANT ALL PRIVILEGES ON twitter.* TO 'twitter'@'localhost';
FLUSH PRIVILEGES;
```

Import the schema:

```bash
mysql -u twitter -p twitter < twitter-min.sql
```

### 3. Configure `config.php`

Edit [`config.php`](../config.php):

| Constant | Purpose |
|----------|---------|
| `DB_HOST` | MySQL hostname (usually `localhost`) |
| `DB_NAME` | Database name |
| `DB_USERNAME` | Database user |
| `DB_PASSWORD` | Database password |
| `SITE_DOMAIN` | Full base URL with trailing slash, e.g. `https://example.com/` |
| `SITE_ADMIN_DOMAIN` | Derived automatically as `SITE_DOMAIN . 'admin/'` |

`HOME_PATH` and the PEAR include path are computed from the file location; normally no change needed.

### 4. Verify web access

- Visit `https://your-domain/` — should redirect to `/admin/`.
- Log in with `admin@localhost.com` / `pass`.
- Change the admin password under **Manage Users** (admin level) or **Account Options**.

### 5. Configure Twitter OAuth credentials

For each Twitter account you add in the dashboard, you need (from a Twitter Developer application):

- Consumer key (`consumer_key`)
- Consumer secret (`consumer_secret`)
- Access token (`oauthtoken`)
- Access token secret (`oauthsecret`)
- API key field (`api_key`) — legacy field; may mirror consumer key depending on how tokens were obtained

These values are entered per account in **New Account** / **Edit Account** forms. The bundled Tijs Verkoyen library expects OAuth 1.0a user context tokens.

> **Note:** Twitter/X API access models changed substantially after this project was written. See [LEGACY-NOTES.md](LEGACY-NOTES.md) before expecting OAuth setup to match current developer portal flows.

### 6. Schedule the cron job

The bot does not run continuously. It executes when [`twitter_cron.php`](../twitter_cron.php) is invoked.

**CLI cron (preferred):**

```cron
*/10 * * * * /usr/bin/php /path/to/project/twitter_cron.php >> /var/log/twitterbot-cron.log 2>&1
```

Adjust interval based on rate limits and campaign volume. Every 5–15 minutes was typical for this era of API limits.

**Web cron (if CLI unavailable):**

Some hosts allow URL cron:

```
https://your-domain/twitter_cron.php
```

Protect this URL if exposed—anyone triggering it consumes API quota. Consider IP restriction or an secret query token (would require a code change; not present in stock codebase).

### 7. Enable accounts and terms

1. Dashboard → create/edit a Twitter account → set **Enabled**.
2. Add tweets linked to a search term (`linked_tem` = term ID).
3. Add search terms → set **Enabled**, optional dates/geo.
4. Confirm cron runs and rows appear in `twitter_user_check`.

## Configuration reference

### `config.php` database helpers

| Function | Behavior |
|----------|----------|
| `db_disconnect()` | Closes MDB2 connection |
| `db_connect()` | Reopens connection from `$dsn` |
| `db_check_conn()` | Cycles connection; called after heavy query loops |

### `admin/php.ini`

A local `php.ini` may exist under `admin/` for host-specific overrides. Check your provider's policy on per-directory INI files.

### Error visibility

- `config.php` sets `error_reporting(0)` globally.
- `twitter_cron.php` sets `display_errors=1` and `E_ALL` for cron debugging.

For production cron, redirect stderr to a log file rather than enabling display errors on a public URL.

## Post-install checklist

- [ ] Default admin password changed
- [ ] `SITE_DOMAIN` matches actual public URL (used in redirects)
- [ ] Database credentials not committed to version control
- [ ] At least one Twitter account enabled with valid OAuth tokens
- [ ] Search terms enabled with linked tweet queue entries
- [ ] Cron job registered and logging output
- [ ] Sent tweets visible under account **Sent Tweets** after cron runs

## Troubleshooting

| Symptom | Things to check |
|---------|-----------------|
| Blank admin page | PHP errors hidden; temporarily enable reporting or check web server error log |
| "Error while connecting" | `config.php` DB constants; MySQL user grants |
| Login fails | Password hash uses `pass_salt()`; regenerate with `admin/pass.php` |
| Cron runs but no tweets | Account `enabled=0`; term `enabled=0`; term hit `max_tweets`; search returns no users |
| OAuth / API errors | Expired or revoked tokens; deprecated API endpoints (see legacy notes) |
| Redirect loops | `SITE_DOMAIN` mismatch with actual host/scheme |
