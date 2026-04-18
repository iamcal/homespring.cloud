# Homespring test harness

Runs every example program under each vendored interpreter, compares output
against an editable per-program expected value, and writes a machine-readable
report to `results.json` for the rest of the site to consume.

## Quick start

```bash
tests/setup.sh        # one-time: install deps, build each interpreter, apply patches
python3 tests/run.py  # run all tests
```

Filter by program or by interpreter:

```bash
python3 tests/run.py -k hello           # only programs whose slug contains 'hello'
python3 tests/run.py -a 2005-joe-neeman # only the OCaml interpreter
python3 tests/run.py -v                 # show expected vs actual on failures
```

Exit status is non-zero when any test fails or errors; timeouts that happened
to produce the expected prefix still count as pass.

## Layout

```
tests/
├── README.md
├── setup.sh                   # builds all interpreters; idempotent
├── run.py                     # the harness
├── results.json               # latest report (generated)
├── adapters are defined inline in run.py (one class per interpreter)
├── patches/
│   ├── 2005-joe-neeman.patch  # OCaml 4.07+ fixes + HS_QUIET/HS_TICKS/HS_LIMIT
│   ├── 2003-jeff-binder.patch # don't exit on stdin EOF
│   ├── cal_henderson_driver.pl
│   └── homespring_js_driver.js
└── programs/
    ├── hello-1/
    │   ├── program.hs
    │   └── meta.json
    └── …
```

## Interpreters

| id                       | language    | year | build step                                                   |
|--------------------------|-------------|------|--------------------------------------------------------------|
| `2005-joe-neeman`        | OCaml       | 2005 | `./configure && make -C src hsrun_opt` (patched)             |
| `2003-jeff-binder`       | Guile Scheme| 2003 | none — invoked as `guile -e main -s www/interpreters/…/hs`   |
| `2003-cal-henderson`     | Perl        | 2003 | `perl Makefile.PL && make` — used via `perl -Mblib`          |
| `2012-quin-kennedy`      | Node.js     | 2012 | none — `node homespring.js [-n LIMIT] <file>`                |
| `2017-cal-henderson-js`  | Node.js     | 2017 | none — driver in `patches/homespring_js_driver.js`           |

Benito van der Zander's 2013 `home-river` is a Homespring **program** collection
and a compiler *to* Homespring, not a runtime, so it has no adapter.

### Why the patches

- **Joe Neeman (OCaml)**: the 2005 source uses `String.lowercase` (removed in
  4.07) and calls `really_input` with a `string` where modern OCaml wants
  `bytes`. We also extend `hsrun.ml` to honor `HS_QUIET=1` (skip the tree
  dump), `HS_TICKS=1` (write `TICKS:N` on stderr at exit), and `HS_LIMIT=N`
  (max ticks), and to treat stdin EOF as "no more input" instead of raising
  `End_of_file`.
- **Jeff Binder (Guile)**: the original script calls `(exit 0)` when stdin
  reaches EOF, which kills the interpreter before the program has a chance to
  finish. The patch turns that exit into a no-op so the main loop keeps
  running until the program itself terminates.

Both patches are re-applied by `setup.sh` after a fresh `git clean` of the
submodules; the submodule shas are not changed.

### Tick counts

Where the interpreter exposes it, we report the number of ticks taken. Joe
Neeman (via `HS_TICKS=1`), Cal Henderson's Perl module (driver counts in a
loop), and homespring.js (driver counts via `onTickEnd`) all report. Jeff
Binder's Guile and Quin Kennedy's Node.js do not expose a tick counter, so
their `actual_ticks` field is `null`.

## Adding a program

Drop a single file `tests/programs/<slug>.json` that points at an
existing source under `www/examples/` — no per-test copy is kept.

```jsonc
{
  "description": "one-line summary",
  "source": "www/examples/<author>/<file>.hs",   // repo-root relative
  "input": "",                   // what to pipe to stdin
  "tick_limit": 50,              // optional; cap per interpreter
  "timeout": 5,                  // wall-clock seconds before we kill the run
  "expected_output": "Hello!",   // default expected output
  "expected_ticks": 7,           // optional; null to skip
  "normalize": "prefix",         // exact | prefix | strip | default (rstrip)
  "overrides": {
    "2005-joe-neeman": {
      "expected_output": "different output for this interpreter",
      "expected_ticks": 8,
      "skip": false,
      "skip_reason": "…"
    }
  }
}
```

Defaults:
- `normalize` is trailing-whitespace tolerant. Use `"exact"` to force a
  byte-for-byte match, `"prefix"` for infinite/repeating programs, `"strip"`
  to ignore all surrounding whitespace.
- `tick_limit` is honored by interpreters that support it (Joe Neeman via
  `HS_LIMIT`, Cal Henderson via `HS_LIMIT`, Quin Kennedy via `-n`, homespring.js
  via `HS_LIMIT`). Jeff Binder ignores it; rely on `timeout` to bound runtime.

## Adding an interpreter

1. Vendor the source (usually as a git submodule under
   `www/interpreters/<year>-<name>/`).
2. Add any one-time build step to `tests/setup.sh` (keep it idempotent).
3. Subclass `Adapter` in `tests/run.py` and register it in the `ADAPTERS`
   list. You need:
   - a stable `id` and human `label`,
   - an `is_available()` that returns `(bool, reason)`,
   - a `run()` that invokes the interpreter and returns a `RunResult`.
4. If output needs post-processing or the interpreter has no direct CLI, add
   a driver script under `tests/patches/` and invoke it from the adapter.

## Report format

`results.json` is the source of truth for downstream consumers (e.g. pages
on the site that show an interpreter-compatibility matrix). Shape:

```jsonc
{
  "generated_at": 1713398400,
  "adapters":  [ {"id": "2005-joe-neeman", "label": "…", "available": true, "reason": "ok"}, … ],
  "programs":  [ {"slug": "hello-1", "description": "…", "source": "tests/programs/hello-1/program.hs"}, … ],
  "results":   [ {"program": "hello-1", "adapter": "…", "status": "pass|fail|error|timeout|skip",
                  "expected_output": "…", "actual_output": "…",
                  "expected_ticks": 7, "actual_ticks": 7,
                  "notes": [], "wall_time_ms": 123}, … ],
  "summary":   { "pass": 44, "fail": 0, "error": 0, "timeout": 0, "skip": 11 }
}
```
