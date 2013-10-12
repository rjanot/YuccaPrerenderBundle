<?php
/*
 * This file was delivered to you as part of the YuccaPrerenderBundle package.
 *
 * (c) Rémi JANOT <r.janot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yucca\PrerenderBundle\Listener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Yucca\PrerenderBundle\HttpClient\ClientInterface;

class KernelListener
{
    /**
     * @var string
     */
    protected $backendUrl;

    /**
     * @var array
     */
    protected $ignoredExtensions;

    /**
     * @var array
     */
    protected $whitelistedUrls;

    /**
     * @var array
     */
    protected $blacklistedUrls;

    /**
     * @var array
     */
    protected $crawlerUserAgents;

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @param string $backendUrl
     * @param array $crawlerUserAgents
     * @param array $ignoredExtensions
     * @param array $whitelistedUrls
     * @param array $blacklistedUrls
     * @param ClientInterface $httpClient
     */
    public function __construct($backendUrl, array $crawlerUserAgents, array $ignoredExtensions, array $whitelistedUrls, array $blacklistedUrls, ClientInterface $httpClient)
    {
        $this->backendUrl = $backendUrl;
        $this->crawlerUserAgents = $crawlerUserAgents;
        $this->ignoredExtensions = $ignoredExtensions;
        $this->whitelistedUrls = $whitelistedUrls;
        $this->blacklistedUrls = $blacklistedUrls;
        $this->setHttpClient($httpClient);
    }

    /**
     * Set the HTTP client used to perform the GET request
     *
     * @param  ClientInterface $httpClient
     * @return void
     */
    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$this->shouldPrerenderPage($request)) {
            return null;
        }


        $event->stopPropagation();

        $uri    = rtrim($this->backendUrl, '/') .
            '/' . $request->getScheme().'://' . $request->getHost() . $request->getRequestUri();

        $event->setResponse(new Response($this->httpClient->send($uri),200));
    }

    /**
     * Is this request should be a prerender request?
     *
     * @param Request $request
     * @return bool
     */
    public function shouldPrerenderPage(Request $request)
    {
        //if it contains _escaped_fragment_, show prerendered page
        if(null !== $request->query->get('_escaped_fragment_')) {
            return true;
        }

        // First, return false if User Agent is not a bot
        if (!$this->isCrawler($request)) {
            return false;
        }

        $uri = $request->getScheme().'://' . $request->getHost() . $request->getRequestUri();

        // Then, return false if URI string contains an ignored extension
        foreach ($this->ignoredExtensions as $ignoredExtension) {
            if (strpos($uri, $ignoredExtension) !== false) {
                return false;
            }
        }

        // Then, return true if it is whitelisted (only if whitelist contains data)
        $whitelistUrls = $this->whitelistedUrls;

        if (!empty($whitelistUrls) && !$this->isWhitelisted($uri, $whitelistUrls)) {
            return false;
        }

        // Finally, return false if it is blacklisted (or the referer)
        $referer       = $request->headers->get('Referer');
        $blacklistUrls = $this->blacklistedUrls;

        if (!empty($blacklistUrls) && $this->isBlacklisted($uri, $referer, $blacklistUrls)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the request is made from a crawler
     *
     * @param  Request $request
     * @return bool
     */
    protected function isCrawler(Request $request)
    {
        $userAgent = strtolower($request->headers->get('User-Agent'));

        foreach ($this->crawlerUserAgents as $crawlerUserAgent) {
            if (strpos($userAgent, strtolower($crawlerUserAgent)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the request is whitelisted
     *
     * @param  string $uri
     * @param  array $whitelistUrls
     * @return bool
     */
    protected function isWhitelisted($uri, array $whitelistUrls)
    {
        foreach ($whitelistUrls as $whitelistUrl) {
            $match = preg_match('`' . $whitelistUrl . '`i', $uri);

            if ($match > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the request is blacklisted
     *
     * @param  string $uri
     * @param  string $referer
     * @param  array $blacklistUrls
     * @return bool
     */
    protected function isBlacklisted($uri, $referer, array $blacklistUrls)
    {
        foreach ($blacklistUrls as $blacklistUrl) {
            $pattern = '`' . $blacklistUrl . '`i';
            $match   = preg_match($pattern, $uri) + preg_match($pattern, $referer);

            if ($match > 0) {
                return true;
            }
        }

        return false;
    }
}
