# 05 — Content to enter

Everything below is **verified or approved** by Elisa (from her CVs, her project list, the live site and the approved prototype). Enter it as written. Anything marked **⏳ pending** must stay **empty**: do not fill it with a guess, a placeholder or the prototype's review notes.

Use this file two ways:
1. Cursor writes `docs/redesign/tools/seed-content.php` from it, to fill a **local** test site (`wp eval-file`). Never run it against the live site.
2. Elisa enters the same content on the live site after she approves the build.

Screenshots for the seed live in `docs/redesign/reference/prototype/assets/shots/`.

## 1. Profile & Links (Site Content → Profile & Links)

| Field | Value |
|---|---|
| `profile_name` | Elisa A. Dela Cruz |
| `profile_short_name` | Elisa Dela Cruz |
| `profile_title` | Senior WordPress & Shopify Developer |
| `portrait` | The illustrated portrait already in the Media Library (`…/uploads/2026/07/628105985_26434143126170683_3120392800049720687_n.jpg`). Alt: "Illustrated portrait of Elisa at her desk with a laptop and a coffee" |
| `availability` | ⏳ pending |
| `location_line` | Manila, working with AU / US / UK time zones |
| `reply_promise` | Replies within one working day |
| `cv_file`, `linkedin_url`, `github_url` | ⏳ pending |

### Career log (newest first)

| type | message | context | when | meta | is_head | body |
|---|---|---|---|---|---|---|
| feat | going independent | HEAD → now | 2022 → | Client Website Manager · independent · local & overseas clients | ✔ | Customising Shopify themes, building WordPress funnels and landing pages, and looking after sites long-term. I also coordinate a small team of SEO specialists, Google Ads people, copywriters and junior developers. |
| feat | Shopify at scale, four storefronts | US beauty brand, via Microsourcing | 2020–22 | Shopify Developer · US account | | Maintaining the US store and replicating it for Australia, Europe and Canada. In the same years: WordPress development and support for Capability Partners and The AM College in Australia, and consulting for Cenit Lighting in Manila. |
| chore | dedicated developer for US & AU accounts | Microsourcing, EMAPTA | 2018–20 | WordPress Developer · Fourandhalf (US real estate) · RedEx Digital & Filta (AU) | | Designing, building and maintaining WordPress sites for overseas agencies and their clients. |
| feat | leading the dev team | | 2016–18 | Mid-level Web Developer / Project Coordinator | | E-commerce sites for US clients on Shopify and WordPress. I ran the dev team, the scrum meetings and the client reporting. This is where I learned that most project problems aren't code problems. |
| refactor | agency years, AU clients | ZigZag / The Colour Club, Follow Digital | 2015–18 | Web Developer · Sage + SASS builds | | Custom WordPress themes for Australian agencies, including photography portfolios built from scratch on Sage. |
| feat | HTML, jQuery, PHP, WordPress & Magento | Glued Creative | 2012–15 | Web Developer | | Where WordPress became the main tool, with Magento along the way. |
| init | PHP portal systems | J.A.O. Builders | 2011 | PHP Web Developer | | A web-based portal system and its database, from scratch. The first commit. |

Hashes: leave empty (generated).

## 2. Testimonials (Testimonials → Add New)

| Title (admin) | `quote` | `quote_short` | `person_name` | `person_role` | `company` | `relationship` | `source_url` | `permission_confirmed` |
|---|---|---|---|---|---|---|---|---|
| Rachel S. – Capability Partners | Working with Elisa has been an outstanding experience. She consistently delivers high-quality solutions, responds quickly to client needs, and combines strong technical expertise with genuine professionalism. | | Rachel S. | Principal Consultant | Capability Partners | client | | **on** (already published on the live site) |
| Christopher Sorini – LinkedIn | Give her an unfamiliar technology, and she'll learn it quickly, then teach you a thing or two along the way. … Elisa is someone I was fortunate to hire and would gladly work with again. | She brings the confidence to challenge assumptions and the judgment to make that pushback constructive. If a timeline needs rethinking or there's a better way to approach a problem, she'll speak up and explain why. | Christopher Sorini | ⏳ pending | ⏳ pending | employer | ⏳ pending | **off** until Elisa confirms |

Do **not** create testimonials for "Grand Teton" or "Flat Stacks" (placeholders, deleted) or for the hero pull quote (real, but attribution pending).

## 3. Projects (Projects → Add New)

Taxonomy terms: Kind = `built` / `solved`; Platform = `wordpress` / `shopify` / `woocommerce` / `elementor`.

### 3.1 US beauty brand (featured, lead, confidential, has case study)

| Field | Value |
|---|---|
| Title / slug | One beauty brand, four Shopify storefronts / `us-beauty-brand` |
| Kind / Platforms | Built / Shopify |
| `tier` | featured |
| `client_display_name` | US beauty brand |
| `confidential` | **on** |
| `industry_badge` / `region` | Beauty / US |
| `role` / `engaged_via` | Shopify developer / via Microsourcing |
| `year_start` / `year_end` | 2020 / 2022 |
| `summary` | Two years on a US beauty brand. I replicated the US store into Australia, Europe and Canada, and built product filtering and colour swatches in custom code instead of apps, across more than 1,000 SKUs. I worked alongside the marketing team on launches, sales and pricing. |
| `chips` | Liquid · 1,000+ SKUs |
| `card_span` | wide |
| `visual_mode` / `illustration` | illustration / swatches |
| `browser_label` | us-beauty-brand · 4 storefronts |
| `visual_badge` | illustration · client not named |
| `live_url`, screenshots | **none, ever** |
| `behind_meta` | built · 2020–22 |
| `behind_rows` | needed → the US store, replicated for AU, EU and CA · tricky → 1,000+ SKUs, four regions, and no apps for filtering or swatches · did → store replication, custom filtering, colour swatches, launch + sale + pricing support · stack → Shopify · Liquid · JavaScript · with → the marketing team |
| `has_case_study` | on |
| `cs_headline` | One beauty brand, four storefronts |
| `cs_lede` | For two years I looked after a US beauty brand's Shopify store, then replicated it for Australia, Europe and Canada, with filtering and colour swatches written in custom code instead of bolted on with apps. |
| `at_a_glance` | client → US beauty brand (not named) · industry → Beauty / cosmetics · role → Shopify developer, via Microsourcing · when → 2020–22 · 2 years · regions → US · AU · EU · CA · catalogue → 1,000+ SKUs · stack → Shopify · Liquid · JavaScript · no apps for filtering or swatches · worked with → Marketing team |
| `situation` | The brand sells beauty products online, with a catalogue of more than 1,000 SKUs. The US store was the original. The brand wanted the same shopping experience in Australia, Europe and Canada. |
| `tricky` | Colour is the product, so shoppers need to filter and compare shades fast. The obvious route was stacking apps for filtering and swatches. On a catalogue this size, every app adds load time, cost and another thing that can break, four times over. |
| `approach` | 1. Maintained the US store for two years: fixes, updates and changes as the business needed them. 2. Replicated it for AU, EU and CA, so each region got the same experience. 3. Built product filtering in custom code, without an app. 4. Built colour swatches in custom code, so shades read correctly across product pages and collections. 5. Worked with the marketing team on new product launches, sales and discount pricing. |
| `technical_breakdown` | ⏳ pending (the prototype's Liquid snippet is illustrative only; Elisa supplies the real approach) |
| `result` | The US store runs filtering and swatches that don't depend on third-party apps. |
| `learned` | ⏳ pending (the prototype line is a draft) |

### 3.2 Three wineries (featured)

| Field | Value |
|---|---|
| Title / slug | Three wineries, moved to Shopify / `three-wineries` |
| Kind / Platforms | Solved + Built / Shopify |
| `tier` | featured |
| `client_display_name` | Boat O'Craigo, Frankland Estate and God's Hill |
| `industry_badge` / `region` | Wine / Australia |
| `role` | Design & development |
| years | ⏳ pending |
| `summary` | Australian wineries moved off older platforms onto custom Shopify stores. For Boat O'Craigo I did the new design too. |
| `names` | Boat O'Craigo → Yarra Valley · Frankland Estate → (empty) · God's Hill → Barossa Valley |
| `chips` | Migration |
| `visual_mode` | screenshot (`boatocraigo.jpg`, alt "Boat O'Craigo homepage") |
| `browser_label` / `visual_badge` | boatocraigo.com.au / + 2 more wineries |
| `live_url` | https://boatocraigo.com.au/ |
| `behind_meta` | migrations |
| `behind_rows` | from → Vin Creative (×2), WordPress (×1) · to → custom Shopify stores · did → Boat O'Craigo: new design + build. Frankland Estate, God's Hill: custom build + migration |
| `has_case_study` | off (⏳ what moved, outcome) |

### 3.3 PerkRunner + Winehub (featured)

| Field | Value |
|---|---|
| Title / slug | Selling subscriptions to Shopify merchants / `perkrunner-winehub` |
| Kind / Platforms | Built / WordPress |
| `tier` | featured |
| `client_display_name` | PerkRunner and Winehub |
| `industry_badge` | Shopify subscription and loyalty software |
| `role` | Design & development |
| years | ⏳ pending |
| `summary` | Designed and built from scratch: the websites for two Shopify subscription and loyalty platforms, PerkRunner and Winehub. |
| `chips` | Design |
| `visual_mode` | split (`perkrunner.jpg` alt "PerkRunner homepage", `winehub.jpg` alt "Winehub homepage") |
| `browser_label` | perkrunner.com · winehub.io |
| `behind_meta` | built · from scratch |
| `behind_rows` | needed → marketing sites for two SaaS products · did → design, build and launch, from blank page to live · stack → WordPress |
| `has_case_study` | off |

### 3.4 Sound & Sight Solutions (featured)

| Field | Value |
|---|---|
| Title / slug | From design file to working website / `sound-and-sight` |
| Kind / Platforms | Built / WordPress + Elementor |
| `tier` | featured |
| `client_display_name` | Sound & Sight Solutions |
| `industry_badge` / `region` | Smart home & AV / Chicago, US |
| `role` | Development |
| years, `live_url` | ⏳ pending |
| `summary` | A custom WordPress site for Sound & Sight Solutions, a Chicago smart-home and AV company, built from their design all the way to deployment. |
| `chips` | Custom |
| `visual_mode` | screenshot (`soundsight-design.jpg`, alt "Sound and Sight Solutions homepage design") |
| `browser_label` / `visual_badge` | Sound & Sight Solutions / design file |
| `behind_meta` | built · design → deploy |
| `behind_rows` | needed → a working site matching the approved design · did → custom build, responsive behaviour, deployment · stack → WordPress · Elementor |
| `has_case_study` | off |

### 3.5 Cenit Lighting + Oasis Water (featured, last)

| Field | Value |
|---|---|
| Title / slug | A Shopify store, then the sister brand / `cenit-oasis` |
| Kind / Platforms | Built / Shopify + WordPress + Elementor |
| `tier` | featured |
| `client_display_name` | Cenit Lighting and Oasis Water Philippines |
| `industry_badge` / `region` | Lighting and water / Philippines |
| `role` | Shopify & WordPress developer |
| years | ⏳ pending |
| `summary` | Cenit Lighting hired me to build their Shopify store. Then they brought me back to turn their design for Oasis Water into a working WordPress site in Elementor. |
| `card_span` | half |
| `visual_mode` | split (`cenit.jpg` alt "Cenit Lighting Shopify store", `oasis.jpg` alt "Oasis Water Philippines homepage") |
| `browser_label` | shop.cenitlighting.com · oasiswaterph.com |
| `behind_meta` | built · repeat client |
| `behind_rows` | first → Shopify store for Cenit Lighting (their WordPress site stayed as is) · then → Oasis Water Philippines: their design, built in WordPress + Elementor · why it matters → they came back |
| `has_case_study` | off |

Note: Cenit hired Elisa for the Shopify store only. Never describe a Cenit WordPress rebuild.

### 3.6 Selected work and highlights

| Title (public name) | Kind | Platforms | `tier` | `region` | `role` |
|---|---|---|---|---|---|
| TVI Resource Development | Built | WordPress, Elementor | selected | PH | From scratch to deployment |
| St. Augustine Gold & Copper | Built | WordPress, Elementor | selected | PH | From scratch to deployment |
| D&D Music | Built | WordPress, Elementor | selected | PH | From scratch to deployment |
| Futbol Funatics | Built | WordPress, Elementor | selected | PH | From scratch to deployment |
| Sorini Strategic Advisors | Built | WordPress, Elementor | selected | | Design → deploy |
| Vertical Green | Built | WordPress, Elementor | selected | | Implemented their design + updates |
| Home of Lights | Built | Shopify | highlight | | Custom PDP template |

For each: `client_display_name` = the title, summary ⏳ pending (leave empty; the card shows name, platform · region/role only), no screenshots until Elisa provides them, `live_url` ⏳ pending. Home of Lights behind rows: did → custom product field + custom product page template · stack → Shopify · Liquid.

**Do not create** (decision pending): Fleshlight Australia, Maria Nicola, Flat Stacks, Vanco, any security or performance story.

## 4. Homepage (Site Content → Homepage (B−))

Fields with approved defaults (title line, headline, intro, buttons, bridge, section headings, contact copy) are already set in the field group. Fill these:

| Field | Value |
|---|---|
| `home_terminal` | p `$ whoami` · o `Elisa A. Dela Cruz · Senior WordPress & Shopify Developer` · p `$ stack` · o `WordPress · Shopify · WooCommerce · PHP · Liquid · JS` · p `$ last_fixed` · o `✔ One beauty brand, four storefronts` → link: the US beauty brand project · o `✔ Three wineries, moved to Shopify` → link: Projects archive. ⏳ `$ uptime` (years) and `$ status` (availability) wait for confirmation |
| `home_featured_projects` | US beauty brand, Three wineries, PerkRunner + Winehub, Sound & Sight, Cenit + Oasis (this order) |
| `home_more_projects` | TVI Resource Development, St. Augustine Gold & Copper, D&D Music, Futbol Funatics, Sorini Strategic Advisors, Home of Lights |
| `home_called` | see table below |
| `home_about_story` | I started in 2011 writing PHP portal systems. Somewhere along the way I became the person teams call when a migration stalls, a site gets hacked, or a checkout breaks at 2am. (⏳ second paragraph: countries list needs confirming) |
| `home_principles` | Find the real problem first. → I'd rather spend an hour diagnosing than a week fixing the wrong thing. · Leave it better documented than I found it. → Handovers, redirect maps, and notes for whoever comes next. · Say the uncomfortable thing early. → If the timeline, plugin or plan won't work, you'll hear it in week one, not week six. |
| `home_pull_testimonial` | Christopher Sorini (stays hidden until permission is confirmed) |
| `home_lead_text` | I've led development teams, creative teams and junior developers, and I mentor young designers and aspiring developers. Today I coordinate SEO specialists, ads, copywriters and junior developers on client projects. (⏳ "Later, UX and UI teams as a senior engineer" once Chris's details are confirmed) |
| `home_record_heading` | ⏳ pending (depends on confirmed stats; renders "Track record." meanwhile) |
| `home_stats` | ⏳ none until confirmed (candidates: 15 years since 2011, 150+ projects, 4 or 5 countries) |
| `home_testimonials` | Rachel S., Christopher Sorini |

### Called in for

| `label` | `title` | `text` | `stack` | `link_label` | `link` |
|---|---|---|---|---|---|
| 01 / move | Moving house without losing the furniture | Migrations: WooCommerce to Shopify, host to host, theme to theme. Rankings, redirects, customers and orders come along. | 301 maps · data import · DNS · Search Console | See a migration | Projects archive |
| 02 / rescue | Getting hacked sites back on their feet | Malware cleanup, recovery, and hardening, so it doesn't happen twice. | file forensics · clean restore · hardening · monitoring | See a rescue | ⏳ empty until a rescue story exists |
| 03 / speed | Making slow sites stop being slow | Core Web Vitals, bloated page builders, image pipelines, caching, and the plugin nobody remembers installing. | Lighthouse · caching · images · script audits | See a speed fix | ⏳ empty until a performance story exists |
| 04 / build | Building the bit the theme can't do | Custom Liquid sections, ACF architecture, PHP and JavaScript functionality, API integrations and automation. | Liquid · PHP · ACF · REST / GraphQL · JS | See a build | Projects archive |

## 5. Inner pages

Assign the templates in wp-admin (Page → Template): `/about/` → About, `/services/` → Services, `/contact/` → Contact.

| Page | Enter now | ⏳ Needs Elisa's copy |
|---|---|---|
| About | `about_h1` (default), `about_names` (default), `about_principles` (same three as the homepage) | `about_lede`, `about_story`, `about_leadership`, `about_lessons`, `about_more` |
| Services | `services_h1` (default); `services_engagements` titles: Build, Move, Rescue, Look after; `services_process` titles: Diagnose → Plan → Build or fix → Hand over; `services_agencies_heading` (default) | Every description, what's included, FAQ answers (pricing, hours, access) |
| Contact | `contact_h1` (default) | `contact_lede`, `contact_next_steps` |

Empty blocks don't render, so the pages are publishable with only the "enter now" content. The pages already exist and are indexed today with thin content: until they have real copy, Elisa may set them to `noindex` in Yoast.

## 6. wp-admin chores (Elisa, on the live site, after approval)

- Unpublish both "Hello world!" posts.
- Sync ACF field groups (ACF → Field Groups → Sync).
- Enter the content above; upload screenshots as WebP.
- Assign page templates; set the primary menu (Work → `/projects/`, Services, About, Contact).
- Check Search Console for "Elisa Dela Cruz" queries before changing the homepage title in Yoast.

## Update, 6 Nov 2026 (Elisa's feedback)

Source: Elisa's 2026 portfolio PDF (`Elisa-Dela-Cruz-Portfolio-2026.pdf`) and her instructions in chat. All of it is in `tools/seed-content.php`.

- Grouped cards split: Boat O'Craigo, Frankland Estate and God's Hill Wines are separate projects; so are Winehub and PerkRunner, and Cenit Lighting and Oasis Water. The old grouped posts are drafted (not deleted) and their URLs redirect to the new cards.
- Homepage featured: US beauty brand, Boat O'Craigo, God's Hill Wines, Winehub, PerkRunner, Frankland Estate. Cenit and Oasis moved to Selected work on /projects/.
- 25 Selected work projects and 2 Technical highlights, with summaries in Elisa's portfolio wording. Screenshots are fresh captures of the live sites (1440px); Sound & Sight uses Elisa's own full-size design file. Work-in-progress wineries, prototypes and sites without a public page have no screenshot.
- Christopher Sorini: shown (Elisa asked), with the review and title from the portfolio. Company and LinkedIn link still ⏳.
- Stats: 13+ years, 150+ projects, 5 countries (portfolio page 1).
- New homepage Toolbox section: tools (portfolio page 3), roles (page 1) and skills (pages 1 and 18).
- Still anonymous: the US beauty brand. The portfolio names it, but the site keeps it unnamed until Elisa says otherwise.

- Services page filled from the portfolio (pages 1 and 18): lede, four engagements with related projects, process steps, agencies text and four FAQs. Pricing and hours are still ⏳ (Elisa to confirm).
- Screenshots for Château Tanunda, Seppeltsfield, All Saints (labelled "new design"), the Winehub and PerkRunner app prototypes and DCX Commerce, cropped from the portfolio PDF. On /projects/, cards without a screenshot sort last.

## Fixed-price packages (added 6 Nov 2026, NOT confirmed)

Services → "Fixed-price fixes": four packages suggested by Claude (Site audit $350, Malware removal $450, Speed fix $500, Care plan $150/month). **Hidden** until Elisa reviews the prices and scope and switches on *Show fixed-price packages* on the Services page in wp-admin.

- The import adds the packages only while the list is empty; after that they are Elisa's to edit.
- The import never clears a field that already has a value, so anything filled in wp-admin (CV, links, payment links) survives future deploys. Fields the import fills with text are still updated by it.
- **Payment:** create a Stripe Payment Link per package (in Elisa's Stripe account), paste it into the package's *Payment link* field. In Stripe, set the link's "after payment" redirect to `https://elisaarmas.com/contact/?package=<slug>&paid=1` (slugs: `site-audit`, `malware-removal`, `speed-fix`, `care-plan`) so the client lands on the Contact form with a "Payment received" note. Without a link, the button opens the Contact form with the package filled in.
- **Service terms:** draft page `/service-terms/` with [confirm] lines. Linked under the packages once published.
