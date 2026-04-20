#!/usr/bin/env node
// Driver for James Thistlewood's 2023 Homespring visualizer.
// Usage: node james_thistlewood_driver.js <program.hs>
// Env:
//   HS_LIMIT=N  cap total ticks (default 1000)
//   HS_TICKS=1  emit "TICKS:N" on stderr at exit
//
// The interpreter ships as a one-file browser script (index.js) that
// wires tokenize/buildTree/Node.executeTick into an EaselJS canvas and
// textareas. We load that script into a vm context with stubbed
// window/document/createjs and shadow its I/O hooks with replacements
// that talk to stdio instead of the DOM. Each call to
// homespringGetInput consumes one stdin line, matching how a webpage
// user would paste input then hit Step.

'use strict';

const fs = require('fs');
const path = require('path');
const vm = require('vm');

const hsPath = process.env.HS_JT_PATH ||
    path.resolve(__dirname, '../../interpreters/2023-james-thistlewood/index.js');

const limit = parseInt(process.env.HS_LIMIT || '0', 10) || 0;
const reportTicks = process.env.HS_TICKS === '1';

const file = process.argv[2];
if (!file) {
    console.error('usage: james_thistlewood_driver.js <program.hs>');
    process.exit(2);
}

const stdinRaw = fs.readFileSync(0, 'utf8');
const inputQueue = stdinRaw.length ? stdinRaw.split('\n') : [];
while (inputQueue.length && inputQueue[inputQueue.length - 1] === '') {
    inputQueue.pop();
}

const source = fs.readFileSync(file, 'utf8');
const interpSrc = fs.readFileSync(hsPath, 'utf8');

const sandbox = {
    window: { addEventListener: () => {} },
    document: { getElementById: () => ({ addEventListener: () => {} }) },
    createjs: { Stage: function () { return {}; }, Shape: function () { return {}; } },
    alert: () => {},
    console,
    __hs_out: (s) => process.stdout.write(String(s)),
    __hs_err: (s) => process.stderr.write(String(s) + '\n'),
    __hs_in: () => (inputQueue.length ? inputQueue.shift() : ''),
    __hs_bridge: null,
};
vm.createContext(sandbox);

// Later top-level function declarations shadow earlier ones at script
// scope, so redeclaring homespring{Output,Error,GetInput} here overrides
// index.js's DOM-coupled versions. The bridge object gives us handles
// to the const/class bindings (executionState and friends) that don't
// otherwise leak onto the global.
const suffix = `

function homespringOutput(text) { __hs_out(text); }
function homespringError(msg)  { __hs_err(msg); }
function homespringGetInput()  { return __hs_in(); }

__hs_bridge = {
    tokenize: tokenize,
    buildTree: buildTree,
    executionState: executionState,
};
`;

vm.runInContext(interpSrc + suffix, sandbox, { filename: hsPath });
const bridge = sandbox.__hs_bridge;

let tickNum = 0;
try {
    const tokens = bridge.tokenize(source);
    const root = bridge.buildTree(tokens);
    const cap = limit || 1000;
    while (tickNum < cap) {
        root.executeTick();
        tickNum++;
        if (bridge.executionState.universeDestroyed) break;
    }
} catch (e) {
    process.stderr.write('interpreter error: ' + (e && e.message || e) + '\n');
    if (reportTicks) process.stderr.write('TICKS:' + tickNum + '\n');
    process.exit(1);
}

if (reportTicks) {
    process.stderr.write('TICKS:' + tickNum + '\n');
}
