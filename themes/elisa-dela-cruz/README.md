# Elisa Dela Cruz — WordPress theme

Custom ACF PRO theme for elisaarmas.com, B− design (v1.4.0). Text domain / slug: `elisa-dela-cruz`.

The full spec lives in the repo under `docs/redesign/`:

- Content model and every ACF field: `docs/redesign/03-acf-schema.md`
- Templates, parts, JS and performance: `docs/redesign/04-build-spec.md`
- Verified content to enter: `docs/redesign/05-content-entry.md`
- QA checklist: `docs/redesign/06-tasks-and-qa.md`

## Layout

| Path | What |
|---|---|
| `functions.php` | Setup, options page, ACF helpers (`edc_opt`, `edc_field`, `edc_repeater`), assets |
| `inc/content-model.php` | `project` CPT, `project_type` / `project_platform` taxonomies, `testimonial` CPT |
| `inc/template-helpers.php` | `edc_project()`, `edc_testimonial()`, `edc_career_log()`, `edc_verified_stats()`… |
| `inc/projects-query.php` | `/projects/` filters, case-study redirect, next case study, CreativeWork schema |
| `inc/visibility.php` | Section toggles, nav, footer links, GA |
| `inc/seo-names.php` | Person schema (name, title, portrait, sameAs) |
| `inc/contact-form.php`, `inc/security.php` | CF7 styling and spam checks, hardening |
| `front-page.php` + `template-parts/home/` | Homepage sections |
| `archive-project.php`, `single-project.php`, `template-parts/project/` | Projects archive and case studies |
| `page-templates/` | About, Services, Contact |
| `assets/css/tokens.css` | Generated from `design/tokens.json` by `docs/redesign/tools/build-tokens.py`. Don't edit by hand |
| `assets/css/site.css`, `assets/js/site.js` | All styles and behaviour (no jQuery, no GSAP) |
| `acf-json/` | ACF local JSON (load and save path) |
| `template-parts/legacy/` | Retired homepage sections, kept for reference only |

## Rules

Empty fields render nothing. Testimonials need `permission_confirmed`, stats need `verified`, and confidential projects never output a real client name, domain, logo or screenshot. See `.cursor/rules/elisa-redesign.mdc`.

## After deploying

1. ACF → Field Groups → sync any group marked "Sync available".
2. Regenerate thumbnails for the new `edc-shot` and `edc-portrait` sizes: `wp media regenerate --only-missing --yes`.
3. Assign the About, Services and Contact templates to those pages.
