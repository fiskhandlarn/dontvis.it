<?php

namespace Dontvisit;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class URLTest extends TestCase
{
    private static $client;
    private static $host;
    private static $isDocker = false;

    public static function setUpBeforeClass(): void
    {
        if (file_exists('/.dockerenv')) {
            self::$host = 'https://host.docker.internal:3000/';
            self::$isDocker = true;
        } else {
            self::$host = 'https://localhost:3000/';
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

        $response = self::$client->request('GET', urlencode('github.com/fiskhandlarn/dontvis.it'), ['allow_redirects' => false]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test301()
    {
        $response = self::$client->request('GET', urlencode(self::$host), ['allow_redirects' => false]);
        if (self::$isDocker) {
            $this->assertEquals(301, $response->getStatusCode());
        } else {
            // localhost doesn't have a tld, we will get a 404 instead of 301
            $this->assertEquals(404, $response->getStatusCode());
        }
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
}
