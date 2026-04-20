#!/usr/bin/env bash
# tests/hs.sh — run a Homespring program through one of the vendored
# interpreters. Convenience wrapper so there's one allow-listed command
# pattern covering every interpreter instead of N per-path entries.
#
# Usage: tests/hs.sh <interp> <file> [extra-args...]
#
# interp: joe | cal | js | james | martijn | quin | jeff | addison
# file:   path to a .hs program (relative to repo root is fine)
#
# Env vars are passed through; we set conservative defaults for the
# common ones:
#   HS_QUIET=1   (default; set to 0 to get tree dumps from OCaml)
#   HS_LIMIT=30  (default; override per-invocation as needed)
#   HS_TICKS     (unset; pass HS_TICKS=1 to get TICKS:N on stderr)
set -euo pipefail

interp="${1:-}"
file="${2:-}"
shift 2 2>/dev/null || true

if [[ -z "$interp" || -z "$file" ]]; then
    cat >&2 <<EOF
usage: $0 <joe|cal|js|james|martijn|quin|jeff|addison> <file.hs> [extra-args]

  joe      — Joe Neeman, OCaml (2005) — interpreters/2005-joe-neeman
  cal      — Cal Henderson, Perl (2003)
  js       — Cal Henderson, homespring.js (2017) — the site's interpreter
  james    — James Thistlewood, browser visualizer (2023)
  martijn  — Martijn Arts, ESM (2018)
  quin     — Quin Kennedy, Node.js (2012)
  jeff     — Jeff Binder, Guile Scheme (2003)
  addison  — Addison Bean, Rust (2017)
EOF
    exit 2
fi

export HS_QUIET="${HS_QUIET:-1}"
export HS_LIMIT="${HS_LIMIT:-30}"

cd /mnt/webroot/homespring.cloud

case "$interp" in
    joe|ocaml)    exec interpreters/2005-joe-neeman/src/hsrun_opt "$file" "$@" ;;
    cal|perl)     exec perl tests/patches/cal_henderson_driver.pl "$file" "$@" ;;
    js|hsjs)      exec node tests/patches/homespring_js_driver.js "$file" "$@" ;;
    james|jt)     exec node tests/patches/james_thistlewood_driver.js "$file" "$@" ;;
    martijn|ma)   exec node tests/patches/martijn_arts_driver.mjs "$file" "$@" ;;
    quin|qk)      exec node interpreters/2012-quin-kennedy/homespring.js "$file" "$@" ;;
    jeff|guile)   exec guile -e main -s interpreters/2003-jeff-binder/hs "$file" "$@" ;;
    addison|rust) exec tests/patches/addison_bean_driver/target/release/addison_bean_driver "$file" "$@" ;;
    *)
        echo "unknown interpreter: $interp" >&2
        echo "pick one of: joe cal js james martijn quin jeff addison" >&2
        exit 2
        ;;
esac
