<?php
/**
 * Created by PhpStorm.
 * User: rjanot
 * Date: 13/10/13
 * Time: 12:16
 */

namespace Yucca\PrerenderBundle\Tests;


use PHPUnit\Framework\TestCase;
use Yucca\PrerenderBundle\YuccaPrerenderBundle;

class YuccaPrerenderBundleTest extends TestCase
{
    public function testGetName()
    {
        $bundle = new YuccaPrerenderBundle();
        $this->assertEquals('YuccaPrerenderBundle', $bundle->getName());
    }
}
