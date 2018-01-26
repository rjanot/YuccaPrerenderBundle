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

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Request "http://www.example.com" didn\'t run properly : Could not resolve host: www.example.com');
        $resp = $curlClient->send('http://www.example.com');
    }
}
