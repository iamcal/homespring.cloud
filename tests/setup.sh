#!/usr/bin/env bash
# Builds every interpreter so the test harness can run them.
# Idempotent — safe to run repeatedly.

set -euo pipefail

here="$(cd "$(dirname "$0")" && pwd)"
repo="$(cd "$here/.." && pwd)"
interp="$repo/interpreters"

log() { printf '\033[36m==>\033[0m %s\n' "$*"; }
warn() { printf '\033[33m[warn]\033[0m %s\n' "$*"; }

# ---- system packages ------------------------------------------------------

need_apt=()
command -v ocamlopt  >/dev/null || need_apt+=("ocaml")
command -v guile     >/dev/null || need_apt+=("guile-3.0")
command -v perl      >/dev/null || need_apt+=("perl")
command -v node      >/dev/null || warn "node is not installed; homespring.js and Quin's interpreter will be unavailable"
command -v cargo     >/dev/null || warn "cargo is not installed; Addison Bean's Rust interpreter will be unavailable"

if [ ${#need_apt[@]} -gt 0 ]; then
    log "installing system packages: ${need_apt[*]}"
    sudo apt-get update
    sudo apt-get install -y "${need_apt[@]}"
fi

# Applies $here/patches/$1.patch inside the submodule $2 if it's not already
# applied. Uses `git apply --check` as the source of truth.
apply_patch() {
    local patch="$here/patches/$1.patch"
    local dir="$2"
    ( cd "$dir"
      if git apply --check --whitespace=nowarn "$patch" >/dev/null 2>&1; then
          log "  applying $(basename "$patch")"
          git apply --whitespace=nowarn "$patch"
      elif git apply --check --whitespace=nowarn --reverse "$patch" >/dev/null 2>&1; then
          : # already applied
      else
          warn "  could not apply or verify $(basename "$patch") against $dir"
      fi
    )
}

# ---- Joe Neeman (OCaml, 2005) --------------------------------------------
# Patch fixes OCaml >= 4.07 build errors, adds HS_QUIET/HS_TICKS/HS_LIMIT
# env-var controls, and handles EOF on stdin gracefully.

joe="$interp/2005-joe-neeman"
log "building Joe Neeman's OCaml interpreter"
apply_patch 2005-joe-neeman "$joe"
(
    cd "$joe"
    GTKLIBS=" " OCAMLFLAGS=" " ./configure >/dev/null
    (cd src && make hsrun_opt) >/dev/null
)
log "  built $joe/src/hsrun_opt"

# ---- Jeff Binder (Guile Scheme, 2003) ------------------------------------
# Needs execute bit; patched so EOF on stdin does not cause the interpreter
# to exit early.

jeff="$interp/2003-jeff-binder"
log "preparing Jeff Binder's Guile interpreter"
chmod +x "$jeff/hs"
apply_patch 2003-jeff-binder "$jeff"
log "  ready"

# ---- Cal Henderson (Perl, 2003) ------------------------------------------

cal="$interp/2003-cal-henderson"
log "building Cal Henderson's Perl module"
(
    cd "$cal"
    [ -f Makefile ] || perl Makefile.PL >/dev/null
    make >/dev/null
)
log "  built $cal/blib/"

# ---- Quin Kennedy (Node.js, 2012) ----------------------------------------
# Nothing to build; the script is loaded directly by node at runtime.

log "Quin Kennedy's Node.js interpreter needs no build step"

# ---- Cal Henderson (homespring.js, 2017) ---------------------------------
# Nothing to build; the library is loaded directly by our driver.

log "homespring.js needs no build step"

# ---- Martijn Arts (JavaScript, 2018) -------------------------------------
# Nothing to build; loaded as an ES module by the .mjs driver.

log "Martijn Arts's homespring-js needs no build step"

# ---- Addison Bean (Rust, 2017) -------------------------------------------
# The source compiles cleanly but is an unfinished WIP — Program::execute is
# still `unimplemented!()` for River programs and main.rs is a hardcoded
# scratchpad with no file-based CLI, so the harness flags it unavailable.
# We still build it so a future driver has a compiled lib to link against.

addison="$interp/2017-addison-bean"
if command -v cargo >/dev/null; then
    log "building Addison Bean's Rust interpreter"
    (cd "$addison" && cargo build --release >/dev/null)
    log "  built $addison/target/release/homespring"
else
    warn "skipping Addison Bean's Rust build (cargo missing)"
fi

log "all interpreters ready. Run  \`python3 $here/run.py\`  to execute the tests."
