# Homespring test harness

Runs every example program under each vendored interpreter, compares output
against an editable per-program expected value, and writes one machine-readable
report per adapter under `results/<adapter_id>.json` for the rest of the site
to consume.

## Quick start

```bash
tests/setup.sh        # one-time: install deps, build each interpreter, apply patches
python3 tests/run.py  # run all tests — writes every results/<adapter>.json
```

Filter by program or by interpreter — targeted runs update only the matching
adapter files, and within those files only the matching program rows:

```bash
python3 tests/run.py -k hello            # only programs whose slug contains 'hello'
python3 tests/run.py -a 2005-joe-neeman  # only the OCaml interpreter
python3 tests/run.py -a 2023-james-thistlewood -k hello  # both filters
python3 tests/run.py -v                  # show expected vs actual on failures
```

Results files are merged by `(adapter, program)` — a filtered re-run overwrites
the matching rows inside the relevant adapter file and leaves the rest intact,
so you can iterate on one interpreter without re-running the others. Untouched
adapter files are never modified.

Exit status is non-zero when any test fails or errors; timeouts that happened
to produce the expected prefix still count as pass.

## Layout

```
tests/
├── README.md
├── setup.sh                   # builds all interpreters; idempotent
├── run.py                     # the harness
├── results/                   # one <adapter>.json per interpreter (generated)
├── adapters are defined inline in run.py (one class per interpreter)
├── patches/
│   ├── 2005-joe-neeman.patch  # OCaml 4.07+ fixes + HS_QUIET/HS_TICKS/HS_LIMIT
│   ├── 2003-jeff-binder.patch # don't exit on stdin EOF
│   ├── cal_henderson_driver.pl
│   ├── homespring_js_driver.js
│   ├── martijn_arts_driver.mjs
│   ├── james_thistlewood_driver.js
│   └── addison_bean_driver/   # Rust driver crate (builds via cargo)
└── programs/
    ├── hello-1/
    │   ├── program.hs
    │   └── meta.json
    └── …
```

## Interpreters

Listed in chronological order of release. All are vendored as git submodules
under `interpreters/<id>/`; `id` doubles as both submodule name and adapter
key in meta.json overrides.

| id                         | language      | year | invocation                                                             |
|----------------------------|---------------|------|------------------------------------------------------------------------|
| `2003-jeff-binder`         | Guile Scheme  | 2003 | `guile -e main -s interpreters/…/hs` (patched for stdin EOF)           |
| `2003-cal-henderson`       | Perl          | 2003 | `perl Makefile.PL && make`, then via `patches/cal_henderson_driver.pl` |
| `2005-joe-neeman`          | OCaml         | 2005 | `./configure && make -C src hsrun_opt` (patched for OCaml 4.07+)       |
| `2012-quin-kennedy`        | Node.js       | 2012 | `node homespring.js [-n LIMIT] <file>` (no driver)                     |
| `2017-cal-henderson-js`    | Node.js       | 2017 | `patches/homespring_js_driver.js` (loads `www/homespring.js`)          |
| `2017-addison-bean`        | Rust          | 2017 | `patches/addison_bean_driver/` (cargo-built wrapper around the crate)  |
| `2018-martijn-arts`        | Node.js (ESM) | 2018 | `patches/martijn_arts_driver.mjs`                                      |
| `2023-james-thistlewood`   | Node.js       | 2023 | `patches/james_thistlewood_driver.js` (vm sandbox with stubbed DOM)    |

Benito van der Zander's 2013 `home-river` is a Homespring **program** collection
and a compiler *to* Homespring, not a runtime, so it has no adapter.

### Patches

Both patches are re-applied by `setup.sh` after a fresh `git clean` of the
submodules; the submodule SHAs are not changed.

- **`2005-joe-neeman.patch`** — the 2005 OCaml source uses `String.lowercase`
  (removed in 4.07) and calls `really_input` with a `string` where modern OCaml
  wants `bytes`. The patch also extends `hsrun.ml` to honor `HS_QUIET=1` (skip
  the tree dump), `HS_TICKS=1` (write `TICKS:N` on stderr at exit), and
  `HS_LIMIT=N` (max ticks), and to treat stdin EOF as "no more input" instead
  of raising `End_of_file`.
- **`2003-jeff-binder.patch`** — the original Guile script calls `(exit 0)`
  when stdin reaches EOF, which kills the interpreter before the program has
  a chance to finish. The patch turns that exit into a no-op so the main
  loop keeps running until the program itself terminates.

### Drivers

Interpreters that aren't a straight CLI invocation get a driver under
`patches/` to bridge them to the harness's stdio + env-var contract
(`HS_LIMIT`, `HS_TICKS`).

- **`cal_henderson_driver.pl`** — wraps the Perl module
  (`Language::Homespring`). Drives `tick()` in a loop, counts ticks, honors
  `HS_LIMIT` / `HS_TICKS`.
- **`homespring_js_driver.js`** — loads `www/homespring.js` (the site's own
  interpreter submodule) as a CommonJS module. Steps tick-by-tick via
  `setImmediate` so stdin `data` events get serviced between ticks —
  matters for interactive programs fed by scripted inputs.
- **`martijn_arts_driver.mjs`** — loads Martijn's homespring-js as an ES
  module. His `Runner` exposes `runs` (total phases) and `tickOrder`, so
  the driver divides to get logical ticks. Multiplies `HS_LIMIT` by phase
  count to get an equivalent phase cap.
- **`james_thistlewood_driver.js`** — the 2023 visualizer is a one-file
  browser script (`index.js` + EaselJS canvas). Driver loads it into a
  `vm` sandbox with stubbed `window`/`document`/`createjs`, then shadows
  the three I/O hooks (`homespringOutput`, `homespringGetInput`,
  `homespringError`) by appending replacement function declarations —
  later top-level declarations win at script scope, so we can intercept
  without touching the submodule. Stdin is split by newlines and one line
  per call matches the visualizer's per-step input model.
- **`addison_bean_driver/`** — a small Rust crate that path-deps on
  `interpreters/2017-addison-bean` and runs a minimal tick loop over the
  phases that are actually wired up (Snow, Water, Misc, FishHatch,
  FishDown, FishUp). Power and Input are skipped because their
  `PropagationOrder::Any` hits `unimplemented!()` inside `Node::tick`.
  Most test programs still fail because `move_salmon(Downstream)` holds
  the parent borrow while the tick recursion is already inside it —
  multi-level trees panic on the first FishDown. Built via
  `cargo build --release` in `setup.sh`.

### Tick counts

Where the interpreter exposes it, we report the number of ticks taken
via `TICKS:N` on stderr:

| id                       | tick count source                                      |
|--------------------------|--------------------------------------------------------|
| `2003-jeff-binder`       | **not reported** — Guile script has no counter         |
| `2003-cal-henderson`     | driver counts loop iterations                          |
| `2005-joe-neeman`        | patched `hsrun.ml` emits `TICKS:N` when `HS_TICKS=1`   |
| `2012-quin-kennedy`      | **not reported** — CLI has no counter                  |
| `2017-cal-henderson-js`  | driver counts via `p.tickNum`                          |
| `2017-addison-bean`      | driver counts loop iterations                          |
| `2018-martijn-arts`      | driver reports `runs / tickOrder.length`               |
| `2023-james-thistlewood` | driver counts loop iterations                          |

`actual_ticks` is `null` for the two interpreters that don't report.

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
- `tick_limit` is honored by every adapter except Jeff Binder's Guile
  (Joe Neeman, Cal Perl, homespring.js, Martijn, James, and Addison read
  `HS_LIMIT`; Quin uses `-n`). For Jeff's interpreter rely on `timeout`
  to bound runtime.

## Adding an interpreter

1. Vendor the source (usually as a git submodule under
   `interpreters/<year>-<name>/`).
2. Add any one-time build step to `tests/setup.sh` (keep it idempotent).
3. Subclass `Adapter` in `tests/run.py` and register it in the `ADAPTERS`
   list. You need:
   - a stable `id` and human `label`,
   - an `is_available()` that returns `(bool, reason)`,
   - a `run()` that invokes the interpreter and returns a `RunResult`.
4. If output needs post-processing or the interpreter has no direct CLI, add
   a driver script under `tests/patches/` and invoke it from the adapter.

## Report format

Each `results/<adapter>.json` is the source of truth for that interpreter's
cells in the site's compatibility matrix. `render_table.py` concatenates
them all. Shape:

```jsonc
{
  "generated_at": 1713398400,
  "adapter":   {"id": "2005-joe-neeman", "label": "…", "available": true, "reason": "ok"},
  "programs":  [ {"slug": "hello-1", "description": "…", "source": "tests/programs/hello-1/program.hs"}, … ],
  "results":   [ {"program": "hello-1", "adapter": "2005-joe-neeman", "status": "pass|fail|error|timeout|skip",
                  "expected_output": "…", "actual_output": "…",
                  "expected_ticks": 7, "actual_ticks": 7,
                  "notes": [], "wall_time_ms": 123}, … ],
  "summary":   { "pass": 44, "fail": 0, "error": 0, "timeout": 0, "skip": 11 }
}
```
