# PHP Twitter Multi-Bot Management System

A legacy PHP web application for managing multiple Twitter accounts, automated mention campaigns driven by search terms, and reporting on sent tweets and retweets. Originally developed in 2009 and maintained through early 2011.

> **Legacy software.** This project targets PHP 5.x-era hosting, bundled PEAR/MDB2, and Twitter API patterns from 2010–2012. Many Twitter endpoints it relies on have since been removed or replaced. Treat this repository as historical reference or a starting point for a modernization effort—not as a drop-in solution for current Twitter/X API usage.

## Features

- **Multi-account management** — Connect and enable multiple Twitter accounts per system user.
- **Search-term campaigns** — Define keywords (with optional geo filters and date windows) that trigger automated `@mention` replies.
- **Tweet queues** — Maintain pools of message templates linked to search terms; the bot picks a random eligible tweet per match.
- **Duplicate avoidance** — Tracks which Twitter usernames have already received a message from a given account.
- **Retweet / mention capture** — Stores incoming mentions and associated user metadata.
- **Reporting** — Export sent-tweet logs as CSV (Excel-compatible) or PDF.
- **Role-based limits** — User levels cap accounts, terms, tweets, and retry attempts.
- **Admin UI** — Browser-based dashboard at `/admin/`.

## Quick start

1. Upload all files to a PHP-capable web host.
2. Create a MySQL database and import [`twitter-min.sql`](twitter-min.sql).
3. Edit [`config.php`](config.php): database credentials and `SITE_DOMAIN`.
4. Open `/admin/` in a browser.
5. Log in with the default credentials (change immediately — see [Security](#security)).
6. Add a Twitter account with valid OAuth credentials, create tweets and search terms, then enable the account.
7. Schedule [`twitter_cron.php`](twitter_cron.php) to run on a cron interval (e.g. every 5–15 minutes).

See [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) for full installation and cron setup.

## Default login

| Field    | Value                 |
|----------|-----------------------|
| Email    | `admin@localhost.com` |
| Password | `pass`                |

The seeded password hash in `twitter-min.sql` corresponds to `pass` using the salted MD5 scheme in `admin/functions/common.php`. Use `admin/pass.php` locally to generate hashes for new passwords.

## How it works (summary)

```
Cron (twitter_cron.php)
  └─ For each system user
       └─ For each enabled Twitter account
            └─ For each enabled search term (within limits/dates)
                 └─ tweetmanage::post_tweet()
                      ├─ Search public timeline for term (TwitterBot)
                      ├─ Skip if username already contacted
                      ├─ Pick random tweet from linked queue
                      ├─ Post @mention via OAuth API
                      ├─ Record in twitter_user_check
                      └─ Gather new retweets/mentions into retweets table
```

Full flow diagrams and file references: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

## Project layout

```
├── config.php              # DB connection, paths, PEAR bootstrap
├── index.php               # Redirects to /admin/
├── twitter_cron.php        # Bot entry point (run via cron)
├── twitter-min.sql         # Database schema + seed data
├── classes/
│   ├── TwitterBot.class.php   # Search + tweet wrapper (Nicolas Perriault)
│   ├── tweetmanage.php        # Core bot orchestration
│   ├── load_tables.php        # Instantiates table model objects
│   └── tables/                # CRUD classes per DB table
├── admin/
│   ├── index.php              # Admin front controller
│   ├── forms/                   # UI form/list renderers
│   └── functions/               # Routing, helpers, PDF/CSV export
├── vendor/twitter.php           # Tijs Verkoyen Twitter API class (v2.0.3)
└── PEAR/                        # Bundled PEAR + MDB2
```

## Documentation

| Document | Description |
|----------|-------------|
| [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) | Requirements, install steps, cron, config reference |
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | Request flow, classes, admin routing |
| [docs/DATABASE.md](docs/DATABASE.md) | Tables, columns, relationships, seed data |
| [docs/ADMIN-GUIDE.md](docs/ADMIN-GUIDE.md) | Using the admin panel end-to-end |
| [docs/LEGACY-NOTES.md](docs/LEGACY-NOTES.md) | API deprecation, security, known quirks |

## Requirements

| Component | Notes |
|-----------|-------|
| PHP | 5.3+ originally; 7.x may work with warnings. `mbstring` extension required. |
| MySQL | 5.x; schema uses MyISAM and `latin1` charset. |
| Web server | Apache recommended; optional `.htaccess` basic auth in `admin/`. |
| Cron | Shell or web cron hitting `twitter_cron.php`. |
| Twitter API | OAuth 1.0a consumer key/secret + access token/secret per account. |

## Security

- Change the default admin password before exposing the site to a network.
- OAuth tokens and API keys are stored in plain text in `twitter_users`.
- Passwords use salted MD5 (`pass_salt()`), which is weak by modern standards.
- Some admin delete paths build SQL via string concatenation rather than bound parameters.
- `error_reporting(0)` in `config.php` hides errors in production—enable logging separately if debugging.
- Do not commit real credentials in `config.php`; use environment-specific values on each host.

Details: [docs/LEGACY-NOTES.md](docs/LEGACY-NOTES.md).

## Third-party libraries

| Library | Purpose | Source |
|---------|---------|--------|
| PEAR + MDB2 | Database abstraction | Bundled in `PEAR/` |
| FPDF | PDF report generation | `admin/fpdf/` |
| jQuery + jQuery UI | Admin UI (v1.4 / 1.8) | `admin/style/js/` |
| Twitter class | OAuth + REST API | [Tijs Verkoyen](http://classes.verkoyen.eu/twitter/) — `vendor/twitter.php` |
| TwitterBot class | Search-and-mention helper | Nicolas Perriault — `classes/TwitterBot.class.php` |

## License / attribution

Original application code is legacy project material. Third-party components retain their respective licenses (FPDF, PEAR, Tijs Verkoyen BSD, TwitterBot MIT).
