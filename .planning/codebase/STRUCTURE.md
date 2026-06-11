---
title: Directory Structure
focus: arch
last_mapped_commit: 62e29ef718631beaccba36396ec8eeb0135ee789
last_mapped_date: 2026-06-11
---

# Directory Structure

## Layout

```
google-workspace-smtp-/                  (repo root)
├── CLAUDE.md                            # AI assistant guidance for this repo
├── CHANGELOG.md                         # Keep a Changelog / SemVer history
├── README.md                            # User-facing setup & troubleshooting docs
├── .gitignore                           # WordPress / macOS / build ignores
├── google-workspace-smtp.zip            # Packaged plugin for manual upload
└── google-workspace-smtp/               # The actual plugin folder (deployable unit)
    └── google_workspace_smtp.php        # ENTIRE plugin implementation (271 lines)
```

Plus `.DS_Store` (macOS cruft; matched by `.gitignore` but present in the tree).

## Key Locations

| Need | Go to |
|------|-------|
| All plugin logic | `google-workspace-smtp/google_workspace_smtp.php` |
| Hook registration | `...php:19-25` (constructor) |
| SMTP configuration | `...php:141-169` (`configure_smtp`) |
| Admin settings page HTML | `...php:181-246` (`options_page`) |
| Field renderers | `...php:95-135` (`*_render`) |
| Settings sanitization | `...php:76-93` (`sanitize_settings`) |
| Test-email handling | `...php:213-228` (inside `options_page`) |
| Activation notice | `...php:253-270` |
| User documentation | `README.md` |
| Version history | `CHANGELOG.md` |
| AI/dev conventions | `CLAUDE.md` |

## The Deployable Unit

Only the `google-workspace-smtp/` **folder** is the plugin. To install, that folder
goes into `wp-content/plugins/`. The root-level files (`README.md`, `CHANGELOG.md`,
`CLAUDE.md`, `.gitignore`, the zip) are project/repo scaffolding and are **not** part
of what runs in WordPress.

> Note: the plugin header lives inside the `.php` file, so WordPress discovers the
> plugin by scanning that file — there is no separate manifest.

## Naming Conventions (filesystem)

- Plugin folder uses kebab-case matching the text domain: `google-workspace-smtp`.
- Main PHP file uses snake_case: `google_workspace_smtp.php` (note: differs from
  the folder name — WordPress does not require them to match).
- Option key and prefix use the `gws_` short prefix (`gws_smtp_settings`,
  `gws_smtp_*` functions).

## What's Absent (by design, for a plugin this size)

- No `includes/`, `admin/`, `assets/`, or `languages/` subfolders.
- No `uninstall.php` (settings are left in the DB on delete).
- No translation `.pot`/`.po`/`.mo` files despite the code being fully translatable.
- No tests, CI config, or build directories.
