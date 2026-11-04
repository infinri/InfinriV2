<?php

declare(strict_types=1);

// Original Infinri namespace integration check. Run with PHP, not a web request.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
$loader = require $root . '/vendor/autoload.php';
$classes = require $root . '/vendor/composer/autoload_classmap.php';
$checked = 0;

foreach ($classes as $class => $file) {
    if (!str_starts_with($file, $root . '/src/')) {
        continue;
    }

    if (!str_starts_with($class, 'Infinri\\')) {
        throw new RuntimeException('Unexpected application namespace: ' . $class);
    }

    if (!class_exists($class) && !interface_exists($class) && !trait_exists($class)) {
        throw new RuntimeException('Autoload failed: ' . $class);
    }

    $checked++;
}

if ($checked === 0) {
    throw new RuntimeException('No application classes found. Regenerate the autoloader.');
}

// Exercise dynamic aliases as well as PSR-4/classmap loading.
foreach (['kirby', 'page', 'field', 'Infinri\\Cms\\Field'] as $alias) {
    if (!class_exists($alias)) {
        throw new RuntimeException('Alias failed: ' . $alias);
    }
}

$page = new Infinri\Cms\Page(['slug' => 'namespace-check', 'content' => ['title' => 'Infinri']]);
if ($page->title()->value() !== 'Infinri') {
    throw new RuntimeException('Content field integration failed.');
}

echo "Namespace check passed: {$checked} application classes, dynamic aliases and content fields.\n";
