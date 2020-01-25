<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use eftec\bladeone\BladeOne;
use Readability\Readability;

if (!ob_start("ob_gzhandler")) {
    //gzip-e-di-doo-da
    ob_start();
}

define('ROOT_URL', $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST']);

// remove beginning slash added by nginx(?)
$url = ltrim($_GET['u'] ?: '', '/');

$hasURL = !empty($url);

if ($hasURL) {
    // don't crawl yourself
    if (strpos($url, $_SERVER['HTTP_HOST']) !== false) {
        header("Location: " . ROOT_URL . '/', true, 301);
        die();
    }

    // prepend with scheme if not present
    if (!preg_match('!^https?://!i', $url)) {
        //  assume non ssl
        $url = 'http://'.$url;
    }

    // Remove scheme from bookmarklet and direct links.
    $articlePermalinkURL = preg_replace('#^https?://#', '', $url);

    $permalinkWithoutScheme = $_SERVER['HTTP_HOST'] . '/' . $articlePermalinkURL;
    $permalink = $_SERVER['REQUEST_SCHEME'] . "://" . $permalinkWithoutScheme;

    // redirect to permalink if current address isn't the same as the wanted permalink
    if (ltrim($_SERVER['REQUEST_URI'], '/') !== $articlePermalinkURL) {
        header("Location: " . $permalink, true, 303);
        die();
    }
} else {
    // default to homepage
    $articlePermalinkURL = false;
    $permalinkWithoutScheme = $_SERVER['HTTP_HOST'] . '/';
    $permalink = $_SERVER['REQUEST_SCHEME'] . "://" . $permalinkWithoutScheme;
}

$blade = new BladeOne(
    __DIR__ . '/../resources/views',
    __DIR__ . '/../storage/views',
    BladeOne::MODE_AUTO
);
$blade->setOptimize(false); // keep whitespace

if ($hasURL) {
    require_once "includes/dbhandler.php";
    $db = new DBHandler();
    list($title, $body) = $db->read($articlePermalinkURL);

    if (!$title){
        // no cache, let's fetch the article

        //var_dump("Fetching article ...");

        // User agent switcheroo
        $UAnum = Rand (0,3) ;

        switch ($UAnum) {
            case 0:
                // TODO DN seems to restrict content if crawled from Google
                $UAstring = "User-Agent: Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)\r\n";
                break;

            case 1:
                $UAstring = "Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)\r\n";
                break;

            case 2:
                $UAstring = "Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)\r\n";
                break;

            case 3:
                $UAstring = "Baiduspider+(+http://www.baidu.com/search/spider.htm)  \r\n";
                break;

                // If this works, many lolz acquired.
        }

        //$UAstring = "User-Agent: Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)\r\n";
        //$UAstring = "Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)\r\n";
        //$UAstring = "Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)\r\n";
        //$UAstring = "Baiduspider+(+http://www.baidu.com/search/spider.htm)  \r\n";

        // Create a stream
        $opts = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>$UAstring
            )
        );

        $context = stream_context_create($opts);
        $html = @file_get_contents($url, false, $context);

        if ($html) {
            require_once 'includes/Readability.php';
            require_once 'includes/JSLikeHTMLElement.php';

            // PHP Readability works with UTF-8 encoded content.
            // If $html is not UTF-8 encoded, use iconv() or
            // mb_convert_encoding() to convert to UTF-8.

            // If we've got Tidy, let's clean up input.
            // This step is highly recommended - PHP's default HTML parser
            // often does a terrible job and results in strange output.
            if (function_exists('tidy_parse_string')) {
                $tidy = tidy_parse_string($html, array(), 'UTF8');
                $tidy->cleanRepair();
                $html = $tidy->value;
            }

            // give it to Readability
            $readability = new Readability($html, $url);

            // print debug output?
            // useful to compare against Arc90's original JS version -
            // simply click the bookmarklet with FireBug's
            // console window open
            $readability->debug = false;

            // convert links to footnotes?
            $readability->convertLinksToFootnotes = true;

            // process it
            $result = $readability->init();

            // does it look like we found what we wanted?
            if ($result) {
                $title = $readability->getTitle()->textContent;

                $content = $readability->getContent()->innerHTML;

                // if we've got Tidy, let's clean it up for output
                if (function_exists('tidy_parse_string')) {
                    $tidy = tidy_parse_string($content,
                                              array('indent'=>true, 'show-body-only'=>true),
                                              'UTF8');
                    $tidy->cleanRepair();
                    $content = $tidy->value;
                    $content = trim(preg_replace('/\s\s+/', ' ', $content));
                }

                $body = $content;

                // save to db
                $db->cache($articlePermalinkURL, $title, $body);
            }
        }
    }
    // else {
    //     var_dump("From cache:");
    // }

    if ($title && $body) {
        echo $blade->run("article",compact("title", "body", "url", "articlePermalinkURL", "permalink", "permalinkWithoutScheme"));
    } else {
        echo $blade->run("notfound",["title" => $url] + compact("articlePermalinkURL"));
    }
} else {
    echo $blade->run("index");
}
