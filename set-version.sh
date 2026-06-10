#!/usr/bin/env bash
#
# set-version.sh — stamp a new axe version across every file that carries it.
#
# Usage:
#   ./set-version.sh 0.4.1        # a release
#   ./set-version.sh 0.4.1-dev    # back to the working-copy suffix
#
# Touches the 5 code stamps only: axe.css (header + --axe-version),
# calendar.css (header), calendar.js (header + Calendar.version). It does NOT
# edit the README changelog — those notes are prose, so add a "### <version>
# (date)" entry by hand, then commit and tag.
#
set -euo pipefail
cd "$(dirname "$0")"

NEW="${1:-}"
if [ -z "$NEW" ]; then
    echo "usage: $0 <version>   e.g. $0 0.4.1   or   $0 0.4.1-dev" >&2
    exit 1
fi

# Shape check: digits.digits.digits with an optional -suffix (e.g. -dev).
if ! [[ "$NEW" =~ ^[0-9]+\.[0-9]+\.[0-9]+(-[a-z0-9.]+)?$ ]]; then
    echo "error: '$NEW' isn't a version (expected e.g. 0.4.1 or 0.4.1-dev)" >&2
    exit 1
fi

# Matches an existing version token wherever it appears: starts with a digit,
# then any digits / letters / dots / hyphens (so it catches 0.4.0 and 0.4.1-dev).
V='[0-9][0-9A-Za-z.-]*'

# axe.css — banner comment and the --axe-version custom property.
sed -i -E "s/AXE v$V/AXE v$NEW/"                                   axe.css
sed -i -E "s/(--axe-version: \")$V(\")/\1$NEW\2/"                  axe.css
# calendar.css — banner comment.
sed -i -E "s/AXE CALENDAR v$V/AXE CALENDAR v$NEW/"                 calendar.css
# calendar.js — banner comment and the runtime Calendar.version.
sed -i -E "s/AXE CALENDAR v$V/AXE CALENDAR v$NEW/"                 calendar.js
sed -i -E "s/(Calendar\.version = ')$V(')/\1$NEW\2/"              calendar.js

echo "axe version set to $NEW:"
grep -nE "AXE( CALENDAR)? v[0-9]|--axe-version:|Calendar\.version = '" axe.css calendar.css calendar.js
echo
echo "Next: add a '### $NEW ($(date +%Y-%m-%d))' note to README.md, then commit + tag."
