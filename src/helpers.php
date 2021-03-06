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

    // don't allow urls starting with punctuation
    if (ctype_punct($url[0])) {
        //var_dump("url starts with punctuation");
        return false;
    }

    // check if url starts with a domain name
    $fullURL = "http://" . preg_replace('#^https?://#', '', $url);
    $parsedURL = parse_url($fullURL);
    if (!isset($parsedURL['host'])) {
        //var_dump('no host found');
        return false;
    }

    // URL must have a tld
    if (strpos($parsedURL['host'], '.') === false) {
        //var_dump('no domain');
        return false;
    }

    $host = $parsedURL['host'];

    if (!is_numeric(str_replace('.', '', $host))) { // allow ip numbers for local/docker testing
        $domainParts = explode('.', $host);
        $tld = end($domainParts);

        if (preg_match('/[0-9]/', $tld) === 1 && stripos($tld, 'xn-') === false) {
            // tld shouldn't contain numbers (except for internationalized tld's, those are ok)
            //var_dump('tld contains numbers');
            return false;
        }

        if (strlen($tld) < 2) {
            // tld must be at least 2 characters
            //var_dump('too short tld');
            return false;
        }

        if (ctype_punct(substr($tld, -1))) {
            // don't allow tlds ending with punctuation
            //var_dump("tld ends with punctuation");
            return false;
        }

        if (preg_match("#[{}|\\\\^~\\[\\]`]+#", $tld) === 1) {
            // tld shouldn't contain any of these: { } | \ ^ ~ [ ] `
            // https://perishablepress.com/stop-using-unsafe-characters-in-urls/
            //var_dump('tld contains invalid characters');
            return false;
        }

        // don't allow (common) file extensions as tld
        if (in_array(mb_strtolower($tld), [
            'access',
            'act',
            'action',
            'api',
            'ashx',
            'asp',
            'aspx',
            'aspx',
            'axd',
            'back',
            'backup',
            'bak',
            'boot',
            'cellsprint',
            'cfg',
            'cfm',
            'cgi',
            'conf',
            'config',
            'core',
            'css',
            'dat',
            'db',
            'debug',
            'dist',
            'dyn',
            'ear',
            'exe',
            'fcgi',
            'gradle',
            'gz',
            'htm',
            'html',
            'icns',
            'ico',
            'icox',
            'inc', // https://icannwiki.org/.inc -> https://ntldstats.com/tld/inc
            'ini',
            'java', // https://icannwiki.org/.java -> https://ntldstats.com/tld/java
            'jenkinsfile',
            'jpg',
            'js',
            'jsa',
            'json',
            'jsp',
            'jspa',
            'jws',
            'key',
            'listprint',
            'local',
            'lock',
            'log',
            'lproj',
            'markdown',
            'mdx',
            'mf',
            'mvc',
            'nsf',
            'old',
            'orig',
            'pac',
            'pbxproj',
            'pem',
            'php',
            'php-eb',
            'php3',
            'php4',
            'php5',
            'pid', // https://icannwiki.org/.pid -> https://ntldstats.com/tld/pid
            'platform',
            'plist',
            'plx',
            'png',
            'po',
            'policy',
            'rar',
            'rar',
            'rb',
            'rdoc',
            'save', // https://icannwiki.org/.save -> https://ntldstats.com/tld/save
            'shm',
            'show_query_columns',
            'showsource',
            'sitemap',
            'sql',
            'swf',
            'swp',
            'tar',
            'tgz',
            'toml',
            'ts',
            'txt',
            'txtx',
            'vm',
            'wadl',
            'xcconfig',
            'xcworkspace',
            'xcworkspacedata',
            'xml',
            'xsql',
            'xz',
            'yaml',
            'yml',
            'zip', // https://icannwiki.org/.zip -> https://ntldstats.com/tld/zip
        ])) {
            //var_dump('tld is file extension');
            return false;
        }

        if ($tld === "py") {
            // .py has second level domains (https://en.wikipedia.org/wiki/.py),
            // but could also be attempts at accessing python files
            if (count($domainParts) >= 2) {
                $domain = join('.', array_slice($domainParts, -2, 2));
                if (!in_array($domain, [
                    'com.py',
                    'coop.py',
                    'edu.py',
                    'gov.py',
                    'mil.py',
                    'net.py',
                    'nic.py',
                    'org.py',
                    'una.py',
                ])) {
                    //var_dump('tld is file extension');
                    return false;
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
