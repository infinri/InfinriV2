This is a test: <?= uniqid() ?>

<?php

if (Str::contains($page->slug(), '-auth')) {
	$infinri->request()->auth();
}

if (Str::contains($page->slug(), '-cookie')) {
	Cookie::get('foo');
}

if (Str::contains($page->slug(), '-session')) {
	$infinri->session();
}
