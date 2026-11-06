# 03 — ACF schema and content model

All field groups for the redesign already exist as **ACF local JSON** in `themes/elisa-dela-cruz/acf-json/`. The theme loads that folder (`acf/settings/load_json` in `functions.php`). Your job is to register the post types and taxonomies they point at, write the PHP that reads them, and sync them in wp-admin. **Do not rename field names or keys**: templates, docs and content entry depend on them.

> The field tables at the bottom of this file are generated from the JSON. If you change a field group, change the JSON (or change it in wp-admin, which rewrites the JSON because `save_json` points at the same folder) and regenerate the tables with `python3 docs/redesign/tools/acf-tables.py`.

## 1. Content model at a glance

```
Options page "Site Content" (edc-site-content)
├── Global Theme Options   group_edc_site_content   (existing, legacy sections; keep, don't delete data)
├── Profile & Links        group_edc_profile        (new: name, title, portrait, CV, LinkedIn, GitHub, career log)
└── Homepage (B−)          group_edc_home_v2        (new: every homepage section)

CPT project      (existing, archive /projects/)    → Project Details  group_edc_project_fields (rebuilt)
  ├── taxonomy project_type      (new) terms: built, solved
  └── taxonomy project_platform  (new) terms: wordpress, shopify, woocommerce, elementor
CPT testimonial  (new, not public)                 → Testimonial      group_edc_testimonial_fields

Page template page-templates/about.php     → About page     group_edc_page_about
Page template page-templates/services.php  → Services page  group_edc_page_services
Page template page-templates/contact.php   → Contact page   group_edc_page_contact
```

## 2. Register these in PHP (new file `inc/content-model.php`, required from `functions.php`)

### 2.1 Update the `project` CPT

Keep everything in `edc_register_project_cpt()` and change:

- `supports`: add `page-attributes` (menu order drives archive order). Keep `title`, `thumbnail`, `excerpt`. Keep `editor` in supports but the field group hides the content editor (`hide_on_screen: the_content`) because case studies are built from fields.
- `taxonomies`: `array( 'project_type', 'project_platform' )`.

### 2.2 Taxonomies

```php
register_taxonomy( 'project_type', 'project', array(
	'labels'            => array( 'name' => 'Kinds of work', 'singular_name' => 'Kind of work' ),
	'public'            => true,
	'hierarchical'      => true,    // checkbox UI in the editor
	'show_admin_column' => true,
	'show_in_rest'      => true,
	'rewrite'           => false,   // filtering uses /projects/?type=…, no term archives
	'query_var'         => false,
) );
register_taxonomy( 'project_platform', 'project', array(
	'labels'            => array( 'name' => 'Platforms', 'singular_name' => 'Platform' ),
	'public'            => true,
	'hierarchical'      => true,
	'show_admin_column' => true,
	'show_in_rest'      => true,
	'rewrite'           => false,
	'query_var'         => false,
) );
```

Seed the terms once (on `init`, guarded by `term_exists`): `project_type` → Built (`built`), Solved (`solved`). `project_platform` → WordPress (`wordpress`), Shopify (`shopify`), WooCommerce (`woocommerce`), Elementor (`elementor`). Term **names** are what the tags display.

### 2.3 Testimonial CPT

```php
register_post_type( 'testimonial', array(
	'labels'        => array( 'name' => 'Testimonials', 'singular_name' => 'Testimonial' ),
	'public'        => false,
	'show_ui'       => true,
	'show_in_menu'  => true,
	'menu_icon'     => 'dashicons-format-quote',
	'menu_position' => 6,
	'supports'      => array( 'title' ),
	'show_in_rest'  => false,
) );
```

### 2.4 Page templates

Create `page-templates/about.php`, `page-templates/services.php`, `page-templates/contact.php`, each starting with `Template Name: About` (etc.). The field groups are located on these exact paths. Elisa assigns them to the existing `/about/`, `/services/` and `/contact/` pages in wp-admin (same URLs, so no redirects are needed).

## 3. Reading the fields: helpers to add (`inc/template-helpers.php`)

Use the existing helpers (`edc_opt()`, `edc_field()`, `edc_repeater()`, `edc_link_is_usable()`) and add these. Every helper returns an empty value when content is missing, and every template checks for empty before printing markup.

| Helper | Returns |
|---|---|
| `edc_profile( $name, $default = '' )` | A Profile & Links option. Thin wrapper over `edc_opt()` |
| `edc_cv_url()` | URL of `cv_file`, or `''` |
| `edc_years( $start, $end )` | `'2020–22'`, `'2022 →'` (end = `now`), `'2019'` (same year or no end), or `''` |
| `edc_inline_html( $html )` | `wp_kses()` allowing only `em[class]`, `strong`, `br`. Use for every "Allowed HTML" field |
| `edc_project( $post_id )` | Normalised array for one project (see 3.1) |
| `edc_project_has_case_study( $post_id )` | `has_case_study` is on |
| `edc_testimonial( $post_id )` | Normalised array, or `null` when the post is missing, unpublished or `permission_confirmed` is off |
| `edc_career_log()` | `career_log` rows with `hash` filled (first 7 chars of `md5( message . when )` when empty) |
| `edc_verified_stats()` | `home_stats` rows where `verified` is on |

### 3.1 `edc_project()` shape

```php
array(
  'id'            => 123,
  'slug'          => 'us-beauty-brand',
  'title'         => 'One beauty brand, four Shopify storefronts', // post title
  'url'           => '…/projects/us-beauty-brand/' or '',           // '' without a case study
  'kinds'         => array( array( 'slug' => 'built', 'name' => 'Built' ) ),
  'platforms'     => array( array( 'slug' => 'shopify', 'name' => 'Shopify' ) ), // falls back to platform_badge
  'chips'         => array( 'Liquid', '1,000+ SKUs' ),
  'client'        => 'US beauty brand',          // client_display_name
  'confidential'  => true,
  'industry'      => 'Beauty', 'region' => 'US',
  'role_line'     => 'Shopify developer · via Microsourcing · 2020–22', // role · engaged_via · years, empties skipped
  'summary'       => '…',                       // summary, else excerpt
  'names'         => array( array( 'name' => "Boat O'Craigo", 'note' => 'Yarra Valley' ) ),
  'span'          => 'wide' | 'half' | 'auto',
  'visual'        => array( 'mode' => 'illustration', 'images' => array( id, … ), 'illustration' => 'swatches',
                            'terminal_line' => '', 'browser_label' => '…', 'badge' => '' ),
  'live_url'      => '',                        // always '' when confidential
  'behind_meta'   => 'built · 2020–22',
  'behind_rows'   => array( array( 'key' => 'needed', 'value' => '…' ) ),
)
```

Confidential projects: `live_url` is forced empty, screenshots are ignored even if uploaded, and `browser_label` must not contain the real domain (sanitise: if it matches the client's real domain pattern, drop it).

## 4. Rules for each model

### Project

- **Tier** decides where it shows on `/projects/`: Featured (large cards, top) → Selected work (compact cards) → Technical highlights (short problem → fix notes, mono). Order inside each tier: `menu_order` ASC, then date DESC.
- **Homepage** shows only the projects picked in `home_featured_projects` (in that order) plus the "Also built" tile from `home_more_projects`. Unpublished picks are skipped.
- **Kind** (`project_type`) is required; a project with neither Built nor Solved is a content error. Show it in wp-admin as an admin notice on the edit screen, don't fail on the front end.
- **Case study**: when `has_case_study` is off, `single-project.php` is never shown: `template_redirect` sends a 302 to `/projects/#project-{slug}`.
- **Legacy fields** `gradient_start`, `gradient_end` and `platform_badge` stay in the group so nothing breaks; they're only fallbacks.

### Testimonial

- Rendered only through `edc_testimonial()`, which enforces `permission_confirmed`.
- Badge text: `$ role: client` or `$ role: employer` (`relationship`).
- Attribution: `<b>{person_name}</b> · {person_role}, {company}`, skipping empty parts.
- `source_url` adds the "Full recommendation on LinkedIn ↗" link (`source_label`).

### Profile & Links

- `linkedin_url`, `github_url`, `cv_file` are each hidden everywhere while empty (nav, hero line, contact block, footer, schema).
- Update `edc_person_same_as()` in `inc/seo-names.php` to read `linkedin_url` and `github_url` first, then fall back to the footer social repeater.
- Person schema: `name` = `profile_name`, `jobTitle` = `profile_title`, `image` = `portrait` (full URL) when set.
- `career_log` is newest first. The homepage shows the first `home_log_visible` entries (default 5) and collapses the rest behind `$ git log --all`. About shows all.

### Homepage (B−)

- Hero, Work and Contact obey the existing `show_hero`, `show_work`, `show_cta` toggles. Work also hides when no featured project is published (`edc_section_is_public()` already does this for `show_work`; switch its check from "any published project" to "any published project in `home_featured_projects`").
- The new sections obey `show_home_bridge`, `show_home_called`, `show_home_about`, `show_home_record`. Add them to `edc_section_anchor_map()` (`services` → `show_home_called`, `about` → `show_home_about`, `track-record` → `show_home_record`) and keep the admin-preview badge behaviour.
- Fields with defaults (headline, bridge, section headings) hold Elisa's approved copy from the prototype. Fields with no default (story, stats, terminal lines, leadership text) render nothing until filled.

## 5. Legacy fields (Global Theme Options)

The old homepage sections (proof strip, tabs, believe, toolbox, services, map, help, old testimonials, old CTA) are retired from the front page but their data stays. Map of what replaces what:

| Old field(s) | Replaced by |
|---|---|
| `hero_eyebrow`, `hero_heading`, `hero_paragraph`, `hero_btn_*` | `home_title_line`, `home_h1`, `home_lede`, `home_cta_*` |
| `terminal_title`, `terminal_lines` | `home_terminal_title`, `home_terminal` (supports links) |
| `proof_*` | Removed. The hero quote returns only once attributed, as a testimonial |
| `story_paragraphs`, `numbers_stats`, `specialise_list`, `hire_photo` | `home_about_story`, `home_stats`, `home_called`, `portrait` |
| `believe_text` | `home_bridge` |
| `services*` | `home_called` (homepage) and the Services page |
| `map_*`, `trust_line` | Removed (countries become part of the career log) |
| `work_eyebrow`, `work_heading` | `home_work_heading`, `home_work_intro` |
| `help_*` | Removed (folded into Called in for) |
| `testimonials` repeater | `testimonial` CPT + `home_testimonials` |
| `cta_*`, `resume_url`, `contact_email`, `cta_links` | `home_contact_*`, `cv_file`, `contact_email` (kept, still used), `linkedin_url`, `github_url` |
| `loader_icon`, `marquee_text` | Removed (loader and marquee are cut) |
| Still used as is | `contact_email`, `cf7_shortcode`, `modal_*`, `footer_*`, `logo_icon`, `nav_cta_label`, `show_hero`, `show_work`, `show_cta`, `show_contact_modal` |

Don't delete the legacy group. A later cleanup can move it to a "Legacy" tab once Elisa has confirmed nothing in it is needed.

## 6. Syncing in wp-admin

1. Deploy the theme branch to a **local** copy of the site (see `06-tasks-and-qa.md`).
2. **ACF → Field Groups** shows "Sync available" for the new and changed groups. Select all → **Sync changes**.
3. Check **Site Content** shows the three groups, **Projects** shows the new tabs, **Testimonials** appears in the menu, and the About / Services / Contact pages show their groups once their template is set.
4. Commit any JSON that wp-admin rewrote, so the repo matches.

## 7. Field reference (generated)

### Profile & Links — `group_edc_profile`

Location: Options page → Site Content. Who Elisa is, links, CV and the career log. Read by the homepage, About, footer and schema.

| Field name | Type | Required | Default | Notes |
|---|---|---|---|---|
| **— Identity tab —** | | | | |
| `profile_name` | text |  | `Elisa A. Dela Cruz` | Used in schema and the footer. |
| `profile_short_name` | text |  | `Elisa Dela Cruz` | Used in copy. |
| `profile_title` | text |  | `Senior WordPress & Shopify Developer` |  |
| `portrait` | image (ID) |  |  | Used on About, the homepage hex and Person schema. |
| `availability` | text |  |  | Terminal status line, e.g. Open to projects and senior roles. Leave empty if not confirmed: empty fields render nothing, never a placeholder. |
| `location_line` | text |  |  | e.g. Manila, working with AU / US / UK time zones |
| `reply_promise` | text |  |  | e.g. Replies within one working day |
| **— Links tab —** | | | | |
| `cv_file` | file (pdf, array) |  |  | Every "Download CV" link is hidden while this is empty. |
| `linkedin_url` | url |  |  | Hidden everywhere while empty. Also used for schema sameAs. |
| `github_url` | url |  |  | Hidden everywhere while empty. Also used for schema sameAs. |
| **— Career log tab —** | | | | |
| `career_log` | repeater |  |  | Feeds the git-log timeline on the homepage (5 newest + "git log --all") and the full list on About. |
| ↳ `type` | select: `feat` / `fix` / `chore` / `refactor` / `init` |  | `feat` |  |
| ↳ `message` | text |  |  | e.g. Shopify at scale, four storefronts |
| ↳ `context` | text |  |  | Shown dimmed in brackets, e.g. US beauty brand, via Microsourcing |
| ↳ `when` | text |  |  | e.g. 2020–22 or 2022 → |
| ↳ `meta` | text |  |  | e.g. Shopify Developer · US account |
| ↳ `hash` | text |  |  | Optional 7 characters; generated if empty. |
| ↳ `is_head` | true/false (default off) |  |  |  |
| ↳ `body` | textarea |  |  |  |

### Homepage (B−) — `group_edc_home_v2`

Location: Options page → Site Content. Content for the redesigned homepage. Hero, Work and Contact also obey the existing show_hero, show_work and show_cta toggles.

| Field name | Type | Required | Default | Notes |
|---|---|---|---|---|
| **— Hero tab —** | | | | |
| `home_title_line` | text |  | `Senior WordPress & Shopify Developer` | Small mono line above the H1. |
| `home_h1` | textarea |  | `Websites are the <em class="voice grad-text">easy</em> part.` | Allowed HTML: <em class="voice grad-text">. |
| `home_lede` | textarea |  | `I'm Elisa Dela Cruz. I build them, and <strong>I solve the p…` | Allowed HTML: <strong>. |
| `home_cta_primary` | text |  | `See the work` |  |
| `home_cta_secondary` | text |  | `Start a project` |  |
| `home_cv_prompt` | text |  | `Hiring?` | Shown before the CV link. Hidden while no CV is uploaded. |
| `home_terminal_title` | text |  | `~/elisa` |  |
| `home_terminal` | repeater |  |  | Real text only. Leave a line out rather than guess. |
| ↳ `kind` | select: `p` / `o` / `ok` |  | `o` |  |
| ↳ `text` | text |  |  |  |
| ↳ `link` | page link (URL) |  |  | Optional. Makes the line a link (e.g. a case study). |
| `home_bridge` | text |  | `Good websites are beautiful.<br>Great websites solve problem…` | Allowed HTML: <br>, <em class="grad-text">. |
| **— Work tab —** | | | | |
| `home_work_heading` | text |  | `The work <em class="voice">behind</em> the websites.` | Allowed HTML: <em class="voice">. |
| `home_work_intro` | text |  | `Some of these you can see. Most of the interesting parts you…` |  |
| `home_featured_projects` | relationship → project (IDs, max 8) |  |  | First one becomes the wide lead card if its width is set to Wide. |
| `home_more_heading` | text |  | `Also built from scratch, ground to deployment` |  |
| `home_more_projects` | relationship → project (IDs, max 8) |  |  | Listed by public name with platform · region. Leave empty to hide the tile. |
| **— Called in for tab —** | | | | |
| `home_called_heading` | text |  | `What I'm usually called in for.` |  |
| `home_called` | repeater, max 4 |  |  | Four cards. |
| ↳ `label` | text |  |  | e.g. 01 / move |
| ↳ `title` | text |  |  |  |
| ↳ `text` | textarea |  |  |  |
| ↳ `stack` | text |  |  | Mono line, e.g. 301 maps · data import · DNS |
| ↳ `link_label` | text |  |  | e.g. See a migration |
| ↳ `link` | page link (URL) |  |  | A project or the filtered archive. Hidden while empty. |
| **— Behind the code tab —** | | | | |
| `home_about_heading` | text |  | `Hi, I'm Elisa. I like <em class="voice">problems</em>.` | Allowed HTML: <em class="voice">. |
| `home_about_story` | textarea |  |  | One paragraph per line. Leave empty if not confirmed: empty fields render nothing, never a placeholder. |
| `home_principles` | repeater, max 4 |  |  |  |
| ↳ `title` | text |  |  |  |
| ↳ `text` | textarea |  |  |  |
| `home_pull_testimonial` | post object → testimonial (ID) |  |  | Uses the testimonial's short version if set. |
| `home_lead_title` | text |  | `I've also led the team.` |  |
| `home_lead_text` | textarea |  |  | Leave empty if not confirmed: empty fields render nothing, never a placeholder. |
| **— Track record tab —** | | | | |
| `home_record_heading` | text |  |  | Must match the verified stats below. |
| `home_stats` | repeater, max 3 |  |  | Up to three. Unverified rows never render. |
| ↳ `value` | text |  |  | e.g. 15 |
| ↳ `label` | text |  |  | e.g. Years |
| ↳ `verified` | true/false (default off) |  |  | Only verified stats render. |
| `home_log_visible` | number |  | `5` |  |
| `home_testimonials` | post object → testimonial (IDs) |  |  | Up to three. Ones without permission confirmed are skipped. |
| **— Contact tab —** | | | | |
| `home_contact_heading` | text |  | `Got something that's <em class="voice grad-text">stuck</em>?` | Allowed HTML: <em class="voice grad-text">. |
| `home_contact_intro` | textarea |  | `A stalled migration, a site that's acting strange, or a role…` |  |
| `home_contact_button` | text |  | `Start a conversation` |  |
| **— Sections on/off tab —** | | | | |
| `show_home_bridge` | true/false (default on) |  |  |  |
| `show_home_called` | true/false (default on) |  |  |  |
| `show_home_about` | true/false (default on) |  |  |  |
| `show_home_record` | true/false (default on) |  |  |  |

### Project Details — `group_edc_project_fields`

Location: Post type = project. Project card, behind panel and case study. See docs/redesign/03-acf-schema.md.

| Field name | Type | Required | Default | Notes |
|---|---|---|---|---|
| **— Card tab —** | | | | |
| `project_kind` | taxonomy → `project_type` (checkbox, saves terms) | yes |  | Tick Built, Solved or both. Drives the Built / Solved filter and the coloured tags. |
| `project_platforms` | taxonomy → `project_platform` (checkbox, saves terms) |  |  | Drives the platform filter and the neutral tags. |
| `tier` | select: `featured` / `selected` / `highlight` | yes | `selected` | Where this project appears on /projects/. |
| `client_display_name` | text | yes |  | Exactly what visitors see. Use an anonymous label when the client must not be named, e.g. "US beauty brand". |
| `confidential` | true/false (default off) |  |  | On: never output the client's real name, logo, live URL or screenshots. Use the illustrated or terminal visual. |
| `industry_badge` | text |  |  | e.g. Wine, Beauty, Smart home & AV |
| `region` | text |  |  | e.g. Australia, US, Philippines |
| `role` | text |  |  | e.g. Shopify developer, Design & development |
| `engaged_via` | text |  |  | Employer of record or agency, e.g. "via Microsourcing". Leave empty for direct clients. |
| `year_start` | number |  |  | Four digits. Leave empty if not confirmed: empty fields render nothing, never a placeholder. |
| `year_end` | text |  |  | Four digits, or "now" for ongoing. Shown as 2020–22 or 2022 →. |
| `summary` | textarea | yes |  | One or two sentences, plain words. Falls back to the excerpt. |
| `names` | repeater |  |  | Only for grouped cards such as "Three wineries". Each shows as → Name · Note. |
| ↳ `name` | text |  |  | e.g. Boat O'Craigo |
| ↳ `note` | text |  |  | e.g. Yarra Valley |
| `chips` | repeater, max 3 |  |  | Shown after the kind and platform tags. Keep it to three. |
| ↳ `label` | text |  |  | e.g. Liquid, 1,000+ SKUs, Migration |
| `card_span` | select: `auto` / `wide` / `half` |  | `auto` | Use Wide for the first featured project only. |
| **— Visual tab —** | | | | |
| `visual_mode` | select: `screenshot` / `split` / `illustration` / `terminal` |  | `screenshot` | Confidential projects must use Illustration or Terminal. |
| `screenshot` | image (ID) |  |  | 1600×940 or larger, top of the homepage. Uploaded as WebP or AVIF if possible. Shown when `p_visual` == screenshot or `p_visual` == split. |
| `screenshot_secondary` | image (ID) |  |  |  Shown when `p_visual` == split. |
| `illustration` | select: `swatches` |  | `swatches` | Add new illustrations in template-parts/project/visual-*.php. Shown when `p_visual` == illustration. |
| `terminal_line` | text |  |  | e.g. $ grep -r "eval(base64_decode" ./wp-content Shown when `p_visual` == terminal. |
| `browser_label` | text |  |  | The mono text in the fake browser bar, e.g. boatocraigo.com.au. For confidential projects use a description, e.g. us-beauty-brand · 4 storefronts. |
| `visual_badge` | text |  |  | Optional, e.g. + 2 more wineries |
| `live_url` | url |  |  | Only if the site is still your work and the client is not confidential. |
| `gradient_start` | color_picker |  | `#7c3aed` | Used only when no visual is set. |
| `gradient_end` | color_picker |  | `#ec4899` |  |
| `platform_badge` | text |  |  | Old field. Platforms now come from the Platforms taxonomy; this is only read if no platform is ticked. |
| **— Behind panel tab —** | | | | |
| `behind_meta` | text |  |  | Right side of the panel header, e.g. built · 2020–22, solved · confidential |
| `behind_rows` | repeater, max 6 |  |  | Shown as key: value in the mono panel. Usual keys: needed, tricky, did, stack, with, from, to, moved, symptom, cause, fix, after, outcome. Leave empty if not confirmed: empty fields render nothing, never a placeholder. |
| ↳ `key` | text |  |  | Lowercase, no colon |
| ↳ `value` | text |  |  | One line |
| **— Case study tab —** | | | | |
| `has_case_study` | true/false (default off) |  |  | Off: the card does not link anywhere and /projects/{slug}/ redirects to the archive. |
| `cs_headline` | text |  |  | Plain words: what happened. Falls back to the title. Shown when `p_has_cs` == 1. |
| `cs_lede` | textarea |  |  | Two or three sentences under the headline. Shown when `p_has_cs` == 1. |
| `at_a_glance` | repeater, max 8 |  |  | The mono sidebar box. Leave empty if not confirmed: empty fields render nothing, never a placeholder. Shown when `p_has_cs` == 1. |
| ↳ `label` | text |  |  | client, role, stack, timeline, team |
| ↳ `value` | text |  |  |  |
| `situation` | wysiwyg |  |  | What was wrong or needed, in the client's terms. Shown when `p_has_cs` == 1. |
| `tricky` | wysiwyg |  |  | The real constraint. Shown when `p_has_cs` == 1. |
| `approach` | repeater, max 8 |  |  | Three to five steps. Shown when `p_has_cs` == 1. |
| ↳ `step` | textarea |  |  |  |
| `technical_breakdown` | wysiwyg |  |  | Shown inside a collapsed <details>. Code allowed (sanitised, nothing proprietary, no credentials). Shown when `p_has_cs` == 1. |
| `result` | wysiwyg |  |  | Only verified outcomes. If nothing was measured, describe what was delivered. Shown when `p_has_cs` == 1. |
| `learned` | textarea |  |  |  Shown when `p_has_cs` == 1. |
| `cs_testimonial` | post object → testimonial (ID) |  |  | Only testimonials with permission confirmed are shown. Shown when `p_has_cs` == 1. |

### Testimonial — `group_edc_testimonial_fields`

Location: Post type = testimonial. Verified testimonials only. The post title is for admin use.

| Field name | Type | Required | Default | Notes |
|---|---|---|---|---|
| `quote` | textarea | yes |  | Exact words, as approved. Use … for cuts. |
| `quote_short` | textarea |  |  | Optional shorter cut for pull quotes. |
| `person_name` | text | yes |  | First name + initial is fine, e.g. Rachel S. |
| `person_role` | text |  |  | e.g. Principal Consultant |
| `company` | text |  |  |  |
| `relationship` | select: `client` / `employer` |  | `client` | Shown as the $ role: badge. |
| `source_url` | url |  |  | e.g. the LinkedIn recommendation URL |
| `source_label` | text |  | `Full recommendation on LinkedIn ↗` |  |
| `permission_confirmed` | true/false (default off) |  |  | Required. Off = never shown anywhere on the site, even if selected. |

### About page — `group_edc_page_about`

Location: Page template = page-templates/about.php. The career timeline comes from Profile & Links → Career log.

| Field name | Type | Required | Default | Notes |
|---|---|---|---|---|
| `about_h1` | text |  | `Hi, I'm Elisa.` |  |
| `about_lede` | textarea |  |  |  |
| `about_story` | wysiwyg |  |  | Three short paragraphs: the start, the agency years, why problems became the thing, today. Leave empty if not confirmed: empty fields render nothing, never a placeholder. |
| `about_leadership` | wysiwyg |  |  | Leave empty if not confirmed: empty fields render nothing, never a placeholder. |
| `about_principles` | repeater, max 6 |  |  | Fuller version of the homepage principles. |
| ↳ `title` | text |  |  |  |
| ↳ `text` | textarea |  |  |  |
| `about_lessons` | repeater, max 6 |  |  | One-liners in Elisa's own words. Section hidden while empty. |
| ↳ `text` | text |  |  |  |
| `about_more` | textarea |  |  | Personal paragraph. Hidden while empty. |
| `about_names` | textarea |  | `You may have worked with me as Elisa Dela Cruz, Elisa Armas …` |  |

### Services page — `group_edc_page_services`

Location: Page template = page-templates/services.php. Ways to work with me.

| Field name | Type | Required | Default | Notes |
|---|---|---|---|---|
| `services_h1` | text |  | `Ways to work with me` |  |
| `services_lede` | textarea |  |  |  |
| `services_engagements` | repeater, max 4 |  |  |  |
| ↳ `key` | select: `build` / `move` / `rescue` / `care` |  |  |  |
| ↳ `title` | text |  |  |  |
| ↳ `who` | textarea |  |  |  |
| ↳ `included` | textarea |  |  | One item per line. |
| ↳ `process` | textarea |  |  |  |
| ↳ `related` | relationship → project (IDs, max 4) |  |  | Shown as links under the engagement. |
| `services_process` | repeater, max 5 |  |  |  |
| ↳ `title` | text |  |  | e.g. Diagnose |
| ↳ `text` | textarea |  |  |  |
| `services_agencies_heading` | text |  | `White-label and overflow development for agencies.` |  |
| `services_agencies_text` | textarea |  |  | Hidden while empty. |
| `services_faq` | repeater |  |  | Answers must be confirmed by Elisa (pricing, hours). |
| ↳ `q` | text |  |  |  |
| ↳ `a` | textarea |  |  |  |

### Contact page — `group_edc_page_contact`

Location: Page template = page-templates/contact.php. The form is the same Contact Form 7 form as the modal (Site Content → Contact modal → CF7 shortcode).

| Field name | Type | Required | Default | Notes |
|---|---|---|---|---|
| `contact_h1` | text |  | `Let's talk` |  |
| `contact_lede` | textarea |  |  |  |
| `contact_next_steps` | repeater, max 4 |  |  | Three short steps. |
| ↳ `text` | text |  |  |  |
