# Architecture

Overview of how the PHP Twitter Multi-Bot system is structured and how data flows through it.

## High-level components

```
┌─────────────────────────────────────────────────────────────────┐
│                         Browser (Admin UI)                        │
│                    admin/index.php + forms                        │
└────────────────────────────┬────────────────────────────────────┘
                             │ HTTP (session auth)
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│  config.php  →  MDB2  →  load_tables.php  →  table model classes │
│  page_procs.php (router)  →  admin/forms/*.php (views)           │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
                        MySQL database

┌─────────────────────────────────────────────────────────────────┐
│              Cron / CLI: twitter_cron.php                         │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│  tweetmanage  →  TwitterBot  →  vendor/twitter.php  →  Twitter  │
└─────────────────────────────────────────────────────────────────┘
```

## Entry points

| File | Role |
|------|------|
| [`index.php`](../index.php) | HTTP redirect to `/admin/` |
| [`admin/index.php`](../admin/index.php) | Admin shell: session, assets, nav, includes router |
| [`twitter_cron.php`](../twitter_cron.php) | Automated bot runner; iterates users → accounts → terms |

Both admin and cron load [`config.php`](../config.php) for database access and [`classes/load_tables.php`](../classes/load_tables.php) for global table model instances.

## Admin request lifecycle

1. **`admin/index.php`** starts the session and loads dependencies:
   - `config.php`, FPDF, `load_tables.php`, `common.php`, `sections.php`, `TwitterBot.class.php`, `page_procs.php`

2. **`sections.php`** auto-requires every `*.php` file in `admin/forms/` (registers form functions).

3. **`page_procs.php`** routes on `$_GET['mode']`:
   - Unauthenticated → `login_frm()`
   - Authenticated → switch on mode (create user, edit terms, reports, etc.)
   - Sets `$output` HTML fragment rendered in the main content area

4. **Table model classes** (`classes/tables/*.php`) encapsulate CRUD against MySQL via MDB2 prepared statements (most paths).

### Admin modes (router)

| `mode` | Handler / action |
|--------|------------------|
| *(default)* | `user_listing()` — dashboard of Twitter accounts |
| `create_user`, `edit_user` | Account form |
| `process_user` | Save Twitter account |
| `edit_terms`, `add_term`, `edit_selected_term`, `process_term` | Search term CRUD |
| `edit_tweets`, `new_tweet`, `edit_selected_tweet`, `process_tweet` | Tweet queue CRUD |
| `send_tweet`, `process` via POST | Manual tweet from admin |
| `sent_tweets`, `sent_tweets_csv`, `sent_tweets_pdf` | Logs and exports |
| `retweets` | Incoming RT/mention listing |
| `view_system_users`, `add_system_users`, `edit_system_users`, `process_system_user` | System user admin |
| `view_user_levels`, `add_user_levels`, `edit_user_levels`, `process_user_levels` | Tier limits |
| `edit_user_options`, `process_user_options` | Profile / serialized options |
| `logoff` | Session destroy |

Navigation visibility is gated by `$_SESSION['user_options']` (unserialized from `user_levels.options` merged with per-user overrides).

## Cron / bot lifecycle

[`twitter_cron.php`](../twitter_cron.php) performs nested loops:

1. **All `system_users`** — loads each via `$system_users->get()`
2. **Enabled `twitter_users`** (`enabled = 1`) for that system user's scope
3. **Enabled `twitter_terms`** for each account where tweet quota allows:
   - Condition: `(max_tweets < tweet_count) OR (max_tweets = 0)` — terms with a cap stop when count reaches max
4. **Date filtering** (optional `datestart` / `dateend`) before calling `post_tweet_call()`
5. **`tweetmanage::post_tweet()`** executes the mention workflow

See [LEGACY-NOTES.md](LEGACY-NOTES.md) for documented quirks in date-range and retry logic in the stock code.

## Core classes

### `tweetmanage` ([`classes/tweetmanage.php`](../classes/tweetmanage.php))

Orchestrates a single mention attempt:

| Method | Purpose |
|--------|---------|
| `post_tweet()` | Search → dedupe check → random tweet → send → gather RTs → record |
| `get_tweet()` | Random row from `tweets` linked to current term |
| `username_check()` | Count prior contacts in `twitter_user_check` |
| `username_insert()` | Log sent mention, bump term/tweet counters, fetch profile image |
| `gather_rts()` | Pull mentions via API, insert new rows into `retweets` |

Uses `$sid` (system user ID) and `$userid` (twitter account ID) for multi-tenant isolation.

### `TwitterBot` ([`classes/TwitterBot.class.php`](../classes/TwitterBot.class.php))

Wrapper around `vendor/twitter.php`:

| Method | Purpose |
|--------|---------|
| `searchAndReturnUsername()` | Atom search feed → extract author screen name |
| `tweetmessage()` | Post status update via `statusesupdate()` |
| `searchAndRetweet()` | Alternate flow: RT template (not used by cron path) |
| `searchFor()` | Builds legacy Atom search URL with optional geo radius |

Search uses the deprecated public Atom endpoint pattern:

```
http://search.twitter.com/search.atom?q=...&near:"City, State"+within:25mi
```

### `vendor/twitter.php`

Monolithic Tijs Verkoyen Twitter REST + OAuth 1.0a client (v2.0.3, ~2010). Instantiated directly in `tweetmanage` for profile images and mentions, and indirectly via `TwitterBot` for posting.

### Table models ([`classes/tables/`](../classes/tables/))

Each class maps to one database table with `get()`, `post()`, `update()`, and domain-specific helpers:

| Class | Table | Notable methods |
|-------|-------|-----------------|
| `system_users` | `system_users` | `login_user()`, `update_options()` |
| `twitter_users` | `twitter_users` | Account CRUD |
| `twitter_terms` | `twitter_terms` | `update_count()` |
| `tweets` | `tweets` | `get_random()`, `update_count()` |
| `twitter_user_check` | `twitter_user_check` | Sent-mention log |
| `retweets` | `retweets` | Captured mentions |
| `user_levels` | `user_levels` | Tier definitions |

Globals (`$tweets`, `$twitter_users`, etc.) are created in `load_tables.php` and referenced throughout.

## Multi-tenancy model

Two IDs scope nearly all data:

| ID | Column | Meaning |
|----|--------|---------|
| `sid` | `system_users.id` | Logged-in admin / tenant owner |
| `userid` | `twitter_users.id` | A connected Twitter account |

Tweets, terms, sent logs, and retweets all carry both IDs so one installation can host multiple system users, each with multiple Twitter accounts.

Admin users with `all_twitters` / `user_access` options bypass some limits for full system management.

## User levels and limits

`user_levels.options` stores a PHP-serialized array, e.g.:

```php
twitter_accounts  // max Twitter accounts (0 = unlimited for admin)
twitter_terms     // max search terms per account
twitter_tweets    // max queued tweets per account
twitter_retries   // max retry attempts when username already contacted
user_access       // manage system users
user_levels       // manage tiers
all_twitters      // see all accounts (admin)
```

Seeded tiers: Admin, Super User, User, Free User — see [DATABASE.md](DATABASE.md).

## Reporting pipeline

Sent tweet reports read from `twitter_user_check` joined to `tweets`:

- **CSV:** `prnt_tweets_lst_csv()` in `admin/functions/common.php` — tab-separated, `.xls` content-disposition
- **PDF:** `prnt_tweets_lst_pdf()` — FPDF landscape table

Session filters (`filterstart`, `filterend`, `linked_tem`) narrow export ranges when set from the sent tweets UI.

## Frontend stack (admin)

- XHTML 1.0 Transitional template in `admin/index.php`
- CSS: `admin/style/css/transdmin.css` (+ IE6/7 overrides)
- jQuery 1.4.x + jQuery UI 1.8.x (datepicker on term forms)
- No build step; assets are static files

## Design characteristics (historical)

- **Front controller pattern** via `mode` query parameter rather than a framework router
- **Active record–style table classes** without an ORM
- **Global state** (`$dbh`, table singletons, `$tweetmanage` in cron)
- **Bundled dependencies** (PEAR tree in-repo) typical of pre-Composer PHP deployments
