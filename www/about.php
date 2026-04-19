<?php $title = 'About — homespring.cloud'; $current = 'about'; include 'include/header.php'; ?>
<style>

html, body {
	min-height: 100%;
}

main {
	max-width: 820px;
	margin: 0 auto;
	padding: 40px 24px 80px;
}

main h1 {
	font-size: 32px;
	font-weight: 700;
	margin-bottom: 12px;
}

main p {
	color: var(--text-dim);
	font-size: 15px;
	line-height: 1.6;
	margin-bottom: 12px;
}

</style>
</head>
<body>

<?php include 'include/nav.php'; ?>

<main>
	<h1>About homespring.cloud</h1>

	<p>Homespring.cloud is a visual debugger and learning tool for the Homespring esoteric programming language.</p>


	<h2>The Homespring Language</h2>

	<p>Homespring is an <a href="https://esolangs.org/wiki/Esoteric_programming_language">Esoteric Programming Language</a>, created by Jeff Binder in 2003.</p>

	<p>Instead of the traditional variables, operators and flow control that most language possess, Homespring program are poems that describe a system of rivers.
		These rivers are populated by salmon which carry data around the system and handle input and output.</p>

	<p>A simple program that outputs the string <code>"Hello World!\n"</code> before terminating:</p>

<pre>
Universe bear hatchery Hello. World!.
 Powers   marshy marshy snowmelt
</pre>

	<p>The <a href="https://jeffreymbinder.net/misc/hs/hstut.html">original tutorial</a> is available to help learn the language more fully.</p>

	<h2>This Debugger</h2>

	<p>Homespring programs, while simple and elegant, can be a challenge to write due to the lack of rivers in most text editors.
		This debugger was created by <a href="https://www.iamcal.com/">Cal Henderson</a> to allow interactive visualiztion of a program's river system,
		without having to install or compile any complex software. Under the good it uses the
		<a href="https://github.com/iamcal/homespring.js">homespring.js</a> interpreter, currently the most complete
		<a href="/interpreters">interpreter implementation</a>.</p>


</main>

<?php include 'include/footer.php'; ?>
