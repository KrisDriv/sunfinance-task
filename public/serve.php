<?php

/**
 * Host Application using built-in php server
 *
 * php -S localhost:8000 public/serve.php
 */
chdir(__DIR__);
$filePath = realpath(ltrim($_SERVER["REQUEST_URI"], '/'));
if ($filePath && is_dir($filePath)) {
    // attempt to find an index file
    foreach (['index.php', 'index.html'] as $indexFile) {
        if ($filePath = realpath($filePath . DIRECTORY_SEPARATOR . $indexFile)) {
            break;
        }
    }
}

if ($filePath && is_file($filePath)) {

    if (str_starts_with($filePath, __DIR__ . DIRECTORY_SEPARATOR) &&
        $filePath != __DIR__ . DIRECTORY_SEPARATOR &&
        !str_starts_with(basename($filePath), '.')
    ) {
        if (strtolower(substr($filePath, -4)) == '.php') {
            // php file; serve through interpreter
            include $filePath;
        } else {
            // asset file; serve from filesystem
            return false;
        }
    } else {
        // disallowed file
        header("HTTP/1.1 404 Not Found");
        echo "404 Not Found";
    }

} else {
    include 'index.php';
}