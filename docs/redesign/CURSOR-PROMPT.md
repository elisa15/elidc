# Cursor kickoff prompt

Paste everything below the line into Cursor (Agent mode) with the `elidc` repo open. Use it once, to start. For later phases, use the short follow-up prompt at the bottom.

---

You are implementing the approved **B− redesign** of Elisa A. Dela Cruz's portfolio theme (`themes/elisa-dela-cruz`, WordPress + ACF PRO). The design, content model, ACF fields and build spec are already written. Your job is to build it exactly as specified, phase by phase, and stop for review after each phase.

## 1. Read before you write any code

Read these in full, in this order:

1. `.cursor/rules/elisa-redesign.mdc` (always applies; it overrides anything else)
2. `docs/redesign/README.md`
3. `docs/redesign/01-brief.md`
4. `docs/redesign/02-design-system.md`
5. `docs/redesign/03-acf-schema.md`
6. `docs/redesign/04-build-spec.md`
7. `docs/redesign/05-content-entry.md`
8. `docs/redesign/06-tasks-and-qa.md`

Then open the visual reference in a browser: `docs/redesign/reference/prototype/b-creative.html` (homepage) and `case-study.html`. Yellow underlines and dashed yellow boxes are review notes, not design.

Sources of truth:

- Tokens: `design/tokens.json` → `python3 docs/redesign/tools/build-tokens.py` → `themes/elisa-dela-cruz/assets/css/tokens.css` (never hand-edit the CSS).
- Component CSS: `docs/redesign/reference/components.css`.
- ACF fields: `themes/elisa-dela-cruz/acf-json/*.json` (already written; sync them, don't recreate them in PHP).

When you've read everything, reply with a short summary (10 lines max) of what you'll build in Phase A, plus any contradictions or gaps you found in the docs. **Wait for my "go" before writing code.**

## 2. Non-negotiable rules

- **Never invent content.** No made-up achievements, stats, testimonials, client names, dates, outcomes or links. This applies to templates, JS, fallbacks, seed data and alt text. Empty field → the element doesn't render. No Lorem ipsum, no `href="#"`.
- Items marked ⏳ in `05-content-entry.md` stay empty. List them; don't fill them.
- **Confidential stays confidential.** The lead project is only ever "US beauty brand". Never its real name, domain, logo or screenshot, anywhere (HTML, alt, schema, `data-` attributes, filenames).
- The public name is "Elisa A. Dela Cruz" / "Elisa Dela Cruz". Never "Elisa Armas".
- Testimonials render only through `edc_testimonial()` (checks `permission_confirmed`). Stats render only when `verified`.
- **Never deploy.** Don't connect to, query or run anything against elisaarmas.com or its database. Don't push to `main`. Don't push at all unless I ask.
- Don't delete ACF data or legacy field groups. Retired templates move to `template-parts/legacy/`.
- Don't rename ACF field names or keys. If a field must change, edit the JSON in `acf-json/`, then run `python3 docs/redesign/tools/acf-tables.py`.

## 3. Code standards

- PHP 7.4 compatible (also test on 8.2). No build step. Vanilla JS: no jQuery, no GSAP.
- Read ACF only through `edc_opt()`, `edc_field()`, `edc_repeater()` and `inc/template-helpers.php`. Everything must work with ACF deactivated (no fatals).
- Escape all output (`esc_html`, `esc_attr`, `esc_url`, `edc_inline_html()`).
- Pass data to template parts with `get_template_part( $slug, null, $args )`.
- CSS uses only token custom properties; section CSS uses semantic tokens (`--surface`, `--fg`, `--fg-muted`, `--line`, `--accent-ink`, `--focus`). Cream sections get `class="light"`. White-text buttons use `--grad-btn`, never `--grad`. Never put `.grad-text` on cream.
- Motion: CSS only, `transform`/`opacity` only, content visible without JS, full `prefers-reduced-motion` support, no loader, nothing auto-rotates.
- Accessibility: WCAG 2.2 AA, visible `:focus-visible`, nothing hover-only, 44px tap targets, one H1 per page.
- Match the surrounding code style. Keep comments sparse and useful.

## 4. Setup

1. Check out `fix/content-integrity` and confirm the latest commit is "92085b0" (or later). Create `feature/b-minus-redesign` from it.
2. Local WordPress only (LocalWP or wp-env): WordPress 6.x, ACF PRO, Contact Form 7, Yoast SEO. Symlink `themes/elisa-dela-cruz` and `plugins/elisaarmas-visitor-log` into `wp-content`. Turn on `WP_DEBUG` and `WP_DEBUG_LOG`.
3. If you need anything you don't have (the ACF PRO licence, a database export, a local URL), stop and ask me. Never use production credentials.

## 5. How we work

- Follow `06-tasks-and-qa.md` in order: Phase A (Foundation) → B (Global chrome) → C (Homepage) → D (Projects) → E (Inner pages) → F (SEO, performance, cleanup).
- **Start with Phase A only** (A1 tokens and stylesheet, A2 content model, A3 helpers, A4 local seed script).
- One commit per task (A1, A2…), with a clear message that names the task.
- After each task, check its ✅ criteria in `06-tasks-and-qa.md` and say which pass, which fail, and how you checked (command and output).
- Run `php -l` on every PHP file you touched and `node --check` on JS before each commit.
- After each phase, **stop** and send me a report:
  - What you built (files added/changed)
  - ✅ criteria: pass/fail with evidence
  - Anything you couldn't verify and why
  - Questions or doc gaps
  - Every ⏳ item still empty
- If the docs are ambiguous or contradict each other, ask. Don't guess, and never fill a gap with invented content.

Start now with step 1: read the docs and send me the Phase A summary.

---

## Follow-up prompt (for each next phase)

> Phase [X] approved. Continue with Phase [Y] from `docs/redesign/06-tasks-and-qa.md`. Same rules as before (`.cursor/rules/elisa-redesign.mdc`). One commit per task, check each ✅ criterion with evidence, then stop and send the phase report. Don't push.

## Final prompt (after Phase F)

> Run the full QA checklist in `docs/redesign/06-tasks-and-qa.md` and tick every box with evidence. Take screenshots of home, `/projects/` and a case study at 1440px and 375px. Draft the pull request description (what changed, QA results, screenshots, every ⏳ item still empty). Don't open the PR or push until I say so. Never deploy.
