<?php
// Axe Universal Viewer — Directory listing backend
// Author: David M. Anderson
// Built with AI assistance (Claude, Anthropic)

header('Content-Type: application/json');

$root = $_SERVER['DOCUMENT_ROOT'];

// Confine listing to this subtree. Default is the whole web root (the original
// behavior); tighten it to a subdirectory to scope the viewer and avoid
// exposing the site's full file inventory, e.g.:
//   $confine = $root . '/files';
$confine = $root;

$path = isset($_GET['url']) ? $_GET['url'] : '';
$path = '/' . ltrim($path, '/');

// Reject obvious traversal early. The realpath containment check below is the
// real guard; this just rejects malformed input cheaply.
if (strpos($path, '..') !== false) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid path']);
    exit;
}

$dir = realpath($root . $path);
$confineReal = realpath($confine);

if ($dir === false || $confineReal === false || !is_dir($dir)) {
    http_response_code(404);
    echo json_encode(['error' => 'Directory not found']);
    exit;
}

// Containment: the resolved target must sit inside the confine root. realpath
// has already resolved any symlinks, so this also blocks a symlink that points
// outside the tree.
if ($dir !== $confineReal && strpos($dir, $confineReal . DIRECTORY_SEPARATOR) !== 0) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$items = [];

foreach (scandir($dir) as $f) {
    if (!$f || $f[0] === '.') continue;

    $full = "$dir/$f";

    if (is_dir($full)) {
        $items[] = [
            'name' => $f,
            'type' => 'folder',
            'modified' => filemtime($full)
        ];
    } else {
        $items[] = [
            'name' => $f,
            'type' => 'file',
            'size' => filesize($full),
            'modified' => filemtime($full)
        ];
    }
}

usort($items, function($a, $b) {
    if ($a['type'] !== $b['type']) {
        return $a['type'] === 'folder' ? -1 : 1;
    }
    return strcasecmp($a['name'], $b['name']);
});

echo json_encode(['items' => $items]);
