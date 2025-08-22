<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Load Composer dependencies.
require_once __DIR__ . '/vendor/autoload.php';

// Define constants.
define("THEME_INC", get_template_directory() . "/includes");

// Auto-load all PHP files in /includes and its subfolders.
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(THEME_INC),
);
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === "php") {
        require_once $file->getPathname();
    }
}