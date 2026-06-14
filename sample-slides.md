---
title: Axe Slides
mode: slides
---

# Axe Slides

A Markdown file, rendered as a deck

Open with `?view=slides` — or let this `mode: slides` frontmatter decide

# How a deck is written

It is ordinary Markdown. A new slide starts at each top-level heading, and a blank-line-surrounded `---` rule forces a break anywhere you want one.

The same file renders as a document with `?view=doc`, and exports to PowerPoint with `pandoc -t pptx`.

# It renders what Markdown renders

- Lists, **bold**, *italic*, and `inline code`
- Links, blockquotes, tables
- Fenced code blocks with the brand's monospace font

# On the brand contract

| Element | Source |
|---------|--------|
| Heading rule | `--color-accent` |
| Callout bar | `--color-highlight` |
| Table header | `--color-nav-bg` |
| Body type | `--font-body` |

# A callout

> Every color and font comes from the same variable contract as the rest of Axe, so a deck matches the documents and the site it sits beside.

---

# A forced break

This slide followed a `---` rule rather than a heading — both pandoc break mechanisms work.

```bash
# present it
open "/axe/view/?url=sample-slides.md"
# export it
pandoc -t pptx -o deck.pptx sample-slides.md
```

# Navigating

Arrow keys or space to move, `Home` and `End` to jump, `f` for full screen. Swipe on a touch screen. The slide number lives in the URL hash, so deep links and the back button work.
