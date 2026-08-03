# Admin Guide

How to use the web dashboard at `/admin/` to configure accounts, campaigns, and reports.

## Logging in

1. Navigate to `https://your-domain/admin/`.
2. Enter email and password (default: `admin@localhost.com` / `pass`).
3. On success, the **Dashboard** lists your Twitter accounts.

Failed login shows a jQuery UI dialog: *"The login information provided does not appear to be valid..."*

## Navigation

### Top menu (authenticated)

| Link | Purpose |
|------|---------|
| **Dashboard** | Twitter account list (home) |
| **Account Options** | Email and personal settings |
| **Manage Users** | System user CRUD (admin only) |
| **User Levels** | Tier/limit configuration (admin only) |
| **Logout** | Ends session |

### Sidebar actions (contextual)

Appears based on current `mode`:

- **New Account** — on dashboard when under account limit
- **Add Term** — while editing terms for an account
- **New Tweet** — while editing tweets for an account
- **Add User** / **Add Level** — on respective admin screens

## Typical setup workflow

### 1. Create a Twitter account record

1. Dashboard → **New Account** (or sidebar link).
2. Fill in:
   - **Username** — Twitter handle
   - **Password** — legacy field; may still be stored but OAuth drives API calls
   - **Enabled** — must be checked for cron to use the account
   - **Consumer key / secret** — OAuth application credentials
   - **OAuth token / secret** — user access tokens
   - **API key** — legacy; often same as consumer key depending on token workflow
3. Submit → returns to dashboard.

**Edit** an existing row from the dashboard action links.

### 2. Add search terms (campaign triggers)

1. From dashboard, open **Terms** for an account (`?mode=edit_terms&userid=N`).
2. **Add Term** in sidebar.
3. Configure:

| Field | Usage |
|-------|-------|
| Term | Keyword/query passed to Twitter search |
| Enabled | Must be on for cron |
| Start / End | Optional date window (jQuery UI datepicker) |
| City / State / Radius | Optional geo-constrained search (miles) |
| Max Tweets | Cap on total sends for this term (`0` = unlimited) |

4. Save → term list for that account.

Each term receives an `id` used when linking tweet templates.

### 3. Build tweet queues

1. Open **Tweets** for the account (`?mode=edit_tweets&userid=N`).
2. **New Tweet**.
3. Fields:

| Field | Usage |
|-------|-------|
| Tweet | Message body (140 chars); `@username` is prepended automatically by the bot |
| Linked Term | Select which search term this message belongs to |
| Max Uses | Per-template cap (`0` = unlimited) |
| Uses | Read-only counter incremented on send |

4. Add multiple templates per term for random rotation.

### 4. Run the bot

Ensure cron hits `twitter_cron.php`. After a cycle:

- Check **Sent Tweets** for the account.
- Inspect **Retweets** for captured mentions.

### 5. Manual tweet (optional)

**Send Tweet** mode posts immediately through the selected account without waiting for cron. The message is also logged in `twitter_user_check`.

## Dashboard account actions

For each Twitter account row, typical links include:

| Action | Description |
|--------|-------------|
| Edit | Account credentials and enabled flag |
| Terms | Manage search terms |
| Tweets | Manage message queue |
| Sent Tweets | View/filter/export mention log |
| Retweets | View stored mentions |
| Send Tweet | One-off manual post |

Exact labels are in [`admin/forms/user_listings.php`](../admin/forms/user_listings.php).

Bulk operations on the dashboard:

- Checkboxes + delete to remove accounts (cascades tweets, terms, logs for that account).
- **Enable/disable** toggles per account before submitting the listing form.

## Sent tweets and exports

**Sent Tweets** shows historical `@mention` activity with optional date and term filters stored in session.

Export buttons:

| Export | URL pattern | Format |
|--------|-------------|--------|
| CSV | `?mode=sent_tweets_csv&userid=N` | Tab-separated, Excel-friendly |
| PDF | `?mode=sent_tweets_pdf&userid=N` | Landscape FPDF table |

Reports include username, tweet text, and sent timestamp.

## System user management (Admin)

**Manage Users** (`user_access` required):

- List, add, edit, delete system users.
- Assign **user level** from dropdown.
- Deleting a system user removes associated Twitter data scoped by `sid`.

**User Levels** (`user_levels` required):

- Define tier names, descriptions, and serialized limits.
- Controls max accounts, terms, tweets, and retries for members of that tier.

## Account options

**Account Options** lets non-admin users update their email and serialized `options` blob (if exposed in the form). Admins use **Manage Users** for level changes.

## User level limits in the UI

When creating resources, the sidebar **Add** links hide once you hit:

- `twitter_accounts` — max Twitter accounts
- `twitter_terms` — max terms per account
- `twitter_tweets` — max queued tweets per account

Admin level (`twitter_accounts = 0`, etc.) treats `0` as unlimited in guard checks.

## Session and security notes

- Authentication is session-based (`$_SESSION['user_logged_in']`, `user_id`, `user_options`).
- No CSRF tokens on forms (legacy).
- Stay on HTTPS in production; OAuth secrets transit through admin forms in plain text over the wire if not TLS-protected.

## What the bot does (operator expectations)

On each cron run, for every **enabled** term on an **enabled** account:

1. Search Twitter for the term (optionally near a location).
2. Take the latest matching author's username.
3. If that username was never contacted by this account for this system user:
   - Pick a random eligible tweet linked to the term.
   - Post `@username {tweet text}`.
   - Increment counters; log the send.
   - Fetch and store new mentions.

If the username was already contacted, the code attempts retries up to the `twitter_retries` limit (see legacy notes for retry behavior).

This is **mention-based outreach**, not plain timeline posting—design campaigns accordingly and comply with platform rules applicable to your use case.
