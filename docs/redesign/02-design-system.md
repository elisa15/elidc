# 02 — Design system

The same system is published as a browsable Claude Design System artifact (link in `README.md`). This file is the version Cursor works from.

## Files

| File | Role | Edit? |
|---|---|---|
| `design/tokens.json` | **Single source of truth** for colour, type, spacing, radius, shadow, z-index | Yes, then regenerate |
| `docs/redesign/tools/build-tokens.py` | Builds `tokens.css` from `tokens.json` | Only to add fluid sizes or composed values |
| `themes/elisa-dela-cruz/assets/css/tokens.css` | Generated CSS custom properties | **Never by hand** |
| `docs/redesign/reference/components.css` | Every component, written against the tokens | Copy to `assets/css/site.css` as the starting point |
| `docs/redesign/reference/prototype/` | The approved B− prototype | Read-only reference |

Regenerate after any token change:

```bash
python3 docs/redesign/tools/build-tokens.py
```

## Colour

### Surfaces work in two contexts

Every section is either **night** (default) or **cream**. A cream section gets `class="light"` (or `data-surface="cream"`), which swaps the semantic tokens. Write component CSS against the semantic tokens, never against `night-*` / `cream-*` directly, unless the component is always one colour (project cards are always cream; the terminal and behind panel are always night).

| Semantic token | Night | Cream | Use |
|---|---|---|---|
| `--surface` | `night-900` #09090d | `cream-100` #f3eee4 | Section ground |
| `--surface-raised` | `night-800` #111116 | `cream-50` #faf6ee | Cards and panels on that ground |
| `--fg` | `paper` #f2f2f5 | `ink-900` #14141a | Headings and body |
| `--fg-muted` | `mist` #a3a3ad | `ink-600` #55525c | Intros, nav, supporting copy |
| `--fg-subtle` | `haze` #83838f | `ink-600` | Mono metadata |
| `--accent-ink` | `magenta` | #b0127a | `~/elisa` in path eyebrows |
| `--focus` | `magenta` | `violet` | Focus ring |
| `--line` / `--line-strong` | white 9% / 16% | ink 12% / 20% | Hairlines / control borders |
| `--chip-bg` | white 6% | ink 6% | Neutral tags, filter track |

Section rhythm on the homepage: Hero (night) → Bridge (night) → **Work (cream)** → Called in for (night) → **Behind the code (cream)** → Track record (night) → Contact (night, violet glow).

### Brand hues

`--magenta` #ff2fb1, `--violet` #8b2fff, `--coral` #ff8a5c, `--mint` #7ee2b8.

- `--grad` (magenta → violet → coral, 120°) is for **large** text (`.grad-text`), the eyebrow dot, the hex portrait frame and the pull-quote rule. Never put `.grad-text` on cream.
- `--grad-btn` (`btn-start` → `btn-mid` → `btn-end`) is the darker gradient behind white button text. It keeps white labels at 5:1 or better. The bright `--grad` behind white 15px text failed contrast (2.3:1 at the coral end), so buttons must use `--grad-btn`.
- Mint is the "system says ok" colour: terminal success, terminal links, commit types, the client badge. Night only.
- Coral is for terminal prompts, behind-panel keys and timeline dates. Night only.

### Contrast (checked)

All text pairs in the tokens meet WCAG AA (4.5:1) on the surfaces their usage notes name. Two changes from the live theme were needed and are deliberate:

| Was | Now | Why |
|---|---|---|
| `--dim-2` #767682 (4.0–4.4:1 on night) | `--haze` #83838f (4.8–5.3:1) | Mono metadata failed AA |
| White on `--grad` buttons (2.3–3.3:1) | White on `--grad-btn` (5.0–6.5:1) | Button labels failed AA |
| Magenta path prefix on cream (2.9:1) | `--accent-ink` #b0127a on cream (5.6:1) | Failed AA |

The legacy names (`--bg`, `--dim`, `--dim-2`, `--border` …) are still emitted by `tokens.css` so the old stylesheet keeps working during the migration. Remove them once `style.css` no longer uses them.

## Type

Fonts load from Google Fonts with `display=swap`: Manrope 400–800, Fraunces 500 roman + italic, JetBrains Mono 400/500. Preload only Manrope 700 and Fraunces 500 italic (used above the fold).

| Style | Font | Size (fluid) | Line height | Weight | Where |
|---|---|---|---|---|---|
| `hero-h1` | Manrope | `--fs-hero-h1` clamp(44, 7vw, 92) | .98 | 700, −0.02em | Home H1 only |
| `contact-h2` | Manrope | `--fs-contact-h2` clamp(40, 6.4vw, 80) | 1.08 | 700 | Closing contact |
| `page-h1` | Manrope | `--fs-page-h1` clamp(38, 6vw, 74) | 1.04 | 700 | Inner pages, case study |
| `section-h2` | Manrope | `--fs-section-h2` clamp(34, 5vw, 56) | 1.08 | 700 | Section headings |
| `card-h3` | Manrope | `--fs-card-h3` clamp(21, 2vw, 25); lead clamp(24, 2.6vw, 32) | 1.12 | 700 | Cards |
| `voice` | Fraunces italic | inherits | — | 500 | `<em class="voice">` inside headings |
| `bridge` | Fraunces | `--fs-bridge` clamp(28, 4vw, 48) | 1.2 | 500 | Bridge statement |
| `quote` | Fraunces italic | `--fs-quote` clamp(19, 1.8vw, 22) | 1.45 | 500 | Testimonials, pull quote |
| `stat` | Fraunces | `--fs-stat` clamp(44, 6vw, 72) | 1 | 500 | Verified stats, gradient text |
| `lede` | Manrope | `--fs-lede` clamp(18, 1.8vw, 21) | 1.6 | 400 | Intros |
| `body` | Manrope | 17px (16px < 760px) | 1.65 | 400 | Default |
| `body-sm` | Manrope | 15.5px | 1.6 | 400 | Card summaries |
| `terminal` | Mono | 14.5px (13px < 760px) | 1.85 | 400 | Hero terminal |
| `behind` | Mono | 13.5px | 1.7 | 400 | Behind panel, at-a-glance |
| `path` | Mono | 13px, +0.02em | 1 | 500 | `~/elisa/work` eyebrows |
| `meta` | Mono | 12.5px | 1.5 | 500 | Role lines, dates, hints |
| `eyebrow` | Mono uppercase | 12px, +0.14em | 1 | 500 | Title line, stat labels |
| `tag` | Mono | 11.5px | 1 | 500 | Tags, badges |

Headings use `text-wrap: balance`. Keep running text under about 65 characters wide (`max-width: 560–620px` on intros).

## Space, layout, shape

- Content width `--wrap` 1180px; side gutter `--gutter` clamp(16px, 4vw, 40px).
- Section padding `--section-pad` clamp(80px, 11vw, 140px); section head → content clamp(40px, 6vw, 64px).
- Grids use `gap`, never margins between siblings. Work grid gap `--grid-gap` clamp(18px, 2.5vw, 28px).
- Breakpoints: **980px** (two columns → one for hero, about, case study), **860px** (mobile nav, touch behind toggle), **760px** (single column, swipe row for featured work).
- Radii: tags 6 · small buttons 8 · buttons 12 · terminal 16 · cards 20 · capability frame 22 · pills 999.
- Shadows: only on card hover (`--shadow-card-hover`), the terminal glow and floating controls. No other shadows.
- Tap targets: 44px minimum for anything that stands alone (`--tap`).

## Components

Markup contracts are below. Styles are in `reference/components.css`. Each component's ACF source is in `03-acf-schema.md`.

### Path eyebrow

```html
<div class="eyebrow path"><b>~/elisa</b>/work</div>
```
Every homepage section starts with one. The path segment is the section slug: `work`, `services`, `about`, `track-record`, `contact`.

### Buttons

```html
<a class="btn btn-primary" href="…">See the work <span class="arr" aria-hidden="true">→</span></a>
<a class="btn btn-ghost js-open-contact" href="/contact/">Start a project</a>
<a class="link-arrow" href="…">See a migration <span class="arr" aria-hidden="true">→</span></a>
```
One primary per view. "Start a project" always opens the contact modal (`js-open-contact`) and falls back to `/contact/` without JS.

### Hero terminal

```html
<div class="terminal" aria-label="Summary">
  <div class="term-bar" aria-hidden="true"><i></i><i></i><i></i><span>~/elisa</span></div>
  <div class="term-body">
    <div class="ln p" style="--i:0">$ whoami</div>
    <div class="ln o" style="--i:1">Elisa A. Dela Cruz · Senior WordPress &amp; Shopify Developer</div>
    <div class="ln o" style="--i:5">✔ <a href="/projects/…/">One beauty brand, four storefronts</a></div>
    <div class="ln ok" style="--i:9">Open to projects and senior roles<span class="cursor" aria-hidden="true"></span></div>
  </div>
</div>
```
All lines are real text in the HTML. They appear one by one with a CSS animation delay (`animation-delay: calc(var(--i) * .25s + .3s)`); there is no JS typing. With reduced motion, everything shows at once.

### Project card (front + behind)

```html
<article class="card flip lead span-4" id="project-us-beauty-brand" data-type="built" data-platform="shopify" data-reveal>
  <div class="shot">
    <div class="shot-bar" aria-hidden="true"><i></i><i></i><i></i><span>us-beauty-brand · 4 storefronts</span></div>
    <div class="shot-img"> <!-- visual: img | .split with 2 imgs | illustration | terminal mock --> </div>
  </div>
  <div class="card-body">
    <div class="tags"><span class="tag built">Built</span><span class="tag">Shopify</span><span class="tag">Liquid</span></div>
    <h3><a href="/projects/us-beauty-brand/">One beauty brand, four Shopify storefronts</a></h3>
    <p>Summary…</p>
    <div class="names"><span>Boat O'Craigo · Yarra Valley</span>…</div>   <!-- grouped projects only -->
    <div class="role">Shopify developer · via Microsourcing · 2020–22</div>
    <button class="behind-toggle" type="button" aria-expanded="false" aria-controls="behind-us-beauty-brand">What's behind it ↓</button>
  </div>
  <div class="behind" id="behind-us-beauty-brand">
    <div class="head"><span>$ cat behind.log</span><span>built · 2020–22</span></div>
    <dl><dt>needed:</dt><dd>…</dd></dl>
    <button class="behind-close" type="button">Close ×</button>
  </div>
</article>
```

- The whole card is clickable through `h3 a::after` only when the project has a case study. Without one, the title is plain text.
- `data-type` lists the project_type slugs (`built`, `solved`, or both) for the JS filter. `data-platform` lists platform slugs.
- Desktop with hover: the behind panel slides up on `:hover` and `:focus-within`, read-only (`pointer-events:none`) so clicks still reach the case-study link.
- Touch or < 860px: the panel sits in the card flow, hidden, toggled by the button (`aria-expanded`). Opening moves focus to Close; Close returns focus to the toggle.
- If a project has no behind rows, render no panel and no toggle, and drop the `flip` class.
- Card widths on the homepage 6-column grid: `span-4` (wide lead), `span-3` (half), default `span 2`.
- Confidential: browser label is a description, visual is an illustration or terminal mock, tags and alt text never name the client.

### "Also built" tile

A dark `.card.more-tile` at the end of the homepage grid listing selected projects as `<li><b>Name</b><span>Platform · Region</span></li>`, with "All projects →" to `/projects/`.

### Work filter

```html
<nav class="filters" aria-label="Filter projects">
  <a href="/projects/?type=all" data-type="all" aria-current="true">All</a>
  <a href="/projects/?type=built" data-type="built">Built</a>
  <a href="/projects/?type=solved" data-type="solved">Solved</a>
</nav>
<p class="sr-only" id="filter-status" aria-live="polite"></p>
```
Real links that work without JS (the archive filters server-side). JS filters in place, updates `aria-current`, the URL (`history.replaceState`) and the live region ("Showing 4 solved projects"). Uses the View Transitions API when available and motion is allowed. On `/projects/` a second row filters by platform.

### Capability card ("Called in for")

```html
<div class="called-grid">
  <div class="called">
    <span class="num">01 / move</span>
    <h3>Moving house without losing the furniture</h3>
    <p>…</p>
    <span class="stack">301 maps · data import · DNS · Search Console</span>
    <a class="link-arrow" href="…">See a migration <span class="arr" aria-hidden="true">→</span></a>
  </div>
</div>
```
The link renders only when a target is set.

### Behind the code

Hex-clipped portrait (`.hex`, sticky on desktop), story paragraphs, numbered principles (`ol.principles`, numbers drawn by CSS counters), pull quote (`figure.pull`), leadership strip (`.lead-strip`, ink background on cream).

### Commit-log timeline

```html
<p class="log-head">$ git log --career --oneline</p>
<ol class="log">
  <li class="head"><details open><summary><span class="hash">a3f9e12</span><span class="msg"><b>feat:</b> going independent <i>(HEAD → now)</i></span><span class="when">2022 →</span></summary>
    <div class="body"><div class="meta">Client Website Manager · independent</div>…</div></details></li>
  <li class="older">…</li>
</ol>
<button type="button" class="log-more" aria-expanded="false" hidden>$ git log --all <span>(show 2011–2015)</span></button>
```
Native `<details>`: works with no JS. JS adds `.collapsed` to hide `li.older` beyond the visible count and un-hides the button. The first entry (HEAD) is open by default. Hash: use the entry's `hash` or the first 7 characters of `md5(message . when)`.

### Testimonial

```html
<figure class="testi">
  <span class="kind">$ role: client</span>          <!-- .kind.emp + "employer" for employers -->
  <blockquote>"…"</blockquote>
  <figcaption class="who"><b>Rachel S.</b> · Principal Consultant, Capability Partners</figcaption>
  <a class="more" href="…">Full recommendation on LinkedIn ↗</a>   <!-- only with a source URL -->
</figure>
```
No stars, no carousel, no auto-rotation. Grid of up to three (`.testis.three`).

### Stats

`.stats > .stat > b + span`. Only rows marked verified. If none are verified, the whole row is omitted. Final values are in the HTML; no count-up animation.

### Contact block and modal

Centered night section with a violet glow, H2 with one `voice grad-text` word, intro, primary button (opens modal) + ghost "Download CV" (only with a CV), an alt row (copy-email button, LinkedIn, GitHub; each only when set), and a mono meta line. The modal is the existing CF7 modal restyled with these tokens; keep the focus trap and focus return already in `main.js`.

### Case study

See `reference/prototype/case-study.html`. Hero (crumbs, tags, H1, lede), the project visual, then a two-column layout: sticky **At a glance** box (mono `dl`) + body with sections 1–6, the **Technical breakdown** in a `<details class="tech">`, an optional testimonial, and two next steps (*Next case study →*, *Start a project*).

## Motion

1. Content first: everything is in the HTML and visible without JS or animation.
2. Animate only `transform` and `opacity`. UI 150–300ms, reveals ≤ 450ms, easing `--ease` cubic-bezier(.2,.7,.2,1).
3. One moving thing per screen. The hero uses the CSS `rise` entrance (staggered 60ms) and the terminal lines; nothing else moves on load.
4. Scroll reveal: `[data-reveal]` rises 12px once, via IntersectionObserver. The `.js` class on `<html>` is what hides elements first, so no-JS visitors see everything.
5. `prefers-reduced-motion: reduce` turns off every animation and transition.
6. Cut for good: loader, mouse parallax, cursor glow, floating particles, marquee, auto-rotating testimonials, animated world map, GSAP.

## Accessibility

- Skip link to `#main`, one H1 per page, landmarks (`header`, `nav`, `main`, `footer`).
- `:focus-visible` ring from `--focus`, 2px, offset 3px, on every interactive element.
- Nothing is hover-only: the behind panel has a button on touch and opens on focus with a keyboard.
- Filters announce results in a live region; copy-email announces "Email address copied".
- Modal: `role="dialog"`, `aria-modal`, labelled by its heading, focus trapped, Escape closes, focus returns to the trigger (already built in `main.js`).
- Images: real `alt` for screenshots ("Boat O'Craigo homepage"), `alt=""` for decoration, `width`/`height` always set, `loading="lazy"` below the fold.
- Contrast: use the token pairs as documented; don't invent new text colours.
