# Distributor Registration

WordPress plugin: multi-step distributor application form, local CSS only (no Bootstrap vendor), data stored in `{prefix}dis_*` tables.

## Installation

1. Copy the `distributors-registration-plugin` folder into `wp-content/plugins/`.
2. Activate **Distributor Registration** in **Plugins**.
3. On first load, missing database tables are created via `dbDelta()`.

## Shortcodes

Two tags render the same form:

| Tag | Notes |
|-----|--------|
| `[distributor_registration_form]` | Primary tag |
| `[distributorregistration]` | Short alias |

### Attributes

| Attribute | Default | Description |
|-----------|---------|-------------|
| `thank_you_url` | *(empty)* | After a successful submit, redirect here. If empty, the user returns to the same page (thank-you UI is shown inline). Must be allowed by `wp_validate_redirect()`. |
| `contact_email` | `info@babybrands.com` | Email shown on the thank-you screen. Can be overridden with the filter below. |

### Example usage (copy into a page or block)

```text
<!-- Default: thank-you on same page, contact email info@babybrands.com -->
[distributor_registration_form]

<!-- Custom thank-you page (absolute URL on this site is typical) -->
[distributor_registration_form thank_you_url="https://example.com/thank-you/"]

<!-- Custom contact email on thank-you screen -->
[distributor_registration_form contact_email="info@example.com"]

<!-- Both attributes -->
[distributor_registration_form thank_you_url="https://example.com/done/" contact_email="support@example.com"]

<!-- Alias shortcode, same behavior -->
[distributorregistration thank_you_url="https://example.com/done/"]
```

### Developer filter

- **`dreg_distributor_registration_contact_email`** — `( string $email, string $shortcode_tag )` — adjust the thank-you contact email per shortcode tag.

## Technical notes

- Form posts to `admin-post.php` with `action=dreg_distributor_register` (see `DREG_POST_ACTION` in the main plugin file).
- Success/error feedback uses query args `dreg_dr_status` and optional `dreg_dr_message`; the shortcode removes them from the URL with `history.replaceState` after display.
- Styles: `assets/css/distributor-registration.css` only (no CDN, no vendor CSS).

## Database

Logical tables (prefix `dis_`): country, province, city, address, distributors, distributors_status, distributors_distribution_plan, distributors_doctors. Table resolution supports legacy non-prefixed names when present.
