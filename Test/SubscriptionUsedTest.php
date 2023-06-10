<?php

/*
 * PortaOne Billing objects helper library
 * API docs: https://docs.portaone.com
 * (c) Alexey Pavlyuts <alexey@pavlyuts.ru>
 */

namespace PortaObjectsTest;

use Porta\Objects\PortaFactory;
use Porta\Objects\SubscriptionUsed;
use \Porta\Objects\Exception\PortaObjectsException;
use Porta\Billing\Billing;

/**
 * Tests for class SubscriptionUse
 *
 */
class SubscriptionUsedTest extends DataTestCase {

    public function testMain() {
        PortaFactory::setup($this->createMock(Billing::class), 'UTC');
        $data = $this->load('addon')['product_subscription'];
        $obj = new SubscriptionUsed($data);
        $this->assertFalse($obj->isActiveDataLoaded());
        $obj->updateWithActiveData($this->load('subscription_state_86'));
        $this->assertTrue($obj->isActiveDataLoaded());
        $this->assertEquals(SubscriptionUsed::STATUS_ACTIVE, $obj->getStatus());
        $this->assertEquals(6, $obj->getEffectiveFee());
        $this->assertEquals('2023-05-27 23:59:59', $obj->getPaidTo()->formatPorta());
    }

    public function testNoValues() {
        $data = $this->load('addon')['product_subscription'];
        unset($data['effective_fee']);
        $obj = new SubscriptionUsed($data);
        $this->assertEquals(0, $obj->getEffectiveFee());
        $data = $this->load('subscription_state_86');
        unset($data['billed_to']);
        $obj->updateWithActiveData($data);
        $this->assertNull($obj->getPaidTo());
    }

    public function testNoUpdatedException() {
        $data = $this->load('addon')['product_subscription'];
        $obj = new SubscriptionUsed($data);
        $this->expectException(PortaObjectsException::class);
        $obj->getStatus();
    }

    public function testWriteProtect() {
        $data = $this->load('addon')['product_subscription'];
        $obj = new SubscriptionUsed($data);
        $this->expectException(PortaObjectsException::class);
        $obj['some_field'] = 0;
    }

}
