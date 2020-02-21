<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Dontvisit\DBHandler;
use Dontvisit\Parser;
use eftec\bladeone\BladeOne;
use Illuminate\Support\Str;

if (!isset($_SERVER['REQUEST_SCHEME']) || !isset($_SERVER['HTTP_HOST'])) {
    // cli, bail!
    die("This script must be run through a web server\n");
}

if (!ob_start('ob_gzhandler')) {
    //gzip-e-di-doo-da
    ob_start();
}

define('ROOT_URL', $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST']);

$requestURI = $_SERVER['REQUEST_URI']; // use whole request URI instead of query parameter u to capture ? and & in given URL
$requestURI = ltrim($requestURI, '/'); // remove beginning slash added by nginx(?)
$requestURI = ltrimword($requestURI, 'index'); // /index?u=https://aftonbladet.se/bil/a/XwMlbg/rishogen-blev-lyxbil
$requestURI = ltrimword($requestURI, '.php');
$requestURI = ltrim($requestURI, '/'); // /index.php/https://aftonbladet.se/bil/a/XwMlbg/rishogen-blev-lyxbil
$requestURI = ltrimword($requestURI, '?u=');

$url = urldecode($requestURI);

$hasURL = !empty($url);

$blade = new BladeOne(
    __DIR__.'/../resources/views',
    __DIR__.'/../storage/views',
    BladeOne::MODE_AUTO
);
$blade->setOptimize(false); // keep whitespace

if ($hasURL) {
    // --- begin URL validation

    if (isValidURL($url)) {
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

        if (!$title) {
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

            // try both ssl and non-ssl (ssl first to avoid mixed content)
            foreach (['https://', 'http://'] as $scheme) {
                $url = $scheme.$articlePermalinkURL;

                $p = new Parser($url);

                foreach ($userAgents as $UA) {
                    if ($p->fetch($UA)) {
                        $p->parse();
                        if ($p->readabilitify()) {
                            $title = $p->title;
                            $body = $p->body;

                            // save to db (non-prettified)
                            $db->cache($articlePermalinkURL, $title, $body, $url, $UA);

                            break;
                        } else {
                            $db->log($url, 'Unable to parse with Readability', $UA);
                        }
                    } else {
                        if (count($p->fetchErrors) > 0) {
                            $db->log($url, implode("\n", $p->fetchErrors), $UA);
                        }
                    }
                }

                if ($title && $body) {
                    // content found, bail!
                    $fetchSuccessful = true;
                    break;
                }
            }

            if (!$fetchSuccessful) {
                bugsnag_error('unsuccessful_fetch', null, compact('url') + ['last_fetch_errors' => $p->fetchErrors], 'info');
            }
        } else {
            // use the URL that was successful when fetched
            $url = $urlFromDB;
        }

        if ($title && $body) {
            $excerpt = Str::words(trim(preg_replace('/\s+/', ' ', strip_tags($body))), 100);

            // prettify for display
            $body = Parser::prettify($body, $url);

            echo $blade->run('article', compact('title', 'body', 'excerpt', 'url', 'articlePermalinkURL', 'permalink', 'permalinkWithoutScheme'));
        } else {
            echo $blade->run('article-notfound', ['title' => $url] + compact('articlePermalinkURL', 'url'));
        }
    } else {
        header("HTTP/1.0 404 Not Found");
        echo $blade->run('404', ['title' => $url, 'articlePermalinkURL' => false]);
    }
} else {
    echo $blade->run('index', ['articlePermalinkURL' => false]);
}
