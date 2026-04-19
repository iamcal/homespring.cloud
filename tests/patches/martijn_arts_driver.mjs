#!/usr/bin/env node
// Driver for Martijn Arts's 2018 homespring-js.
// Usage: node martijn_arts_driver.mjs <program.hs>
// Env:
//   HS_LIMIT=N  cap total ticks (each tick = one full phase cycle)
//   HS_TICKS=1  report "TICKS:N" to stderr at end
//
// Martijn's parser expects a single line of space-separated tokens with
// empty tokens meaning "go up one level". It does not understand newline
// + indent source as used by every other implementation, so standard
// Homespring programs get mangled into whatever tree split(' ') produces.
// We still run them as-is — the accuracy of the resulting shape is the
// thing we're measuring.
import fs from 'fs';
import path from 'path';
import { fileURLToPath, pathToFileURL } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const hsPath = process.env.HS_MARTIJN_PATH ||
    path.resolve(__dirname, '../../interpreters/2018-martijn-arts/homespring.js');

const hs = await import(pathToFileURL(hsPath).href);

const limit = parseInt(process.env.HS_LIMIT || '0', 10) || 0;
const reportTicks = process.env.HS_TICKS === '1';
const phaseLimit = limit ? limit * hs.tickOrder.length : 0;

const file = process.argv[2];
if (!file) {
    console.error('usage: martijn_arts_driver.mjs <program.hs>');
    process.exit(2);
}
const source = fs.readFileSync(file, 'utf8');

const runner = new hs.Runner();
const origDoTick = runner.doTick.bind(runner);
runner.doTick = function () {
    if (phaseLimit && this.runs >= phaseLimit) {
        this.continue = false;
        return;
    }
    origDoTick();
};

const root = hs.tokensToTree(runner, hs.tokens, hs.parser(source));
runner.setRoot(root);
runner.run();

if (reportTicks) {
    process.stderr.write('TICKS:' + Math.floor(runner.runs / hs.tickOrder.length) + '\n');
}
