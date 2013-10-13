<?php
/*
 * This file was delivered to you as part of the YuccaPrerenderBundle package.
 *
 * (c) Rémi JANOT <r.janot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yucca\PrerenderBundle\HttpClient;

/**
 * Interface ClientInterface
 * @package Yucca\PrerenderBundle\HttpClient
 */
interface ClientInterface
{
    public function send($url);
}
