<?php

namespace Charcoal\Tests\Admin\Script\User;

use PDO;

// From PSR-3
use Psr\Log\NullLogger;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-core'
use Charcoal\Model\Service\MetadataLoader;
use Charcoal\Source\DatabaseSource;

// From 'charcoal-admin'
use Charcoal\Admin\Script\User\ResetPasswordScript;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class ResetPasswordScriptTest extends AbstractTestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Instance of class under test
     * @var CreateScript
     */
    private $obj;

    /**
     * @return Container
     */
    private function getContainer()
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerScriptDependencies($container);
        return $container;
    }

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->container = $this->getContainer();

        $this->obj = new ResetPasswordScript([
            'logger' => $this->container['logger'],
            'climate' => $this->container['climate'],
            'model_factory' => $this->container['model/factory'],

            // Will call `setDependencies()` on object. AdminScript expects a 'mode/factory'.
            'container' => $this->container
        ]);
    }

    /**
     * @return void
     */
    public function testDefaultArguments()
    {
        $args = $this->obj->defaultArguments();

        $this->assertArrayHasKey('email', $args);
        $this->assertArrayHasKey('password', $args);
    }

    /**
     * @return void
     */
    public function testArguments()
    {
        $args = $this->obj->arguments();

        $this->assertArrayHasKey('email', $args);
        $this->assertArrayHasKey('password', $args);
    }

    /**
     * @return void
     */
    /*
    public function testInvokeWithArguments()
    {
        global $argv;

        $argv = [];
        $argv[] = 'vendor/bin/charcoal';

        $argv[] = '--email';
        $argv[] = 'foobar@example.com';

        $argv[] = '--password';
        $argv[] = '[Foo]{bar}123';

        $model = $this->container['model/factory']->create('charcoal/admin/user');
        $source = $model->source();
        $source->createTable();

        $model->setData([
            'email' => 'foobar@example.com',
            'password' => 'BarFoo123'
        ]);
        $model->setRevisionEnabled(false);
        $model->save();

        $request = $this->createMock('\Psr\Http\Message\RequestInterface');
        $response = $this->createMock('\Psr\Http\Message\ResponseInterface');

        $obj = $this->obj;
        $ret = $obj($request, $response);

        $this->assertSame($ret, $response);
    }
    */
}
