# 01 — Brief

## What we're building

A redesign of **elisaarmas.com**, the portfolio of **Elisa A. Dela Cruz**, a senior WordPress & Shopify developer based in Manila (career since 2011). The site is a custom WordPress theme (`themes/elisa-dela-cruz`), 100% driven by ACF PRO.

The approved direction is **B−**: keep the current visual identity (dark night sections, cream sections, magenta → violet → coral gradient, Manrope / Fraunces / JetBrains Mono) and add substance and personality:

- a real portfolio, **"The work behind the websites"**, where each project card has a flip side showing the engineering behind it;
- a capability section that links each claim to proof;
- a short "behind the code" About block with principles and leadership;
- a track record with a **git-log career timeline** and verified testimonials;
- real inner pages: `/projects/`, `/projects/{slug}/`, `/about/`, `/services/`, `/contact/`.

The visual reference is the approved prototype: `reference/prototype/b-creative.html` (homepage) and `reference/prototype/case-study.html` (case-study template). Open them in a browser. Yellow-underlined text and dashed yellow boxes in the prototype are **review notes**, not design.

## Who it's for

Two audiences with equal weight, one brand:

| Audience | Wants | Path |
|---|---|---|
| **Clients** (agencies, business owners) | "Can she fix *my* problem?" | Hero → work card → case study → Start a project |
| **Employers** (hiring managers, tech leads) | "Is she senior, reliable, a good teammate?" | Hero "Hiring? Download CV" → work → case study technical breakdown → About timeline → CV / LinkedIn |

## Positioning and voice

- Title, everywhere: **Senior WordPress & Shopify Developer**.
- Public name: **Elisa A. Dela Cruz** (formal, schema, footer) / **Elisa Dela Cruz** (copy). "Armas" is her middle name. Do not write "Elisa Armas" as her name. Keep the alias list in `inc/seo-names.php`.
- Core line: *"I don't just build websites. I solve the problems behind them."*
- Home H1: *"Websites are the easy part."*

**Two voices** carry the whole design:

| Voice | Who's talking | Set in | Used for |
|---|---|---|---|
| The person | Elisa | Fraunces italic (`em.voice`) | One or two words per heading, quotes, the bridge statement |
| The system | Her tools | JetBrains Mono | Path eyebrows (`~/elisa/work`), terminal, behind panels, metadata, git log |

Everything else is Manrope.

**Copy rules:** plain words, first person, short sentences, active voice. Name things the way a client would ("moving your store", not "data migration pipeline"). No buzzwords, no exclamation marks, no emoji in copy.

## Non-negotiable rules

These came from Elisa and override anything else in these docs.

1. **Never invent content.** No made-up achievements, statistics, testimonials, client names, project outcomes or relationships. This includes fallback/default copy in PHP or JS.
2. **Empty means hidden.** If an ACF field is empty, the element it feeds does not render. Never print a placeholder, a `#` link, or "Lorem ipsum" to the public. (Admins may see a hint in wp-admin, never on the front end.)
3. **Confidential clients stay confidential.** The `confidential` flag on a project means: no real client name, logo, URL or screenshot anywhere, including alt text, filenames, schema and `data-` attributes. The lead project is shown only as "US beauty brand".
4. **Testimonials need permission.** A testimonial renders only when `permission_confirmed` is on.
5. **Stats need verification.** A stat renders only when its `verified` toggle is on.
6. **Don't remove content without evaluating its value.** Retired sections move to `template-parts/legacy/`; their ACF data stays in the database.
7. **Nothing reaches the live site without Elisa's explicit approval.** Work on a branch, test locally, open a pull request. Never deploy, never touch the production database, never run anything against `elisaarmas.com`.

## Decisions already made (don't re-open)

| Decision | Value |
|---|---|
| Direction | B− (Refined evolution + selected signature moments) |
| Audience split | 50% employers / 50% clients |
| Homepage order | Hero → Bridge → Work → Called in for → Behind the code → Track record → Contact |
| Work concept | "The work behind the websites", **Built** and **Solved**, filterable |
| Lead project | "US beauty brand" (confidential), illustrated swatch visual, never named |
| Last featured project | Cenit Lighting + Oasis Water ("repeat client") |
| Testimonials | Placeholder quotes ("Grand Teton", "Flat Stacks") are deleted for good. Rachel S. (Capability Partners) is real. Christopher Sorini's LinkedIn recommendation is real; title, company and permission pending. The hero pull quote is from a real client; attribution pending, so it is not used yet |
| Motion | Content first; CSS only; no loader; no GSAP; one moving thing per screen; full `prefers-reduced-motion` support |
| Nav | Work · Services · About · Contact + **Start a project** button |

## Still waiting on Elisa

Build the slots for these, keep them empty, and make sure empty renders nothing.

- Christopher Sorini: title, company, period, link, OK to quote.
- Hero pull-quote attribution.
- Years for each project; Sound & Sight live domain; what moved in the winery migrations; PerkRunner/Winehub stack.
- A real security/malware recovery story and a performance story.
- LinkedIn URL, GitHub URL, CV PDF.
- Confirmed stats (years, number of projects, countries) and the Track record heading that depends on them.
- Availability wording for the terminal status line.
- Decisions on Fleshlight AU (suggested: leave out), Maria Nicola and Flat Stacks ("previously maintained"), Vanco (design only).
