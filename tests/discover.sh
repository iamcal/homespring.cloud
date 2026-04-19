#!/usr/bin/env bash
# Run every new program under every interpreter and print a compact
# comparison. Used to figure out expected_output values for new meta.json
# files. Not part of the regular harness.

set -u

REPO="$(cd "$(dirname "$0")/.." && pwd)"
HSRUN="$REPO/interpreters/2005-joe-neeman/src/hsrun_opt"
JEFF="$REPO/interpreters/2003-jeff-binder/hs"
QUIN="$REPO/interpreters/2012-quin-kennedy/homespring.js"
PERL_DRV="$REPO/tests/patches/cal_henderson_driver.pl"
JS_DRV="$REPO/tests/patches/homespring_js_driver.js"

LIMIT="${HS_LIMIT:-40}"
TO="${TO:-4}"
STDIN_TEXT="${STDIN:-hello}"

run_one() {
    local name="$1" cmd="$2"
    local out
    out="$(eval "$cmd" 2>&1 | head -c 160)"
    if [ -z "$out" ]; then
        printf '  %-10s (empty)\n' "$name:"
    else
        # compact: replace newlines with \n
        printf '  %-10s %s\n' "$name:" "$(printf '%s' "$out" | tr '\n' '|' | sed 's/|/\\n/g')"
    fi
}

progs=("$@")
if [ ${#progs[@]} -eq 0 ]; then
    # default: every tests/programs/<slug>/ that exists
    while IFS= read -r d; do
        progs+=("$(basename "$d")")
    done < <(find "$REPO/tests/programs" -mindepth 1 -maxdepth 1 -type d | sort)
fi

for slug in "${progs[@]}"; do
    f="$REPO/tests/programs/$slug/program.hs"
    [ -f "$f" ] || { echo "$slug: missing"; continue; }
    echo "=== $slug ==="
    run_one "ocaml"  "printf '%s\n' '$STDIN_TEXT' | HS_QUIET=1 HS_LIMIT=$LIMIT timeout $TO '$HSRUN' '$f'"
    run_one "jeff"   "(printf '%s\n' '$STDIN_TEXT'; sleep $TO) | timeout $TO '$JEFF' '$f'"
    run_one "perl"   "printf '%s\n' '$STDIN_TEXT' | HS_LIMIT=$LIMIT timeout $TO perl '$PERL_DRV' '$f'"
    run_one "quin"   "printf '%s\n' '$STDIN_TEXT' | timeout $TO node '$QUIN' -n $LIMIT '$f'"
    run_one "js"     "printf '%s\n' '$STDIN_TEXT' | HS_LIMIT=$LIMIT timeout $TO node '$JS_DRV' '$f'"
done
