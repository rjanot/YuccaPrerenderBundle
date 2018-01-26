<?php
/*
 * This file was delivered to you as part of the YuccaPrerenderBundle package.
 *
 * (c) Rémi JANOT <r.janot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Yucca\PrerenderBundle;

/**
 * Class Events
 * @package Yucca\PrerenderBundle
 */
final class Events
{
    // @codingStandardsIgnoreStart
    const shouldPrerenderPage = 'yucca_prerender.should_prerender';
    const onBeforeRequest = 'yucca_prerender.render.before';
    const onAfterRequest = 'yucca_prerender.render.after';
    // @codingStandardsIgnoreEnd
}
