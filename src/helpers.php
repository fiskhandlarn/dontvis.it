<?php

declare(strict_types=1);

use Dotenv\Dotenv;

Dotenv::create(realpath(__DIR__ . '/..'))->safeload();

function ltrimword($str, $word)
{
    // https://stackoverflow.com/a/4517270/1109380
    if (substr($str, 0, strlen($word)) == $word) {
        $str = substr($str, strlen($word));
    }

    return $str;
}

function require_image($imagePath)
{
    if (defined('DEBUG') && DEBUG) {
        echo '<!-- '.esc_html($imagePath).' -->'.PHP_EOL;
    }

    $image = file_get_contents($imagePath);

    $hash = md5($imagePath);
    $image = str_replace('cls-', 'cls-' . $hash . '-', $image);

    echo $image;
}
