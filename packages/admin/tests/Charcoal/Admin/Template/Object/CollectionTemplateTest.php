<?php

namespace Charcoal\Tests\Admin\Template\Object;

use ReflectionClass;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Template\Object\CollectionTemplate;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class CollectionTemplateTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * Tested Class.
     *
     * @var CollectionTemplate
     */
    private $obj;

    /**
     * Store the service container.
     *
     * @var Container
     */
    private $container;

    /**
     * Set up the test.
     *
     * @return void
     */
    public function setUp(): void
    {
        $container = $this->container();

        $this->obj = new CollectionTemplate([
            'logger'          => $container['logger'],
            'metadata_loader' => $container['metadata/loader'],
            'container'       => $container
        ]);
    }

    /**
     * @return void
     */
    public function testAuthRequiredIsTrue()
    {
        $res = $this->callMethod($this->obj, 'authRequired');
        $this->assertTrue($res);
    }

    /**
     * @return void
     */
    public function testInit()
    {
        //$ret = $this->obj->init();
        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testTitle()
    {
        $this->obj->setObjType('charcoal/admin/user');
        $ret = $this->obj->title();
        $ret2 = $this->obj->title();

        $this->assertSame($ret, $ret2);
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    protected function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerTemplateDependencies($container);
            $containerProvider->registerWidgetFactory($container);
            $containerProvider->registerDashboardBuilder($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
