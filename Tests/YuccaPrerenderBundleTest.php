<?php
/**
 * Created by PhpStorm.
 * User: rjanot
 * Date: 13/10/13
 * Time: 12:16
 */

namespace Yucca\PrerenderBundle\Tests;


use Yucca\PrerenderBundle\YuccaPrerenderBundle;

class YuccaPrerenderBundleTest extends \PHPUnit_Framework_TestCase{
    public function testGetName()
    {
        $bundle = new YuccaPrerenderBundle();
        $this->assertEquals('YuccaPrerenderBundle', $bundle->getName());
    }
} 
