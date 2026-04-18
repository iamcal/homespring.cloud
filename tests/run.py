#!/usr/bin/env python3
"""
Homespring test harness.

Runs every program in tests/programs/ under every registered interpreter
adapter, compares output against the expected output declared in each
program's meta.json, and writes a machine-readable report to
tests/results.json.

See tests/README.md for details on adding programs and interpreters.
"""
from __future__ import annotations

import argparse
import json
import os
import re
import shutil
import subprocess
import sys
import time
from dataclasses import dataclass, field, asdict
from pathlib import Path
from typing import Any

ROOT = Path(__file__).resolve().parent
REPO = ROOT.parent
INTERP_DIR = REPO / "www" / "interpreters"


# ---------------------------------------------------------------------------
# Adapters
# ---------------------------------------------------------------------------

@dataclass
class RunResult:
    stdout: str = ""
    stderr: str = ""
    exit_code: int = 0
    ticks: int | None = None
    timed_out: bool = False
    wall_time_ms: int = 0
    error: str | None = None


class Adapter:
    """Base class for interpreter adapters."""

    # Identifier used in meta.json overrides and in the report.
    id: str = ""
    # Human-readable label for reports.
    label: str = ""

    def is_available(self) -> tuple[bool, str]:
        """Return (available, reason)."""
        return True, "ok"

    def run(self, program_path: Path, stdin: str, timeout_s: float,
            tick_limit: int | None) -> RunResult:
        raise NotImplementedError

    # Utilities used by subclasses.
    def _exec(self, cmd: list[str], stdin: str, timeout_s: float,
              env: dict[str, str] | None = None) -> RunResult:
        started = time.monotonic()
        env_full = os.environ.copy()
        if env:
            env_full.update(env)
        try:
            proc = subprocess.run(
                cmd,
                input=stdin,
                capture_output=True,
                text=True,
                timeout=timeout_s,
                env=env_full,
            )
        except subprocess.TimeoutExpired as e:
            def _decode(v: Any) -> str:
                if v is None:
                    return ""
                if isinstance(v, bytes):
                    return v.decode("utf-8", errors="replace")
                return v
            return RunResult(
                stdout=_decode(e.stdout),
                stderr=_decode(e.stderr),
                exit_code=-1,
                timed_out=True,
                wall_time_ms=int((time.monotonic() - started) * 1000),
            )
        except FileNotFoundError as e:
            return RunResult(exit_code=-1, error=str(e))
        elapsed_ms = int((time.monotonic() - started) * 1000)
        ticks = _parse_ticks(proc.stderr)
        return RunResult(
            stdout=proc.stdout,
            stderr=proc.stderr,
            exit_code=proc.returncode,
            ticks=ticks,
            wall_time_ms=elapsed_ms,
        )


_TICKS_RX = re.compile(r"^TICKS:(\d+)$", re.MULTILINE)


def _parse_ticks(stderr: str) -> int | None:
    m = _TICKS_RX.search(stderr or "")
    return int(m.group(1)) if m else None


class JoeNeemanAdapter(Adapter):
    id = "2005-joe-neeman"
    label = "Joe Neeman (OCaml, 2005)"

    @property
    def binary(self) -> Path:
        return INTERP_DIR / self.id / "src" / "hsrun_opt"

    def is_available(self) -> tuple[bool, str]:
        if not self.binary.exists():
            return False, f"missing {self.binary} — run setup.sh"
        return True, "ok"

    def run(self, program_path, stdin, timeout_s, tick_limit):
        env = {"HS_QUIET": "1", "HS_TICKS": "1"}
        if tick_limit:
            env["HS_LIMIT"] = str(tick_limit)
        return self._exec([str(self.binary), str(program_path)], stdin,
                          timeout_s, env)


class JeffBinderAdapter(Adapter):
    id = "2003-jeff-binder"
    label = "Jeff Binder (Guile Scheme, 2003)"

    @property
    def script(self) -> Path:
        return INTERP_DIR / self.id / "hs"

    def is_available(self) -> tuple[bool, str]:
        if shutil.which("guile") is None:
            return False, "guile not in PATH"
        if not self.script.exists():
            return False, f"missing {self.script}"
        return True, "ok"

    def run(self, program_path, stdin, timeout_s, tick_limit):
        # Jeff's interpreter doesn't expose tick counts. It also reads stdin
        # with (char-ready?) + read-line, so once the input pipe closes it
        # stops consuming — which breaks programs like add.hs that expect
        # successive lines to arrive while the tree is running. We hold the
        # stdin pipe open for the full timeout by piping the payload through
        # a subshell that then sleeps, and use GNU timeout(1) to kill the
        # interpreter at the end.
        inner_timeout = max(timeout_s - 0.5, 0.5)
        script = (
            f'printf %s "$1"; sleep {timeout_s + 1}'
        )
        cmd = [
            "bash", "-c",
            (f'({script}) | '
             f'timeout -k 0.1 {inner_timeout:.2f} '
             f'guile -e main -s "$2" "$3"'),
            "--",
            stdin,
            str(self.script),
            str(program_path),
        ]
        return self._exec(cmd, "", timeout_s + 2)


class CalHendersonAdapter(Adapter):
    id = "2003-cal-henderson"
    label = "Cal Henderson (Perl, 2003)"

    @property
    def blib(self) -> Path:
        return INTERP_DIR / self.id / "blib" / "lib" / "Language" / "Homespring.pm"

    def is_available(self) -> tuple[bool, str]:
        if shutil.which("perl") is None:
            return False, "perl not in PATH"
        if not self.blib.exists():
            return False, f"module not built — run setup.sh"
        return True, "ok"

    def run(self, program_path, stdin, timeout_s, tick_limit):
        env = {"HS_TICKS": "1"}
        if tick_limit:
            env["HS_LIMIT"] = str(tick_limit)
        driver = ROOT / "patches" / "cal_henderson_driver.pl"
        return self._exec(["perl", str(driver), str(program_path)],
                          stdin, timeout_s, env)


class QuinKennedyAdapter(Adapter):
    id = "2012-quin-kennedy"
    label = "Quin Kennedy (Node.js, 2012)"

    @property
    def script(self) -> Path:
        return INTERP_DIR / self.id / "homespring.js"

    def is_available(self) -> tuple[bool, str]:
        if shutil.which("node") is None:
            return False, "node not in PATH"
        if not self.script.exists():
            return False, f"missing {self.script}"
        return True, "ok"

    def run(self, program_path, stdin, timeout_s, tick_limit):
        # Quin's interpreter: `node homespring.js [-n LIMIT] <filename>`
        args = ["node", str(self.script)]
        if tick_limit:
            args += ["-n", str(tick_limit)]
        args.append(str(program_path))
        return self._exec(args, stdin, timeout_s)


class HomespringJsAdapter(Adapter):
    id = "2017-cal-henderson-js"
    label = "Cal Henderson (homespring.js, 2017)"

    @property
    def lib(self) -> Path:
        return REPO / "www" / "homespring.js" / "lib" / "homespring.js"

    def is_available(self) -> tuple[bool, str]:
        if shutil.which("node") is None:
            return False, "node not in PATH"
        if not self.lib.exists():
            return False, f"missing {self.lib} — submodule not initialized"
        return True, "ok"

    def run(self, program_path, stdin, timeout_s, tick_limit):
        env = {"HS_TICKS": "1"}
        if tick_limit:
            env["HS_LIMIT"] = str(tick_limit)
        driver = ROOT / "patches" / "homespring_js_driver.js"
        return self._exec(["node", str(driver), str(program_path)], stdin,
                          timeout_s, env)


ADAPTERS: list[Adapter] = [
    JoeNeemanAdapter(),
    JeffBinderAdapter(),
    CalHendersonAdapter(),
    QuinKennedyAdapter(),
    HomespringJsAdapter(),
]


# ---------------------------------------------------------------------------
# Programs
# ---------------------------------------------------------------------------

@dataclass
class Program:
    slug: str
    dir: Path
    source: Path
    meta: dict

    @property
    def description(self) -> str:
        return self.meta.get("description", "")

    @property
    def stdin(self) -> str:
        return self.meta.get("input", "")

    @property
    def timeout(self) -> float:
        return float(self.meta.get("timeout", 5.0))

    @property
    def tick_limit(self) -> int | None:
        v = self.meta.get("tick_limit")
        return int(v) if v else None

    @property
    def expected_output(self) -> str:
        return self.meta.get("expected_output", "")

    @property
    def expected_ticks(self) -> int | None:
        v = self.meta.get("expected_ticks")
        return int(v) if v is not None else None

    @property
    def skip(self) -> list[str]:
        return list(self.meta.get("skip", []))

    def override_for(self, adapter_id: str) -> dict:
        return self.meta.get("overrides", {}).get(adapter_id, {})


def load_programs(programs_dir: Path) -> list[Program]:
    programs = []
    for meta_path in sorted(programs_dir.glob("*.json")):
        slug = meta_path.stem
        meta = json.loads(meta_path.read_text())
        source_name = meta["source"]
        # "source" is resolved relative to the repo root so that every program
        # can point directly at www/examples/<author>/… without needing a
        # local copy under tests/programs/.
        source = (REPO / source_name).resolve()
        if not source.exists():
            print(f"skipping {slug}: source {source} missing", file=sys.stderr)
            continue
        programs.append(Program(slug=slug, dir=meta_path.parent, source=source,
                                meta=meta))
    return programs


# ---------------------------------------------------------------------------
# Running
# ---------------------------------------------------------------------------

@dataclass
class TestOutcome:
    program: str
    adapter: str
    status: str  # pass | fail | error | skip | timeout
    expected_output: str
    actual_output: str
    expected_ticks: int | None
    actual_ticks: int | None
    notes: list[str] = field(default_factory=list)
    wall_time_ms: int = 0
    # Authors are trusted to run their own examples correctly even when the
    # harness can't easily test it (e.g. Jeff's interactive programs that
    # need a real terminal). Cells flagged with presumed_pass render as a
    # qualified "yes" in the HTML table with the skip_reason as a tooltip.
    presumed_pass: bool = False


def compare(expected: str, actual: str, normalize: str | None) -> bool:
    # Default: byte-for-byte. Any extra output — stray newlines, stray
    # tokens — counts as a failure unless the meta.json explicitly
    # spells it out as a per-adapter expected_output override.
    # normalize: "strip"  — ignore leading/trailing whitespace.
    # normalize: "prefix" — actual must *start with* expected; use only
    # for genuinely infinite programs whose termination point is
    # arbitrary (e.g. jeff-first).
    if normalize == "strip":
        return expected.strip() == actual.strip()
    if normalize == "prefix":
        return actual.startswith(expected)
    return expected == actual


def apply_extra_newlines(canonical: str, positions: list[int]) -> str:
    """Return [canonical] with a '\\n' inserted at each position in the array.
    Positions index the original canonical string; applied left-to-right so
    the positions refer to canonical offsets, not modified-string offsets."""
    out = []
    last = 0
    for p in sorted(positions):
        out.append(canonical[last:p])
        out.append("\n")
        last = p
    out.append(canonical[last:])
    return "".join(out)


def run_one(program: Program, adapter: Adapter) -> TestOutcome:
    override = program.override_for(adapter.id)
    expected = override.get("expected_output", program.expected_output)
    # Quin Kennedy's interpreter appends newlines in places where the other
    # interpreters don't. Instead of writing out the whole divergent output,
    # the meta.json can list the character positions (in the canonical string)
    # at which extra newlines should be inserted.
    extra_nls = override.get("extra_newlines")
    if extra_nls:
        expected = apply_extra_newlines(expected, extra_nls)
    expected_ticks = override.get("expected_ticks", program.expected_ticks)
    normalize = override.get("normalize", program.meta.get("normalize"))

    if adapter.id in program.skip or override.get("skip"):
        return TestOutcome(
            program=program.slug, adapter=adapter.id, status="skip",
            expected_output=expected, actual_output="",
            expected_ticks=expected_ticks, actual_ticks=None,
            notes=[override.get("skip_reason",
                               program.meta.get("skip_reason", "skipped"))],
            presumed_pass=bool(override.get("presumed_pass")),
        )

    available, reason = adapter.is_available()
    if not available:
        return TestOutcome(
            program=program.slug, adapter=adapter.id, status="error",
            expected_output=expected, actual_output="",
            expected_ticks=expected_ticks, actual_ticks=None,
            notes=[f"unavailable: {reason}"],
        )

    tick_limit = override.get("tick_limit", program.tick_limit)
    timeout_s = float(override.get("timeout", program.timeout))
    result = adapter.run(program.source, program.stdin, timeout_s, tick_limit)

    notes = []
    matched = compare(expected, result.stdout, normalize)
    # Surface any human-authored explanation from the per-adapter override so
    # that downstream consumers (e.g. the HTML renderer) can use it as a tooltip.
    override_note = override.get("notes")
    if override_note:
        notes.append(override_note)
    if result.timed_out:
        notes.append(f"timed out after {timeout_s}s")
    if result.error:
        notes.append(f"error: {result.error}")

    status = "pass" if matched else "fail"
    if result.timed_out and not matched:
        status = "timeout"
    elif result.timed_out:
        notes.append("matched partial output before timeout")
    if result.error:
        status = "error"

    if (expected_ticks is not None and result.ticks is not None
            and expected_ticks != result.ticks):
        notes.append(
            f"tick mismatch: expected {expected_ticks}, got {result.ticks}")
        if status == "pass":
            status = "fail"

    return TestOutcome(
        program=program.slug, adapter=adapter.id, status=status,
        expected_output=expected, actual_output=result.stdout,
        expected_ticks=expected_ticks, actual_ticks=result.ticks,
        notes=notes, wall_time_ms=result.wall_time_ms,
    )


STATUS_STYLES = {
    "pass": ("\033[32m", "PASS"),
    "fail": ("\033[31m", "FAIL"),
    "error": ("\033[33m", "ERR "),
    "timeout": ("\033[33m", "TIME"),
    "skip": ("\033[90m", "SKIP"),
}


def format_status(outcome: TestOutcome, color: bool) -> str:
    sty, label = STATUS_STYLES.get(outcome.status, ("", outcome.status))
    if color:
        return f"{sty}{label}\033[0m"
    return label


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--programs", default=str(ROOT / "programs"),
                        help="programs directory")
    parser.add_argument("--filter", "-k", default=None,
                        help="only run programs matching substring")
    parser.add_argument("--adapter", "-a", default=None,
                        help="only run this adapter id")
    parser.add_argument("--json", default=str(ROOT / "results.json"),
                        help="path to write JSON report")
    parser.add_argument("--no-color", action="store_true")
    parser.add_argument("--verbose", "-v", action="store_true")
    args = parser.parse_args()

    color = not args.no_color and sys.stdout.isatty()
    programs = load_programs(Path(args.programs))
    if args.filter:
        programs = [p for p in programs if args.filter in p.slug]

    adapters = ADAPTERS
    if args.adapter:
        adapters = [a for a in ADAPTERS if a.id == args.adapter]

    outcomes: list[TestOutcome] = []
    counts = {"pass": 0, "fail": 0, "error": 0, "timeout": 0, "skip": 0}

    for program in programs:
        print(f"\n{program.slug}  ({program.description or 'no description'})")
        for adapter in adapters:
            outcome = run_one(program, adapter)
            outcomes.append(outcome)
            counts[outcome.status] = counts.get(outcome.status, 0) + 1
            status = format_status(outcome, color)
            ticks = (f" ticks={outcome.actual_ticks}"
                     if outcome.actual_ticks is not None else "")
            notes = f"  [{'; '.join(outcome.notes)}]" if outcome.notes else ""
            print(f"  {status}  {adapter.label:42}{ticks}{notes}")
            if args.verbose and outcome.status in ("fail", "timeout"):
                print(f"    expected: {outcome.expected_output!r}")
                print(f"    actual:   {outcome.actual_output!r}")

    total = sum(counts.values())
    print("\n" + "=" * 60)
    print(f"total: {total}  pass: {counts['pass']}  fail: {counts['fail']}  "
          f"error: {counts['error']}  timeout: {counts['timeout']}  "
          f"skip: {counts['skip']}")

    report = {
        "generated_at": int(time.time()),
        "adapters": [{"id": a.id, "label": a.label,
                      "available": a.is_available()[0],
                      "reason": a.is_available()[1]} for a in ADAPTERS],
        "programs": [{"slug": p.slug, "description": p.description,
                      "source": str(p.source.relative_to(REPO))}
                     for p in programs],
        "results": [asdict(o) for o in outcomes],
        "summary": counts,
    }
    Path(args.json).write_text(json.dumps(report, indent=2) + "\n")
    print(f"\nwrote {args.json}")
    return 0 if counts["fail"] == 0 and counts["error"] == 0 else 1


if __name__ == "__main__":
    sys.exit(main())
