<?php
// Axe Universal Viewer — Directory listing backend
// Author: David M. Anderson
// Built with AI assistance (Claude, Anthropic)

header('Content-Type: application/json');

$path = isset($_GET['url']) ? $_GET['url'] : '';
$path = '/' . ltrim($path, '/');

// Prevent directory traversal
if (strpos($path, '..') !== false) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid path']);
    exit;
}

$dir = $_SERVER['DOCUMENT_ROOT'] . $path;

if (!file_exists($dir) || !is_dir($dir)) {
    http_response_code(404);
    echo json_encode(['error' => 'Directory not found']);
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
