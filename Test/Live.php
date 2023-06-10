<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace PortaObjectsTest;

use Porta\Objects\PortaFactory;
use Porta\Objects\Account;
use Porta\Objects\Addon;
use PortaApi\Config as C;
use GuzzleHttp\RequestOptions as RO;

/**
 * Description of Live
 *
 * @author alexe
 */
class Live extends DataTestCase {

    public static function setUpBeforeClass(): void {
        PortaFactory::$billing = new Billing(
                [
            C::HOST => 'billing.telco.pw',
            C::ACCOUNT => [
                C::LOGIN => 'pavlyuts',
                C::PASSWORD => 'h65LR8*q3H',
            ],
            C::OPTIONS => [
                //RO::TIMEOUT => 3,
                RO::VERIFY => false,
            ],
                ],
                new \PortaApi\Session\SessionFileStorage(__DIR__ . '/session.tmp')
        );
        parent::setUpBeforeClass();
    }

    protected function write($name, $data) {
        file_put_contents($this->getFileName($name), JSON_ENCODE($data, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
    }

    protected function get($endpoint, $data, $target) {
        $r = PortaFactory::$billing->call($endpoint, $data);
        $this->write($target, $r);
    }

    public function testLoad() {
        $this->assertTrue(true);
//        $this->get(
//                '/Account/get_account_info',
//                [
//                    "i_account" => 2984,
//                ],
//                'account_2984');
//
//        $this->get(
//                '/Account/get_subscriptions',
//                [
//                    "i_account" => 2984,
//                    "with_effective_fees" => 1,
//                    "with_promotional_periods_info" => 1,
//                    "with_regular_discount_list" => 1,
//                    "with_upcharge_list" => 1
//                ],
//                'account_subscriptions_2984');
//
//        $this->get(
//                '/Product/get_allowed_addons',
//                [
//                    "i_product" => 60,
//                    "detailed_info" => 1,
//                    "with_subscription" => 1,
//                ],
//                'allowed_addons_60');
//
//        $this->get(
//                '/Customer/get_customer_info',
//                [
//                    "i_customer" => 2543,
//                    "detailed_info" => 1
//                ],
//                'customer_2984');
//
//        $this->get(
//                '/Account/get_account_info',
//                [
//                    "i_account" => 2984,
//                    'with_customer_info' => 1
//                ],
//                'account_with_customer');
    }

}
