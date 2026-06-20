# Axe

Axe renders documents on-brand. A semantic CSS base styles plain HTML, and a matching set of components renders the standardized text formats the browser won't — CSV, Markdown, and iCalendar. One variable contract drives all of it, so point Axe at any of them and it comes out looking like your site.

It doesn't sit in a familiar category, and it isn't trying to. It isn't a utility framework (Tailwind), a component library (Bootstrap), or a design system. It's a small framework plus a curated set of components, held together by one idea: every piece takes a document and renders it on-brand through the same variable contract. The CSS base does that for semantic HTML; the components do it for the document formats HTML leaves on the floor.

## What Axe Is

The web runs on a document metaphor. A server sends a document and the requestor renders it; a browser, at its core, is a document viewer. But it's a selective one. It renders HTML, images, and PDF natively, and for nearly everything else it gives up and downloads the file. The axe viewer picks up a defined slice of what the browser abandons: standardized, text-based formats that carry visual structure worth rendering and have no native browser renderer. CSV, Markdown, iCalendar. It's the renderer the browser never shipped — point it at one of those files with `?url=`, and it renders it on-brand. (Browsing the directories that hold those files is a separate tool, [browse](https://github.com/anderix/browse), which hands each file back to this viewer to render.)

That boundary is a door policy, not an accident. A format earns a place in the viewer when it is text, standardized, structurally renderable, and unrendered by browsers. JSON is already handled by browsers, so it stays out. YAML and TOML are configuration rather than documents, so they stay out. The set is curated on purpose — which is why this is the axe viewer, not a universal one.

The components carry no look of their own, and that is deliberate. A standalone widget ships its own complete styling and imposes it on every host; an axe component ships almost none and wears the host's identity through the variable contract instead. That dependence is the reason the components live inside Axe rather than as separate libraries. They are built on the CSS base as a substrate, not decorated by it as a convenience — pull the base out from under the calendar and its toolbar buttons drop to bare browser defaults. The coupling isn't a packaging detail to engineer away; it is what the components are for. They are the proof that the contract is worth depending on.

The viewer reads, it never writes. It fetches a representation and renders it: no upload, no delete, no write surface. That is the document metaphor held to its word — a browser doesn't write to the server to render a page, and neither does the viewer.

## Quick Start

1. Copy the `axe/` folder into your project.
2. Create a `brand.css` defining your colors, fonts, and shape (or use the brand builder to generate one).
3. Import both in your project CSS or HTML:

```html
<link rel="stylesheet" href="brand.css">
<link rel="stylesheet" href="axe/axe.css">
```

4. Write semantic HTML. No classes required for standard elements.

## Running Locally

A page styled with Axe opens straight from disk — the CSS and the vendored scripts load over `file://` with no server needed. The viewers in `view/` are different: they `fetch()` the file you point them at, and browsers block `fetch()` of local files (every `file://` document is treated as its own opaque origin), so a viewer pointed at a local document over `file://` will report "Could not load." The fix is to serve the files over HTTP — any static server works, and no PHP or other backend is involved. The recommended option is Python's built-in server, since it's present wherever Python is:

```bash
cd path/to/axe          # or your project root
python3 -m http.server 8000
# then open http://localhost:8000/view/?url=sample.csv
```

Any other static server does the job too — for example `php -S localhost:8000` if you already have PHP on hand.

## Shipping Documents (cleave)

When you want to hand someone a rendered document they can just open — no server, no internet — bake it with `tools/cleave.py`. It inlines the document and only the assets that format needs into one self-contained `.html` that the viewer renders in place (over `file://`), sidestepping the fetch restriction above.

```bash
tools/cleave.py report.md            # -> report.html (a document)
tools/cleave.py deck.md --slides     # -> deck.html (a slide deck)
tools/cleave.py data.csv             # -> data.html (an interactive table)
tools/cleave.py team.ics             # -> team.html (a calendar)
tools/cleave.py report.md --brand mybrand.css   # inline a brand palette
```

For Markdown the render mode follows the same rules as the live viewer: `--slides` (or a `mode: slides` frontmatter key) makes a deck, otherwise it's a document. The output is portable and offline — email it, drop it on a share, open it from a USB stick. cleave finds the Axe assets relative to its own location, so symlinking it onto your PATH works: `ln -s "$PWD/tools/cleave.py" ~/bin/cleave`. One caveat: the default output name swaps the extension for `.html`, so `report.md` and `report.csv` would both target `report.html` — pass an explicit output name to disambiguate.

## Structure

```
axe.css               Framework core. Projects import brand.css + axe.css.
default.css           Default brand baseline (a complete set of contract vars).
                      Sites override it with their own brand.css.
theme.js              Theme detection and toggle. Include in <head>.
calendar.js           iCalendar (.ics) engine: parser, day/week/month/list views, CSV/iCal export.
calendar.css          Calendar styles. Uses the variable contract only.
sample.csv            Demo CSV (also the CSV-view demo and fixture).
sample.md             Demo Markdown document (also the document-view demo and fixture).
sample.ics            Demo calendar feed (also the viewer demo and round-trip fixture).
sample-slides.md      Demo slide deck (also the slides-view demo and fixture).
kitchen-sink.html     Reference page showing all styled HTML elements.
README.md             This file.
dependencies/
  marked.min.js       Markdown parser for the viewer (MIT licensed).
  purify.min.js       DOMPurify — sanitizes rendered Markdown (Apache-2.0 / MPL-2.0).
tools/
  brand-builder.html  Generates brand.css from color, font, shape, and shadow inputs.
  cleave.py           Bakes a CSV/Markdown/iCalendar file into one self-contained
                      HTML file that renders from disk (file://) with no server.
view/
  index.html          Axe viewer: renders one CSV, Markdown, or iCalendar file. ?url=path/to/file
                      Markdown renders as a document or, with ?view=slides (or mode: slides
                      frontmatter), as a native slide deck.
```

## How Projects Use Axe

Each project provides its own `brand.css` defining the visual identity. `axe.css` is universal and shared. Project-specific component classes go in the project's own stylesheet.

```css
@import url('brand.css');
@import url('axe/axe.css');

/* Project-specific styles below */
```

## Layout

Axe provides two layout containers:

`<main>` is a full-width container (max 1100px, no surface background). Use it for app-like pages.

`<article>` is a constrained document panel (max 860px, surface background, shadow). Use it for prose and documents.

`<section>` groups content with border separators.

`.grid` is the only class the CSS base adds. It creates a responsive card grid. Children can be `<article>` or `<a>` elements. (The document components carry their own classes, namespaced under `.axe-cal` and the viewer chrome.)

## Calendar (iCalendar)

The axe viewer renders `.ics` / `.ical` feeds the same way it renders CSV and Markdown. Point it at a feed and it opens with a component-owned toolbar above a scrolling view body: Day, Week, Month, and List tabs, a Today button with a direction arrow, prev/next navigation, a clickable title that opens a date picker, a timezone selector, and CSV / iCal export. On a narrow screen the right cluster collapses into a hamburger and List becomes the default view.

```
view/index.html?url=path/to/feed.ics
view/index.html?url=path/to/feed.ics&view=week
```

Append `&view=` to open on a specific view — `day`, `week`, `month`, or `list`. It's the same `?view=` parameter Markdown uses for `doc`/`slides`, its valid values keyed to the file type. Omit it (or pass anything unrecognized) and the calendar opens on Month, exactly as before — on a narrow screen the existing responsive override still makes List the default.

`calendar.js` is the engine behind it: a single classic script with no dependencies and no build step, the same relationship `marked.min.js` has with Markdown. It parses RFC 5545 iCalendar, recurrence included, and renders four views — a Day and Week time grid with overlap-aware event columns and a live current-time line, a Month grid with true multi-day spanning bars, and a lazy-loading List — then exports back to CSV (RFC 4180) or iCalendar (round-trip stable). It also embeds in any page on its own.

```html
<link rel="stylesheet" href="calendar.css">
<script src="calendar.js"></script>
<div id="cal"></div>
<script>
  const cal = new Calendar(document.getElementById('cal'), {
    url: 'feed.ics',             // or source: '<raw iCal text>'
    view: 'month',               // 'day' | 'week' | 'month' (default) | 'list'
    timezone: 'America/Chicago'  // optional; defaults to the browser zone
  });
  cal.render();
</script>
```

After it loads, `cal.switchView('list')`, `cal.setTimezone('UTC')`, `cal.filter(e => …)`, and `cal.export('csv' | 'ical')` drive it.

The parser is standards-only: it reads compliant iCalendar and carries no vendor-specific branches. A feed that encodes data in a non-standard way (Scoutbook, for instance, writes all-day events as timed midnight-to-23:45) should be normalized by whatever serves it, never patched for inside the engine.

### Category colors

Event chips and bars are tinted by a per-event hue, computed deterministically from the category name in `calendar.js`. Color is always a redundant cue — the label rides along — so the calendar stays readable when it's ignored. The shared saturation and lightness, plus the fallback hue for uncategorized events, come from three brand tokens so a site can tune them (including per theme); they default to a mid blue and `404` harmlessly back to in-component fallbacks when undefined.

| Variable             | Default | Purpose                                       |
|----------------------|---------|-----------------------------------------------|
| --cal-cat-hue        | 210     | Fallback hue for events with no category      |
| --cal-cat-saturation | 55%     | Saturation of all categorical chips and bars  |
| --cal-cat-lightness  | 50%     | Lightness of all categorical chips and bars   |

### Remote feeds and CORS

External `?url=` fetches are denied by default for security (see [SECURITY.md](SECURITY.md)); enable specific hosts via `EXTERNAL_ALLOWLIST` in `view/index.html`. Even once allowlisted, the viewer fetches in the browser, so a remote feed only loads if that origin sends `Access-Control-Allow-Origin` — most calendar feeds don't. A locked-down remote feed needs a same-origin proxy that re-serves it, and that proxy is also the right place to normalize any non-standard encoding before the calendar sees it. Local and same-origin files load directly and are unaffected by the allowlist.

## Dark Mode

Brand files generated by the brand builder include light mode, dark mode, and system preference support out of the box. Include `theme.js` in your `<head>` to detect system preference and restore saved choices. Add a `<button class="theme-toggle" aria-label="Toggle theme"></button>` anywhere in your page to let users switch themes. Both the button styles and the script behavior are part of the framework.

Leave that button empty and the framework draws the icon for you — a sun in light mode, a moon in dark — tracking the resolved theme so it agrees with the painted page even before `theme.js` runs. The button must be genuinely empty (`:empty` matches no child nodes, not even whitespace, so write the tags adjacent), and it still needs an `aria-label` since the glyph is decorative. To use different icons, redefine `--theme-toggle-icon-light` and `--theme-toggle-icon-dark` in your brand or site CSS; their values are CSS `content` strings (for example `"\2600"` for ☀). Put your own markup inside the button instead and the default glyph steps aside.

## Variable Contract

Variables are split into two groups: a **required contract** that `axe.css` depends on, and an **extended palette** that the brand builder generates for convenience but that `axe.css` never references.

### Required contract

`axe.css` may only reference variables in this list. Any `brand.css` must define them. Adding a new variable to `axe.css` requires adding it here and to the brand builder output.

| Variable                | Purpose                              |
|-------------------------|--------------------------------------|
| --color-bg              | Page background                      |
| --color-surface         | Card, main, elevated surface         |
| --color-text            | Primary body text                    |
| --color-text-muted      | Secondary / caption text             |
| --color-border          | Borders and dividers                 |
| --color-accent          | Links, buttons, primary emphasis     |
| --color-accent-hover    | Hover state for accent               |
| --color-highlight       | Marks, highlights, secondary accent  |
| --color-highlight-hover | Hover state for highlight            |
| --color-nav-bg          | Navigation background                |
| --color-nav-text        | Navigation link color                |
| --color-danger          | Errors, destructive actions, now-line |
| --color-success         | Confirmation, positive status        |
| --color-warning         | Caution, pending status              |
| --font-body             | Body typeface                        |
| --font-heading          | Heading typeface                     |
| --font-mono             | Code and monospace                   |
| --line-height           | Base line height                     |
| --radius                | Border radius (all corners)          |
| --shadow                | Subtle elevation shadow              |
| --shadow-md             | Medium elevation shadow              |

### Extended palette

The brand builder generates these from the two color inputs (primary and accent). `axe.css` never references them, but they're documented here so projects can use them consistently across brand guides, component styles, and overrides. They're theme-independent (unchanged between light and dark mode) since they describe the raw brand palette rather than UI roles.

| Variable                | Purpose                                           |
|-------------------------|---------------------------------------------------|
| --primary               | Primary brand color (raw input)                   |
| --primary-tint-1/2/3    | Progressively lighter mixes toward white          |
| --primary-shade-1/2/3   | Progressively darker mixes toward black           |
| --secondary             | Accent brand color (raw input)                    |
| --secondary-tint-1/2/3  | Progressively lighter mixes toward white          |
| --secondary-shade-1/2/3 | Progressively darker mixes toward black           |

Tints and shades are generated by RGB mixing toward white or black at stops of 30%, 60%, and 85%. RGB mixing desaturates tints naturally, producing UI-functional neutrals rather than saturated color ramps.

## Design Rules

**Build what you need, not what you might need.** A pattern enters the framework when a real project requires it. No speculative additions.

**Semantic first.** Style HTML elements directly before reaching for classes. If a `<button>` can look right without a class, it should.

**`brand.css` is always project-specific. `axe.css` is universal.** `axe.css` must work with any valid `brand.css`. Never hard-code colors, fonts, or radii in `axe.css`.

**Mobile first.** Base styles target small screens. Use `min-width` media queries to expand.

**JavaScript only where rendering needs it.** The CSS base is styling alone; `theme.js` adds theme detection and the toggle. The components that render documents — the calendar engine behind the viewer — carry their own JavaScript, with no build step and a small set of vendored dependencies (a Markdown parser and a sanitizer). A component earns its script by rendering a format CSS can't.

**When in doubt, put it in the project first.** Promote to the framework when a second project needs it.

## Security

The viewer renders document content as live HTML in your site's origin, so treat every document you point it at as code. Markdown is sanitized with DOMPurify and calendar event URLs are scheme-checked before they become links, but those are mitigations, not a license to render untrusted input freely. External `?url=` fetches are denied by default. Before you deploy the viewer, read [SECURITY.md](SECURITY.md) — it covers the threat model, the `EXTERNAL_ALLOWLIST` knob, and the operator responsibilities the framework cannot enforce for you. (The directory-browsing tool [browse](https://github.com/anderix/browse) carries its own server-side lister and its own `SECURITY.md`.)

## Versioning

Axe carries a single version number so you can tell which build a site is running — it's vendored into several projects, and copies drift. The number is stamped in the file headers (`axe.css`, `calendar.css`, `calendar.js`), exposed as the `--axe-version` custom property, and as `Calendar.version`. To audit a deployment, `curl https://site/axe/axe.css | head`, or read `getComputedStyle(document.documentElement).getPropertyValue('--axe-version')` (or `Calendar.version`) in the console. Bump all of those together on release.

This is a deploy-tracking stamp, not a strict semver contract; git remains the source of truth for what changed. Breaking changes to variable names still get a dated note below.

The working copy is stamped with a plain release version — no `-dev` suffix. Most consumers (`anderix.com`, `excelano.com`, `troop99.org`, `xinglet.com`) ride the `axe -> ~/axe` symlink, so they deploy whatever the working copy holds the next time `updatesite` runs; there is no separate vendor step to lag behind. Stamp the version you're shipping the moment you make the change (via `./set-version.sh`), and that's what those sites report on their next deploy. The only true hard copies are projects like [interpreter-strip](https://github.com/anderix/interpreter-strip) that vendor a subset of files by hand; re-vendor those in the same pass when a change affects them.

### 1.7.6 (2026-06-20)

The document viewer no longer floats a short Markdown file as a shrunken block near the centre of the page. `.md-view` is a flex item in the flex-column `main`, and its `margin: 0 auto` disables the cross-axis stretch — so without an explicit width it sized to its content instead of the column, and a two-line document drifted to the middle of the viewport. It now carries `width: 100%` for the same reason `body > main` already does, so every document fills the centred 860px column and short pages read left-aligned from the column edge like long ones. Surfaced by a short page in the xcribe drop-in; the bug was latent for any brief document.

### 1.7.5 (2026-06-19)

A bare `<button class="theme-toggle"></button>` now draws its own sun/moon glyph, so consumers no longer hand-roll one. The icon shows the current theme — sun in light, moon in dark — and is resolved the same two ways `default.css` resolves dark (the `[data-theme="dark"]` attribute and the `prefers-color-scheme` media query, gated on `:not([data-theme="light"])`), so it agrees with the painted page even before `theme.js` runs or with JavaScript off. The glyphs are monochrome dingbats (U+2600, U+263E) that inherit `currentColor`, exposed as `--theme-toggle-icon-light` / `--theme-toggle-icon-dark` for a site to override. The rule is scoped to `:empty`, so a button with its own content is left untouched and the change is additive — existing consumers that supply their own glyph (anderix's inline character, excelano's nested span) are unaffected until they migrate. kitchen-sink and the document viewer switched to bare buttons to demonstrate the default.

### 1.7.4 (2026-06-19)

Clicking a slide now advances the deck, matching the PowerPoint/Keynote default — previously the only ways forward on a desktop were the arrow keys (or Space/PageDown) and the small `›` button. The handler lives on the `.stage` so the control bar is unaffected, and it skips clicks on in-deck links and on click-drags that selected text, so following a link or copying a quote no longer jumps a slide. Back-navigation stays on the keys and the `‹` button, leaving right-click to the browser's context menu.

### 1.7.3 (2026-06-17)

Fixes the viewer's theme toggle needing two clicks on first use, and removes the drift that caused it. The viewer had hand-copied `theme.js`'s detect-and-toggle logic inline, but dropped the `prefers-color-scheme` fallback from the init — so when the OS was in dark mode with no saved choice, the page rendered dark via the media query with no `data-theme` attribute set, and the first click merely re-asserted dark (a visual no-op) before the second flipped it. The viewer now loads `theme.js` in `<head>` like it already loads `calendar.js`, deleting both inline copies, so there is one source of truth. `cleave` learned a `SRC_THEME` entry to inline `theme.js` into baked files, parallel to `SRC_CALJS`; its template-drift guard now fails the bake loudly if that script tag ever goes missing.

### 1.7.2 (2026-06-15)

Widens the CSV viewer's always-visible scrollbars to roughly 1.75x. Firefox's `scrollbar-width` takes no pixel value, so it moves from `thin` to `auto` (its wide track); Chromium and Safari get an explicit 21px width via the `::-webkit-scrollbar` rule, with the thumb radius bumped to match.

### 1.7.1 (2026-06-15)

Makes the 1.7.0 CSV freezing actually work in Firefox, where it didn't. Three causes: the page body grew with content so the whole page scrolled rather than `.csv-wrap`, leaving the sticky header nothing to stick within and pushing the horizontal scrollbar below the fold — the body is now pinned to the viewport so `.csv-wrap` is the scroll container on both axes; `position: sticky` on a `<th>` is silently dropped under `border-collapse: collapse` in Firefox, so the table is now `border-collapse: separate`; and the header and filter rows both pinned to `top: 0` and overlapped, so the filter row is now offset below the header by its measured height. The always-visible scrollbar's thumb also moved from the too-faint `--color-border` to the muted-text gray so it actually reads.

### 1.7.0 (2026-06-15)

Makes the CSV viewer usable on wide files. The header row and the row-number column now freeze: the header stays put on vertical scroll (it already did) and the `#` column stays put on horizontal scroll, with the header's number cell pinned as the top-left corner. The scroll container also gets a thin, always-visible on-brand scrollbar instead of relying on the OS overlay scrollbar, which auto-hides on GNOME/GTK and left no cue that a wide table scrolls at all — `scrollbar-color` opts Firefox out of overlay mode, with matching `::-webkit-scrollbar` rules for Chromium and Safari.

### 1.6.0 (2026-06-15)

The calendar's initial view is now selectable from the URL: `&view=day|week|month|list` (or a baked `data-view`) opens the viewer on that view. It reuses the same `?view=` parameter Markdown uses for `doc`/`slides` — one parameter whose valid values are keyed to the file type — and validates against the calendar's own view registry, so an unrecognized value (including the Markdown ones) falls back to Month, exactly as when the parameter is omitted. The narrow-screen List default is unchanged.

### 1.5.2 (2026-06-15)

Fixes unreadable columns in the CSV viewer on wide files. The table used `table-layout: fixed; width: 100%`, which crammed every column into the viewport and divided the width evenly regardless of content, so a many-column file squeezed each column down to a few characters. It now uses `table-layout: auto` so columns size to their content (capped at `400px` per cell, with an ellipsis), with `min-width: 100%` to still fill the width when a narrow file leaves slack and a `5rem` floor so no column collapses. Wide tables overflow into the horizontal scrollbar that the scroll container already provided. The full value of any clipped cell remains available via its hover `title`.

### 1.5.1 (2026-06-15)

Fixes a contrast regression on the calendar's export buttons. The viewer's hand-built `Export CSV`, `Export iCal`, `Subscribe`, and `Copy URL` buttons carried only `cal-action`, not `cal-action ghost`. When the surface treatment moved onto the shared `.ghost` class in 1.1.0, these buttons lost their background and fell back to the framework's solid-accent base `<button>`, leaving accent-colored labels on an accent fill — unreadable, worst on dark brands. They now match the component's own actions and render as proper ghost buttons.

### 1.5.0 (2026-06-15)

Adds a serverless path for shipping rendered documents. The viewer learns an embedded-document mode: when a page contains a hidden `<textarea id="axe-embed">` carrying the document text, it renders that directly and skips the `fetch()` the live viewer normally does — which means it works over `file://`, where browsers block fetching local files. A new tool, `tools/cleave.py`, produces those pages: point it at a `.csv`, `.md`, or `.ics` and it bakes one self-contained `.html` with the document and only the assets that format needs inlined (so a CSV stays small and skips `marked`/`calendar.js`), ready to email or open from a USB stick. The same Markdown file becomes a document or a deck via `--slides`, mirroring the live viewer. Two render functions that referenced fetch-path variables (`name`, `fetchUrl`) were hoisted so the embedded path renders cleanly, including the calendar.

### 1.4.1 (2026-06-15)

Fixes the slide presenter's full-screen control, which used a single corner arrow (`⇱`) that read as pointing the wrong way. It now uses the standard direction-neutral four-corners glyph (`⛶`).

### 1.4.0 (2026-06-14)

Teaches the viewer to render a Markdown file as a slide deck, on the same brand contract as everything else it renders. A `.md` file is a document by default and a deck when asked — the switch is `?view=slides` (the analog of `pandoc -t pptx`), with a `mode: slides` frontmatter key as the in-file default; `?view=doc` forces the document rendering of a deck file. Slides divide the way pandoc divides them — a blank-line-surrounded `---` rule always breaks, and a heading at the slide level breaks too, with the level autodetected (or pinned via a `slide-level:` frontmatter key) — so the same file the viewer presents is the file `pandoc -t pptx` exports. The presenter is native, no third-party engine: one slide at a time, keyboard (arrows, space, PageUp/Down, Home/End, `f` for full screen), on-screen controls, touch swipe, a progress bar, and a slide hash so deep links and back/forward work. It scales with pure CSS container-query units — no JS measurement — and the same `marked` and DOMPurify the document path already uses do the parsing and sanitizing, so a deck carries no new attack surface.

### 1.3.0 (2026-06-14)

Splits the file browser out of the viewer. The directory-listing feature — its `list.php` backend, its `renderDir` front-end, and its file-list styling — now lives in a separate tool, [browse](https://github.com/anderix/browse), which lists a directory and hands each file back to this viewer to render. Axe's viewer becomes render-only: point it at a `.csv`, `.md`, or `.ics` and it renders that one document; a path with no recognized extension now shows a short "point me at a file" message instead of browsing.

The reason is deployment, not identity. Axe is vendored almost everywhere for its styles and document viewers, but `list.php` was the only server-side, filesystem-reading, per-deploy-configured code in the bundle — and it shipped to every site whether or not that site browsed files. Pulling it into its own tool means the default Axe is now entirely client-side with no server surface, safe to deploy as-is, while directory listing becomes a deliberate opt-in you stand up only where you want it.

### 1.2.0 (2026-06-14)

Closes the viewer's untrusted-document attack surface and adds a `SECURITY.md` covering the threat model and the knobs deployers need. The viewer renders document content as live HTML in the host origin, so each ingest path is now defended.

Markdown is sanitized with DOMPurify (newly vendored at `dependencies/purify.min.js`) before it reaches the DOM — `marked` does no sanitizing, so a `.md` file's raw `<img onerror=…>` or `<script>` previously executed as the host site. Calendar event URLs (the iCalendar `URL:` property) now pass through a scheme allowlist before becoming a link `href`, so a `javascript:` URL is dropped and the event renders as plain text instead of a clickable script.

External `?url=https://…` fetches are now denied by default. Rendering third-party content in your origin is a reflected-XSS vector; enable specific hosts via the `EXTERNAL_ALLOWLIST` array in `view/index.html`. This also closes an open redirect on unknown file types. The `view/list.php` directory lister is now confined to a configurable `$confine` subtree, resolved with `realpath()` so the check also blocks symlinks pointing outside the tree. The viewer page ships a defense-in-depth Content-Security-Policy, and the CSV filter input's value is now attribute-escaped.

### 1.1.0 (2026-06-12)

Makes the viewer and calendar fully brandable — no hardcoded color survives outside the brand source — and promotes the patterns the calendar proved into the framework.

Adds three semantic state tokens to the required contract: `--color-danger`, `--color-success`, and `--color-warning`, with light and dark values in `default.css` and the brand builder. The calendar's current-time line now draws from `--color-danger` instead of a literal red, and the two nav-hover overlays that hardcoded a translucent white — which assumed a dark nav and vanished on a light one — now derive from `--color-nav-text`, so they read on either.

Consolidates the calendar's category color into one shared `--cat-color` recipe. Every categorical surface — month bars, list rows, Day and Week blocks, popover dots, and chips — had re-inlined the same `hsl()` computation; they now mix a single value computed once from the per-event hue and the brand category tokens. This retires the Day and Week color divergence, where those blocks ran a parallel recipe that ignored the brand tokens, and it tints the list row's left spine by category to match the other views, so the color scan cue is consistent across all four.

Adds a `.ghost` button variant. A bare `<button>` is the solid accent primary; `.ghost` is the neutral secondary that takes the accent only on hover. The framework styled every button as a primary and offered no secondary, so each component that wanted a quiet button redeclared the surface treatment and re-asserted its background on hover to defeat the primary fill — nine times over. That hack now lives once in the framework, and the calendar's toolbar buttons carry only their own deltas.

Promotes three more patterns to framework classes, each demoed in the kitchen sink: `.panel` (an elevated surface for popovers and cards), `.eyebrow` (a monospace overline label), and `.tag` (a status pill). Adds a `--z-*` stacking scale so a sticky bar, a popover, and a modal layer predictably — an embedded calendar's popover now clears the viewer's sticky toolbar rather than ducking under it.

### 1.0.0 (2026-06-10)

First stable release — the version stamp leaves the `-dev` track. The calendar component is feature-complete: Day, Week, Month, and List views under one component-owned toolbar, RFC 5545 parsing with recurrence, CSV and iCal export, timezone control, and full brand-variable theming. The work that landed on the way here:

Completes the component-owned toolbar and the full view set. The calendar now renders one persistent toolbar above a scrolling view body — Today with a direction arrow, prev/next navigation, a clickable title that opens a date picker, the view tabs, declarative host actions, and the theme toggle — and the host supplies only the feed and any custom controls (via `getToolbarSlot()`). On a narrow screen the right cluster collapses into a hamburger and the list becomes the default. Navigation is wired to the wheel (paging the month, scrolling the list) and the keyboard (PageUp/PageDown page, Home jumps to today). The toolbar's ghost buttons re-assert their own background on hover, so the framework's default accent button-fill no longer leaks under them and washes out the accent-colored label.

Adds the Day and Week time-grid views, so the set is now Day, Week, Month, and List. Both render a 24-hour vertical grid with the hours in a left gutter, timed events positioned by start and end and split into side-by-side columns when they overlap, all-day and multi-day events on a header strip above the grid, and a live current-time line on today's column. The two views share one renderer over a different day list (one column versus seven). Day and Week page along the horizontal axis — the same two nav chevrons re-point left and right — and their title-click picker offers a day grid rather than the month grid Month and List use; the wheel scrolls the hours rather than paging.

Fixes a list-view bug where a single unbreakable token (a long URL in a description) could force an event card wider than the viewport, which — because a vertical scroll container also accepts horizontal scrolling — let the list pan sideways on a touch swipe. Event cards now shrink to the available width and break long tokens, and the scrolling views lock their horizontal axis.

Reworks the Day and Week time grid into a single sticky scroller so a narrow Week stays usable. The whole grid — the day-name header, the all-day strip, the hour gutter, and the day columns — now lives in one CSS grid inside one scroll container, with the header rows and the gutter frozen by `position: sticky`. On a wide screen the seven columns share the width and only the hours scroll, exactly as before, with the bonus that the header now also pins when you scroll down. On a phone the columns take a fixed width and the day area scrolls horizontally past the frozen gutter and header, like a spreadsheet with a frozen first column and header row; the view opens centered on today. Day view keeps its single full-width column and never scrolls sideways.

Fixes a related bug where switching from Day or Week back to Month or List left scrolling dead. The body element persists across view changes, and the time grid's `overflow: hidden` was lingering on it because each renderer only cleaned up after some of the others. Clearing every view's layout class in one place before each draw makes the next view's own scrolling reliable regardless of where you came from.

Fixes the frozen hour gutter scrolling away on a narrow Week. `position: sticky` clamps an element to its containing block, and the grid container had collapsed to the viewport width while its columns overflowed it — so the gutter could only stay pinned across the table's own narrow width, then slid off with the rest. Letting the table grow to its content width on the narrow path gives the sticky gutter and header the full scroll extent to hold against.

Adds a horizontal swipe to the Day view to page to the adjacent day — swipe left for the next day, right for the previous — since the prev/next chevrons hide on a narrow screen. A view opts in with a `swipeNav` flag, so Week (where a horizontal swipe scrolls its day columns) is deliberately left out. The swipe commits only to a clear, dominantly-horizontal gesture, so a vertical hour-scroll never pages.

Makes the narrow month fallback a clean single month. The small-screen stand-in for the month grid is a day-grouped list; it now expands recurrence only within the displayed month and emits only that month's days, instead of borrowing the List view's infinite, today-anchored feed. That drops both the leading and trailing days that pad the wide six-week grid and any out-of-month occurrences of a recurring series, so the list reads as exactly the month its title names.

A pre-release code review hardened three things. The description linkifier now escapes double and single quotes before building its anchors, closing an attribute-breakout hole where a quote inside a feed's URL or description could inject an event handler when the description was assigned via `innerHTML` — relevant because the axe viewer renders arbitrary user-supplied `.ics`. Recurrence expansion now keeps an occurrence whenever it overlaps the visible window rather than only when its start falls inside it, so a multi-day recurring event (a repeating campout) that begins just before the window still rides into view. And the component gained a `destroy()` method that stops the now-line ticker, disconnects the list observer, and removes its resize, scroll, and document-level outside-click listeners, for a host that mounts and unmounts the calendar.

### 0.4.1-dev (2026-06-10)

Promotes the calendar's categorical event colors to brand tokens (`--cal-cat-hue`, `--cal-cat-saturation`, `--cal-cat-lightness`), so the chip and bar palette is now fully brand-overridable — the last hard-coded color values in the component. Defaults match the previous look, so no rendered calendar changes.

Reworks the list view to open on today and lazy-load by event count. It scrolls inside the calendar area (like the month grid), so a toolbar above stays put; it pins today (or the next upcoming day) to the top with a small run of history just above; and it renders only a window — roughly the last 10 events back and two screens forward — extending in 20-event batches as you scroll, instead of dumping the whole feed oldest-first. Like the month grid, the list view needs a height-constrained container (a definite `height`, not `min-height`); without one it falls back to normal flow.

### 0.4.0 (2026-06-10)

Renames the in-axe default brand `brand.css` → `default.css` and establishes the brand cascade: a page links `default.css` (a complete baseline) and then its own sibling `brand.css`, which overrides only what differs and 404s harmlessly when absent. The axe viewer links the site brand this way, so it inherits each deployment's identity. This decouples the framework from any single brand: `axe/` becomes uniformly symlinkable across a site collection (or shared from one public copy), with `brand.css` the only per-site file.

### 0.3.0 (2026-06-09)

Adds the iCalendar calendar component (`calendar.js` + `calendar.css`): a default month grid with true multi-day spanning bars, a list view, CSV and iCal exporters, timezone control, and viewer integration for `.ics`/`.ical`. Also fixes four nav cascade gaps so a nav can mix anchors, plain text, and a theme toggle and stay readable in light and dark.

---

Built with the assistance of Claude (Anthropic).
