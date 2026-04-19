<?php
// Top navigation strip (brand + tabs). Used on every page. Set $current to
// one of 'debugger' | 'interpreters' | 'about' before including to highlight
// the matching tab.
$current = isset($current) ? $current : '';
$tabs = [
	['key' => 'debugger',     'label' => 'Debugger',     'href' => '/'],
	['key' => 'interpreters', 'label' => 'Interpreters', 'href' => '/interpreters'],
	['key' => 'about',        'label' => 'About',        'href' => '/about'],
];
?>
<div id="top-nav">
	<span class="brand"><a href="/"><span class="brand-logo"><?= file_get_contents(__DIR__ . '/../www/river.svg') ?></span><span class="brand-text">Homespring.cloud</span></a></span>
	<div class="nav-tabs">
<?php foreach ($tabs as $tab): ?>
		<a class="nav-tab<?= $tab['key'] === $current ? ' active' : '' ?>" href="<?= $tab['href'] ?>"><?= htmlspecialchars($tab['label']) ?></a>
<?php endforeach; ?>
	</div>
</div>
