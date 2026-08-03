# Legacy Notes

Important context for anyone running, maintaining, or modernizing this codebase without expecting current-platform behavior out of the box.

## Timeline

| Era | Detail |
|-----|--------|
| 2009 | Initial development |
| 2010–2011 | Tweaks, OAuth migration fields added |
| 2012 | Database export (`twitter-min.sql`) |
| Present | Twitter renamed to X; APIs and auth models substantially changed |

## Deprecated / removed Twitter APIs

The bot depends on infrastructure that no longer matches the current X developer platform:

### Public Atom search

`TwitterBot::searchFor()` loads:

```
http://search.twitter.com/search.atom?q=...
```

This legacy Search API was deprecated and removed years ago. **`searchAndReturnUsername()` will fail** on modern infrastructure unless replaced with v2 search or another data source.

### Tijs Verkoyen Twitter class (v2.0.3)

Bundled at [`vendor/twitter.php`](../vendor/twitter.php) (~2010). Method names and endpoints target Twitter API v1.1-era REST:

- `statusesupdate()` for posting
- `statusesMentions()` for mention harvest
- `usersProfileImage()` for avatars

Many endpoints, rate limits, and authentication flows have changed. The library is unmaintained relative to current API versions.

### OAuth 1.0a user tokens

The admin UI stores consumer key/secret plus access token/secret per account. Modern apps often use OAuth 2.0 with PKCE, elevated access tiers, and project-based developer accounts. Token acquisition steps in commented code inside `TwitterBot::__construct()` reflect an older authorize/access-token dance.

## Security (read before production use)

| Area | Risk | Detail |
|------|------|--------|
| Password storage | High | Salted MD5 in `pass_salt()` — crackable; not suitable for new deployments |
| SQL construction | Medium | Some deletes use `execute_qry("DELETE ... id = '".$sel_id."'")` without binding |
| Secrets at rest | High | OAuth tokens in `twitter_users` are plain text |
| Error suppression | Low | `error_reporting(0)` in `config.php` hides failures |
| CSRF | Medium | Admin forms lack CSRF tokens |
| Cron URL | Medium | `twitter_cron.php` is unauthenticated if web-accessible |
| Default credentials | High | Seeded admin password is documented publicly |

**Recommendation for any revival:** rotate all secrets, replace password hashing, parameterize all SQL, protect cron, and treat this codebase as reference material for a rewrite using current SDKs.

## PHP compatibility

| Feature | Status |
|---------|--------|
| `get_magic_quotes_gpc()` | Removed PHP 5.4; guarded in `config.php` |
| PEAR / MDB2 | Uncommon on PHP 8+ without patches; bundled copy is old |
| `mysql` driver via MDB2 | May need mysqli DSN adjustments on newer stacks |
| Short open tags `<?PHP` | Works if `short_open_tag` enabled (non-standard casing) |

Expect notices or fatal errors on PHP 8+ until dependencies are updated or replaced (e.g. PDO + modern HTTP client).

## Known logic quirks (stock code)

Documented for operators debugging "why didn't it tweet?"—**not fixed** in this documentation pass per project scope.

### Date range filtering in `twitter_cron.php`

When both `datestart` and `dateend` are set, the end date is mistakenly read from `datestart` twice in one branch (`$end_date = strtotime($term_row['datestart'])`). Date-gated campaigns may not fire when intended.

Individual branches also compare `>=` / `<=` against `time()` in ways that may invert intended "run between start and end" semantics. Review carefully before relying on schedule fields.

### Retry logic in `tweetmanage::post_tweet()`

```php
if ($this->username_check($this->username) < 1) {
    // send
} elseif ($this->max_retries <= $this->cur_retry) {
    $this->cur_retry++;
    $this->post_tweet();
} else {
    $this->cur_retry = 0;
}
```

The condition uses `<=` where `>=` might have been intended, and `$this->username_check($this->username)` passes an argument to a method that takes none (harmless in PHP but confusing). Retry behavior when a username is already in `twitter_user_check` may not match the `twitter_retries` user-level setting as operators expect.

### Quota comparisons

Both cron term selection and `get_random()` use `(max_* < count)` rather than `<=`. A term with `max_tweets = 100` stops when `tweet_count` reaches 100 only if the comparison behaves as `<` vs equal—verify empirically when setting caps.

### Search returns one author

`searchAndReturnUsername()` loops entries but overwrites `$author` each iteration, effectively returning the last match in the feed, not necessarily the newest or best match.

## Operational ethics and ToS

The original `TwitterBot` header states:

> *This bot is NOT intended to be used for SPAM purpose.*

Automated `@mention` outreach may violate current platform automation rules. Anyone deploying this system is responsible for compliance with applicable terms of service, anti-spam laws, and recipient consent expectations.

## Modernization paths (suggested, not implemented)

If you need a working system today, plan for:

1. **Replace search** — X API v2 `recent search` or curated input lists instead of Atom feeds.
2. **Replace HTTP client** — Guzzle + official or community X SDK with OAuth 2.0.
3. **Replace PEAR MDB2** — PDO with prepared statements throughout.
4. **Admin refresh** — Framework auth, bcrypt/argon2 passwords, CSRF, env-based config.
5. **Schema** — utf8mb4, InnoDB, proper FKs, encrypted token column.
6. **Queue** — Decouple cron into a job worker (Redis, database queue) with rate limiting and idempotency keys.

## Related files for auditors

| Concern | Location |
|---------|----------|
| Cron orchestration | `twitter_cron.php` |
| Mention workflow | `classes/tweetmanage.php` |
| Search + post wrapper | `classes/TwitterBot.class.php` |
| API client | `vendor/twitter.php` |
| Admin routing | `admin/functions/page_procs.php` |
| Password hash | `admin/functions/common.php` → `pass_salt()` |
| Raw SQL deletes | `page_procs.php` (`execute_qry` calls) |

## Historical value

Despite platform drift, the repo illustrates patterns common in late-2000s PHP products:

- Global table models + query-string routing
- Cron-driven social automation
- Multi-tenant scoping with composite `(sid, userid)` keys
- Tiered SaaS-style limits via serialized PHP in MySQL

Useful as archival reference or as a requirements sketch for a greenfield replacement.
