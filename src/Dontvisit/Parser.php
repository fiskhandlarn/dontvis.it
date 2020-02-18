<?php

declare(strict_types=1);

namespace Dontvisit;

use Readability\Readability;
use \DOMDocument;
use \DOMXPath;
use \Error;

class Parser
{
    private $content = false;
    private $html = false;
    private $title = false;
    private $url = false;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function __get($property) {
        switch ($property) {
            case "title":
                return $this->title;
            case "body":
                return $this->content;
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

    public function fetch(string $UAString): bool
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
        $this->html = @file_get_contents($this->url, false, $context);

        return !!$this->html;
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
            $html = mb_convert_encoding($html, "UTF-8");
        }

        $html = $this->tidyClean($html);

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

    /*********************************************************************************
     *    ___      _           __                   __  __           __
     *   / _ \____(_)  _____ _/ /____    __ _  ___ / /_/ /  ___  ___/ /__
     *  / ___/ __/ / |/ / _ `/ __/ -_)  /  ' \/ -_) __/ _ \/ _ \/ _  (_-<
     * /_/  /_/ /_/|___/\_,_/\__/\__/  /_/_/_/\__/\__/_//_/\___/\_,_/___/
     *
     *********************************************************************************/

    private static function makeAbsoluteURLs(string $content, string $url): string
    {
        // determine the base url
        $urlParts = parse_url($url);
        $domain = $urlParts['scheme'] . '://' . $urlParts['host'] . '/';
        $tagsAndAttributes = [
            'img' => 'src',
            'form' => 'action',
            'a' => 'href'
        ];

        // make all relative urls absolute
        // https://stackoverflow.com/a/48837947/1109380
        $dom = new DOMDocument;
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
                $node->setAttribute($attr, $domain . ltrim($node->getAttribute($attr), '/'));
            }
        }

        if (env('ANONYMIZER_URL', false)) {
            // second, prepend all urls (except img's) with anonymizer
            unset($tagsAndAttributes['img']);
            foreach ($tagsAndAttributes as $tag => $attr) {
                foreach ($xpath->query("//{$tag}[not(starts-with(@{$attr}, '#'))]") as $node) {
                    $node->setAttribute($attr, env('ANONYMIZER_URL') . $node->getAttribute($attr));
                }
            }
        }

        return $dom->saveHTML();
    }

    private function removeAttributes(string $html): string
    {
        // remove all attributes except some
        // https://stackoverflow.com/a/3026411/1109380
        $dom = new DOMDocument;
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
            join('', [
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
                '<canvas>',
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
                '<form>',
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
                '<iframe>',
                '<img>',
                '<input>',
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
                '<option>',
                '<output>',
                '<p>',
                '<p>',
                '<param>',
                '<pre>',
                '<progress>',
                '<q>',
                '<rp>',
                '<rt>',
                '<ruby>',
                '<s>',
                '<samp>',
                '<script>',
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
                '<textarea>',
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
                                      ['indent'=>true, 'show-body-only'=>true],
                                      'UTF8');
            $tidy->cleanRepair();
            $content = $tidy->value;
        }

        return $content;
    }
}
