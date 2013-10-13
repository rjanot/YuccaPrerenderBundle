<?php
/**
 * Created by PhpStorm.
 * User: rjanot
 * Date: 13/10/13
 * Time: 12:48
 */

namespace Yucca\PrerenderBundle\Tests\DependencyInjection;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yucca\PrerenderBundle\DependencyInjection\Configuration;
use Yucca\PrerenderBundle\DependencyInjection\YuccaPrerenderExtension;

class YuccaPrerenderExtensionTest extends \PHPUnit_Framework_TestCase{
    protected $containerBuilder;

    /**
     * @param $class
     * @param $propertyName
     * @return mixed
     */
    static public function getReflectedPropertyValue($class, $propertyName)
    {
        $reflectedClass = new \ReflectionClass($class);
        $property = $reflectedClass->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($class);
    }

    /**
     * @param mixed  $value
     * @param string $key
     */
    private function assertParameter($value, $key)
    {
        $this->assertEquals($value, $this->containerBuilder->getParameter($key), sprintf('%s parameter is correct', $key));
    }

    /**
     * @return mixed
     */
    public function testDefaultLoad() {
        $config = new Configuration();
        $defaultIgnoredExtensions = self::getReflectedPropertyValue($config, 'defaultIgnoredExtensions');
        $defaultCrawlerUserAgents = self::getReflectedPropertyValue($config, 'defaultCrawlerUserAgents');

        $this->containerBuilder = new ContainerBuilder();
        $extension = new YuccaPrerenderExtension();
        $extension->load(array(), $this->containerBuilder);

        $this->assertParameter('http://prerender.herokuapp.com', 'yucca_prerender.backend_url');
        $this->assertParameter($defaultCrawlerUserAgents, 'yucca_prerender.crawler_user_agents');
        $this->assertParameter($defaultIgnoredExtensions, 'yucca_prerender.ignored_extensions');
        $this->assertParameter(array(), 'yucca_prerender.whitelist_urls');
        $this->assertParameter(array(), 'yucca_prerender.blacklist_urls');
    }

    /**
     * @return mixed
     */
    public function testBackendUrl() {
        $this->containerBuilder = new ContainerBuilder();
        $extension = new YuccaPrerenderExtension();
        $extension->load(array(array('backend_url'=>'http://localhost:3000')), $this->containerBuilder);

        $this->assertParameter('http://localhost:3000', 'yucca_prerender.backend_url');
    }

    /**
     * @return mixed
     */
    public function testCrawler() {
        $this->containerBuilder = new ContainerBuilder();
        $extension = new YuccaPrerenderExtension();
        $extension->load(array(array('crawler_user_agents'=>array('My new bot'))), $this->containerBuilder);

        $this->assertParameter(array('My new bot'), 'yucca_prerender.crawler_user_agents');
    }

    /**
     * @return mixed
     */
    public function testIgnoredExtensions() {
        $this->containerBuilder = new ContainerBuilder();
        $extension = new YuccaPrerenderExtension();
        $extension->load(array(array('ignored_extensions'=>array('.io'))), $this->containerBuilder);

        $this->assertParameter(array('.io'), 'yucca_prerender.ignored_extensions');
    }

    /**
     * @return mixed
     */
    public function testWhitelist() {
        $this->containerBuilder = new ContainerBuilder();
        $extension = new YuccaPrerenderExtension();
        $extension->load(array(array('whitelist_urls'=>array('/users'))), $this->containerBuilder);

        $this->assertParameter(array('/users'), 'yucca_prerender.whitelist_urls');
    }

    /**
     * @return mixed
     */
    public function testBlacklist() {
        $this->containerBuilder = new ContainerBuilder();
        $extension = new YuccaPrerenderExtension();
        $extension->load(array(array('blacklist_urls'=>array('/users'))), $this->containerBuilder);

        $this->assertParameter(array('/users'), 'yucca_prerender.blacklist_urls');
    }
} 
