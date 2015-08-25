<?php

namespace Charcoal\Tests\Config;

/**
*
*/
class AbstractConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
    * @var mixed The Abstract Config mock
    */
    public $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Config\AbstractConfig');
    }

    public function testDefaultData()
    {
        $obj = $this->obj;
        $this->assertEquals([], $obj->default_data());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->set_data([
            'foo'=>'bar',
            'bar'=>'baz'
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals('bar', $obj->get('foo'));
        $this->assertEquals('baz', $obj->get('bar'));
    }

    public function testGet()
    {
        $obj = $this->obj;
        $this->assertNull($obj->get('foobar'));

        $obj->set('foobar', 42);
        $this->assertEquals(42, $obj->get('foobar'));

        $obj->set('foo', ['bar'=>666]);
        $this->assertEquals(['bar'=>666], $obj->get('foo'));
        $this->assertEquals(666, $obj->get('foo/bar'));
    }

    public function testGetWithCustomSeparator()
    {
        $obj = $this->obj;
        $obj->set('foo', ['bar'=>42]);
        $obj->set_separator('.');
        $this->assertEquals(42, $obj->get('foo.bar'));
    }

    public function testHas()
    {
        $obj = $this->obj;
        $this->assertFalse($obj->has('foobar'));
        $obj['foobar'] = 42;
        $this->assertTrue($obj->has('foobar'));

        $obj['foo'] = ['bar'=>'baz'];
        $this->assertTrue($obj->has('foo/bar'));
    }

    // public function testSetWithSeparator()
    // {
    //     $obj = $this->obj;
    //     $obj->set('foo/bar', 'baz');
    //     $this->assertEquals('baz', $obj->get('foo/bar'));
    //     $this->assertEquals(['bar'=>'baz'],
    // }

    /**
    * Assert that the `set_separator` method:
    * - is chainable
    * - sets the value (retrievable with `separator()`)
    * - only accepts strings (or throws exception)
    */
    public function testSetSeparator()
    {
        $obj = $this->obj;
        $ret = $obj->set_separator('_');
        $this->assertSame($ret, $obj);
        $this->assertEquals('_', $obj->separator());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_separator(false);
    }

    public function testSetSeparatorWithMoreThanOneCharacterThrowsException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_separator('foo');
    }

    public function testArrayAccess()
    {
        $obj = $this->obj;
        $obj['foo'] = 'test';
        $this->assertEquals('test', $obj['foo']);

        $this->assertTrue(isset($obj['foo']));
        unset($obj['foo']);
        $this->assertNotTrue(isset($obj['foo']));
    }

    public function testArrayAccessGetNumericException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        $obj[0];
    }

    public function testArrayAccessSetNumericException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        $obj[0] = 'foo';
    }

    public function testArrayAccessIssetNumericException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        isset($obj[0]);
    }

    public function testArrayAccessUnsetNumericException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        unset($obj[0]);
    }

    public function testAddFileIni()
    {
        $obj = $this->obj;
        $ret = $obj->add_file(__DIR__.'/config_files/test.ini');
        $this->assertSame($ret, $obj);

        $this->assertEquals(['test'=>'phpunit'], $obj['config']);

        //$this->setExpectedException('\InvalidArgumentException');
        //$obj->add_file(__DIR__.'/config_files/broken.ini');
    }

    public function testAddFileJson()
    {
        $obj = $this->obj;
        $ret = $obj->add_file(__DIR__.'/config_files/test.json');
        $this->assertSame($ret, $obj);

        $this->assertEquals(['test'=>'phpunit'], $obj['config']);

    }

    public function testAddFilePhp()
    {
        $obj = $this->obj;
        $ret = $obj->add_file(__DIR__.'/config_files/test.php');
        $this->assertSame($ret, $obj);
        $this->assertEquals(['test'=>'phpunit'], $obj['config']);
    }
}
