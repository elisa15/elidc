# Elisa Dela Cruz — WordPress theme

Custom ACF-driven theme built from the approved v6 portfolio design.
Text domain / slug: `elisa-dela-cruz`.

## What's already done (by this theme)

- Registers the `project` custom post type (`functions.php`) — public,
  archive at `/projects/`, supports title/editor/thumbnail/excerpt.
- Registers an ACF Options Page ("Site Content", menu slug
  `edc-site-content`) — guarded with `function_exists('acf_add_options_page')`
  so it silently does nothing if only ACF Free is installed.
- Registers two nav menu locations: **Primary Navigation** and **Footer
  Navigation** (both optional — the theme falls back to the original static
  menu markup if nothing is assigned).
- All templates render the *exact* original mockup design/copy as fallback
  content, so the site looks correct even with zero ACF data.

## Setup checklist

1. **Install & activate this theme.**
   Appearance → Themes → Add New → Upload `elisa-dela-cruz.zip` (or drop the
   unzipped `elisa-dela-cruz/` folder into `wp-content/themes/`) → Activate.

2. **Install & activate Advanced Custom Fields** (Free or PRO). PRO is only
   required for the Options Page fields (site-wide copy) — the `project`
   Custom Post Type itself works with the free version.

3. **Build the two ACF field groups** described in `SCHEMA.md`:
   - One field group located on **Post Type = Project**.
   - One field group located on **Options Page = Site Content**.

   Once built, exporting these as **ACF Local JSON** into the theme's
   `acf-json/` folder (Custom Fields → Tools → Export → "Export as JSON",
   or ACF's automatic local-JSON sync if the folder is writable) keeps the
   field definitions version-controlled with the theme.

4. **Install & activate Contact Form 7.** Create your form (Contact →
   Contact Forms → Add New), copy its shortcode (e.g.
   `[contact-form-7 id="123" title="Contact form 1"]`), and paste it into
   **Site Content → Contact modal → CF7 Shortcode**. Until this is set (or
   if CF7 isn't active), the contact modal shows the original static
   placeholder form instead of erroring.

5. **Add Projects.** Posts → Projects → Add New for each case study:
   - Set a Featured Image (used as the case-study visual).
   - Fill in the `industry_badge` / `platform_badge` / `role` / `summary`
     ACF fields (see SCHEMA.md §1).
   - Write the full case-study body in the main content editor.
   - Publish in the order you want them to appear in the homepage carousel.

6. **Set the static front page.** Settings → Reading → "A static page" is
   **not** required — this theme uses `front-page.php`, which WordPress
   automatically uses for the site's root URL regardless of the Reading
   setting, as long as no other front-page.php-overriding plugin is active.

7. **Assign nav menus (optional).** Appearance → Menus → create a menu with
   Why me (#hire) / Services (#services) / Work (#work) / Contact (#contact)
   links, assign it to "Primary Navigation". Do the same for "Footer
   Navigation" if you want it different from the default About/Services/
   Work/Contact set.

8. **Fill in Site Content.** Work through the ACF Options Page tab by tab —
   every field has a sensible default already live, so this can be done
   gradually without ever breaking the page.

## File map

```
elisa-dela-cruz/
  style.css                  Theme header + all ported CSS
  functions.php              Setup, CPT, Options Page, enqueue, helpers
  header.php / footer.php    Site chrome, nav, loader, contact modal include
  front-page.php             Assembles the one-page home
  single-project.php         Individual case-study page
  archive-project.php        /projects/ grid fallback
  index.php / page.php       Generic WP fallback templates
  template-parts/            One file per homepage section
  assets/js/main.js          All interactive JS (reveal, GSAP, carousels, modal)
  assets/images/             Bundled logo files
  acf-json/                  Drop ACF local-JSON exports here
  SCHEMA.md                  Full ACF field reference for Cursor
```

## Notes / deliberate trade-offs

- The world map's 5 node **positions** and connecting travel-routes are
  fixed pixel coordinates baked into the SVG (hand-tuned to the dot-matrix
  illustration) — only each node's tooltip *content* is ACF-driven. See the
  comment at the top of `template-parts/section-map.php` and the Global map
  section of `SCHEMA.md`.
- Service card icons are fixed inline SVGs that cycle by index — only
  title/description are ACF-driven, so nobody has to manage SVG markup in
  wp-admin.
- Terminal typing lines and the testimonials carousel are localized into JS
  via `wp_add_inline_script()` (`window.edcData`) rather than re-queried by
  AJAX, keeping the exact same lightweight vanilla-JS behavior as the
  original mockup.
