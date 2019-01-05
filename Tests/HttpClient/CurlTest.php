<?php

namespace Yucca\PrerenderBundle\Tests\HttpClient;

use PHPUnit\Framework\TestCase;
use Yucca\PrerenderBundle\HttpClient\Curl;
use Yucca\PrerenderBundle\HttpClient\Exception;

class CurlTest extends TestCase
{
    public function testSend()
    {
        $curlClient = new Curl();
        $this->assertInstanceOf('Yucca\PrerenderBundle\HttpClient\ClientInterface', $curlClient);

        $resp = $curlClient->send('http://www.example.com');
        $this->assertContains('<title>Example Domain</title>', $resp);
    }
}
