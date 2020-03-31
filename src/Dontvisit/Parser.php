<?php

declare(strict_types=1);

namespace Dontvisit;

use DOMDocument;
use DOMXPath;
use Error;
use Readability\Readability;

class Parser
{
    private $content = false;
    private $fetchErrors = [];
    private $html = false;
    private $title = false;
    private $url = false;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function __get($property)
    {
        switch ($property) {
            case 'title':
                return $this->title;
            case 'body':
                return $this->content;
            case 'fetchErrors':
                return $this->fetchErrors;
            default:
                break;
        }
    }

    /*********************************************************************************
     *    ___       __   ___                  __  __           __
     *   / _ \__ __/ /  / (_)___   __ _  ___ / /_/ /  ___  ___/ /__
     *  / ___/ // / _ \/ / / __/  /  ' \/ -_) __/ _ \/ _ \/ _  (_-<
     * /_/   \_,_/_.__/_/_/\__/  /_/_/_/\__/\__/_//_/\___/\_,_/___/
     *
     *********************************************************************************/

    public function fetch(string $UAString, int $timeout = -1): bool
    {
        if (!$this->url) {
            throw new Error('URL not set.');

            return false;
        }

        // Create a stream
        $opts = [
            'http'=> [
                'method' => 'GET',
                'header' => $UAString,
            ],
        ];

        $context = stream_context_create($opts);

        $this->fetchErrors = [];
        if ($timeout !== -1) {
            ini_set('default_socket_timeout', (string)$timeout); // 900 Seconds = 15 Minutes
        }
        set_error_handler([&$this, 'fetchErrorHandler'], E_ALL);
        $this->html = file_get_contents($this->url, false, $context);
        restore_error_handler();

        // check for redirects
        // https://stackoverflow.com/a/37588381/1109380
        if ((bool) $this->html && isset($http_response_header)) {
            $pattern = "/^Location:\s*(.*)$/i";
            $location_headers = preg_grep($pattern, $http_response_header);
            if (!empty($location_headers) &&
                preg_match($pattern, array_values($location_headers)[0], $matches)) {
                if (isset($matches[1]) ) {
                    // update url to wanted redirect (which file_get_contents() already has followed when fetching)
                    $this->url = $matches[1];
                }
            }
        }

        return (bool) $this->html;
    }

    public function parse()
    {
        if (!$this->url) {
            throw new Error('URL not set.');

            return false;
        }

        if (!$this->html) {
            throw new Error('HTML not set.');

            return false;
        }

        $html = $this->html;

        // PHP Readability works with UTF-8 encoded content.
        // If $html is not UTF-8 encoded, use iconv() or
        // mb_convert_encoding() to convert to UTF-8.
        if (mb_detect_encoding($html) !== 'UTF-8') {
            $html = mb_convert_encoding($html, 'UTF-8');
        }

        $html = $this->tidyClean($html);

        $html = $this->replaceDataAttributes($html);
        $html = $this->removeAttributes($html);

        $this->html = $html;
    }

    public static function prettify(string $content, string $url): string
    {
        $content = self::tidyIndent($content);
        $content = self::stripTags($content);
        $content = self::makeAbsoluteURLs($content, $url);

        return $content;
    }

    public function readabilitify(): bool
    {
        if (!$this->url) {
            throw new Error('URL not set.');

            return false;
        }

        if (!$this->html) {
            throw new Error('HTML not set.');

            return false;
        }

        // give it to Readability
        $readability = new Readability($this->html, $this->url);

        // print debug output?
        // useful to compare against Arc90's original JS version -
        // simply click the bookmarklet with FireBug's
        // console window open
        $readability->debug = false;

        // convert links to footnotes?
        //$readability->convertLinksToFootnotes = true;

        // process it
        $result = $readability->init();

        // does it look like we found what we wanted?
        if ($result) {
            $this->title = $readability->getTitle()->textContent;
            $this->content = $readability->getContent()->innerHTML;

            return true;
        }

        return false;
    }

    public function url(): string
    {
        return $this->url;
    }


    /*********************************************************************************
     *    ___      _           __                   __  __           __
     *   / _ \____(_)  _____ _/ /____    __ _  ___ / /_/ /  ___  ___/ /__
     *  / ___/ __/ / |/ / _ `/ __/ -_)  /  ' \/ -_) __/ _ \/ _ \/ _  (_-<
     * /_/  /_/ /_/|___/\_,_/\__/\__/  /_/_/_/\__/\__/_//_/\___/\_,_/___/
     *
     *********************************************************************************/

    private function fetchErrorHandler(int $errno, string $errstr, string $errfile = null, int $errline = null): bool
    {
        $type = '';
        switch ($errno) {
            case E_ERROR: // 1 //
                $type = 'E_ERROR';
                break;
            case E_WARNING: // 2 //
                $type = 'E_WARNING';
                break;
            case E_PARSE: // 4 //
                $type = 'E_PARSE';
                break;
            case E_NOTICE: // 8 //
                $type = 'E_NOTICE';
                break;
            case E_CORE_ERROR: // 16 //
                $type = 'E_CORE_ERROR';
                break;
            case E_CORE_WARNING: // 32 //
                $type = 'E_CORE_WARNING';
                break;
            case E_COMPILE_ERROR: // 64 //
                $type = 'E_COMPILE_ERROR';
                break;
            case E_COMPILE_WARNING: // 128 //
                $type = 'E_COMPILE_WARNING';
                break;
            case E_USER_ERROR: // 256 //
                $type = 'E_USER_ERROR';
                break;
            case E_USER_WARNING: // 512 //
                $type = 'E_USER_WARNING';
                break;
            case E_USER_NOTICE: // 1024 //
                $type = 'E_USER_NOTICE';
                break;
            case E_STRICT: // 2048 //
                $type = 'E_STRICT';
                break;
            case E_RECOVERABLE_ERROR: // 4096 //
                $type = 'E_RECOVERABLE_ERROR';
                break;
            case E_DEPRECATED: // 8192 //
                $type = 'E_DEPRECATED';
                break;
            case E_USER_DEPRECATED: // 16384 //
                $type = 'E_USER_DEPRECATED';
                break;
        }

        $this->fetchErrors[] = $type.': '.$errstr;

        /* Don't execute PHP internal error handler */
        return true;
    }

    private static function makeAbsoluteURLs(string $content, string $url): string
    {
        // determine the base url
        $urlParts = parse_url($url);

        $basePath = '';
        if (isset($urlParts['path'])) {
            $basePath = ltrim(dirname($urlParts['path']), '/');
            if (strlen($basePath) > 0) {
                $basePath .= '/';
            }
        }

        $domain = $urlParts['scheme'].'://'.$urlParts['host'].'/';

        $tagsAndAttributes = [
            'img'  => 'src',
            'a'    => 'href',
            'source' => 'srcset',
        ];

        // make all relative urls absolute
        // https://stackoverflow.com/a/48837947/1109380
        $dom = new DOMDocument();
        libxml_use_internal_errors(true); // https://stackoverflow.com/a/6090728/1109380
        $dom->loadHTML(
            mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), // https://stackoverflow.com/a/8218649/1109380
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // first, prepend all urls with source domain
        foreach ($tagsAndAttributes as $tag => $attr) {
            foreach ($xpath->query("//{$tag}[not(starts-with(@{$attr}, '//')) and not(starts-with(@{$attr}, 'http')) and not(starts-with(@{$attr}, '#'))]") as $node) {
                $path = $node->getAttribute($attr);
                if ($path) {
                    if ($path[0] === '/') {
                        $path = ltrim($path, '/');
                    } else {
                        $path = $basePath . $path;
                    }

                    $node->setAttribute($attr, $domain . $path);
                }
            }
        }

        if (env('ANONYMIZER_URL', false)) {
            // second, prepend all urls (except img's) with anonymizer
            unset($tagsAndAttributes['img']);
            unset($tagsAndAttributes['source']);
            foreach ($tagsAndAttributes as $tag => $attr) {
                foreach ($xpath->query("//{$tag}[not(starts-with(@{$attr}, '#'))]") as $node) {
                    $node->setAttribute($attr, env('ANONYMIZER_URL').$node->getAttribute($attr));
                }
            }
        }

        return $dom->saveHTML();
    }

    private function removeAttributes(string $html): string
    {
        // remove all attributes except some
        // https://stackoverflow.com/a/3026411/1109380
        $dom = new DOMDocument();
        libxml_use_internal_errors(true); // https://stackoverflow.com/a/6090728/1109380
        $dom->loadHTML($html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//@*');
        foreach ($nodes as $node) {
            if (!in_array($node->nodeName, ['xlink:href', 'rel', 'src', 'srcset', 'srcSet', 'sizes', 'href', 'media', 'sizes', 'value', 'content', 'dir', 'lang', 'xml:lang'])) {
                $node->parentNode->removeAttribute($node->nodeName);
            }
        }
        $html = $dom->saveHTML();

        return $html;
    }

    private function replaceDataAttributes(string $html): string
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true); // https://stackoverflow.com/a/6090728/1109380
        $dom->loadHTML($html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//@*');
        foreach ($nodes as $node) {
            if (in_array($node->nodeName, ['data-src', 'data-srcset'])) {
                // create new attribute without data- prefix
                $node->parentNode->setAttribute(ltrim($node->nodeName, 'data-'), ($node->nodeValue));

                // remove data attribute
                $node->parentNode->removeAttribute($node->nodeName);
            }
        }
        $html = $dom->saveHTML();

        return $html;
    }

    private function tidyClean(string $html): string
    {
        // If we've got Tidy, let's clean up input.
        // This step is highly recommended - PHP's default HTML parser
        // often does a terrible job and results in strange output.
        if (function_exists('tidy_parse_string')) {
            $tidy = tidy_parse_string($html, [], 'UTF8');
            $tidy->cleanRepair();
            $html = $tidy->value;
        }

        return $html;
    }

    private static function stripTags(string $content): string
    {
        // strip potentially harmful tags
        return strip_tags(
            $content,
            implode('', [
                '<a>',
                '<abbr>',
                '<address>',
                '<area>',
                '<article>',
                '<aside>',
                '<audio>',
                '<b>',
                '<base>',
                '<bdi>',
                '<bdo>',
                '<blockquote>',
                '<body>',
                '<br>',
                '<button>',
                '<caption>',
                '<cite>',
                '<code>',
                '<col>',
                '<colgroup>',
                '<command>',
                '<data>',
                '<datalist>',
                '<dd>',
                '<del>',
                '<details>',
                '<dfn>',
                '<div>',
                '<dl>',
                '<dt>',
                '<em>',
                '<embed>',
                '<fieldset>',
                '<figcaption>',
                '<figure>',
                '<footer>',
                '<h1>',
                '<h2>',
                '<h3>',
                '<h4>',
                '<h5>',
                '<h6>',
                '<head>',
                '<header>',
                '<hgroup>',
                '<hr>',
                '<html>',
                '<i>',
                '<img>',
                '<ins>',
                '<kbd>',
                '<keygen>',
                '<label>',
                '<legend>',
                '<li>',
                '<link>',
                '<map>',
                '<mark>',
                '<math>',
                '<menu>',
                '<meta>',
                '<meter>',
                '<nav>',
                '<noscript>',
                '<object>',
                '<ol>',
                '<optgroup>',
                '<output>',
                '<p>',
                '<p>',
                '<param>',
                '<picture>',
                '<pre>',
                '<progress>',
                '<q>',
                '<rp>',
                '<rt>',
                '<ruby>',
                '<s>',
                '<samp>',
                '<section>',
                '<select>',
                '<small>',
                '<source>',
                '<span>',
                '<strong>',
                '<style>',
                '<sub>',
                '<summary>',
                '<sup>',
                '<table>',
                '<tbody>',
                '<td>',
                '<tfoot>',
                '<th>',
                '<thead>',
                '<time>',
                '<title>',
                '<tr>',
                '<track>',
                '<u>',
                '<ul>',
                '<var>',
                '<video>',
                '<wbr>',
            ])
        );
    }

    private static function tidyIndent(string $content): string
    {
        // if we've got Tidy, let's clean it up for output
        if (function_exists('tidy_parse_string')) {
            $tidy = tidy_parse_string($content,
                                      ['indent'=> true, 'show-body-only'=>true],
                                      'UTF8');
            $tidy->cleanRepair();
            $content = $tidy->value;
        }

        return $content;
    }
}
