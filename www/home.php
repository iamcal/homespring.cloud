<?php
	$current = 'debugger';
	include 'include/header.php';
?>
<script src="homespring.js/lib/homespring.js?v=2"></script>
<style>

/* Debugger-specific: pin the body to the viewport so the tree pane can
   fill the remaining height without scrolling the whole page. */
html, body {
	height: 100%;
	overflow: hidden;
}

/* ---- Debugger controls toolbar (second row under the top nav) ---- */

#debug-toolbar {
	height: var(--toolbar-h);
	background: var(--surface);
	border-bottom: 1px solid var(--border);
	display: flex;
	align-items: center;
	padding: 0 16px;
	gap: 8px;
}

#debug-toolbar button {
	background: var(--surface2);
	color: var(--text);
	border: 1px solid var(--border);
	border-radius: 6px;
	padding: 6px 14px;
	font-size: 13px;
	font-family: var(--font-sans);
	cursor: pointer;
	display: flex;
	align-items: center;
	gap: 5px;
	transition: background 0.15s, border-color 0.15s;
}

#debug-toolbar button:hover:not(:disabled) {
	background: var(--border);
	border-color: var(--accent);
}

#debug-toolbar button.active {
	background: var(--accent);
	color: var(--bg);
	border-color: var(--accent);
}

#debug-toolbar button:disabled {
	opacity: 0.4;
	cursor: not-allowed;
}

#debug-toolbar button:disabled .btn-icon {
	filter: grayscale(1);
}

#debug-toolbar button .btn-icon {
	font-size: 13px;
	line-height: 1;
}

#debug-toolbar .speed-control {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 0 4px;
}

#debug-toolbar .speed-icon {
	font-size: 14px;
	user-select: none;
	line-height: 1;
}

#debug-toolbar #speed-slider {
	width: 120px;
	cursor: pointer;
	accent-color: var(--accent);
}

#debug-toolbar .tick-counter {
	margin-left: auto;
	font-family: var(--font-mono);
	font-size: 13px;
	color: var(--text-dim);
}

/* ---- Layout ---- */

#main {
	display: flex;
	flex-direction: column;
	/* Leave room for the top nav and the debug toolbar. */
	height: calc(100% - 2 * var(--toolbar-h));
}

#top-row {
	display: flex;
	flex: 0 0 40%;
	min-height: 200px;
	border-bottom: 2px solid var(--border);
}

#bottom-row {
	flex: 1 1 60%;
	min-height: 200px;
	position: relative;
}

/* ---- Pane chrome ---- */

.pane {
	display: flex;
	flex-direction: column;
	overflow: hidden;
}

.pane-header {
	height: 32px;
	min-height: 32px;
	background: var(--surface);
	border-bottom: 1px solid var(--border);
	display: flex;
	align-items: center;
	padding: 0 12px;
	font-size: 12px;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	color: var(--text-dim);
	user-select: none;
}

.pane-body {
	flex: 1;
	overflow: auto;
	background: var(--bg);
}

/* ---- Source pane ---- */

#source-pane {
	flex: 0 0 50%;
	border-right: 2px solid var(--border);
}

/* The textarea provides its own scrollbar; the wrapping .pane-body
   shouldn't add a second one. */
#source-pane .pane-body {
	overflow: hidden;
}

#source-editor {
	width: 100%;
	height: 100%;
	background: var(--bg);
	color: var(--text);
	border: none;
	padding: 12px 16px;
	font-family: var(--font-mono);
	font-size: 14px;
	line-height: 1.6;
	resize: none;
	outline: none;
	tab-size: 4;
}

#source-editor::selection {
	background: rgba(137, 180, 250, 0.3);
}

/* ---- Right column ---- */

#right-col {
	flex: 1;
	display: flex;
	flex-direction: column;
}

#input-pane {
	flex: 0 0 50%;
	border-bottom: 1px solid var(--border);
}

#output-pane {
	flex: 1;
}

#input-area {
	width: 100%;
	height: 100%;
	background: var(--bg);
	color: var(--text);
	border: none;
	padding: 12px 16px;
	font-family: var(--font-mono);
	font-size: 14px;
	line-height: 1.6;
	resize: none;
	outline: none;
}

#output-log {
	padding: 12px 16px;
	font-family: var(--font-mono);
	font-size: 14px;
	line-height: 1.6;
	white-space: pre-wrap;
	color: var(--green);
}

/* ---- Tree pane ---- */

#tree-pane {
	width: 100%;
	height: 100%;
}

#tree-pane .pane-body {
	position: relative;
}

#tree-canvas {
	width: 100%;
	height: 100%;
	display: block;
}

/* ---- Tree node styles ---- */

.tree-node circle {
	stroke-width: 2px;
	cursor: pointer;
	transition: r 0.15s;
}

.tree-node:hover circle {
	r: 10;
}

.tree-node text {
	font-family: var(--font-mono);
	font-size: 11px;
	fill: var(--text);
	pointer-events: none;
}

.tree-link {
	fill: none;
	stroke: var(--border);
	stroke-width: 1.5px;
}

.salmon-node rect {
	rx: 3;
	ry: 3;
}

.salmon-node text {
	font-family: var(--font-mono);
	font-size: 10px;
	pointer-events: none;
}

.salmon-node.dying rect {
	fill: #f38ba8 !important;
	opacity: 0.5 !important;
}

.salmon-node.dying text {
	fill: #f38ba8 !important;
}

</style>
</head>
<body>

<?php include 'include/nav.php'; ?>

<!-- Debugger controls -->
<div id="debug-toolbar">
	<button id="btn-reset" title="Reset program"><span class="btn-icon">🔄</span>Reset</button>
	<button id="btn-step" title="Step one tick"><span class="btn-icon">⤵️</span>Step</button>
	<button id="btn-play" title="Run program"><span class="btn-icon">▶️</span>Play</button>
	<button id="btn-stop" title="Pause execution" disabled><span class="btn-icon">⏸️</span>Pause</button>
	<div class="sep"></div>
	<label class="speed-control" title="Playback speed">
		<span class="speed-icon">🐢</span>
		<input id="speed-slider" type="range" min="0" max="100" value="53">
		<span class="speed-icon">🐇</span>
	</label>
	<div class="sep"></div>
	<span class="tick-counter">Tick: <span id="tick-num">0</span></span>
</div>

<!-- Main layout -->
<div id="main">

	<!-- Top row: source + input/output -->
	<div id="top-row">

		<div id="source-pane" class="pane">
			<div class="pane-header">Source</div>
			<div class="pane-body">
				<textarea id="source-editor" spellcheck="false">Universe bear hatchery Hello. World!.
 Powers   marshy marshy snowmelt</textarea>
			</div>
		</div>

		<div id="right-col">
			<div id="input-pane" class="pane">
				<div class="pane-header">Input</div>
				<div class="pane-body">
					<textarea id="input-area" spellcheck="false" placeholder="Program input..."></textarea>
				</div>
			</div>
			<div id="output-pane" class="pane">
				<div class="pane-header">Output</div>
				<div class="pane-body">
					<div id="output-log"></div>
				</div>
			</div>
		</div>

	</div>

	<!-- Bottom row: tree visualization -->
	<div id="bottom-row">
		<div id="tree-pane" class="pane" style="height:100%">
			<div class="pane-header">River System</div>
			<div class="pane-body">
				<svg id="tree-canvas"></svg>
			</div>
		</div>
	</div>

</div>

<script>

// ---- Node descriptions from the Homespring Proposed Language Standard (2005) ----

var NODE_DESCRIPTIONS = {
	'hatchery':                'When powered, creates a mature, upstream salmon named \u201Chomeless\u201D. Operates during the fish tick hatch step.',
	'hydro_power':             'Creates electricity when watered. Can be destroyed by snowmelt.',
	'snowmelt':                'Creates a snowmelt at the end of each snow tick.',
	'shallows':                'Mature salmon take two turns to pass through.',
	'rapids':                  'Young salmon take two turns to pass through.',
	'append_down':             'For each downstream salmon that did not arrive from the first child, destroy that salmon and append its name to each downstream salmon that did arrive from the first child.',
	'bear':                    'Eats mature salmon.',
	'force_field':             'Blocks water, snowmelt and salmon when powered.',
	'sense':                   'Blocks electricity when mature salmon are present.',
	'clone':                   'For each salmon, create a young, downstream salmon with the same name.',
	'young_bear':              'Eats every other mature salmon (the first mature salmon gets eaten, the second one doesn\u2019t, etc.). Young salmon are moved to the beginning of the list because they don\u2019t have to take the time to evade the bear.',
	'bird':                    'Eats young salmon.',
	'upstream_killing_device': 'When powered and if it contains more than one child, kills all the salmon in the last child.',
	'waterfall':               'Blocks upstream salmon.',
	'universe':                'If destroyed by a snowmelt, the program terminates. The program is terminated in the miscellaneous tick following the snow tick in which the Universe is destroyed.',
	'powers':                  'Generates power.',
	'marshy':                  'Snowmelts take two turns to pass through.',
	'insulated':               'Blocks power.',
	'upstream_sense':          'Blocks the flow of electricity when upstream, mature salmon are present.',
	'downstream_sense':        'Blocks the flow of electricity when downstream, mature salmon are present.',
	'evaporates':              'Blocks water and snowmelt when powered.',
	'youth_fountain':          'Makes all salmon young.',
	'oblivion':                'When powered, changes the name of each salmon to \u201C\u201D. Can be destroyed by snowmelt.',
	'pump':                    'Very blocks salmon unless powered.',
	'range_sense':             'Blocks electricity when mature salmon are here or upstream.',
	'fear':                    'Very blocks salmon when powered.',
	'reverse_up':              'For each downstream salmon that arrived from the second child, move it to the first child unless it is prevented from moving there.',
	'reverse_down':            'For each downstream salmon that arrived from the first child, move it to the second child unless it is prevented from moving there.',
	'time':                    'Makes all salmon mature.',
	'lock':                    'Very blocks downstream salmon and blocks snowmelt when powered.',
	'inverse_lock':            'Very blocks downstream salmon and blocks snowmelt when not powered.',
	'young_sense':             'Blocks electricity when young salmon are present.',
	'switch':                  'Blocks electricity unless mature salmon are present.',
	'young_switch':            'Blocks electricity unless young salmon are present.',
	'narrows':                 'Very blocks salmon if another salmon is present.',
	'append_up':               'For each downstream salmon that did not arrive from the first child, destroy that salmon and append its name to each upstream salmon.',
	'young_range_sense':       'Blocks electricity when young salmon are here or upstream.',
	'net':                     'Very blocks mature salmon.',
	'force_down':              'For each downstream salmon that arrived from the first child, move it to the second child unless it is prevented from moving there.\nAlso blocks upstream salmon from moving to the last child.',
	'force_up':                'For each downstream salmon that arrived from the second child, move it to the first child unless it is prevented from moving there.\nAlso blocks upstream salmon from moving to the first child.',
	'spawn':                   'When powered, makes all salmon upstream spawn.',
	'power_invert':            'This node is powered if and only if none of its children are powered. Can be destroyed by snowmelt.',
	'current':                 'Very blocks young salmon.',
	'bridge':                  'If destroyed by snowmelt, blocks snowmelt and water and very blocks salmon.',
	'split':                   'Splits each salmon into a new salmon for each letter in the original salmon\u2019s name. The original salmon are destroyed.',
	'range_switch':            'Blocks electricity unless mature salmon are here or upstream.',
	'young_range_switch':      'Blocks electricity unless young salmon are here or upstream.'
};

// Spring description verbatim from section 5 of the spec
var SPRING_DESCRIPTION = 'The name of the node determines its behaviour; a list of reserved names is in section 7.2.\nEvery node whose name is not a reserved name is a spring: it creates water.';

// ---- State ----

var program = null;
var running = false;
var runInterval = null;
// ANIM_DURATION is the ms spent animating salmon movement; PAUSE_DURATION is
// the quiet ms between animations when running. Together they set the total
// ms per tick when playing. Both are recomputed by updateSpeed() from the
// toolbar's speed slider.
var ANIM_DURATION = 350;
var PAUSE_DURATION = 100;

// ---- Persistent layout state ----

var layoutData = null;   // { nodeList, nodeMap, maxDepth, depthCounts, W, H }
var prevSalmonPos = {};  // salmonUid -> nodeUid (where each salmon was last tick)
var prevSalmonState = {}; // salmonUid -> { name, age, direction }
var animating = false;   // true while animation phases are running
var deadSalmon = [];     // salmon that died last tick, shown until next step

// ---- SVG layer groups (persistent across ticks) ----

var linkGroup = null;
var nodeGroup = null;
var salmonGroup = null;

// ---- DOM refs ----

var srcEditor = document.getElementById('source-editor');
var inputArea = document.getElementById('input-area');
var outputLog = document.getElementById('output-log');
var tickNum = document.getElementById('tick-num');
var btnReset = document.getElementById('btn-reset');
var btnStep = document.getElementById('btn-step');
var btnPlay = document.getElementById('btn-play');
var btnStop = document.getElementById('btn-stop');
var speedSlider = document.getElementById('speed-slider');
var treeSvg = document.getElementById('tree-canvas');

// ---- Speed control ----
// Slider: 0 = turtle (slow, 900ms/tick), 100 = rabbit (fast, 50ms/tick).
// The default value of 53 lands on ~450ms/tick, matching the original hand-
// tuned ratio (350ms animation + 100ms pause). Both durations scale with the
// total so the feel stays proportional at every speed.
var TICK_MS_SLOW = 900;
var TICK_MS_FAST = 50;
var ANIM_FRACTION = 350 / 450;
var PAUSE_FRACTION = 100 / 450;

function updateSpeed(){
	var s = parseInt(speedSlider.value, 10);
	var total = TICK_MS_SLOW - (s / 100) * (TICK_MS_SLOW - TICK_MS_FAST);
	ANIM_DURATION = total * ANIM_FRACTION;
	PAUSE_DURATION = total * PAUSE_FRACTION;
}
speedSlider.addEventListener('input', updateSpeed);
updateSpeed();

// ---- Program management ----

function createProgram(){
	var src = srcEditor.value;
	outputLog.textContent = '';
	prevSalmonPos = {};
	deadSalmon = [];
	try {
		program = new HS.Program(src);
	} catch(e) {
		outputLog.textContent = 'Parse error: ' + e.message;
		program = null;
		layoutData = null;
		clearSvg();
		updateTickDisplay();
		updateButtonStates();
		return;
	}

	program.input = inputArea.value || null;

	program.onOutput = function(str){
		outputLog.textContent += str;
	};

	computeLayout();
	renderTree();
	renderSalmonStatic();
	updateTickDisplay();
	updateButtonStates();
}

function resetProgram(){
	stopRunning();
	createProgram();
}

// Single source of truth for toolbar button enable/disable state. Call this
// any time `running`, `animating`, `program`, or `program.terminated` change.
function updateButtonStates(){
	var terminated = !program || program.terminated;
	btnPlay.disabled = running || terminated;
	btnStop.disabled = !running;
	btnStep.disabled = running || animating || terminated;
	// Reset is only meaningful once at least one tick has been executed —
	// a fresh program (initial load, after reset, or after a source edit)
	// has nothing to reset to.
	btnReset.disabled = !program || program.tickNum === 0;
}

function doTick(onComplete){
	if (!program || program.terminated || animating) return;
	if (inputArea.value){
		program.input = inputArea.value;
		inputArea.value = '';
	}

	snapshotSalmon();
	program.tick();
	updateNodeVisuals();
	updateTickDisplay();

	animating = true;
	deadSalmon = [];
	updateButtonStates();

	renderSalmonAnimated(function(){
		animating = false;
		updateButtonStates();
		if (onComplete) onComplete();
	});
}

function stepProgram(){
	doTick();
}

function startRunning(){
	if (!program || program.terminated) return;
	running = true;
	btnPlay.classList.add('active');
	srcEditor.disabled = true;
	updateButtonStates();

	function playTick(){
		if (!running || !program || program.terminated){
			stopRunning();
			return;
		}
		doTick(function(){
			if (running) runInterval = setTimeout(playTick, PAUSE_DURATION);
		});
	}
	playTick();
}

function stopRunning(){
	running = false;
	if (runInterval){
		clearInterval(runInterval);
		runInterval = null;
	}
	btnPlay.classList.remove('active');
	srcEditor.disabled = false;
	updateButtonStates();
}

function updateTickDisplay(){
	tickNum.textContent = program ? program.tickNum : 0;
}

// ---- Toolbar events ----

btnReset.addEventListener('click', resetProgram);
btnStep.addEventListener('click', stepProgram);
btnPlay.addEventListener('click', startRunning);
btnStop.addEventListener('click', stopRunning);

// Reparse on source change (debounced)
var parseTimer = null;
srcEditor.addEventListener('input', function(){
	stopRunning();
	clearTimeout(parseTimer);
	parseTimer = setTimeout(function(){
		createProgram();
	}, 400);
});

// ---- SVG helpers ----

var SVG_NS = 'http://www.w3.org/2000/svg';

function svgEl(tag, attrs){
	var el = document.createElementNS(SVG_NS, tag);
	if (attrs){
		for (var k in attrs){
			el.setAttribute(k, attrs[k]);
		}
	}
	return el;
}

function clearSvg(){
	while (treeSvg.firstChild) treeSvg.removeChild(treeSvg.firstChild);
	linkGroup = null;
	nodeGroup = null;
	salmonGroup = null;
}

// ---- Layout computation (runs on parse and resize) ----

function computeLayout(){
	if (!program || !program.root){ layoutData = null; return; }

	var container = treeSvg.parentNode;
	var W = container.clientWidth || 800;
	var H = container.clientHeight || 400;

	var depthCounts = {};
	var nodeList = [];
	var nextLeafSlot = 0;
	var maxDepth = 0;

	// Post-order layout: leaves each claim a fresh y-slot; internal nodes
	// take the centroid of their children's slots. A non-branching chain
	// is thus a run of nodes all sharing the one slot of the eventual leaf,
	// so the river stays on a single horizontal line instead of weaving
	// up and down as other depth rows fill in.
	function layoutNode(node, depth){
		if (!depthCounts[depth]) depthCounts[depth] = 0;
		depthCounts[depth]++;
		if (depth > maxDepth) maxDepth = depth;

		var info = { node: node, depth: depth, children: [] };
		nodeList.push(info);

		if (!node.kids.length){
			info.ySlot = nextLeafSlot++;
		} else {
			var sum = 0;
			for (var i = 0; i < node.kids.length; i++){
				var child = layoutNode(node.kids[i], depth + 1);
				info.children.push(child);
				sum += child.ySlot;
			}
			info.ySlot = sum / node.kids.length;
		}
		return info;
	}

	layoutNode(program.root, 0);

	var leafCount = nextLeafSlot || 1;

	var padX = 80;
	var usableW = W - padX * 2;

	// If the per-depth column spacing gets tight, horizontal labels drawn
	// above adjacent-column nodes will collide into each other. When that
	// happens, flag renderTree() to draw labels at 45° up-right so they
	// fan out of their columns instead of stacking against each other.
	// Tilted labels rise higher above each node, so reserve extra top
	// padding so they don't clip off the top of the canvas.
	var columnSpacing = maxDepth > 0 ? usableW / maxDepth : usableW;
	var tiltLabels = columnSpacing < 60;
	var padTop = tiltLabels ? 110 : 50;
	var padBottom = 50;
	var usableH = H - padTop - padBottom;

	for (var i = 0; i < nodeList.length; i++){
		var n = nodeList[i];
		n.x = padX + (maxDepth > 0 ? (n.depth / maxDepth) * usableW : usableW / 2);
		n.y = padTop + (leafCount > 1 ? (n.ySlot / (leafCount - 1)) * usableH : usableH / 2);
	}

	var nodeMap = {};
	for (var i = 0; i < nodeList.length; i++){
		nodeMap[nodeList[i].node.uid] = nodeList[i];
	}

	layoutData = { nodeList: nodeList, nodeMap: nodeMap, maxDepth: maxDepth, depthCounts: depthCounts, W: W, H: H, tiltLabels: tiltLabels };
}

// ---- Bezier math for path animation ----

function cubicBezier(t, p0, p1, p2, p3){
	var u = 1 - t;
	return u*u*u*p0 + 3*u*u*t*p1 + 3*u*t*t*p2 + t*t*t*p3;
}

// Get the Bezier control points for the path between parent and child nodes
function getPathPoints(fromInfo, toInfo){
	var mx = (fromInfo.x + toInfo.x) / 2;
	return {
		p0x: fromInfo.x, p0y: fromInfo.y,
		p1x: mx,         p1y: fromInfo.y,
		p2x: mx,         p2y: toInfo.y,
		p3x: toInfo.x,   p3y: toInfo.y
	};
}

// Returns {x, y} at parameter t along the path between two adjacent nodes.
// parentInfo is always the node closer to root. direction determines t mapping.
// upstream: child→parent (t=0 at child, t=1 at parent)
// downstream: parent→child (t=0 at parent, t=1 at child)
function pointOnEdge(parentInfo, childInfo, t, upstream){
	// The stored Bezier goes from parent to child
	var p = getPathPoints(parentInfo, childInfo);
	// For upstream, we reverse: walk from child (t=0) to parent (t=1) means Bezier parameter = 1-t
	var bt = upstream ? (1 - t) : t;
	return {
		x: cubicBezier(bt, p.p0x, p.p1x, p.p2x, p.p3x),
		y: cubicBezier(bt, p.p0y, p.p1y, p.p2y, p.p3y)
	};
}

// ---- Snapshot salmon state before tick ----

function snapshotSalmon(){
	prevSalmonPos = {};
	prevSalmonState = {};
	if (!program) return;
	for (var i = 0; i < program.nodes.length; i++){
		var node = program.nodes[i];
		for (var s = 0; s < node.salmon.length; s++){
			var sal = node.salmon[s];
			prevSalmonPos[sal.uid] = node.uid;
			prevSalmonState[sal.uid] = {
				name: sal.name,
				age: sal.age,
				direction: sal.direction
			};
		}
	}
}

// ---- Node color helpers ----

function nodeColor(node){
	if (node.is_destroyed) return '#585b70';
	if (node.generates_power) return '#f9e2af';
	if (node.isPowered()) return '#f9e2af';
	if (node.is_watered) return '#89b4fa';
	if (node.is_snowy) return '#cdd6f4';
	return '#a6e3a1';
}

function nodeStroke(node){
	if (node.is_destroyed) return '#45475a';
	return '#313150';
}

// ---- Render static tree (links + nodes) ----

function renderTree(){
	clearSvg();
	if (!layoutData) return;

	var ld = layoutData;
	treeSvg.setAttribute('width', ld.W);
	treeSvg.setAttribute('height', ld.H);
	treeSvg.setAttribute('viewBox', '0 0 ' + ld.W + ' ' + ld.H);

	// links layer
	linkGroup = svgEl('g', { 'class': 'link-layer' });
	treeSvg.appendChild(linkGroup);

	for (var i = 0; i < ld.nodeList.length; i++){
		var n = ld.nodeList[i];
		if (n.node.parent){
			var pInfo = ld.nodeMap[n.node.parent.uid];
			if (pInfo){
				var path = svgEl('path', {
					'd': 'M' + pInfo.x + ',' + pInfo.y +
					     ' C' + ((pInfo.x + n.x) / 2) + ',' + pInfo.y +
					     ' ' + ((pInfo.x + n.x) / 2) + ',' + n.y +
					     ' ' + n.x + ',' + n.y,
					'class': 'tree-link'
				});
				linkGroup.appendChild(path);
			}
		}
	}

	// nodes layer
	nodeGroup = svgEl('g', { 'class': 'node-layer' });
	treeSvg.appendChild(nodeGroup);

	for (var i = 0; i < ld.nodeList.length; i++){
		var n = ld.nodeList[i];
		var g = svgEl('g', {
			'class': 'tree-node',
			'transform': 'translate(' + n.x + ',' + n.y + ')',
			'data-uid': n.node.uid
		});

		g.appendChild(svgEl('circle', {
			'r': 7, 'cx': 0, 'cy': 0,
			'fill': nodeColor(n.node),
			'stroke': nodeStroke(n.node)
		}));

		var labelAttrs = {
			'x': 0, 'y': -14,
			'text-anchor': ld.tiltLabels ? 'start' : 'middle',
			'fill': n.node.is_destroyed ? '#585b70' : '#cdd6f4'
		};
		if (ld.tiltLabels){
			// Rotate -45° around the text anchor (0,-14) so the label
			// extends up-and-to-the-right instead of horizontally.
			labelAttrs.transform = 'rotate(-45 0 -14)';
		}
		var label = svgEl('text', labelAttrs);
		label.textContent = n.node.name || '';
		g.appendChild(label);

		var stateLabel = svgEl('text', {
			'x': 0, 'y': 20,
			'text-anchor': 'middle',
			'font-size': '9px',
			'fill': '#7f849c'
		});
		g.appendChild(stateLabel);

		var tip = svgEl('title');
		g.appendChild(tip);

		// stash refs for fast updates
		n.svgGroup = g;
		n.svgCircle = g.querySelector('circle');
		n.svgLabel = label;
		n.svgStateLabel = stateLabel;
		n.svgTip = tip;

		nodeGroup.appendChild(g);
	}

	// salmon layer (on top)
	salmonGroup = svgEl('g', { 'class': 'salmon-layer' });
	treeSvg.appendChild(salmonGroup);

	updateNodeVisuals();
}

// ---- Update node visuals in-place (no DOM rebuild) ----

function updateNodeVisuals(){
	if (!layoutData) return;
	var ld = layoutData;

	for (var i = 0; i < ld.nodeList.length; i++){
		var n = ld.nodeList[i];
		var node = n.node;

		n.svgCircle.setAttribute('fill', nodeColor(node));
		n.svgCircle.setAttribute('stroke', nodeStroke(node));
		n.svgLabel.setAttribute('fill', node.is_destroyed ? '#585b70' : '#cdd6f4');

		var flags = [];
		if (node.is_destroyed) flags.push('\uD83D\uDCA5');
		if (node.isPowered()) flags.push('\u26A1');
		if (node.is_watered) flags.push('\uD83D\uDCA7');
		if (node.is_snowy) flags.push('\u2744\uFE0F');
		n.svgStateLabel.textContent = flags.join('');

		var tipLines = ['Node: ' + (node.name || '(unnamed)')];
		var nodeType = node.lname || node.name || 'unknown';
		tipLines.push('Type: ' + nodeType);
		var desc = NODE_DESCRIPTIONS[nodeType.replace(/ /g, '_')] || SPRING_DESCRIPTION;
		tipLines.push(desc);
		if (node.is_destroyed) tipLines.push('State: Destroyed');
		if (node.generates_power) tipLines.push('Power: Generating');
		if (node.isPowered() && !node.generates_power) tipLines.push('Powered: Yes');
		if (node.is_watered) tipLines.push('Watered: Yes');
		if (node.is_snowy) tipLines.push('Snowy: Yes');
		tipLines.push('Children: ' + node.kids.length);
		if (node.salmon && node.salmon.length) tipLines.push('Salmon here: ' + node.salmon.length);
		n.svgTip.textContent = tipLines.join('\n');
	}
}

// ---- Salmon rendering with event-driven animation ----

function makeSalmonEl(name, age, direction, nodeName){
	var isUpstream = direction === HS.const.UPSTREAM;
	var isYoung = age === HS.const.YOUNG;
	var salColor = isUpstream ? '#fab387' : '#89b4fa';
	var dirArrow = isUpstream ? '\u2192' : '\u2190';

	var sg = svgEl('g', { 'class': 'salmon-node' });

	var rect = svgEl('rect', {
		'x': 0, 'y': -8, 'height': 16,
		'fill': salColor, 'opacity': '0.25',
		'rx': 3, 'ry': 3
	});
	sg.appendChild(rect);

	var sLabel = svgEl('text', {
		'x': 4, 'y': 4,
		'fill': salColor,
		'font-weight': isYoung ? 'normal' : 'bold'
	});
	sLabel.textContent = dirArrow + ' ' + name;
	sg.appendChild(sLabel);

	var salTip = svgEl('title');
	salTip.textContent = [
		'Name: ' + name,
		'Age: ' + (isYoung ? 'Young' : 'Mature'),
		'Direction: ' + (isUpstream ? 'Upstream' : 'Downstream'),
		'At node: ' + (nodeName || '(unnamed)')
	].join('\n');
	sg.appendChild(salTip);

	// measure text
	salmonGroup.appendChild(sg);
	var bbox = sLabel.getBBox();
	rect.setAttribute('width', bbox.width + 8);
	salmonGroup.removeChild(sg);

	return sg;
}

function salmonRestPos(nodeInfo, index){
	return { x: nodeInfo.x + 20, y: nodeInfo.y + 6 + (index * 18) };
}

// Render salmon at their final (post-tick) positions, no animation
function renderSalmonStatic(){
	if (!salmonGroup || !layoutData) return;
	while (salmonGroup.firstChild) salmonGroup.removeChild(salmonGroup.firstChild);

	var ld = layoutData;

	// count live salmon per node for stacking
	var slotCounts = {};

	for (var i = 0; i < ld.nodeList.length; i++){
		var nInfo = ld.nodeList[i];
		var node = nInfo.node;
		if (!node.salmon || !node.salmon.length) continue;
		if (!slotCounts[node.uid]) slotCounts[node.uid] = 0;
		for (var s = 0; s < node.salmon.length; s++){
			var sal = node.salmon[s];
			var sg = makeSalmonEl(sal.name, sal.age, sal.direction, node.name);
			var pos = salmonRestPos(nInfo, slotCounts[node.uid]++);
			sg.setAttribute('transform', 'translate(' + pos.x + ',' + pos.y + ')');
			salmonGroup.appendChild(sg);
		}
	}

	// render dead salmon from this tick (skull + red background, persist until next step)
	for (var d = 0; d < deadSalmon.length; d++){
		var dead = deadSalmon[d];
		var nInfo = ld.nodeMap[dead.node];
		if (!nInfo) continue;
		if (!slotCounts[dead.node]) slotCounts[dead.node] = 0;
		var sg = makeDeadSalmonEl(dead, nInfo.node.name);
		var pos = salmonRestPos(nInfo, slotCounts[dead.node]++);
		sg.setAttribute('transform', 'translate(' + pos.x + ',' + pos.y + ')');
		salmonGroup.appendChild(sg);
	}
}

function makeDeadSalmonEl(dead, nodeName){
	var sg = svgEl('g', { 'class': 'salmon-node dying' });

	var rect = svgEl('rect', {
		'x': 0, 'y': -8, 'height': 16,
		'fill': '#f38ba8', 'opacity': '0.35',
		'rx': 3, 'ry': 3
	});
	sg.appendChild(rect);

	var sLabel = svgEl('text', {
		'x': 4, 'y': 4,
		'fill': '#f38ba8',
		'font-weight': dead.age === HS.const.YOUNG ? 'normal' : 'bold'
	});
	sLabel.textContent = '\uD83D\uDC80 ' + dead.name;
	sg.appendChild(sLabel);

	var tip = svgEl('title');
	tip.textContent = [
		'DEAD: ' + dead.name,
		'Killed by: ' + dead.cause,
		'At node: ' + (nodeName || '(unnamed)')
	].join('\n');
	sg.appendChild(tip);

	// measure text
	salmonGroup.appendChild(sg);
	var bbox = sLabel.getBBox();
	rect.setAttribute('width', bbox.width + 8);
	salmonGroup.removeChild(sg);

	return sg;
}

// Event-driven animated salmon rendering
function renderSalmonAnimated(onComplete){
	if (!salmonGroup || !layoutData || !program) {
		if (onComplete) onComplete();
		return;
	}

	var ld = layoutData;
	var events = program.events || [];

	// separate events into phases
	var moves = [];
	var deaths = [];
	var creates = [];
	var spawns = [];

	for (var i = 0; i < events.length; i++){
		var e = events[i];
		if (e.type === 'move') moves.push(e);
		else if (e.type === 'die') deaths.push(e);
		else if (e.type === 'create') creates.push(e);
		else if (e.type === 'spawn') spawns.push(e);
	}

	// Phase 0: Show salmon at their pre-tick positions
	while (salmonGroup.firstChild) salmonGroup.removeChild(salmonGroup.firstChild);

	// build a map of pre-tick salmon elements by uid
	var salmonEls = {};
	// count salmon per node for stacking
	var nodeSlotCount = {};

	// draw all pre-tick salmon at their old positions
	for (var uid in prevSalmonPos){
		var nodeUid = prevSalmonPos[uid];
		var nInfo = ld.nodeMap[nodeUid];
		var state = prevSalmonState[uid];
		if (!nInfo || !state) continue;

		if (!nodeSlotCount[nodeUid]) nodeSlotCount[nodeUid] = 0;
		var slot = nodeSlotCount[nodeUid]++;

		var sg = makeSalmonEl(state.name, state.age, state.direction, nInfo.node.name);
		var pos = salmonRestPos(nInfo, slot);
		sg.setAttribute('transform', 'translate(' + pos.x + ',' + pos.y + ')');
		salmonGroup.appendChild(sg);
		salmonEls[uid] = { el: sg, nodeUid: nodeUid, slot: slot };
	}

	// Pre-compute the slot each surviving salmon will occupy at its
	// destination post-tick. When multiple salmon move from one node to
	// another together, the animation interpolates each salmon's slot from
	// start to end linearly so the group stays as a vertical stack, even
	// while reordering — instead of all collapsing to slot 0.
	var endSlots = {};
	for (var i = 0; i < ld.nodeList.length; i++){
		var nInfo = ld.nodeList[i];
		var node = nInfo.node;
		if (!node.salmon) continue;
		for (var s = 0; s < node.salmon.length; s++){
			endSlots[node.salmon[s].uid] = s;
		}
	}

	// Phase 1: Animate movements
	animatePhase(moves, salmonEls, endSlots, function(){
		// Phase 2: Animate deaths (flash and fade)
		animateDeaths(deaths, salmonEls, function(){
			// Phase 3: Show final state
			renderSalmonStatic();
			animating = false;
			if (onComplete) onComplete();
		});
	});
}

function animatePhase(moves, salmonEls, endSlots, onComplete){
	if (!moves.length){
		if (onComplete) onComplete();
		return;
	}

	var ld = layoutData;
	var animations = [];

	for (var i = 0; i < moves.length; i++){
		var evt = moves[i];
		var entry = salmonEls[evt.salmon];
		if (!entry) continue;

		var fromInfo = ld.nodeMap[evt.from];
		var toInfo = ld.nodeMap[evt.to];
		if (!fromInfo || !toInfo) continue;

		var sg = entry.el;
		var endSlot = endSlots[evt.salmon];
		if (endSlot === undefined) endSlot = 0;

		var anim = buildMoveAnimation(sg, fromInfo, toInfo, entry.slot, endSlot);
		if (anim) animations.push(anim);
	}

	if (!animations.length){
		if (onComplete) onComplete();
		return;
	}

	var startTime = performance.now();
	requestAnimationFrame(function tick(now){
		var t = Math.min((now - startTime) / ANIM_DURATION, 1);
		var eased = t < 1 ? t * (2 - t) : 1;
		var anyRunning = false;

		for (var a = 0; a < animations.length; a++){
			var anim = animations[a];
			if (anim.done) continue;

			var pos;
			if (anim.segmented && anim.segments.length){
				var totalSegs = anim.segments.length;
				var rawSeg = eased * totalSegs;
				var segIdx = Math.min(Math.floor(rawSeg), totalSegs - 1);
				var segT = rawSeg - segIdx;
				if (eased >= 1){ segIdx = totalSegs - 1; segT = 1; }
				var seg = anim.segments[segIdx];
				pos = {
					x: cubicBezier(segT, seg.p0x, seg.p1x, seg.p2x, seg.p3x),
					y: cubicBezier(segT, seg.p0y, seg.p1y, seg.p2y, seg.p3y)
				};
			} else if (anim.points){
				pos = {
					x: cubicBezier(eased, anim.points.p0x, anim.points.p1x, anim.points.p2x, anim.points.p3x),
					y: cubicBezier(eased, anim.points.p0y, anim.points.p1y, anim.points.p2y, anim.points.p3y)
				};
			}

			if (pos){
				// Add the per-frame slot offset so groups move as a stack and
				// reorder smoothly rather than collapsing onto a single point.
				if (anim.startSlot !== undefined && anim.endSlot !== undefined){
					var slot = anim.startSlot + (anim.endSlot - anim.startSlot) * eased;
					pos.y += slot * 18;
				}
				anim.el.setAttribute('transform', 'translate(' + pos.x + ',' + pos.y + ')');
			}

			if (t >= 1) anim.done = true;
			else anyRunning = true;
		}

		if (anyRunning) requestAnimationFrame(tick);
		else if (onComplete) onComplete();
	});
}

function animateDeaths(deaths, salmonEls, onComplete){
	// Record dead salmon so they persist in the final render until next tick
	for (var i = 0; i < deaths.length; i++){
		var evt = deaths[i];
		var state = prevSalmonState[evt.salmon];
		if (state){
			deadSalmon.push({
				name: state.name,
				age: state.age,
				direction: state.direction,
				node: evt.node,
				cause: evt.cause
			});
		}
	}
	if (onComplete) onComplete();
}

function buildMoveAnimation(sg, fromInfo, toInfo, fromSlot, toSlot){
	var parentInfo, childInfo;
	var ox = 20, oy = 6;

	if (toInfo.node.parent && toInfo.node.parent.uid === fromInfo.node.uid){
		parentInfo = fromInfo;
		childInfo = toInfo;
	} else if (fromInfo.node.parent && fromInfo.node.parent.uid === toInfo.node.uid){
		parentInfo = toInfo;
		childInfo = fromInfo;
	} else {
		var path = findPath(fromInfo, toInfo);
		if (!path || path.length < 2) return null;
		return buildMultiSegmentAnimation(sg, path, fromSlot, toSlot);
	}

	// The bezier runs between node centers (no slot offset); the per-frame
	// y adjustment in animatePhase adds startSlot→endSlot interpolation.
	var pp = getPathPoints(parentInfo, childInfo);
	var animPoints;

	if (fromInfo === parentInfo){
		animPoints = {
			p0x: pp.p0x + ox, p0y: pp.p0y + oy,
			p1x: pp.p1x + ox, p1y: pp.p1y + oy,
			p2x: pp.p2x + ox, p2y: pp.p2y + oy,
			p3x: pp.p3x + ox, p3y: pp.p3y + oy
		};
	} else {
		animPoints = {
			p0x: pp.p3x + ox, p0y: pp.p3y + oy,
			p1x: pp.p2x + ox, p1y: pp.p2y + oy,
			p2x: pp.p1x + ox, p2y: pp.p1y + oy,
			p3x: pp.p0x + ox, p3y: pp.p0y + oy
		};
	}

	sg.setAttribute('transform',
		'translate(' + animPoints.p0x + ',' + (animPoints.p0y + fromSlot * 18) + ')');
	return {
		el: sg, points: animPoints, done: false,
		startSlot: fromSlot, endSlot: toSlot
	};
}

function findPath(fromInfo, toInfo){
	function ancestors(info){
		var path = [];
		var n = info.node;
		while (n){ path.push(n.uid); n = n.parent; }
		return path;
	}

	var aFrom = ancestors(fromInfo);
	var aTo = ancestors(toInfo);
	var ld = layoutData;

	var toSet = {};
	for (var i = 0; i < aTo.length; i++) toSet[aTo[i]] = i;

	var caIdx = -1, caUid;
	for (var i = 0; i < aFrom.length; i++){
		if (toSet[aFrom[i]] !== undefined){ caUid = aFrom[i]; caIdx = i; break; }
	}
	if (caIdx === -1) return null;

	var upPath = [];
	for (var i = 0; i <= caIdx; i++) upPath.push(ld.nodeMap[aFrom[i]]);

	var downPath = [];
	var caToIdx = toSet[caUid];
	for (var i = caToIdx - 1; i >= 0; i--) downPath.push(ld.nodeMap[aTo[i]]);

	return upPath.concat(downPath);
}

function buildMultiSegmentAnimation(sg, path, fromSlot, toSlot){
	var segments = [];
	var ox = 20, oy = 6;

	for (var i = 0; i < path.length - 1; i++){
		var a = path[i], b = path[i + 1];
		var parentInfo, childInfo;

		if (b.node.parent && b.node.parent.uid === a.node.uid){
			parentInfo = a; childInfo = b;
		} else {
			parentInfo = b; childInfo = a;
		}

		var pp = getPathPoints(parentInfo, childInfo);
		var goingDown = (a === parentInfo);

		if (goingDown){
			segments.push({
				p0x: pp.p0x + ox, p0y: pp.p0y + oy,
				p1x: pp.p1x + ox, p1y: pp.p1y + oy,
				p2x: pp.p2x + ox, p2y: pp.p2y + oy,
				p3x: pp.p3x + ox, p3y: pp.p3y + oy
			});
		} else {
			segments.push({
				p0x: pp.p3x + ox, p0y: pp.p3y + oy,
				p1x: pp.p2x + ox, p1y: pp.p2y + oy,
				p2x: pp.p1x + ox, p2y: pp.p1y + oy,
				p3x: pp.p0x + ox, p3y: pp.p0y + oy
			});
		}
	}

	var startPos = segments[0];
	sg.setAttribute('transform',
		'translate(' + startPos.p0x + ',' + (startPos.p0y + fromSlot * 18) + ')');

	return {
		el: sg, segments: segments, done: false, segmented: true,
		startSlot: fromSlot, endSlot: toSlot
	};
}

// ---- Resize handling ----

var resizeTimer = null;
window.addEventListener('resize', function(){
	clearTimeout(resizeTimer);
	resizeTimer = setTimeout(function(){
		if (program && program.root){
			computeLayout();
			renderTree();
			renderSalmonStatic();
		}
	}, 100);
});

// ---- Init ----

createProgram();

</script>
<?php include 'include/footer.php'; ?>
