<?php

declare(strict_types=1);

use Bugsnag\Client;
use Bugsnag\Handler;
use Dotenv\Dotenv;

Dotenv::create(realpath(__DIR__.'/..'))->safeload();

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

        // save for bugsnag_error()
        global $__bugsnag;
        $__bugsnag = $bugsnag;
    } else {
        // don't show any error messages
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
    }
}

if (!function_exists('bugsnag_error')) {
    function bugsnag_error($name, $message = null, array $metaData = null, $severity = null)
    {
        global $__bugsnag;

        if (isset($__bugsnag)) {
            $report = \Bugsnag\Report::fromNamedError($__bugsnag->getConfig(), $name, $message);
            $report->addMetaData($metaData);
            $report->setSeverity($severity);
            $__bugsnag->notify($report);
        }
    }
}

function isValidURL($url): bool
{
    // url must be at least 4 characters (https://en.wikipedia.org/wiki/Single-letter_second-level_domain)
    if (strlen($url) < 4) {
        //var_dump('url must be at least 4 characters');
        return false;
    }

    // don't allow dot files
    if (strpos($url, '.') === 0) {
        //var_dump("don't allow dot files");
        return false;
    }

    // don't allow query strings
    if (strpos($url, '?') === 0) {
        //var_dump("don't allow query strings");
        return false;
    }

    // check if url starts with a domain name
    $fullURL = "http://" . preg_replace('#^https?://#', '', $url);
    $parsedURL = parse_url($fullURL);
    if (isset($parsedURL['host'])) {
        // URL must have a tld
        if (strpos($parsedURL['host'], '.') === false) {
            //var_dump('no domain');
            return false;
        } else {
            $domainParts = explode('.', $parsedURL['host']);
            $tld = end($domainParts);

            if (!is_numeric($tld)) { // allow ip numbers for local/docker testing
                // tld must be at least 2 characters
                if (strlen($tld) < 2) {
                    //var_dump('too short tld');
                    return false;
                } else {
                    // don't allow (common) file extensions as tld
                    if (in_array($tld, [
                        'html',
                        'htm',
                        'php',
                        'php3',
                        'rar',
                    ])) {
                        //var_dump('tld is file extension');
                        return false;
                    }
                }
            }
        }
    }

    return true;
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
    $image = str_replace('cls-', 'cls-'.$hash.'-', $image);

    echo $image;
}
