# 07 — Deploy plan (elisaarmas.com)

Status: **waiting for Elisa's approval.** Nothing below has been run on the live site.

Everything runs from the Mac through `docs/redesign/tools/deploy.sh <stage>`, over the `dcx` SSH key, one stage at a time. Between stages we stop and check.

## Scope

Touched on the server:

- `public_html/wp-content/themes/elisa-dela-cruz` (replaced; the old copy is kept next to it as `elisa-dela-cruz.previous`)
- `public_html/wp-content/edc-deploy-tmp` (temporary: scripts and the project screenshots, deleted at the end)
- `public_html/wp-content/uploads` (6 project screenshots added to the Media Library, plus thumbnails for the new image sizes)
- this site's database (ACF field groups, new projects and testimonials, Site Content options, page templates, menu links)

Not touched: any other folder in `public_html`, any other website, plugins, WordPress core, `.htaccess`, `wp-config.php`.

## What the live site has today (checked 6 Nov, read-only)

| | Live now | After the deploy |
|---|---|---|
| Theme | `elisa-dela-cruz` **1.0.0** (none of the GitHub fixes are live yet) | 1.4.0 |
| Projects | none published (one empty draft, #6357, left alone) | 12 projects from `05-content-entry.md` (5 featured, 6 selected, 1 technical highlight) |
| Work section | off (`show_work` empty) | on |
| Testimonials | none | Rachel S. (shown); Christopher Sorini (saved, **hidden** until you confirm permission) |
| About / Services / Contact pages | exist, empty, default template | same pages and URLs, new templates and fields |
| `/projects/` | already the project archive (the empty "Projects" page #19 is shadowed by it and stays as it is) | the new archive |
| Main menu | Home `#top` · Services `#services` · About `#hire` · Projects `#work` · Contact `#contact` | Work · Services · About · Contact (pages, not anchors). "Home" stays in the menu but is hidden by the theme; delete it in Appearance → Menus whenever you like |
| ACF field groups in the database | Global Theme Options, old Project Details | synced from the theme's JSON (8 groups) |
| Contact form | CF7 #6245, `hello@elisaarmas.com` | unchanged, used by the modal and the Contact page |
| Yoast | Person = user 1 | unchanged; Person schema now gets your name, title and links from Profile & Links |

Kept as is: `contact_email`, `cf7_shortcode`, the old Global Theme Options data (nothing is deleted), the visitor-log plugin (no changes).

## Decisions for you before we start

1. **Nav button label.** Live has `nav_cta_label` = "Schedule a call". The design uses "Start a project". Keep yours, or change it? (Default in this plan: keep "Schedule a call".)
2. **Backup location on your Mac:** `~/Backups/elisaarmas-deploy/<date-time>/`. OK, or somewhere else?
3. **When:** the content is incomplete for 2–3 minutes between the theme swap and the end of the import. Pick a quiet time.

## Stages

| # | Stage | What it does | Stop and check |
|---|---|---|---|
| 0 | `preflight` | Repo committed and lint-clean; live site is elisaarmas.com, theme 1.0.0, ACF PRO active; no leftovers from an earlier deploy | Output looks right |
| 1 | `backup` | Database (`wp db export`, streamed to the Mac, nothing written on the server) → `db.sql.gz`; theme folder → `theme/`; menu and key options → `settings.txt`. Checks the dump unzips and counts tables | Backup folder exists, table count > 0 |
| 2 | `upload` | Uploads the theme to `elisa-dela-cruz.incoming`, the scripts and screenshots to `edc-deploy-tmp`, then swaps folders (`elisa-dela-cruz` → `.previous`, `.incoming` → `elisa-dela-cruz`). The swap is two renames, under a second | Site loads on the new theme |
| 3 | `migrate` | ACF sync (dry run printed first; doesn't rewrite the theme's JSON) → content import (refuses the live site unless the run carries the approval flag; ⏳ fields stay empty) → live settings (dry run printed first: Work on, page templates, menu links and order) → thumbnails for the new sizes → cache and permalink flush | Each dry run matches this plan |
| 4 | `verify` | Fetches home, `/projects/`, a filtered view, the case study, About, Services, Contact: each must return 200, have one H1, no `href="#"`, no confidential name. Checks the case-study redirect. Shows any PHP errors from the theme | All lines pass. Then you look at the site |
| 5 | `cleanup` | Deletes `edc-deploy-tmp` | — |
| 6 | later, after you confirm | Delete `themes/elisa-dela-cruz.previous` | Your OK |

## Rollback

- **Theme only (seconds):** `deploy.sh rollback-theme` puts the previous theme back (the new one is kept as `.failed-<date>`).
- **Everything:** `deploy.sh rollback-theme` then `deploy.sh rollback-db` restores the database from the backup taken in stage 1. Anything saved on the site after the backup would be lost (CF7 doesn't store messages, so only admin edits).

Trigger rollback if stage 4 fails and can't be fixed in a few minutes, or on your word.

## Rehearsal (done locally, 6 Nov)

On the local copy, made to look like live (old anchor menu, Work off, default page templates):

- ACF sync: dry run listed 8 groups; sync created them with the same field counts as the JSON; second run "up to date"; theme JSON files unchanged.
- Import: refused with `home` set to `https://elisaarmas.com` and no flag, and with a wrong flag; ran normally otherwise; idempotent.
- Live settings: dry run and real run printed the same changes; second run changed nothing; menu renders Work · Services · About · Contact.
- `deploy.sh`: syntax-checked. The SSH, rsync and curl parts only run against the server, so they're first exercised in stages 0–2, which stop before anything irreversible.

## After the deploy (your content, when ready)

Filled in wp-admin → Site Content / the pages, never guessed: CV, LinkedIn, GitHub, portrait, availability, Chris's title, company and permission, stats, About story and leadership, Services details and FAQ, Contact next steps. Yoast page titles stay as they are until you decide.
