<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace PortaObjectsTest;

use Porta\Objects\PortaObject;
use Porta\Objects\Defs\DefBase;
use PortaObjectsTest\Wrappers\DefBaseWrap;
use Porta\Objects\Exception\PortaObjectsException;
use Porta\Billing\Billing;

/**
 * test class for PortaObject
 *
 */
class PortaObjectTest extends \PHPUnit\Framework\TestCase {

    const DATA = [
        'i_test' => '7',
        'key1' => 'Value1',
        'key2' => 2,
        'key3' => ['key-l2-1' => 'value-l2-1', 'key-l2-2' => 'value-l2-2',],
    ];
    const ITER_DATA = [
        0 => ['i_test' => 11, 'name' => 'Name-11'],
        1 => ['i_test' => 7, 'name' => 'Name-7'],
        2 => ['i_test' => 20, 'name' => 'Name-20'],
        3 => ['i_test' => 1, 'name' => 'Name-1'],
        4 => ['i_test' => 16, 'name' => 'Name-16'],
        5 => ['i_test' => 8, 'name' => 'Name-8'],
        6 => ['i_test' => 17, 'name' => 'Name-17'],
        7 => ['i_test' => 5, 'name' => 'Name-5'],
        8 => ['i_test' => 6, 'name' => 'Name-6'],
        9 => ['i_test' => 7, 'name' => 'Name-7'],
        10 => ['i_test' => 18, 'name' => 'Name-18'],
    ];

    public function testGetSetId() {
        $obj = new PortaObject(self::DATA, new DefBase('test', 'Test'));
        $this->assertEquals(7, $obj->getIndex());
        $obj->setIndex(10);
        $this->assertEquals(10, $obj->getIndex());
        $this->assertEquals(10, $obj->getData()['i_test']);
    }

    public function testGetSet() {
        $def = new DefBase('test', 'Test');
        $obj = new PortaObject(self::DATA, $def);
        $this->assertEquals($def, $obj->getDef());
        $this->assertFalse($obj->isNew());
        $this->assertFalse($obj->isUpdated());
        $this->assertFalse(isset($obj['key4']));
        $this->assertFalse(isset($obj->key4));
        $this->assertNull($obj['key4']);
        $this->assertNull($obj->key4);
        $obj['Key4'] = 'Value4';
        $obj->Key5 = 'Value5';
        $this->assertTrue($obj->isUpdated());
        $this->assertTrue(isset($obj['key3']));
        $this->assertTrue(isset($obj->key3));
        $this->assertEquals(2, $obj['key2']);
        $this->assertEquals(2, $obj->key2);
        $obj->key2 = 'NewValue2';
        $this->assertEquals('NewValue2', $obj->key2);

        $changeData = ['Key4' => 'Value4', 'Key5' => 'Value5', 'key2' => 'NewValue2'];
        $this->assertEquals(array_merge(['i_test' => 7], $changeData), $obj->getUpdateData());
        $this->assertEquals(array_merge(self::DATA, $changeData), $obj->getData());

        $obj = new PortaObject(self::DATA, new DefBase('test', 'Test'));
        unset($obj->key2);
        $this->assertFalse(isset($obj->key2));
        $this->assertnull($obj->getData()['key2']);
        $this->assertArrayHasKey('key2', $obj->getUpdateData());
        $this->assertNull($obj->getUpdateData()['key2']);

        $obj = new PortaObject(self::DATA, new DefBase('test', 'Test'));
        $obj['Key4'] = 'Value4';
        $this->assertEquals(array_merge(self::DATA, ['Key4' => 'Value4']), $obj->getData());
        $this->expectException(PortaObjectsException::class);
        unset($obj->i_test);
    }

    public function testDateTime() {
        $obj = new PortaObject(self::DATA, new DefBase('test', 'Test'));
        \Porta\Objects\PortaFactory::$defaultTimezone = 'Pacific/Palau';
        $t = new \PortaDateTime('now', 'Pacific/Palau');
        $t->setTime((int) $t->format('G'), (int) $t->format('i'), (int) $t->format('s'));
        $obj->timeTest = $t;
        $this->assertEquals($t->formatPorta(), $obj->timeTest);
        $this->assertEquals($t->format(DATE_W3C), $obj->getPortaDateTime('timeTest')->format(DATE_W3C));
        $this->assertEquals($t->format(DATE_W3C), $obj->getPortaDateTime('timeTest', 'Pacific/Palau')->format(DATE_W3C));
        $this->assertNull($obj->getPortaDateTime('unknown'));
        $this->expectException(PortaObjectsException::class);
        $obj->getPortaDateTime('key1');
    }

    public function testGetRequired() {
        $obj = new PortaObject(self::DATA, new DefBase('test', 'Test'));
        $this->assertEquals('Value1', $obj->getRequired('key1'));
        $this->expectException(PortaObjectsException::class);
        $obj->getRequired('unknown');
    }

    public function testLoadParent() {
        $defChild = new DefBaseWrap('child', 'Child');
        $defParent = new DefBaseWrap('parent', 'Parent');
        $child = new PortaObject(['i_child' => 14, 'i_parent' => 89], $defChild);
        $mock = $this->createMock(Billing::class);
        $mock->expects($this->exactly(1))
                ->method('call')
                ->withConsecutive(
                        [
                            $this->equalTo('/Parent/get_parent_info'),
                            $this->equalTo(['i_parent' => 89, 'field_option_2' => 1, 'param1' => 'value1'])
                        ]
                )
                ->willReturn(['parent_info' => ['i_parent' => 89]]);
        \Porta\Objects\PortaFactory::setup($mock, 'Pacific/Palau');
        $parent = $child->loadParent($defParent, DefBaseWrap::OPTION_2, ['param1' => 'value1']);
        $this->assertInstanceOf(PortaObject::class, $parent);
        $this->assertEquals(89, $parent->getIndex());
    }

    public function testLoadChilds() {
        $defParent = new DefBaseWrap('parent', 'Parent');
        $defChild = new DefBaseWrap('child', 'Child');
        $parent = new PortaObject(['i_parent' => 14], $defParent);
        $mock = $this->createMock(Billing::class);
        $mock->expects($this->exactly(1))
                ->method('call')
                ->withConsecutive(
                        [
                            $this->equalTo('/Child/get_child_list'),
                            $this->equalTo(['i_parent' => 14, 'field_option_2' => 1, 'param1' => 'value1'])
                        ]
                )
                ->willReturn([
                    'child_list' =>
                    [
                        ['i_child' => 13, 'i_parent' => 89],
                        ['i_child' => 7, 'i_parent' => 89],
                        ['i_child' => 78, 'i_parent' => 89],
                    ]
        ]);
        \Porta\Objects\PortaFactory::setup($mock, 'Pacific/Palau');
        $childs = $parent->loadChildren($defChild, DefBaseWrap::OPTION_2, ['param1' => 'value1']);
        $this->assertEquals(3, count($childs));
        foreach ($childs as $key => $element) {
            $this->assertInstanceOf(PortaObject::class, $element);
            $this->assertEquals($key, $element->getIndex());
        }
    }

    public function testWriteNew() {
        $data = self::DATA;
        unset($data['i_test']);
        $data2 = $data;
        $data2['key4'] = 'value4';
        $mock = $this->createMock(Billing::class);
        $mock->expects($this->exactly(2))
                ->method('call')
                ->withConsecutive(
                        [$this->equalTo('/Test/add_test'), $this->equalTo(['test_info' => $data2])],
                        [$this->equalTo('/Test/add_test'), $this->equalTo(['test_info' => $data])]
                )
                ->willReturn(['i_test' => 7], []);
        \Porta\Objects\PortaFactory::setup($mock, 'Pacific/Palau');

        $obj = new PortaObject($data, new DefBase('test', 'Test'));
        $obj->key4 = 'value4';
        $this->assertTrue($obj->isNew());
        $obj->write();
        $this->assertEquals(array_merge(self::DATA, ['key4' => 'value4']), $obj->getData());
        $this->assertEquals(['i_test' => '7'], $obj->getUpdateData());

        $obj = new PortaObject($data, new DefBase('test', 'Test'));
        $this->expectException(PortaObjectsException::class);
        $obj->write();
    }

    public function testWriteUpdated() {
        $data = self::DATA;
        $data['key4'] = 'value4';
        $mock = $this->createMock(Billing::class);
        $mock->expects($this->exactly(2))
                ->method('call')
                ->withConsecutive(
                        [$this->equalTo('/Test/update_test'), $this->equalTo(['test_info' => ['i_test' => 7, 'key4' => 'value4']])],
                        [$this->equalTo('/Test/update_test'), $this->equalTo(['test_info' => ['i_test' => 7, 'key4' => 'value4']])]
                )
                ->willReturn(['i_test' => 7], []);
        \Porta\Objects\PortaFactory::setup($mock, 'Pacific/Palau');

        $obj = new PortaObject($data, new DefBase('test', 'Test'));
        $this->assertFalse($obj->isNew());
        $this->assertFalse($obj->isUpdated());
        $obj->write();

        $obj->key4 = 'value4';
        $this->assertFalse($obj->isNew());
        $this->assertTrue($obj->isUpdated());

        $obj->write();
        $this->assertEquals($data, $obj->getData());
        $this->assertEquals(['i_test' => '7'], $obj->getUpdateData());

        $obj = new PortaObject($data, new DefBase('test', 'Test'));
        $obj->key4 = 'value4';
        $this->expectException(PortaObjectsException::class);
        $obj->write();
    }

}
