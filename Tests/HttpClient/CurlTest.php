<?php

namespace Yucca\PrerenderBundle\Tests\HttpClient;

use Yucca\PrerenderBundle\HttpClient\Curl;

class CurlTest extends \PHPUnit_Framework_TestCase
{
    public function testSend()
    {
        $curlClient = new Curl();
        $this->assertInstanceOf('Yucca\PrerenderBundle\HttpClient\ClientInterface', $curlClient);

        $resp = $curlClient->send('http://www.example.com');
        $this->assertContains('<title>Example Domain</title>', $resp);
    }
}
