<?php

// Load Composer dependencies.
require_once __DIR__ . '/vendor/autoload.php';

// Initialize Timber.
Timber\Timber::init();

// Define constants.
define("MY_THEME_INC", get_template_directory() . "/includes");

// Auto-load all PHP files in /includes and its subfolders.
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(MY_THEME_INC),
);
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === "php") {
        require_once $file->getPathname();
    }
}