#!/usr/bin/env node
// Driver for Cal Henderson's homespring.js that reports tick count.
// Usage: node homespring_js_driver.js <program.hs>
// Env: HS_LIMIT=N (optional tick cap)
// Writes program output to stdout and "TICKS:N" to stderr at end.

const fs = require('fs');
const path = require('path');

const hsPath = process.env.HS_JS_PATH ||
    path.resolve(__dirname, '../../www/homespring.js/lib/homespring.js');
const HS = require(hsPath);

const limit = parseInt(process.env.HS_LIMIT || '0', 10) || undefined;
const reportTicks = process.env.HS_TICKS === '1';

const file = process.argv[2];
if (!file) {
    console.error('usage: homespring_js_driver.js <program.hs>');
    process.exit(2);
}

const source = fs.readFileSync(file, 'utf8');
const p = new HS.Program(source, { strictmode: false });

let ticks = 0;
p.onOutput = (s) => process.stdout.write(s);
p.onTerminate = () => {
    if (reportTicks) process.stderr.write('TICKS:' + ticks + '\n');
    // Drain stdin listener so node exits
    if (process.stdin && process.stdin.destroy) process.stdin.destroy();
};
p.onTickEnd = () => { ticks++; };

process.stdin.setEncoding('utf8');
process.stdin.on('data', (chunk) => {
    const s = chunk.toString();
    const line = s.endsWith('\n') ? s.slice(0, -1) : s;
    if (line.length > 0) p.input = line;
});
process.stdin.on('end', () => {
    // Keep running; interpreter decides when to terminate.
});

p.run(limit);
