#!/usr/bin/env python3
"""Splice the rendered tbody (from render_table.py) into interpreters.php,
replacing whatever currently sits between <tbody> and </tbody>.

Typical usage:
    python3 tests/render_table.py > /tmp/tbody.html
    python3 tests/splice.py
"""
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
target = ROOT / 'www' / 'interpreters.php'
body_path = Path('/tmp/tbody.html')

lines = target.read_text().splitlines(keepends=True)
start = next(i for i, l in enumerate(lines) if l.strip() == '<tbody>')
end = next(i for i, l in enumerate(lines) if l.strip() == '</tbody>')
body = body_path.read_text()

target.write_text(
    ''.join(lines[:start + 1]) + '\n' + body + '\n' + ''.join(lines[end:])
)
