# 04 — Build spec

What to build, file by file, inside `themes/elisa-dela-cruz/`. Read `02-design-system.md` for markup contracts and `03-acf-schema.md` for fields.

## 0. Ground rules for the code

- **PHP 7.4 compatible.** No build step: plain PHP, CSS and vanilla JS (ES2018).
- Read ACF only through the helpers (`edc_opt`, `edc_field`, `edc_repeater`, and the new ones in `inc/template-helpers.php`). Templates must work when ACF is inactive (helpers return defaults/empties).
- **Escape everything**: `esc_html`, `esc_attr`, `esc_url`, and `edc_inline_html()` for fields marked "Allowed HTML". Never `echo get_field()` raw.
- **No fallback content** beyond the approved defaults already stored in the ACF JSON. Delete any hardcoded copy you find in templates that is not one of those defaults.
- Template parts get data through `get_template_part( $slug, null, $args )` and read `$args`. No globals.
- Keep files small: one section or component per template part.
- Keep the existing security, contact-form and visitor-log code working (`inc/security.php`, `inc/contact-form.php`, the `elisaarmas-visitor-log` plugin).

## 1. Assets

| File | Action |
|---|---|
| `assets/css/tokens.css` | Already generated. Enqueue first (`edc-tokens`). |
| `assets/css/site.css` | **New.** Start from `docs/redesign/reference/components.css`. Enqueue after tokens (`edc-site`, deps `edc-tokens`). |
| `style.css` | Keep the theme header comment. Move the rules still needed by un-migrated parts (CF7 modal form, back-to-top, admin preview badge) into `site.css`, then delete the rest. Bump `Version` in the header and `EDC_VERSION` in `functions.php` together. |
| `assets/js/site.js` | **New** vanilla script replacing `main.js` (see §6). Enqueue in the footer with `defer`. |
| `assets/js/main.js` | Delete once `site.js` covers everything. Port, don't rewrite: the CF7 challenge (`edcFillCf7Challenge`), the modal focus trap and focus return, the mobile nav and the GA4 click tracking. |
| GSAP | Remove the `edc-gsap` enqueue. Nothing in B− needs it. |
| Google Fonts | Replace the current URL with the one at the top of `components.css` (remove its `@import` from `site.css` and enqueue the URL instead). Add `<link rel="preload">` for Manrope 700 and Fraunces 500 italic only. |

## 2. PHP structure

```
functions.php                 setup, enqueue, requires (keep existing helpers)
inc/content-model.php         NEW  CPTs, taxonomies, term seeding, admin notices  (03 §2)
inc/template-helpers.php      NEW  helpers from 03 §3
inc/projects-query.php        NEW  archive filtering, ordering, case-study redirect
inc/visibility.php            update anchor map + show_work check (03 §4)
inc/seo-names.php             update schema (03 §4 Profile)
inc/contact-form.php          restyle modal CSS to tokens; logic unchanged
inc/security.php              unchanged
```

### `inc/projects-query.php`

- `pre_get_posts` on the main query of `is_post_type_archive( 'project' )`:
  - `posts_per_page` = -1 (the archive groups by tier itself).
  - `orderby` = `array( 'menu_order' => 'ASC', 'date' => 'DESC' )`.
  - Read `$_GET['type']` and `$_GET['platform']` with `sanitize_key()`. If the value is a real term slug in `project_type` / `project_platform`, add a `tax_query`. Anything else (including `all`) is ignored.
- `template_redirect`: on `is_singular( 'project' )` where `has_case_study` is off, `wp_safe_redirect( get_post_type_archive_link( 'project' ) . '#project-' . $slug, 302 ); exit;`
- `wp_robots`: add `noindex` to filtered archive URLs (`?type=` or `?platform=` present), so only `/projects/` is indexed.

## 3. Templates

### `header.php`

- Keep `wp_head()`, `body_class()`, `wp_body_open()`, the SVG gradient defs if still used.
- Add `<a class="skip" href="#main">Skip to content</a>` as the first focusable element.
- Remove the cursor glow and the grain overlay from the header (grain moves into the hero only).
- New nav markup (`.nav > .wrap.nav-in`): logo (`logo_icon`, `alt` = site name), menu button (`.menu-btn`, `aria-expanded`, `aria-controls="menu"`), `<ul id="menu">`.
- Menu: the `primary` menu location if assigned; otherwise `edc_primary_menu_fallback()` updated to **Work** `/projects/`, **Services** `/services/`, **About** `/about/`, **Contact** `/contact/`, then the CTA `<a class="btn btn-primary js-open-contact" href="/contact/">Start a project</a>` (label from `nav_cta_label`, default "Start a project"). Mark the current page with `aria-current="page"`.

### `footer.php`

- `.site-footer`: `© {year} {profile_name}`, footer nav (Work · Services · About · Contact), then CV ↓, LinkedIn, GitHub, Email, each only when set. Keep the existing social repeater as a fallback for LinkedIn/GitHub.
- Keep the contact modal include and back-to-top button.

### `front-page.php`

Replace the sections array with:

```php
$sections = array(
	array( 'show_hero',         'Hero',            'template-parts/home/hero' ),
	array( 'show_home_bridge',  'Bridge',          'template-parts/home/bridge' ),
	array( 'show_work',         'Work',            'template-parts/home/work' ),
	array( 'show_home_called',  'Called in for',   'template-parts/home/called' ),
	array( 'show_home_about',   'Behind the code', 'template-parts/home/about' ),
	array( 'show_home_record',  'Track record',    'template-parts/home/record' ),
	array( 'show_cta',          'Contact',         'template-parts/home/contact' ),
);
```

Keep the `edc_should_show_section()` / `edc_section_open()` / `edc_section_close()` loop as is.

Move these to `template-parts/legacy/` (unused, kept for reference): `section-hero.php`, `section-proof-testi.php`, `section-hire.php`, `section-believe.php`, `section-toolbox.php`, `section-services.php`, `section-map.php`, `section-work.php`, `section-help.php`, `section-testimonials.php`, `section-cta.php`.

### Homepage parts (`template-parts/home/`)

| Part | Section element | Reads | Notes |
|---|---|---|---|
| `hero.php` | `<section class="hero" id="top">` + `.grain` | `home_title_line`, `home_h1`, `home_lede`, `home_cta_*`, `home_cv_prompt`, `cv_file`, `home_terminal_title`, `home_terminal` | H1 = `<span class="title-line">` + headline. Primary button → `#work` if Work is public, else `/projects/`. Secondary opens the modal. CV line only with a CV. Terminal lines: `kind` → class `p` / `o` / `ok`, `--i` = row index, `link` wraps the text after "✔ ". The last `ok` line gets the blinking cursor. Classes `rise d1…d4` for the entrance. |
| `bridge.php` | `<section class="bridge">` | `home_bridge` | `<p>` with `edc_inline_html()`. |
| `work.php` | `<section class="sec light" id="work">` | `home_work_heading`, `home_work_intro`, `home_featured_projects`, `home_more_heading`, `home_more_projects` | Path eyebrow `~/elisa/work`. Filter nav (All · Built · Solved) only if both kinds exist among the featured projects. Hint + swipe hint. `.work-grid.swipe.v2` with a card per featured project via `template-parts/project/card` (`context` = `home`), then the more tile. |
| `called.php` | `<section class="sec" id="services">` | `home_called_heading`, `home_called` | `.called-grid` of up to 4. Hide the link when `link` is empty. |
| `about.php` | `<section class="sec light" id="about">` | `portrait`, `home_about_heading`, `home_about_story`, `home_principles`, `home_pull_testimonial`, `home_lead_title`, `home_lead_text` | `.btc` grid. Hex portrait only with a portrait. Story: one `<p>` per line. Pull quote via `edc_testimonial()` (short version if set); skip if `null`. Lead strip only with text. "More about me →" to `/about/` if that page is published. |
| `record.php` | `<section class="sec" id="track-record">` | `home_record_heading`, `edc_verified_stats()`, `edc_career_log()`, `home_log_visible`, `home_testimonials` | Heading falls back to "Track record." when empty. Stats row only with verified stats. Timeline via `template-parts/common/commit-log` (`visible` = `home_log_visible`). Testimonials via `template-parts/common/testimonial`, `.testis.three` if three, `.testis` otherwise; skip the block when none pass `edc_testimonial()`. |
| `contact.php` | `<section class="contact" id="contact">` | `home_contact_heading`, `home_contact_intro`, `home_contact_button`, `cv_file`, `contact_email`, `linkedin_url`, `github_url`, `reply_promise`, `location_line` | Alt row: copy-email `<button data-copy>` (with a `mailto:` link as the no-JS fallback), LinkedIn, GitHub. Meta line = `reply_promise · location_line`, skipping empties. Live region `#copy-status`. |

### Project parts (`template-parts/project/`)

| Part | Args | Output |
|---|---|---|
| `card.php` | `post_id`, `context` (`home` / `archive`), `index` | The card from 02 §Project card. `context=home` adds `span-4` / `span-3` from `card_span` and `lead` on the wide card. Adds `data-reveal`. `id="project-{slug}"` (the redirect target). |
| `visual.php` | `project` (normalised array) | Switches on `visual.mode`: `screenshot` → one `<img>` (`wp_get_attachment_image( $id, 'large', false, array( 'loading' => 'lazy', 'alt' => … ) )`), `split` → `.shot-img.split` with two, `illustration` → `visual-{illustration}.php`, `terminal` → `.mock` + `.mock-label`. Adds `.badge` when set. Alt text: "{client} homepage" for screenshots; never for confidential projects (they can't have screenshots). |
| `visual-swatches.php` | — | The swatch + filter illustration from the prototype, `role="img"` with `aria-label="Illustration: colour swatches with product filters"`. |
| `behind.php` | `project` | Panel with `head` (`$ cat behind.log` + `behind_meta`) and the `dl`. Nothing when there are no rows. |
| `compact.php` | `post_id` | Archive "Selected work" card: smaller visual, name, platform · region, role, one-line summary, live link only when not confidential (`rel="noopener"`, `target="_blank"`, "Visit site ↗"). |
| `highlight.php` | `post_id` | Archive "Technical highlights": mono block with title + behind rows, no visual. |

### Common parts (`template-parts/common/`)

`commit-log.php` (args `entries`, `visible`), `testimonial.php` (arg `testimonial` array), `stats.php` (arg `stats`), `path-eyebrow.php` (arg `path`).

### `archive-project.php` (`/projects/`)

1. Night header: path eyebrow `~/elisa/work`, H1 "The work behind the websites", intro "Builds you can see, and fixes you mostly can't."
2. Cream section: Kind filter (All · Built · Solved) and Platform filter (All platforms · WordPress · Shopify · WooCommerce · Elementor; only platforms with at least one project). Both are links built with `add_query_arg()`; both keep the other's value; `aria-current` on the active one.
3. **Featured** (tier `featured`): `.work-grid` of `card.php` with `context=archive`.
4. **Selected work** (tier `selected`): grid of `compact.php`, 3 columns desktop / 1 mobile.
5. **Technical highlights** (tier `highlight`): list of `highlight.php`. Hide headings of empty tiers.
6. If the filtered result is empty: "Nothing here yet for that filter." + link to `/projects/`.
7. Contact CTA strip: "Got one like these? Tell me about it." + Start a project.

### `single-project.php` (case study)

Only reached when `has_case_study` is on (see redirect above).

1. `.cs-hero`: crumbs (`~/elisa/work / {slug}` with a link to `/projects/`), tags, H1 (`cs_headline` or title), lede (`cs_lede`).
2. The project visual in a `.shot` frame (`.cs-shot`).
3. `.cs-layout`: **At a glance** (`at_a_glance` rows; skip the box when empty) + `.cs-body` with, in order and each only when filled: "The situation" (`situation`), "What made it tricky" (`tricky`), "What I did" (`approach` as `<ol>`), `<details class="tech">` "▸ Technical breakdown" (`technical_breakdown`), "The result" (`result`), "What I'd tell the next client" (`learned`), the testimonial (`cs_testimonial` through `edc_testimonial()`).
4. `.cs-next`: next featured project that has a case study (by `menu_order`, wrapping to the first) + "Got one like this? Start a project →" (opens modal).
5. Schema: `CreativeWork` with `name`, `about`, `author` → the Person `@id` Yoast outputs. Confidential projects: no client name in schema.

### Page templates (`page-templates/`)

| Template | Sections |
|---|---|
| `about.php` | Night hero: H1 `about_h1`, `about_lede`, portrait (hex). Story (`about_story`). Full career timeline (`commit-log`, all visible). Leadership (`about_leadership`). How I work (`about_principles`, numbered). Things I've learned (`about_lessons`, only if set). A little more (`about_more`). Names paragraph (`about_names`, small mono). CTA: Download CV (if set) + Start a project. |
| `services.php` | Night hero: H1 `services_h1`, `services_lede`. Engagements (`services_engagements`): one block each with who it's for, what's included (`<ul>`, one item per line), how it usually runs, related projects as links. Process strip (`services_process`, numbered). Agencies block (only with text). FAQ (`services_faq` as `<details>`). CTA. |
| `contact.php` | H1 `contact_h1`, `contact_lede`, the CF7 form inline (`edc_contact_form()`), alternatives (email copy, LinkedIn), What happens next (`contact_next_steps`, numbered), `reply_promise` · `location_line`. |

All three: use `edc_inline_html()` for intros, hide every empty block, one H1.

## 4. Visibility and navigation logic

- `inc/visibility.php`: update `edc_section_anchor_map()` to `work` → `show_work`, `services` → `show_home_called`, `about` → `show_home_about`, `track-record` → `show_home_record`, `contact` → `show_cta`. Change `edc_has_published_projects()` to check the IDs in `home_featured_projects` for at least one published project.
- `edc_link_is_usable()` already hides empty, `#` and hidden-section links. Use it for every link built from a field.

## 5. SEO and schema

- `inc/seo-names.php`: Person `name` from `profile_name`; `jobTitle` from `profile_title`; `image` from `portrait`; `sameAs` from `linkedin_url` / `github_url` (fallback: footer social). Keep `alternateName` and `knowsAbout`.
- Don't change Yoast titles in code. Elisa sets page titles in Yoast after checking Search Console (decision pending).
- Filtered archive URLs are `noindex` (see §2).
- Every page: exactly one H1; headings in order.

## 6. JavaScript (`assets/js/site.js`)

One IIFE, no dependencies. Everything progressive: the page is complete without it.

| Feature | Behaviour |
|---|---|
| `.js` class | Add `js` to `<html>` first thing (enables reveal hiding). |
| Nav | `.scrolled` after 30px scroll (passive listener). Menu button toggles `.open`, `aria-expanded`, label "Open menu" / "Close menu". Escape closes and returns focus. |
| Reveal | IntersectionObserver adds `.in` to `[data-reveal]` once (threshold .12). Reduced motion or no IO: add `.in` to all immediately. |
| Work filter | Click on `.filters a[data-type]`: prevent default, show/hide `.work-grid .card` by `data-type` (the more tile shows for All only), update `aria-current`, `history.replaceState` with the link's href, live region text "Showing N built projects". Wrap in `document.startViewTransition` when available and motion is allowed. On load, apply `?type=` if present. Archive pages filter server-side, so skip in-place filtering there (detect `body.post-type-archive-project`). |
| Behind toggle | `.behind-toggle` toggles `.show-behind` on the card and `aria-expanded`; on open focus `.behind-close`; close returns focus to the toggle. |
| Timeline | If `.log` has more than `data-visible` entries: add `.collapsed`, un-hide `.log-more`; the button toggles `.collapsed`, `aria-expanded` and its label "(show …)" / "(hide …)"; on expand focus the first `li.older summary`. |
| Copy email | `[data-copy]` click: `navigator.clipboard.writeText` (catch rejection), label "Copied ✓" for 1.6s, live region "Email address copied". |
| Modal | Port from `main.js`: open on `.js-open-contact` (prevent default), CF7 challenge fill, focus first focusable, trap Tab, Escape / backdrop / close button closes, return focus (menu button fallback on mobile). |
| GA4 | Port the delegated click tracker from `main.js`. Events: `cta_start_project` (any `.js-open-contact`), `cv_download`, `case_study_open` (card links), `filter_change` (param `type`), `terminal_link_click`, `contact_form_submit` (CF7 `wpcf7mailsent` event). Use `data-ga-event` attributes in templates. |

## 7. Performance budget

- JS ≤ 30 KB gzipped (excluding GA). CSS ≤ 40 KB uncompressed.
- No layout shift: every image has `width`/`height`; the hero has no images.
- Screenshots: register an image size `edc-shot` (1200×705, hard crop from top) and serve it with `srcset`. Upload WebP/AVIF where possible.
- Portrait: `edc-portrait` (600×672).
- Remove wp-emoji (done), GSAP, the loader (done), particles, marquee and cursor glow.
- Target on mobile: LCP < 1.8s, CLS < 0.05, INP < 200ms.
