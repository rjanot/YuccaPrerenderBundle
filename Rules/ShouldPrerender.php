<?php

namespace Yucca\PrerenderBundle\Rules;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class ShouldPrerender
 * @package Yucca\PrerenderBundle\Rules
 */
class ShouldPrerender
{
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
     * @param array $crawlerUserAgents
     * @param array $ignoredExtensions
     * @param array $whitelistedUrls
     * @param array $blacklistedUrls
     */
    public function __construct(array $crawlerUserAgents, array $ignoredExtensions, array $whitelistedUrls, array $blacklistedUrls)
    {
        $this->crawlerUserAgents = $crawlerUserAgents;
        $this->ignoredExtensions = $ignoredExtensions;
        $this->whitelistedUrls = $whitelistedUrls;
        $this->blacklistedUrls = $blacklistedUrls;
    }

    /**
     * Is this request should be a prerender request?
     *
     * @param Request $request
     *
     * @return bool
     */
    public function shouldPrerenderPage(Request $request)
    {
        //if it contains _escaped_fragment_, show prerendered page
        if (null !== $request->query->get('_escaped_fragment_')) {
            return true;
        }

        // First, return false if User Agent is not a bot
        if (!$this->isCrawler($request)) {
            return false;
        }

        $uri = $request->getScheme().'://'.$request->getHost().$request->getRequestUri();

        // Then, return false if URI string contains an ignored extension
        if ($this->isIgnoredExtension($uri)) {
            return false;
        }

        // Then, return true if it is whitelisted (only if whitelist contains data)
        if (!empty($this->whitelistedUrls) && !$this->isWhitelisted($uri, $this->whitelistedUrls)) {
            return false;
        }

        // Finally, return false if it is blacklisted (or the referer)
        $referer = $request->headers->get('Referer');
        $blacklistUrls = $this->blacklistedUrls;

        if ($this->isBlacklisted($uri, $referer, $blacklistUrls)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $uri
     *
     * @return bool
     */
    public function isIgnoredExtension($uri)
    {
        foreach ($this->ignoredExtensions as $ignoredExtension) {
            if (strpos($uri, $ignoredExtension) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the request is made from a crawler
     *
     * @param  Request $request
     *
     * @return bool
     */
    public function isCrawler(Request $request)
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
     * @param  array  $whitelistUrls
     *
     * @return bool
     */
    public function isWhitelisted($uri, array $whitelistUrls = null)
    {
        if (is_null($whitelistUrls)) {
            $whitelistUrls = $this->whitelistedUrls;
        }
        foreach ($whitelistUrls as $whitelistUrl) {
            $match = preg_match('`'.$whitelistUrl.'`i', $uri);

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
     * @param  array  $blacklistUrls
     *
     * @return bool
     */
    public function isBlacklisted($uri, $referer, array $blacklistUrls = null)
    {
        if (is_null($blacklistUrls)) {
            $blacklistUrls = $this->blacklistedUrls;
        }
        foreach ($blacklistUrls as $blacklistUrl) {
            $pattern = '`'.$blacklistUrl.'`i';
            $match = preg_match($pattern, $uri) + preg_match($pattern, $referer);

            if ($match > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getWhitelistedUrls(): array
    {
        return $this->whitelistedUrls;
    }

    /**
     * @return array
     */
    public function getBlacklistedUrls(): array
    {
        return $this->blacklistedUrls;
    }
}
