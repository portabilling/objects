<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace PortaObjectsTest;

use Porta\Objects\Subscription;

/**
 * Test class for Subscription
 */
class SubscriptionTest extends \PHPUnit\Framework\TestCase {

    const DATA = [
        'waive_blocked_charges' => 'Y',
        'prorate_last_period' => 'Y',
        'charge_suspended_customers' => 'Y',
        'generate_daily_charge' => 'Y',
        'waive_acc_expired_charges' => 'Y',
        'iso_4217' => 'USD',
        'waive_charges_for_last_bp' => 'Y',
        'shared' => 'N',
        'activation_mode' => 1,
        'discount_list' => [],
        'prorate_first_period' => 'Y',
        'i_subscription' => 96,
        'waive_suspended_charges' => 'Y',
        'multi_month_discount_list' => [],
        'charge_model' => 0,
        'waive_charges_for_regular_bp' => 'Y',
        'invoice_description' => 'Unlim Data, daily',
        'name' => 'Unlim Data daily',
        'periodic_fees' => [
            [
                [
                    'i_billing_period' => 4,
                    'periods' => 0,
                    'fee' => 37.5,
                    'i_subscription' => 96,
                ],
                [
                    'i_billing_period' => 1,
                    'periods' => 0,
                    'fee' => 1.25,
                    'i_subscription' => 96,
                ],
                [
                    'i_billing_period' => 3,
                    'periods' => 0,
                    'fee' => 18.75,
                    'i_subscription' => 96,
                ],
                [
                    'i_billing_period' => 2,
                    'periods' => 0,
                    'fee' => 8.75,
                    'i_subscription' => 96,
                ],
            ],
        ],
        'description' => '',
        'waive_credit_exceeded_charges' => 'Y',
        'waive_charges_for_first_bp' => 'Y',
        'multiple' => 'N',
        'penalty_mode' => 'none',
        'activation_fee' => 0,
    ];

    public function testSubscription() {
        $s = new Subscription(self::DATA);
        $this->assertEquals(96, $s->getIndex());
        $this->assertEquals('Unlim Data daily', $s->getName());
        $this->assertEquals(Subscription::ACTIVATION_START_DATE, $s->getActivationMode());
        $this->assertEquals(Subscription::CHARGE_PROGRESSIVELY, $s->getChargeModel());
        $this->assertTrue($s->isDailyCharged());
        $this->assertEquals(18.75, $s->getFee(Subscription::PERIOD_SEMIMONTH));
        $this->expectException(\Porta\Objects\Exception\PortaObjectsException::class);
        $s->getFee(1, 2);
    }

}
