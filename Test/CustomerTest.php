<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace PortaObjectsTest;

use Porta\Objects\PortaFactory;
use Porta\Objects\Customer;
use Porta\Objects\Account;
use Porta\Billing\Billing;

/**
 * Test for Customer class
 */
class CustomerTest extends DataTestCase {

    public function testPostpaid() {
        $cus = new Customer($this->load('customer_2543')['customer_info']);
        $this->assertEquals('PP Mobile test', $cus->getName());
        $this->assertFalse($cus->isPrepaid());
        $this->assertTrue($cus->isCreditLimitSet());
        $this->assertFalse($cus->isAvailableFundsUnlimited());
        $this->assertEquals(12.39, $cus->getAvailableFunds());
        unset($cus['credit_limit']);
        $this->assertTrue($cus->isAvailableFundsUnlimited());
        $this->assertNull($cus->getAvailableFunds());
    }

    public function testPrepaid() {
        $cus = new Customer($this->load('customer_444')['customer_info']);
        $this->assertTrue($cus->isPrepaid());
        $this->assertTrue($cus->isCreditLimitSet());
        $this->assertFalse($cus->isAvailableFundsUnlimited());
        $this->assertEquals(23.61693, $cus->getAvailableFunds());
    }

    public function testLoadAccounts() {
        $cus = new Customer($this->load('customer_444')['customer_info']);
        $mock = $this->createMock(Billing::class);
        $mock->expects($this->exactly(2))
                ->method('call')
                ->withConsecutive(
                        [
                            $this->equalTo('/Account/get_account_list'),
                            $this->equalTo(["i_customer" => 444, "detailed_info" => 1])
                        ],
                        [
                            $this->equalTo('/Account/get_account_list'),
                            $this->equalTo(["i_customer" => 444, "detailed_info" => 1, 'param' => 'value'])
                        ],
                )
                ->willReturn(
                        $this->load('account_list_444'),
                        $this->load('account_list_444'),
        );
        PortaFactory::setup($mock, 'Pacific/Palau');
        $this->assertEquals([2983, 549, 2722], $cus->getAccountsIndices());
        $this->assertEquals('6808801279@msisdn', $cus->getAccount(2983)->getId());

        $cus = new Customer($this->load('customer_444')['customer_info']);
        $cus->loadAccounts(\Porta\Objects\Account::LOAD_DETAILED_INFO, ['param' => 'value']);
        $this->assertEquals([2983, 549, 2722], $cus->getAccountsIndices());
        $this->assertEquals('6808801279@msisdn', $cus->getAccount(2983)->getId());
    }

    public function testAttachAccaountException() {
        $cus = new Customer($this->load('customer_444')['customer_info']);
        $this->expectException(\Porta\Objects\Exception\PortaObjectsException::class);
        $cus->attachAccount(new Account(['i_account' => 1234, 'i_customer' => 999]));
    }

}
