<?php
	$title = 'Interpreters';
	$current = 'interpreters';
	include '../include/header.php';

	// Cards below the h1 show a compatibility score in the top-right. The
	// yes/total counts are parsed out of the <tbody> further down so the
	// score always matches the table. Rows with a merged compat-merged
	// cell (Quin's 4 programs) are skipped — they have no per-interpreter
	// result.
	$interpreters = [
		['lang' => 'Guile Scheme', 'date' => '2003-04-14', 'author' => 'Jeff Binder',   'url' => 'https://github.com/iamcal/Homespring',               'repo' => 'github.com/iamcal/Homespring',               'desc' => 'The original reference implementation.'],
		['lang' => 'Perl',         'date' => '2003-04-15', 'author' => 'Cal Henderson', 'url' => 'https://github.com/iamcal/perl-Language-Homespring', 'repo' => 'github.com/iamcal/perl-Language-Homespring', 'desc' => 'The first third party interpreter, including a tree visualizer.'],
		['lang' => 'OCaml',        'date' => '2005-11-24', 'author' => 'Joe Neeman',    'url' => 'https://github.com/jneem/homespring',                'repo' => 'github.com/jneem/homespring',                'desc' => 'Released along with an updated spec.'],
		['lang' => 'JavaScript',   'date' => '2012-10-30', 'author' => 'Quin Kennedy',  'url' => 'https://github.com/quinkennedy/Homespring',          'repo' => 'github.com/quinkennedy/Homespring',          'desc' => 'The first JavaScript implementation.'],
		['lang' => 'JavaScript',   'date' => '2017-01-29', 'author' => 'Cal Henderson', 'url' => 'https://github.com/iamcal/homespring.js',            'repo' => 'github.com/iamcal/homespring.js',            'desc' => 'The implementation that powers this website.'],
		['lang' => 'Rust',         'date' => '2017-12-19', 'author' => 'Addison Bean', 'url' => 'https://github.com/Addisonbean/Homespring-rs',        'repo' => 'github.com/Addisonbean/Homespring-rs',       'desc' => 'An incomplete WIP — parses programs, but only a few node types and ticks are implemented.'],
		['lang' => 'JavaScript',   'date' => '2018-05-30', 'author' => 'Martijn Arts',  'url' => 'https://github.com/martijnarts/homespring-js',       'repo' => 'github.com/martijnarts/homespring-js',       'desc' => 'A demo implementation with HTML visualizer.'],
		['lang' => 'JavaScript',   'date' => '2023-01-02', 'author' => 'James Thistlewood', 'url' => 'https://github.com/jthistle/homespring-visualizer', 'repo' => 'github.com/jthistle/homespring-visualizer', 'desc' => 'A browser visualizer with an embedded interpreter; driven headlessly here.'],
	];
	foreach ($interpreters as &$_i) { $_i['yes'] = 0; $_i['total'] = 0; }
	unset($_i);

	$_src = file_get_contents(__FILE__);
	if (preg_match('/<tbody>(.*?)<\/tbody>/s', $_src, $_m)) {
		preg_match_all('/<tr(?![^>]*class="author-row")[^>]*>(.*?)<\/tr>/s', $_m[1], $_rows);
		foreach ($_rows[1] as $_row) {
			if (strpos($_row, 'compat-merged') !== false) continue;
			preg_match_all('/<td class="compat\s+([^"]*)"/', $_row, $_cells);
			if (count($_cells[1]) !== count($interpreters)) continue;
			foreach ($_cells[1] as $_col => $_cls) {
				$_classes = preg_split('/\s+/', $_cls);
				if (in_array('yes', $_classes, true)) {
					$interpreters[$_col]['yes']++;
					$interpreters[$_col]['total']++;
				} elseif (in_array('no', $_classes, true)) {
					$interpreters[$_col]['total']++;
				}
			}
		}
	}
?>
<style>

html, body {
	min-height: 100%;
}

main {
	max-width: 1100px;
	margin: 0 auto;
	padding: 40px 24px 80px;
}

h1 {
	font-size: 32px;
	font-weight: 700;
	margin-bottom: 12px;
	color: var(--text);
}

p.lead {
	color: var(--text-dim);
	font-size: 15px;
	line-height: 1.6;
	margin-bottom: 8px;
	max-width: 760px;
}

p.lead a {
	font-weight: 500;
}

/* Interpreter cards */

.interp-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 12px;
	margin-top: 8px;
}

.interp-card {
	background: var(--surface);
	border: 1px solid var(--border);
	border-radius: 8px;
	padding: 14px 16px;
	position: relative;
}

.interp-card .name {
	font-size: 15px;
	font-weight: 600;
	color: var(--accent);
	margin-bottom: 4px;
	padding-right: 50px;
}

.interp-card .score {
	position: absolute;
	top: 12px;
	right: 14px;
	font-family: var(--font-mono);
	font-size: 14px;
	font-weight: 600;
	color: var(--green);
	cursor: help;
}

.interp-card .name .year {
	font-family: var(--font-mono);
	font-size: 13px;
	font-weight: 400;
	color: var(--text-dim);
	margin-left: 6px;
}

.interp-card .author {
	font-size: 13px;
	color: var(--text);
	margin-bottom: 8px;
}

.interp-card a.repo {
	font-size: 12px;
	font-family: var(--font-mono);
	word-break: break-all;
}

.interp-card .blurb {
	margin-top: 8px;
	color: var(--text-dim);
	font-size: 12px;
	line-height: 1.4;
}

/* Examples table — wrapped in a horizontally-scrolling container so the
   compat matrix can keep growing sideways. The program-name column is
   frozen to the left via position:sticky so it stays visible while the
   description and per-interpreter cells scroll. */

.table-scroll {
	margin-top: 16px;
	border: 1px solid var(--border);
	border-radius: 8px;
	overflow-x: auto;
	background: var(--surface);
	/* Break out of the 1100px main column and span the viewport minus
	   a 40px gutter on each side, so the compat matrix can fit more
	   interpreters without forcing a horizontal scrollbar. The 40px
	   inset also absorbs any vertical-scrollbar width so 100vw doesn't
	   cause page-level horizontal overflow. When viewport ≤ main's
	   max-width the calc is ~0 and nothing changes. */
	width: calc(100vw - 80px);
	margin-left: calc(50% - 50vw + 40px);
	margin-right: calc(50% - 50vw + 40px);
}

table.examples {
	width: 100%;
	border-collapse: separate;
	border-spacing: 0;
	background: var(--surface);
}

table.examples thead th {
	background: var(--surface2);
	color: var(--text-dim);
	font-size: 11px;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	text-align: left;
	padding: 10px 14px;
	border-bottom: 1px solid var(--border);
	font-weight: 600;
}

table.examples thead th.compat {
	text-align: center;
	width: 90px;
	line-height: 1.35;
}

table.examples thead th.compat .lang {
	display: block;
	font-size: 11px;
	color: var(--text);
	text-transform: none;
	letter-spacing: 0;
	font-weight: 600;
}

table.examples thead th.compat .year {
	display: block;
	font-family: var(--font-mono);
	font-size: 11px;
	color: var(--text-dim);
	font-weight: 400;
	margin-top: 1px;
}

table.examples thead th.compat .pct {
	display: block;
	font-family: var(--font-mono);
	font-size: 11px;
	font-weight: 600;
	color: var(--green);
	margin-top: 4px;
	cursor: help;
}

table.examples tbody td {
	padding: 10px 14px;
	font-size: 13px;
	border-bottom: 1px solid var(--border);
	vertical-align: top;
}

table.examples tbody tr:last-child td {
	border-bottom: none;
}

table.examples tbody tr.author-row td {
	background: var(--surface2);
	color: var(--accent);
	font-weight: 600;
	font-size: 13px;
	padding: 10px 14px;
	letter-spacing: 0.3px;
}

table.examples tbody tr.author-row td .author-meta {
	color: var(--text-dim);
	font-weight: 400;
	font-size: 12px;
	margin-left: 8px;
}

table.examples td.name {
	font-family: var(--font-mono);
	font-size: 13px;
	white-space: nowrap;
}

/* Freeze the program-name column and the matching header cell. Sticky
   cells need an explicit background so scrolled content doesn't bleed
   through, and a right border so the seam is visible while scrolling. */
table.examples thead th:first-child,
table.examples td.name {
	position: sticky;
	left: 0;
	background: var(--surface);
	border-right: 1px solid var(--border);
	z-index: 1;
}

table.examples thead th:first-child {
	background: var(--surface2);
	z-index: 2;
}

/* Give the description some breathing room now that we can scroll. */
table.examples td.desc,
table.examples thead th:nth-child(2) {
	min-width: 360px;
}

table.examples td.name a {
	color: var(--text);
}

table.examples td.name a:hover {
	color: var(--accent);
}

table.examples td.name .src-alt {
	color: var(--text-dim);
	font-size: 11px;
	margin-left: 6px;
}

table.examples td.name .src-alt a {
	color: var(--text-dim);
}

table.examples td.name .src-alt a:hover {
	color: var(--accent);
}

table.examples td.desc {
	color: var(--text-dim);
	line-height: 1.5;
}

table.examples td.compat {
	text-align: center;
	font-family: var(--font-mono);
	font-size: 13px;
}

/* Keep the baseline aligned across all cells in a row, regardless of
   whether the cell contains a <sup> footnote marker. */
table.examples td.compat sup {
	line-height: 0;
}

.compat.yes {
	color: var(--green);
}

.compat.yes.presumed i {
	font-style: italic;
	color: var(--green);
	opacity: 0.75;
}

.compat.yes.presumed[title] i {
	cursor: help;
}

.compat.no {
	color: var(--red);
	opacity: 0.7;
}

.compat.no[title] {
	cursor: help;
}

.compat.unknown {
	color: var(--yellow);
}

.compat-merged {
	color: var(--text-dim);
	padding: 14px 20px !important;
	line-height: 1.5;
	font-size: 12px;
}

.compat-merged p {
	margin-bottom: 8px;
	/* Keep each paragraph visible while the user scrolls horizontally
	   through the 7-column spanning cell. The sticky left sits just past
	   the frozen name column — the name column's higher z-index means it
	   visually covers any overlap, so only the portion to the right of
	   the name column is ever shown. */
	position: sticky;
	left: 150px;
}

.compat-merged p:last-child {
	margin-bottom: 0;
}

.compat-merged code {
	font-size: 11px;
}

.footnote {
	color: var(--text-dim);
	font-size: 13px;
	margin-top: 16px;
	line-height: 1.6;
}

.legend-inline {
	display: flex;
	flex-direction: column;
	gap: 6px;
	margin-top: 12px;
	font-size: 12px;
	font-family: var(--font-mono);
	color: var(--text-dim);
}

.legend-inline span b {
	font-weight: 400;
}

</style>
</head>
<body>

<?php include '../include/nav.php'; ?>

<main>

<h1>Interpreters</h1>
<p class="lead">
	The original Homespring specification shipped with an interpreter
	and another implementation was created the next day. This page documents
	all of the known interpreter implementations, along with a compatibility
	matrix showing which interpreters can run which of the collected example
	programs.
</p>

<p class="lead">
	Programs are grouped by author and listed under their original filenames.
</p>

<h2>The Interpreters</h2>
<div class="interp-grid">
<?php foreach ($interpreters as $i):
	$pct = $i['total'] > 0 ? round(100 * $i['yes'] / $i['total']) : 0;
	$tip = $i['yes'] . '/' . $i['total'] . ' examples run correctly';
?>
	<div class="interp-card">
		<div class="score" title="<?= htmlspecialchars($tip) ?>"><?= $pct ?>%</div>
		<div class="name"><?= htmlspecialchars($i['lang']) ?> <span class="year"><?= $i['date'] ?></span></div>
		<div class="author"><?= htmlspecialchars($i['author']) ?></div>
		<a class="repo" href="<?= htmlspecialchars($i['url']) ?>"><?= htmlspecialchars($i['repo']) ?></a>
		<div class="blurb"><?= htmlspecialchars($i['desc']) ?></div>
	</div>
<?php endforeach; ?>
</div>

<h2>The Example Programs</h2>

<div class="table-scroll">
<table class="examples">
	<thead>
		<tr>
			<th>Example</th>
			<th>Description</th>
<?php
	// Short labels for the compact column headers — the "Guile Scheme"
	// card title doesn't fit in a 90px-wide cell, so drop "Guile".
	$short_lang = ['Guile Scheme' => 'Scheme'];
	foreach ($interpreters as $i):
		$pct = $i['total'] > 0 ? round(100 * $i['yes'] / $i['total']) : 0;
		$tip = $i['yes'] . '/' . $i['total'] . ' examples run correctly';
		$lang = $short_lang[$i['lang']] ?? $i['lang'];
		$year = substr($i['date'], 0, 4);
?>
			<th class="compat" title="<?= htmlspecialchars($tip) ?>"><span class="lang"><?= htmlspecialchars($lang) ?></span><span class="year"><?= $year ?></span><span class="pct"><?= $pct ?>%</span></th>
<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>

		<tr class="author-row">
			<td colspan="10">Jeff Binder <span class="author-meta">— 2003 · author of Homespring and its <a href="https://github.com/iamcal/Homespring">original Scheme interpreter</a></span></td>
		</tr>

		<tr>
			<td class="name"><a href="examples/2003-jeff-binder/add.hs">add.hs</a></td>
			<td class="desc">Reads two numbers from input and outputs their sum.</td>
			<td class="compat yes presumed" title="Runs this program correctly under an interactive terminal — typing `2` then `3` at the prompts produces `? + 5` — but the harness's one-shot piped stdin closes before the slow unary counter finishes."><i>yes</i></td>
			<td class="compat no" title="Cannot handle the full add program">no</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes<sup>*</sup></td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="No stdin support — input salmon are never created.">no</td>
			<td class="compat no" title="Reads input but the unary counter doesn't produce the expected sum.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2003-jeff-binder/cat.hs">cat.hs</a></td>
			<td class="desc">Echoes its input straight to output, like the Unix <code>cat</code> utility.</td>
			<td class="compat yes presumed" title="Does echo stdin (verify with `yes hello | hs cat.hs`), but it requires a continuously-open stdin stream; the harness's piped single-shot input closes too quickly for the interpreter's poll-based reader to pick up the data."><i>yes</i></td>
			<td class="compat no" title="Has no input support">no</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes<sup>*</sup></td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="No stdin support — input salmon are never created.">no</td>
			<td class="compat no" title="Produces no output — the driver's one-line-per-tick stdin model doesn't line up with the interpreter's input loop for this program.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2003-jeff-binder/first.hs">first.hs</a></td>
			<td class="desc">A minimal "Hello, world" — a single hatchery feeding a bear.</td>
			<td class="compat yes">yes</td>
			<td class="compat yes presumed" title="Emits the first line one tick later than others."><i>yes</i></td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes<sup>*</sup></td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Runs but produces no output.">no</td>
			<td class="compat yes">yes</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2003-jeff-binder/hello-1.hs">hello-1.hs</a></td>
			<td class="desc">"Hello, World!" using a universe, a hatchery, and snowmelt-powered rapids.</td>
			<td class="compat yes">yes</td>
			<td class="compat yes presumed" title="Finishes in 6 ticks instead of the OCaml reference's 7 — a minor phase-ordering difference."><i>yes</i></td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes<sup>*</sup></td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Enters an infinite loop and hits the 3s timeout without producing any output.">no</td>
			<td class="compat yes">yes</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2003-jeff-binder/hello-2.hs">hello-2.hs</a></td>
			<td class="desc">"Hello, World!" — an alternative arrangement using the power-override rule.</td>
			<td class="compat yes">yes</td>
			<td class="compat yes presumed" title="Finishes in 9 ticks instead of the OCaml reference's 10 — a minor phase-ordering difference."><i>yes</i></td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes<sup>*</sup></td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Runs 8 phases and produces no output.">no</td>
			<td class="compat yes">yes</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2003-jeff-binder/hello-3.hs">hello-3.hs</a></td>
			<td class="desc">"Hello, World!" using marshy force, field-sense shallows, and a hydro-power spring.</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Does not support sense / shallows / force field">no</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes<sup>*</sup></td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Does not support the sense / shallows / force.field nodes this program uses to gate output.">no</td>
			<td class="compat no" title="Correct greeting followed by a stray `homeless` salmon — an extra hatchery emission after the program's intended output.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2003-jeff-binder/hi.hs">hi.hs</a></td>
			<td class="desc">Prompts the user for their name and greets them back with a "Hi".</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Has no input support">no</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes<sup>*</sup></td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="No stdin support — input salmon are never created.">no</td>
			<td class="compat no" title="Reads input but doesn't produce the full greeting.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2003-jeff-binder/name.hs">name.hs</a></td>
			<td class="desc">An acrostic program whose node names spell out HOMESPRING line by line.</td>
			<td class="compat no" title="Emits a 'HatcheryHatcheryhomeless' cycle indefinitely instead of the OCaml/JS 'Great'/'homeless' pattern — completely different salmon naming.">no</td>
			<td class="compat no" title="Starts with 'homelesshomelessGreat' and keeps alternating 'homeless'/'Great' forever, where OCaml/JS stop after ten tokens.">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Matches the first ten tokens exactly, but continues emitting 'homeless'/'Great' pairs indefinitely where OCaml/JS stop.">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Parser splits on spaces only, so the newline + indent structure of the source collapses into a flat tree — the intended river layout is lost and output is either empty or a stream of literal node-name salmon.">no</td>
			<td class="compat no" title="Acrostic salmon not emitted.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2003-jeff-binder/null.hs">null.hs</a></td>
			<td class="desc">The empty program — zero bytes, does nothing.</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Has no special handling for the null program and silently produces nothing">no</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes<sup>*</sup></td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Empty source is parsed as a single-node River rather than triggering the Quine branch, so the hardcoded 'not a quine' message never fires.">no</td>
			<td class="compat no" title="No special handling for the null/empty program.">no</td>
			<td class="compat no" title="Parser crashes with 'trying to add token above root' — the tokenizer yields a trailing empty token for the empty source that buildTree can't resolve.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2003-jeff-binder/quiz.hs">quiz.hs</a></td>
			<td class="desc">A tiny maths quiz that asks "what's six times four?" and grades the reply.</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Has no input support">no</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes<sup>*</sup></td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="No stdin support — input salmon are never created.">no</td>
			<td class="compat no" title="Reads input but produces no response.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2003-jeff-binder/simple.hs">simple.hs</a></td>
			<td class="desc">The simplest possible non-empty program — a single node.</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes</td>
		</tr>

		<tr class="author-row">
			<td colspan="10">Cal Henderson <span class="author-meta">— 2003 · author of the <a href="https://github.com/iamcal/perl-Language-Homespring">Perl interpreter</a></span></td>
		</tr>

		<tr>
			<td class="name"><a href="examples/2003-cal-henderson/hello2.hs">hello2.hs</a></td>
			<td class="desc">A compact "Hello, World!" — shipped with the Perl interpreter as a test program.</td>
			<td class="compat yes">yes</td>
			<td class="compat yes presumed" title="Finishes in 6 ticks instead of the OCaml reference's 7 — a minor phase-ordering difference."><i>yes</i></td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes<sup>*</sup></td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Runs but produces no output — the parser's flat tree leaves nothing to route salmon to the root.">no</td>
			<td class="compat yes">yes</td>
		</tr>

		<tr class="author-row">
			<td colspan="10">Joe Neeman <span class="author-meta">— 2005 · author of the <a href="https://github.com/jneem/homespring">OCaml interpreter</a></span></td>
		</tr>

		<tr>
			<td class="name"><a href="examples/2005-joe-neeman/flipflop.hs">flipflop.hs</a></td>
			<td class="desc">A flip-flop that alternates state using an inverse-lock and a pair of pump/switch nodes.</td>
			<td class="compat no" title="Emits 'hello' followed by an infinite 'homeless' loop instead of the expected 'helloo'">no</td>
			<td class="compat no" title="Does not support inverse-lock / pump / switch">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Emits 'hello' (one 'o' short of the OCaml/JS reference)">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Does not support inverse-lock / pump / switch nodes.">no</td>
			<td class="compat no" title="Flip-flop logic doesn't produce any output.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2005-joe-neeman/tic.hs">tic.hs</a></td>
			<td class="desc">A tic-tac-toe implementation — the most elaborate program in the collection.</td>
			<td class="compat no" title="Emits nothing for this program">no</td>
			<td class="compat no" title="Does not support the node types used">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Emits nothing for this program">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Does not support the node types used">no</td>
			<td class="compat no" title="Game loop doesn't advance past the initial render.">no</td>
		</tr>

		<tr class="author-row">
			<td colspan="10">Quin Kennedy <span class="author-meta">— 2012 · author of a <a href="https://github.com/quinkennedy/Homespring">JavaScript interpreter</a></span></td>
		</tr>

		<tr>
			<td class="name"><a href="examples/2012-quin-kennedy/reverse.hsg">reverse.hsg</a></td>
			<td class="desc">Reverses the input using a <code>force.up</code> node and a <code>split</code>.</td>
			<td class="compat-merged" colspan="8" rowspan="4">
				<p>Quin's example files don't run correctly on compliant interpreters, due to two important mistakes in the implementation.</p>
				
				<p>The <code>reverse*.hsg</code> examples rely on the <code>force up</code> node allowing upstream salmon to move to the first child, which it should be blocking.</p>
				
				<p>The <code>split.hsg</code> example relies on an incorrect implementation of <code>append up</code> where the appending logic is supposed to run in the misc tick,
				but instead runs in the fish tick down, before any upstream salmon have had a chance to arrive (or leave, from the previous tick).</p>
			</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2012-quin-kennedy/reverse-2.hsg">reverse-2.hsg</a></td>
			<td class="desc">Reverses the input — a more poetic variant ("split wings calm the ebb and flow").</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2012-quin-kennedy/reverse-3.hsg">reverse-3.hsg</a></td>
			<td class="desc">Reverses the input — a third variation of the same arrangement.</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2012-quin-kennedy/split.hsg">split.hsg</a></td>
			<td class="desc">Splits input into pieces.</td>
		</tr>

		<tr class="author-row">
			<td colspan="10">Benito van der Zander <span class="author-meta">— 2013 · author of the <a href="https://github.com/benibela/home-river">HomeSpringTree compiler</a>; most are generated from HomeSpringTree (.hst) sources</span></td>
		</tr>

		<tr>
			<td class="name"><a href="examples/2013-benito-van-der-zander/clock.hs">clock.hs</a><span class="src-alt">(<a href="examples/2013-benito-van-der-zander/clock.hst">.hst</a>)</span></td>
			<td class="desc">A ticking clock driven by the <code>time</code> node and a range-switch cascade.</td>
			<td class="compat no" title="Does not terminate; produces 'XXXXXXXXy\n' indefinitely instead of stopping after the clock's 5-cycle run">no</td>
			<td class="compat no" title="Does not support the node types used">no</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes<sup>*</sup></td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Parser splits on spaces only, so the newline + indent structure of the source collapses into a flat tree — the intended river layout is lost and output is either empty or a stream of literal node-name salmon.">no</td>
			<td class="compat no" title="Emits the correct y-column sequence but with only one X per column instead of eight — the shallows/range counter isn't incrementing as expected.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2013-benito-van-der-zander/count.hs">count.hs</a><span class="src-alt">(<a href="examples/2013-benito-van-der-zander/count.hst">.hst</a>)</span></td>
			<td class="desc">Counts from 0 to 9 and then to 100 using a bridged bear and hatchery cascade.</td>
			<td class="compat no" title="Produces a broken, non-monotonic counter ('9\n10\n19\n20\n29\n30\n…') instead of 1,2,3,…">no</td>
			<td class="compat no" title="Produces a broken counter ('1,2,..9,0,11,2,..')">no</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes<sup>*</sup></td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Parser splits on spaces only, so the newline + indent structure of the source collapses into a flat tree — the intended river layout is lost and output is either empty or a stream of literal node-name salmon.">no</td>
			<td class="compat no" title="Correct output; driver runs to the tick cap instead of detecting natural termination so the actual_ticks count doesn't match the OCaml reference.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2013-benito-van-der-zander/count2.hs">count2.hs</a><span class="src-alt">(<a href="examples/2013-benito-van-der-zander/count2.hst">.hst</a>)</span></td>
			<td class="desc">A more elaborate counter built from digit generators.</td>
			<td class="compat no" title="Raises a runtime backtrace on this program">no</td>
			<td class="compat no" title="Overflows its node walker (deep recursion)">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Produces '..._A8________A7________A6_' (reversed digits) instead of '..._A1________A2________A3_'">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Parser splits on spaces only, so the newline + indent structure of the source collapses into a flat tree — the intended river layout is lost and output is either empty or a stream of literal node-name salmon.">no</td>
			<td class="compat no" title="Counter emits full 0-9 runs per decade marker (A0123456789…) instead of the hold-and-carry sequence (…A1…A2…A3…).">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2013-benito-van-der-zander/count3.hs">count3.hs</a><span class="src-alt">(<a href="examples/2013-benito-van-der-zander/count3.hst">.hst</a>)</span></td>
			<td class="desc">Another counter variant.</td>
			<td class="compat no" title="Raises a runtime backtrace">no</td>
			<td class="compat no" title="Overflows (deep recursion)">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Produces '_________9_9A_9B…' instead of '________x_xA…'">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Parser splits on spaces only, so the newline + indent structure of the source collapses into a flat tree — the intended river layout is lost and output is either empty or a stream of literal node-name salmon.">no</td>
			<td class="compat no" title="Halts partway through — emits 72 underscores then stops before reaching the lettered digits.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2013-benito-van-der-zander/count4.hs">count4.hs</a><span class="src-alt">(<a href="examples/2013-benito-van-der-zander/count4.hst">.hst</a>)</span></td>
			<td class="desc">A larger counter implementation (~10 KB of source).</td>
			<td class="compat no" title="Raises a runtime backtrace">no</td>
			<td class="compat no" title="Overflows (deep recursion)">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Emits a reordered prefix ('numberhello…')">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Parser splits on spaces only, so the newline + indent structure of the source collapses into a flat tree — the intended river layout is lost and output is either empty or a stream of literal node-name salmon.">no</td>
			<td class="compat no" title="No output produced within the tick cap.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2013-benito-van-der-zander/count.poem.hs">count.poem.hs</a><span class="src-alt">(<a href="examples/2013-benito-van-der-zander/count.poem.hst">.hst</a>)</span></td>
			<td class="desc">A counter written in the poetic style Homespring is intended to be read in.</td>
			<td class="compat no" title="Produces a broken, non-monotonic counter ('9\n10\n19\n20\n29\n30\n…') instead of 1,2,3,…">no</td>
			<td class="compat no" title="Produces a broken counter">no</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes<sup>*</sup></td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Parser splits on spaces only, so the newline + indent structure of the source collapses into a flat tree — the intended river layout is lost and output is either empty or a stream of literal node-name salmon.">no</td>
			<td class="compat no" title="Correct output; driver runs to the tick cap instead of detecting natural termination so the actual_ticks count doesn't match the OCaml reference.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2013-benito-van-der-zander/count.poem.withfillers.hs">count.poem.withfillers.hs</a></td>
			<td class="desc">A poetic counter with additional filler words for extra flow.</td>
			<td class="compat no" title="Emits unrelated output for this program">no</td>
			<td class="compat no" title="Produces a broken counter">no</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes<sup>*</sup></td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Parser splits on spaces only, so the newline + indent structure of the source collapses into a flat tree — the intended river layout is lost and output is either empty or a stream of literal node-name salmon.">no</td>
			<td class="compat no" title="No output produced within the 250-tick cap.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2013-benito-van-der-zander/fizzbuzz.hs">fizzbuzz.hs</a><span class="src-alt">(<a href="examples/2013-benito-van-der-zander/fizzbuzz.hst">.hst</a>)</span></td>
			<td class="desc">Classic FizzBuzz — prints Fizz for multiples of 3, Buzz for 5, FizzBuzz for both.</td>
			<td class="compat no" title="Raises a runtime backtrace">no</td>
			<td class="compat no" title="Overflows (deep recursion)">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Emits placeholder tokens ('c_\nc_\nFizz\nc_\nBuzz…') where it should emit digits">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Parser splits on spaces only, so the newline + indent structure of the source collapses into a flat tree — the intended river layout is lost and output is either empty or a stream of literal node-name salmon.">no</td>
			<td class="compat no" title="No output produced within the tick cap — program is known to be very slow; raising the cap may or may not help.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2013-benito-van-der-zander/fizzbuzz.poem.hs">fizzbuzz.poem.hs</a><span class="src-alt">(<a href="examples/2013-benito-van-der-zander/fizzbuzz.poem.hst">.hst</a>)</span></td>
			<td class="desc">FizzBuzz written in the poetic style — considerably longer.</td>
			<td class="compat no" title="Raises a runtime backtrace">no</td>
			<td class="compat no" title="Overflows (deep recursion)">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Emits placeholder tokens ('c_\nc_\nFizz\nc_\nBuzz…') where it should emit digits">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Parser splits on spaces only, so the newline + indent structure of the source collapses into a flat tree — the intended river layout is lost and output is either empty or a stream of literal node-name salmon.">no</td>
			<td class="compat no" title="No output produced within the tick cap — program is known to be very slow; raising the cap may or may not help.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2013-benito-van-der-zander/fizzbuzztick.hs">fizzbuzztick.hs</a><span class="src-alt">(<a href="examples/2013-benito-van-der-zander/fizzbuzztick.hst">.hst</a>)</span></td>
			<td class="desc">A compact FizzBuzz variant driven by time ticks.</td>
			<td class="compat no" title="Emits only 'tick' lines, not the Fizz/Buzz numbers">no</td>
			<td class="compat no" title="Overflows (deep recursion)">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Starts with 'tick' and reorders the output">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Parser splits on spaces only, so the newline + indent structure of the source collapses into a flat tree — the intended river layout is lost and output is either empty or a stream of literal node-name salmon.">no</td>
			<td class="compat no" title="Emits `tick` on every tick but never the Fizz/Buzz arithmetic.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2013-benito-van-der-zander/helloworld.hs">helloworld.hs</a><span class="src-alt">(<a href="examples/2013-benito-van-der-zander/helloworld.hst">.hst</a>)</span></td>
			<td class="desc">A fourth take on "Hello, World!" — generated from a HomeSpringTree source.</td>
			<td class="compat no" title="Loops 'o world' indefinitely">no</td>
			<td class="compat no" title="Does not support the node types used">no</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes<sup>*</sup></td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Parser splits on spaces only, so the newline + indent structure of the source collapses into a flat tree — the intended river layout is lost and output is either empty or a stream of literal node-name salmon.">no</td>
			<td class="compat yes">yes</td>
		</tr>

		<tr class="author-row">
			<td colspan="10">Cal Henderson <span class="author-meta">— 2026 · focused tests for this harness, each written to exercise one node whose behaviour the older programs barely touched</span></td>
		</tr>

		<tr>
			<td class="name"><a href="examples/2026-cal-henderson/test-waterfall.hs">test-waterfall.hs</a></td>
			<td class="desc">Upstream salmon are blocked by the <code>waterfall</code> and spawn there instead of climbing to the leaf.</td>
			<td class="compat no" title="Waterfall doesn't block upstream salmon — the homeless fish climb straight past it and spawn at the Hello leaf instead.">no</td>
			<td class="compat no" title="Waterfall doesn't block upstream salmon — the homeless fish climb straight past it and spawn at the Hello leaf instead.">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Emits only a stray newline — waterfall blocks more than just upstream salmon.">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Parser splits on spaces only, so the newline + indent structure of the source collapses into a flat tree — the intended river layout is lost and output is either empty or a stream of literal node-name salmon.">no</td>
			<td class="compat no" title="Waterfall doesn't block — salmon climb through it and the hatchery, so output is 'homelesshatchery' cycles instead of 'waterfallhomeless'.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2026-cal-henderson/test-clone.hs">test-clone.hs</a></td>
			<td class="desc"><code>clone</code> duplicates each passing salmon, so every cycle emits multiple <code>homeless</code> before <code>Hello</code>.</td>
			<td class="compat no" title="Clone produces one fewer salmon per cycle than the reference — 3 homeless before Hello instead of 4.">no</td>
			<td class="compat no" title="Clone has no effect — a single homeless per cycle instead of the duplicated output the OCaml reference produces.">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Clone over-multiplies — 6 homeless per cycle instead of the reference's 4.">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Parser splits on spaces only, so the newline + indent structure of the source collapses into a flat tree — the intended river layout is lost and output is either empty or a stream of literal node-name salmon.">no</td>
			<td class="compat yes">yes</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2026-cal-henderson/test-young-bear.hs">test-young-bear.hs</a></td>
			<td class="desc"><code>young. bear</code> eats every other mature salmon, letting only half the <code>homeless</code> fish reach the root.</td>
			<td class="compat no" title="young. bear eats every mature salmon (like a regular bear) instead of every other, so the homeless fish never reach output.">no</td>
			<td class="compat no" title="young. bear's eat-every-other logic is offset differently — produces 'Hello\nhomelessHello\nHello\nhomelessHello\n' instead of the reference's uniform alternation.">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Similar eat-every-other pattern but offset by one cycle vs the OCaml reference.">no</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Parser splits on spaces only, so the newline + indent structure of the source collapses into a flat tree — the intended river layout is lost and output is either empty or a stream of literal node-name salmon.">no</td>
			<td class="compat no" title="Alternation is offset by one cycle vs the OCaml reference — extra 'Hello' before the first 'homeless' pair.">no</td>
		</tr>
		<tr>
			<td class="name"><a href="examples/2026-cal-henderson/test-spawn.hs">test-spawn.hs</a></td>
			<td class="desc"><code>spawn</code> forces upstream salmon to spawn in place, so the output is the node's own name.</td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="spawn has no effect — upstream salmon continue past it to the Hello leaf instead of spawning in place.">no</td>
			<td class="compat yes">yes</td>
			<td class="compat yes">yes<sup>*</sup></td>
			<td class="compat yes">yes</td>
			<td class="compat no" title="Panics with `already borrowed: BorrowMutError` on the first FishDown tick — river.rs:237's move_salmon borrows the parent mutably while the tick recursion still holds it, so any multi-level tree crashes before producing output.">no</td>
			<td class="compat no" title="Parser splits on spaces only, so the newline + indent structure of the source collapses into a flat tree — the intended river layout is lost and output is either empty or a stream of literal node-name salmon.">no</td>
			<td class="compat yes">yes</td>
		</tr>

	</tbody>
</table>
</div>

<div class="legend-inline">
	<span><b class="compat yes">yes</b> &nbsp;runs correctly</span>
	<span><b class="compat yes presumed"><i>yes</i></b> &nbsp;runs correctly, with caveats (hover for details)</span>
	<span><b class="compat no">no</b> &nbsp;does not run correctly (hover for details)</span>
	<span><b class="compat unknown">?</b> &nbsp;no canonical expected output</span>
</div>

<p class="footnote">
	<sup>*</sup> Quin Kennedy's interpreter appends an extra newline to every
	output — the program produces the correct text but with one trailing
	<code>\n</code> beyond what the other interpreters emit.
</p>

<p class="footnote">
	Compatibility data is generated by the test harness in
	<code>tests/</code>, which runs every example above under every
	interpreter and writes per-adapter reports under <code>tests/results/</code>. Hover over a
	cell for further details about that specific result.
	The full interpreter source repositories are
	checked in as submodules under <code>interpreters/</code>.
</p>

</main>

<?php include '../include/footer.php'; ?>
