#!/usr/bin/env node
// Tokenize a Homespring source string using the homespring.js tokenizer.
// Useful for poking at edge cases in the lexer.
//
// Usage:
//   tests/tokenize.js 'Hello,. world ..'
//   echo -n 'Hello,. world ..' | tests/tokenize.js
//
// Each token is printed as a JSON-quoted string on its own line so
// whitespace, escapes, and blank tokens are visible.

const path = require('path');
const HS = require(path.resolve(__dirname, '../www/homespring.js/lib/homespring.js'));

async function main() {
    let src;
    if (process.argv.length > 2) {
        src = process.argv.slice(2).join(' ');
    } else {
        const chunks = [];
        for await (const c of process.stdin) chunks.push(c);
        src = Buffer.concat(chunks).toString('utf8');
    }

    let tokens;
    try {
        const p = new HS.Program('');
        tokens = p.tokenize(src);
    } catch (e) {
        console.error('tokenizer error:', e.message);
        process.exit(1);
    }

    console.log(`${tokens.length} token(s):`);
    tokens.forEach((t, i) => {
        console.log(`  ${String(i).padStart(3)}: ${JSON.stringify(t)}`);
    });
}

main();
