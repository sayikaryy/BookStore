<?php

$publicPath = __DIR__ . '/public';

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// If the requested URI is a static file, serve it reliably with proper headers
if ($uri !== '/' && file_exists($publicPath . $uri) && !str_ends_with($uri, '.php')) {
    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
    ];
    $ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
        header('Content-Length: ' . filesize($publicPath . $uri));
        header('Access-Control-Allow-Origin: *');
        header('Cache-Control: public, max-age=86400');
        readfile($publicPath . $uri);
        exit;
    }
    return false;
}

require_once $publicPath . '/index.php';
