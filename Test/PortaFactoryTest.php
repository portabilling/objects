<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace PortaObjectsTest;

use Porta\Objects\PortaFactory;
use Porta\Objects\Defs\DefBase;
use PortaObjectsTest\Wrappers\DefBaseWrap;
use Porta\Objects\PortaObject;
use Porta\Objects\Customer;
use Porta\Objects\Account;
use Porta\Billing\Billing;

/**
 * PortaFactory test class
 *
 */
class PortaFactoryTest extends DataTestCase {

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

    public function testSetup() {
        $mock = $this->createMock(Billing::class);
        $mock->expects($this->exactly(1))
                ->method('isSessionPresent')
                ->willReturn(true);
        PortaFactory::setup($mock, 'Pacific/Palau');
        $this->assertInstanceOf(Billing::class, PortaFactory::$billing);
        $this->assertTrue(PortaFactory::$billing->isSessionPresent());
        $this->assertEquals('Pacific/Palau', PortaFactory::$defaultTimezone);
    }

    public function testCreateObjectFromData() {
        $obj = PortaFactory::createObjectFromData(self::DATA, new DefBase('test', 'Test'));
        $this->assertInstanceOf(PortaObject::class, $obj);
        $this->assertEquals(7, $obj->getIndex());
        $this->assertEquals(self::DATA, $obj->getData());
    }

    public function testCreateObjectsFromArray() {
        $data = [7 => self::DATA, 10 => self::DATA];
        $data[10]['i_test'] = 10;
        $list = PortaFactory::createObjectsFromArray($data, new DefBase('test', 'Test'));
        foreach ($list as $key => $val) {
            $this->assertInstanceOf(PortaObject::class, $val);
            $this->assertEquals($key, $val->getIndex());
            $this->assertEquals($data[$key], $val->getData());
        }
        $this->assertEquals([], PortaFactory::createObjectsFromArray([], new DefBase('test', 'Test')));
    }

    public function testLoadById() {
        $mock = $this->createMock(Billing::class);
        $mock->expects($this->exactly(3))
                ->method('call')
                ->withConsecutive(
                        [$this->equalTo('/Test/get_test_info'), $this->equalTo(['i_test' => 7])],
                        [$this->equalTo('/Test/get_test_info'), $this->equalTo(['i_test' => 7, 'field_option_2' => 1, 'extra_param' => 'extra_value'])],
                        [$this->equalTo('/Test/get_test_info'), $this->equalTo(['i_test' => 7])],
                )
                ->willReturn(['test_info' => self::DATA], ['test_info' => self::DATA], []);
        PortaFactory::setup($mock, 'Pacific/Palau');
        $result = PortaFactory::loadByIndex(7, new DefBase('test', 'Test'));
        $this->assertInstanceOf(PortaObject::class, $result);
        $this->assertEquals(self::DATA, $result->getData());
        $result = PortaFactory::loadByIndex(7, new DefBaseWrap('test', 'Test'), ['extra_param' => 'extra_value'], DefBaseWrap::OPTION_2);
        $this->assertInstanceOf(PortaObject::class, $result);
        $this->assertEquals(self::DATA, $result->getData());
        $this->assertNull(PortaFactory::loadByIndex(7, new DefBase('test', 'Test')));
    }

    public function testLoadList() {
        $data = [7 => self::DATA, 10 => self::DATA];
        $data[10]['i_test'] = 10;
        $mock = $this->createMock(Billing::class);
        $mock->expects($this->exactly(2))
                ->method('call')
                ->withConsecutive(
                        [$this->equalTo('/Test/get_test_list'), $this->equalTo(['extra_param' => 'extra_value'])],
                        [$this->equalTo('/Test/get_test_list'), $this->equalTo(['field_option_2' => 1, 'param1' => 'val1'])]
                )
                ->willReturn(['test_list' => $data], []);
        PortaFactory::setup($mock, 'Pacific/Palau');
        $list = PortaFactory::loadList(new DefBase('test', 'Test'), ['extra_param' => 'extra_value']);
        foreach ($list as $key => $val) {
            $this->assertInstanceOf(PortaObject::class, $val);
            $this->assertEquals($key, $val->getIndex());
            $this->assertEquals($data[$key], $val->getData());
        }
        $this->assertNull(PortaFactory::loadList(new DefBaseWrap('test', 'Test'), ['param1' => 'val1'], DefBaseWrap::OPTION_2));
    }

    public function testLoadListGeneratorBulk() {
        $mock = $this->createMock(Billing::class);
        $mock->expects($this->exactly(4))
                ->method('call')
                ->withConsecutive(
                        [$this->equalTo('/Test/get_test_list'), ['field_option_2' => 1, 'param1' => 'value1', 'offset' => 0, 'limit' => 3]],
                        [$this->equalTo('/Test/get_test_list'), ['field_option_2' => 1, 'param1' => 'value1', 'offset' => 3, 'limit' => 3]],
                        [$this->equalTo('/Test/get_test_list'), ['field_option_2' => 1, 'param1' => 'value1', 'offset' => 6, 'limit' => 3]],
                        [$this->equalTo('/Test/get_test_list'), ['field_option_2' => 1, 'param1' => 'value1', 'offset' => 9, 'limit' => 3]],
                )
                ->willReturn(
                        ['test_list' => array_slice(self::ITER_DATA, 0, 3)],
                        ['test_list' => array_slice(self::ITER_DATA, 3, 3)],
                        ['test_list' => array_slice(self::ITER_DATA, 6, 3)],
                        ['test_list' => array_slice(self::ITER_DATA, 9, 3)],
        );
        PortaFactory::setup($mock, 'Pacific/Palau');
        $i = 0;
        foreach (PortaFactory::loadListGeneratorBulk(new DefBaseWrap('test', 'Test'), 3, ['param1' => 'value1'], DefBaseWrap::OPTION_2) as $group) {
            foreach ($group as $key => $element) {
                $this->assertEquals($key, $element->getIndex());
                $this->assertEquals(self::ITER_DATA[$i]['i_test'], $element->getIndex());
                $i += 1;
            }
        }
    }

    public function testLoadIteratorByOne() {
        $mock = $this->createMock(Billing::class);
        $mock->expects($this->exactly(4))
                ->method('call')
                ->withConsecutive(
                        [$this->equalTo('/Test/get_test_list'), ['param1' => 'value1', 'offset' => 0, 'limit' => 3]],
                        [$this->equalTo('/Test/get_test_list'), ['param1' => 'value1', 'offset' => 3, 'limit' => 3]],
                        [$this->equalTo('/Test/get_test_list'), ['param1' => 'value1', 'offset' => 6, 'limit' => 3]],
                        [$this->equalTo('/Test/get_test_list'), ['param1' => 'value1', 'offset' => 9, 'limit' => 3]],
                )
                ->willReturn(
                        ['test_list' => array_slice(self::ITER_DATA, 0, 3)],
                        ['test_list' => array_slice(self::ITER_DATA, 3, 3)],
                        ['test_list' => array_slice(self::ITER_DATA, 6, 3)],
                        ['test_list' => array_slice(self::ITER_DATA, 9, 3)],
        );
        PortaFactory::setup($mock, 'Pacific/Palau');
        $i = 0;
        foreach (PortaFactory::loadListGeneratorByOne(new DefBase('test', 'Test'), 3, ['param1' => 'value1']) as $element) {
            $this->assertEquals(self::ITER_DATA[$i]['i_test'], $element->getIndex());
            $i += 1;
        }
    }

    public function testLoadIteratorFail() {
        $mock = $this->createMock(Billing::class);
        $mock->expects($this->exactly(1))
                ->method('call')
                ->withConsecutive([$this->equalTo('/Test/get_test_list'), ['param1' => 'value1', 'offset' => 0, 'limit' => 3]],)
                ->willReturn([]);
        PortaFactory::setup($mock, 'Pacific/Palau');
        $i = 0;
        foreach (PortaFactory::loadListGeneratorBulk(new DefBase('test', 'Test'), 3, ['param1' => 'value1']) as $element) {
            $this->assertEquals(self::ITER_DATA[$i]['i_test'], $element->getIndex());
            $i += 1;
        }
        $this->assertEquals(0, $i);
    }

    public function testLoadAccount() {
        $mock = $this->createMock(Billing::class);
        $mock->expects($this->exactly(3))
                ->method('call')
                ->withConsecutive(
                        [$this->equalTo('/Account/get_account_info'), ['i_account' => 2984, 'with_customer_info' => 1, 'param' => 'value']],
                        [$this->equalTo('/Account/get_account_info'), ['i_account' => 2984]],
                        [$this->equalTo('/Account/get_account_info'), ['id' => 'AccountId']],
                )
                ->willReturn(
                        $this->load('account_with_customer_2984'),
                        [],
                        $this->load('account_with_customer_2984'),
        );
        PortaFactory::setup($mock, 'Pacific/Palau');
        $acc = PortaFactory::loadAccountByIndex(2984, Account::LOAD_WITH_CUSTOMER_INFO, ['param' => 'value']);
        $this->assertInstanceOf(Account::class, $acc);
        $this->assertInstanceOf(Customer::class, $acc->getCustomer());
        $this->assertNull(PortaFactory::loadAccountByIndex(2984));
        $this->assertInstanceOf(Account::class, PortaFactory::loadAccountById('AccountId'));
    }

}
