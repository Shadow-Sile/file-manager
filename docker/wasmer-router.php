<?php

$publicPath = __DIR__.'/public';
$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$file = realpath($publicPath.$uri);

if ($file !== false && str_starts_with($file, $publicPath.'/') && is_file($file)
    && ! str_contains($uri, '/.') && strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'php') {
    return false;
}

require $publicPath.'/index.php';
