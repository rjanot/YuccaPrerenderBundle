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

use Throwable;

/**
 * Class Exception
 * @package Yucca\PrerenderBundle\HttpClient
 */
class Exception extends \Exception
{
    public $header;
    public $body;

    /**
     * Exception constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     * @param string         $header
     * @param string         $body
     */
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null, $header, $body)
    {
        parent::__construct($message, $code, $previous);
        $this->header = $header;
        $this->body = $body;
    }
}
