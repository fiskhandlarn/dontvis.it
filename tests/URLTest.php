<?php

namespace Dontvisit;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use DOMDocument;

class URLTest extends TestCase
{
    private static $client;
    private static $host;

    public static function setUpBeforeClass(): void
    {
        if (file_exists('/.dockerenv')) {
            self::$host = 'https://172.17.0.1:3000/';
        } else {
            self::$host = 'https://127.0.0.1:3000/';
        }

        echo "Using " . self::$host . "\n";

        self::$client = new Client([
            'base_uri' => self::$host,
            'verify' => false,
            'http_errors' => false,
        ]);
    }

    public function test200()
    {
        $response = self::$client->request('GET', '');
        $this->assertEquals(200, $response->getStatusCode());

        // test for no redirection
        $response = self::$client->request('GET', urlencode('github.com/fiskhandlarn/dontvis.it'));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testScrape()
    {
        $response = self::$client->request('GET', urlencode('github.com/fiskhandlarn/dontvis.it'));
        $this->assertEquals('fiskhandlarn/dontvis.it: dontvis.it, the idiot circumventor tool – dontvis.it', $this->getTitle($response->getBody()->getContents()));
    }

    public function test301()
    {
        $response = self::$client->request('GET', urlencode(self::$host), ['allow_redirects' => false]);
        $this->assertEquals(301, $response->getStatusCode());
    }

    public function test303()
    {
        $response = self::$client->request('GET', urlencode('https://github.com/fiskhandlarn/dontvis.it'), ['allow_redirects' => false]);
        $this->assertEquals(303, $response->getStatusCode());
    }

    public function test404()
    {
        $response = self::$client->request('GET', 'abc');
        $this->assertEquals(404, $response->getStatusCode());
    }

    // https://stackoverflow.com/a/30523600/1109380
    private function getTitle($html): string
    {
        $title = '';
        $dom = new DOMDocument();

        libxml_use_internal_errors(true); // https://stackoverflow.com/a/6090728/1109380
        if ($dom->loadHTML($html)) {
            $list = $dom->getElementsByTagName("title");
            if ($list->length > 0) {
                $title = $list->item(0)->textContent;
            }
        }
        libxml_clear_errors();

        return $title;
    }
}
