<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Dontvisit\DBHandler;
use Dontvisit\FirewallHandler;
use Dontvisit\Parser;
use eftec\bladeone\BladeOne;
use Illuminate\Support\Str;

if (!isset($_SERVER['REQUEST_SCHEME']) || !isset($_SERVER['HTTP_HOST']) || !isset($_SERVER['REQUEST_URI'])) {
    // cli, bail!
    die("This script must be run through a web server\n");
}

if (!ob_start('ob_gzhandler')) {
    //gzip-e-di-doo-da
    ob_start();
}

$fh = new FirewallHandler();
$fh->begin();

$currentVersion = trim(file(__DIR__ . '/../CHANGELOG.md')[2] ?? '', "#\n ");

define('ROOT_URL', $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST']);

$requestURI = $_SERVER['REQUEST_URI']; // use whole request URI instead of query parameter u to capture ? and & in given URL
$requestURI = ltrim($requestURI, '/'); // remove beginning slash added by nginx(?)
$requestURI = ltrimword($requestURI, 'index'); // /index?u=https://aftonbladet.se/bil/a/XwMlbg/rishogen-blev-lyxbil
$requestURI = ltrimword($requestURI, '.php');
$requestURI = ltrim($requestURI, '/'); // /index.php/https://aftonbladet.se/bil/a/XwMlbg/rishogen-blev-lyxbil
$requestURI = ltrimword($requestURI, '?u=');

$url = urldecode($requestURI);

// remove NUL and newlines
$url = str_replace(["\r", "\n", "\0"], "", $url);

$hasURL = !empty($url);

$blade = new BladeOne(
    __DIR__.'/../resources/views',
    __DIR__.'/../storage/views',
    BladeOne::MODE_AUTO
);
$blade->setOptimize(false); // keep whitespace

if ($hasURL) {
    // --- begin URL validation

    if ($url === 'sitemap.xml') {
        $stat = stat(__DIR__);
        header('Content-Type: application/xml');
        echo $blade->run('sitemap', ['lastmod' => date('Y-m-d', $stat['mtime'])]);
    } elseif (isValidURL($url)) {
        // don't crawl yourself
        if (strpos($url, $_SERVER['HTTP_HOST']) !== false) {
            header('Location: '.ROOT_URL.'/', true, 301);
            die();
        }

        // Remove scheme from bookmarklet and direct links.
        $articlePermalinkURL = preg_replace('#^https?://#', '', $url);

        $permalinkWithoutScheme = $_SERVER['HTTP_HOST'].'/'.$articlePermalinkURL;
        $permalink = $_SERVER['REQUEST_SCHEME'].'://'.$permalinkWithoutScheme;

        // redirect to permalink if current address isn't the same as the wanted permalink
        if (urldecode(ltrim($_SERVER['REQUEST_URI'], '/')) !== $articlePermalinkURL) {
            header('Location: '.$permalink, true, 303);
            die();
        }

        // --- begin article fetching/parsing

        $db = new DBHandler();
        list($title, $body, $urlFromDB) = $db->read($articlePermalinkURL);

        if (!$body) {
            // no cache, let's fetch the article

            $fetchSuccessful = false;
            $lastErrorMessage = '';

            // User agent switcheroo
            $userAgents = [];

            // DN seems to restrict content if crawled from Google
            if (stripos($url, 'dn.se') === false) {
                $userAgents[] = "User-Agent: Mozilla/5.0 (compatible, Googlebot/2.1, +http://www.google.com/bot.html)\r\n";
            }

            $userAgents[] = "Mozilla/5.0 (compatible, Yahoo! Slurp, http://help.yahoo.com/help/us/ysearch/slurp)\r\n";
            $userAgents[] = "Mozilla/5.0 (compatible, bingbot/2.0, +http://www.bing.com/bingbot.htm)\r\n";
            $userAgents[] = "Baiduspider+(+http://www.baidu.com/search/spider.htm)  \r\n";

            // divide the timeout so that we can make all the requests
            $timeout = ini_get('default_socket_timeout');
            $timeoutPerFetch = (int)floor($timeout / count($userAgents) / 2);

            // try both ssl and non-ssl (ssl first to avoid mixed content)
            foreach (['https://', 'http://'] as $scheme) {
                $url = $scheme.$articlePermalinkURL;

                $p = new Parser($url);

                foreach ($userAgents as $UA) {
                    if ($p->fetch($UA, $timeoutPerFetch)) {
                        $p->parse();
                        if ($p->readabilitify()) {
                            $title = $p->title;
                            $body = $p->body;

                            if ($body) {
                                // save potentially new url from followed redirect (see Parser::fetch())
                                $url = $p->url();

                                // save to db (non-prettified)
                                $db->cache($articlePermalinkURL, $title, $body, $url, $UA);

                                break;
                            } else {
                                $db->log($url, 'Empty body from Readability', $UA);
                            }
                        } else {
                            $db->log($url, 'Unable to parse with Readability', $UA);
                        }
                    } else {
                        if (count($p->fetchErrors) > 0) {
                            $db->log($url, implode("\n", $p->fetchErrors), $UA);
                        }
                    }
                }

                if ($body) {
                    // content found, bail!
                    $fetchSuccessful = true;
                    break;
                }
            }

            // restore timeout
            ini_set('default_socket_timeout', (string)$timeout);

            if (!$fetchSuccessful) {
                // don't notify if full URL is filename-like
                $fullURL = "http://" . preg_replace('#^https?://#', '', $url);
                $parsedURL = parse_url($fullURL);
                if (
                    strlen(trim($parsedURL['path'] ?? '', '/')) !== 0 || // URL has domain and path
                    strpos($parsedURL['host'] ?? '', 'www') === 0 // URL begins with www and is probably not a filename
                ) {
                    bugsnag_error('unsuccessful_fetch', null, compact('url') + ['last_fetch_errors' => $p->fetchErrors], 'info');
                }
            }
        } else {
            // use the URL that was successful when fetched
            $url = $urlFromDB;
        }

        if ($body) {
            $excerpt = Str::words(trim(preg_replace('/\s+/', ' ', strip_tags($body))), 100);

            // prettify for display
            $body = Parser::prettify($body, $url);

            if (!$title) {
                $title = $url;
            }

            $url = htmlentities($url, ENT_SUBSTITUTE);
            echo $blade->run('article', compact('title', 'body', 'excerpt', 'url', 'articlePermalinkURL', 'permalink', 'permalinkWithoutScheme', 'currentVersion'));
        } else {
            $url = htmlentities($url, ENT_SUBSTITUTE);
            $randomURLs = $db->randomURLs(1);
            echo $blade->run('article-notfound', ['title' => $url, 'randomURL' => count($randomURLs) ? array_pop($randomURLs) : false] + compact('articlePermalinkURL', 'url', 'currentVersion'));
        }
    } else {
        $fh->prevent($_SERVER['REMOTE_ADDR']);
        header("HTTP/1.0 404 Not Found");
        $url = htmlentities($url, ENT_SUBSTITUTE);
        echo $blade->run('404', ['title' => $url, 'articlePermalinkURL' => false] + compact('currentVersion'));
    }
} else {
    $db = new DBHandler();
    $topURLs = $db->topURLs();
    $latestURLs = $db->latestURLs();

    echo $blade->run('index', ['articlePermalinkURL' => false] + compact('currentVersion', 'topURLs', 'latestURLs'));
}
