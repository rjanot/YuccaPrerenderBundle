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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Yucca\PrerenderBundle\Event\RenderAfterEvent;
use Yucca\PrerenderBundle\Event\RenderBeforeEvent;
use Yucca\PrerenderBundle\Event\ShouldPrerenderEvent;
use Yucca\PrerenderBundle\Events;
use Yucca\PrerenderBundle\HttpClient\ClientInterface;
use Yucca\PrerenderBundle\Rules\ShouldPrerender;

class KernelListener
{
    /**
     * @var string
     */
    protected $backendUrl;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var null|bool
     */
    protected $forceSecureRedirect;

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * KernelListener constructor.
     *
     * @param string                   $backendUrl
     * @param string                   $token
     * @param ClientInterface          $httpClient
     * @param EventDispatcherInterface $eventDispatcher
     * @param bool                     $forceSecureRedirect
     * @param ShouldPrerender          $rules
     */
    public function __construct(
        $backendUrl,
        $token,
        ClientInterface $httpClient,
        EventDispatcherInterface $eventDispatcher,
        $forceSecureRedirect,
        ShouldPrerender $rules
    ) {
        $this->backendUrl = $backendUrl;
        $this->token = $token;
        $this->forceSecureRedirect = $forceSecureRedirect;
        $this->setHttpClient($httpClient);
        $this->setEventDispatcher($eventDispatcher);
        $this->rules = $rules;
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

    /**
     * Set the Event Dispatcher
     *
     * @param  EventDispatcherInterface $eventDispatcher
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param GetResponseEvent $event
     * @return bool
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$event->isMasterRequest()) {
            return false;
        }

        //Check if we have to prerender page
        $eventshouldPrerender = new ShouldPrerenderEvent($request);
        $this->eventDispatcher->dispatch(Events::shouldPrerenderPage, $eventshouldPrerender);

        $shouldPrerender = $eventshouldPrerender->getShouldPrerender();
        if (is_null($shouldPrerender)) {
            //Check if we have to prerender page
            if (!$this->rules->shouldPrerenderPage($request)) {
                return false;
            }
        } elseif (false === $shouldPrerender) {
            return false;
        }

        $event->stopPropagation();

        //Dispatch event for a more custom way of retrieving response
        if ($this->forceSecureRedirect === null) {
            $scheme = $request->getScheme();
        } else {
            $scheme = $this->forceSecureRedirect ? 'https' : 'http';
        }
        $uri = rtrim($this->backendUrl, '/').'/'.$scheme.'://'.$request->getHost().$request->getRequestUri();

        $eventBefore = new RenderBeforeEvent($request, $uri);
        // @codingStandardsIgnoreStart
        $this->eventDispatcher->dispatch(Events::onBeforeRequest, $eventBefore);
        // @codingStandardsIgnoreEnd

        //Check if event get back a response
        if ($eventBefore->hasResponse()) {
            $response = $eventBefore->getResponse();
            if (is_string($response)) {
                $event->setResponse(new Response($response, 200));

                return true;
            } elseif ($response instanceof Response) {
                $event->setResponse($response);

                return true;
            }
        }


        //Launch prerender
        try {
            $event->setResponse(new Response($this->httpClient->send($eventBefore->getPrerenderUrl(), $this->token), 200));
        } catch (\Yucca\PrerenderBundle\HttpClient\Exception $e) {
            // pass
        }

        //Dispatch event to save response
        if ($event->getResponse()) {
            $eventAfter = new RenderAfterEvent($request, $event->getResponse());
            $this->eventDispatcher->dispatch(Events::onAfterRequest, $eventAfter);
        }

        return true;
    }
}
