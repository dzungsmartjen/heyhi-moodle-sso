# HeyHi SSO for Moodle (example plugin)

Example Moodle local plugin (`local_heyhi`) that signs a logged-in Moodle user in to HeyHi.

It adds a **Go to HeyHi** link to the Moodle navigation menu. When the user clicks it, the plugin:

1. Generates a single-use SSO token by calling the HeyHi API from the Moodle server.
2. Redirects the browser to `https://{branch-url}/profile?token={token}`.

> This is a starting point for a school's developers, not a finished product.
> It has not been tested on a live Moodle site.

## Requirements

- Moodle 4.4 or later (uses the `primary_extend` navigation hook).
- An OAuth2 client ID and client secret issued by HeyHi.
- Moodle usernames must be the same as the users' HeyHi usernames.

## Install

```bash
git clone https://github.com/dzungsmartjen/heyhi-moodle-sso.git <moodle>/local/heyhi
```

Then visit *Site administration > Notifications* to complete the installation, and fill in the
settings under *Site administration > Plugins > Local plugins > HeyHi SSO*:

| Setting | Value |
| --- | --- |
| HeyHi API base URL | `https://api.heyhiapi.com` |
| OAuth2 client ID / secret | Issued by HeyHi |
| HeyHi branch URL | The school's HeyHi site |
| Parent role short name | Moodle role that maps to the HeyHi Parent role (optional) |

## Things to adapt

- **Role mapping** — `client::get_heyhi_role()` maps teacher-type roles to Tutor (1), the configured
  parent role to Parent (3), and everyone else to Student (2). Change it to match your own roles.
- **Who sees the link** — it is shown to every logged-in, non-guest user.

## How it works

| File | Purpose |
| --- | --- |
| `sso.php` | Page behind the menu link: gets the token and redirects. |
| `classes/client.php` | Server-to-server API calls (`/oauth2/token`, `/api/v1/access`) and role mapping. |
| `classes/hook_callbacks.php`, `db/hooks.php` | Add the link to the navigation menu. |
| `settings.php` | Admin settings page. |

Notes on the HeyHi API:

- `/oauth2/token` uses the client-credentials grant (`grant_type`, `client_id`, `client_secret`).
  The access token is cached until shortly before it expires.
- `/api/v1/access` reports failures as HTTP 200 with `{"success": false, "message": "..."}`.
- The SSO token is returned already URL-encoded. Append it to the redirect URL as-is.
