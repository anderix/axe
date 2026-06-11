# Axe

Axe is a lightweight, semantic CSS framework. Write plain HTML and it looks good on theme.

It is not a utility framework (Tailwind), a component library (Bootstrap), or a design system. It is a thin, opinionated base that makes semantic HTML look good and stay on-brand with zero configuration.

## Quick Start

1. Copy the `axe/` folder into your project.
2. Create a `brand.css` defining your colors, fonts, and shape (or use the brand builder to generate one).
3. Import both in your project CSS or HTML:

```html
<link rel="stylesheet" href="brand.css">
<link rel="stylesheet" href="axe/axe.css">
```

4. Write semantic HTML. No classes required for standard elements.

## Structure

```
axe.css               Framework core. Projects import brand.css + axe.css.
default.css           Default brand baseline (a complete set of contract vars).
                      Sites override it with their own brand.css.
theme.js              Theme detection and toggle. Include in <head>.
calendar.js           iCalendar (.ics) engine: parser, month + list views, CSV/iCal export.
calendar.css          Calendar styles. Uses the variable contract only.
sample.ics            Demo calendar feed (also the viewer demo and round-trip fixture).
kitchen-sink.html     Reference page showing all styled HTML elements.
README.md             This file.
dependencies/
  marked.min.js       Markdown parser for the viewer (MIT licensed).
tools/
  brand-builder.html  Generates brand.css from color, font, shape, and shadow inputs.
view/
  index.html          Universal viewer: directories, CSV, Markdown, iCalendar. ?url=path/to/resource
  list.php            Directory listing backend (returns JSON, requires PHP).
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

`.grid` is the only class in the framework. It creates a responsive card grid. Children can be `<article>` or `<a>` elements.

## Calendar (iCalendar)

The universal viewer renders `.ics` / `.ical` feeds the same way it renders CSV and Markdown. Point it at a feed and it opens on a month grid, with a list toggle, a timezone selector, and CSV / iCal export in the toolbar:

```
view/index.html?url=path/to/feed.ics
```

`calendar.js` is the engine behind it: a single classic script with no dependencies and no build step, the same relationship `marked.min.js` has with Markdown. It parses RFC 5545 iCalendar, renders a month grid with true multi-day spanning bars, and exports back to CSV (RFC 4180) or iCalendar (round-trip stable). It also embeds in any page on its own.

```html
<link rel="stylesheet" href="calendar.css">
<script src="calendar.js"></script>
<div id="cal"></div>
<script>
  const cal = new Calendar(document.getElementById('cal'), {
    url: 'feed.ics',             // or source: '<raw iCal text>'
    view: 'month',               // 'month' (default) or 'list'
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

The viewer fetches in the browser, so a remote `?url=` only loads if that origin sends `Access-Control-Allow-Origin`. Most calendar feeds don't. A locked-down remote feed needs a same-origin proxy that re-serves it, and that proxy is also the right place to normalize any non-standard encoding before the calendar sees it. Local files and same-origin or CORS-open feeds load directly.

## Dark Mode

Brand files generated by the brand builder include light mode, dark mode, and system preference support out of the box. Include `theme.js` in your `<head>` to detect system preference and restore saved choices. Add a `<button class="theme-toggle">` anywhere in your page to let users switch themes. Both the button styles and the script behavior are part of the framework.

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

**Minimal JavaScript.** `theme.js` is the only script in the framework. The brand builder and viewer are tools, not part of the core.

**When in doubt, put it in the project first.** Promote to the framework when a second project needs it.

## Versioning

Axe carries a single version number so you can tell which build a site is running — it's vendored into several projects, and copies drift. The number is stamped in the file headers (`axe.css`, `calendar.css`, `calendar.js`), exposed as the `--axe-version` custom property, and as `Calendar.version`. To audit a deployment, `curl https://site/axe/axe.css | head`, or read `getComputedStyle(document.documentElement).getPropertyValue('--axe-version')` (or `Calendar.version`) in the console. Bump all of those together on release.

This is a deploy-tracking stamp, not a strict semver contract; git remains the source of truth for what changed. Breaking changes to variable names still get a dated note below.

While iterating, the working copy carries a `-dev` suffix (for example `0.4.1-dev`). This is deliberate: `anderix.com` runs the working copy via the symlink and gets deployed often, ahead of the last stable release that other sites vendor. The `-dev` suffix keeps the stamp honest — a site reporting `0.4.1-dev` is bleeding-edge, one reporting `0.4.0` is the last release. When a change is stable enough to push everywhere, drop the suffix (`0.4.1`) and re-vendor the files into each consuming project in the same pass.

### 0.5.0-dev (2026-06-10)

Completes the component-owned toolbar and the full view set. The calendar now renders one persistent toolbar above a scrolling view body — Today with a direction arrow, prev/next navigation, a clickable title that opens a date picker, the view tabs, declarative host actions, and the theme toggle — and the host supplies only the feed and any custom controls (via `getToolbarSlot()`). On a narrow screen the right cluster collapses into a hamburger and the list becomes the default. Navigation is wired to the wheel (paging the month, scrolling the list) and the keyboard (PageUp/PageDown page, Home jumps to today). The toolbar's ghost buttons re-assert their own background on hover, so the framework's default accent button-fill no longer leaks under them and washes out the accent-colored label.

Adds the Day and Week time-grid views, so the set is now Day, Week, Month, and List. Both render a 24-hour vertical grid with the hours in a left gutter, timed events positioned by start and end and split into side-by-side columns when they overlap, all-day and multi-day events on a header strip above the grid, and a live current-time line on today's column. The two views share one renderer over a different day list (one column versus seven). Day and Week page along the horizontal axis — the same two nav chevrons re-point left and right — and their title-click picker offers a day grid rather than the month grid Month and List use; the wheel scrolls the hours rather than paging.

Fixes a list-view bug where a single unbreakable token (a long URL in a description) could force an event card wider than the viewport, which — because a vertical scroll container also accepts horizontal scrolling — let the list pan sideways on a touch swipe. Event cards now shrink to the available width and break long tokens, and the scrolling views lock their horizontal axis.

Reworks the Day and Week time grid into a single sticky scroller so a narrow Week stays usable. The whole grid — the day-name header, the all-day strip, the hour gutter, and the day columns — now lives in one CSS grid inside one scroll container, with the header rows and the gutter frozen by `position: sticky`. On a wide screen the seven columns share the width and only the hours scroll, exactly as before, with the bonus that the header now also pins when you scroll down. On a phone the columns take a fixed width and the day area scrolls horizontally past the frozen gutter and header, like a spreadsheet with a frozen first column and header row; the view opens centered on today. Day view keeps its single full-width column and never scrolls sideways.

Fixes a related bug where switching from Day or Week back to Month or List left scrolling dead. The body element persists across view changes, and the time grid's `overflow: hidden` was lingering on it because each renderer only cleaned up after some of the others. Clearing every view's layout class in one place before each draw makes the next view's own scrolling reliable regardless of where you came from.

Fixes the frozen hour gutter scrolling away on a narrow Week. `position: sticky` clamps an element to its containing block, and the grid container had collapsed to the viewport width while its columns overflowed it — so the gutter could only stay pinned across the table's own narrow width, then slid off with the rest. Letting the table grow to its content width on the narrow path gives the sticky gutter and header the full scroll extent to hold against.

Adds a horizontal swipe to the Day view to page to the adjacent day — swipe left for the next day, right for the previous — since the prev/next chevrons hide on a narrow screen. A view opts in with a `swipeNav` flag, so Week (where a horizontal swipe scrolls its day columns) is deliberately left out. The swipe commits only to a clear, dominantly-horizontal gesture, so a vertical hour-scroll never pages.

Makes the narrow month fallback a clean single month. The small-screen stand-in for the month grid is a day-grouped list; it now expands recurrence only within the displayed month and emits only that month's days, instead of borrowing the List view's infinite, today-anchored feed. That drops both the leading and trailing days that pad the wide six-week grid and any out-of-month occurrences of a recurring series, so the list reads as exactly the month its title names.

### 0.4.1-dev (2026-06-10)

Promotes the calendar's categorical event colors to brand tokens (`--cal-cat-hue`, `--cal-cat-saturation`, `--cal-cat-lightness`), so the chip and bar palette is now fully brand-overridable — the last hard-coded color values in the component. Defaults match the previous look, so no rendered calendar changes.

Reworks the list view to open on today and lazy-load by event count. It scrolls inside the calendar area (like the month grid), so a toolbar above stays put; it pins today (or the next upcoming day) to the top with a small run of history just above; and it renders only a window — roughly the last 10 events back and two screens forward — extending in 20-event batches as you scroll, instead of dumping the whole feed oldest-first. Like the month grid, the list view needs a height-constrained container (a definite `height`, not `min-height`); without one it falls back to normal flow.

### 0.4.0 (2026-06-10)

Renames the in-axe default brand `brand.css` → `default.css` and establishes the brand cascade: a page links `default.css` (a complete baseline) and then its own sibling `brand.css`, which overrides only what differs and 404s harmlessly when absent. The universal viewer links the site brand this way, so it inherits each deployment's identity. This decouples the framework from any single brand: `axe/` becomes uniformly symlinkable across a site collection (or shared from one public copy), with `brand.css` the only per-site file.

### 0.3.0 (2026-06-09)

Adds the iCalendar calendar component (`calendar.js` + `calendar.css`): a default month grid with true multi-day spanning bars, a list view, CSV and iCal exporters, timezone control, and viewer integration for `.ics`/`.ical`. Also fixes four nav cascade gaps so a nav can mix anchors, plain text, and a theme toggle and stay readable in light and dark.

---

Built with the assistance of Claude (Anthropic).
