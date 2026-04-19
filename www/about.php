<?php
	$title = 'About';
	$current = 'about';
	include '../include/header.php';
?>
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

<?php include '../include/nav.php'; ?>

<main>
	<h1>About Homespring.cloud</h1>

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

	<p>There have been three language specifications over the last 25 years:</p>
	<ul>
		<li><a href="/homespring.js/docs/Homespring-Official-Language-Standard.pdf">Jeff Binder's Official 2003 Spec</a></li>
		<li><a href="/homespring.js/docs/Homespring-Proposed-Language-Standard.pdf">Joe Neeman's Proposed 2005 Spec</a></li>
		<li><a href="/homespring.js/docs/Homespring-Updated-Language-Standard.pdf">Cal Henderson's Updated 2026 Spec</a></li>
	</ul>


	<h2>This Debugger</h2>

	<p>Homespring programs, while simple and elegant, can be a challenge to write due to the lack of rivers in most text editors.
		This debugger was created by <a href="https://www.iamcal.com/">Cal Henderson</a> to allow interactive visualiztion of a program's river system,
		without having to install or compile any complex software. Under the hood it uses the
		<a href="https://github.com/iamcal/homespring.js">homespring.js</a> interpreter, currently the most complete
		<a href="/interpreters">interpreter implementation</a>.</p>

	<p>The source code for this website is <a href="https://github.com/iamcal/homespring.cloud">available on github</a>.</p>


	<h2>Futher Reading</h2>

	<p>You can find links to all of the known implementations on the <a href="/interpreters">interpreters page</a>.</p>

	<p>The <a href="https://esolangs.org/wiki/Homespring">Homespring page on the Esolang Wiki</a> has some basic information about the language.</p>

	<p>Jeff Binder wrote a <a href="https://jeffreymbinder.net/208/homespring">short history of the language</a> in 2018.</p>

	<p>Try It Online has an <a href="https://tio.run/#homespring">online Homespring version</a>, though it always seems to time out after 60 seconds with no output.</p>

</main>

<?php include '../include/footer.php'; ?>
