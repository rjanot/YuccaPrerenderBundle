<?php
/*
 * This file was delivered to you as part of the YuccaPrerenderBundle package.
 *
 * (c) Rémi JANOT <r.janot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Yucca\PrerenderBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ShouldPrerenderEvent
 * @package Yucca\PrerenderBundle\Event
 */
class ShouldPrerenderEvent extends Event
{
    protected $request;
    protected $shouldPrerender;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param bool $shouldPrerender
     *
     * @return $this
     */
    public function setShouldPrerender(bool $shouldPrerender)
    {
        $this->shouldPrerender = $shouldPrerender;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getShouldPrerender() : ?bool
    {
        return $this->shouldPrerender;
    }
}
