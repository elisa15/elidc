# 06 — Tasks and QA

Work through these in order. Each task is one commit (or a few), on a branch off `fix/content-integrity` named `feature/b-minus-redesign`. Open one pull request to `main` at the end, or one per phase if Elisa prefers. **Never deploy.**

## Environment

- A **local** copy of the site: LocalWP (or wp-env) with WordPress 6.x, PHP 7.4+ (test on 8.2 too), **ACF PRO**, **Contact Form 7**, **Yoast SEO**. Elisa provides the ACF PRO licence or a database export; never use production credentials.
- Symlink or copy `themes/elisa-dela-cruz` and `plugins/elisaarmas-visitor-log` into the local `wp-content`.
- `WP_DEBUG` and `WP_DEBUG_LOG` on. The build is done when the log stays empty.
- Tools used by the checks below: `php -l`, `node --check`, Lighthouse (Chrome DevTools), axe DevTools or `@axe-core/cli`, a keyboard.

## Phase A — Foundation

**A1. Tokens and stylesheet**
- Enqueue `assets/css/tokens.css` then `assets/css/site.css` (from `reference/components.css`). Fonts enqueued with preload (04 §1).
- ✅ Done when: the homepage still renders with the old sections (legacy aliases keep them styled), `tokens.css` is unchanged after running `build-tokens.py`.

**A2. Content model**
- `inc/content-model.php`: project CPT update, `project_type` + `project_platform` taxonomies with seeded terms, `testimonial` CPT, page templates registered (03 §2).
- Sync ACF field groups locally.
- ✅ Done when: ACF shows no pending sync; Projects has Card / Visual / Behind panel / Case study tabs; Testimonials menu exists; Site Content shows Profile & Links and Homepage (B−); About / Services / Contact pages show their groups after their template is set.

**A3. Helpers**
- `inc/template-helpers.php` (03 §3) and `inc/projects-query.php` (04 §2).
- ✅ Done when: unit-style checks with WP-CLI pass, e.g. `wp eval 'var_dump( edc_years( 2020, 2022 ) );'` → `2020–22`; `edc_years( 2022, "now" )` → `2022 →`; `edc_testimonial()` returns `null` for a testimonial with permission off; a project with `has_case_study` off redirects (302) to `/projects/#project-{slug}`.

**A4. Seed script (local only)**
- `docs/redesign/tools/seed-content.php`, run with `wp eval-file`. Creates the profile options, career log, testimonials, projects (with media from `reference/prototype/assets/shots/`), homepage options and page templates from `05-content-entry.md`. Idempotent (re-running updates, never duplicates). Refuses to run when `home_url()` contains `elisaarmas.com`.
- ✅ Done when: running it twice gives the same result; every ⏳ field is empty.

## Phase B — Global chrome

**B1. Header and nav** (04 §3 `header.php`). ✅ Skip link is the first Tab stop; menu works with keyboard and Escape; current page marked; no `#` links.

**B2. Footer** (04 §3 `footer.php`). ✅ LinkedIn / GitHub / CV links absent while their fields are empty, present once filled.

**B3. JS** — `assets/js/site.js` (04 §6); remove GSAP and `main.js`. ✅ No console errors; modal focus trap, focus return, CF7 challenge and GA events still work (check `dataLayer` in the console).

## Phase C — Homepage

**C1. Front page loop + legacy move** (04 §3 `front-page.php`).
**C2. Hero** → **C3. Bridge** → **C4. Work** (with `project/card.php`, `visual*.php`, `behind.php`) → **C5. Called in for** → **C6. Behind the code** → **C7. Track record** (`common/commit-log.php`, `testimonial.php`, `stats.php`) → **C8. Contact**.

✅ Done when, side by side with `reference/prototype/b-creative.html` at 1440px, 980px, 760px and 375px:
- Layout, type, colours and spacing match the prototype (review notes excluded).
- Section order and night/cream rhythm match 02 §Colour.
- With the seed content, the page shows no ⏳ content and no empty wrappers (no empty `<p>`, `<dl>`, `<ul>`, headings without content).
- Each section toggle in Homepage (B−) → Sections on/off hides its section for visitors and shows the admin badge for admins.

## Phase D — Projects

**D1. Archive** `/projects/` (04 §3). ✅ `?type=built`, `?type=solved`, `?platform=shopify` and combinations filter without JS; invalid values are ignored; filtered URLs are `noindex`; tiers render in order; empty tiers have no heading.

**D2. Case study** `single-project.php`. ✅ Matches `reference/prototype/case-study.html`; the US beauty brand page shows no real client name anywhere in the HTML (view source and search for the brand name and its domains); the technical breakdown section is absent while empty; next-case-study link works and wraps.

## Phase E — Inner pages

**E1. About**, **E2. Services**, **E3. Contact** (04 §3 page templates). ✅ Each has one H1, hides every empty block, and the CF7 form on Contact submits (CF7 mail log or Flamingo locally).

## Phase F — SEO, performance, cleanup

**F1. Schema** (04 §5). ✅ Google Rich Results Test (paste HTML) shows Person with name, jobTitle, image, sameAs (once set); no confidential names in any JSON-LD.

**F2. Performance** (04 §7). ✅ Lighthouse mobile on home, `/projects/` and the case study: Performance ≥ 90, Accessibility 100, Best Practices ≥ 95, SEO 100; LCP < 1.8s, CLS < 0.05.

**F3. Cleanup**: remove dead CSS from `style.css`, remove legacy token aliases if unused, bump `EDC_VERSION` and the `style.css` version, update `README.md` and `SCHEMA.md` to describe the new model (point to `docs/redesign/03-acf-schema.md`).

## QA checklist (run before the pull request)

### Content integrity
- [ ] `grep -rniE "lorem|verified client feedback" themes/elisa-dela-cruz --include=*.php --include=*.js | grep -v /legacy/` finds nothing.
- [ ] With **every** ACF field empty (fresh install, ACF active), the homepage shows only the approved defaults; no fake projects, quotes, stats or links.
- [ ] With ACF **deactivated**, no PHP fatal errors.
- [ ] A testimonial with permission off never renders (homepage, pull quote, case study).
- [ ] An unverified stat never renders.
- [ ] A confidential project never outputs its real name, domain, logo or screenshot (check HTML, alt text, schema, `data-` attributes, image filenames).
- [ ] No `href="#"` anywhere: `curl -s http://site.local/ | grep -c 'href="#"'` → 0.

### Accessibility (WCAG 2.2 AA)
- [ ] axe: 0 violations on home, `/projects/`, a case study, About, Services, Contact.
- [ ] Keyboard only: every interactive element reachable, visible focus ring, logical order; behind panel opens on focus; filters, timeline, modal, menu all operable; Escape closes menu and modal.
- [ ] Screen reader (VoiceOver): filter changes are announced; copy email is announced; card names and links make sense out of context.
- [ ] `prefers-reduced-motion: reduce` (DevTools → Rendering): no animation, no transitions, terminal shows all lines.
- [ ] 200% zoom and 320px width: no horizontal scroll, nothing clipped.
- [ ] Tap targets ≥ 44px on mobile for standalone links and buttons.

### Behaviour
- [ ] JS disabled: all content visible, filters work as links, timeline shows every entry, "Start a project" goes to `/contact/`.
- [ ] Touch device (or DevTools touch emulation): behind panels use the toggle button; swipe row scrolls with snap.
- [ ] Safari, Chrome, Firefox, iOS Safari, Android Chrome.

### Code
- [ ] `find themes/elisa-dela-cruz -name '*.php' -exec php -l {} \;` clean on PHP 7.4 and 8.2.
- [ ] `node --check themes/elisa-dela-cruz/assets/js/site.js` clean.
- [ ] `debug.log` empty after clicking through every page.
- [ ] `python3 docs/redesign/tools/build-tokens.py` leaves `git status` clean (tokens.css not hand-edited).
- [ ] `python3 docs/redesign/tools/acf-tables.py` leaves `git status` clean (docs match the JSON).

## Definition of done

All QA boxes ticked, screenshots of home / projects / case study at 1440 and 375 attached to the pull request, the list of every ⏳ item still empty in the PR description, and **no deployment**. Elisa reviews, approves, and deploys herself.
