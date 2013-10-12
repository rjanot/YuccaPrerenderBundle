<?php

namespace Yucca\PrerenderBundle\Tests\Listener;

use Symfony\Component\HttpFoundation\Request;
use Yucca\PrerenderBundle\Listener\KernelListener;

class KernelListenerTest extends \PHPUnit_Framework_TestCase
{
    public function shouldRenderProvider()
    {
        return array(
            array(
                'user_agent'         => '',
                'host'               => 'www.example.com',
                'uri'                => '',
                'referer'            => 'http://google.com',
                'ignored_extensions' => array(),
                'whitelist'          => array(),
                'blacklist'          => array(),
                'should_prerender'   => false
            ),
            // Test a non-bot crawler
            array(
                'user_agent'         => 'Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5355d Safari/8536.25',
                'host'               => 'www.example.com',
                'uri'                => '',
                'referer'            => 'http://google.com',
                'ignored_extensions' => array(),
                'whitelist'          => array(),
                'blacklist'          => array(),
                'should_prerender'   => false
            ),
            // Test a Google Bot crawler
            array(
                'user_agent'         => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
                'host'               => 'www.example.com',
                'uri'                => '',
                'referer'            => 'http://google.com',
                'ignored_extensions' => array(),
                'whitelist'          => array(),
                'blacklist'          => array(),
                'should_prerender'   => true
            ),
            // Test a Yahoo Bot crawler
            array(
                'user_agent'         => 'Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)',
                'host'               => 'www.example.com',
                'uri'                => '',
                'referer'            => 'http://google.com',
                'ignored_extensions' => array(),
                'whitelist'          => array(),
                'blacklist'          => array(),
                'should_prerender'   => true
            ),
            // Test a Bing Bot crawler
            array(
                'user_agent'         => 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
                'host'               => 'www.example.com',
                'uri'                => '',
                'referer'            => 'http://google.com',
                'ignored_extensions' => array(),
                'whitelist'          => array(),
                'blacklist'          => array(),
                'should_prerender'   => true
            ),
            // Test a Baidu Bot crawler
            array(
                'user_agent'         => 'Baiduspider+(+http://www.baidu.com/search/spider.htm)',
                'host'               => 'www.example.com',
                'uri'                => '',
                'referer'            => 'http://google.com',
                'ignored_extensions' => array(),
                'whitelist'          => array(),
                'blacklist'          => array(),
                'should_prerender'   => true
            ),
            // Test a bot crawler with ignored_extension
            array(
                'user_agent'         => 'Baiduspider+(+http://www.baidu.com/search/spider.htm)',
                'host'               => 'www.example.com',
                'uri'                => '/screen.css',
                'referer'            => 'http://google.com',
                'ignored_extensions' => array('.jpg', '.css'),
                'whitelist'          => array(),
                'blacklist'          => array(),
                'should_prerender'   => false
            ),
            // Test a bot crawler that is whitelisted
            array(
                'user_agent'         => 'Baiduspider+(+http://www.baidu.com/search/spider.htm)',
                'host'               => 'www.example.com',
                'uri'                => '',
                'referer'            => 'http://google.com',
                'ignored_extensions' => array(),
                'whitelist'          => array('example.com'),
                'blacklist'          => array(),
                'should_prerender'   => true
            ),
            // Test a bot crawler that is whitelisted with more complex regex
            array(
                'user_agent'         => 'Baiduspider+(+http://www.baidu.com/search/spider.htm)',
                'host'               => 'www.example.com',
                'uri'                => '/users/michael',
                'referer'            => 'http://google.com',
                'ignored_extensions' => array(),
                'whitelist'          => array('/users/.*'),
                'blacklist'          => array(),
                'should_prerender'   => true
            ),
            // Test a bot crawler that is not whitelisted
            array(
                'user_agent'         => 'Baiduspider+(+http://www.baidu.com/search/spider.htm)',
                'host'               => 'www.example.com',
                'uri'                => '/foo',
                'referer'            => 'http://google.com',
                'ignored_extensions' => array(),
                'whitelist'          => array('/bar'),
                'blacklist'          => array(),
                'should_prerender'   => false
            ),
            // Test a bot crawler that is blacklisted
            array(
                'user_agent'         => 'Baiduspider+(+http://www.baidu.com/search/spider.htm)',
                'host'               => 'www.example.com',
                'uri'                => '/foo',
                'referer'            => 'http://google.com',
                'ignored_extensions' => array(),
                'whitelist'          => array(),
                'blacklist'          => array('/foo'),
                'should_prerender'   => false
            ),
            // Test a bot crawler that is blacklisted with more complex regex
            array(
                'user_agent'         => 'Baiduspider+(+http://www.baidu.com/search/spider.htm)',
                'host'               => 'www.example.com',
                'uri'                => '/users/julia',
                'referer'            => 'http://google.com',
                'ignored_extensions' => array(),
                'whitelist'          => array(),
                'blacklist'          => array('/users/*'),
                'should_prerender'   => false
            ),
            // Test a bot crawler that is not blacklisted
            array(
                'user_agent'         => 'Baiduspider+(+http://www.baidu.com/search/spider.htm)',
                'host'               => 'www.example.com',
                'uri'                => '/bar',
                'referer'            => 'http://google.com',
                'ignored_extensions' => array(),
                'whitelist'          => array(),
                'blacklist'          => array('/foo'),
                'should_prerender'   => true
            ),
            // Test a bot crawler and a referer that is blacklisted
            array(
                'user_agent'         => 'Baiduspider+(+http://www.baidu.com/search/spider.htm)',
                'host'               => 'www.example.com',
                'uri'                => '/foo',
                'referer'            => '/search',
                'ignored_extensions' => array(),
                'whitelist'          => array(),
                'blacklist'          => array('/search'),
                'should_prerender'   => false
            ),
            // Test a bot crawler and a referer that is not blacklisted
            array(
                'user_agent'         => 'Baiduspider+(+http://www.baidu.com/search/spider.htm)',
                'host'               => 'www.example.com',
                'uri'                => '/foo',
                'referer'            => '/search',
                'ignored_extensions' => array(),
                'whitelist'          => array(),
                'blacklist'          => array(),
                'should_prerender'   => true
            ),
            // Test a bot crawler and a referer that is not blacklisted by a regex
            array(
                'user_agent'         => 'Baiduspider+(+http://www.baidu.com/search/spider.htm)',
                'host'               => 'www.example.com',
                'uri'                => '/foo',
                'referer'            => '/profile/search',
                'ignored_extensions' => array(),
                'whitelist'          => array(),
                'blacklist'          => array('^/search', 'help'),
                'should_prerender'   => true
            ),
            // Test a bot crawler that combines whitelist and blacklist (1)
            array(
                'user_agent'         => 'Baiduspider+(+http://www.baidu.com/search/spider.htm)',
                'host'               => 'www.example.com',
                'uri'                => '/users/julia',
                'referer'            => 'http://google.com',
                'ignored_extensions' => array(),
                'whitelist'          => array('/users/*'),
                'blacklist'          => array('/users/julia'),
                'should_prerender'   => false
            ),
            // Test a bot crawler that combines whitelist and blacklist (2)
            array(
                'user_agent'         => 'Baiduspider+(+http://www.baidu.com/search/spider.htm)',
                'host'               => 'www.example.com',
                'uri'                => '/users/julia',
                'referer'            => 'http://google.com',
                'ignored_extensions' => array(),
                'whitelist'          => array('/users/*'),
                'blacklist'          => array('/users/michael'),
                'should_prerender'   => true
            ),
        );
    }

    /**
     * @dataProvider shouldRenderProvider
     */
    public function testShouldRender($userAgent, $request_host, $request_uri, $referer, $ignoredExtensions, $whitelist, $blacklist, $result)
    {
        $request  = new Request();
        $request->headers->set('HOST',$request_host);
        $request->headers->set('User-Agent', $userAgent);
        $request->headers->set('Referer', $referer);
        $request->server->set('REQUEST_URI', $request_uri);

        $httpClient = $this->getMock('Yucca\PrerenderBundle\HttpClient\ClientInterface');

        $listener = new KernelListener(
            'http://prerender_backend:12345',
            array('googlebot','yahoo','bingbot','baiduspider','facebookexternalhit'),
            $ignoredExtensions,
            $whitelist,
            $blacklist,
            $httpClient
        );

        $this->assertEquals($result, $listener->shouldPrerenderPage($request));
    }
}
