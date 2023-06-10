<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace PortaObjectsTest;

use Porta\Objects\PortaFactory;
use Porta\Objects\Account;
use PortaObjectsTest\Wrappers\AccountWrap;
use Porta\Objects\Defs\DefAccount;
use Porta\Objects\Addon;
use Porta\Objects\Customer;
use Porta\Billing\Billing;

/**
 * Test clas for Account
 *
 */
class AccountTest extends DataTestCase {

    const ACT_ADDON_IND = [86, 87, 89, 63, 62, 61];
    const ANSWER1 = [
        'i_account' => 2984,
        'assigned_addons' =>
        [
            0 =>
            [
                'i_product' => 86,
                'addon_effective_from' => '2023-05-15 09:34:58',
                'addon_effective_to' => '2023-05-15 00:00:00',
            ],
            1 =>
            [
                'i_product' => 89,
                'addon_effective_from' => '2023-05-31 15:00:00',
                'addon_effective_to' => NULL,
            ],
            2 =>
            [
                'i_product' => 63,
                'addon_effective_from' => '2023-05-11 21:32:30',
                'addon_effective_to' => NULL,
            ],
            3 =>
            [
                'i_product' => 62,
                'addon_effective_from' => '2023-05-11 21:32:30',
                'addon_effective_to' => NULL,
            ],
            4 =>
            [
                'i_product' => 61,
                'addon_effective_from' => '2023-05-11 21:32:30',
                'addon_effective_to' => NULL,
            ],
        ],
    ];
    const ANSWER2 = [
        'i_account' => 2984,
        'assigned_addons' =>
        [
            0 =>
            [
                'i_product' => 86,
                'addon_effective_from' => '2023-05-15 09:34:58',
                'addon_effective_to' => '2023-05-15 00:00:00',
            ],
            1 =>
            [
                'i_product' => 89,
                'addon_effective_from' => '2023-05-31 15:00:00',
                'addon_effective_to' => NULL,
            ],
            2 =>
            [
                'i_product' => 99,
                'addon_effective_from' => '2023-05-15 00:00:00',
                'addon_effective_to' => NULL,
            ],
        ],
        'cont1' => 'contact1',
    ];
    const ANSWER3 = [
        'i_account' => 2984,
        'assigned_addons' =>
        [
            0 =>
            [
                'i_product' => 86,
                'addon_effective_from' => '2023-05-15 09:34:58',
                'addon_effective_to' => '2023-05-31 14:59:59',
            ],
            1 =>
            [
                'i_product' => 87,
                'addon_effective_from' => '2023-05-21 18:47:13',
                'addon_effective_to' => '2023-05-31 14:59:59',
            ],
            2 =>
            [
                'i_product' => 89,
                'addon_effective_from' => '2023-05-31 15:00:00',
                'addon_effective_to' => NULL,
            ],
            3 =>
            [
                'i_product' => 63,
                'addon_effective_from' => '2023-05-11 21:32:30',
                'addon_effective_to' => NULL,
            ],
            4 =>
            [
                'i_product' => 62,
                'addon_effective_from' => '2023-05-11 21:32:30',
                'addon_effective_to' => NULL,
            ],
            5 =>
            [
                'i_product' => 61,
                'addon_effective_from' => '2023-05-11 21:32:30',
                'addon_effective_to' => NULL,
            ],
            6 =>
            [
                'i_product' => 85,
                'addon_effective_from' => '2023-05-15 00:00:00',
                'addon_effective_to' => NULL,
            ],
        ],
    ];

    public function testCreate() {
        $acc = new AccountWrap($this->load('account_2984')['account_info']);
        $this->assertEquals(2984, $acc->getIndex());
        $this->assertEquals('6808881050@msisdn', $acc->getId());
        $this->assertEquals('6808881050', $acc->getIdNoRealm());
        $this->assertEquals(Account::BILL_MODEL_CREDIT, $acc->getBillingModel());
        $this->assertEquals(Account::BILL_STATUS_OPEN, $acc->getBillStatus());
        $this->assertEquals(Account::ROLE_MOBILE, $acc->getRole());
        $this->assertEquals(2543, $acc->getCustomerIndex());
        $this->assertNull($acc->getCustomer());
        return $acc;
    }

    public function testAddons() {
        PortaFactory::setup($this->createMock(Billing::class), 'UTC');
        $acc = new AccountWrap($this->load('account_2984')['account_info']);
        $t = new \DateTimeImmutable('2023-05-15 00:00:00');
        $this->assertEquals(self::ACT_ADDON_IND, $addonInices = $acc->getActiveAddonsIndices());
        foreach ($addonInices as $index) {
            $this->assertInstanceOf(Addon::class, $acc->getActiveAddon($index));
        }
        $this->assertNull($acc->getActiveAddon(99));
        $this->assertEquals(self::ACT_ADDON_IND, array_keys($addons = $acc->getActiveAddons()));

        $this->assertFalse($acc->isUpdated());
        $acc->setAddonEffectiveTo(86, $t);
        $this->assertTrue($acc->isUpdated());
        $acc->addonRemove(87);
        $this->assertTrue($acc->isUpdated());
        $this->assertNull($acc->getActiveAddon(87));
        $this->assertArrayHasKey('assigned_addons', $acc->spyUpdatedData());
        $this->assertTrue($acc->spyUpdatedData()['assigned_addons']);
        $this->assertEquals(self::ANSWER1, $acc->getUpdateData());
        $acc->addonsRemove([61, 62, 63]);
        $acc->addonAdd(99, $t);
        $acc->cont1 = 'contact1';
        $this->assertEquals(self::ANSWER2, $acc->getUpdateData());
        return $acc;
    }

    /**
     *
     * @depends testAddons
     */
    public function testWriteUpdated(AccountWrap $acc) {
        $mock = $this->createMock(Billing::class);
        $mock->expects($this->exactly(1))
                ->method('call')
                ->withConsecutive(
                        [
                            $this->equalTo('/Account/update_account'),
                            $this->equalTo(['account_info' => self::ANSWER2])
                        ]
                )
                ->willReturn(['i_account' => 2984]);
        PortaFactory::setup($mock, 'Pacific/Palau');
        $acc->write();
        $this->assertFalse($acc->isUpdated());
        $this->assertEquals([86, 89, 99], $acc->getActiveAddonsIndices());
    }

    public function testAvailableAddons() {
        $acc = new AccountWrap($this->load('account_2984')['account_info']);
        $mock = $this->createMock(Billing::class);
        $mock->expects($this->exactly(2))
                ->method('call')
                ->withConsecutive(
                        [
                            $this->equalTo('/Product/get_allowed_addons'),
                            $this->equalTo(["detailed_info" => 1, "i_product" => 60, "with_subscription" => 1])
                        ],
                        [
                            $this->equalTo('/Account/update_account'),
                            $this->equalTo(['account_info' => self::ANSWER3])
                        ]
                )
                ->willReturn(
                        $this->load('allowed_addons_60'),
                        ['i_account' => 2984]
        );
        PortaFactory::setup($mock, 'Pacific/Palau');
        $acc->loadAvailableAddons(Addon::LOAD_DETAILED_INFO + Addon::LOAD_WITH_SUBSCRIPTION);
        $this->assertEquals([80, 75, 71, 73, 78, 77, 85, 93, 88, 90, 91, 92, 74,], $acc->getAvailableAddonsIndices());
        foreach ($acc->getAvailableAddons() as $addon) {
            $this->assertInstanceOf(Addon::class, $addon);
        }

        $t = new \DateTimeImmutable('2023-05-15 00:00:00');
        $this->assertFalse($acc->isUpdated());
        $acc->addonAdd(85, $t);
        $this->assertTrue($acc->isUpdated());
        $addon = clone $acc->getActiveAddon(85);
        $acc->write();
        $this->assertEquals($addon->getData(), $acc->getActiveAddon(85)->getData());
        $this->assertFalse($acc->isUpdated());
        $this->assertFalse($acc->getActiveAddon(85)->isUpdated());
    }

    public function testAddAddonException() {
        $acc = new AccountWrap($this->load('account_2984')['account_info']);
        $this->expectException(\Porta\Objects\Exception\PortaObjectsException::class);
        $acc->addonAdd(63);
    }

    public function testAddonNoChange() {
        $acc = new AccountWrap($this->load('account_2984')['account_info']);
        $this->assertFalse($acc->isUpdated());
        $acc->param1 = 'value1';
        $this->assertTrue($acc->isUpdated());
        $this->assertEquals(['i_account' => 2984, 'param1' => 'value1'], $acc->getUpdateData());
    }

    public function testCustomer() {
        $acc = new AccountWrap($this->load('account_2984')['account_info']);
        $mock = $this->createMock(Billing::class);
        $mock->expects($this->exactly(1))
                ->method('call')
                ->withConsecutive(
                        [
                            $this->equalTo('/Customer/get_customer_info'),
                            $this->equalTo(["i_customer" => 2543, 'detailed_info' => 1, 'get_time_zone_name' => 1])
                        ],
                )
                ->willReturn(
                        $this->load('customer_2543'),
        );
        PortaFactory::setup($mock, 'Pacific/Palau');
        $acc->loadCustomer(Customer::LOAD_DETAILED_INFO, ['get_time_zone_name' => 1]);
        $cus = $acc->getCustomer();
        $this->assertEquals(2543, $acc->getCustomerIndex());
        $this->assertEquals($acc, $cus->getAccount(2984));
        $cus2 = (clone $cus)->setIndex(99);
        $this->expectException(\Porta\Objects\Exception\PortaObjectsException::class);
        $acc->setCustomer($cus2);
    }

    public function testSubscriptionsLoadActive() {
        $acc = new AccountWrap($this->load('account_2984')['account_info']);
        $mock = $this->createMock(Billing::class);
        $mock->expects($this->exactly(1))
                ->method('call')
                ->withConsecutive(
                        [
                            $this->equalTo('/Account/get_subscriptions'),
                            $this->equalTo([
                                'i_account' => $acc->getIndex(),
                                'with_effective_fees' => 1,
                                'with_promotional_periods_info' => 1,
                                'with_regular_discount_list' => 1,
                                'with_upcharge_list' => 1
                            ])
                        ],
                )
                ->willReturn(
                        $this->load('account_subscriptions_2984'),
        );
        PortaFactory::setup($mock, 'Pacific/Palau');
        $acc->loadActiveSubscriptions();
        $this->assertEquals('2023-05-27 14:59:59', $acc->getActiveAddon(86)->getPaidTo()->formatPorta());
    }

    public function testSubscriptionsLoadActiveException() {
        $acc = new AccountWrap($this->load('account_2984')['account_info']);
        $data = $this->load('account_subscriptions_2984')['subscriptions'][0];
        $this->expectException(\Porta\Objects\Exception\PortaObjectsException::class);
        $acc->attachActiveSubscriptions([$data, $data]);
    }

    public function testActiveateSubscription() {
        $acc = new Account(['i_account' => 7, 'assigned_addons' => []]);
        $mock = $this->createMock(Billing::class);
        $mock->expects($this->exactly(2))
                ->method('call')
                ->withConsecutive(
                        [
                            $this->equalTo('/Account/activate_subscriptions'),
                            $this->equalTo(['i_account' => 7])
                        ],
                        [
                            $this->equalTo('/Account/activate_subscriptions'),
                            $this->equalTo(['i_account' => 7])
                        ],
                )
                ->willReturn(
                        ['success' => 1],
                        [],
        );
        PortaFactory::setup($mock, 'Pacific/Palau');
        $this->assertEquals($acc, $acc->activateSubscriptions());
        $this->expectException(\Porta\Objects\Exception\PortaObjectsException::class);
        $acc->activateSubscriptions();
    }

    public function testChargeSubscription() {
        $acc = new Account(['i_account' => 7, 'assigned_addons' => []]);
        $mock = $this->createMock(Billing::class);
        $mock->expects($this->exactly(2))
                ->method('call')
                ->withConsecutive(
                        [
                            $this->equalTo('/Account/charge_subscription_fees'),
                            $this->equalTo(['i_account' => 7])
                        ],
                        [
                            $this->equalTo('/Account/charge_subscription_fees'),
                            $this->equalTo(['i_account' => 7, 'immediately_in_advance' => 5])
                        ],
                )
                ->willReturn(
                        ['success' => 1],
                        [],
        );
        PortaFactory::setup($mock, 'Pacific/Palau');
        $this->assertEquals($acc, $acc->chargeSubscriptions());
        $this->expectException(\Porta\Objects\Exception\PortaObjectsException::class);
        $acc->chargeSubscriptions(5);
    }

}
