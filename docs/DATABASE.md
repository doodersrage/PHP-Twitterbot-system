# Database Schema

Schema source: [`twitter-min.sql`](../twitter-min.sql) (exported February 2012). Engine: **MyISAM**, charset: **latin1**.

## Entity relationship (logical)

```
system_users (1) ──< twitter_users (N)     [via twitter_users.sid]
twitter_users (1) ──< twitter_terms (N)   [via twitter_terms.userid + sid]
twitter_users (1) ──< tweets (N)          [via tweets.userid + sid]
twitter_terms (1) ──< tweets (N)          [via tweets.linked_tem → term id]
twitter_users (1) ──< twitter_user_check  [sent mention log]
twitter_users (1) ──< retweets            [incoming mentions captured]
system_users (N) ──> user_levels (1)      [via system_users.user_level]
```

There are no foreign key constraints (MyISAM); relationships are enforced in application code.

## Tables

### `system_users`

Administrative login accounts for the web UI.

| Column | Type | Description |
|--------|------|-------------|
| `id` | int PK AI | System user ID (`sid` elsewhere) |
| `email` | varchar(140) | Login email |
| `password` | varchar(60) | Salted MD5 via `pass_salt()` |
| `added` | datetime | Account creation time |
| `last_login` | datetime | Last successful login |
| `last_ip` | varchar(15) | Last login IP |
| `user_level` | tinyint | FK → `user_levels.id` |
| `options` | text | Serialized per-user option overrides |

**Seed row:** `admin@localhost.com` / password hash for `pass`, level `1` (Admin).

### `user_levels`

Named tiers controlling feature limits.

| Column | Type | Description |
|--------|------|-------------|
| `id` | int PK AI | Level ID |
| `name` | varchar(120) | Display name |
| `description` | text | Human description |
| `cost` | decimal(10,2) | Legacy billing field |
| `options` | text | PHP serialized capability limits |

**Seeded levels:**

| ID | Name | Highlights |
|----|------|------------|
| 1 | Admin | Full access; unlimited accounts (`twitter_accounts = 0` means no cap in UI checks) |
| 2 | Super User | 20 accounts, 20 terms, 50 tweets, 20 retries |
| 3 | User | 5 / 5 / 40 / 5 |
| 4 | Free User | 1 / 1 / 10 / 1 |

Option keys used in admin UI:

- `user_access`, `user_levels`, `all_twitters`
- `twitter_accounts`, `twitter_terms`, `twitter_tweets`, `twitter_retries`

Value `0` for numeric limits typically means **unlimited** in listing/form guard checks.

### `twitter_users`

Connected Twitter/X accounts.

| Column | Type | Description |
|--------|------|-------------|
| `id` | int PK AI | Twitter account record ID (`userid` elsewhere) |
| `username` | varchar(20) | Twitter handle |
| `password` | varchar(140) | Legacy password field (pre-OAuth primary auth) |
| `enabled` | int(1) | `1` = cron processes this account |
| `sid` | int | Owning `system_users.id` |
| `api_key` | varchar(255) | Legacy API key field |
| `consumer_key` | varchar(255) | OAuth 1.0a consumer key |
| `consumer_secret` | varchar(255) | OAuth consumer secret |
| `oauthtoken` | varchar(255) | OAuth access token |
| `oauthsecret` | varchar(255) | OAuth access token secret |

Cron selects rows where `enabled = 1`.

### `twitter_terms`

Search keywords / campaigns per Twitter account.

| Column | Type | Description |
|--------|------|-------------|
| `id` | int PK AI | Term ID (linked from `tweets.linked_tem`) |
| `userid` | int | `twitter_users.id` |
| `sid` | int | `system_users.id` |
| `term` | varchar(140) | Search query string |
| `datestart` | date | Optional campaign start |
| `dateend` | date | Optional campaign end |
| `city` | varchar(255) | Geo filter: city |
| `state` | varchar(255) | Geo filter: state |
| `radius` | int | Geo radius in miles |
| `max_tweets` | bigint | Max sends for this term (`0` = unlimited) |
| `tweet_count` | bigint | Sends so far (incremented on success) |
| `enabled` | tinyint(1) | `1` = active in cron |

Cron filter (active terms):

```sql
enabled = 1 AND ((max_tweets < tweet_count) OR (max_tweets = 0))
```

When `max_tweets` is non-zero and `tweet_count >= max_tweets`, the term is skipped.

### `tweets`

Message templates / queue entries.

| Column | Type | Description |
|--------|------|-------------|
| `id` | int PK AI | Tweet row ID |
| `modified` | timestamp | Last update |
| `tweet` | varchar(140) | Message body (without `@mention` prefix) |
| `userid` | int | `twitter_users.id` |
| `sid` | int | `system_users.id` |
| `linked_tem` | int | `twitter_terms.id` this tweet belongs to |
| `max_uses` | bigint | Per-template send cap (`0` = unlimited) |
| `uses` | bigint | Times this template was sent |

Random selection (`tweets::get_random()`):

```sql
WHERE userid = ? AND sid = ? AND linked_tem = ?
  AND ((max_uses < uses) OR (max_uses = 0))
ORDER BY RAND() LIMIT 1
```

Note: the condition uses `<` not `<=`, matching the term quota pattern in cron.

### `twitter_user_check`

Log of automated (and some manual) mentions sent.

| Column | Type | Description |
|--------|------|-------------|
| `id` | int PK AI | Log entry ID |
| `userid` | int | `twitter_users.id` |
| `sid` | int | `system_users.id` |
| `username` | varchar(20) | Target Twitter user mentioned |
| `tweet_id` | int | Source template from `tweets.id` |
| `tweet` | text | Full message sent (including `@user` prefix) |
| `sent` | timestamp | Send time |
| `profile_image` | text | Cached profile image URL |

Dedupe logic: before sending, bot counts rows with same `username` + `userid` + `sid`.

### `retweets`

Stored mentions / interactions pulled from the API.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | Twitter status ID |
| `screen_name` | varchar(255) | Author handle |
| `profile_image_url` | text | Avatar URL |
| `url` | text | User profile URL |
| `friends_count` | int | Snapshot metric |
| `followers_count` | int | Snapshot metric |
| `favourites_count` | int | Snapshot metric |
| `listed_count` | int | Snapshot metric |
| `name` | varchar(255) | Display name |
| `data` | text | `serialize()` of full API status array |
| `userid` | int | `twitter_users.id` |
| `sid` | int | `system_users.id` |
| `rt_date` | datetime | Status created time |

Collection stops at the first already-known tweet ID (assumes chronological DESC order from API).

## Password hashing

Admin passwords are not stored as bcrypt/scrypt. The helper in `admin/functions/common.php`:

```php
$salt = '1sa1t9a5s*@';
$new_pass = md5($salt . $password);
```

Generate a hash for a new password by running `admin/pass.php` locally (remove or protect on production servers).

## Backup considerations

- OAuth secrets in `twitter_users` are sensitive—encrypt backups at rest.
- `retweets.data` can grow large due to serialized payloads.
- No migrations folder; schema changes require manual SQL and code updates.

## Charset note

Tables use `latin1`. Emoji and full Unicode in tweet bodies may not round-trip correctly without converting schema to `utf8mb4` (would be a modernization task outside stock documentation scope).
