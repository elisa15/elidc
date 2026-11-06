# elisaarmas.com redesign (B−): implementation pack

Everything Cursor (or any developer) needs to build the approved B− redesign into the existing `elisa-dela-cruz` theme. Read the files in order the first time; afterwards, use them as reference.

| # | File | What it answers |
|---|---|---|
| 01 | [01-brief.md](01-brief.md) | What we're building, for whom, the voice, the **non-negotiable rules**, decisions already made, what's still pending |
| 02 | [02-design-system.md](02-design-system.md) | Tokens, colour contexts, type scale, layout, every component's markup, motion and accessibility rules |
| 03 | [03-acf-schema.md](03-acf-schema.md) | Content model, CPTs and taxonomies to register, helper functions, every ACF field (generated from the JSON) |
| 04 | [04-build-spec.md](04-build-spec.md) | File-by-file build: assets, PHP structure, templates, template parts, JS, SEO, performance |
| 05 | [05-content-entry.md](05-content-entry.md) | The exact, verified content to enter; what stays empty |
| 06 | [06-tasks-and-qa.md](06-tasks-and-qa.md) | Ordered tasks with acceptance criteria, QA checklist, definition of done |
| 07 | [07-deploy-plan.md](07-deploy-plan.md) | How the theme and content go live: stages, backup, rollback |

## Sources of truth

| What | Where |
|---|---|
| Design tokens | `design/tokens.json` → generates `themes/elisa-dela-cruz/assets/css/tokens.css` via `docs/redesign/tools/build-tokens.py` |
| Component CSS | `docs/redesign/reference/components.css` (starting point for `assets/css/site.css`) |
| Visual reference | `docs/redesign/reference/prototype/b-creative.html` (home) and `case-study.html`. Open in a browser. Yellow underlines and dashed yellow boxes are review notes, not design |
| ACF field groups | `themes/elisa-dela-cruz/acf-json/*.json` (local JSON, ready to sync) |
| Design System (browsable) | Claude Design System artifact "Elisa Dela Cruz Design System": https://claude.ai/artifact/A5nK7XDXpFyhoALkS57Wdw (private to Elisa unless she shares it) |
| Cursor rules | `.cursor/rules/elisa-redesign.mdc` (always applied) |

## The three rules that matter most

1. **Never invent content**, and never ship fallback copy that could be mistaken for real content. Empty field → nothing renders.
2. **Confidential stays confidential** ("US beauty brand" is never named, anywhere).
3. **Never deploy.** Branch, test locally, pull request. Elisa approves and deploys.

## Status (2 Nov 2026)

Done on branch `fix/content-integrity`: placeholder testimonials and fake fallback projects removed, dead `#` links hidden, loader removed, CSS-only hero entrance, `:focus-visible`, modal focus trap, schema job title, wp-emoji off, theme 1.3.0. Design tokens, component CSS, ACF field groups and these docs added.

Next: everything in `06-tasks-and-qa.md`, starting at Phase A.
