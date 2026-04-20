#!/usr/bin/env node
// Driver for Cal Henderson's homespring.js that reports tick count.
// Usage: node homespring_js_driver.js <program.hs>
// Env: HS_LIMIT=N (optional tick cap), HS_TICKS=1 (emit TICKS:N on stderr).
//
// We step tick-by-tick with setImmediate between ticks so stdin 'data'
// events can be processed. A synchronous p.run() loop would starve them,
// which breaks scripted inputs that arrive after the program has started.

const fs = require('fs');
const path = require('path');

const hsPath = process.env.HS_JS_PATH ||
    path.resolve(__dirname, '../../www/homespring.js/lib/homespring.js');
const HS = require(hsPath);

const limit = parseInt(process.env.HS_LIMIT || '0', 10) || 0;
const reportTicks = process.env.HS_TICKS === '1';

const file = process.argv[2];
if (!file) {
    console.error('usage: homespring_js_driver.js <program.hs>');
    process.exit(2);
}

const source = fs.readFileSync(file, 'utf8');
const p = new HS.Program(source, { strictmode: false });

p.onOutput = (s) => process.stdout.write(s);

process.stdin.setEncoding('utf8');
process.stdin.on('data', (chunk) => {
    const s = chunk.toString();
    const line = s.endsWith('\n') ? s.slice(0, -1) : s;
    if (line.length > 0) p.input = line;
});
process.stdin.on('end', () => {});

let done = false;
function finish() {
    if (done) return;
    done = true;
    if (reportTicks) process.stderr.write('TICKS:' + p.tickNum + '\n');
    process.exit(0);
}

function loop() {
    if (done) return;
    if (p.terminated) { finish(); return; }
    if (limit && p.tickNum >= limit) { finish(); return; }
    p.tick();
    setImmediate(loop);
}
setImmediate(loop);
