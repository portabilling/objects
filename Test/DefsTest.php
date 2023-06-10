<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace PortaObjectsTest;

use Porta\Objects\Defs;
use PortaObjectsTest\Wrappers\DefBaseWrap;

/**
 * Test class for DefBase
 *
 */
class DefsTest extends \PHPUnit\Framework\TestCase {

    public function testBaseNormal() {
        $def = new Defs\DefBase('test', 'Test', 'TestNamespace\TestClass');

        $this->assertEquals('test', $def->getKey());
        $this->assertEquals('TestNamespace\TestClass', $def->getClass());
        $this->assertEquals('i_test', $def->getIndexField());
        $this->assertEquals('/Test/get_test_info', $def->getLoadMethod());
        $this->assertEquals('test_info', $def->getLoadFieldName());
        $this->assertEquals('/Test/get_test_list', $def->getListMethod());
        $this->assertEquals('test_list', $def->getListFieldName());
        $this->assertEquals('/Test/add_test', $def->getCreateMethod());
        $this->assertEquals('test_info', $def->getCreateFieldName());
        $this->assertEquals('/Test/update_test', $def->getUpdateMethod());
        $this->assertEquals('test_info', $def->getUpdateFieldName());
        $def = new Defs\DefBase('test', 'Test');
        $this->assertEquals('Porta\Objects\PortaObject', $def->getClass());
    }

    public function testBaseOptions() {
        $def = new DefBaseWrap('test', 'Test');
        $this->assertEquals([], $def->buildLoadOptions(0));
        $this->assertEquals(['field_option_2' => 1], $def->buildLoadOptions(DefBaseWrap::OPTION_2));
        $this->assertEquals(
                ['field_option_1' => 1, 'field_option_4' => 1],
                $def->buildLoadOptions(DefBaseWrap::OPTION_1 + DefBaseWrap::OPTION_4)
        );
    }

    public function testCustomer() {
        $def = new Defs\DefCustomer();
        $this->assertEquals(\Porta\Objects\Customer::class, $def->getClass());
        $this->assertEquals('customer_info', $def->getLoadFieldName());
        $this->assertEquals(['effective_values' => 1], $def->buildLoadOptions(\Porta\Objects\Customer::LOAD_EFFECTIVE_VALUES));
    }

    public function testAccount() {
        $def = new Defs\DefAccount();
        $this->assertEquals(\Porta\Objects\Account::class, $def->getClass());
        $this->assertEquals('account_info', $def->getLoadFieldName());
        $this->assertEquals(['get_service_features' => 1], $def->buildLoadOptions(\Porta\Objects\Account::LOAD_GET_SERVICE_FEATURES));
    }

    public function testAddon() {
        $def = new Defs\DefAddon();
        $this->assertEquals(\Porta\Objects\Addon::class, $def->getClass());
        $this->assertEquals(['detailed_info' => 1], $def->buildLoadOptions(\Porta\Objects\Addon::LOAD_DETAILED_INFO));
        $this->assertEquals('/Product/get_allowed_addons', $def->getListMethod());
    }

    public function testSubscription() {
        $def = new Defs\DefSubscription();
        $this->assertEquals(\Porta\Objects\Subscription::class, $def->getClass());
        $this->assertEquals('subscriptions', $def->getListFieldName());
        $this->assertEquals(['with_discounts' => 1], $def->buildLoadOptions(\Porta\Objects\Subscription::LOAD_WITH_DISCOUNTS));
    }

}
