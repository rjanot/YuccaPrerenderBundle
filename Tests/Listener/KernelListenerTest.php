<?php

namespace Yucca\PrerenderBundle\Tests\Listener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Yucca\PrerenderBundle\Listener\KernelListener;
use Yucca\PrerenderBundle\Rules\ShouldPrerender;

class KernelListenerTest extends TestCase
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
                'user_agent'         => 'Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X)'.
                    'AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5355d Safari/8536.25',
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
                'user_agent'         => 'Mozilla/5.0 (compatible; Yahoo! Slurp;'.
                    'http://help.yahoo.com/help/us/ysearch/slurp)',
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
            //Test _escaped_fragment_
            array(
                'user_agent'         => '',
                'host'               => 'www.example.com',
                'uri'                => '?_escaped_fragment_=heyaImABot',
                'referer'            => 'http://google.com',
                'ignored_extensions' => array(),
                'whitelist'          => array(),
                'blacklist'          => array(),
                'should_prerender'   => true
            ),
        );
    }

    /**
     * @dataProvider shouldRenderProvider
     */
    public function testOnKernelRequest(
        $userAgent,
        $request_host,
        $request_uri,
        $referer,
        $ignoredExtensions,
        $whitelist,
        $blacklist,
        $result
    ) {

        $request  = new Request();
        $request->headers->set('HOST', $request_host);
        $request->headers->set('User-Agent', $userAgent);
        $request->headers->set('Referer', $referer);
        $request->server->set('REQUEST_URI', $request_uri);

        parse_str(parse_url($request_host.'/'.$request_uri, PHP_URL_QUERY), $query);
        $request->query->replace($query);

        $httpClient = $this->createMock(\Yucca\PrerenderBundle\HttpClient\ClientInterface::class);
        $eventDispatcher = $this->createMock(\Symfony\Component\EventDispatcher\EventDispatcher::class);

        $shouldPrerenderRules = new ShouldPrerender(
            array('googlebot','yahoo','bingbot','baiduspider','facebookexternalhit','twitterbot'),
            $ignoredExtensions,
            $whitelist,
            $blacklist
        );

        $listener = new KernelListener(
            'http://prerender_backend:12345',
            'null',
            $httpClient,
            $eventDispatcher,
            false,
            $shouldPrerenderRules
        );


        $event = new GetResponseEvent(
            $this->createMock(\Symfony\Component\HttpKernel\HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
        $listener->onKernelRequest($event);
        $this->assertEquals($result, $event->hasResponse());
    }
}
