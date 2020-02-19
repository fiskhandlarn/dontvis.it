<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Bugsnag\Client;
use Bugsnag\Handler;

Dotenv::create(realpath(__DIR__ . '/..'))->safeload();

if (env('DEBUG')) {
    // show all error messages
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    if (env('BUGSNAG_API_KEY', false)) {
        // let bugsnag handle all errors
        $bugsnag = Client::make(env('BUGSNAG_API_KEY'));
        $bugsnag->setErrorReportingLevel(E_ALL);
        Handler::register($bugsnag);
    } else {
        // don't show any error messages
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
    }
}

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
