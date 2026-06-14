# Security Policy

## Reporting a vulnerability

Please report suspected vulnerabilities privately through GitHub Security Advisories at https://github.com/anderix/axe/security/advisories/new. If you would rather not use GitHub, email david.anderson@excelano.com instead. I aim to respond within seven days.

Please do not open public issues for security problems.

## Supported versions

Axe is vendored into each consuming site rather than installed from a package registry. Security fixes ship through `main`; pull the latest `axe.css`, `calendar.js`, `view/`, and the files under `dependencies/`, then redeploy to apply them. There are no maintained release branches.

## Security model — read this before deploying the viewer

The CSS framework itself (`axe.css`, `calendar.css`, `default.css`, `theme.js`) is static styling with no attack surface. The security model is entirely about the **viewer** (`view/`) and the **calendar component** (`calendar.js`), because their whole job is to turn a document into live HTML in your site's origin.

Treat every document the viewer renders as if it were executable code. A `.md`, `.csv`, or `.ics` file is markup, and markup that reaches the DOM can carry script. Axe defends against this in two places, but the defenses are mitigation, not a license to render anything from anywhere:

- **Markdown** is run through DOMPurify (`dependencies/purify.min.js`) before insertion, which strips `<script>`, `onerror`/`onload` and other event-handler attributes, and `javascript:` URLs. Do not remove this step or render Markdown with `marked.parse()` directly — `marked` does not sanitize.
- **Calendar event URLs** (the `URL:` property of an `.ics` event) are passed through a scheme allowlist before becoming a link `href`. Only `http:`, `https:`, and `mailto:` survive; a `javascript:` URL is dropped and the event renders as plain text.

CSV and TSV cells are HTML-escaped on the way into the table, so a spreadsheet cannot inject markup.

## What the viewer fetches

By default the viewer only reads files from its own origin. The `?url=` parameter accepts a root-relative path (`?url=reports/q1.md`) and fetches it same-origin.

External URLs (`?url=https://…`) are **denied by default**. Rendering third-party content in your origin is a reflected-XSS vector — a crafted link would run someone else's markup as your site. To enable specific remote feeds, add their hostnames to the `EXTERNAL_ALLOWLIST` array near the top of the script block in `view/index.html`, and add the same hosts to the `connect-src` directive of the Content-Security-Policy. Only allowlist hosts whose content you trust as much as your own.

## The directory lister

`view/list.php` returns a JSON listing of a directory's contents. It has no authentication and is meant to back the viewer's file-browser mode. It is confined to a configurable subtree: the `$confine` constant at the top of the file defaults to the whole web root (the original behavior), and the request path is resolved with `realpath()` and checked to fall inside that subtree, which also blocks symlinks pointing outside it. Tighten `$confine` to the smallest directory the viewer needs (for example `$root . '/files'`) so the lister cannot enumerate the rest of your site, and never point it at a tree containing credentials, source, or other files you would not publish.

## Operator responsibilities

A few things only the deployer can do, and Axe cannot enforce them for you:

- Only point the viewer at documents whose authorship you trust. The built-in sanitization raises the bar but is not a substitute for controlling your inputs.
- Serve the viewer over HTTPS.
- For higher assurance, serve the viewer from a dedicated origin or subdomain, so that a sanitizer bypass cannot reach your main site's cookies or session.
- The `view/index.html` page ships a defense-in-depth Content-Security-Policy in a `<meta>` tag. A header-based CSP is stronger: it can use a script nonce instead of `'unsafe-inline'` and can set `frame-ancestors` (which `<meta>` cannot). Add one at the web-server level if your hosting allows it.
- The calendar component trusts its `source` text completely. When you embed `Calendar` directly rather than through the viewer, the provenance of the iCalendar text is your responsibility.
