# ACF Field Schema — Elisa Dela Cruz theme

This is the exact list of ACF fields the theme's PHP templates expect. The
theme already registers the **Project** custom post type and the **ACF
Options Page** (menu: *Site Content*) in `functions.php` — Cursor only needs
to build the two ACF field groups below and attach them:

1. A field group located on **Post Type = Project** (the CPT already exists).
2. A field group located on **Options Page = Site Content** (the page already exists, slug `edc-site-content`).

Every field is read through a fallback-safe helper (`edc_opt()`, `edc_field()`,
`edc_repeater()` in `functions.php`), so the site renders correctly with the
mockup's original placeholder copy even before any of this is filled in.
Nothing will break if a field is left empty — but naming must match exactly
(field **name**, not label) for real content to take over.

---

## 1. Field group: "Project Fields" → Location: Post Type = `project`

| Field name (key) | Type | Notes |
|---|---|---|
| `industry_badge` | Text | e.g. "Luxury Fashion" — shown as first pill on the card + single page |
| `platform_badge` | Text | e.g. "Shopify" — shown as second (accent) pill |
| `role` | Text | e.g. "Ongoing design & optimisation partner" |
| `summary` | Textarea | Short 1–2 sentence card summary. Falls back to the post excerpt if empty |
| `gradient_start` | Text or Color Picker | CSS color, used as placeholder background if no Featured Image is set |
| `gradient_end` | Text or Color Picker | Paired with `gradient_start` |

Also uses WordPress-native fields on each Project post (not ACF):
- **Featured Image** — used as the case-study visual (card + single hero) at full size; if omitted, the gradient placeholder above is shown instead.
- **Title** — case-study title (e.g. "Maria Nicola").
- **Content editor (the_content)** — the full body copy shown on the single case-study page (`single-project.php`). Write this as normal paragraphs/blocks.
- **Excerpt** — optional, used as a `summary` fallback in the archive grid only.

The theme is set to show the 5 most recently created Projects (ordered by
menu_order/date) in the homepage carousel — publish in the order you want
them to appear, or set Menu Order under Screen Options.

---

## 2. Field group: "Site Content" → Location: Options Page = `edc-site-content`

Group fields into ACF tabs matching the sections below for admin sanity —
tab names are suggestions only, they don't need to match field names.

### Hero
| Field name | Type | Default shown if empty |
|---|---|---|
| `hero_eyebrow` | Text | "Shopify & WordPress consultant" |
| `hero_heading` | Text/Textarea (allow HTML — used raw) | `I turn ideas into<br><em class="grad-text">unforgettable</em> websites.` |
| `hero_paragraph` | Textarea | 13-years bio paragraph (see mockup) |
| `hero_btn_primary` | Text | "Schedule a call" |
| `hero_btn_secondary` | Text | "View my work" |
| `terminal_title` | Text | "~/elisa" |
| `marquee_text` | Text | scrolling ticker line (services list) |

### Terminal typing animation
| Field name | Type | Notes |
|---|---|---|
| `terminal_lines` | **Repeater** | Sub-fields below. If left empty, the original 4-line demo is used. |
| ↳ `prompt` | Text | e.g. `$ whoami` |
| ↳ `output` | Textarea | **One output line per row** (newline-separated) — each line types out in sequence |
| ↳ `accent` | True/False | Renders the block in the mint "accent" color (used for the "recent_work" block) |

### Proof strip (under hero)
| Field name | Type | Default |
|---|---|---|
| `proof_rating` | Number (1–5) | 5 |
| `proof_quote` | Textarea | "Elisa doesn't just build websites…" |
| `proof_attribution` | Text | "Verified client feedback" |

### "Why clients keep coming back" (tabbed section)
| Field name | Type | Notes |
|---|---|---|
| `hire_eyebrow` | Text | "Behind the code" |
| `hire_heading` | Text (allow HTML) | `Why clients keep<br>coming back.` |
| `hire_photo` | Image (URL) | Optional headshot; falls back to the gradient blob placeholder |
| `story_paragraphs` | Textarea | **One paragraph per line** — "My story" tab |
| `numbers_stats` | **Repeater** (3 rows recommended) | "By the numbers" tab |
| ↳ `number` | Text | e.g. "13+" |
| ↳ `label` | Textarea | e.g. "Years solving real business problems" (line breaks allowed) |
| `numbers_note` | Text | small note under the 3 stats |
| `specialise_list` | **Repeater** | "What I specialise in" tab |
| ↳ `item` | Text | e.g. "Shopify development" |

### "What I believe"
| Field name | Type | Default |
|---|---|---|
| `believe_text` | Textarea (allow HTML) | 3-line editorial statement, `<em>` allowed |

### Toolbox
| Field name | Type | Notes |
|---|---|---|
| `toolbox_eyebrow` | Text | "My toolbox" |
| `toolbox_heading` | Text | "Tools I use every day" |
| `toolbox_chips` | **Repeater** | list of floating chips |
| ↳ `label` | Text | e.g. "Shopify" |
| ↳ `size` | Select: `` (normal) / `lg` / `sm` | controls chip visual size |

### Services
| Field name | Type | Notes |
|---|---|---|
| `services_eyebrow` | Text | "Services" |
| `services_heading` | Text | "Where I make the biggest difference" |
| `services_subheading` | Text | subhead under H2 |
| `services` | **Repeater** (icons are fixed in code, cycle automatically — don't need an icon field) |
| ↳ `title` | Text | e.g. "Store migrations" |
| ↳ `description` | Textarea | one-liner |

### Global map ("Global reach")
**Important:** node **positions** and the connecting travel-routes are
hand-tuned pixel coordinates baked into the SVG in
`template-parts/section-map.php` (they're tied to a custom dot-matrix world
illustration). Keep this repeater at **exactly 5 rows** — if it's ever empty
or the wrong count, the theme auto-pads/truncates against the original 5
defaults (US / UK / South Africa / Australia / Philippines) so the design
never breaks. Only the tooltip *content* per node is editable.

| Field name | Type | Notes |
|---|---|---|
| `map_eyebrow` | Text | "Global reach" |
| `map_heading` | Text | "Trusted by agencies and businesses worldwide" |
| `map_nodes` | **Repeater — exactly 5 rows, in this order: US, UK, South Africa, Australia, Philippines** |
| ↳ `flag` | Text | emoji flag, e.g. 🇺🇸 |
| ↳ `country` | Text | e.g. "United States" |
| ↳ `left` | Text/Number | leave as the existing default unless the map illustration itself is redrawn |
| ↳ `top` | Text/Number | same caveat as `left` |
| ↳ `industries` | Text | comma-separated, e.g. "Fashion, Retail" |
| ↳ `services` | Text | comma-separated, e.g. "Shopify, Custom Dev, Performance" |
| `trust_line` | Textarea | sentence under the map |
| `map_metrics` | **Repeater** (4 rows) | the stat row under the map |
| ↳ `number` | Text | e.g. "13+" |
| ↳ `label` | Text | e.g. "Years Experience" |

### Work / projects section header
(actual project cards come from the `project` CPT, see section 1)
| Field name | Type | Default |
|---|---|---|
| `work_eyebrow` | Text | "Selected work" |
| `work_heading` | Text | "Recent projects" |

### "Sound familiar?" pain-point list
| Field name | Type | Notes |
|---|---|---|
| `help_eyebrow` | Text | "Sound familiar?" |
| `help_heading` | Text | "What clients usually come to me for" |
| `help_list_items` | **Repeater** | |
| ↳ `item` | Text | e.g. "Website running slowly" |

### Testimonials
| Field name | Type | Notes |
|---|---|---|
| `testimonials_eyebrow` | Text | "Testimonials" |
| `testimonials_heading` | Text | "What clients say" |
| `testimonials` | **Repeater** | rotates automatically every 8s on the front-end |
| ↳ `quote` | Textarea | full quote, include the quotation marks if you want them styled exactly like the mockup |
| ↳ `name` | Text | client/contact name |
| ↳ `company` | Text | company name |
| ↳ `rating` | Number (1–5) | stars shown |

### Final CTA ("Let's work together")
| Field name | Type | Default |
|---|---|---|
| `cta_eyebrow` | Text | "Let's work together" |
| `cta_heading` | Text (allow HTML) | `Need someone who can<br><em ...>actually solve it?</em>` |
| `cta_paragraph` | Textarea | supporting sentence |
| `cta_btn_primary` | Text | "Schedule a call" |
| `cta_btn_secondary` | Text | "Download resume" |
| `resume_url` | Text/URL | link target for the resume button (leave empty to disable, "#" shown) |
| `cta_btn_tertiary` | Text | "Email me" |
| `contact_email` | Text/Email | used for the `mailto:` link |
| `cta_links` | **Repeater** | small link row under the buttons |
| ↳ `label` | Text | e.g. "LinkedIn" |
| ↳ `url` | Text/URL | |

### Footer
| Field name | Type | Default |
|---|---|---|
| `footer_logo` | Image (URL) | falls back to theme's bundled `footer-new-logo.png` |
| `footer_tagline` | Textarea | one-line tagline |
| `footer_social` | **Repeater** | |
| ↳ `type` | Select: `email` / `linkedin` / `github` | controls which fixed icon is shown |
| ↳ `url` | Text/URL | |
| `copyright_text` | Text | defaults to `© {current year} Elisa A. Dela Cruz` |
| `footer_domain_label` | Text | defaults to the site's own domain |

### Nav / branding (also read on every page, not just the homepage)
| Field name | Type | Default |
|---|---|---|
| `logo_icon` | Image (URL) | nav logo mark — falls back to theme's bundled icon PNG |
| `logo_text` | Text | "Elisa Dela Cruz" |
| `loader_icon` | Image (URL) | full-page loader icon — falls back to same bundled icon PNG |
| `nav_cta_label` | Text | "Schedule a call" |

### Contact modal
| Field name | Type | Default |
|---|---|---|
| `modal_eyebrow` | Text | "Let's talk" |
| `modal_heading` | Text | "Tell me about your project" |
| `modal_subheading` | Text | "Share a few details and I'll get back to you within a day." |
| `cf7_shortcode` | Text | **paste the full Contact Form 7 shortcode here**, e.g. `[contact-form-7 id="123" title="Contact form 1"]` — see README.md |

---

## Field type cheat-sheet for Cursor

- "Text (allow HTML)" fields are output with `wp_kses_post()`, not
  `esc_html()` — so `<br>` and `<em class="grad-text">…</em>` are safe to
  type directly into the field value in the WP admin.
- All **Repeater** sub-field names above must match exactly (they're read via
  `get_row()` as an associative array in `edc_repeater()`).
- Every field lives at the **top level** of its field group (no nested
  groups/flexible content needed) except the repeaters' own sub-fields.
- Nothing above is required — ACF PRO is only needed for the Options Page
  (`acf_add_options_page()`); if only ACF Free is available, the whole
  Options-Page section of fields simply won't exist and every `edc_opt()`
  call safely returns its hardcoded default instead.
