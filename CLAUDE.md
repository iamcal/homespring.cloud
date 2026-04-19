# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Is

A visual debugger and learning tool for the [Homespring](https://esolangs.org/wiki/Homespring) esoteric programming language, hosted at https://homespring.cloud.

## Architecture

- **Deployment**: Apache virtual host with SSL (Let's Encrypt). Config in `site.conf` / `ssl.conf`. Served from `/var/www/html/homespring.cloud/www`.
- **`www/homespring.js/`**: Git submodule (`git@github.com:iamcal/homespring.js.git`) — the interpreter. This is a separate project; do not edit files here directly.
- **`www/index.php`**: Main page — 4-pane layout (source editor, input, output, tree visualization) with toolbar. Uses plain SVG for tree rendering, Catppuccin-inspired dark theme. Served at `https://homespring.cloud/` via DirectoryIndex.
- **`www/d.htm`**: Earlier prototype of the visual debugger — uses SVG.js. Kept as reference.
- **`www/index.htm`**: Earlier D3.js-based tree visualizer (static, no interactivity).
- **`www/simple.htm`**: Minimal console-only test page.

All three pages use `homespring.js/lib/homespring.js` as the interpreter. The interpreter API entry point is `HS.Program(source)` with `.tick()`, `.runSync()`, and `.run()` methods.

## Commands (homespring.js submodule)

```bash
cd www/homespring.js
npm install                    # install dev dependencies
npm test                       # run Karma/Jasmine tests (headless Chrome)
npm run uglify                 # build minified lib/homespring.min.js
npm run coverage               # run tests with coverage
npm run build                  # uglify + test + coverage
```

Tests run via Karma with headless Chrome (Puppeteer). Test files are in `www/homespring.js/test/` and are numbered by node type (e.g., `01_hatchery.js`, `07_bear.js`). The test suite runs against the **minified** build (`homespring.min.js`).

## Deployment

```bash
sudo ./install.sh              # symlinks site.conf into Apache sites-available and reloads
```
